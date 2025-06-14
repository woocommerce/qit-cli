<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class UpEnvironmentCommand extends QITCommand implements EnvironmentCommand {
	protected E2EEnvironment $e2e_environment;
	protected TunnelRunner $tunnel_runner;

	protected static $defaultName = 'env:up';

	public function __construct( E2EEnvironment $e2e_environment, TunnelRunner $tunnel_runner ) {
		parent::__construct();
		$this->e2e_environment = $e2e_environment;
		$this->tunnel_runner   = $tunnel_runner;
	}

	// EnvironmentCommand interface implementation
	public function getEnvironmentName(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function shouldPrepareEnvironment(): bool {
		return true; // Always prepare for env:up
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Creates a temporary local test environment.' )
			// These options override what's in qit.json
			 ->addOption( 'wp_version', null, InputOption::VALUE_OPTIONAL, 'WordPress version' )
		     ->addOption( 'woo_version', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version' )
		     ->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL, 'PHP version' )
		     ->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
		     ->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
		     ->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volume mappings', [] )
		     ->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
		     ->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Object Cache' )
		     ->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins' )
		     ->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes' )
		     ->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling', 'no_tunnel' )
		     ->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Return JSON format', false )
		     ->addOption( 'env', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables', [] )
		     ->addOption( 'env_file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment files', [] );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>Use WSL on Windows.</comment>' );

			return Command::FAILURE;
		}

		/** @var EnvironmentResult $result */
		$result = $this->getPreCommandResult();

		// The PreCommand has already:
		// - Parsed qit.json
		// - Resolved the environment configuration
		// - Downloaded all extensions
		// - Created the EnvInfo object

		$env_info = $result->env_info;

		// Apply CLI overrides that weren't handled in PreCommand
		$this->applyCliOverrides( $env_info, $input );

		// Handle tunnel
		if ( $env_info->tunnel ) {
			try {
				$this->tunnel_runner->check_tunnel_support( $env_info->tunnel_type );
			} catch ( \Exception $e ) {
				$output->writeln( '<error>' . $e->getMessage() . '</error>' );

				return Command::FAILURE;
			}
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

	/**
	 * Apply CLI overrides that weren't handled in PreCommand
	 */
	protected function applyCliOverrides( $env_info, InputInterface $input ): void {
		// Apply version overrides
		if ( $wp = $input->getOption( 'wp_version' ) ) {
			$env_info->wp_version = $wp;
		}
		if ( $woo = $input->getOption( 'woo_version' ) ) {
			$env_info->woo_version = $woo;
		}
		if ( $php = $input->getOption( 'php_version' ) ) {
			$env_info->php_version = $php;
		}

		// Apply other options
		if ( $input->getOption( 'object_cache' ) ) {
			$env_info->object_cache = true;
		}
		if ( $input->getOption( 'skip_activating_plugins' ) ) {
			$env_info->skip_activating_plugins = true;
		}
		if ( $input->getOption( 'skip_activating_themes' ) ) {
			$env_info->skip_activating_themes = true;
		}

		// Handle tunnel
		$tunnel                = $input->getOption( 'tunnel' ) ?? 'no_tunnel';
		$env_info->tunnel      = $tunnel !== 'no_tunnel';
		$env_info->tunnel_type = $tunnel;
	}
}