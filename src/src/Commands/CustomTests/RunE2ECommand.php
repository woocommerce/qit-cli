<?php
/*
 * We need this to shut down the environment if the user
 * press "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function QIT_CLI\is_windows;

class RunE2ECommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	/** @var E2ETestManager */
	protected $e2e_test_manager;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		OutputInterface $output,
		E2ETestManager $e2e_test_manager,
		WooExtensionsList $woo_extensions_list
	) {
		$this->e2e_environment     = $e2e_environment;
		$this->cache               = $cache;
		$this->output              = $output;
		$this->e2e_test_manager    = $e2e_test_manager;
		$this->woo_extensions_list = $woo_extensions_list;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		$this
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'A WooCommerce Extension Slug or Marketplace ID.' )
			->addArgument( 'test_path', InputArgument::OPTIONAL, 'Path to your E2E tests (Optional, if not set, it will try to download your custom tests that you have previously uploaded to QIT)' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Plugin to activate in the environment. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Theme install, if multiple provided activates the last. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '(Optional) Additional volume mappings, eg: /home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin.', [] )
			->addOption( 'php_ext', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install in the environment.', [] )
			->addOption( 'require', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Load PHP file before running the command (may be used more than once).' )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, '(Optional) Whether to enable Object Cache (Redis) in the environment.' )
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins in the environment.' )
			->addOption( 'test_tag', null, InputOption::VALUE_OPTIONAL, 'The tag of the test to run. If not set, it will run the default test.', 'default' )
			->addOption( 'sut', null, InputOption::VALUE_OPTIONAL, 'Path to the development build of the extension you are testing. Accepts a WordPress plugin or theme directory, or a WordPress plugin or theme zip file.', null )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'The WooCommerce Version. Accepts "nightly", "stable", or a GitHub Tag (eg: 8.6.1).' )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Runs tests in UI mode. In this mode, you can start and view the tests running.' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run the environment for Codegen. In this mode, you can generate your test files.' )
			->addOption( 'testing_theme', null, InputOption::VALUE_NONE, 'If the "woo_extension" is a theme, set this flag.' )
			->addOption( 'bootstrap_only', 'b', InputOption::VALUE_NONE, 'If set, will only start the environment and bootstrap the plugins.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To use run E2E Tests on Window, please use WSL. Check our guide here: https://qit.woo.com/docs/windows.</comment>' );

			return Command::FAILURE;
		}

		try {
			$options        = $this->parse_options( $input );
			$env_up_options = $options['env_up'];
			$other_options  = $options['other'];
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		/*
		foreach ( $other_options as $k => $o ) {
			if ( ! in_array( $k, [ 'compatibility', 'woo' ], true ) ) {
				$this->output->writeln( sprintf( '<comment>Option "%s" is not part of the "env:up" command definition and will be ignored.</comment>', $k ) );
			}
		}
		*/

		// Check if option "--ui" is set. If it is, "--codegen" cannot be set. Also check the other way around, and set $test_mode accordingly.
		if ( $input->getOption( 'ui' ) && $input->getOption( 'codegen' ) ) {
			$output->writeln( '<error>Cannot run tests in both "UI" and "Codegen" mode at the same time.</error>' );

			return Command::INVALID;
		}

		if ( $input->getOption( 'ui' ) ) {
			$test_mode = E2ETestManager::$test_modes['ui'];
		} elseif ( $input->getOption( 'codegen' ) ) {
			putenv( 'QIT_CODEGEN=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			$test_mode = E2ETestManager::$test_modes['codegen'];
		} else {
			$test_mode = E2ETestManager::$test_modes['headless'];
		}

		$woo_extension = $input->getArgument( 'woo_extension' );

		if ( empty( $woo_extension ) && $test_mode !== E2ETestManager::$test_modes['codegen'] ) {
			$output->writeln( '<error>The extension parameter is only optional in --codegen mode.</error>' );

			return Command::INVALID;
		}

		// SUT.
		if ( ! empty( $woo_extension ) ) {
			// Testing a SUT that is published.
			try {
				$this->woo_extensions_list->check_woo_extension_exists( $input->getArgument( 'woo_extension' ) );

				if ( is_numeric( $input->getArgument( 'woo_extension' ) ) ) {
					$woo_extension = $this->woo_extensions_list->get_woo_extension_slug_by_id( $input->getArgument( 'woo_extension' ) );
				} else {
					$woo_extension = $input->getArgument( 'woo_extension' );
				}
			} catch ( \Exception $e ) {
				$this->output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

				return Command::FAILURE;
			}
		}

		// Local ZIP build for the SUT.
		$sut_override = $input->getOption( 'sut' );

		if ( ! empty( $sut_override ) ) {
			if ( empty( $woo_extension ) ) {
				$this->output->writeln( '<error>Cannot set the "sut" option without setting the "woo_extension" argument.</error>' );

				return Command::INVALID;
			}

			putenv( sprintf( 'QIT_SUT_OVERRIDE=%s', $sut_override ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		// Test Path, if local.
		$test_path = $input->getArgument( 'test_path' );

		if ( ! empty( $test_path ) && file_exists( $test_path ) && ! empty( $woo_extension ) ) {
			putenv( sprintf( 'QIT_CUSTOM_TESTS_PATH=%s|%s', $woo_extension, $test_path ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		$bootstrap_only = $input->getOption( 'bootstrap_only' ) || $test_mode === 'codegen';

		if ( ! empty( $woo_extension ) ) {
			$test_tag = $input->getOption( 'test_tag' );
			if ( ! empty( $test_tag ) ) {
				$woo_extension = $woo_extension . ':' . $test_tag;
			}

			if ( $input->getOption( 'testing_theme' ) === 'true' ) {
				$env_up_options['--theme'][] = $woo_extension;
			} else {
				$env_up_options['--plugin'][] = $woo_extension;
			}
		}

		$additional_volumes = [];

		$env_up_options['--volume'] = $additional_volumes;
		$env_up_options['--json']    = true;

		if ( $output->isVerbose() ) {
			$env_up_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$env_up_options['--very-verbose'] = true;
		}

		if ( $input->getOption( 'object_cache' ) ) {
			$env_up_options['--object_cache'] = true;
		}

		// Invoke the "env:up" Command.
		$env_up_command = $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() );

		// Schedule a catch-all for this environment to be terminated when this script ends (ungracefully).
		$this->handle_termination();

		$resource_stream = fopen( 'php://temp', 'w+' );

		/*
		 * By default "run:e2e" configures the site URL in "container mode",
		 * which means the site URL will be accessible by other containers
		 * in the network, but not from host.
		 *
		 * If we run in "bootstrap_only" mode, we want to expose the site
		 * URL to the host, so that the user can access it from the browser.
		 */
		if ( $bootstrap_only ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( sprintf( 'QIT_SUT=%s', $woo_extension ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		$up_exit_status_code = $env_up_command->run(
			new ArrayInput( $env_up_options ),
			new StreamOutput( $resource_stream )
		);

		// Read from the stream.
		$up_output = stream_get_contents( $resource_stream, - 1, 0 );
		$env_json  = json_decode( $up_output, true );

		if ( ! is_array( $env_json ) || empty( $env_json['env_id'] ) ) {
			$this->output->writeln( sprintf( '<error>Failed to parse the environment JSON. Output: %s</error>', $up_output ) );

			return Command::FAILURE;
		}

		/** @var E2EEnvInfo $env_info */
		$env_info = E2EEnvInfo::from_array( $env_json );

		// Store in $GLOBALS so that's available in the shutdown function.
		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( $up_exit_status_code !== Command::SUCCESS ) {
			$this->output->writeln( sprintf( '<error>Failed to start the environment. Output: %s</error>', stream_get_contents( $resource_stream, - 1, 0 ) ) );
			Environment::down( $env_json['env_id'], new NullOutput() );

			return Command::FAILURE;
		}

		if ( is_null( $woo_extension ) ) {
			$woo_extension = '';
		}

		$this->e2e_test_manager->run_tests( $env_info, $compatibility, $test_mode, $bootstrap_only, $woo_extension );

		return Command::SUCCESS;
	}

	public static function shutdown_test_run(): void {
		echo "\nShutting down environment...\n";
		// Env not up or could not parse the "up" JSON.
		if ( empty( $GLOBALS['env_to_shutdown'] ) || ! $GLOBALS['env_to_shutdown'] instanceof EnvInfo ) {
			return;
		}
		try {
			Environment::down( $GLOBALS['env_to_shutdown'], new NullOutput() );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// no-op.
		}
	}

	/**
	 * This function will shut down the temporary environment
	 * when the test finishes or is aborted.
	 */
	private function handle_termination(): void {
		/*
		 * PHP-handled termination, such as:
		 * - Script finished executing.
		 * - There was a fatal, an exception, etc.
		 */
		register_shutdown_function( static function () {
			static::shutdown_test_run();
		} );

		/*
		 * If "pcntl" extension is available, handle OS signals, such as:
		 *
		 * - Ctrl+C (SIGINT)
		 * - kill (SIGTERM)
		 *
		 * This way if a test is running and the user kills the process
		 * with "Ctrl+C" we can terminate the temporary environments.
		 */
		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function (): void {
				static::shutdown_test_run();
				exit;
			};

			pcntl_signal( SIGINT, $signal_handler ); // Ctrl+C.
			pcntl_signal( SIGTERM, $signal_handler ); // eg: kill 123, where "123" is the PID of this PHP process.
		}
	}

	/**
	 * This function will validate the required options, categorize them
	 * into options that are to be sent to "env:up" and the rest.
	 *
	 * This can then be used to pass to "env:up" the options that it needs,
	 * while using the other options for the rest.
	 *
	 * @param InputInterface $input
	 *
	 * @return array<mixed>
	 */
	protected function parse_options( InputInterface $input, bool $filter_to_send = true ): array {
		$options = parent::parse_options( $input, false );

		// Iterate over all options of UpEnvironmentCommand
		// Remote keys in $options array that are not part of the definition
		// Notify user if that happens.
		$up_command_option_names = array_map( function ( $option ) {
			return $option->getName();
		}, $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() )->getDefinition()->getOptions() );

		$parsed_options = [
			'env_up' => [],
			'other'  => [],
		];

		foreach ( $options as $option_name => $option_value ) {
			if ( ! in_array( $option_name, $up_command_option_names, true ) ) {
				$parsed_options['other'][ $option_name ] = $option_value;
			} else {
				$parsed_options['env_up']["--$option_name"] = $option_value;
			}
		}

		return $parsed_options;
	}
}
