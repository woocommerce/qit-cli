<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\App;
use QIT_CLI\OptionReuseTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class UpEnvironmentCommand extends QITCommand {
	use OptionReuseTrait;

	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var TunnelRunner */
	protected $tunnel_runner;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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

			/*
			─────────────── Environment Configuration ───────────────
			*/
			// Override parent's --environment option with clearer help text
			->addOption(
				'environment', 'e',
				InputOption::VALUE_OPTIONAL,
				'Pick an <comment>environment block</comment> from qit.json (e.g. --environment=legacy)',
				'default'
			)

			/* ─────────────── Runtime Variables ─────────────── */
			->addOption(
				'env', null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Set a <info>runtime variable</info> (repeatable)  --env KEY=VAL  --env FOO=BAR',
				[]
			)
			->addOption(
				'env_file', null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Load variables from file (repeatable)       --env-file ./prod.env',
				[]
			)

			/* ─────────────── Environment Options ─────────────── */
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Output JSON format' )
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling. Valid options: ' . implode( ', ', array_keys( TunnelRunner::$tunnel_map ) ), 'no_tunnel' )
			->addOption( 'php', null, InputOption::VALUE_OPTIONAL, 'PHP version (e.g., 8.0, 7.4)', '8.2' )
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'WordPress version (stable, nightly, rc, or version number)', 'stable' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version (stable, nightly, rc, or version number)' )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Redis object cache' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins to install', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes to install', [] )
			->addOption( 'volume', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volume mappings (local:container)', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install', [] );

		$this->setHelp( $this->getHelpText() );
	}

	public function get_environment_name(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function should_prepare_environment(): bool {
		return true; // We need to download extensions
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To use QIT Environments on Windows, please use WSL.</comment>' );

			return Command::FAILURE;
		}

		// Get the merged environment configuration using the simplified API
		$env_config = $this->get_environment_config( $this->get_environment_name() );

		// Explicitly download extensions for current environment (lazy loading)
		$resolved_extensions = $this->download_extensions();

		// Create proper E2EEnvInfo object from the config
		$env_info_array = [
			'environment'    => 'e2e',
			'env_id'         => 'qitenv' . bin2hex( random_bytes( 8 ) ),
			'php'            => $env_config['php'] ?? '8.2',
			'wp'             => $env_config['wp'] ?? 'stable',
			'woo'            => $env_config['woo'] ?? '',
			'plugins'        => $env_config['plugins'] ?? [],
			'themes'         => $env_config['themes'] ?? [],
			'volumes'        => $env_config['volumes'] ?? [],
			'php_extensions' => $env_config['php_extensions'] ?? [],
			'envs'           => $env_config['envs'] ?? [],
			'env_files'      => $env_config['env_files'] ?? [],
			'object_cache'   => $env_config['object_cache'] ?? false,
			'tunnel'         => ( $env_config['tunnel'] ?? false ) === true,
			'tunnel_type'    => $env_config['tunnel_type'] ?? 'no_tunnel',
			'site_url'       => 'http://localhost:8080', // This would be determined during setup
		];

		$env_info = \QIT_CLI\Environment\Environments\EnvInfo::from_array( $env_info_array );

		// Handle QIT_SELF_TEST=precommand early return
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->writeln( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );

			return Command::SUCCESS;
		}

		// Type assertion: we know this is E2EEnvInfo since environment is 'e2e'
		assert( $env_info instanceof E2EEnvInfo );

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
			$output->writeln( 'Environment info: ' . json_encode( $env_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		}

		// Initialize and start the environment
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up( $input->hasOption( 'QIT_UP_AND_TEST' ) ? 'up_and_test' : 'up' );

		// Output result
		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );
		} else {
			$output->writeln( '<info>Environment started successfully!</info>' );
			$output->writeln( '' );
			$output->writeln( 'Environment ID: ' . $env_info->env_id );
			$output->writeln( 'PHP Version: ' . $env_info->php );
			$output->writeln( 'WordPress Version: ' . $env_info->wp );
			if ( $env_info->woo ) {
				$output->writeln( 'WooCommerce Version: ' . $env_info->woo );
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

<comment>Note:</comment> Packages declared in <info>bootstrap_packages</info> run <info>only</info> their <info>globalSetup</info> phase.

{
  "$schema": "https://qit.woo.com/json-schema/qit",
  "sut": {
    "slug": "my-plugin",
    "type": "plugin",
    "source": {"type": "local", "path": "./"}
  },
  "environments": {
    "base": {
      "php": "8.0",
      "wp": "stable",
      "plugins": ["woocommerce", "akismet"],
      "bootstrap_packages": ["vendor/setup-package:stable"]
    },
    "legacy": {
      "extends": "base",
      "php": "7.4",
      "wp": "5.9"
    }
  }
}

<comment>CLI Options Override Configuration</comment>
<info>qit env:up --php=8.3</info> - Forces PHP 8.3 regardless of config
<info>qit env:up --plugin=gutenberg</info> - Adds plugin to configured environment

<comment>Examples</comment>
# Use default environment from qit.json
<info>qit env:up</info>

# Use specific environment
<info>qit env:up --environment=legacy</info>

# Override configuration
<info>qit env:up --php=8.3 --plugin=gutenberg --object_cache</info>

# With tunnel for external access
<info>qit env:up --tunnel=cloudflare</info>
HELP;
	}
}
