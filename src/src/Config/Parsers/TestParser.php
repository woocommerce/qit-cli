<?php

namespace QIT_CLI\Config\Parsers;

use Symfony\Component\Console\Application;

class TestParser extends AbstractConfigParser {
	private Application $console_application;

	public function __construct( Application $console_application ) {
		$this->console_application = $console_application;
	}

	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Tests must be an array.' );
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
			$valid_options[] = 'compatibility_tests';
			$valid_options[] = 'env';
			$valid_options[] = 'extends';
			$valid_options[] = 'test_package';
			foreach ( $profiles as $profile => $config ) {
				if ( ! is_string( $profile ) ) {
					throw new \RuntimeException( "Profile for test type '$test_type' must be a string." );
				}
				if ( ! is_array( $config ) ) {
					throw new \RuntimeException( "Configuration for '$test_type:$profile' must be an array." );
				}

				if ( $test_type === 'e2e' ) {
					if ( isset( $config['test_package'] ) && ! isset( $context['custom_test_packages'][ $config['test_package'] ] ) ) {
						throw new \RuntimeException( "Test package '{$config['test_package']}' not found in custom_test_packages." );
					}
					if ( ! isset( $config['test_package'] ) && ( ! isset( $config['compatibility_tests'] ) || empty( $config['compatibility_tests'] ) ) ) {
						throw new \RuntimeException( "Either 'test_package' or 'compatibility_tests' must be set for '$test_type:$profile'." );
					}

					if ( isset( $config['compatibility_tests'] ) && ! is_array( $config['compatibility_tests'] ) ) {
						throw new \RuntimeException( "compatibility_tests in '$test_type:$profile' must be an array." );
					}
					if ( isset( $config['compatibility_tests'] ) ) {
						foreach ( $config['compatibility_tests'] as $key => $compat_test ) {
							if ( ! is_int( $key ) || ! is_string( $compat_test ) ) {
								throw new \RuntimeException( "Compatibility test in '$test_type:$profile' must be a string." );
							}
						}
					}
				}

				foreach ( $config as $config_key => $config_value ) {
					if ( $config_key !== 'settings' && ! in_array( $config_key, $valid_options ) ) {
						throw new \RuntimeException( "Invalid key '$config_key' in profile '$test_type:$profile'. Must be one of: " . implode( ', ', $valid_options ) . ", or settings" );
					}
					if ( $config_key === 'env' && ! is_string( $config_value ) ) {
						throw new \RuntimeException( "Env in '$test_type:$profile' must be a string." );
					}
					if ( $config_key === 'extends' && ! is_string( $config_value ) ) {
						throw new \RuntimeException( "Extends in '$test_type:$profile' must be a string." );
					}
					if ( $config_key === 'pre_test_build' ) {
						if ( ! is_array( $config_value ) || ! isset( $config_value['command'] ) || ! is_string( $config_value['command'] ) || ! isset( $config_value['output'] ) || ! is_string( $config_value['output'] ) ) {
							throw new \RuntimeException( "Invalid pre_test_build in '$test_type:$profile'. Must be an array with 'command' and 'output' keys, both containing strings." );
						}
					}
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

	public function get( string $test_type, string $profile ): array {
		if ( ! isset( $this->parsed_tests[ $test_type ][ $profile ] ) ) {
			throw new \RuntimeException( "Test configuration '$test_type:$profile' not found." );
		}

		return $this->parsed_tests[ $test_type ][ $profile ];
	}
}