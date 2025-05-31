<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class CustomTestPackageParser extends AbstractConfigParser {
	protected string $root_path;

	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) || empty( $value ) ) {
			throw new \RuntimeException( 'Test packages must be an array of package definitions.' );
		}

		$this->root_path = $context['root_path'] ?? getcwd();
		$packages        = [];

		foreach ( $value as $index => $package ) {
			if ( ! isset( $package['type'], $package['name'], $package['file'] ) ||
			     ! is_string( $package['type'] ) || ! is_string( $package['name'] ) || ! is_string( $package['file'] ) ) {
				throw new \RuntimeException( "Test package at index $index must have 'type', 'name', and 'file' as strings." );
			}

			if ( isset( $package['extends'] ) && ! is_string( $package['extends'] ) ) {
				throw new \RuntimeException( "Extends for test package '{$package['type']}:{$package['name']}' must be a string." );
			}

			$test_type         = $package['type'];
			$package_name      = $package['name'];
			$file_path         = $this->root_path . DIRECTORY_SEPARATOR . $package['file'];
			$file_dir          = dirname( $file_path );
			$file_dir_relative = str_replace( $this->root_path . DIRECTORY_SEPARATOR, '', $file_dir ) . DIRECTORY_SEPARATOR;

			if ( ! file_exists( $file_path ) ) {
				throw new \RuntimeException( "Test package file '$file_path' for '{$test_type}:{$package_name}' not found." );
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
					if (is_bool($val)) {
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
							$path = ltrim( $script['command'], './' );
							if ( strpos( $path, $file_dir_relative ) === 0 ) {
								$path = substr( $path, strlen( $file_dir_relative ) );
							}
							$script['command'] = $path;
						}
					}

					// Sort scripts by priority
					usort( $scripts, function ( $a, $b ) {
						$priority_a = $a['priority'] ?? 0;
						$priority_b = $b['priority'] ?? 0;
						return $priority_a <=> $priority_b;
					} );
				}
			}

			if ( isset( $config['mu_plugins'] ) ) {
				foreach ( $config['mu_plugins'] as &$plugin ) {
					$plugin = ltrim( $plugin, './' );
					if ( strpos( $plugin, $file_dir_relative ) === 0 ) {
						$plugin = substr( $plugin, strlen( $file_dir_relative ) );
					}
				}
			}

			if ( isset( $config['test_results'] ) ) {
				foreach ( $config['test_results'] as &$result ) {
					$result = ltrim( $result, './' );
					if ( strpos( $result, $file_dir_relative ) === 0 ) {
						$result = substr( $result, strlen( $file_dir_relative ) );
					}
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

		foreach ( $section as $test_type => $package_list ) {
			$resolved[ $test_type ] = [];
			foreach ( $package_list as $package_name => $data ) {
				$resolved[ $test_type ][ $package_name ] = $this->resolve_package_extends(
					$data['config'],
					$data['extends'],
					$test_type,
					$package_name,
					$section,
					[]
				);
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

		$merged = $this->merge_configs( $base_config, $config );

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

	protected function merge_configs( array $base, array $child ): array {
		$simple_fields = [ 'test_command', 'description' ];
		$merged        = $base;

		foreach ( $simple_fields as $field ) {
			if ( isset( $child[ $field ] ) ) {
				if ( $field === 'test_command' && ! is_string( $child[ $field ] ) ) {
					throw new \RuntimeException( "Test command must be a string." );
				}
				$merged[ $field ] = $child[ $field ];
			}
		}

		$complex_fields = [ 'lifecycle', 'env_vars', 'test_results', 'mu_plugins', 'required_secrets' ];
		foreach ( $complex_fields as $field ) {
			if ( isset( $child[ $field ] ) ) {
				if ( $field === 'env_vars' && ! is_array( $child[ $field ] ) ) {
					throw new \RuntimeException( "Environment variables must be an array." );
				}
				if ( $field === 'env_vars' && is_array( $child[ $field ] ) ) {
					foreach ( $child[ $field ] as $key => $value ) {
						if (is_bool($value)) {
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

	public function get_resolved_package( string $test_type, string $package_name, array $packages, string $root_path ): array {
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
