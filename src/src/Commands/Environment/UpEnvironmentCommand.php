<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvironment;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\QITInput;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

/**
 * Qit env:up  – create a disposable local E2E environment.
 */
class UpEnvironmentCommand extends QITCommand {
	/** @var E2EEnvironment */
	private E2EEnvironment $e2e_environment;
	/** @var PerformanceEnvironment */
	private PerformanceEnvironment $performance_environment;
	/** @var TunnelRunner */
	private TunnelRunner $tunnel_runner;
	/** @var \QIT_CLI\PreCommand\Extensions\VersionResolver */
	private \QIT_CLI\PreCommand\Extensions\VersionResolver $version_resolver;
	/** @var \QIT_CLI\Environment\EnvironmentVars */
	private \QIT_CLI\Environment\EnvironmentVars $environment_vars;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, PerformanceEnvironment $performance_environment, TunnelRunner $tunnel_runner, \QIT_CLI\PreCommand\Extensions\VersionResolver $version_resolver, \QIT_CLI\Environment\EnvironmentVars $environment_vars ) {
		$this->e2e_environment         = $e2e_environment;
		$this->performance_environment = $performance_environment;
		$this->tunnel_runner           = $tunnel_runner;
		$this->version_resolver        = $version_resolver;
		$this->environment_vars        = $environment_vars;
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
			->addOption( 'environment_type', null, InputOption::VALUE_OPTIONAL, 'The type of environment to create. Valid values: "e2e", "performance".', 'e2e' )
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
			->addOption( 'global_setup', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Test packages to run global setup', [] )
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
	protected function doExecute( QITInput $input, OutputInterface $output ): int {
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

		// Convert Extension objects to arrays for serialization
		$plugin_arrays = [];
		foreach ( $final_plugins as $plugin ) {
			if ( $plugin instanceof Extension ) {
				// Use jsonSerialize to get the array representation
				$plugin_arrays[] = $plugin->jsonSerialize();
			} else {
				$plugin_arrays[] = $plugin;
			}
		}

		$theme_arrays = [];
		foreach ( $final_themes as $theme ) {
			if ( $theme instanceof Extension ) {
				// Use jsonSerialize to get the array representation
				$theme_arrays[] = $theme->jsonSerialize();
			} else {
				$theme_arrays[] = $theme;
			}
		}

		/* ─ 3.5. Parse volumes to get proper associative array structure ─ */
		$parsed_volumes = [];
		if ( ! empty( $env_config['volumes'] ) ) {
			$parsed_volumes = \QIT_CLI\App::make( \QIT_CLI\Environment\EnvVolumeParser::class )->parse_volumes( $env_config['volumes'] );
		}

		/* ─ 3.6. Process global setup packages if present ─ */
		$global_setup_packages = [];
		if ( ! empty( $env_config['global_setup'] ) ) {
			// Download and prepare global setup packages
			$package_downloader = \QIT_CLI\App::make( \QIT_CLI\PreCommand\Download\TestPackageDownloader::class );
			foreach ( $env_config['global_setup'] as $package_ref ) {
				try {
					$manifest = $package_downloader->download_single( $package_ref, sys_get_temp_dir() . '/qit-cache' );
					if ( $manifest ) {
						$metadata                              = $package_downloader->get_package_metadata( $package_ref );
						$global_setup_packages[ $package_ref ] = [
							'path'   => $metadata['downloaded_path'] ?? '',
							'source' => 'registry',
						];
					}
				} catch ( \Exception $e ) {
					$output->writeln( "<warning>Failed to download global setup package {$package_ref}: {$e->getMessage()}</warning>" );
				}
			}
		}

		/* ─ 4. Materialise E2EEnvInfo DTO ─ */
		/** @var E2EEnvInfo $env_info */
		$env_info = E2EEnvInfo::from_array( [
			'env_id'                => 'qitenv' . bin2hex( random_bytes( 8 ) ),
			'environment'           => 'e2e',
			'php'                   => $env_config['php'] ?? '8.2',
			'wp'                    => $env_config['wp'] ?? 'stable',
			'woo'                   => $env_config['woo'] ?? '',
			'object_cache'          => $env_config['object_cache'] ?? false,
			'plugins'               => $plugin_arrays,
			'themes'                => $theme_arrays,
			'php_extensions'        => $env_config['php_extensions'] ?? [],
			'volumes'               => $parsed_volumes,
			'envs'                  => $env_config['envs'] ?? [],
			'global_setup_packages' => $global_setup_packages,
			'tunnel'                => $env_config['tunnel'] ?? false,
			'tunnel_type'           => $env_config['tunnel_type'] ?? 'no_tunnel',
			'site_url'              => 'http://localhost:8080',
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
		$environment_type = $input->getOption( 'environment_type' ) ?? 'e2e';
		$environment      = $this->get_environment( $environment_type );
		$environment->init( $env_info );
		$environment->up();

		/* ─ 8. Save environment info and generate source files ─ */
		$this->save_environment_info( $env_info );
		$files = $this->environment_vars->save_environment_file( $env_info );

		/* ─ 9. Print result ─ */
		if ( $input->getOption( 'json' ) ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );
		} else {
			$this->renderHumanSummary( $output, $env_info );

			// Show manual testing instructions (unless only global setup packages were run)
			if ( empty( $env_info->global_setup_packages ) || ! empty( $env_info->plugins ) || ! empty( $env_info->themes ) ) {
				$output->writeln( '' );
				$output->writeln( 'To run manual tests:' );
				$output->writeln( '  1. Navigate to your test directory' );
				$output->writeln( '  2. Load environment variables in this terminal:' );
				$output->writeln( sprintf( '     <info>source "$(qit env:source %s)"</info>', $env_info->env_id ) );
				$output->writeln( '  3. Run your tests:' );
				$output->writeln( '     <info>npx playwright test</info>' );
			}
		}

		return Command::SUCCESS;
	}

	/*******************************************************************
	 * Helpers
	 ******************************************************************/

	/**
	 * Get the appropriate environment instance based on the environment type.
	 */
	protected function get_environment( string $environment_type ): \QIT_CLI\Environment\Environments\Environment {
		switch ( $environment_type ) {
			case 'performance':
				return $this->performance_environment;
			case 'e2e':
			default:
				return $this->e2e_environment;
		}
	}

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
					// Use the new parser for string inputs
					try {
						$extension    = \QIT_CLI\PreCommand\Extensions\ExtensionInputParser::parse( $plugin_config, 'plugin' );
						$extensions[] = $extension;
					} catch ( \InvalidArgumentException $e ) {
						throw new \RuntimeException( 'Invalid plugin specification: ' . $e->getMessage() );
					}
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
					// Use the new parser for string inputs
					try {
						$extension    = \QIT_CLI\PreCommand\Extensions\ExtensionInputParser::parse( $theme_config, 'theme' );
						$extensions[] = $extension;
					} catch ( \InvalidArgumentException $e ) {
						throw new \RuntimeException( 'Invalid theme specification: ' . $e->getMessage() );
					}
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
		// $input is actually a QITInput instance when called from our commands

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
		$merge_simple_list( 'global_setup', 'global_setup' );

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
	/**
	 * Save environment info to a JSON file for later use.
	 *
	 * @param E2EEnvInfo $env_info The environment info to save.
	 */
	private function save_environment_info( E2EEnvInfo $env_info ): void {
		$env_dir   = $this->environment_vars->get_env_directory();
		$info_file = $env_dir . '/' . $env_info->env_id . '.json';

		// Store essential information for later retrieval
		$data = [
			'env_id'        => $env_info->env_id,
			'site_url'      => $env_info->site_url,
			'php'           => $env_info->php,
			'wp'            => $env_info->wp,
			'woo'           => $env_info->woo,
			'db_port'       => $env_info->db_port ?? 0,
			'php_container' => $env_info->php_container ?: 'qit_env_php_' . $env_info->env_id,
			'db_container'  => $env_info->db_container ?: 'qit_env_db_' . $env_info->env_id,
			'nginx_port'    => $env_info->nginx_port ?? '',
		];

		file_put_contents( $info_file, json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	private function renderHumanSummary( OutputInterface $out, EnvInfo $info ): void {
		$out->writeln( '' );
		$out->writeln( sprintf( '<info>✅ Environment ready: %s</info>', $info->env_id ) );
		$out->writeln( '' );

		// URL and credentials
		$out->writeln( sprintf( '  URL:         %s', $info->site_url ) );
		$out->writeln( '  Credentials: admin/password' );

		// Stack information
		if ( $info instanceof E2EEnvInfo ) {
			$stack_parts   = [];
			$stack_parts[] = sprintf( 'WordPress %s', $info->wp );
			$stack_parts[] = sprintf( 'PHP %s', $info->php );
			$out->writeln( sprintf( '  Stack:       %s', implode( ', ', $stack_parts ) ) );

			// Plugins line (only if plugins exist)
			$plugin_names = [];
			foreach ( $info->plugins as $plugin ) {
				if ( $plugin->slug === 'woocommerce' && $info->woo ) {
					$plugin_names[] = sprintf( 'WooCommerce %s', $info->woo );
				} elseif ( $plugin->slug !== 'woocommerce' ) {
					$plugin_names[] = sprintf( '%s %s', $this->format_plugin_name( $plugin->slug ), $plugin->version );
				}
			}
			if ( ! empty( $plugin_names ) ) {
				$out->writeln( sprintf( '  Plugins:     %s', implode( ', ', $plugin_names ) ) );
			}

			// Theme line (only if non-default theme exists)
			$theme_names = [];
			foreach ( $info->themes as $theme ) {
				// Skip default themes
				if ( ! in_array( $theme->slug, [ 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo' ], true ) ) {
					$theme_names[] = sprintf( '%s %s', $this->format_plugin_name( $theme->slug ), $theme->version );
				}
			}
			if ( ! empty( $theme_names ) ) {
				$out->writeln( sprintf( '  Theme:       %s', implode( ', ', $theme_names ) ) );
			}

			// Global setup packages (if any were executed)
			if ( ! empty( $info->global_setup_packages ) ) {
				$out->writeln( '' );
				$out->writeln( '  Global Setup completed:' );
				foreach ( $info->global_setup_packages as $pkg_id => $pkg_info ) {
					$out->writeln( sprintf( '    • %s', $pkg_id ) );
				}
			}
		} elseif ( $info instanceof PerformanceEnvInfo ) {
			$stack_parts   = [];
			$stack_parts[] = sprintf( 'WordPress %s', $info->wp );
			$stack_parts[] = sprintf( 'PHP %s', $info->php_version );
			$out->writeln( sprintf( '  Stack:       %s', implode( ', ', $stack_parts ) ) );

			if ( property_exists( $info, 'woo' ) && $info->woo ) {
				$out->writeln( sprintf( '  Plugins:     WooCommerce %s', $info->woo ) );
			}
		}

		// Tunnel information (only if enabled)
		if ( $info->tunnel ) {
			$out->writeln( sprintf( '  Tunnel:      %s', $info->tunnel_type ) );
		}
	}

	/**
	 * Format plugin/theme name for display.
	 *
	 * @param string $slug The plugin/theme slug.
	 * @return string Formatted name.
	 */
	private function format_plugin_name( string $slug ): string {
		// Convert slug to title case
		$name = str_replace( [ '-', '_' ], ' ', $slug );
		return ucwords( $name );
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
