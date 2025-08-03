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
	private InputInterface $symfonyInput;
	private array $resolvedConfig;
	private ?array $currentTestProfile = null;
	private ?array $currentEnvironmentConfig = null;
	private string $testType;
	
	/**
	 * @param InputInterface $input The raw Symfony input
	 * @param array $resolvedConfig The resolved configuration from qit.json
	 * @param string $testType The test type (e2e, activation, etc.)
	 */
	public function __construct( InputInterface $input, array $resolvedConfig, string $testType = 'e2e' ) {
		$this->symfonyInput = $input;
		$this->resolvedConfig = $resolvedConfig;
		$this->testType = $testType;
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
		
		// Check if there's only one profile for this test type
		$profiles = $this->resolvedConfig['test_types'][ $this->testType ] ?? [];
		if ( count( $profiles ) === 1 ) {
			return array_key_first( $profiles );
		}
		
		return 'default';
	}
	
	/**
	 * Get resolved test profile configuration.
	 */
	public function getTestProfile(): array {
		if ( $this->currentTestProfile === null ) {
			$profileName = $this->getProfileName();
			$this->currentTestProfile = $this->resolvedConfig['test_types'][ $this->testType ][ $profileName ] ?? [];
		}
		
		return $this->currentTestProfile;
	}
	
	/**
	 * Get fully resolved environment configuration.
	 * This includes all inheritance, CLI overrides, and special resolution.
	 */
	public function getEnvironmentConfig(): array {
		if ( $this->currentEnvironmentConfig === null ) {
			$envName = $this->getEnvironment();
			$config = $this->resolvedConfig['environments'][ $envName ] ?? [];
			
			// Apply CLI overrides - but we don't do this here anymore!
			// This should be handled by env:up when we pass options to it
			
			$this->currentEnvironmentConfig = $config;
		}
		
		return $this->currentEnvironmentConfig;
	}
	
	/**
	 * Get test packages with proper merging of profile and CLI options.
	 */
	public function getTestPackages(): array {
		$profile = $this->getTestProfile();
		$packages = $profile['test_packages'] ?? [];
		
		// Add CLI test packages if provided
		if ( $this->hasOption( 'test-package' ) ) {
			$cliPackages = (array) $this->getOption( 'test-package' );
			$packages = array_unique( array_merge( $packages, $cliPackages ) );
		}
		
		return $packages;
	}
	
	/**
	 * Get SUT (System Under Test) information.
	 */
	public function getSut(): ?array {
		// CLI argument takes precedence
		$sutArg = $this->getArgument( 'sut' );
		if ( $sutArg ) {
			return [ 'slug' => $sutArg ];
		}
		
		// Check test profile
		$profile = $this->getTestProfile();
		if ( isset( $profile['sut'] ) ) {
			return $profile['sut'];
		}
		
		// Check global config
		return $this->resolvedConfig['sut'] ?? null;
	}
	
	/**
	 * Get all environment-related options formatted for env:up command.
	 * This is what RunE2E passes to EnvironmentRunner.
	 */
	public function getEnvironmentOptions(): array {
		$options = [];
		
		// List of options that env:up understands
		$envUpOptions = [
			'environment', 'php', 'wp', 'woo', 'plugin', 'theme', 
			'volume', 'php_extension', 'object_cache', 'tunnel',
			'env', 'env_file', 'skip_activating_plugins', 
			'skip_activating_themes', 'json'
		];
		
		// Pass through explicitly provided CLI options
		foreach ( $envUpOptions as $opt ) {
			if ( $this->hasOption( $opt ) ) {
				$value = $this->getOption( $opt );
				if ( $value !== null && $value !== false ) {
					$options["--$opt"] = $value;
				}
			}
		}
		
		// Include resolved environment name if not already set
		if ( !isset( $options['--environment'] ) ) {
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
		if (!function_exists('QIT_CLI\is_option_explicitly_provided')) {
			return $this->symfonyInput->hasParameterOption("--$name");
		}
		return is_option_explicitly_provided( $this->symfonyInput, $name );
	}
	
	/**
	 * Get option value.
	 */
	public function getOption( string $name ): mixed {
		return $this->symfonyInput->getOption( $name );
	}
	
	/**
	 * Get argument value.
	 */
	public function getArgument( string $name ): mixed {
		return $this->symfonyInput->getArgument( $name );
	}
	
	/**
	 * Check if argument exists and has value.
	 */
	public function hasArgument( string $name ): bool {
		$value = $this->symfonyInput->getArgument( $name );
		return $value !== null && $value !== '';
	}
	
	/**
	 * Set an option value (used by RunActivationTestCommand).
	 */
	public function setOption( string $name, mixed $value ): void {
		$this->symfonyInput->setOption( $name, $value );
	}
	
	/**
	 * Get the underlying Symfony input for legacy compatibility.
	 */
	public function getSymfonyInput(): InputInterface {
		return $this->symfonyInput;
	}
	

	// ===================================================================
	// InputInterface implementation - delegate to wrapped input
	// ===================================================================

	/**
	 * {@inheritdoc}
	 */
	public function getFirstArgument(): ?string {
		return $this->symfonyInput->getFirstArgument();
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasParameterOption( $values, bool $onlyParams = false ): bool {
		return $this->symfonyInput->hasParameterOption( $values, $onlyParams );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterOption( $values, $default = false, bool $onlyParams = false ) {
		return $this->symfonyInput->getParameterOption( $values, $default, $onlyParams );
	}

	/**
	 * {@inheritdoc}
	 */
	public function bind( InputDefinition $definition ): void {
		$this->symfonyInput->bind( $definition );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate(): void {
		$this->symfonyInput->validate();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array {
		return $this->symfonyInput->getArguments();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setArgument( string $name, $value ): void {
		$this->symfonyInput->setArgument( $name, $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOptions(): array {
		return $this->symfonyInput->getOptions();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isInteractive(): bool {
		return $this->symfonyInput->isInteractive();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setInteractive( bool $interactive ): void {
		$this->symfonyInput->setInteractive( $interactive );
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string {
		return $this->symfonyInput->__toString();
	}
}