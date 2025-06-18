<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Results\EnvironmentResult;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class UpEnvironmentCommand extends QITCommand implements EnvironmentCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var TunnelRunner */
	protected $tunnel_runner;

	protected static $defaultName = 'env:up';
	protected static $defaultDescription = 'Creates a temporary local test environment';

	public function __construct( E2EEnvironment $e2e_environment, TunnelRunner $tunnel_runner ) {
		$this->e2e_environment = $e2e_environment;
		$this->tunnel_runner   = $tunnel_runner;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure(); // Adds --config, --environment options

		$this
			->setDescription( 'Creates a temporary local test environment that is completely ephemeral' )
			->setAliases( [ 'env:start' ] )
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Output JSON format' )
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling. Valid options: ' . implode( ', ', array_keys( TunnelRunner::$tunnel_map ) ), 'no_tunnel' )
			->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL, 'PHP version (e.g., 8.0, 7.4)', '8.2' )
			->addOption( 'wp_version', null, InputOption::VALUE_OPTIONAL, 'WordPress version (latest, nightly, rc, or version number)', 'latest' )
			->addOption( 'woo_version', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version (latest, nightly, rc, or version number)', 'stable' )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Redis object cache' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins to install', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes to install', [] )
			->addOption( 'volume', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volume mappings (local:container)', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install', [] )
			->addOption( 'env', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables (KEY=value)', [] )
			->addOption( 'env_file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Load env vars from file', [] );

		$this->setHelp( $this->getHelpText() );
	}

	public function getEnvironmentName(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function shouldPrepareEnvironment(): bool {
		return true; // We need to download extensions
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To use QIT Environments on Windows, please use WSL.</comment>' );

			return Command::FAILURE;
		}

		/** @var EnvironmentResult $result */
		$result = $this->getPreCommandResult();

		// Everything is already resolved and downloaded!
		$env_info = $result->env_info;

		// Handle tunnel option
		$tunnel = TunnelRunner::get_tunnel_value( $input );
		if ( $tunnel !== 'no_tunnel' ) {
			try {
				$this->tunnel_runner->check_tunnel_support( $tunnel );
				$env_info->tunnel      = true;
				$env_info->tunnel_type = $tunnel;
			} catch ( \Exception $e ) {
				$output->writeln( '<error>' . $e->getMessage() . '</error>' );

				return Command::FAILURE;
			}
		}

		if ( $output->isVeryVerbose() ) {
			$output->writeln( 'Environment info: ' . json_encode( $env_info, JSON_PRETTY_PRINT ) );
		}

		// Initialize and start the environment
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up( $input->hasOption( 'QIT_UP_AND_TEST' ) ? 'up_and_test' : 'up' );

		// Output result
		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		} else {
			$output->writeln( '<info>Environment started successfully!</info>' );
			$output->writeln( '' );
			$output->writeln( 'Environment ID: ' . $env_info->env_id );
			$output->writeln( 'PHP Version: ' . $env_info->php_version );
			$output->writeln( 'WordPress Version: ' . $env_info->wp_version );
			if ( $env_info->woo_version ) {
				$output->writeln( 'WooCommerce Version: ' . $env_info->woo_version );
			}
			$output->writeln( '' );
			$output->writeln( 'Installed Plugins:' );
			foreach ( $env_info->plugins as $plugin ) {
				$output->writeln( '  - ' . $plugin->slug . ' (' . $plugin->version . ')' );
			}
			if ( ! empty( $env_info->themes ) ) {
				$output->writeln( '' );
				$output->writeln( 'Installed Themes:' );
				foreach ( $env_info->themes as $theme ) {
					$output->writeln( '  - ' . $theme->slug . ' (' . $theme->version . ')' );
				}
			}
			$output->writeln( '' );
			$output->writeln( 'Site URL: ' . $env_info->site_url );
		}

		return Command::SUCCESS;
	}

	protected function getHelpText(): string {
		return <<<'HELP'
Creates a configurable, temporary, disposable test environment.

<comment>Usage</comment>
<info>qit env:up</info>
<info>qit env:up --environment=legacy</info>
<info>qit env:up --config=./custom-qit.json</info>

<comment>Configuration File</comment>
Create a qit.json file to define environments:

{
  "$schema": "https://qit.woo.com/json-schema/qit",
  "sut": {
    "slug": "my-plugin",
    "type": "plugin",
    "source": {"type": "local", "path": "./"}
  },
  "environments": {
    "base": {
      "php_version": "8.0",
      "wp_version": "stable",
      "plugins": ["woocommerce", "akismet"]
    },
    "legacy": {
      "extends": "base",
      "php_version": "7.4",
      "wp_version": "5.9"
    }
  }
}

<comment>CLI Options Override Configuration</comment>
<info>qit env:up --php_version=8.3</info> - Forces PHP 8.3 regardless of config
<info>qit env:up --plugin=gutenberg</info> - Adds plugin to configured environment

<comment>Examples</comment>
# Use default environment from qit.json
<info>qit env:up</info>

# Use specific environment
<info>qit env:up --environment=legacy</info>

# Override configuration
<info>qit env:up --php_version=8.3 --plugin=gutenberg --object_cache</info>

# With tunnel for external access
<info>qit env:up --tunnel=cloudflare</info>
HELP;
	}
}