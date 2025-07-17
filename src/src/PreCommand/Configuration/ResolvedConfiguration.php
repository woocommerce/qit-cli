<?php

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\Environment\Extension;

/**
 * Represents a fully resolved configuration with all extends processed,
 * dependencies resolved, and test packages loaded.
 */
class ResolvedConfiguration {
	// Existing properties
	public ?array $sut               = null;
	public ?Extension $sut_extension = null;
	public array $environments       = [];
	public array $test_types         = [];
	public array $groups             = [];
	public array $test_packages      = [];
	public array $resolved_plugins   = [];
	public array $resolved_themes    = [];
	public array $php_extensions     = [];
	public array $required_secrets   = [];
	public array $required_services  = [];
	public array $metadata           = [];
	public string $cache_dir         = '';

	protected array $raw_config;

	public function __construct( array $raw_config ) {
		$this->raw_config = $raw_config;
	}

	/**
	 * Get environment configuration by name
	 */
	public function get_environment( string $name ): array {
		if ( ! isset( $this->environments[ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found in configuration" );
		}

		return $this->environments[ $name ];
	}

	/**
	 * Get test configuration for a specific type and profile
	 */
	public function get_test_config( string $test_type, string $profile ): array {
		if ( ! isset( $this->test_types[ $test_type ] ) ) {
			throw new \RuntimeException( "Test type '$test_type' not found in configuration" );
		}

		if ( ! isset( $this->test_types[ $test_type ][ $profile ] ) ) {
			throw new \RuntimeException( "Profile '$profile' not found for test type '$test_type'" );
		}

		return $this->test_types[ $test_type ][ $profile ];
	}

	/**
	 * Get a specific test package by reference
	 */
	public function get_test_package( string $reference ): array {
		if ( ! isset( $this->test_packages[ $reference ] ) ) {
			throw new \RuntimeException( "Test package '$reference' not found" );
		}

		return $this->test_packages[ $reference ];
	}

	/**
	 * Get all test packages for a specific test configuration
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
	 * Check if a test package is local
	 */
	public function is_local_package( string $reference ): bool {
		$package = $this->get_test_package( $reference );

		return ( $package['local'] ?? false ) || ( $package['remote'] ?? true ) === false;
	}

	/**
	 * Get all resolved plugins
	 */
	public function get_all_plugins(): array {
		return $this->resolved_plugins;
	}

	/**
	 * Get all resolved themes
	 */
	public function get_all_themes(): array {
		return $this->resolved_themes;
	}

	/**
	 * Get all PHP extensions required
	 */
	public function get_all_php_extensions(): array {
		return array_unique( $this->php_extensions );
	}

	/**
	 * Get required secrets
	 */
	public function get_required_secrets(): array {
		return array_unique( $this->required_secrets );
	}

	/**
	 * Get required external services
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
	 */
	public function validate(): array {
		$errors = [];

		// SUT is only required if test types are defined
		if ( ! empty( $this->test_types ) && empty( $this->sut ) ) {
			$errors[] = 'System Under Test (SUT) is required when test types are defined';
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
	 */
	public function export(): array {
		return [
			'sut'               => $this->sut,
			'sut_extension'     => serialize( $this->sut_extension ),
			'environments'      => $this->environments,
			'test_types'        => $this->test_types,
			'groups'            => $this->groups,
			'test_packages'     => $this->test_packages,
			'resolved_plugins'  => array_map( 'serialize', $this->resolved_plugins ),
			'resolved_themes'   => array_map( 'serialize', $this->resolved_themes ),
			'required_secrets'  => $this->required_secrets,
			'required_services' => $this->required_services,
			'php_extensions'    => $this->php_extensions,
			'metadata'          => $this->metadata,
			'cache_dir'         => $this->cache_dir,
		];
	}

	/**
	 * Import configuration from cache
	 */
	public static function import( array $data ): self {
		$config = new self( $data );

		$config->sut               = $data['sut'];
		$config->sut_extension     = unserialize( $data['sut_extension'] );
		$config->environments      = $data['environments'];
		$config->test_types        = $data['test_types'];
		$config->groups            = $data['groups'];
		$config->test_packages     = $data['test_packages'];
		$config->resolved_plugins  = array_map( 'unserialize', $data['resolved_plugins'] );
		$config->resolved_themes   = array_map( 'unserialize', $data['resolved_themes'] );
		$config->required_secrets  = $data['required_secrets'];
		$config->required_services = $data['required_services'];
		$config->php_extensions    = $data['php_extensions'];
		$config->metadata          = $data['metadata'];
		$config->cache_dir         = $data['cache_dir'];

		return $config;
	}
}
