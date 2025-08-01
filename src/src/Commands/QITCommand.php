<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use QIT_CLI\PreCommand\Configuration\ExtensionFactory;
use QIT_CLI\PreCommand\Objects\SutInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_option_explicitly_provided;

abstract class QITCommand extends Command {
	protected InputInterface $input;
	protected OutputInterface $output;

	/** @var ?ResolvedConfiguration Lazy-initialized configuration and services */
	private ?ResolvedConfiguration $cfg = null;
	private ?\QIT_CLI\PreCommand\Extensions\ResolvedExtensions $resolved_extensions = null;
	/** @var string[] */
	private array $resolved_envs = [];
	/** @var array<string, mixed> */
	private array $packages = [];

	protected function configure(): void {
		// Always add config stuff
		$this->addOption(
			'config',
			'',
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file',
			null
		);

		// Add profile option if needed (overridden by subclasses)
		$this->configureProfileOption();

		// Add environment option if needed (overridden by subclasses)
		$this->configureEnvironmentOption();
	}

	/**
	 * Configure profile option - override in subclasses that need it
	 */
	protected function configureProfileOption(): void {
		// Base implementation does nothing
	}

	/**
	 * Configure environment option - override in subclasses that need it
	 */
	protected function configureEnvironmentOption(): void {
		// Base implementation does nothing
	}

	public function execute( InputInterface $input, OutputInterface $output ): int {
		$this->input  = $input;
		$this->output = $output;

		try {
			return $this->doExecute( $input, $output );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );
			return Command::FAILURE;
		}
	}

	/**
	 * Lazy configuration parsing - only parse once when needed.
	 */
	private function config(): ResolvedConfiguration {
		if ( $this->cfg === null ) {
			// Build CLI SUT to pass to parser for proper precedence
			$sut_cli = $this->build_cli_sut();

			// Initialize parsed configuration
			$parsed_config = [
				'sut'           => null,
				'environments'  => [],
				'test_types'    => [],
				'groups'        => [],
				'test_packages' => [],
			];

			// Parse qit.json if provided
			$config_file = $this->get_config_file();
			if ( $config_file ) {
				$parsed_config = App::make( QitJsonParser::class )->parse( $config_file );
			}

			// Create resolved configuration
			$resolved                          = new ResolvedConfiguration( $parsed_config );
			$resolved->metadata['config_file'] = $config_file;

			// Process SUT
			if ( $sut_cli ) {
				// CLI-provided SUT takes precedence
				$resolved->sut           = [
					'slug'   => $sut_cli->slug,
					'type'   => $sut_cli->type,
					'source' => [ 'type' => 'wporg' ],
				];
				$resolved->sut_extension = App::make( ExtensionFactory::class )->for_sut( $resolved->sut );
			} elseif ( isset( $parsed_config['sut'] ) ) {
				// Fall back to qit.json SUT
				$resolved->sut           = $parsed_config['sut'];
				$resolved->sut_extension = App::make( ExtensionFactory::class )->for_sut( $parsed_config['sut'] );
			}

			// Apply default fallbacks
			if ( ! isset( $parsed_config['environments']['default'] ) ) {
				$parsed_config['environments']['default'] = [];
			}
			if ( ! isset( $parsed_config['test_types']['e2e']['default'] ) ) {
				$parsed_config['test_types']['e2e']['default'] = [];
			}

			// Copy basic configuration
			$resolved->environments          = $parsed_config['environments'] ?? [];
			$resolved->test_types            = $parsed_config['test_types'] ?? [];
			$resolved->groups                = $parsed_config['groups'] ?? [];
			$resolved->test_packages         = $parsed_config['test_packages'] ?? [];
			$resolved->test_package_metadata = $parsed_config['test_package_metadata'] ?? [];

			// Validate configuration
			$errors = $resolved->validate();
			if ( ! empty( $errors ) ) {
				throw new \RuntimeException( "Configuration validation failed:\n" . implode( "\n", array_map( fn( $e ) => "  - $e", $errors ) ) );
			}

			$this->cfg = $resolved;
		}
		return $this->cfg;
	}

	/**
	 * Get config file path from options or default location.
	 */
	private function get_config_file(): ?string {
		$config_file = $this->input->getOption( 'config' );
		if ( $config_file === null && file_exists( getcwd() . '/qit.json' ) ) {
			$config_file = getcwd() . '/qit.json';
		}
		return $config_file;
	}

	/**
	 * Build CLI SUT input from command line options
	 */
	private function build_cli_sut(): ?SutInput {
		$slug = null;
		$type = null;

		// Check for plugin slug
		if ( $this->input->hasOption( 'plugin' ) ) {
			$slug = $this->input->getOption( 'plugin' );
			$type = 'plugin';
		}

		// Check for theme slug
		if ( $this->input->hasOption( 'theme' ) ) {
			if ( $slug !== null ) {
				throw new \InvalidArgumentException( 'Cannot specify both --plugin and --theme options' );
			}
			$slug = $this->input->getOption( 'theme' );
			$type = 'theme';
		}

		if ( $slug === null ) {
			return null;
		}

		return new SutInput( $slug, $type );
	}

	/**
	 * Get test profile configuration - simple helper method.
	 *
	 * @return array<string, mixed>
	 */
	public function get_current_test_profile( string $test_type, string $profile = 'default' ): array {
		$defaults = $this->extract_defaults();
		$cli      = $this->extract_explicit();

		try {
			$json = $this->config()->get_test_config( $test_type, $profile );
		} catch ( \RuntimeException $e ) {
			$json = [];
		}

		return $this->merger()->merge( $cli, $json, $defaults );
	}

	/**
	 * Get environment configuration with proper CLI merging.
	 *
	 * @return array<string, mixed>
	 */
	public function get_environment_config( string $env = 'default' ): array {
		// Validate environment options at the point where they're actually processed
		$this->validate_environment_options();

		$defaults = $this->extract_defaults();
		$cli      = $this->extract_explicit();

		try {
			$json = $this->config()->get_environment( $env );
		} catch ( \RuntimeException $e ) {
			$json = [];
		}

		// Apply profile defaults for environment commands
		// env:up is an e2e command, so apply e2e profile defaults
		$profile_defaults = [];
		try {
			$profile_defaults = $this->config()->get_test_config( 'e2e', 'default' );
		} catch ( \InvalidArgumentException $e ) {
			// No profile defaults available, use empty array
			$profile_defaults = [];
		}

		// Merge in correct precedence order: CLI > Profile Defaults > Environment Config > Command Defaults
		// First merge command defaults with environment config (environment takes precedence over command defaults)
		$base = $this->merger()->merge( [], $json, $defaults );
		// Then merge with profile defaults (profile takes precedence over environment)
		$base = $this->merger()->merge( [], $profile_defaults, $base );
		// Finally merge with CLI (CLI takes precedence over everything)
		return $this->merger()->merge( $cli, [], $base );
	}

	/**
	 * Get merger service lazily
	 */
	private function merger(): ConfigMerger {
		return App::make( ConfigMerger::class );
	}

	/**
	 * Extract CLI defaults using the simplified approach.
	 * Only includes options that were NOT explicitly provided by the user.
	 *
	 * @return array<string, mixed>
	 */
	private function extract_defaults(): array {
		$defaults = [];

		// Get all options from input
		foreach ( $this->input->getOptions() as $name => $value ) {
			// Skip framework options
			if ( in_array( $name, [ 'help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction' ], true ) ) {
				continue;
			}

			// Skip explicitly provided options - they should be handled by extract_explicit()
			if ( $this->is_explicitly_provided( $name ) ) {
				continue;
			}

			// Use automatic pluralization for array options
			$config_key              = $this->normalize_key( $name, $value );
			$defaults[ $config_key ] = $value;
		}

		return $defaults;
	}

	/**
	 * Extract explicitly provided CLI options.
	 *
	 * @return array<string, mixed>
	 */
	private function extract_explicit(): array {
		$explicit = [];

		// Get all options from input
		foreach ( $this->input->getOptions() as $name => $value ) {
			// Skip framework options
			if ( in_array( $name, [ 'help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction' ], true ) ) {
				continue;
			}

			// Only include if explicitly provided (not just default value)
			if ( $this->is_explicitly_provided( $name ) ) {
				$config_key = $this->normalize_key( $name, $value );

				// Handle special cases
				if ( $name === 'phpstan_level' && $value !== null ) {
					$value = (int) $value;
				}

				// For boolean flags, treat explicitly provided null as true
				// For boolean flags with values, set the flag to true but preserve the value
				if ( $this->is_boolean_flag( $name ) ) {
					if ( $value === null ) {
						$value = true;
					} else {
						// For tunnel, we need both tunnel=true and tunnel_type=value
						if ( $name === 'tunnel' && $value !== null ) {
							$explicit['tunnel']      = true;
							$explicit['tunnel_type'] = $value;
							continue; // Skip the normal assignment below
						} else {
							$value = true; // Other boolean flags with values still become true
						}
					}
				}

				$explicit[ $config_key ] = $value;
			}
		}

		return $explicit;
	}

	/**
	 * Check if an option was explicitly provided by the user.
	 */
	private function is_explicitly_provided( string $option_name ): bool {
		return is_option_explicitly_provided( $this->input, $option_name );
	}

	/**
	 * Check if an option is a boolean flag that should be treated as true when present.
	 */
	private function is_boolean_flag( string $option_name ): bool {
		$boolean_flags = [
			'object_cache',
			'tunnel',
			'json',
			'skip_activating_plugins',
			'skip_activating_themes',
		];

		return in_array( $option_name, $boolean_flags, true );
	}

	/**
	 * Normalize CLI option name to config key using automatic pluralization.
	 *
	 * @param string $cli_name The CLI option name to normalize.
	 * @param mixed  $value    The value to check for pluralization logic.
	 */
	private function normalize_key( string $cli_name, $value ): string {
		// For array values, pluralize if not already plural
		if ( is_array( $value ) ) {
			return str_ends_with( $cli_name, 's' ) ? $cli_name : $cli_name . 's';
		}

		return $cli_name;
	}

	/**
	 * Validate environment-related CLI options.
	 *
	 * @throws \InvalidArgumentException If validation fails.
	 */
	public function validate_environment_options(): void {
		// Validate --environment option
		$env_opt = $this->input->getOption( 'environment' );
		if ( $env_opt !== null && ! preg_match( '/^[A-Za-z0-9_-]+$/', $env_opt ) ) {
			throw new \InvalidArgumentException(
				"--environment expects a name like 'production' or 'php82', "
				. "got '{$env_opt}'. Did you mean --env?"
			);
		}

		// Validate each --env value
		$env_vars = $this->input->getOption( 'env' ) ?? [];
		foreach ( $env_vars as $pair ) {
			if ( ! str_contains( $pair, '=' ) ) {
				throw new \InvalidArgumentException(
					"Invalid --env '{$pair}'. Expected KEY=VAL. "
					. "Did you mean --environment={$pair} ?"
				);
			}
		}
	}

	/**
	 * Get current CLI context with safe option access.
	 *
	 * @return array<string, string>
	 */
	private function currentContext(): array {
		return [
			'environment'  => $this->input->hasOption( 'environment' )
								? $this->input->getOption( 'environment' ) ?? 'default'
								: 'default',
			'test_type'    => 'e2e', // Fixed since test_type option doesn't exist
			'test_profile' => $this->input->hasOption( 'profile' )
								? $this->input->getOption( 'profile' ) ?? 'default'
								: 'default',
		];
	}

	/**
	 * Create temporary environment info for extension resolution.
	 */
	private function create_temp_env_info(): \QIT_CLI\Environment\Environments\EnvInfo {
		$env_info                = new \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo();
		$env_info->env_id        = uniqid();
		$env_info->temporary_env = \QIT_CLI\normalize_path( sys_get_temp_dir() . '/qit-resolve-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'resolving';

		return $env_info;
	}

	/**
	 * Lazily resolve and download only the extensions required by the given
	 * environment names (defaults to the current environment if omitted).
	 *
	 * @param string[] $env_names
	 */
	public function download_extensions( array $env_names = [] ): \QIT_CLI\PreCommand\Extensions\ResolvedExtensions {
		if ( empty( $env_names ) ) {
			$context   = $this->currentContext();
			$env_names = [ $context['environment'] ];
		}

		// Calculate delta of new environments
		$new = array_diff( $env_names, $this->resolved_envs );

		if ( empty( $new ) ) {
			// All requested environments already resolved
			return $this->resolved_extensions ?? new \QIT_CLI\PreCommand\Extensions\ResolvedExtensions();
		}

		// 1) parse config (pure)
		$cfg = $this->config();

		// Validate environment names exist
		foreach ( $new as $env_name ) {
			if ( ! isset( $cfg->environments[ $env_name ] ) ) {
				throw new \RuntimeException( "download_extensions(): environment '$env_name' not found in configuration" );
			}
		}

		// 2) RESOLVE environments first (handle extends inheritance)
		$resolved_envs = [];
		foreach ( $new as $env_name ) {
			$resolved_envs[ $env_name ] = $cfg->get_environment( $env_name );
		}

		// 3) pick extensions from RESOLVED environments (pure)
		$extracted = App::make( QitJsonParser::class )->extract_extensions_from_resolved_envs( $resolved_envs, $new );

		// Create Extension objects from extracted configurations
		$extensions = [];

		// Add SUT extension if it exists
		if ( $cfg->sut && $cfg->sut_extension ) {
			$extensions[] = $cfg->sut_extension;
		}

		// Create Extension objects from plugins
		foreach ( $extracted['plugins'] as $plugin_config ) {
			if ( is_string( $plugin_config ) ) {
				$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $plugin_config, 'plugin' );
				$extension->from                = 'wporg';
				$extension->version             = 'stable';
				$extension->added_automatically = 'Added from environment configuration';
				$extensions[]                   = $extension;
			} else {
				$extension    = App::make( ExtensionFactory::class )->from_plugin_config( $plugin_config );
				$extensions[] = $extension;
			}
		}

		// Create Extension objects from themes
		foreach ( $extracted['themes'] as $theme_config ) {
			if ( is_string( $theme_config ) ) {
				$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $theme_config, 'theme' );
				$extension->from                = 'wporg';
				$extension->version             = 'stable';
				$extension->added_automatically = 'Added from environment configuration';
				$extensions[]                   = $extension;
			} else {
				$extension    = App::make( ExtensionFactory::class )->from_theme_config( $theme_config );
				$extensions[] = $extension;
			}
		}

		// Remove duplicates by slug
		$unique = [];
		foreach ( $extensions as $ext ) {
			$key = $ext->slug . '_' . $ext->type;
			if ( ! isset( $unique[ $key ] ) ) {
				$unique[ $key ] = $ext;
			}
		}
		$extensions = array_values( $unique );

		// 4) resolve/download them (impure) – heavy
		$env_info = $this->create_temp_env_info();
		$delta    = App::make( ExtensionResolver::class )->resolve( $extensions, $env_info, sys_get_temp_dir() . '/qit-cache' );

		// 5) merge with existing results
		if ( $this->resolved_extensions === null ) {
			$this->resolved_extensions = $delta;
		} else {
			$this->resolved_extensions->merge( $delta );
		}

		// 5) update resolved environments tracking (avoid array_merge)
		foreach ( $new as $env ) {
			$this->resolved_envs[] = $env;
		}

		return $this->resolved_extensions;
	}

	/**
	 * Lazily download test packages required by the given profiles.
	 * Signature mirrors download_extensions().
	 *
	 * @param array<array<string, string>> $profiles
	 * @return array<string, mixed>
	 */
	public function download_test_packages( array $profiles = [] ): array {
		// Handle default profile logic using currentContext()
		if ( $profiles === [] ) {
			$context  = $this->currentContext();
			$profiles = [
				[
					'type' => $context['test_type'],
					'name' => $context['test_profile'],
				],
			];
		}

		$cfg = $this->config();

		// Validate test profile tuples exist
		foreach ( $profiles as $profile ) {
			if ( ! isset( $profile['type'] ) || ! isset( $profile['name'] ) ) {
				throw new \RuntimeException( "download_test_packages(): profile must have 'type' and 'name' keys" );
			}

			$test_type    = $profile['type'];
			$profile_name = $profile['name'];

			if ( ! isset( $cfg->test_types[ $test_type ] ) ) {
				throw new \RuntimeException( "download_test_packages(): test type '$test_type' not found in configuration" );
			}

			if ( ! isset( $cfg->test_types[ $test_type ][ $profile_name ] ) ) {
				throw new \RuntimeException( "download_test_packages(): profile '$test_type:$profile_name' not found in configuration" );
			}
		}

		// Resolve test profiles first (handle extends inheritance)
		$resolved_profiles = [];
		foreach ( $profiles as $profile ) {
			$resolved_profiles[] = $cfg->get_test_config( $profile['type'], $profile['name'] );
		}

		$references = App::make( QitJsonParser::class )->extract_package_refs_from_resolved_profiles( $resolved_profiles );

		// Calculate delta of new package references
		$new_refs = array_diff( $references, array_keys( $this->packages ) );

		if ( empty( $new_refs ) ) {
			// All requested packages already downloaded
			return $this->packages;
		}

		// Download only the new packages
		$new_packages = App::make( TestPackageDownloader::class )->download( $new_refs, sys_get_temp_dir() . '/qit-cache' );

		// Merge with existing packages (avoid array_merge)
		foreach ( $new_packages as $ref => $manifest ) {
			$this->packages[ $ref ] = $manifest;
		}

		return $this->packages;
	}

	/**
	 * Get resolved SUT configuration.
	 *
	 * @return array<string, mixed>|null
	 */
	public function get_resolved_sut(): ?array {
		$cfg = $this->config();
		return $cfg->sut;
	}

	/**
	 * Get SUT warning message if CLI overrides config.
	 */
	public function get_sut_warning(): ?string {
		// Check if CLI SUT was provided
		$cli_sut = $this->build_cli_sut();
		$cfg     = $this->config();

		if ( $cli_sut && isset( $cfg->metadata['config_file'] ) && ! empty( $cfg->metadata['config_file'] ) ) {
			return 'CLI argument overrides SUT defined in configuration file.';
		}

		return null;
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}
