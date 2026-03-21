<?php

namespace QIT_CLI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * Smart input wrapper that understands QIT configuration system.
 *
 * This class encapsulates all configuration resolution logic, including:
 * - qit.json file parsing and inheritance
 * - CLI option precedence
 * - Test profile resolution
 * - Environment configuration merging
 */
class QITInput implements InputInterface {
	private InputInterface $symfony_input;
	/** @var array<string,scalar|array<string,scalar|array<string,scalar>>> */
	private array $resolved_config;
	/** @var array<string,string|array<string>>|null */
	private ?array $current_test_profile = null;
	/** @var array<string,string|bool|array<string>>|null */
	private ?array $current_environment_config = null;
	private string $test_type;

	/**
	 * @param InputInterface                                                 $input The raw Symfony input.
	 * @param array<string,scalar|array<string,scalar|array<string,scalar>>> $resolved_config The resolved configuration from qit.json.
	 * @param string                                                         $test_type The test type (e2e, activation, etc.).
	 */
	public function __construct( InputInterface $input, array $resolved_config, string $test_type = 'e2e' ) {
		$this->symfony_input   = $input;
		$this->resolved_config = $resolved_config;
		$this->test_type       = $test_type;
	}

	/**
	 * @return array<string,scalar|array<string,scalar|array<string,scalar>>>
	 */
	public function get_resolved_config(): array {
		return $this->resolved_config;
	}

	/**
	 * Get environment name with proper precedence:
	 * 1. CLI --environment flag
	 * 2. Test profile's environment setting
	 * 3. Default 'default'
	 */
	public function get_environment(): string {
		// CLI flag takes precedence
		if ( $this->hasOption( 'environment' ) ) {
			return $this->getOption( 'environment' );
		}

		// Check test profile for environment setting
		$profile = $this->get_test_profile();
		if ( isset( $profile['environment'] ) ) {
			return $profile['environment'];
		}

		return 'default';
	}

	/**
	 * Get the current test profile name.
	 */
	public function get_profile_name(): string {
		if ( $this->hasOption( 'profile' ) ) {
			return $this->getOption( 'profile' );
		}

		// If test type doesn't exist in config, return 'default'
		if ( ! isset( $this->resolved_config['test_types'][ $this->test_type ] ) ) {
			return 'default';
		}

		// Check if there's only one profile for this test type
		$profiles = $this->resolved_config['test_types'][ $this->test_type ] ?? [];
		if ( count( $profiles ) === 1 ) {
			return array_key_first( $profiles );
		}

		return 'default';
	}

	/**
	 * Get resolved test profile configuration.
	 *
	 * @return array<string,string|array<string>>
	 */
	public function get_test_profile(): array {
		if ( $this->current_test_profile === null ) {
			// If test type doesn't exist in config, return empty array
			// This allows commands to work without configuration when packages are provided explicitly
			if ( ! isset( $this->resolved_config['test_types'][ $this->test_type ] ) ) {
				$this->current_test_profile = [];
			} else {
				$profile_name = $this->get_profile_name();

				// Check if the profile exists for this test type
				if ( ! isset( $this->resolved_config['test_types'][ $this->test_type ][ $profile_name ] ) ) {
					// Get available profiles for error message
					$available_profiles = array_keys( $this->resolved_config['test_types'][ $this->test_type ] );

					// Only throw error if profile was explicitly requested via CLI and is not 'default'
					// We allow 'default' to not exist for backward compatibility
					if ( $this->hasOption( 'profile' ) && $this->getOption( 'profile' ) === $profile_name && $profile_name !== 'default' ) {
						$available_list = empty( $available_profiles )
							? 'No profiles are defined for this test type'
							: 'Available profiles: ' . implode( ', ', $available_profiles );

						throw new \InvalidArgumentException(
							"Profile '{$profile_name}' does not exist for test type '{$this->test_type}'. {$available_list}"
						);
					}
				}

				$this->current_test_profile = \QIT_CLI\PreCommand\Configuration\EnvironmentConfigResolver::normalize_aliases(
					$this->resolved_config['test_types'][ $this->test_type ][ $profile_name ] ?? []
				);
			}
		}

		return $this->current_test_profile;
	}

	/**
	 * Get fully resolved environment configuration.
	 * This includes all inheritance, CLI overrides, and special resolution.
	 *
	 * @return array<string,string|bool|array<string>>
	 */
	public function get_environment_config(): array {
		if ( $this->current_environment_config === null ) {
			$env_name = $this->get_environment();
			$config   = $this->resolved_config['environments'][ $env_name ] ?? [];

			// Apply CLI overrides - but we don't do this here anymore!
			// This should be handled by env:up when we pass options to it

			$this->current_environment_config = $config;
		}

		return $this->current_environment_config;
	}

	/**
	 * Get test packages with proper merging of profile and CLI options.
	 *
	 * @return array<string>
	 */
	public function get_test_packages(): array {
		$profile  = $this->get_test_profile();
		$packages = $profile['test_packages'] ?? [];

		// Add CLI test packages if provided (or programmatically set)
		$test_package_option = $this->getOption( 'test-package' );
		if ( ! empty( $test_package_option ) ) {
			$cli_packages = (array) $test_package_option;
			$packages     = array_unique( array_merge( $packages, $cli_packages ) );
		}

		return $packages;
	}

	/**
	 * Get SUT (System Under Test) information.
	 *
	 * @return array<string,string>|null
	 */
	public function get_sut(): ?array {
		// CLI argument takes precedence
		$sut_arg = $this->getArgument( 'sut' );
		if ( $sut_arg ) {
			return [ 'slug' => $sut_arg ];
		}

		// Check test profile
		$profile = $this->get_test_profile();
		if ( isset( $profile['sut'] ) && is_array( $profile['sut'] ) ) {
			// Ensure it's a flat array of strings
			$sut = [];
			foreach ( $profile['sut'] as $key => $value ) {
				if ( is_string( $key ) && is_string( $value ) ) {
					$sut[ $key ] = $value;
				}
			}
			return $sut;
		}

		// Check global config
		if ( isset( $this->resolved_config['sut'] ) && is_array( $this->resolved_config['sut'] ) ) {
			// Ensure it's a flat array of strings
			$sut = [];
			foreach ( $this->resolved_config['sut'] as $key => $value ) {
				if ( is_string( $key ) && is_string( $value ) ) {
					$sut[ $key ] = $value;
				}
			}
			return $sut;
		}

		return null;
	}

	/**
	 * Get all environment-related options formatted for env:up command.
	 * This is what RunE2E passes to EnvironmentRunner.
	 *
	 * @return array<string,string|bool|array<string>>
	 */
	public function get_environment_options(): array {
		$options = [];

		// List of options that env:up understands
		$env_up_options = [
			'config',
			'environment',
			'php_version',
			'wordpress_version',
			'woocommerce_version',
			'plugin',
			'theme',
			'volume',
			'php_extension',
			'object_cache',
			'tunnel',
			'env',
			'env_file',
			'skip_activating_plugins',
			'skip_activating_themes',
			'json',
		];

		// Profile values that map to env:up options (profile key => env:up option name).
		// These are injected as defaults — CLI flags override them.
		$profile_to_env_up = [
			'php_version'         => 'php_version',
			'wordpress_version'   => 'wordpress_version',
			'woocommerce_version' => 'woocommerce_version',
			'object_cache'        => 'object_cache',
		];

		// 1. Collect profile defaults first (lowest priority for env params)
		$profile = $this->get_test_profile();
		foreach ( $profile_to_env_up as $profile_key => $env_up_key ) {
			if ( isset( $profile[ $profile_key ] ) && $profile[ $profile_key ] !== '' ) {
				$options[ "--$env_up_key" ] = $profile[ $profile_key ];
			}
		}

		// 2. CLI options override profile defaults
		foreach ( $env_up_options as $opt ) {
			if ( $this->hasOption( $opt ) ) {
				$value = $this->getOption( $opt );
				if ( $value !== null && $value !== false ) {
					$options[ "--$opt" ] = $value;
				}
			} else {
				// Also check for programmatically set options (e.g., via setOption())
				// even if they weren't explicitly provided via CLI
				$value = $this->getOption( $opt );
				if ( $value === true || ( is_array( $value ) && ! empty( $value ) ) ) {
					$options[ "--$opt" ] = $value;
				}
			}
		}

		// Include resolved environment name if not already set
		if ( ! isset( $options['--environment'] ) ) {
			$options['--environment'] = $this->get_environment();
		}

		return $options;
	}

	/**
	 * Check if an option was explicitly provided via CLI.
	 */
	public function hasOption( string $name ): bool {
		// For ArrayInput or when the function is not available,
		// check if the option has a non-null value
		if ( ! function_exists( 'QIT_CLI\is_option_explicitly_provided' ) ) {
			return $this->symfony_input->hasParameterOption( "--$name" );
		}
		return is_option_explicitly_provided( $this->symfony_input, $name );
	}

	/**
	 * Get option value.
	 *
	 * @return string|bool|array<string>|null
	 */
	public function getOption( string $name ) {
		return $this->symfony_input->getOption( $name );
	}

	/**
	 * Get argument value.
	 *
	 * @return string|null
	 */
	public function getArgument( string $name ) {
		return $this->symfony_input->getArgument( $name );
	}

	/**
	 * Check if argument exists and has value.
	 */
	public function hasArgument( string $name ): bool {
		$value = $this->symfony_input->getArgument( $name );
		return $value !== null && $value !== '';
	}

	/**
	 * Set an option value (used by RunActivationTestCommand).
	 *
	 * @param string                    $name The option name.
	 * @param string|bool|array<string> $value The option value.
	 */
	public function setOption( string $name, $value ): void {
		$this->symfony_input->setOption( $name, $value );
	}

	/**
	 * Get the underlying Symfony input for legacy compatibility.
	 */
	public function get_symfony_input(): InputInterface {
		return $this->symfony_input;
	}


	// ===================================================================
	// InputInterface implementation - delegate to wrapped input
	// ===================================================================

	/**
	 * {@inheritdoc}
	 */
	public function getFirstArgument(): ?string {
		return $this->symfony_input->getFirstArgument();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string|array<string> $values
	 */
	public function hasParameterOption( $values, bool $only_params = false ): bool {
		return $this->symfony_input->hasParameterOption( $values, $only_params );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string|array<string>       $values
	 * @param scalar|array<scalar>|false $default_value
	 * @return scalar|array<scalar>|false
	 */
	public function getParameterOption( $values, $default_value = false, bool $only_params = false ) {
		return $this->symfony_input->getParameterOption( $values, $default_value, $only_params );
	}

	/**
	 * {@inheritdoc}
	 */
	public function bind( InputDefinition $definition ): void {
		$this->symfony_input->bind( $definition );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(): void {
		$this->symfony_input->validate();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array<string,string|null>
	 */
	public function getArguments(): array {
		return $this->symfony_input->getArguments();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setArgument( string $name, $value ): void {
		$this->symfony_input->setArgument( $name, $value );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array<string,string|bool|array<string>|null>
	 */
	public function getOptions(): array {
		return $this->symfony_input->getOptions();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isInteractive(): bool {
		return $this->symfony_input->isInteractive();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInteractive( bool $interactive ): void {
		$this->symfony_input->setInteractive( $interactive );
	}

	/**
	 * Convert to string representation.
	 *
	 * @return string
	 */
	public function __toString(): string {
		// Check if underlying input supports __toString
		if ( method_exists( $this->symfony_input, '__toString' ) ) {
			return (string) $this->symfony_input;
		}
		// Fallback to a basic string representation
		return sprintf( 'QITInput[test_type=%s]', $this->test_type );
	}
}
