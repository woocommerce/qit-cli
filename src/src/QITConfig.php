<?php

namespace QIT_CLI;

use Symfony\Component\Console\Application;

class QITConfig {

	private array $config = [];
	private string $config_file;
	private Application $console_application;

	public function __construct( string $config_file = 'qit.json', Application $console_application = null ) {
		$this->config_file         = $config_file;
		$this->console_application = $console_application ?? new Application();
		$this->load_config();
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

	private function load_config(): void {
		if ( ! file_exists( $this->config_file ) ) {
			$this->config = [];

			return;
		}

		$contents = file_get_contents( $this->config_file );
		$decoded  = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		$this->config = $decoded;

		foreach ( $this->config as $key => &$value ) {
			switch ( $key ) {
				case '$schema':
					if ( ! is_string( $value ) ) {
						throw new \RuntimeException( '$schema must be a string.' );
					}
					break;
				case 'slug':
					if ( ! is_string( $value ) ) {
						throw new \RuntimeException( 'Slug must be a string.' );
					}
					break;
				case 'type':
					if ( ! in_array( $value, [ 'plugin', 'theme', 'website' ] ) ) {
						throw new \RuntimeException( 'Invalid type. Must be plugin, theme, or website.' );
					}
					break;
				case 'pre_test_build':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'pre_test_build must be an array.' );
					}
					if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) ) {
						throw new \RuntimeException( 'pre_test_build must contain a "command" key with a string value.' );
					}
					if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) ) {
						throw new \RuntimeException( 'pre_test_build must contain a "output" key with a string value.' );
					}
					break;
				case 'tests':
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
								// If there is a test_package, make sure it references a package in "custom_test_packages".
								if ( isset( $config['test_package'] ) && ! isset( $this->config['custom_test_packages'][ $config['test_package'] ] ) ) {
									throw new \RuntimeException( "Test package '{$config['test_package']}' not found in custom_test_packages." );
								}
								// Make sure at least a valid test_package or a non-empty "compatibility_tests" array are present.
								if ( ! isset( $config['test_package'] ) && ( ! isset( $config['compatibility_tests'] ) || empty( $config['compatibility_tests'] ) ) ) {
									throw new \RuntimeException( "Either 'test_package' or 'compatibility_tests' must be set for '$test_type:$profile'." );
								}

								// Check that "compatibility_tests" is an array of strings.
								if ( isset( $config['compatibility_tests'] ) && ! is_array( $config['compatibility_tests'] ) ) {
									throw new \RuntimeException( "compatibility_tests in '$test_type:$profile' must be an array." );
								}
								if ( isset( $config['compatibility_tests'] ) ) {
									foreach ( $config['compatibility_tests'] as $compat_test ) {
										if ( ! is_string( $compat_test ) ) {
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
					foreach ( $value as $test_type => $profiles ) {
						$this->validate_single_level_inheritance( $profiles, "test profile '$test_type'" );
					}
					break;
				case 'groups':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Groups must be an array.' );
					}
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
							if ( ! isset( $this->config['tests'][ $test_type ] ) ) {
								throw new \RuntimeException( "Test type '$test_type' in group '$group_name' not found in tests configuration." );
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
								if ( ! isset( $this->config['tests'][ $test_type ][ $profile ] ) ) {
									throw new \RuntimeException( "Test profile '$profile' for type '$test_type' in group '$group_name' not found in tests configuration." );
								}
							}
						}
					}
					break;
				case 'custom_test_packages':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Custom test packages must be an array.' );
					}
					foreach ( $value as $test_type => $packages ) {
						if ( ! is_string( $test_type ) ) {
							throw new \RuntimeException( 'Custom test package test type must be a string.' );
						}
						if ( ! is_array( $packages ) ) {
							throw new \RuntimeException( "Packages for test type '$test_type' must be an array." );
						}
						foreach ( $packages as $package_name => $config ) {
							if ( ! is_string( $package_name ) ) {
								throw new \RuntimeException( "Package name for test type '$test_type' must be a string." );
							}
							if ( ! is_array( $config ) ) {
								throw new \RuntimeException( "Configuration for custom test package '$test_type:$package_name' must be an array." );
							}
							foreach ( $config as $config_key => $config_value ) {
								switch ( $config_key ) {
									case 'extends':
										if ( ! is_string( $config_value ) ) {
											throw new \RuntimeException( "'extends' in custom test package '$test_type:$package_name' must be a string." );
										}
										break;
									case 'root_path':
										if ( ! is_string( $config_value ) ) {
											throw new \RuntimeException( "root_path in custom test package '$test_type:$package_name' must be a string." );
										}
										break;
									case 'description':
										if ( ! is_string( $config_value ) ) {
											throw new \RuntimeException( "description in custom test package '$test_type:$package_name' must be a string." );
										}
										break;
									case 'test_command':
										if ( ! is_string( $config_value ) ) {
											throw new \RuntimeException( "test_command in custom test package '$test_type:$package_name' must be a string." );
										}
										break;
									case 'test_results':
										if ( ! is_array( $config_value ) ) {
											throw new \RuntimeException( "test_results in custom test package '$test_type:$package_name' must be an array." );
										}
										foreach ( $config_value as $result_type => $result_path ) {
											if ( ! is_string( $result_type ) ) {
												throw new \RuntimeException( "Test result type in '$test_type:$package_name' must be a string." );
											}
											if ( ! is_string( $result_path ) ) {
												throw new \RuntimeException( "Test result path for '$result_type' in '$test_type:$package_name' must be a string." );
											}
										}
										break;
									case 'lifecycle':
										if ( ! is_array( $config_value ) ) {
											throw new \RuntimeException( "lifecycle in custom test package '$test_type:$package_name' must be an array." );
										}
										foreach ( $config_value as $lifecycle_phase => $lifecycle_script ) {
											if ( ! in_array( $lifecycle_phase, [ 'setup', 'teardown' ] ) ) {
												throw new \RuntimeException( "Invalid lifecycle phase '$lifecycle_phase' in '$test_type:$package_name'. Must be 'setup' or 'teardown'." );
											}
											if ( ! is_string( $lifecycle_script ) ) {
												throw new \RuntimeException( "Lifecycle script for '$lifecycle_phase' in '$test_type:$package_name' must be a string." );
											}
										}
										break;
									case 'mu_plugins':
										if ( ! is_array( $config_value ) ) {
											throw new \RuntimeException( "mu_plugins in custom test package '$test_type:$package_name' must be an array." );
										}
										foreach ( $config_value as $plugin_path ) {
											if ( ! is_string( $plugin_path ) ) {
												throw new \RuntimeException( "MU plugin path in '$test_type:$package_name' must be a string." );
											}
										}
										break;
									case 'constraints':
										if ( ! is_array( $config_value ) ) {
											throw new \RuntimeException( "constraints in custom test package '$test_type:$package_name' must be an array." );
										}
										foreach ( $config_value as $constraint_type => $constraint_value ) {
											if ( $constraint_type === 'wordpress' ) {
												if ( ! is_string( $constraint_value ) ) {
													throw new \RuntimeException( "WordPress constraint in '$test_type:$package_name' must be a string." );
												}
											} elseif ( $constraint_type === 'requires_plugins' ) {
												if ( ! is_array( $constraint_value ) ) {
													throw new \RuntimeException( "requires_plugins in '$test_type:$package_name' must be an array." );
												}
												foreach ( $constraint_value as $plugin_name => $plugin_version ) {
													if ( ! is_string( $plugin_name ) ) {
														throw new \RuntimeException( "Plugin name in requires_plugins for '$test_type:$package_name' must be a string." );
													}
													if ( ! is_string( $plugin_version ) ) {
														throw new \RuntimeException( "Plugin version for '$plugin_name' in '$test_type:$package_name' must be a string." );
													}
												}
											} else {
												throw new \RuntimeException( "Unknown constraint type '$constraint_type' in '$test_type:$package_name'." );
											}
										}
										break;
									case 'required_secrets':
										if ( ! is_array( $config_value ) ) {
											throw new \RuntimeException( "required_secrets in custom test package '$test_type:$package_name' must be an array." );
										}
										foreach ( $config_value as $secret ) {
											if ( ! is_string( $secret ) ) {
												throw new \RuntimeException( "Required secret in '$test_type:$package_name' must be a string." );
											}
										}
										break;
									case 'env_vars':
										if ( ! is_array( $config_value ) ) {
											throw new \RuntimeException( "env_vars in custom test package '$test_type:$package_name' must be an array." );
										}
										foreach ( $config_value as $var_name => $var_value ) {
											if ( ! is_string( $var_name ) ) {
												throw new \RuntimeException( "Environment variable name in '$test_type:$package_name' must be a string." );
											}
											if ( ! is_string( $var_value ) ) {
												throw new \RuntimeException( "Environment variable value for '$var_name' in '$test_type:$package_name' must be a string." );
											}
										}
										break;
									default:
										throw new \RuntimeException( "Unknown key '$config_key' in custom test package '$test_type:$package_name' configuration." );
								}
							}
						}
						$this->validate_single_level_inheritance( $packages, "custom test package '$test_type'" );
					}
					break;
				case 'environments':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Environments must be an array.' );
					}
					foreach ( $value as $env_name => $config ) {
						if ( ! is_string( $env_name ) ) {
							throw new \RuntimeException( 'Environment name must be a string.' );
						}
						if ( ! is_array( $config ) ) {
							throw new \RuntimeException( "Configuration for environment '$env_name' must be an array." );
						}
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
									break;
								case 'wordpress_version':
									if ( ! is_string( $env_value ) ) {
										throw new \RuntimeException( "wordpress_version in environment '$env_name' must be a string." );
									}
									break;
								case 'woocommerce_version':
									if ( ! is_string( $env_value ) ) {
										throw new \RuntimeException( "woocommerce_version in environment '$env_name' must be a string." );
									}
									break;
								case 'object_cache':
									if ( ! is_bool( $env_value ) ) {
										throw new \RuntimeException( "object_cache in environment '$env_name' must be a boolean." );
									}
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
									break;
								case 'plugins':
									if ( ! is_array( $env_value ) ) {
										throw new \RuntimeException( "plugins in environment '$env_name' must be an array." );
									}
									foreach ( $env_value as $plugin ) {
										if ( ! is_string( $plugin ) ) {
											throw new \RuntimeException( "Plugin in environment '$env_name' must be a string." );
										}
									}
									break;
								case 'bootstrap':
									if ( ! is_array( $env_value ) ) {
										throw new \RuntimeException( "bootstrap in environment '$env_name' must be an array." );
									}
									foreach ( $env_value as $bootstrap_item ) {
										if ( ! is_array( $bootstrap_item ) || ! isset( $bootstrap_item['slug'], $bootstrap_item['test_package'] ) ) {
											throw new \RuntimeException( "Bootstrap item in environment '$env_name' must be an object with 'slug' and 'test_package' fields." );
										}
										if ( ! is_string( $bootstrap_item['slug'] ) ) {
											throw new \RuntimeException( "Bootstrap slug in environment '$env_name' must be a string." );
										}
										if ( ! is_string( $bootstrap_item['test_package'] ) ) {
											throw new \RuntimeException( "Bootstrap test_package in environment '$env_name' must be a string." );
										}
									}
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
									break;
								default:
									throw new \RuntimeException( "Unknown key '$env_key' in environment '$env_name' configuration." );
							}
						}
					}
					$this->validate_single_level_inheritance( $value, 'environment' );
					break;
				default:
					throw new \RuntimeException( "Unknown top-level key '$key' in configuration." );
			}
		}

		// Resolve extends for environments
		if ( isset( $this->config['environments'] ) ) {
			$this->config['environments'] = $this->resolve_extends_section( $this->config['environments'], 'environment' );
		}

		// Resolve extends for custom_test_packages
		if ( isset( $this->config['custom_test_packages'] ) ) {
			foreach ( $this->config['custom_test_packages'] as $test_type => &$packages ) {
				$packages = $this->resolve_extends_section( $packages, "custom test package '$test_type'" );
			}
		}

		// Resolve extends for tests
		if ( isset( $this->config['tests'] ) ) {
			foreach ( $this->config['tests'] as $test_type => &$profiles ) {
				$profiles = $this->resolve_extends_section( $profiles, "test profile '$test_type'" );
			}
		}

		// Apply global pre_test_build to test profiles if not set
		if ( isset( $this->config['pre_test_build'] ) && isset( $this->config['tests'] ) ) {
			foreach ( $this->config['tests'] as $test_type => &$profiles ) {
				foreach ( $profiles as $profile => &$config ) {
					if ( ! isset( $config['pre_test_build'] ) ) {
						$config['pre_test_build'] = $this->config['pre_test_build'];
					}
				}
			}
		}
	}

	private function validate_single_level_inheritance( array $section, string $section_name ): void {
		foreach ( $section as $name => $config ) {
			if ( isset( $config['extends'] ) ) {
				$base_name = $config['extends'];
				if ( ! isset( $section[ $base_name ] ) ) {
					throw new \RuntimeException( "Extended configuration '$base_name' not found in $section_name '$name'." );
				}
				if ( isset( $section[ $base_name ]['extends'] ) ) {
					throw new \RuntimeException( "Deep inheritance not allowed in $section_name: '$base_name' cannot extend another configuration." );
				}
			}
		}
	}

	private function resolve_extends_section( array $section, string $section_name ): array {
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

	public function get( string $key, $default = null ) {
		return $this->config[ $key ] ?? $default;
	}

	public function get_all(): array {
		return $this->config;
	}

	public function get_config_file(): string {
		return $this->config_file;
	}

	public function get_nested_value( string $path ): mixed {
		$keys  = explode( '.', $path );
		$value = $this->config;
		foreach ( $keys as $key ) {
			if ( is_array( $value ) && array_key_exists( $key, $value ) ) {
				$value = $value[ $key ];
			} else {
				return null;
			}
		}

		return $value;
	}

	public function get_environment( string $name ): array {
		return $this->config['environments'][ $name ] ?? [];
	}

	public function get_custom_test_package( string $name ): array {
		[ $test_type, $package_name ] = explode( '.', $name, 2 );
		if ( ! isset( $this->config['custom_test_packages'][ $test_type ][ $package_name ] ) ) {
			throw new \RuntimeException( "Configuration '$package_name' not found in section 'custom_test_packages.$test_type'." );
		}

		return $this->config['custom_test_packages'][ $test_type ][ $package_name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		return $this->config['tests'][ $test_type ][ $profile ] ?? [];
	}

	public function get_group( string $group_name ): array {
		$group = $this->config['groups'][ $group_name ] ?? [];

		return is_array( $group ) ? $group : [];
	}

	public function get_group_tests( string $group_name ): array {
		$group = $this->get_group( $group_name );
		if ( empty( $group ) ) {
			return [];
		}

		$tests = [];
		foreach ( $group as $test_type => $profiles ) {
			foreach ( $profiles as $profile ) {
				$tests[] = [
					'type'    => $test_type,
					'profile' => $profile,
					'config'  => $this->get_test_config( $test_type, $profile ),
				];
			}
		}

		return $tests;
	}

	public function get_compatibility_tests( string $test_type, string $profile ): array {
		$test_config  = $this->get_test_config( $test_type, $profile );
		$compat_tests = $test_config['compatibility_tests'] ?? [];
		if ( ! is_array( $compat_tests ) ) {
			throw new \RuntimeException( "Invalid compatibility_tests for '$test_type:$profile'. Must be an array." );
		}

		return $compat_tests;
	}
}