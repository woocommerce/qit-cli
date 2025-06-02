<?php

namespace QIT_CLI\PreCommand\Parsers;

use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Application;

class ConfigParser {
	public array $parsed_config = [];
	protected string $config_file;
	protected string $root_path;
	protected Application $console_application;

	public function __construct( string $config_file, Application $console_application ) {
		$this->config_file         = $config_file;
		$this->console_application = $console_application;
		$this->root_path           = dirname( $config_file );
		$this->parsed_config       = $this->parse_config( $config_file, [], true );
	}

	protected function parse_config( string $config_file, array $parsed_files, bool $is_top_level = false ): array {
		if ( in_array( $config_file, $parsed_files, true ) ) {
			throw new \RuntimeException( "Circular dependency detected in qit.json configuration: $config_file" );
		}

		$parsed_files[] = $config_file;

		if ( ! file_exists( $config_file ) ) {
			throw new \RuntimeException( "Config file '$config_file' not found." );
		}

		$contents   = file_get_contents( $config_file );
		$raw_config = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $raw_config ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		$root_path     = dirname( $config_file );
		$raw_config    = $this->resolve_paths( $raw_config, $root_path );
		$parsed_config = [];

		if ( isset( $raw_config['sut'] ) ) {
			$parsed_config['sut'] = $this->parse_sut( $raw_config['sut'], [
				'root_path' => $root_path,
				'context'   => 'sut.source',
			] );
		}

		if ( isset( $parsed_config['sut'] ) && isset( $raw_config['environments'] ) ) {
			$this->validate_sut_consistency( $parsed_config['sut'], $raw_config['environments'] );
		}

		foreach ( $raw_config as $key => $value ) {
			if ( $key === 'extends' || $key === 'sut' ) {
				continue;
			}
			switch ( $key ) {
				case '$schema':
					$parsed_config[ $key ] = $this->parse_simple_value( $value, $key );
					break;
				case 'test_types':
					$parsed_config[ $key ] = $this->parse_test_types( $value, $raw_config['test_packages'] ?? [] );
					break;
				case 'test_groups':
					$parsed_config[ $key ] = $this->parse_test_groups( $value, [ 'test_types' => $parsed_config['test_types'] ?? [] ] );
					break;
				case 'environments':
					$parsed_config[ $key ] = $this->parse_environments( $value, [
						'test_packages' => $raw_config['test_packages'] ?? [],
						'root_path'     => $root_path,
					], $parsed_config['sut'] ?? null );
					break;
				case 'test_packages':
					$parsed_config[ $key ] = $this->parse_test_packages( $value, [ 'root_path' => $root_path ] );
					break;
				default:
					throw new \RuntimeException( "Unknown configuration $key in qit.json." );
			}
		}

		if ( isset( $raw_config['extends'] ) ) {
			$base_file     = $this->resolve_extends_path( $raw_config['extends'], $config_file );
			$base_config   = $this->parse_config( $base_file, $parsed_files, false );
			$parsed_config = $this->merge_configs( $base_config, $parsed_config, $raw_config );
		}

		if ( $is_top_level && ! isset( $parsed_config['sut'] ) ) {
			throw new \RuntimeException( 'SUT configuration is required.' );
		}

		return $parsed_config;
	}

	protected function parse_simple_value( $value, string $key ) {
		if ( ! is_scalar( $value ) ) {
			throw new \RuntimeException( "'$key' in qit.json must be a scalar." );
		}

		return $value;
	}

	protected function parse_sut( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', 'SutParser: Parsing SUT config: ' . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT must be an array\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT configuration must be an array.' );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT missing type\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT must contain a "type" key with a string value.' );
		}

		$valid_types = [ 'plugin', 'theme' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Invalid SUT type: {$value['type']}\n", FILE_APPEND );
			throw new \RuntimeException( "Invalid SUT type '{$value['type']}'. Must be one of: " . implode( ', ', $valid_types ) );
		}

		if ( ! isset( $value['slug'] ) || ! is_string( $value['slug'] ) || empty( $value['slug'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT missing or empty slug\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT must contain a non-empty "slug" string.' );
		}

		if ( ! isset( $value['source'] ) || ! is_array( $value['source'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT '{$value['slug']}' missing source\n", FILE_APPEND );
			throw new \RuntimeException( "SUT '{$value['slug']}' must specify a 'source' object." );
		}

		$value['source'] = $this->parse_source( $value['source'], [
			'slug'    => $value['slug'],
			'context' => 'sut.source',
		] );

		file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT parsing completed\n", FILE_APPEND );

		return $value;
	}

	protected function parse_source( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', 'SourceParser: Parsing source config: ' . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( "Source config must be an array for {$context['context']}." );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			throw new \RuntimeException( "Source must contain a 'type' key with a string value for {$context['context']}." );
		}

		$valid_types = [ 'build', 'directory', 'url', 'zip', 'wccom', 'wporg' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			throw new \RuntimeException( "Invalid source type '{$value['type']}' for {$context['context']}. Must be one of: " . implode( ', ', $valid_types ) );
		}

		$context_name = $context['context'] ?? 'unknown';

		switch ( $value['type'] ) {
			case 'build':
				if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) || empty( $value['command'] ) ) {
					throw new \RuntimeException( 'Build source must contain a non-empty "command" string' );
				}
				if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) || empty( $value['output'] ) ) {
					throw new \RuntimeException( "Build source must contain a non-empty 'output' string for {$context_name}." );
				}
				if ( ! preg_match( '/\.zip$/', $value['output'] ) ) {
					throw new \RuntimeException( "Build source output must be a .zip file for {$context_name}." );
				}
				break;
			case 'directory':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					throw new \RuntimeException( "Directory source must contain a non-empty 'path' string for {$context_name}." );
				}
				if ( ! is_dir( $value['path'] ) ) {
					throw new \RuntimeException( "Directory does not exist: {$value['path']}" );
				}
				break;
			case 'url':
				if ( ! isset( $value['url'] ) || ! is_string( $value['url'] ) || empty( $value['url'] ) ) {
					throw new \RuntimeException( "URL source must contain a non-empty 'url' string for {$context_name}." );
				}
				if ( ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $value['url'] ) ) {
					throw new \RuntimeException( "URL source must be a valid HTTPS URL ending in .zip for {$context_name}." );
				}
				break;
			case 'zip':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					throw new \RuntimeException( "Zip source must contain a non-empty 'path' string for {$context_name}." );
				}
				if ( ! file_exists( $value['path'] ) ) {
					throw new \RuntimeException( "Zip file does not exist: {$value['path']}" );
				}
				if ( ! preg_match( '/\.zip$/', $value['path'] ) ) {
					throw new \RuntimeException( "Zip source path must be a .zip file for {$context_name}." );
				}
				break;
			case 'wccom':
			case 'wporg':
				if ( ! isset( $context['slug'] ) || ! is_string( $context['slug'] ) || empty( $context['slug'] ) ) {
					throw new \RuntimeException( "{$value['type']} source must have a non-empty 'slug' from context for {$context_name}." );
				}
				if ( isset( $value['version'] ) && ( ! is_string( $value['version'] ) || empty( $value['version'] ) ) ) {
					throw new \RuntimeException( "If version is provided for {$value['type']} source, it must be a non-empty string for {$context_name}." );
				}
				break;
		}

		return $value;
	}

	protected function parse_test_types( $value, array $custom_test_packages = [] ): array {
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
			$valid_options = $this->get_valid_options_for_test_type( $test_type );
			$valid_options = array_merge( $valid_options, [ 'pre_test_build', 'run', 'environment', 'extends', 'tweaks', 'php_version' ] );
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
							$parts = explode( '/', $test_package, 2 );
							if ( count( $parts ) === 2 ) {
								[ $package_source, $package_name ] = $parts;
								$name_parts                        = explode( '@', $package_name, 2 );
								$package_name_without_version      = $name_parts[0];
								$package_version                   = $name_parts[1] ?? null;

								if ( $package_source === 'local' ) {
									if ( $package_version ) {
										throw new \RuntimeException( "Versioned reference '$test_package' in '$test_type:$profile' is not supported for local test packages. Use 'local/{$package_name_without_version}'." );
									}
									$found = false;
									foreach ( $custom_test_packages as $package ) {
										if ( is_array( $package ) &&
											isset( $package['type'] ) && $package['type'] === $test_type &&
											isset( $package['name'] ) && $package['name'] === $package_name_without_version ) {
											$found = true;
											break;
										}
									}
									if ( ! $found ) {
										throw new \RuntimeException( "Test package '$test_package' in '$test_type:$profile' not found in test_packages configuration. Ensure it is defined with matching type and name." );
									}
								} elseif ( ! isset( $custom_test_packages[ $package_source ][ $package_name_without_version ] ) ) {
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

	protected function parse_test_groups( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Groups must be an array.' );
		}

		$test_types = $context['test_types'] ?? [];

		foreach ( $value as $group_name => $test_refs ) {
			if ( ! is_string( $group_name ) ) {
				throw new \RuntimeException( 'Group name must be a string.' );
			}
			if ( ! is_array( $test_refs ) ) {
				throw new \RuntimeException( "Test references for group '$group_name' must be an array." );
			}
			if ( empty( $test_refs ) ) {
				throw new \RuntimeException( "Test references for group '$group_name' cannot be empty." );
			}
			$seen_refs = [];
			foreach ( $test_refs as $test_type => $profiles ) {
				if ( ! is_string( $test_type ) ) {
					throw new \RuntimeException( "Test type in group '$group_name' must be a string." );
				}
				if ( ! is_array( $profiles ) ) {
					throw new \RuntimeException( "Profiles for test type '$test_type' in group '$group_name' must be an array." );
				}
				if ( ! isset( $test_types[ $test_type ] ) ) {
					throw new \RuntimeException( "Test type '$test_type' in group '$group_name' not found in test_types configuration." );
				}
				foreach ( $profiles as $profile ) {
					if ( ! is_string( $profile ) ) {
						throw new \RuntimeException( "Profile in group '$group_name' for test type '$test_type' must be a string." );
					}
					$ref_key = "$test_type.$profile";
					if ( in_array( $ref_key, $seen_refs ) ) {
						throw new \RuntimeException( "Duplicate test reference '$ref_key' in group '$group_name'." );
					}
					$seen_refs[] = $ref_key;
					if ( ! isset( $test_types[ $test_type ][ $profile ] ) ) {
						throw new \RuntimeException( "Test profile '$profile' for type '$test_type' in group '$group_name' not found in test_types configuration." );
					}
				}
			}
		}

		return $value;
	}

	protected function parse_environments( $value, array $context = [], ?array $sut_config = null ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Environments must be an array.' );
		}

		$environments  = [];
		$test_packages = $context['test_packages'] ?? [];

		foreach ( $value as $env_name => $config ) {
			if ( ! is_string( $env_name ) ) {
				throw new \RuntimeException( 'Environment name must be a string in environments configuration.' );
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
						$parsed_env[ $env_key ] = $this->parse_extensions( $env_value, $env_key, $context, $sut_config, $env_name );
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
								$name_parts                = explode( '@', $name_version, 2 );
								if ( count( $name_parts ) !== 2 ) {
									throw new \RuntimeException( "Invalid test package name/version '$name_version' at index $index in setup for environment '$env_name'. Expected 'name@version'." );
								}
								[ $name ] = $name_parts;
								$found    = false;
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

	protected function parse_extensions( array $items, string $type, array $context, ?array $sut_config = null, string $env_name = 'unknown' ): array {
		$extensions    = [];
		$type_singular = $type === 'plugins' ? 'plugin' : 'theme';

		foreach ( $items as $index => $item ) {
			$extension = [];

			if ( is_string( $item ) ) {
				$extension = [
					'slug'   => $item,
					'type'   => $type_singular,
					'source' => [ 'type' => 'wporg' ],
				];
			} elseif ( is_array( $item ) && isset( $item['slug'] ) ) {
				$extension = [
					'slug'   => $item['slug'],
					'type'   => $type_singular,
					'source' => $this->parse_extension_source( $item, $context, $env_name ),
				];
			} else {
				throw new \RuntimeException( "Invalid $type_singular at index $index in environment '$env_name'" );
			}

			if ( $sut_config && $extension['slug'] === $sut_config['slug'] ) {
				$this->validate_extension_sut_consistency( $extension, $sut_config, $type_singular, $env_name );
			}

			$extensions[] = $extension;
		}

		return $extensions;
	}

	protected function parse_extension_source( array $item, array $context, string $env_name ): array {
		if ( ! isset( $item['source'] ) ) {
			return [ 'type' => 'wporg' ];
		}

		$source        = $item['source'];
		$source_type   = $source['type'] ?? 'wporg';
		$parsed_source = [ 'type' => $source_type ];

		switch ( $source_type ) {
			case 'directory':
				if ( ! isset( $source['path'] ) ) {
					throw new \RuntimeException( "Extension '{$item['slug']}' has no directory path in environment '$env_name'" );
				}
				if ( ! is_dir( $source['path'] ) ) {
					throw new \RuntimeException( "Directory does not exist: {$source['path']} in environment '$env_name'" );
				}
				$parsed_source['path'] = $source['path'];
				break;
			case 'zip':
				if ( ! isset( $source['path'] ) ) {
					throw new \RuntimeException( "Extension '{$item['slug']}' has no zip path in environment '$env_name'" );
				}
				if ( ! file_exists( $source['path'] ) ) {
					throw new \RuntimeException( "Zip file does not exist: {$source['path']} in environment '$env_name'" );
				}
				if ( ! preg_match( '/\.zip$/', $source['path'] ) ) {
					throw new \RuntimeException( "Zip source path must be a .zip file for '{$item['slug']}' in environment '$env_name'" );
				}
				$parsed_source['path'] = $source['path'];
				break;
			case 'url':
				if ( ! isset( $source['url'] ) ) {
					throw new \RuntimeException( "Extension '{$item['slug']}' has no URL in environment '$env_name'" );
				}
				$parsed_source['url'] = $source['url'];
				break;
			case 'wporg':
			case 'wccom':
				$parsed_source['version'] = $source['version'] ?? 'stable';
				break;
			case 'build':
				// Reuse parse_source for build validation
				$parsed_source = $this->parse_source( $source, [
					'slug'    => $item['slug'],
					'context' => "environment.$env_name.plugins.{$item['slug']}",
				] );
				break;
			default:
				throw new \RuntimeException( "Invalid source type '$source_type' for extension '{$item['slug']}' in environment '$env_name'. Valid types are: directory, zip, url, wporg, wccom, build." );
		}

		return $parsed_source;
	}

	protected function validate_extension_sut_consistency( array $extension, array $sut_config, string $type, string $env_name ): void {
		if ( $extension['source']['type'] !== $sut_config['source']['type'] ) {
			throw new \RuntimeException( "SUT configuration mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}

		if ( $sut_config['source']['type'] === 'directory' && ( ! isset( $extension['source']['path'] ) || $extension['source']['path'] !== $sut_config['source']['path'] ) ) {
			throw new \RuntimeException( "SUT path mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}

		if ( $sut_config['source']['type'] === 'zip' && ( ! isset( $extension['source']['path'] ) || $extension['source']['path'] !== $sut_config['source']['path'] ) ) {
			throw new \RuntimeException( "SUT path mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}

		if ( $sut_config['source']['type'] === 'build' && (
				! isset( $extension['source']['command'] ) || $extension['source']['command'] !== $sut_config['source']['command'] ||
				! isset( $extension['source']['output'] ) || $extension['source']['output'] !== $sut_config['source']['output']
			) ) {
			throw new \RuntimeException( "SUT build configuration mismatch for $type '{$sut_config['slug']}' in environment '$env_name'" );
		}
	}

	protected function parse_test_packages( $value, array $context = [] ): array {
		if ( ! is_array( $value ) || empty( $value ) ) {
			throw new \RuntimeException( 'Test packages must be an array of package definitions.' );
		}

		$root_path     = $context['root_path'] ?? getcwd();
		$packages      = [];
		$seen_packages = [];

		foreach ( $value as $index => $package ) {
			if ( ! isset( $package['type'], $package['name'], $package['file'] ) ||
				! is_string( $package['type'] ) || ! is_string( $package['name'] ) || ! is_string( $package['file'] ) ) {
				throw new \RuntimeException( "Test package at index $index must have 'type', 'name', and 'file' as strings." );
			}

			if ( isset( $package['extends'] ) && ! is_string( $package['extends'] ) ) {
				throw new \RuntimeException( "Extends for test package '{$package['type']}:{$package['name']}' must be a string." );
			}

			$test_type       = $package['type'];
			$package_name    = $package['name'];
			$package_version = $package['version'] ?? null;
			$package_key     = "{$test_type}:{$package_name}" . ( $package_version ? "@{$package_version}" : '' );

			if ( in_array( $package_key, $seen_packages, true ) ) {
				throw new \RuntimeException( "Duplicate test package definition for '{$test_type}:{$package_name}' in test_packages. Each test package must have a unique type and name combination." );
			}
			$seen_packages[] = $package_key;

			$file_path         = $root_path . DIRECTORY_SEPARATOR . $package['file'];
			$file_dir          = dirname( $file_path );
			$file_dir_relative = str_replace( $root_path . DIRECTORY_SEPARATOR, '', $file_dir ) . DIRECTORY_SEPARATOR;

			if ( ! file_exists( $file_path ) ) {
				throw new \RuntimeException( "Test package file '$file_path' for '{$test_type}:{$package_name}' not found. Verify the file path in test_packages configuration." );
			}

			$contents = file_get_contents( $file_path );
			$config   = json_decode( $contents, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $config ) ) {
				throw new \RuntimeException( "Invalid JSON in test package file '$file_path' for '{$test_type}:{$package_name}': " . json_last_error_msg() );
			}

			if ( ! isset( $config['$schema'] ) || $config['$schema'] !== 'https://qit.woo.com/json-schema/test-package' ) {
				throw new \RuntimeException( "Test package '{$test_type}:{$package_name}' must have \$schema set to 'https://qit.woo.com/json-schema/test-package'." );
			}

			if ( ! isset( $config['version'], $config['author'] ) ) {
				throw new \RuntimeException( "Test package '{$test_type}:{$package_name}' must include 'version' and 'author'." );
			}

			if ( ! is_string( $config['version'] ) ) {
				throw new \RuntimeException( "Version for test package '{$test_type}:{$package_name}' must be a string." );
			}
			if ( ! is_string( $config['author'] ) && ! is_array( $config['author'] ) ) {
				throw new \RuntimeException( "Author for test package '{$test_type}:{$package_name}' must be a string or array." );
			}
			if ( isset( $config['test_command'] ) && ! is_string( $config['test_command'] ) ) {
				throw new \RuntimeException( "Test command for test package '{$test_type}:{$package_name}' must be a string." );
			}
			if ( isset( $config['env_vars'] ) && ! is_array( $config['env_vars'] ) ) {
				throw new \RuntimeException( "Environment variables for test package '{$test_type}:{$package_name}' must be an array." );
			}

			if ( isset( $config['env_vars'] ) ) {
				foreach ( $config['env_vars'] as $key => &$val ) {
					if ( is_bool( $val ) ) {
						$config['env_vars'][ $key ] = $val ? 'true' : 'false';
					} else {
						$config['env_vars'][ $key ] = (string) $val;
					}
				}
			}

			if ( isset( $config['extends'] ) ) {
				throw new \RuntimeException( "Test package '{$test_type}:{$package_name}' must not include 'extends' in standalone file." );
			}

			if ( isset( $config['lifecycle'] ) ) {
				foreach ( $config['lifecycle'] as $phase => &$scripts ) {
					foreach ( $scripts as &$script ) {
						if ( isset( $script['command'] ) ) {
							$original_prefix = '';
							if ( strpos( $script['command'], './' ) === 0 ) {
								$original_prefix = './';
							}

							$path = ltrim( $script['command'], './' );
							if ( strpos( $path, $file_dir_relative ) === 0 ) {
								$path = substr( $path, strlen( $file_dir_relative ) );
							}
							$script['command'] = $original_prefix . $path;

							if ( $original_prefix === './' && ! file_exists( $file_dir . DIRECTORY_SEPARATOR . $path ) ) {
								throw new \RuntimeException( "Lifecycle script file '{$file_dir}/{$path}' for '{$test_type}:{$package_name}' not found. Verify the file path in lifecycle configuration." );
							}
						}
					}
					usort( $scripts, function ( $a, $b ) {
						$priority_a = $a['priority'] ?? 0;
						$priority_b = $b['priority'] ?? 0;

						return $priority_a <=> $priority_b;
					} );
				}
			}

			if ( isset( $config['mu_plugins'] ) ) {
				foreach ( $config['mu_plugins'] as &$plugin ) {
					$original_prefix = '';
					if ( strpos( $plugin, './' ) === 0 ) {
						$original_prefix = './';
					}

					$path = ltrim( $plugin, './' );
					if ( strpos( $path, $file_dir_relative ) === 0 ) {
						$path = substr( $path, strlen( $file_dir_relative ) );
					}
					$plugin = $original_prefix . $path;
				}
			}

			if ( isset( $config['test_results'] ) ) {
				foreach ( $config['test_results'] as &$result ) {
					$original_prefix = '';
					if ( strpos( $result, './' ) === 0 ) {
						$original_prefix = './';
					}

					$path = ltrim( $result, './' );
					if ( strpos( $path, $file_dir_relative ) === 0 ) {
						$path = substr( $path, strlen( $file_dir_relative ) );
					}
					$result = $original_prefix . $path;
				}
			}

			if ( ! isset( $packages[ $test_type ] ) ) {
				$packages[ $test_type ] = [];
			}
			$packages[ $test_type ][ $package_name ] = [
				'config'  => $config,
				'extends' => $package['extends'] ?? null,
			];
		}

		return $this->resolve_extends( $packages, 'test package' );
	}

	protected function resolve_extends( array $section, string $section_name ): array {
		$resolved = [];
		$pending  = $section;

		while ( ! empty( $pending ) ) {
			$resolved_something = false;

			foreach ( $pending as $name => $config ) {
				if ( ! isset( $config['extends'] ) ) {
					$resolved[ $name ] = $config;
					unset( $pending[ $name ] );
					$resolved_something = true;
					continue;
				}

				$base_name = $config['extends'];
				if ( ! isset( $section[ $base_name ] ) ) {
					throw new \RuntimeException( "Extended configuration '$base_name' not found in $section_name '$name'." );
				}

				if ( isset( $resolved[ $base_name ] ) ) {
					$base_config  = $resolved[ $base_name ];
					$child_config = $config;
					unset( $child_config['extends'] );
					$merged_config     = array_merge( $base_config, $child_config );
					$resolved[ $name ] = $merged_config;
					unset( $pending[ $name ] );
					$resolved_something = true;
				}
			}

			if ( ! $resolved_something && ! empty( $pending ) ) {
				throw new \RuntimeException( "Circular dependency detected in $section_name configurations." );
			}
		}

		return $resolved;
	}

	protected function resolve_package_extends( array $config, ?string $extends, string $test_type, string $package_name, array $packages, array $visited ): array {
		if ( ! $extends ) {
			unset( $config['$schema'] );

			return $config;
		}

		if ( strpos( $extends, ':' ) !== false ) {
			throw new \RuntimeException( "Unsupported external extends reference '$extends' for '{$test_type}:{$package_name}'." );
		}

		if ( ! is_string( $extends ) ) {
			throw new \RuntimeException( "Extends for '{$test_type}:{$package_name}' must be a string." );
		}

		$current_key = "$test_type:$package_name";
		if ( in_array( $current_key, $visited, true ) ) {
			throw new \RuntimeException( "Circular dependency detected in test package '{$test_type}:{$package_name}'." );
		}
		$visited[] = $current_key;

		if ( ! isset( $packages[ $test_type ][ $extends ] ) ) {
			throw new \RuntimeException( "Extended package '$extends' not found for '{$test_type}:{$package_name}' in test_packages." );
		}

		$base_config = $this->resolve_package_extends(
			$packages[ $test_type ][ $extends ]['config'],
			$packages[ $test_type ][ $extends ]['extends'],
			$test_type,
			$extends,
			$packages,
			$visited
		);

		$merged = $this->merge_package_configs( $base_config, $config );

		if ( ! is_string( $config['version'] ) ) {
			throw new \RuntimeException( "Version for test package '{$test_type}:{$package_name}' must be a string." );
		}
		if ( ! is_string( $config['author'] ) && ! is_array( $config['author'] ) ) {
			throw new \RuntimeException( "Author for test package '{$test_type}:{$package_name}' must be a string or array." );
		}
		$merged['version'] = $config['version'];
		$merged['author']  = $config['author'];
		unset( $merged['$schema'] );

		return $merged;
	}

	protected function merge_package_configs( array $base, array $child ): array {
		$simple_fields = [ 'test_command', 'description' ];
		$merged        = $base;

		foreach ( $simple_fields as $field ) {
			if ( isset( $child[ $field ] ) ) {
				if ( $field === 'test_command' && ! is_string( $child[ $field ] ) ) {
					throw new \RuntimeException( 'Test command must be a string.' );
				}
				$merged[ $field ] = $child[ $field ];
			}
		}

		$complex_fields = [ 'lifecycle', 'env_vars', 'test_results', 'mu_plugins', 'required_secrets' ];
		foreach ( $complex_fields as $field ) {
			if ( isset( $child[ $field ] ) ) {
				if ( $field === 'env_vars' && ! is_array( $child[ $field ] ) ) {
					throw new \RuntimeException( 'Environment variables must be an array.' );
				}
				if ( $field === 'env_vars' && is_array( $child[ $field ] ) ) {
					foreach ( $child[ $field ] as $key => $value ) {
						if ( is_bool( $value ) ) {
							$child[ $field ][ $key ] = $value ? 'true' : 'false';
						} else {
							$child[ $field ][ $key ] = (string) $value;
						}
					}
				}
				if ( ! isset( $base[ $field ] ) ) {
					$merged[ $field ] = $child[ $field ];
				} elseif ( is_array( $base[ $field ] ) && is_array( $child[ $field ] ) ) {
					if ( $field === 'lifecycle' ) {
						$merged[ $field ] = $this->merge_lifecycle( $base[ $field ], $child[ $field ] );
					} elseif ( $field === 'required_secrets' ) {
						$merged[ $field ] = array_unique( array_merge( $base[ $field ], $child[ $field ] ) );
					} else {
						$merged[ $field ] = array_replace_recursive( $base[ $field ], $child[ $field ] );
					}
				} else {
					$merged[ $field ] = $child[ $field ];
				}
			}
		}

		return $merged;
	}

	protected function merge_lifecycle( array $base, array $child ): array {
		$merged = $base;
		foreach ( $child as $phase => $scripts ) {
			if ( ! isset( $base[ $phase ] ) ) {
				$merged[ $phase ] = $scripts;
			} else {
				$combined = array_merge( $base[ $phase ], $scripts );
				usort( $combined, function ( $a, $b ) {
					$priority_a = $a['priority'] ?? 0;
					$priority_b = $b['priority'] ?? 0;

					return $priority_a <=> $priority_b;
				} );
				$merged[ $phase ] = $combined;
			}
		}

		return $merged;
	}

	protected function get_valid_options_for_test_type( string $test_type ): array {
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

	protected function resolve_paths( array $config, string $root_path ): array {
		$resolved = $config;

		$resolvePath = function ( $path ) use ( $root_path ) {
			if ( is_string( $path ) && ! str_starts_with( $path, '/' ) && ! filter_var( $path, FILTER_VALIDATE_URL ) ) {
				$full_path = $root_path . DIRECTORY_SEPARATOR . ltrim( $path, './' . DIRECTORY_SEPARATOR );

				return \QIT_CLI\normalize_path( $full_path, false );
			}

			return $path;
		};

		if ( isset( $resolved['sut']['source']['path'] ) ) {
			$resolved['sut']['source']['path'] = $resolvePath( $resolved['sut']['source']['path'] );
		}

		if ( isset( $resolved['sut']['source']['output'] ) ) {
			$resolved['sut']['source']['output'] = $resolvePath( $resolved['sut']['source']['output'] );
		}

		if ( isset( $resolved['environments'] ) ) {
			foreach ( $resolved['environments'] as &$env ) {
				foreach ( [ 'plugins', 'themes' ] as $type ) {
					if ( isset( $env[ $type ] ) ) {
						foreach ( $env[ $type ] as &$item ) {
							if ( is_array( $item ) && isset( $item['source']['path'] ) ) {
								$item['source']['path'] = $resolvePath( $item['source']['path'] );
							}
							if ( is_array( $item ) && isset( $item['source']['output'] ) ) {
								$item['source']['output'] = $resolvePath( $item['source']['output'] );
							}
						}
					}
				}
			}
			unset( $env );
		}

		file_put_contents( '/tmp/qit/qit_debug.log', 'ConfigParser: Resolved paths: ' . json_encode( $resolved, JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );

		return $resolved;
	}

	protected function validate_sut_consistency( array $sut_config, array $raw_environments ): void {
		foreach ( $raw_environments as $env_name => $env_config ) {
			if ( isset( $env_config['plugins'] ) ) {
				foreach ( $env_config['plugins'] as $plugin ) {
					if ( ! is_array( $plugin ) || ! isset( $plugin['slug'] ) ) {
						continue;
					}
					if ( $plugin['slug'] === $sut_config['slug'] ) {
						if ( ! isset( $plugin['source']['type'] ) || $plugin['source']['type'] !== $sut_config['source']['type'] ) {
							throw new \RuntimeException( "SUT configuration mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'directory' && ( ! isset( $plugin['source']['path'] ) || $plugin['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'zip' && ( ! isset( $plugin['source']['path'] ) || $plugin['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'build' && (
								! isset( $plugin['source']['command'] ) || $plugin['source']['command'] !== $sut_config['source']['command'] ||
								! isset( $plugin['source']['output'] ) || $plugin['source']['output'] !== $sut_config['source']['output']
							) ) {
							throw new \RuntimeException( "SUT build configuration mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
					}
				}
			}
			if ( isset( $env_config['themes'] ) ) {
				foreach ( $env_config['themes'] as $theme ) {
					if ( ! is_array( $theme ) || ! isset( $theme['slug'] ) ) {
						continue;
					}
					if ( $theme['slug'] === $sut_config['slug'] ) {
						if ( ! isset( $theme['source']['type'] ) || $theme['source']['type'] !== $sut_config['source']['type'] ) {
							throw new \RuntimeException( "SUT configuration mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'directory' && ( ! isset( $theme['source']['path'] ) || $theme['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'zip' && ( ! isset( $theme['source']['path'] ) || $theme['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'build' && (
								! isset( $theme['source']['command'] ) || $theme['source']['command'] !== $sut_config['source']['command'] ||
								! isset( $theme['source']['output'] ) || $theme['source']['output'] !== $sut_config['source']['output']
							) ) {
							throw new \RuntimeException( "SUT build configuration mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
					}
				}
			}
		}
	}

	protected function resolve_extends_path( string $extends, string $current_file ): string {
		if ( ! filter_var( $extends, FILTER_VALIDATE_URL ) ) {
			$base_dir      = dirname( $current_file );
			$resolved_path = realpath( $base_dir . DIRECTORY_SEPARATOR . $extends );
			if ( $resolved_path === false ) {
				throw new \RuntimeException( "Base config file '$extends' not found." );
			}

			return $resolved_path;
		}

		try {
			$request  = new RequestBuilder( $extends );
			$contents = $request->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "Failed to fetch base config from URL '$extends'." );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_base_' );
		file_put_contents( $temp_file, $contents );

		return $temp_file;
	}

	protected function merge_configs( array $base, array $child, array $child_raw ): array {
		$merged = $base;

		if ( isset( $child['sut'] ) ) {
			$merged['sut'] = $child['sut'];
		} elseif ( isset( $child_raw['sut'] ) ) {
			$merged['sut'] = $this->parse_sut( $child_raw['sut'], [
				'root_path' => $this->root_path,
				'context'   => 'sut.source',
			] );
		}

		foreach ( $child_raw as $key => $value ) {
			if ( $key === 'extends' || $key === 'sut' ) {
				continue;
			}
			if ( isset( $base[ $key ] ) && is_array( $base[ $key ] ) && is_array( $value ) ) {
				if ( in_array( $key, [ 'environments', 'test_types', 'test_packages', 'test_groups' ], true ) ) {
					$merged[ $key ] = array_replace_recursive( $base[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		foreach ( $child as $key => $value ) {
			if ( $key === 'sut' || in_array( $key, [ 'environments', 'test_types', 'test_packages', 'test_groups' ], true ) ) {
				continue;
			}
			$merged[ $key ] = $value;
		}

		return $merged;
	}

	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found." );
		}

		return $this->parsed_config['environments'][ $name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		return $this->parsed_config['test_types'][ $test_type ][ $profile ] ?? [];
	}

	public function get_resolved_package( string $test_type, string $package_name, array $packages ): array {
		if ( ! $test_type || ! $package_name ) {
			throw new \RuntimeException( "Invalid package format '$test_type:$package_name'. Expected 'test_type:package_name'." );
		}
		if ( ! isset( $packages[ $test_type ][ $package_name ] ) ) {
			throw new \RuntimeException( "Package '$test_type:$package_name' not found in test_packages." );
		}

		return $this->resolve_package_extends(
			$packages[ $test_type ][ $package_name ]['config'],
			$packages[ $test_type ][ $package_name ]['extends'],
			$test_type,
			$package_name,
			$packages,
			[]
		);
	}
}
