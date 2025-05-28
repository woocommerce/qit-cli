<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use Symfony\Component\Console\Application;

class TestParser extends AbstractConfigParser {
	private Application $console_application;

	public function __construct( Application $console_application ) {
		$this->console_application = $console_application;
	}

	public function parse( $value, array $custom_test_packages = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Test types must be an array.' );
		}

		foreach ( $value as $test_type => $profiles ) {
			if ( ! is_string( $test_type ) ) {
				throw new \RuntimeException( 'Test type must be a string.' );
			}
			if ( ! is_array( $profiles ) ) {
				throw new \RuntimeException( "Profiles for test type '$test_type' must be an array." );
			}
			$valid_options   = $this->get_valid_options_for_test_type( $test_type );
			$valid_options[] = 'pre_test_build';
			$valid_options[] = 'run';
			$valid_options[] = 'environment';
			$valid_options[] = 'extends';
			$valid_options[] = 'tweaks';
			$valid_options[] = 'php_version';
			foreach ( $profiles as $profile => $config ) {
				if ( ! is_string( $profile ) ) {
					throw new \RuntimeException( "Profile for test type '$test_type' must be a string." );
				}
				if ( ! is_array( $config ) ) {
					throw new \RuntimeException( "Configuration for '$test_type:$profile' must be an array." );
				}

				foreach ( $config as $config_key => $config_value ) {
					if ( ! in_array( $config_key, $valid_options ) ) {
						throw new \RuntimeException( "Invalid key '$config_key' in profile '$test_type:$profile'. Must be one of: " . implode( ', ', $valid_options ) );
					}
					if ( $config_key === 'environment' && ! is_string( $config_value ) ) {
						throw new \RuntimeException( "Environment in '$test_type:$profile' must be a string." );
					}
					if ( $config_key === 'extends' && ! is_string( $config_value ) ) {
						throw new \RuntimeException( "Extends in '$test_type:$profile' must be a string." );
					}
					if ( $config_key === 'php_version' && ( ! is_string( $config_value ) || ! preg_match( '/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', $config_value ) ) ) {
						throw new \RuntimeException( "Invalid php_version in '$test_type:$profile'. Must be a valid PHP version string (e.g., '8.4')." );
					}
					if ( $config_key === 'pre_test_build' ) {
						if ( ! is_array( $config_value ) || ! isset( $config_value['command'] ) || ! is_string( $config_value['command'] ) || ! isset( $config_value['output'] ) || ! is_string( $config_value['output'] ) ) {
							throw new \RuntimeException( "Invalid pre_test_build in '$test_type:$profile'. Must be an array with 'command' and 'output' keys, both containing strings." );
						}
					}
					if ( $config_key === 'run' ) {
						if ( ! is_array( $config_value ) || ! isset( $config_value['test_packages'] ) || ! is_array( $config_value['test_packages'] ) ) {
							throw new \RuntimeException( "run in '$test_type:$profile' must be an array with a 'test_packages' array." );
						}
						foreach ( $config_value['test_packages'] as $index => $test_package ) {
							if ( ! is_string( $test_package ) ) {
								throw new \RuntimeException( "Test package at index $index in '$test_type:$profile' must be a string." );
							}
							// Validate test_package format (e.g., "local/default", "woocommerce/default:latest")
							$parts = explode( '/', $test_package, 2 );
							if ( count( $parts ) === 2 ) {
								[ $package_source, $package_name ] = $parts;
								if ( $package_source !== 'local' && ! isset( $custom_test_packages[ $package_source ][ $package_name ] ) ) {
									throw new \RuntimeException( "Test package '$test_package' in '$test_type:$profile' not found in test_packages." );
								}
							}
						}
					}
					if ( $config_key === 'tweaks' ) {
						if ( ! is_array( $config_value ) ) {
							throw new \RuntimeException( "tweaks in '$test_type:$profile' must be an array." );
						}
						if ( isset( $config_value['skip'] ) ) {
							if ( ! is_array( $config_value['skip'] ) ) {
								throw new \RuntimeException( "tweaks.skip in '$test_type:$profile' must be an array." );
							}
							foreach ( $config_value['skip'] as $index => $skip_item ) {
								if ( ! is_string( $skip_item ) ) {
									throw new \RuntimeException( "Skip item at index $index in tweaks for '$test_type:$profile' must be a string." );
								}
							}
						}
					}
				}

				if ( $test_type === 'e2e' && ! isset( $config['run']['test_packages'] ) ) {
					throw new \RuntimeException( "run.test_packages must be set for '$test_type:$profile'." );
				}
			}
		}

		return $this->resolve_extends( $value, 'test profile' );
	}

	private function get_valid_options_for_test_type( string $test_type ): array {
		try {
			$command    = $this->console_application->find( "run:$test_type" );
			$definition = $command->getDefinition();
			$options    = [];
			foreach ( $definition->getOptions() as $option ) {
				$options[] = $option->getName();
			}

			return $options;
		} catch ( \InvalidArgumentException $e ) {
			throw new \RuntimeException( "No command found for test type '$test_type'. Expected a 'run:$test_type' command." );
		}
	}
}