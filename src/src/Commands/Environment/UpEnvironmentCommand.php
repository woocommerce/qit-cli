<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Commands\Environment\ExtensionSummary;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

/**
 * qit env:up  – create a disposable local E2E environment.
 */
class UpEnvironmentCommand extends QITCommand {
	/** @var E2EEnvironment */
	private E2EEnvironment $e2e_environment;
	/** @var TunnelRunner */
	private TunnelRunner $tunnel_runner;
	/** @var \QIT_CLI\PreCommand\Extensions\VersionResolver */
	private \QIT_CLI\PreCommand\Extensions\VersionResolver $version_resolver;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, TunnelRunner $tunnel_runner, \QIT_CLI\PreCommand\Extensions\VersionResolver $version_resolver ) {
		$this->e2e_environment  = $e2e_environment;
		$this->tunnel_runner    = $tunnel_runner;
		$this->version_resolver = $version_resolver;
		parent::__construct();
	}

	/*******************************************************************
	 * CLI definition
	 ******************************************************************/
	protected function configure(): void {
		parent::configure(); // adds --config and --environment

		$this->setDescription( 'Creates a temporary local test environment that is completely ephemeral' )
			->setAliases( [ 'env:start' ] )
			/* ─ Environment selection ─ */
			->addOption(
				'environment', 'e',
				InputOption::VALUE_OPTIONAL,
				'Pick an <comment>environment block</comment> from qit.json (e.g. --environment=legacy)',
				'default'
			)
			/* ─ Runtime env‑vars ─ */
			->addOption( 'env', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Set env var  --env KEY=VAL', [] )
			->addOption( 'env_file', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Load vars from file  --env_file ./prod.env', [] )
			/* ─ Scalars ─ */
			->addOption( 'php', null, InputOption::VALUE_OPTIONAL, 'PHP version (e.g., 8.2, 8.3)', '8.2' )
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'WordPress version (stable, rc, 6.6)', 'stable' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version', null )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Redis object cache' )
			/* ─ Lists ─ */
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
			->addOption( 'volume', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volumes (host:container)', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
			/* ─ Misc ─ */
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunnelling (cloudflare, ngrok)', 'no_tunnel' )
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Machine‑readable JSON output' )
			->setHelp( $this->getHelpText() );
	}

	/*******************************************************************
	 * Execution
	 ******************************************************************/
	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		/** @var \QIT_CLI\QITInput $input */

		/* ─ Safety guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>QIT environments require WSL on Windows.</comment>' );

			return Command::FAILURE;
		}

		/* ─ 1. Build the *final* env config (config‑file ⊕ CLI) ─ */
		$env_name   = $input->getOption( 'environment' ) ?? 'default';
		$env_config = $this->get_environment_config( $env_name );
		$env_config = $this->applyCliOverrides( $env_config, $input );

		/* ─ 2. Resolve extensions using the merged config (includes CLI overrides) ─ */
		$resolved_ext = $this->download_extensions_from_config( $env_config );

		/* ─ 3. Use the fully-resolved extension lists ─ */
		$final_plugins = $resolved_ext->get_plugins();
		$final_themes  = $resolved_ext->get_themes();

		/* ─ 3.5. Parse volumes to get proper associative array structure ─ */
		$parsed_volumes = [];
		if ( ! empty( $env_config['volumes'] ) ) {
			$parsed_volumes = \QIT_CLI\App::make( \QIT_CLI\Environment\EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] );
		}

		/* ─ 4. Materialise E2EEnvInfo DTO ─ */
		$env_info = E2EEnvInfo::from_array( [
			'env_id'         => 'qitenv' . bin2hex( random_bytes( 8 ) ),
			'environment'    => 'e2e',
			'php'            => $env_config['php'] ?? '8.2',
			'wp'             => $env_config['wp'] ?? 'stable',
			'woo'            => $env_config['woo'] ?? '',
			'object_cache'   => $env_config['object_cache'] ?? false,
			'plugins'        => $final_plugins,
			'themes'         => $final_themes,
			'php_extensions' => $env_config['php_extensions'] ?? [],
			'volumes'        => $parsed_volumes,
			'envs'           => $env_config['envs'] ?? [],
			'tunnel'         => $env_config['tunnel'] ?? false,
			'tunnel_type'    => $env_config['tunnel_type'] ?? 'no_tunnel',
			'site_url'       => 'http://localhost:8080',
		] );

		/* ─ 5. Honour --tunnel (validated against TunnelRunner) ─ */
		if ( $env_info->tunnel_type !== 'no_tunnel' ) {
			$this->tunnel_runner->check_tunnel_support( $env_info->tunnel_type );
			$env_info->tunnel = true;
		}

		/* ─ 6. SELF‑TEST shortcut ─ */
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );

			return Command::SUCCESS;
		}

		/* ─ 7. Bring the environment up ─ */
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up();

		/* ─ 8. Print result ─ */
		if ( $input->getOption( 'json' ) ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );
		} else {
			$this->renderHumanSummary( $output, $env_info );
		}

		return Command::SUCCESS;
	}

	/*******************************************************************
	 * Helpers
	 ******************************************************************/

	/**
	 * Download extensions from the given environment configuration.
	 * This method processes the merged config that includes CLI overrides.
	 *
	 * @param array<string,mixed> $env_config
	 *
	 * @return \QIT_CLI\PreCommand\Extensions\ResolvedExtensions
	 */
	private function download_extensions_from_config( array $env_config ): \QIT_CLI\PreCommand\Extensions\ResolvedExtensions {
		$extensions = [];

		// Create Extension objects from plugins in the merged config
		if ( isset( $env_config['plugins'] ) ) {
			foreach ( $env_config['plugins'] as $plugin_config ) {
				if ( is_string( $plugin_config ) ) {
					$extension = new \QIT_CLI\PreCommand\Objects\Extension( $plugin_config, 'plugin' );
					// Don't set $extension->from - let ExtensionResolver determine the correct source
					$extension->version             = 'stable';
					$extension->added_automatically = 'Added from CLI or environment configuration';
					$extensions[]                   = $extension;
				} else {
					// Handle array configuration
					$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $plugin_config['slug'], 'plugin' );
					$extension->added_automatically = 'Added from CLI or environment configuration';

					if ( isset( $plugin_config['from'] ) ) {
						$extension->from = $plugin_config['from'];

						switch ( $plugin_config['from'] ) {
							case 'wporg':
								$extension->version = $plugin_config['version'] ?? 'stable';
								break;
							case 'wccom':
								$extension->version  = $plugin_config['version'] ?? 'stable';
								$extension->wccom_id = $plugin_config['wccom_id'] ?? null;
								break;
							case 'local':
								$extension->directory = $plugin_config['directory'] ?? null;
								$extension->source    = $plugin_config['source'] ?? null;
								break;
							case 'url':
								$extension->source  = $plugin_config['source'] ?? null;
								$extension->version = $plugin_config['version'] ?? 'stable';
								break;
						}
					} else {
						// Don't set $extension->from - let ExtensionResolver determine the correct source
						$extension->version = 'stable';
					}

					$extensions[] = $extension;
				}
			}
		}

		// Create Extension objects from themes in the merged config
		if ( isset( $env_config['themes'] ) ) {
			foreach ( $env_config['themes'] as $theme_config ) {
				if ( is_string( $theme_config ) ) {
					$extension = new \QIT_CLI\PreCommand\Objects\Extension( $theme_config, 'theme' );
					// Don't set $extension->from - let ExtensionResolver determine the correct source
					$extension->version             = 'stable';
					$extension->added_automatically = 'Added from CLI or environment configuration';
					$extensions[]                   = $extension;
				} else {
					// Handle array configuration
					$extension                      = new \QIT_CLI\PreCommand\Objects\Extension( $theme_config['slug'], 'theme' );
					$extension->added_automatically = 'Added from CLI or environment configuration';

					if ( isset( $theme_config['from'] ) ) {
						$extension->from = $theme_config['from'];

						switch ( $theme_config['from'] ) {
							case 'wporg':
								$extension->version = $theme_config['version'] ?? 'stable';
								break;
							case 'local':
								$extension->directory = $theme_config['directory'] ?? null;
								$extension->source    = $theme_config['source'] ?? null;
								break;
							case 'url':
								$extension->source = $theme_config['source'] ?? null;
								break;
						}
					} else {
						// Don't set $extension->from - let ExtensionResolver determine the correct source
						$extension->version = 'stable';
					}

					$extensions[] = $extension;
				}
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

		// Resolve/download them using ExtensionResolver
		$env_info = \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo::from_array( [
			'env_id'      => 'temp_' . bin2hex( random_bytes( 4 ) ),
			'environment' => 'e2e',
		] );
		$resolver = \QIT_CLI\App::make( \QIT_CLI\PreCommand\Extensions\ExtensionResolver::class );

		// Use the proper QIT cache directory
		$cache_dir = \QIT_CLI\Config::get_qit_dir() . 'cache';

		return $resolver->resolve( $extensions, $cache_dir );
	}

	/**
	 * Merge *explicit* CLI options into the resolved environment config.
	 *
	 * @param array<string,mixed> $config
	 * @param InputInterface      $input
	 * @return array<string,mixed>
	 */
	private function applyCliOverrides( array $config, InputInterface $input ): array {
		/** @var \QIT_CLI\QITInput $input */

		/* ─ Scalars ─ */
		foreach ( [ 'php', 'wp', 'woo', 'tunnel' ] as $opt ) {
			if ( $input->hasOption( $opt ) ) {
				if ( $opt === 'tunnel' ) {
					$tunnel_value          = $input->getOption( $opt );
					$config['tunnel_type'] = $tunnel_value;
					$config['tunnel']      = $tunnel_value !== 'no_tunnel';
				} else {
					$config[ $opt ] = $input->getOption( $opt );
				}
			}
		}

		/* ─ Resolve special versions and add plugins explicitly ─ */
		$config = $this->resolve_woo( $config, $input );
		$config = $this->resolve_wp( $config, $input );
		if ( $input->hasOption( 'object_cache' ) ) {
			$config['object_cache'] = (bool) $input->getOption( 'object_cache' );
		}

		/* ─ Array‑merge helpers with slug-keyed deduplication ─ */
		$merge_list = function ( string $key, string $opt_name ) use ( &$config, $input ): void {
			if ( ! $input->hasOption( $opt_name ) ) {
				return;
			}
			$cfg = $config[ $key ] ?? [];
			$cli = (array) $input->getOption( $opt_name );

			// Use slug-keyed merge to handle mixed string/array entries properly
			$index = [];
			foreach ( array_merge( $cfg, $cli ) as $entry ) {
				$slug = is_string( $entry ) ? $entry : ( $entry['slug'] ?? null );
				if ( ! $slug ) {
					continue;
				}

				/**
				 * Precedence: later entries override earlier ones.
				 * We iterate            →   first config, then CLI.
				 * Therefore:            →   anything from the CLI wins ‑
				 *                          regardless of whether it is a string
				 *                          or a rich object.
				 */
				$index[ $slug ] = $entry;
			}
			$config[ $key ] = array_values( $index );
		};

		$merge_list( 'plugins', 'plugin' );
		$merge_list( 'themes', 'theme' );

		/* ─ Simple array merge for non-extension lists ─ */
		$merge_simple_list = function ( string $key, string $opt_name ) use ( &$config, $input ): void {
			if ( ! $input->hasOption( $opt_name ) ) {
				return;
			}
			$cli            = (array) $input->getOption( $opt_name );
			$cfg            = $config[ $key ] ?? [];
			$config[ $key ] = array_values( array_unique( array_merge( $cfg, $cli ) ) );
		};

		// Volumes should stay as simple arrays until parsed later
		if ( $input->hasOption( 'volume' ) ) {
			$cli_volumes = (array) $input->getOption( 'volume' );
			$cfg_volumes = $config['volumes'] ?? [];
			// Just merge the arrays without re-indexing
			$config['volumes'] = array_merge( $cfg_volumes, $cli_volumes );
		}
		
		$merge_simple_list( 'php_extensions', 'php_extension' );

		/* ─ Runtime env vars - process files immediately ─ */
		$existing_env_vars = $config['envs'] ?? [];
		$env_files         = array_merge(
			$config['env_files'] ?? [],
			$input->hasOption( 'env_file' ) ? (array) $input->getOption( 'env_file' ) : []
		);

		// Get CLI env vars as array of key=value strings (EnvParser expects this format)
		$cli_env_vars = $input->hasOption( 'env' ) ? (array) $input->getOption( 'env' ) : [];

		// Parse and merge everything using EnvParser
		$parsed_vars = \QIT_CLI\App::make( \QIT_CLI\Environment\EnvParser::class )->parse( $cli_env_vars, $env_files );

		// Merge with existing env vars (existing takes precedence, then parsed)
		$config['envs'] = array_merge( $existing_env_vars, $parsed_vars );

		// Remove env_files - no longer needed
		unset( $config['env_files'] );

		return $config;
	}

	/**
	 * Resolve --woo option explicitly.
	 * Adds WooCommerce plugin with the specified version.
	 *
	 * @param array<string,mixed> $config
	 * @param InputInterface      $input
	 * @return array<string,mixed>
	 */
	private function resolve_woo( array $config, InputInterface $input ): array {
		/** @var \QIT_CLI\QITInput $input */
		if ( ! $input->hasOption( 'woo' ) ) {
			return $config;
		}

		$woo_version = $input->getOption( 'woo' );

		$resolved_source = $this->version_resolver->resolve_woo( $woo_version );

		if ( $resolved_source !== null ) {
			// Special version (rc, nightly) - resolve to URL
			$woo_plugin = [
				'slug'                => 'woocommerce',
				'from'                => 'url',
				'source'              => $resolved_source,
				'version'             => $woo_version,
				'added_automatically' => 'Added via --woo option',
			];
		} else {
			// Regular version - add as wporg plugin
			$woo_plugin = [
				'slug'                => 'woocommerce',
				'from'                => 'wporg',
				'version'             => $woo_version === 'stable' ? 'stable' : $woo_version,
				'added_automatically' => 'Added via --woo option',
			];
		}

		// Add WooCommerce to plugins list, avoiding duplicates
		$config['plugins'] = $config['plugins'] ?? [];

		// Remove any existing WooCommerce plugin to avoid conflicts
		$config['plugins'] = array_filter( $config['plugins'], function ( $plugin ) {
			$slug = is_string( $plugin ) ? $plugin : ( $plugin['slug'] ?? null );
			return $slug !== 'woocommerce';
		});

		// Add the resolved WooCommerce plugin
		$config['plugins'][] = $woo_plugin;

		return $config;
	}

	/**
	 * Resolve --wp option explicitly.
	 * Resolves WordPress special versions (like rc).
	 *
	 * @param array<string,mixed> $config
	 * @param InputInterface      $input
	 * @return array<string,mixed>
	 */
	private function resolve_wp( array $config, InputInterface $input ): array {
		/** @var \QIT_CLI\QITInput $input */
		if ( ! $input->hasOption( 'wp' ) ) {
			return $config;
		}

		$wp_version = $input->getOption( 'wp' );

		$resolved_wp = $this->version_resolver->resolve_wp( $wp_version );

		if ( $resolved_wp !== null ) {
			$config['wp'] = $resolved_wp;
		}

		return $config;
	}



	/**
	 * Nicely formatted human output.
	 */
	private function renderHumanSummary( OutputInterface $out, E2EEnvInfo $info ): void {
		$out->writeln( '<info>Environment started ✔</info>' );
		$out->writeln( "ID:          <comment>{$info->env_id}</comment>" );
		$out->writeln( "PHP:         {$info->php}" );
		$out->writeln( "WordPress:   {$info->wp}" );
		if ( $info->woo ) {
			$out->writeln( "WooCommerce: {$info->woo}" );
		}
		// Render extension tables using the dedicated ExtensionSummary class
		$extension_summary = new ExtensionSummary( $this->e2e_environment );
		$extension_summary->render_extension_tables( $out, $info );
		if ( $info->tunnel ) {
			$out->writeln( "Tunnel:      {$info->tunnel_type}" );
		}
		$out->writeln( "Site URL:    {$info->site_url}" );
	}

	/*******************************************************************
	 * Long help text
	 ******************************************************************/
	private function getHelpText(): string {
		return <<<'HELP'
Creates a fully‑configured, disposable local environment.

Precedence: CLI defaults → qit.json → explicit CLI overrides.

Examples
  <info>qit env:up</info>
      Uses the "default" environment from qit.json

  <info>qit env:up --environment=legacy</info>
      Spins up the "legacy" block from qit.json

  <info>qit env:up --php=8.3 --plugin=gutenberg</info>
      Overrides PHP version and adds Gutenberg, leaving the rest untouched

  <info>qit env:up --tunnel=cloudflare</info>
      Exposes the site publicly through Cloudflare Tunnel
HELP;
	}
}
