<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_option_explicitly_provided;
use function QIT_CLI\is_windows;

/**
 * Qit env:up  – create a disposable local E2E environment
 */
class UpEnvironmentCommand extends QITCommand {
	/** @var E2EEnvironment */
	private E2EEnvironment $e2e_environment;
	/** @var TunnelRunner */
	private TunnelRunner $tunnel_runner;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, TunnelRunner $tunnel_runner ) {
		$this->e2e_environment = $e2e_environment;
		$this->tunnel_runner   = $tunnel_runner;
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

		/* ─ Safety guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>QIT environments require WSL on Windows.</comment>' );

			return Command::FAILURE;
		}

		/* ─ 1. Merge CLI overrides first ─ */
		$env_name   = $input->getOption( 'environment' ) ?? 'default';
		$env_config = $this->get_environment_config( $env_name );
		$env_config = $this->applyCliOverrides( $env_config, $input );

		/* ─ 2. Resolve extensions with merged config ─ */
		$resolved_ext = $this->download_extensions_from_config( $env_config );

		/* ─ 3. Use resolved extensions directly ─ */
		$env_config['plugins'] = $resolved_ext->get_plugins();
		$env_config['themes']  = $resolved_ext->get_themes();

		/* ─ 4. Create E2EEnvInfo with properly resolved extensions ─ */
		$env_info = E2EEnvInfo::from_array( [
			'env_id'      => 'qitenv' . bin2hex( random_bytes( 8 ) ),
			'environment' => 'e2e',

			'php'          => $env_config['php'] ?? '8.2',
			'wp'           => $env_config['wp'] ?? 'stable',
			'woo'          => $env_config['woo'] ?? '',
			'object_cache' => $env_config['object_cache'] ?? false,

			'plugins'        => $env_config['plugins'],
			'themes'         => $env_config['themes'],
			'php_extensions' => $env_config['php_extensions'] ?? [],
			'volumes'        => $env_config['volumes'] ?? [],

			'envs'      => $env_config['envs'] ?? [],
			'env_files' => $env_config['env_files'] ?? [],

			'tunnel'      => $env_config['tunnel'] ?? false,
			'tunnel_type' => $env_config['tunnel_type'] ?? 'no_tunnel',

			// This is filled in by the environment once it knows its port mapping.
			'site_url'    => 'http://localhost:8080',
		] );

		/* ─ 5.  Honour --tunnel (validated against TunnelRunner) ─ */
		if ( $env_info->tunnel_type !== 'no_tunnel' ) {
			$this->tunnel_runner->check_tunnel_support( $env_info->tunnel_type );
			$env_info->tunnel = true;
		}

		/* ─ 6.  SELF‑TEST shortcut ─ */
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );

			return Command::SUCCESS;
		}

		/* ─ 7.  Bring the environment up ─ */
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up();

		/* ─ 8.  Print result ─ */
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
	 * Download extensions using merged config instead of environment name.
	 *
	 * @param array<string,mixed> $env_config
	 * @return \QIT_CLI\PreCommand\Extensions\ResolvedExtensions
	 */
	private function download_extensions_from_config( array $env_config ): \QIT_CLI\PreCommand\Extensions\ResolvedExtensions {
		// Create temporary environment with merged config
		$temp_env_name = 'temp_' . uniqid();
		
		// Since we can't easily override the config, we'll use a simpler approach:
		// Create Extension objects directly from the merged config and resolve them
		$extensions = [];
		
		// Process plugins from merged config
		foreach ( $env_config['plugins'] ?? [] as $plugin_config ) {
			if ( is_string( $plugin_config ) ) {
				$extension = new \QIT_CLI\PreCommand\Objects\Extension( $plugin_config, 'plugin' );
			} else {
				$slug = $plugin_config['slug'] ?? '';
				if ( $slug ) {
					$extension = new \QIT_CLI\PreCommand\Objects\Extension( $slug, 'plugin' );
					$extension->from = $plugin_config['from'] ?? 'wporg';
					$extension->version = $plugin_config['version'] ?? 'stable';
				} else {
					continue;
				}
			}
			$extensions[] = $extension;
		}
		
		// Process themes from merged config
		foreach ( $env_config['themes'] ?? [] as $theme_config ) {
			if ( is_string( $theme_config ) ) {
				$extension = new \QIT_CLI\PreCommand\Objects\Extension( $theme_config, 'theme' );
			} else {
				$slug = $theme_config['slug'] ?? '';
				if ( $slug ) {
					$extension = new \QIT_CLI\PreCommand\Objects\Extension( $slug, 'theme' );
					$extension->from = $theme_config['from'] ?? 'wporg';
					$extension->version = $theme_config['version'] ?? 'stable';
				} else {
					continue;
				}
			}
			$extensions[] = $extension;
		}
		
		// If no extensions to resolve, return empty result
		if ( empty( $extensions ) ) {
			return new \QIT_CLI\PreCommand\Extensions\ResolvedExtensions();
		}
		
		// Create temporary environment info for resolution
		$env_info = new \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo();
		$env_info->env_id = uniqid();
		$env_info->temporary_env = \QIT_CLI\normalize_path( sys_get_temp_dir() . '/qit-resolve-' . $env_info->env_id );
		$env_info->created_at = time();
		$env_info->status = 'resolving';
		
		// Resolve extensions using ExtensionResolver
		$resolver = App::make( \QIT_CLI\PreCommand\Extensions\ExtensionResolver::class );
		return $resolver->resolve( $extensions, $env_info, sys_get_temp_dir() . '/qit-cache' );
	}

	/**
	 * Merge *explicit* CLI options into the resolved environment config.
	 *
	 * @param array<string,mixed> $config
	 *
	 * @return array<string,mixed>
	 */
	private function applyCliOverrides( array $config, InputInterface $input ): array {

		/* ─ Scalars ─ */
		foreach ( [ 'php', 'wp', 'woo', 'tunnel' ] as $opt ) {
			if ( is_option_explicitly_provided( $input, $opt ) ) {
				if ( $opt === 'tunnel' ) {
					$tunnel_value          = $input->getOption( $opt );
					$config['tunnel_type'] = $tunnel_value;
					$config['tunnel']      = $tunnel_value !== 'no_tunnel';
				} else {
					$config[ $opt ] = $input->getOption( $opt );
				}
			}
		}
		if ( is_option_explicitly_provided( $input, 'object_cache' ) ) {
			$config['object_cache'] = (bool) $input->getOption( 'object_cache' );
		}

		/* ─ Array‑merge helpers ─ */
		$merge_list = function ( string $key, string $opt_name ) use ( &$config, $input ): void {
			if ( ! is_option_explicitly_provided( $input, $opt_name ) ) {
				return;
			}
			$cli            = (array) $input->getOption( $opt_name );
			$cfg            = $config[ $key ] ?? [];
			$config[ $key ] = array_values( array_unique( array_merge( $cfg, $cli ) ) );
		};

		$merge_list( 'plugins', 'plugin' );
		$merge_list( 'themes', 'theme' );
		$merge_list( 'volumes', 'volume' );
		$merge_list( 'php_extensions', 'php_extension' );

		/* ─ Runtime env vars ─ */
		if ( is_option_explicitly_provided( $input, 'env' ) ) {
			$parsed = [];
			foreach ( $input->getOption( 'env' ) as $pair ) {
				$parts        = array_map( 'trim', explode( '=', $pair, 2 ) );
				$k            = $parts[0];
				$v            = $parts[1] ?? '';
				$parsed[ $k ] = $v;
			}
			$config['envs'] = array_merge( $config['envs'] ?? [], $parsed );
		}
		$merge_list( 'env_files', 'env_file' );

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
		$out->writeln( 'Plugins:     ' . implode( ', ', array_map( fn( $p ) => $p->slug, $info->plugins ) ) );
		if ( $info->themes ) {
			$out->writeln( 'Themes:      ' . implode( ', ', array_map( fn( $t ) => $t->slug, $info->themes ) ) );
		}
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
