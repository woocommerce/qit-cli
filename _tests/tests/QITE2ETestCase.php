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
			'run_id'                          => [
				'normalize' => 123456,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				}
			],
			'wordpress_version'               => [
				'normalize' => '6.0.0-normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value ) && strlen( $value ) > 1 && strlen( $value ) < 60;
				}
			],
			'woocommerce_version'             => [
				'normalize' => '6.0.0-normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value ) && strlen( $value ) > 1 && strlen( $value ) < 60;
				}
			],
			'test_results_manager_url'        => [
				'normalize' => 'https://test-results-manager.com',
				'validate'  => static function ( $value ) {
					return filter_var( $value, FILTER_VALIDATE_URL );
				}
			],
			'test_results_manager_expiration' => [
				'normalize' => 1234567890,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				}
			],
			'test_result_json' => [
				'normalize' => static function( $value ) {
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

					// Decode if needed.
					if ( $array ) {
						$value = json_decode( $value, true );
					}

					return $value;
				},
				'validate' => static function( $value ) {
					if ( is_array( $value ) ) {
						$value = json_encode( $value );
					}

					return ! is_null( json_decode( $value ) );
				}
			],
			'debug_log' => [
				'normalize' => static function ( $value ) use ( $file_path ) {
					if ( ! is_array( $value ) ) {
						return $value;
					}

					$normalized_debug_log = [];

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

						if ( stripos( $file_path, 'api/delete_products' ) !== false ) {
							$pos = stripos( $debug_log['message'], 'Stack Trace:' );

							if ( $pos !== false ) {
								// Remove stack trace from debug log.
								$debug_log['message'] = substr( $debug_log['message'], 0, $pos );

								// If the resulting entry is empty, skip it.
								if ( empty( trim( $debug_log['message'] ) ) ) {
									continue;
								}
							}
						}

						// Sometimes the test site might fail to contact WP.org, this is beyond our control.
						if ( stripos( $debug_log['message'], 'Something may be wrong with WordPress.org' ) !== false ) {
							// If it happens only a few times, ignore it.
							if ( $debug_log['count'] <= 3 ) {
								echo "Removing 'Something may be wrong with WordPress.org' from debug_log.message\n";
								unset( $value[ $k ] );
								continue;
							}
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
						 * - Counts above 100 are rounded to the nearest 10.
						 *
						 * Additionally, certain known failure messages (e.g., WordPress.org connectivity issues)
						 * are conditionally removed from the logs.
						 */
						if ( $debug_log['count'] < 50 ) {
							// No-op. Exact match for counts below 50.
						} elseif ( $debug_log['count'] < 100 ) {
							if ( $debug_log['count'] % 5 === 0 ) {
								echo "Skipping normalization as it's already divisible by 5\n";
							} else {
								echo "Normalizing debug_log.count from {$debug_log['count']} to ";
								$debug_log['count'] = round( $debug_log['count'] / 5 ) * 5;  // Round to the closest 5 if not already divisible by 5.
								echo "{$debug_log['count']}\n";
							}
						} else {
							if ( $debug_log['count'] % 10 === 0 ) {
								echo "Skipping normalization as it's already divisible by 10\n";
							} else {
								echo "Normalizing debug_log.count from {$debug_log['count']} to ";
								$debug_log['count'] = round( $debug_log['count'] / 10 ) * 10;  // Round to the closest 10 if not already divisible by 10.
								echo "{$debug_log['count']}\n";
							}
						}

						// todo: regenerate snapshots and remove strval later.
						$normalized_debug_log[] = array_map( 'strval', $debug_log );
					}

					// Sort alphabetically by message.
					usort( $normalized_debug_log, function ( $a, $b ) {
						return strcmp( $a['message'], $b['message'] );
					} );

					return $normalized_debug_log;
				},
				'validate' => static function( $value ) {
					if ( empty( $value ) ) {
						return true;
					}

					if ( is_array( $value ) ) {
						$value = json_encode( $value );
					}

					return ! is_null( json_decode( $value ) );
				}
			],
			'test_result_aws_expiration' => [
				'normalize' => 1234567890,
				'validate'  => static function ( $value ) {
					return empty( $value ) || preg_match( '/^\d+$/', $value );
				}
			],
			'test_result_aws_url' => [
				'normalize' => 'https://test-results-aws.com',
				'validate'  => static function ( $value ) {
					return empty( $value ) || filter_var( $value, FILTER_VALIDATE_URL );
				}
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
						$this->fail( 'Invalid value for key: ' . $k );
					}
				}
			}
		}

		$json = json_encode( $json, JSON_PRETTY_PRINT );

		return test_result_parser( $json );
	}
}