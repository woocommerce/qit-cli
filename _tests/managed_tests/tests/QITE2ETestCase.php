<?php

namespace QITE2E;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../test-result-parser.php';

class QITE2ETestCase extends TestCase {
	public function validate_and_normalize( string $file_path, ?callable $callback = null ): string {
		if ( ! file_exists( $file_path ) ) {
			$this->fail( 'Test result file not found at: ' . $file_path );
		}

		$json = json_decode( file_get_contents( $file_path ), true );

		if ( ! is_array( $json ) || empty( $json ) ) {
			$this->fail( 'Test result file is not a JSON: ' . $file_path );
		}

		/*
		 * Sort 'phpcs' and 'semgrep' files in security tests,
		 * so that the order in which the files are scanned do
		 * not change the JSON of the test.
		 */
		foreach ( $json as &$v ) {
			if ( isset( $v['test_result_json']['tool']['phpcs']['files'] ) ) {
				if ( ! uksort( $v['test_result_json']['tool']['phpcs']['files'], 'strcmp' ) ) {
					$this->fail( 'Failed to sort phpcs files' );
				}
			}

			if ( isset( $v['test_result_json']['tool']['semgrep']['files'] ) ) {
				if ( ! uksort( $v['test_result_json']['tool']['semgrep']['files'], 'strcmp' ) ) {
					$this->fail( 'Failed to sort semgrep files' );
				}
			}
		}

		$rules = [
			'test_run_id'                     => [
				'normalize' => 123456,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				},
			],
			'run_id'                          => [
				'normalize' => 123456,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				},
			],
			'wordpress_version'               => [
				'normalize' => '6.0.0-normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value ) && strlen( $value ) > 1 && strlen( $value ) < 60;
				},
			],
			'woocommerce_version'             => [
				'normalize' => '6.0.0-normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value ) && strlen( $value ) > 1 && strlen( $value ) < 60;
				},
			],
			'test_results_manager_url'        => [
				'normalize' => 'https://test-results-manager.com',
				'validate'  => static function ( $value ) {
					return filter_var( $value, FILTER_VALIDATE_URL );
				},
			],
			'test_results_manager_expiration' => [
				'normalize' => 1234567890,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				},
			],
			'runner'                          => [
				'optional'  => true,
				'normalize' => 'normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value );
				},
			],
			'workflow_id'                     => [
				'optional'  => true,
				'normalize' => '1234567890',
				'validate'  => static function ( $value ) {
					return ! empty( $value );
				},
			],
			'test_summary'                    => [
				'normalize' => static function ( $value ) use ( $file_path ) {
					if ( stripos( $file_path, 'delete_products' ) !== false ) {
						// We don't really care how it fails, we just want to make sure it fails.
						return 'Delete_Products Normalized Summary';
					}

					return $value;
				},
				'validate'  => static function ( $value ) {
					return true;
				},
			],
			'event' => [
				'normalize' => static function ( $value ) use ( $file_path ) {
					if ( in_array( $value, [ 'ci_run', 'local_run' ], true ) ) {
						return 'local_or_ci_run_normalized';
					}

					return $value;
				},
				'validate'  => static function ( $value ) {
					return ! empty( $value );
				},
			],
			'test_result_json'                => [
				'normalize' => static function ( $value ) use ( $file_path ) {
					// Encode as JSON if needed.
					$array = false;
					if ( is_array( $value ) ) {
						$array = true;
						$value = json_encode( $value );
					}

					// Normalize timestamps such as [01-Mar-2023 10:55:12 UTC] to [TIMESTAMP]
					$value = preg_replace( '/\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\]/', '[TIMESTAMP]', $value );

					// Normalize tests running on "staging-compatibility" to "compatibility".
					$value = str_replace( 'staging-compatibility', 'compatibility', $value );

					// Normalize the repo.
					$value = str_replace( 'compatibility-dashboard', 'qit-runner', $value );

					// Decode if needed.
					if ( $array ) {
						$value = json_decode( $value, true );
					}

					if ( stripos( $file_path, 'delete_products' ) !== false ) {
						// We don't really care how it fails, we just want to make sure it fails.
						return [];
					}

					return $value;
				},
				'validate'  => static function ( $value ) {
					if ( is_array( $value ) ) {
						$value = json_encode( $value );
					}

					return ! is_null( json_decode( $value ) );
				},
			],
			'test_media' => [
				'normalize' => static function ( $value ) {
					foreach ( $value as &$test_media ) {
						// Normalize the path to a filename.
						$test_media['path'] = 'normalized.' . pathinfo( $test_media['path'], PATHINFO_EXTENSION );

						// Normalize timings.
						if ( ! empty( $test_media['data']['Timings'] ) ) {
							foreach ( $test_media['data']['Timings'] as $k => $timing ) {
								$test_media['data']['Timings'][ $k ] = preg_replace( '/\d+\.\d+s/', 'NORMALIZED', $timing );
							}
						}

						// Normalize JavaScript Console Log, removing "qitenvnginx1234567890", eg:
						// Uncaught exception: "Error - Uncaught Error in custom page. - Error: Uncaught Error in custom page.
						//    at http://qitenvnginx66e994a8c7848/wp-admin/admin.php?page=plugin-a:200:223"
						if ( ! empty( $test_media['data']['JavaScript Console Log'] ) ) {
							foreach ( $test_media['data']['JavaScript Console Log'] as $k => $log ) {
								$test_media['data']['JavaScript Console Log'][ $k ] = preg_replace( '/http:\/\/qitenvnginx[0-9a-f]+/', 'http://normalized', $log );
							}
						}

						if ( ! empty( $test_media['data']['PHP Debug Log'] ) ) {
							foreach ( $test_media['data']['PHP Debug Log'] as $k => $log ) {
								// Normalize timestamps.
								$test_media['data']['PHP Debug Log'][ $k ] = preg_replace( '/\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\]/', '[TIMESTAMP]', $log );
							}
						}
					}
					return $value;
				},
				'validate' => static function ( $value ) {
					foreach ( $value as $test_media ) {
						// Parse $test_media['path'] as a filepath, and validate that the extension is either "jpg" or "webm".
						$extension = pathinfo( $test_media['path'], PATHINFO_EXTENSION );
						if ( $extension !== 'jpg' && $extension !== 'webm' ) {
							return false;
						}

						if ( ! empty( $test_media['data']['Timings'] ) ) {
							foreach ( $test_media['data']['Timings'] as $timing ) {
								// It usually looks like this:
								// Time to page load: 0.293s
								// Time to network idle: 0.669s
								// Let's validate that it ends with "s".
								if ( substr( $timing, - 1 ) !== 's' ) {
									return false;
								}
							}
						}
						if ( ! empty( $test_media['data']['JavaScript Console Log'] ) ) {
							foreach ( $test_media['data']['JavaScript Console Log'] as $log ) {
								// Validate that the log is a string.
								if ( ! is_string( $log ) ) {
									return false;
								}
							}
						}
					}

					return true;
				},
			],
			'debug_log'                       => [
				'normalize' => function ( $value ) use ( $file_path ) {
					if ( ! is_array( $value ) ) {
						return $value;
					}

					if ( stripos( $file_path, 'woo-e2e/delete_products' ) !== false || stripos( $file_path, 'woo-api/delete_products' ) !== false ) {
						return [
							[
								'count'   => '0',
								'message' => 'Debug log is ignored for woo-e2e/delete_products tests.',
							],
						];
					}

					$normalize_custom_tests_debug_log = function ( $debug_log ) {
						$normalized = [];
						foreach ( $debug_log as $fatal_or_not => $logs ) {
							/**
							 * Example structure:
							 * array (
							 * 'message' => 'This is test notice!',
							 * 'type' => 'notice',
							 * 'file_line' => 'wp-content/mu-plugins/qit-mu-woocommerce.php:105',
							 * 'traces' =>
							 * array (
							 * ),
							 * 'count' => 98,
							 * )
							 */
							foreach ( $logs as $hash => $log ) {
								$message = $log['message'];

								$normalized[] = [
									'message'   => $message,
									'type'      => $log['type'],
									'file_line' => $log['file_line'],
									'count'     => $this->normalize_count( $log['count'] ),
								];
							}
						}

						// Sort the normalized array for consistent ordering
						usort($normalized, function($a, $b) {
							return strcmp($a['message'], $b['message'])
								?: strcmp($a['type'], $b['type'])
									?: strcmp($a['file_line'], $b['file_line']);
						});

						return $normalized;
					};

					$normalize_debug_log = function ( $value ) use ( $file_path ) {
						if ( empty( $value ) ) {
							return [];
						}

						/*
						 * $debug_log is an array with the following structure:
						 *
						 * [
						 *   'count' => <int>,
						 *   'message' => <string>,
						 * ]
						 */
						foreach ( $value as $k => $debug_log ) {
							// Normalize timestamps such as [01-Mar-2023 10:55:12 UTC] to [TIMESTAMP]
							$debug_log['message'] = preg_replace( '/\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\]/', '[TIMESTAMP]', $debug_log['message'] );

							// Normalize tests running on staging-compatibility to compatibility.
							$debug_log['message'] = str_replace( 'staging-compatibility', 'compatibility', $debug_log['message'] );

							$debug_log['message'] = str_replace( 'compatibility-dashboard', 'qit-runner', $debug_log['message'] );

							// Sometimes the test site might fail to contact WP.org, this is beyond our control.
							if ( stripos( $debug_log['message'], 'Something may be wrong with WordPress.org' ) !== false ) {
								// If it happens only a few times, ignore it.
								if ( $debug_log['count'] <= 3 ) {
									echo "Removing 'Something may be wrong with WordPress.org' from debug_log.message\n";
									unset( $value[ $k ] );
									continue;
								}
							}

							// There seems to be a bug on WP 6.5 RC releases around PHP statcache.
							// @see https://wordpress.slack.com/archives/C02RQBWTW/p1709330758080609
							if ( stripos( $debug_log['message'], 'No such file or directory in /var/www/html/wp-admin/includes/class-wp-filesystem-direct.php on line 636' ) !== false ) {
								echo "Removing '{$debug_log['message']}' from debug_log.message\n";
								unset( $value[ $k ] );
								continue;
							}

							// Ignore errors containing "WP_Block_Patterns_Registry::register was called incorrectly.", this can show up erratically on WP 6.5+.
							if ( stripos( $debug_log['message'], 'WP_Block_Patterns_Registry::register was called incorrectly.' ) !== false ) {
								echo "Removing 'WP_Block_Patterns_Registry::register was called incorrectly.' from debug_log.message\n";
								unset( $value[ $k ] );
								continue;
							}

							// Ignore containing "Maximum execution time of 30 seconds exceeded in" in E2E.
							if ( stripos( $file_path, 'woo-e2e/' ) !== false && stripos( $debug_log['message'], 'Maximum execution time of 30 seconds exceeded in' ) !== false ) {
								echo "Removing 'Maximum execution time of 30 seconds exceeded in' from debug_log.message\n";
								unset( $value[ $k ] );
								continue;
							}

							$debug_log['count'] = $this->normalize_count( $debug_log['count'] );

							// todo: regenerate snapshots and remove strval later.
							$normalized_debug_log[] = array_map( 'strval', $debug_log );
						}

						// Sort alphabetically by message.
						usort( $normalized_debug_log, function ( $a, $b ) {
							return strcmp( $a['message'], $b['message'] );
						} );

						return $normalized_debug_log;
					};

					$logs = [];

					if ( array_key_exists( 'qm_logs', $value ) ) {
						$logs['qm_logs'] = $normalize_custom_tests_debug_log( $value['qm_logs'] );
					} else {
						$logs['generic'] = $normalize_debug_log( $value );
					}

					if ( array_key_exists( 'debug_log', $value ) ) {
						if ( is_string( $value['debug_log'] ) ) {
							$value['debug_log'] = json_decode( $value['debug_log'], true );
						}
						$logs['debug_log'] = $normalize_debug_log( $value['debug_log'] );
					}

					return $logs;
				},
				'validate'  => static function ( $value ) {
					if ( empty( $value ) ) {
						return true;
					}

					if ( is_array( $value ) ) {
						$value = json_encode( $value );
					}

					return ! is_null( json_decode( $value ) );
				},
			],
			'test_result_aws_expiration'      => [
				'normalize' => 1234567890,
				'validate'  => static function ( $value ) {
					return empty( $value ) || preg_match( '/^\d+$/', $value );
				},
			],
			'test_result_aws_url'             => [
				'normalize' => 'https://test-results-aws.com',
				'validate'  => static function ( $value ) {
					return empty( $value ) || filter_var( $value, FILTER_VALIDATE_URL );
				},
			],
		];

		if ( ! is_null( $callback ) ) {
			$rules = $callback( $rules );
		}

		foreach ( $json as &$j ) {
			foreach ( $j as $k => &$v ) {
				// Check if the current key is in the processing rules.
				if ( array_key_exists( $k, $rules ) ) {
					// Validate the existing value.
					if ( $rules[ $k ]['validate']( $v ) ) {
						// Normalize for snapshot testing.
						if ( is_callable( $rules[ $k ]['normalize'] ) ) {
							$v = $rules[ $k ]['normalize']( $v );
						} else {
							$v = $rules[ $k ]['normalize'];
						}
					} else {
						if ( isset( $rules[ $k ]['optional'] ) && $rules[ $k ]['optional'] ) {
							// Some things are fine to fail, we just normalize if needed.
							continue;
						}
						$this->fail( 'Invalid value for key: ' . $k );
					}
				}
			}
		}

		$json = json_encode( $json, JSON_PRETTY_PRINT );

		return test_result_parser( $json );
	}
	
	/*
	 * Normalize PHP debug logs captured during test runs.
	 *
	 * The normalization process is focused on the 'count' key within the debug logs.
	 * This allows for some flexibility in test runs where slight variations in log counts
	 * might occur due to uncontrollable conditions like AJAX requests firing or not firing.
	 *
	 * Normalization rules for 'count':
	 * - Exact values are retained for counts below 50.
	 * - Counts between 50 and 100 are rounded to the nearest 5.
	 * - Counts between 100 and 200 are rounded to the nearest 10.
	 * - Counts above 200 are rounded to the nearest 25.
	 * - Counts above 1000 are rounded to the nearest 100.
	 * - Counts above 10000 are rounded to the nearest 1000.
	 *
	 * Additionally, certain known failure messages (e.g., WordPress.org connectivity issues)
	 * are conditionally removed from the logs.
	 */
	protected function normalize_count( int $count ): int {
		if ( $count < 25 ) {
			// No-op. Exact match for counts below 50.
		} elseif ( $count < 100 ) {
			if ( $count % 5 === 0 ) {
				echo "Skipping normalization as it's already divisible by 5\n";
			} else {
				echo "Normalizing debug_log.count from {$count} to ";
				$count = round( $count / 5 ) * 5;  // Round to the closest 5.
				echo "{$count}\n";
			}
		} elseif ( $count < 200 ) {
			if ( $count % 10 === 0 ) {
				echo "Skipping normalization as it's already divisible by 10\n";
			} else {
				echo "Normalizing debug_log.count from {$count} to ";
				$count = round( $count / 10 ) * 10;  // Round to the closest 10.
				echo "{$count}\n";
			}
		} elseif ( $count < 1000 ) {
			if ( $count % 50 === 0 ) {
				echo "Skipping normalization as it's already divisible by 50\n";
			} else {
				echo "Normalizing debug_log.count from {$count} to ";
				$count = round( $count / 50 ) * 50;  // Round to the closest 50.
				echo "{$count}\n";
			}
		} elseif ( $count < 10000 ) {
			if ( $count % 100 === 0 ) {
				echo "Skipping normalization as it's already divisible by 100\n";
			} else {
				echo "Normalizing debug_log.count from {$count} to ";
				$count = round( $count / 100 ) * 100;  // Round to the closest 100.
				echo "{$count}\n";
			}
		} else {
			if ( $count % 1000 === 0 ) {
				echo "Skipping normalization as it's already divisible by 1000\n";
			} else {
				echo "Normalizing debug_log.count from {$count} to ";
				$count = round( $count / 1000 ) * 1000;  // Round to the closest 1000.
				echo "{$count}\n";
			}
		}

		return $count;
	}
}