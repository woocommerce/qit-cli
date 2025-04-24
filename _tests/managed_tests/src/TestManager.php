<?php

class TestManager {
	private $logger;
	private $tests_based_on_custom_tests;
	private $one_of_each;

	public function __construct( Logger $logger, array $tests_based_on_custom_tests, bool $one_of_each ) {
		$this->logger                      = $logger;
		$this->tests_based_on_custom_tests = $tests_based_on_custom_tests;
		$this->one_of_each                 = $one_of_each;
	}

	public function get_test_types(): array {
		$test_types = [];
		$ignore     = [ 'vendor', 'tests' ];

		$it = new DirectoryIterator( dirname( __DIR__ ) );
		foreach ( $it as $file ) {
			if ( $file->isDir() && ! $file->isDot() && ! in_array( $file->getBaseName(), $ignore, true ) ) {
				$test_types[] = $file->getPathname();
			}
		}

		return $test_types;
	}

	private function get_tests_in_test_type( string $path ) {
		$tests = [];
		$it    = new DirectoryIterator( $path );
		foreach ( $it as $file ) {
			if ( $file->isDir() && ! $file->isDot() ) {
				if ( stripos( $file->getBasename(), '-' ) !== false ) {
					throw new \UnexpectedValueException(
						sprintf( 'Please rename the test "%s" to "%s"',
							$file->getBasename(),
							str_replace( '-', '_', $file->getBasename() ) )
					);
				}
				$tests[] = $file->getPathname();
			}
		}

		return $tests;
	}

	public function filter_test_types( array $test_types ): array {
		if ( ! is_null( Context::$test_types ) ) {
			$test_types = array_filter( $test_types, function ( $test_type_path ) {
				return in_array( basename( $test_type_path ), Context::$test_types, true );
			} );
			$this->logger->log( "Filtered test types based on request: " . implode( ',', array_map( 'basename', $test_types ) ) );
		} else {
			// Exclude custom test types if none specified
			$test_types = array_filter( $test_types, function ( $test_type_path ) {
				return ! in_array( basename( $test_type_path ), $this->tests_based_on_custom_tests, true );
			} );

			if ( count( $test_types ) !== count( $this->get_test_types() ) ) {
				$removed = array_diff( $this->get_test_types(), $test_types );
				$this->logger->log( "Skipping tests based on custom tests: " . implode( ',', array_map( 'basename', $removed ) ) );
				maybe_echo( sprintf( "Skipping tests based on custom tests, which must run in a dedicated process: \n - %s",
						implode( "\n - ", array_map( 'basename', $removed ) ) ) . "\n" );
			}
		}

		if ( getenv( 'QIT_SKIP_E2E' ) === 'yes' ) {
			$before_count = count( $test_types );
			$test_types   = array_filter( $test_types, function ( $test_type_path ) {
				return basename( $test_type_path ) !== 'woo-e2e';
			} );
			$after_count  = count( $test_types );
			if ( $before_count != $after_count ) {
				$this->logger->log( "QIT_SKIP_E2E=yes, removing woo-e2e tests" );
			}
		}

		if ( empty( $test_types ) ) {
			$this->logger->log( "No test types found, exiting" );
			throw new Exception( 'No test types found.' );
		}

		$this->logger->log( "Final test types to run: " . implode( ',', array_map( 'basename', $test_types ) ) );

		return $test_types;
	}

	public function generate_test_runs( array $test_types ): array {
		$tests_to_run = [];

		foreach ( $test_types as $test_type ) {
			$tests_to_run[ basename( $test_type ) ] = [];

			$scenarios = $this->get_tests_in_test_type( $test_type );

			// If --one-of-each is set, pick exactly one scenario (prefer no_op)
			if ( $this->one_of_each && ! empty( $scenarios ) ) {
				$no_op_path = null;
				foreach ( $scenarios as $scenario_path ) {
					if ( basename( $scenario_path ) === 'no_op' ) {
						$no_op_path = $scenario_path;
						break;
					}
				}
				if ( $no_op_path ) {
					$scenarios = [ $no_op_path ];
				} else {
					// pick the first scenario if no_op doesn’t exist
					$scenarios = [ reset( $scenarios ) ];
				}
			}
			
			foreach ( $scenarios as $test ) {
				// Scenario filtering
				if ( ! is_null( Context::$scenarios ) ) {
					if ( ! in_array( basename( $test ), Context::$scenarios ) ) {
						$this->logger->log( "Skipping " . basename( $test ) . " not in scenarios" );
						maybe_echo( sprintf( "Skipping %s, running only %s\n", basename( $test ), implode( ',', Context::$scenarios ) ) );
						continue;
					}
				}

				$env          = require $test . '/env.php';
				$wp_versions  = isset( $env['wp'] ) ? explode( ',', $env['wp'] ) : [ '' ];
				$woo_versions = isset( $env['woo'] ) ? explode( ',', $env['woo'] ) : [ '' ];
				$php_versions = isset( $env['php'] ) ? explode( ',', $env['php'] ) : [ '' ];

				foreach ( $wp_versions as $wp_version ) {
					foreach ( $woo_versions as $woo_version ) {
						foreach ( $php_versions as $php_version ) {
							$sut_slug = file_exists( $test . '/' . Context::$extension_slug )
								? Context::$extension_slug
								: Context::$theme_slug;

							if ( ! $this->env_filters_match( $env, $wp_version, $woo_version, $php_version ) ) {
								$this->logger->log( "Skipping " . basename( $test ) . " does not match env filters" );
								maybe_echo( sprintf( "Skipping %s, does not match env filters\n", basename( $test ) ) );
								continue;
							}

							$tests_to_run[ basename( $test_type ) ][] = [
								'type'                 => basename( $test_type ),
								'slug'                 => basename( $test ),
								'php'                  => $php_version,
								'wp'                   => $wp_version,
								'woo'                  => $woo_version,
								'features'             => $env['features'] ?? '',
								'remove_from_snapshot' => $env['remove_from_snapshot'] ?? '',
								'params'               => $env['params'] ?? [],
								'path'                 => $test,
								'sut_slug'             => $sut_slug,
							];
						}
					}
				}
			}
		}

		return $tests_to_run;
	}

	private function env_filters_match( $env, $wp_version, $woo_version, $php_version ) {
		if ( empty( Context::$env_filters ) ) {
			return true;
		}

		foreach ( Context::$env_filters as $key => $value ) {
			if ( ! isset( $env[ $key ] ) ) {
				return false;
			}

			switch ( $key ) {
				case 'wp':
					if ( $value !== $wp_version ) {
						return false;
					}
					break;
				case 'woo':
					if ( $value !== $woo_version ) {
						return false;
					}
					break;
				case 'php':
					if ( $value !== $php_version ) {
						return false;
					}
					break;
				default:
					if ( $value !== $env[ $key ] ) {
						return false;
					}
			}
		}

		return true;
	}
}