<?php

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Represents a fully resolved configuration with all extends processed,
 * dependencies resolved, and test packages loaded.
 */
class ResolvedConfiguration {
	/** @var array<string,mixed>|null Existing properties. */
	public ?array $sut               = null;
	public ?Extension $sut_extension = null;
	/** @var array<string,array<string,mixed>> */
	public array $environments = [];
	/** @var array<string,array<string,mixed>> */
	public array $test_types = [];
	/** @var array<string,array<string,mixed>> */
	public array $groups = [];
	/** @var array<string,TestPackageManifest> */
	public array $test_packages = [];
	/** @var array<string,array<string,mixed>> Package metadata (reference, local, remote, path, etc.) */
	public array $test_package_metadata = [];
	/** @var array<string,Extension> */
	public array $resolved_plugins = [];
	/** @var array<string,Extension> */
	public array $resolved_themes = [];
	/** @var string[] */
	public array $php_extensions = [];
	/** @var string[] */
	public array $required_secrets = [];
	/** @var string[] */
	public array $required_services = [];
	/** @var array<string,mixed> */
	public array $metadata   = [];
	public string $cache_dir = '';

	/** @var array<string,mixed> */
	protected array $raw_config;

	/**
	 * @param array<string,mixed> $raw_config
	 */
	public function __construct( array $raw_config ) {
		$this->raw_config = $raw_config;
	}

	/** @var array<string,array<string,mixed>> */
	private array $env_cache = [];
	/** @var array<string,array<string,mixed>> */
	private array $profile_cache = [];

	/**
	 * Return env config with all `extends` ancestors recursively merged.
	 * Result is memoised per env name.
	 *
	 * @return array<string,mixed>
	 */
	public function get_environment( string $environment ): array {
		if ( isset( $this->env_cache[ $environment ] ) ) {
			return $this->env_cache[ $environment ];
		}

		$raw = $this->environments[ $environment ] ?? [];
		if ( empty( $raw ) ) {
			return [];
		}

		$merged                          = $this->resolve_nested_extends( $raw, $this->environments );
		$this->env_cache[ $environment ] = $merged;
		return $this->env_cache[ $environment ];
	}

	/**
	 * Get test configuration for a given test type and profile with inheritance resolved.
	 *
	 * @return array<string,mixed>
	 */
	public function get_test_config( string $test_type, string $profile ): array {
		$key = "$test_type:$profile";
		if ( isset( $this->profile_cache[ $key ] ) ) {
			return $this->profile_cache[ $key ];
		}

		$raw = $this->test_types[ $test_type ][ $profile ] ?? [];
		if ( empty( $raw ) ) {
			return [];
		}

		// For test profiles, we need to look in the same test_type for extends
		$context                     = $this->test_types[ $test_type ] ?? [];
		$merged                      = $this->resolve_nested_extends( $raw, $context );
		$this->profile_cache[ $key ] = $merged;
		return $this->profile_cache[ $key ];
	}

	/**
	 * Recursively merge `extends` chain; pure helper
	 *
	 * @param array<string,mixed>               $node
	 * @param array<string,array<string,mixed>> $context
	 * @return array<string,mixed>
	 */
	private function resolve_nested_extends( array $node, array $context ): array {
		if ( ! isset( $node['extends'] ) ) {
			return $node;
		}

		$parent_name = $node['extends'];
		$parent      = $context[ $parent_name ] ?? null;

		if ( $parent === null ) {
			throw new \RuntimeException( "Parent '$parent_name' not found in extends chain" );
		}

		// Remove extends from current node to avoid infinite recursion
		unset( $node['extends'] );

		// Recursively resolve parent and merge with current node
		$resolved_parent = $this->resolve_nested_extends( $parent, $context );
		return $this->deep_merge( $resolved_parent, $node );
	}

	/**
	 * Deep merge two arrays with special handling for certain keys
	 *
	 * @param array<string,mixed> $base
	 * @param array<string,mixed> $override
	 * @return array<string,mixed>
	 */
	private function deep_merge( array $base, array $override ): array {
		$merged = $base;

		foreach ( $override as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				// Keys that should be merged and deduplicated for extends inheritance
				$merge_keys = [ 'plugins', 'themes', 'volumes', 'php_extensions' ];
				// Keys that should replace rather than merge
				$replace_keys = [ 'envs', 'secrets', 'test_packages' ];

				if ( in_array( $key, $merge_keys, true ) ) {
					// Merge and deduplicate arrays for list options
					$merged[ $key ] = array_values( array_unique( array_merge( $merged[ $key ], $value ) ) );
				} elseif ( in_array( $key, $replace_keys, true ) ) {
					$merged[ $key ] = $value;
				} else {
					$merged[ $key ] = $this->deep_merge( $merged[ $key ], $value );
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Get a specific test package by reference
	 */
	public function get_test_package( string $reference ): TestPackageManifest {
		if ( ! isset( $this->test_packages[ $reference ] ) ) {
			throw new \RuntimeException( "Test package '$reference' not found" );
		}

		return $this->test_packages[ $reference ];
	}

	/**
	 * Get all test packages
	 *
	 * @return array<string,TestPackageManifest>
	 */
	public function get_all_test_packages(): array {
		return $this->test_packages;
	}

	/**
	 * Get all test packages for a specific test configuration
	 *
	 * @return array<string,TestPackageManifest>
	 */
	public function get_test_packages_for_config( string $test_type, string $profile ): array {
		$config = $this->get_test_config( $test_type, $profile );

		if ( empty( $config['test_packages'] ) ) {
			return [];
		}

		$packages = [];
		foreach ( $config['test_packages'] as $ref ) {
			$packages[ $ref ] = $this->get_test_package( $ref );
		}

		return $packages;
	}

	/**
	 * Check if a test package is local based on reference format
	 */
	public function is_local_package( string $reference ): bool {
		// Check for local/ prefix (new format)
		if ( strpos( $reference, 'local/' ) === 0 ) {
			return true;
		}

		// Check for file paths
		if ( strpos( $reference, '/' ) !== false && strpos( $reference, ':' ) === false ) {
			return true;
		}

		// Check for .json extension
		if ( substr( $reference, - 5 ) === '.json' ) {
			return true;
		}

		// Fallback to stored metadata
		return $this->test_package_metadata[ $reference ]['local'] ?? false;
	}

	/**
	 * Get all resolved plugins
	 *
	 * @return array<string,Extension>
	 */
	public function get_all_plugins(): array {
		return $this->resolved_plugins;
	}

	/**
	 * Get all resolved themes
	 *
	 * @return array<string,Extension>
	 */
	public function get_all_themes(): array {
		return $this->resolved_themes;
	}

	/**
	 * Get all PHP extensions required
	 *
	 * @return string[]
	 */
	public function get_all_php_extensions(): array {
		return array_unique( $this->php_extensions );
	}

	/**
	 * Get required secrets
	 *
	 * @return string[]
	 */
	public function get_required_secrets(): array {
		return array_unique( $this->required_secrets );
	}

	/**
	 * Get required external services
	 *
	 * @return string[]
	 */
	public function get_required_services(): array {
		return array_unique( $this->required_services );
	}

	/**
	 * Check if configuration requires secrets
	 */
	public function requires_secrets(): bool {
		return ! empty( $this->required_secrets );
	}

	/**
	 * Check if configuration requires external services
	 */
	public function requires_external_services(): bool {
		return ! empty( $this->required_services );
	}

	/**
	 * Validate the configuration
	 *
	 * @return string[]
	 */
	public function validate(): array {
		$errors = [];

		// SUT is only required if test types are defined and actually used for test execution
		// For configuration precedence testing, test_types can be defined without SUT
		if ( ! empty( $this->test_types ) && empty( $this->sut ) ) {
			// Only require SUT if test types reference test packages (indicating actual test execution)
			$requires_sut = false;
			foreach ( $this->test_types as $type => $profiles ) {
				foreach ( $profiles as $profile => $config ) {
					if ( ! empty( $config['test_packages'] ) ) {
						$requires_sut = true;
						break 2;
					}
				}
			}

			if ( $requires_sut ) {
				$errors[] = 'System Under Test (SUT) is required when test types define test packages';
			}
		}

		// Validate environments exist for test configs that reference them
		foreach ( $this->test_types as $type => $profiles ) {
			foreach ( $profiles as $profile => $config ) {
				if ( isset( $config['environment'] ) && ! isset( $this->environments[ $config['environment'] ] ) ) {
					$errors[] = "Test '$type:$profile' references non-existent environment '{$config['environment']}'";
				}
			}
		}

		// Validate groups reference existing test types
		foreach ( $this->groups as $group => $tests ) {
			foreach ( $tests as $test_type => $profiles ) {
				if ( ! isset( $this->test_types[ $test_type ] ) ) {
					$errors[] = "Group '$group' references non-existent test type '$test_type'";
				}
				foreach ( $profiles as $profile ) {
					if ( ! isset( $this->test_types[ $test_type ][ $profile ] ) ) {
						$errors[] = "Group '$group' references non-existent profile '$test_type:$profile'";
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Export configuration for caching
	 *
	 * @return array<string,mixed>
	 */
	public function export(): array {
		return [
			'sut'                   => $this->sut,
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Used for object caching
			'sut_extension'         => serialize( $this->sut_extension ),
			'environments'          => $this->environments,
			'test_types'            => $this->test_types,
			'groups'                => $this->groups,
			'test_packages'         => array_map( static fn ( TestPackageManifest $m ) => $m->jsonSerialize(), $this->test_packages ),
			'test_package_metadata' => $this->test_package_metadata,
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Used for object caching
			'resolved_plugins'      => array_map( 'serialize', $this->resolved_plugins ),
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Used for object caching
			'resolved_themes'       => array_map( 'serialize', $this->resolved_themes ),
			'required_secrets'      => $this->required_secrets,
			'required_services'     => $this->required_services,
			'php_extensions'        => $this->php_extensions,
			'metadata'              => $this->metadata,
			'raw_config'            => $this->raw_config,
		];
	}

	/**
	 * Import configuration from cache
	 *
	 * @param array<string,mixed> $data
	 */
	public static function import( array $data ): self {
		$config = new self( $data );

		$config->sut = $data['sut'];
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- Used for object caching
		$config->sut_extension         = unserialize( $data['sut_extension'] );
		$config->environments          = $data['environments'];
		$config->test_types            = $data['test_types'];
		$config->groups                = $data['groups'];
		$config->test_packages         = array_map( static fn ( array $a ) => new TestPackageManifest( $a ), $data['test_packages'] );
		$config->test_package_metadata = $data['test_package_metadata'] ?? [];
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- Used for object caching
		$config->resolved_plugins = array_map( 'unserialize', $data['resolved_plugins'] );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- Used for object caching
		$config->resolved_themes   = array_map( 'unserialize', $data['resolved_themes'] );
		$config->required_secrets  = $data['required_secrets'];
		$config->required_services = $data['required_services'];
		$config->php_extensions    = $data['php_extensions'];
		$config->metadata          = $data['metadata'];

		return $config;
	}
}
