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
	private array $resolved_config;
	private ?array $current_test_profile       = null;
	private ?array $current_environment_config = null;
	private string $test_type;

	/**
	 * @param InputInterface $input The raw Symfony input.
	 * @param array          $resolved_config The resolved configuration from qit.json.
	 * @param string         $test_type The test type (e2e, activation, etc.).
	 */
	public function __construct( InputInterface $input, array $resolved_config, string $test_type = 'e2e' ) {
		$this->symfony_input   = $input;
		$this->resolved_config = $resolved_config;
		$this->test_type       = $test_type;
	}

	/**
	 * Get environment name with proper precedence:
	 * 1. CLI --environment flag
	 * 2. Test profile's environment setting
	 * 3. Default 'default'
	 */
	public function getEnvironment(): string {
		// CLI flag takes precedence
		if ( $this->hasOption( 'environment' ) ) {
			return $this->getOption( 'environment' );
		}

		// Check test profile for environment setting
		$profile = $this->getTestProfile();
		if ( isset( $profile['environment'] ) ) {
			return $profile['environment'];
		}

		return 'default';
	}

	/**
	 * Get the current test profile name.
	 */
	public function getProfileName(): string {
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
	 */
	public function getTestProfile(): array {
		if ( $this->current_test_profile === null ) {
			// If test type doesn't exist in config, return empty array
			// This allows commands to work without configuration when packages are provided explicitly
			if ( ! isset( $this->resolved_config['test_types'][ $this->test_type ] ) ) {
				$this->current_test_profile = [];
			} else {
				$profile_name               = $this->getProfileName();
				$this->current_test_profile = $this->resolved_config['test_types'][ $this->test_type ][ $profile_name ] ?? [];
			}
		}

		return $this->current_test_profile;
	}

	/**
	 * Get fully resolved environment configuration.
	 * This includes all inheritance, CLI overrides, and special resolution.
	 */
	public function getEnvironmentConfig(): array {
		if ( $this->current_environment_config === null ) {
			$env_name = $this->getEnvironment();
			$config   = $this->resolved_config['environments'][ $env_name ] ?? [];

			// Apply CLI overrides - but we don't do this here anymore!
			// This should be handled by env:up when we pass options to it

			$this->current_environment_config = $config;
		}

		return $this->current_environment_config;
	}

	/**
	 * Get test packages with proper merging of profile and CLI options.
	 */
	public function getTestPackages(): array {
		$profile  = $this->getTestProfile();
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
	 */
	public function getSut(): ?array {
		// CLI argument takes precedence
		$sut_arg = $this->getArgument( 'sut' );
		if ( $sut_arg ) {
			return [ 'slug' => $sut_arg ];
		}

		// Check test profile
		$profile = $this->getTestProfile();
		if ( isset( $profile['sut'] ) ) {
			return $profile['sut'];
		}

		// Check global config
		return $this->resolved_config['sut'] ?? null;
	}

	/**
	 * Get all environment-related options formatted for env:up command.
	 * This is what RunE2E passes to EnvironmentRunner.
	 */
	public function getEnvironmentOptions(): array {
		$options = [];

		// List of options that env:up understands
		$env_up_options = [
			'environment',
			'php',
			'wp',
			'woo',
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

		// Pass through explicitly provided CLI options
		foreach ( $env_up_options as $opt ) {
			if ( $this->hasOption( $opt ) ) {
				$value = $this->getOption( $opt );
				if ( $value !== null && $value !== false ) {
					$options[ "--$opt" ] = $value;
				}
			}
		}

		// Include resolved environment name if not already set
		if ( ! isset( $options['--environment'] ) ) {
			$options['--environment'] = $this->getEnvironment();
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
	 */
	public function getOption( string $name ): mixed {
		return $this->symfony_input->getOption( $name );
	}

	/**
	 * Get argument value.
	 */
	public function getArgument( string $name ): mixed {
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
	 */
	public function setOption( string $name, mixed $value ): void {
		$this->symfony_input->setOption( $name, $value );
	}

	/**
	 * Get the underlying Symfony input for legacy compatibility.
	 */
	public function getSymfonyInput(): InputInterface {
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
	 */
	public function hasParameterOption( $values, bool $only_params = false ): bool {
		return $this->symfony_input->hasParameterOption( $values, $only_params );
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function __toString(): string {
		return $this->symfony_input->__toString();
	}
}
