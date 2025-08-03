<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
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

		/* ─ 1. Build the *final* env config (config‑file ⊕ CLI) ─ */
		$env_name   = $input->getOption( 'environment' ) ?? 'default';
		$env_config = $this->env_config( $env_name, $input );

		/* ─ 2. Resolve extensions using the merged config (includes CLI overrides) ─ */
		$resolved_ext = $this->resolve_extensions( $env_config );

		/* ─ 3. Use the fully-resolved extension lists ─ */
		$final_plugins = $resolved_ext->get_plugins();
		$final_themes  = $resolved_ext->get_themes();

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
			'volumes'        => $env_config['volumes'] ?? [],
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
