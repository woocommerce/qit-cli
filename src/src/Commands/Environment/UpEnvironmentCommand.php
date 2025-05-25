<?php

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

class UpEnvironmentCommand extends QITCommand {
	protected E2EEnvironment $e2e_environment;
	protected TunnelRunner $tunnel_runner;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, TunnelRunner $tunnel_runner ) {
		parent::__construct();
		$this->e2e_environment = $e2e_environment;
		$this->tunnel_runner   = $tunnel_runner;
	}

	protected function configure(): void {
		parent::configure();

		$this->setName( 'env:up' )
			->setDescription( 'Creates a temporary local test environment.' )
			->addOption( 'wp_version', null, InputOption::VALUE_OPTIONAL, 'WordPress version', 'stable' )
			->addOption( 'woo_version', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version' )
			->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL, 'PHP version', '8.2' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Plugins to activate', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Themes to install', [] )
			->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional volume mappings', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Object Cache' )
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins' )
			->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Return JSON format', false )
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling', 'no_tunnel' )
			->addOption( 'env', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables', [] )
			->addOption( 'env_file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment files', [] )
			->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'Dependencies mode', 'activate' )
			->addOption( 'environment', null, InputOption::VALUE_OPTIONAL, 'Environment from qit.json', 'default' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>Use WSL on Windows.</comment>' );

			return Command::FAILURE;
		}

		$env_info = $this->get_env_info();
		if ( ! $env_info instanceof E2EEnvInfo ) {
			$output->writeln( '<error>Expected E2E environment configuration.</error>' );

			return Command::FAILURE;
		}

		// Handle tunnel
		if ( $env_info->tunnel ) {
			try {
				$this->tunnel_runner->check_tunnel_support( $env_info->tunnel_type );
				$env_info->tunnel = true;
			} catch ( \Exception $e ) {
				$output->writeln( '<error>' . $e->getMessage() . '</error>' );

				return Command::FAILURE;
			}
		} else {
			$env_info->tunnel = false;
		}

		// Set skip flags
		if ( $env_info->skip_activating_plugins ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}
		if ( $env_info->skip_activating_themes ) {
			$this->e2e_environment->set_skip_activating_themes( true );
		}

		// Initialize and run environment
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up( 'up' );

		// Output
		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		} else {
			$output->writeln( $env_info->site_url );
		}

		return Command::SUCCESS;
	}
}
