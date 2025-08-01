<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Configuration\ConfigResolver;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use QIT_CLI\PreCommand\Objects\SutInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_option_explicitly_provided;

abstract class QITCommand extends Command {
	protected InputInterface $input;
	protected OutputInterface $output;

	/** @var array<string,mixed>|null Resolved configuration */
	private ?array $config = null;
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
	 * Get resolved configuration - single pass resolution.
	 *
	 * @return array<string,mixed>
	 */
	private function cfg(): array {
		if ( $this->config === null ) {
			$config_file   = $this->get_config_file();
			$cli_overrides = $this->collect_cli_overrides();
			$this->config  = ConfigResolver::load( $config_file, $cli_overrides );
		}
		return $this->config;
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
	 * Collect CLI parameter overrides for configuration resolution.
	 *
	 * @return array<string,mixed>
	 */
	private function collect_cli_overrides(): array {
		$overrides = [];

		// Collect all explicitly provided options
		foreach ( $this->input->getOptions() as $name => $value ) {
			if ( is_option_explicitly_provided( $this->input, $name ) && $value !== null ) {
				$overrides[ $name ] = $value;
			}
		}

		return $overrides;
	}


	/**
	 * Get test profile configuration - simple helper method.
	 *
	 * @return array<string, mixed>
	 */
	public function get_current_test_profile( string $test_type, string $profile = 'default' ): array {
		$config = $this->cfg();
		return $config['test_types'][ $test_type ][ $profile ] ?? [];
	}

	/**
	 * Get environment configuration with proper CLI merging.
	 *
	 * @return array<string, mixed>
	 */
	public function get_environment_config( string $env = 'default' ): array {
		$config = $this->cfg();
		return $config['environments'][ $env ] ?? [];
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
		$cfg = $this->cfg();

		// Validate environment names exist
		foreach ( $new as $env_name ) {
			if ( ! isset( $cfg['environments'][ $env_name ] ) ) {
				throw new \RuntimeException( "download_extensions(): environment '$env_name' not found in configuration" );
			}
		}

		// 2) RESOLVE environments first (handle extends inheritance)
		$resolved_envs = [];
		foreach ( $new as $env_name ) {
			$resolved_envs[ $env_name ] = $cfg['environments'][ $env_name ];
		}

		// 3) pick extensions from RESOLVED environments (pure)
		$extracted = $this->extract_extensions_from_environments( $resolved_envs, $new );

		// Create Extension objects from extracted configurations
		$extensions = [];

		// Add SUT extension if it exists
		if ( isset( $cfg['sut_extension'] ) ) {
			$extensions[] = $cfg['sut_extension'];
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
				$extension    = $this->create_extension_from_config( $plugin_config, 'plugin' );
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
				$extension    = $this->create_extension_from_config( $theme_config, 'theme' );
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
	 * Extract plugin and theme configurations from resolved environments.
	 * Returns raw configuration arrays without creating Extension objects.
	 *
	 * @param array<string,array<string,mixed>> $resolved_envs
	 * @param array<string>                     $env_names
	 * @return array{plugins: array<mixed>, themes: array<mixed>}
	 */
	private function extract_extensions_from_environments(
		array $resolved_envs,
		array $env_names
	): array {
		$plugins = [];
		$themes  = [];

		foreach ( $env_names as $env_name ) {
			$env_config = $resolved_envs[ $env_name ] ?? [];

			// Extract plugins
			if ( isset( $env_config['plugins'] ) ) {
				foreach ( $env_config['plugins'] as $plugin_config ) {
					$plugins[] = $plugin_config;
				}
			}

			// Extract themes
			if ( isset( $env_config['themes'] ) ) {
				foreach ( $env_config['themes'] as $theme_config ) {
					$themes[] = $theme_config;
				}
			}
		}

		return [
			'plugins' => $plugins,
			'themes'  => $themes,
		];
	}

	/**
	 * Extract test package references from resolved profile configurations.
	 * Returns raw package reference arrays.
	 *
	 * @param array<array<string,mixed>> $resolved_profiles
	 * @return array<string>
	 */
	private function extract_package_refs_from_resolved_profiles(
		array $resolved_profiles
	): array {
		$refs = [];
		foreach ( $resolved_profiles as $test_config ) {
			$refs = array_merge(
				$refs,
				$test_config['test_packages'] ?? []
			);
		}
		return $refs;
	}

	/**
	 * Create Extension from configuration array (inlined from ExtensionFactory).
	 *
	 * @param array<string,mixed> $config
	 * @param string              $type
	 * @return \QIT_CLI\PreCommand\Objects\Extension
	 */
	private function create_extension_from_config( array $config, string $type ): \QIT_CLI\PreCommand\Objects\Extension {
		$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $config['slug'], $type );
		$extension->added_automatically = 'Added from environment configuration';

		if ( isset( $config['from'] ) ) {
			$extension->from = $config['from'];

			switch ( $config['from'] ) {
				case 'wporg':
					$extension->version = $config['version'] ?? 'stable';
					break;

				case 'wccom':
					$extension->version = $config['version'] ?? 'stable';
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $config['path'];
					$extension->source    = $config['path'];
					break;

				case 'url':
					$extension->source = $config['url'];
					break;

				case 'build':
					$extension->source = [
						'type'    => 'build',
						'command' => $config['command'],
						'output'  => $config['output'],
					];
					break;

				default:
					throw new \RuntimeException( "Unknown extension source type: {$config['from']}" );
			}
		} elseif ( isset( $config['source'] ) ) {
			// Legacy source format support
			$source = $config['source'];
			switch ( $source['type'] ) {
				case 'wporg':
					$extension->from    = 'wporg';
					$extension->version = $source['version'] ?? 'stable';
					break;

				case 'wccom':
					$extension->from    = 'wccom';
					$extension->version = $source['version'] ?? 'stable';
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $source['path'];
					$extension->source    = $source['path'];
					break;

				case 'url':
					$extension->from   = 'url';
					$extension->source = $source['url'];
					break;

				default:
					throw new \RuntimeException( "Unknown legacy source type: {$source['type']}" );
			}
		} else {
			throw new \RuntimeException( "Extension config missing both 'from' and 'source' properties" );
		}

		return $extension;
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

		$cfg = $this->cfg();

		// Validate test profile tuples exist
		foreach ( $profiles as $profile ) {
			if ( ! isset( $profile['type'] ) || ! isset( $profile['name'] ) ) {
				throw new \RuntimeException( "download_test_packages(): profile must have 'type' and 'name' keys" );
			}

			$test_type    = $profile['type'];
			$profile_name = $profile['name'];

			if ( ! isset( $cfg['test_types'][ $test_type ] ) ) {
				throw new \RuntimeException( "download_test_packages(): test type '$test_type' not found in configuration" );
			}

			if ( ! isset( $cfg['test_types'][ $test_type ][ $profile_name ] ) ) {
				throw new \RuntimeException( "download_test_packages(): profile '$test_type:$profile_name' not found in configuration" );
			}
		}

		// Resolve test profiles first (handle extends inheritance)
		$resolved_profiles = [];
		foreach ( $profiles as $profile ) {
			$resolved_profiles[] = $cfg['test_types'][ $profile['type'] ][ $profile['name'] ];
		}

		$references = $this->extract_package_refs_from_resolved_profiles( $resolved_profiles );

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
		$cfg = $this->cfg();
		return $cfg['sut'] ?? null;
	}

	/**
	 * Get SUT warning message if CLI overrides config.
	 */
	public function get_sut_warning(): ?string {
		// Since CLI SUT resolution is now handled by individual commands,
		// this method no longer needs to provide warnings
		return null;
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}
