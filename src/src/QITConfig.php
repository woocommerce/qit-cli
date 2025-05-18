<?php

namespace QIT_CLI;

use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class QITConfig {
	use MatchesSnapshots;

	private array $config = [];
	private string $configFile;
	private Application $consoleApplication;

	public function __construct( string $configFile = 'qit.json', Application $consoleApplication = null ) {
		$this->configFile         = $configFile;
		$this->consoleApplication = $consoleApplication ?? new Application();
		$this->load_config();
	}

	private function get_valid_options_for_test_type( string $testType ): array {
		try {
			$command    = $this->consoleApplication->find( "run:$testType" );
			$definition = $command->getDefinition();
			$options    = [];
			foreach ( $definition->getOptions() as $option ) {
				$options[] = $option->getName();
			}

			return $options;
		} catch ( \InvalidArgumentException $e ) {
			throw new \RuntimeException( "No command found for test type '$testType'. Expected a 'run:$testType' command." );
		}
	}

	private function load_config(): void {
		if ( ! file_exists( $this->configFile ) ) {
			$this->config = [];

			return;
		}

		$contents = file_get_contents( $this->configFile );
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
					if ( is_string( $value ) ) {
						$value = [ 'command' => $value ];
					} elseif ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Invalid pre_test_build. Must be string or array.' );
					}
					break;
				case 'tests':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Tests must be an array.' );
					}
					foreach ( $value as $testType => $profiles ) {
						if ( ! is_string( $testType ) ) {
							throw new \RuntimeException( 'Test type must be a string.' );
						}
						if ( ! is_array( $profiles ) ) {
							throw new \RuntimeException( "Profiles for test type '$testType' must be an array." );
						}
						$validOptions   = $this->get_valid_options_for_test_type( $testType );
						$validOptions[] = 'settings';
						$validOptions[] = 'pre_test_build';
						$validOptions[] = 'test_matrix';
						$validOptions[] = 'env';
						$validOptions[] = 'extends';
						foreach ( $profiles as $profile => $config ) {
							if ( ! is_string( $profile ) ) {
								throw new \RuntimeException( "Profile for test type '$testType' must be a string." );
							}
							if ( ! is_array( $config ) ) {
								throw new \RuntimeException( "Configuration for '$testType:$profile' must be an array." );
							}
							foreach ( $config as $configKey => $configValue ) {
								if ( ! in_array( $configKey, $validOptions ) ) {
									throw new \RuntimeException( "Invalid key '$configKey' in profile '$testType:$profile'. Must be one of: " . implode( ', ', $validOptions ) );
								}
								if ( $configKey === 'settings' && ! is_array( $configValue ) ) {
									throw new \RuntimeException( "Settings in '$testType:$profile' must be an array." );
								}
								if ( $configKey === 'test_matrix' && ! is_array( $configValue ) ) {
									throw new \RuntimeException( "Test_matrix in '$testType:$profile' must be an array." );
								}
								if ( $configKey === 'env' && ! is_string( $configValue ) ) {
									throw new \RuntimeException( "Env in '$testType:$profile' must be a string." );
								}
								if ( $configKey === 'extends' && ! is_string( $configValue ) ) {
									throw new \RuntimeException( "Extends in '$testType:$profile' must be a string." );
								}
								if ( $configKey === 'pre_test_build' ) {
									if ( is_string( $configValue ) ) {
										$config[ $configKey ] = [ 'command' => $configValue ];
									} elseif ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "Invalid pre_test_build in '$testType:$profile'. Must be string or array." );
									}
								}
							}
						}
					}
					break;
				case 'groups':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Groups must be an array.' );
					}
					foreach ( $value as $groupName => $testRefs ) {
						if ( ! is_string( $groupName ) ) {
							throw new \RuntimeException( 'Group name must be a string.' );
						}
						if ( ! is_array( $testRefs ) ) {
							throw new \RuntimeException( "Test references for group '$groupName' must be an array." );
						}
						if ( empty( $testRefs ) ) {
							throw new \RuntimeException( "Test references for group '$groupName' cannot be empty." );
						}
						$seenRefs = [];
						foreach ( $testRefs as $testRef ) {
							if ( ! is_string( $testRef ) ) {
								throw new \RuntimeException( "Test reference '$testRef' in group '$groupName' must be a string." );
							}
							if ( ! preg_match( '/^[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+$/', $testRef ) ) {
								throw new \RuntimeException( "Invalid test reference '$testRef' in group '$groupName'. Expected 'type:profile'." );
							}
							if ( in_array( $testRef, $seenRefs ) ) {
								throw new \RuntimeException( "Duplicate test reference '$testRef' in group '$groupName'." );
							}
							$seenRefs[] = $testRef;
							[ $type, $profile ] = explode( ':', $testRef, 2 );
							if ( ! isset( $this->config['tests'][ $type ] ) ) {
								throw new \RuntimeException( "Test type '$type' from reference '$testRef' in group '$groupName' not found in tests configuration." );
							}
							if ( ! isset( $this->config['tests'][ $type ][ $profile ] ) ) {
								throw new \RuntimeException( "Test profile '$profile' from reference '$testRef' in group '$groupName' not found in tests configuration." );
							}
						}
					}
					break;
				case 'custom_test_packages':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Custom test packages must be an array.' );
					}
					foreach ( $value as $packageName => $config ) {
						if ( ! is_string( $packageName ) ) {
							throw new \RuntimeException( 'Custom test package name must be a string.' );
						}
						if ( ! is_array( $config ) ) {
							throw new \RuntimeException( "Configuration for custom test package '$packageName' must be an array." );
						}
						foreach ( $config as $configKey => $configValue ) {
							switch ( $configKey ) {
								case 'extends':
									if ( ! is_string( $configValue ) ) {
										throw new \RuntimeException( "'extends' in custom test package '$packageName' must be a string." );
									}
									break;
								case 'root_path':
									if ( ! is_string( $configValue ) ) {
										throw new \RuntimeException( "root_path in custom test package '$packageName' must be a string." );
									}
									break;
								case 'description':
									if ( ! is_string( $configValue ) ) {
										throw new \RuntimeException( "description in custom test package '$packageName' must be a string." );
									}
									break;
								case 'test_command':
									if ( ! is_string( $configValue ) ) {
										throw new \RuntimeException( "test_command in custom test package '$packageName' must be a string." );
									}
									break;
								case 'test_results':
									if ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "test_results in custom test package '$packageName' must be an array." );
									}
									foreach ( $configValue as $resultType => $resultPath ) {
										if ( ! is_string( $resultType ) ) {
											throw new \RuntimeException( "Test result type in '$packageName' must be a string." );
										}
										if ( ! is_string( $resultPath ) ) {
											throw new \RuntimeException( "Test result path for '$resultType' in '$packageName' must be a string." );
										}
									}
									break;
								case 'lifecycle':
									if ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "lifecycle in custom test package '$packageName' must be an array." );
									}
									foreach ( $configValue as $lifecyclePhase => $lifecycleScript ) {
										if ( ! in_array( $lifecyclePhase, [ 'setup', 'teardown' ] ) ) {
											throw new \RuntimeException( "Invalid lifecycle phase '$lifecyclePhase' in '$packageName'. Must be 'setup' or 'teardown'." );
										}
										if ( ! is_string( $lifecycleScript ) ) {
											throw new \RuntimeException( "Lifecycle script for '$lifecyclePhase' in '$packageName' must be a string." );
										}
									}
									break;
								case 'mu_plugins':
									if ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "mu_plugins in custom test package '$packageName' must be an array." );
									}
									foreach ( $configValue as $pluginPath ) {
										if ( ! is_string( $pluginPath ) ) {
											throw new \RuntimeException( "MU plugin path in '$packageName' must be a string." );
										}
									}
									break;
								case 'constraints':
									if ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "constraints in custom test package '$packageName' must be an array." );
									}
									foreach ( $configValue as $constraintType => $constraintValue ) {
										if ( $constraintType === 'wordpress' ) {
											if ( ! is_string( $constraintValue ) ) {
												throw new \RuntimeException( "WordPress constraint in '$packageName' must be a string." );
											}
										} elseif ( $constraintType === 'requires_plugins' ) {
											if ( ! is_array( $constraintValue ) ) {
												throw new \RuntimeException( "requires_plugins in '$packageName' must be an array." );
											}
											foreach ( $constraintValue as $pluginName => $pluginVersion ) {
												if ( ! is_string( $pluginName ) ) {
													throw new \RuntimeException( "Plugin name in requires_plugins for '$packageName' must be a string." );
												}
												if ( ! is_string( $pluginVersion ) ) {
													throw new \RuntimeException( "Plugin version for '$pluginName' in '$packageName' must be a string." );
												}
											}
										} else {
											throw new \RuntimeException( "Unknown constraint type '$constraintType' in '$packageName'." );
										}
									}
									break;
								case 'required_secrets':
									if ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "required_secrets in custom test package '$packageName' must be an array." );
									}
									foreach ( $configValue as $secret ) {
										if ( ! is_string( $secret ) ) {
											throw new \RuntimeException( "Required secret in '$packageName' must be a string." );
										}
									}
									break;
								case 'env_vars':
									if ( ! is_array( $configValue ) ) {
										throw new \RuntimeException( "env_vars in custom test package '$packageName' must be an array." );
									}
									foreach ( $configValue as $varName => $varValue ) {
										if ( ! is_string( $varName ) ) {
											throw new \RuntimeException( "Environment variable name in '$packageName' must be a string." );
										}
										if ( ! is_string( $varValue ) ) {
											throw new \RuntimeException( "Environment variable value for '$varName' in '$packageName' must be a string." );
										}
									}
									break;
								default:
									throw new \RuntimeException( "Unknown key '$configKey' in custom test package '$packageName' configuration." );
							}
						}
					}
					break;
				case 'environments':
					if ( ! is_array( $value ) ) {
						throw new \RuntimeException( 'Environments must be an array.' );
					}
					foreach ( $value as $envName => $config ) {
						if ( ! is_string( $envName ) ) {
							throw new \RuntimeException( 'Environment name must be a string.' );
						}
						if ( ! is_array( $config ) ) {
							throw new \RuntimeException( "Configuration for environment '$envName' must be an array." );
						}
						foreach ( $config as $envKey => $envValue ) {
							switch ( $envKey ) {
								case 'extends':
									if ( ! is_string( $envValue ) ) {
										throw new \RuntimeException( "'extends' in environment '$envName' must be a string." );
									}
									break;
								case 'php_version':
									if ( ! is_string( $envValue ) || ! preg_match( '/^[0-9]+\.[0-9]+(\.[0-9]+)?$/', $envValue ) ) {
										throw new \RuntimeException( "Invalid php_version in environment '$envName'. Must be a valid PHP version string (e.g., '8.2')." );
									}
									break;
								case 'wordpress_version':
									if ( ! is_string( $envValue ) ) {
										throw new \RuntimeException( "wordpress_version in environment '$envName' must be a string." );
									}
									break;
								case 'woocommerce_version':
									if ( ! is_string( $envValue ) ) {
										throw new \RuntimeException( "woocommerce_version in environment '$envName' must be a string." );
									}
									break;
								case 'object_cache':
									if ( ! is_bool( $envValue ) ) {
										throw new \RuntimeException( "object_cache in environment '$envName' must be a boolean." );
									}
									break;
								case 'env_vars':
									if ( ! is_array( $envValue ) ) {
										throw new \RuntimeException( "env_vars in environment '$envName' must be an array." );
									}
									foreach ( $envValue as $varName => $varValue ) {
										if ( ! is_string( $varName ) ) {
											throw new \RuntimeException( "Environment variable name in '$envName' must be a string." );
										}
										if ( ! is_string( $varValue ) ) {
											throw new \RuntimeException( "Environment variable value for '$varName' in '$envName' must be a string." );
										}
									}
									break;
								case 'plugins':
									if ( ! is_array( $envValue ) ) {
										throw new \RuntimeException( "plugins in environment '$envName' must be an array." );
									}
									foreach ( $envValue as $plugin ) {
										if ( ! is_string( $plugin ) ) {
											throw new \RuntimeException( "Plugin in environment '$envName' must be a string." );
										}
									}
									break;
								case 'bootstrap':
									if ( ! is_array( $envValue ) ) {
										throw new \RuntimeException( "bootstrap in environment '$envName' must be an array." );
									}
									foreach ( $envValue as $bootstrapItem ) {
										if ( ! is_string( $bootstrapItem ) ) {
											throw new \RuntimeException( "Bootstrap item in environment '$envName' must be a string." );
										}
									}
									break;
								case 'volumes':
									if ( ! is_array( $envValue ) ) {
										throw new \RuntimeException( "volumes in environment '$envName' must be an array." );
									}
									foreach ( $envValue as $volume ) {
										if ( ! is_string( $volume ) ) {
											throw new \RuntimeException( "Volume in environment '$envName' must be a string." );
										}
									}
									break;
								case 'compatibility':
									if ( ! is_array( $envValue ) ) {
										throw new \RuntimeException( "compatibility in environment '$envName' must be an array." );
									}
									foreach ( $envValue as $compatKey => $compatValue ) {
										if ( ! is_string( $compatKey ) ) {
											throw new \RuntimeException( "Compatibility key in '$envName' must be a string." );
										}
										if ( ! is_string( $compatValue ) ) {
											throw new \RuntimeException( "Compatibility value for '$compatKey' in '$envName' must be a string." );
										}
									}
									break;
								default:
									throw new \RuntimeException( "Unknown key '$envKey' in environment '$envName' configuration." );
							}
						}
					}
					break;
				default:
					break;
			}
		}
	}

	public function get( string $key, $default = null ) {
		return $this->config[ $key ] ?? $default;
	}

	public function getAll(): array {
		return $this->config;
	}

	public function getConfigFile(): string {
		return $this->configFile;
	}

	public function getNestedValue( string $path ): mixed {
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

	private function resolve_extends( array $section, string $name, array $stack = [] ): array {
		if ( in_array( $name, $stack ) ) {
			throw new \RuntimeException( 'Circular dependency detected: ' . implode( ' -> ', $stack ) . " -> $name" );
		}
		$stack[] = $name;

		if ( ! isset( $section[ $name ] ) ) {
			throw new \RuntimeException( "Configuration '$name' not found in section." );
		}

		$config = $section[ $name ];
		if ( isset( $config['extends'] ) ) {
			$base_name = $config['extends'];
			if ( ! is_string( $base_name ) ) {
				throw new \RuntimeException( "Invalid 'extends' value for '$name'. Must be a string." );
			}
			$base_config = $this->resolve_extends( $section, $base_name, $stack );
			unset( $config['extends'] );
			// Replace all keys declared in the extending config, preserving undeclared base config keys
			$config = array_merge( $base_config, $config );
		}

		return $config;
	}

	public function get_environment( string $name ): array {
		$envs = $this->config['environments'] ?? [];

		return $this->resolve_extends( $envs, $name );
	}

	public function get_custom_test_package( string $name ): array {
		$packages = $this->config['custom_test_packages'] ?? [];

		return $this->resolve_extends( $packages, $name );
	}

	public function get_test_config( string $test_type, string $profile ): array {
		$tests = $this->config['tests'] ?? [];
		if ( ! isset( $tests[ $test_type ] ) ) {
			return [];
		}
		$section = $tests[ $test_type ];
		try {
			$resolved_config = $this->resolve_extends( $section, $profile );
		} catch ( \RuntimeException $e ) {
			throw new \RuntimeException( "Error resolving test profile '$test_type:$profile': " . $e->getMessage() );
		}

		if ( isset( $resolved_config['pre_test_build'] ) ) {
			if ( is_string( $resolved_config['pre_test_build'] ) ) {
				$resolved_config['pre_test_build'] = [ 'command' => $resolved_config['pre_test_build'] ];
			} elseif ( ! is_array( $resolved_config['pre_test_build'] ) ) {
				throw new \RuntimeException( "Invalid pre_test_build for '$test_type:$profile'. Must be string or array." );
			}
		} elseif ( isset( $this->config['pre_test_build'] ) ) {
			$resolved_config['pre_test_build'] = $this->config['pre_test_build'];
		}

		if ( isset( $resolved_config['test_matrix'] ) && ! is_array( $resolved_config['test_matrix'] ) ) {
			throw new \RuntimeException( "Invalid test_matrix for '$test_type:$profile'. Must be an array." );
		}

		return $resolved_config;
	}

	public function get_group( string $group_name ): array {
		return $this->config['groups'][ $group_name ] ?? [];
	}

	public function get_group_tests( string $group_name ): array {
		$group = $this->config['groups'][ $group_name ] ?? [];
		if ( ! is_array( $group ) ) {
			throw new \RuntimeException( "Group '$group_name' must be an array." );
		}
		if ( empty( $group ) ) {
			throw new \RuntimeException( "Test references for group '$group_name' cannot be empty." );
		}
		$tests    = [];
		$seenRefs = [];
		foreach ( $group as $testRef ) {
			if ( ! is_string( $testRef ) || ! preg_match( '/^[a-zA-Z0-9_-]+:[a-zA-Z0-9_-]+$/', $testRef ) ) {
				throw new \RuntimeException( "Invalid test reference '$testRef' in group '$group_name'. Expected 'type:profile'." );
			}
			if ( in_array( $testRef, $seenRefs ) ) {
				throw new \RuntimeException( "Duplicate test reference '$testRef' in group '$group_name'." );
			}
			$seenRefs[] = $testRef;
			[ $type, $profile ] = explode( ':', $testRef, 2 );
			if ( ! isset( $this->config['tests'][ $type ] ) ) {
				throw new \RuntimeException( "Test type '$type' from reference '$testRef' in group '$group_name' not found in tests configuration." );
			}
			if ( ! isset( $this->config['tests'][ $type ][ $profile ] ) ) {
				throw new \RuntimeException( "Test profile '$profile' from reference '$testRef' in group '$group_name' not found in tests configuration." );
			}
			$tests[] = [
				'type'    => $type,
				'profile' => $profile,
				'config'  => $this->get_test_config( $type, $profile ),
			];
		}

		return $tests;
	}

	public function get_test_matrix( string $test_type, string $profile ): array {
		$test_config = $this->get_test_config( $test_type, $profile );
		$matrix      = $test_config['test_matrix'] ?? [];
		if ( ! is_array( $matrix ) ) {
			throw new \RuntimeException( "Invalid test_matrix for '$test_type:$profile'. Must be an array." );
		}

		return $matrix;
	}
}