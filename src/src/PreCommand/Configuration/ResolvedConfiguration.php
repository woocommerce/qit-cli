<?php

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\Environment\Extension;

/**
 * Represents a fully resolved configuration with all dependencies and packages loaded
 */
class ResolvedConfiguration {
	/** @var array The parsed qit.json configuration */
	public array $raw_config;

	/** @var array System Under Test information */
	public array $sut;

	/** @var Extension The resolved SUT extension object */
	public Extension $sut_extension;

	/** @var array<string, array> Resolved environments with extends applied */
	public array $environments = [];

	/** @var array<string, array<string, array>> Test types with profiles */
	public array $test_types = [];

	/** @var array<string, array> Test groups */
	public array $groups = [];

	/** @var array<string, array> Loaded test package manifests */
	public array $test_packages = [];

	/** @var array<Extension> All resolved plugins (including dependencies) */
	public array $resolved_plugins = [];

	/** @var array<Extension> All resolved themes (including dependencies) */
	public array $resolved_themes = [];

	/** @var array<string> PHP extensions required */
	public array $php_extensions = [];

	/** @var array<string> Required secrets */
	public array $required_secrets = [];

	/** @var array<string> Required external services */
	public array $required_services = [];

	/** @var string Cache directory path */
	public string $cache_dir;

	/** @var array<string, string> Global environment variables */
	public array $global_env_vars = [];

	/** @var array Metadata about the configuration */
	public array $metadata = [
		'config_file' => '',
		'resolved_at' => '',
		'qit_version' => '',
		'php_version' => PHP_VERSION,
	];

	public function __construct( array $raw_config ) {
		$this->raw_config              = $raw_config;
		$this->metadata['resolved_at'] = date( 'Y-m-d H:i:s' );
		$this->metadata['qit_version'] = \QIT_CLI\App::getVar( 'CLI_VERSION' );
	}

	/**
	 * Get a specific environment configuration
	 */
	public function get_environment( string $name ): array {
		if ( ! isset( $this->environments[ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found in resolved configuration" );
		}

		return $this->environments[ $name ];
	}

	/**
	 * Get test configuration for a specific type and profile
	 */
	public function get_test_config( string $type, string $profile ): array {
		if ( ! isset( $this->test_types[ $type ][ $profile ] ) ) {
			throw new \RuntimeException( "Test configuration '$type:$profile' not found" );
		}

		return $this->test_types[ $type ][ $profile ];
	}

	/**
	 * Get all test packages for a test configuration
	 */
	public function get_test_packages_for_config( string $type, string $profile ): array {
		$config   = $this->get_test_config( $type, $profile );
		$packages = [];

		if ( isset( $config['test_packages'] ) ) {
			foreach ( $config['test_packages'] as $ref ) {
				if ( isset( $this->test_packages[ $ref ] ) ) {
					$packages[ $ref ] = $this->test_packages[ $ref ];
				}
			}
		}

		return $packages;
	}

	/**
	 * Get all plugins for an environment (including base plugins and test-specific)
	 */
	public function get_plugins_for_environment( string $env_name ): array {
		$env     = $this->get_environment( $env_name );
		$plugins = [];

		// Start with resolved plugins
		foreach ( $this->resolved_plugins as $plugin ) {
			$plugins[ $plugin->slug ] = $plugin;
		}

		// Add environment-specific plugins
		if ( isset( $env['plugins'] ) ) {
			foreach ( $env['plugins'] as $plugin_config ) {
				$slug = $plugin_config['slug'];
				if ( ! isset( $plugins[ $slug ] ) ) {
					// This would be resolved during environment setup
					$plugin           = new Extension( $slug, 'plugin' );
					$plugin->source   = $plugin_config['source'] ?? null;
					$plugins[ $slug ] = $plugin;
				}
			}
		}

		return array_values( $plugins );
	}

	/**
	 * Check if configuration requires specific secrets
	 */
	public function requires_secrets(): bool {
		return ! empty( $this->required_secrets );
	}

	/**
	 * Get all required secrets
	 */
	public function get_required_secrets(): array {
		return array_unique( $this->required_secrets );
	}

	/**
	 * Check if configuration requires external services
	 */
	public function requires_external_services(): bool {
		return ! empty( $this->required_services );
	}

	/**
	 * Get test package by reference
	 */
	public function get_test_package( string $reference ): ?array {
		return $this->test_packages[ $reference ] ?? null;
	}

	/**
	 * Get setup-only packages for an environment
	 */
	public function get_setup_packages_for_environment( string $env_name ): array {
		$env      = $this->get_environment( $env_name );
		$packages = [];

		if ( isset( $env['setup_only'] ) ) {
			foreach ( $env['setup_only'] as $ref ) {
				if ( isset( $this->test_packages[ $ref ] ) ) {
					$packages[ $ref ] = $this->test_packages[ $ref ];
				}
			}
		}

		return $packages;
	}

	/**
	 * Get all environment names
	 */
	public function get_environment_names(): array {
		return array_keys( $this->environments );
	}

	/**
	 * Get all test type names
	 */
	public function get_test_type_names(): array {
		return array_keys( $this->test_types );
	}

	/**
	 * Get all profiles for a test type
	 */
	public function get_profiles_for_test_type( string $type ): array {
		return isset( $this->test_types[ $type ] ) ? array_keys( $this->test_types[ $type ] ) : [];
	}

	/**
	 * Get all group names
	 */
	public function get_group_names(): array {
		return array_keys( $this->groups );
	}

	/**
	 * Get tests in a group
	 */
	public function get_tests_in_group( string $group ): array {
		return $this->groups[ $group ] ?? [];
	}

	/**
	 * Check if a test package is local
	 */
	public function is_local_package( string $reference ): bool {
		$package = $this->get_test_package( $reference );

		return $package && ( $package['local'] ?? false );
	}

	/**
	 * Get all unique PHP extensions required
	 */
	public function get_all_php_extensions(): array {
		$extensions = $this->php_extensions;

		// Add PHP extensions from environments
		foreach ( $this->environments as $env ) {
			if ( isset( $env['php_extensions'] ) ) {
				$extensions = array_merge( $extensions, $env['php_extensions'] );
			}
		}

		return array_unique( $extensions );
	}

	/**
	 * Validate that all requirements are met
	 */
	public function validate(): array {
		$errors = [];

		// Validate SUT
		if ( empty( $this->sut ) ) {
			$errors[] = 'No System Under Test (SUT) defined';
		}

		// Validate environments
		if ( empty( $this->environments ) ) {
			$errors[] = 'No environments defined';
		}

		// Validate test types reference existing environments
		foreach ( $this->test_types as $type => $profiles ) {
			foreach ( $profiles as $profile => $config ) {
				if ( isset( $config['environment'] ) && ! isset( $this->environments[ $config['environment'] ] ) ) {
					$errors[] = "Test '$type:$profile' references undefined environment '{$config['environment']}'";
				}
			}
		}

		// Validate groups reference existing test types
		foreach ( $this->groups as $group => $tests ) {
			foreach ( $tests as $test_type => $profiles ) {
				if ( ! isset( $this->test_types[ $test_type ] ) ) {
					$errors[] = "Group '$group' references undefined test type '$test_type'";
				}
				foreach ( $profiles as $profile ) {
					if ( ! isset( $this->test_types[ $test_type ][ $profile ] ) ) {
						$errors[] = "Group '$group' references undefined profile '$test_type:$profile'";
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Export configuration for caching
	 */
	public function export(): array {
		return [
			'metadata'          => $this->metadata,
			'sut'               => $this->sut,
			'environments'      => $this->environments,
			'test_types'        => $this->test_types,
			'groups'            => $this->groups,
			'test_packages'     => $this->test_packages,
			'resolved_plugins'  => array_map( fn( $p ) => (array) $p, $this->resolved_plugins ),
			'resolved_themes'   => array_map( fn( $t ) => (array) $t, $this->resolved_themes ),
			'php_extensions'    => $this->php_extensions,
			'required_secrets'  => $this->required_secrets,
			'required_services' => $this->required_services,
		];
	}

	/**
	 * Import configuration from cache
	 */
	public static function import( array $data ): self {
		$config = new self( $data['raw_config'] ?? [] );

		$config->metadata          = $data['metadata'] ?? [];
		$config->sut               = $data['sut'] ?? [];
		$config->environments      = $data['environments'] ?? [];
		$config->test_types        = $data['test_types'] ?? [];
		$config->groups            = $data['groups'] ?? [];
		$config->test_packages     = $data['test_packages'] ?? [];
		$config->php_extensions    = $data['php_extensions'] ?? [];
		$config->required_secrets  = $data['required_secrets'] ?? [];
		$config->required_services = $data['required_services'] ?? [];

		// Recreate Extension objects
		if ( isset( $data['resolved_plugins'] ) ) {
			foreach ( $data['resolved_plugins'] as $plugin_data ) {
				$plugin = new Extension( $plugin_data['slug'], 'plugin' );
				foreach ( $plugin_data as $key => $value ) {
					if ( property_exists( $plugin, $key ) ) {
						$plugin->$key = $value;
					}
				}
				$config->resolved_plugins[] = $plugin;
			}
		}

		if ( isset( $data['resolved_themes'] ) ) {
			foreach ( $data['resolved_themes'] as $theme_data ) {
				$theme = new Extension( $theme_data['slug'], 'theme' );
				foreach ( $theme_data as $key => $value ) {
					if ( property_exists( $theme, $key ) ) {
						$theme->$key = $value;
					}
				}
				$config->resolved_themes[] = $theme;
			}
		}

		return $config;
	}
}