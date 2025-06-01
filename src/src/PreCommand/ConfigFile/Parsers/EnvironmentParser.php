<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI\App;

class EnvironmentParser extends AbstractConfigParser {
	protected ExtensionParser $extension_parser;

	public function __construct( ExtensionParser $extension_parser ) {
		$this->extension_parser = $extension_parser;
	}

	public function parse( $value, array $context = [], ?array $sut_config = null ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Environments must be an array.' );
		}

		$environments  = [];
		$test_packages = $context['test_packages'] ?? [];

		foreach ( $value as $env_name => $config ) {
			if ( ! is_string( $env_name ) ) {
				throw new \RuntimeException( "Environment name must be a string in environments configuration." );
			}
			if ( ! is_array( $config ) ) {
				throw new \RuntimeException( "Configuration for environment '$env_name' must be an array." );
			}

			$parsed_env = [];

			foreach ( $config as $env_key => $env_value ) {
				switch ( $env_key ) {
					case 'extends':
						if ( ! is_string( $env_value ) ) {
							throw new \RuntimeException( "'extends' in environment '$env_name' must be a string." );
						}
						break;
					case 'php_version':
						if ( ! is_string( $env_value ) || ! preg_match( '/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', $env_value ) ) {
							throw new \RuntimeException( "Invalid php_version in environment '$env_name'. Must be a valid PHP version string (e.g., '8.2')." );
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					case 'wp_version':
					case 'woo_version':
						if ( ! is_string( $env_value ) ) {
							throw new \RuntimeException( "'$env_key' in environment '$env_name' must be a string." );
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					case 'object_cache':
						if ( ! is_bool( $env_value ) ) {
							throw new \RuntimeException( "object_cache in environment '$env_name' must be a boolean." );
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					case 'env_vars':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "env_vars in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $var_name => $var_value ) {
							if ( ! is_string( $var_name ) ) {
								throw new \RuntimeException( "Environment variable name in '$env_name' must be a string." );
							}
							if ( ! is_string( $var_value ) ) {
								throw new \RuntimeException( "Environment variable value for '$var_name' in '$env_name' must be a string." );
							}
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					case 'plugins':
					case 'themes':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "'$env_key' in environment '$env_name' must be an array." );
						}
						$parsed_env[ $env_key ] = $this->extension_parser->parse( $env_value, $env_key, $context, $sut_config, $env_name );
						break;
					case 'setup':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "setup in environment '$env_name' must be an array." );
						}
						if ( isset( $env_value['test_packages'] ) ) {
							if ( ! is_array( $env_value['test_packages'] ) ) {
								throw new \RuntimeException( "test_packages in setup for environment '$env_name' must be an array." );
							}
							foreach ( $env_value['test_packages'] as $index => $test_package ) {
								if ( ! is_string( $test_package ) ) {
									throw new \RuntimeException( "Test package at index $index in setup for environment '$env_name' must be a string." );
								}
								$parts = explode( ':', $test_package, 2 );
								if ( count( $parts ) !== 2 ) {
									throw new \RuntimeException( "Invalid test package format '$test_package' at index $index in setup for environment '$env_name'. Expected 'source:name@version'." );
								}
								[ $source, $name_version ] = $parts;
								$name_parts = explode( '@', $name_version, 2 );
								if ( count( $name_parts ) !== 2 ) {
									throw new \RuntimeException( "Invalid test package name/version '$name_version' at index $index in setup for environment '$env_name'. Expected 'name@version'." );
								}
								[ $name ] = $name_parts;
								$found = false;
								foreach ( $test_packages as $pkg ) {
									if ( $pkg['type'] === $source && $pkg['name'] === $name ) {
										$found = true;
										break;
									}
								}
								if ( ! $found ) {
									throw new \RuntimeException( "Test package '$test_package' at index $index in setup for environment '$env_name' not found in test_packages configuration." );
								}
							}
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					case 'volumes':
						if ( ! is_array( $env_value ) ) {
							throw new \RuntimeException( "volumes in environment '$env_name' must be an array." );
						}
						foreach ( $env_value as $volume ) {
							if ( ! is_string( $volume ) ) {
								throw new \RuntimeException( "Volume in environment '$env_name' must be a string." );
							}
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					case 'extension_set':
						if ( ! is_string( $env_value ) ) {
							throw new \RuntimeException( "extension_set in environment '$env_name' must be a string." );
						}
						$parsed_env[ $env_key ] = $env_value;
						break;
					default:
						throw new \RuntimeException( "Unknown key '$env_key' in environment '$env_name' configuration." );
				}
			}

			$environments[ $env_name ] = $parsed_env;
		}

		return $this->resolve_extends( $environments, 'environment' );
	}
}