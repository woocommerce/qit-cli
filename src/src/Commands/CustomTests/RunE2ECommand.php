<?php
/*
 * We need this to shut down the environment if the user
 * press "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Extension;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\PluginDependencies;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
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

	/** @var LocalTestRunNotifier */
	protected $test_run_notifier;

	/** @var PluginDependencies */
	protected $dependencies;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		OutputInterface $output,
		E2ETestManager $e2e_test_manager,
		WooExtensionsList $woo_extensions_list,
		LocalTestRunNotifier $test_run_notifier,
		PluginDependencies $dependencies
	) {
		$this->e2e_environment     = $e2e_environment;
		$this->cache               = $cache;
		$this->output              = $output;
		$this->e2e_test_manager    = $e2e_test_manager;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->test_run_notifier   = $test_run_notifier;
		$this->dependencies        = $dependencies;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [], [
			'php_version',
		] );

		$this
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'The slug or WooCommerce ID of the main extension under test.' )
			->addArgument( 'test', InputArgument::OPTIONAL, '(Optional) The tests for the main extension under test. Accepts test tags, or a test directory. If not set, will use the "default" test tag of this extension.' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL. If not provided, the source will be the slug.' )
			->addOption( 'sut_action', null, InputOption::VALUE_OPTIONAL, 'What action to take on the SUT. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['test'] )
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'The WordPress version. Accepts "stable", "nightly", or a version number.', 'stable' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'The WooCommerce Version. Accepts "stable", "nightly", or a GitHub Tag (eg: 8.6.1).', 'stable' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Plugin to activate in the environment. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Theme install, if multiple provided activates the last. Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.', [] )
			->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional volume mappings, eg: /home/mycomputer/my-plugin:/var/www/html/wp-content/plugins/my-plugin.', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions to install in the environment.', [] )
			->addOption( 'require', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Load PHP file before running the command (may be used more than once).' )
			->addOption( 'config', null, InputOption::VALUE_OPTIONAL, '(Optional) QIT config file to use.' )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Whether to enable Object Cache (Redis) in the environment.' )
			->addOption( 'shard', null, InputOption::VALUE_OPTIONAL, 'Playwright Sharding argument.' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Do not upload the report to QIT Manager.' )
			->addOption( 'update_snapshots', null, InputOption::VALUE_NONE, 'Update snapshots where applicable (eg: Playwright Snapshots).' )
			->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional options and parameters to pass to Playwright.' )
			->addOption( 'dependencies', null, InputOption::VALUE_OPTIONAL, 'How to handle SUT dependencies. Possible values are: "activate", "bootstrap", "test", or "none"', Extension::ACTIONS['bootstrap'] )
			->addOption( 'skip_activating_plugins', null, InputOption::VALUE_NONE, 'Skip activating plugins in the environment.' )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Runs tests in UI mode. In this mode, you can start and view the tests running.' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run the environment for Codegen. In this mode, you can generate your test files.' )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it up until you shut it down.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To use run E2E Tests on Window, please use WSL. Check our guide here: https://qit.woo.com/docs/environment/getting-started#getting-started---windows</comment>' );

			return Command::FAILURE;
		}

		try {
			$options        = $this->parse_options( $input );
			$env_up_options = $options['env_up'];
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

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

		$wait                = $input->getOption( 'up_only' ) || $test_mode === E2ETestManager::$test_modes['codegen'];
		$woo_extension       = $input->getArgument( 'woo_extension' );
		$test                = $input->getArgument( 'test' );
		$woocommerce_version = $input->getOption( 'woo' );
		$shard               = $input->getOption( 'shard' );
		$update_snapshots    = $input->getOption( 'update_snapshots' );
		$pw_options          = $input->getOption( 'pw_options' ) ?? '';
		$sut_action          = $input->getOption( 'sut_action' );

		if ( empty( $input->getOption( 'source' ) ) ) {
			$source = $woo_extension;
		} else {
			if ( file_exists( realpath( $input->getOption( 'source' ) ) ) ) {
				$source = realpath( $input->getOption( 'source' ) );
			} else {
				$source = $input->getOption( 'source' );
			}
		}

		// Prevent usage of "--woo" and "--plugin woocommerce" together.
		if ( ! empty( $woocommerce_version ) && ! empty( $input->getOption( 'plugin' ) ) ) {
			foreach ( $input->getOption( 'plugin' ) as $p ) {
				if ( $p === 'woocommerce' ) {
					$output->writeln( '<error>Both "--woo" and "--plugin woocommerce" cannot be used at the same time. Please use one or another when running the test.</error>' );

					return Command::INVALID;
				}
			}
		}

		if ( ! empty( $pw_options ) ) {
			// Remove wrapping double quotes if they exist.
			if ( substr( $pw_options, 0, 1 ) === '"' && substr( $pw_options, - 1 ) === '"' ) {
				$pw_options = substr( $pw_options, 1, - 1 );
			}
		}

		if ( ! empty( $update_snapshots ) ) {
			$pw_options .= ' --update-snapshots';
		}

		App::setVar( 'pw_options', $pw_options );

		// Validate the extension is set if needed.
		if ( empty( $woo_extension ) ) {
			if ( ! empty( $source ) ) {
				$output->writeln( '<error>The extension parameter is required when the source parameter is set.</error>' );

				return Command::INVALID;
			}
			if ( ! empty( $sut_action ) ) {
				$output->writeln( '<error>The extension parameter is required when the sut_action parameter is set.</error>' );

				return Command::INVALID;
			}
			if ( ! $wait ) {
				$output->writeln( '<error>The extension parameter is only optional in --up_only or --codegen modes.</error>' );

				return Command::INVALID;
			}
		}

		if ( ! empty( $woo_extension ) ) {
			// Validate WooExtension.
			try {
				if ( is_numeric( $woo_extension ) ) {
					$woo_extension_id = $woo_extension;
					$woo_extension    = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_extension );
				} else {
					$woo_extension_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_extension );
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

				return Command::INVALID;
			}

			$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_extension_id );

			if ( ! empty( $test ) ) {
				$woo_extension_extension_syntax = sprintf( '%s:%s:base64%s', $woo_extension, $sut_action, base64_encode( $test ) );
			} else {
				$woo_extension_extension_syntax = sprintf( '%s:%s', $woo_extension, $sut_action );
			}

			if ( $sut_type === 'theme' ) {
				$env_up_options['--theme'][] = $woo_extension_extension_syntax;
			} else {
				$env_up_options['--plugin'][] = $woo_extension_extension_syntax;
			}

			if ( $input->getOption( 'dependencies' ) !== 'none' ) {
				$dependencies_action = $input->getOption( 'dependencies' );

				if ( ! in_array( $dependencies_action, Extension::ACTIONS, true ) ) {
					$output->writeln( sprintf( '<error>Invalid dependencies action. Possible values are: none, %s.</error>', implode( ', ', Extension::ACTIONS ) ) );

					return Command::INVALID;
				}

				/*
				 * Todo: Also handle dependencies of additional WCCOM plugins passed as "--plugin".
				 * For this, we need to parse the "--plugin" option array and see if we can resolve them as WCCOM IDs.
				 * This is not needed for now for the Activation Test.
				 */
				$dependencies = $this->dependencies->get_plugin_and_php_ext_dependencies( $woo_extension_id, [] );

				foreach ( $dependencies['php_extensions'] as $php_extension ) {
					$env_up_options['--php_extension'][] = $php_extension;
				}

				foreach ( $dependencies['plugins'] as $plugin ) {
					foreach ( $env_up_options['--plugin'] as $p ) {
						// If the plugin is already in the list, skip it.
						if ( strpos( $p, $plugin ) !== false ) {
							continue 2;
						}
					}
					$env_up_options['--plugin'][] = "$plugin:$dependencies_action";
				}
			}
		}

		if ( ! is_null( $shard ) ) {
			if ( ! preg_match( '/^\d+\/\d+$/', $shard ) ) {
				$output->writeln( '<error>Invalid shard format. Should be in the format current/total, eg: 1/5.</error>' );

				return Command::INVALID;
			}

			// Validate first part is higher than 0, and lower or equal to the second part.
			[ $current, $total ] = explode( '/', $shard );
			if ( $current <= 0 || $current > $total ) {
				$output->writeln( '<error>Invalid shard format. First part should be higher than 0, and lower or equal to the second part.</error>' );

				return Command::INVALID;
			}
		}

		$additional_volumes = [];

		$env_up_options['--volume'] = $additional_volumes;
		$env_up_options['--json']   = true;

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
		if ( $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( sprintf( 'QIT_SUT=%s', $woo_extension ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( sprintf( 'QIT_SUT_SOURCE=%s', $source ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		if ( ! empty( $woo_extension_id ) ) {
			App::setVar( 'QIT_SUT', (int) $woo_extension_id );
		}

		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );

		App::setVar( 'QIT_ENV_UP_OPTIONS', $env_up_options );

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

		App::singleton( E2EEnvInfo::class, $env_info );

		if ( ! empty( $woo_extension_id ) ) {
			$env_info->sut_slug = $woo_extension;
			$env_info->sut_id   = $woo_extension_id;
			// @phan-suppress-next-line PhanPossiblyUndeclaredVariable
			$env_info->sut_type = $sut_type; // @phpstan-ignore-line
			$this->test_run_notifier->notify_test_started( $woo_extension_id, $woocommerce_version ?? 'none', $env_info );
		}

		// Store in $GLOBALS so that's available in the shutdown function.
		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( $up_exit_status_code !== Command::SUCCESS ) {
			$this->output->writeln( sprintf( '<error>Failed to start the environment. Output: %s</error>', stream_get_contents( $resource_stream, - 1, 0 ) ) );
			Environment::down( $env_json['env_id'], new NullOutput() );

			return Command::FAILURE;
		}

		$exit_status_code = $this->e2e_test_manager->run_tests( $env_info, $test_mode, $wait, $shard );

		$io = new SymfonyStyle( $input, $output );

		if ( $exit_status_code === Command::SUCCESS ) {
			$io->success( "Tests passed. Run 'qit e2e-report' to view the report." );

			return Command::SUCCESS;
		} else {
			if ( $test_mode === E2ETestManager::$test_modes['headless'] ) {
				$io->error( "Tests failed. Run 'qit e2e-report' to view the report." );
			}

			return Command::FAILURE;
		}
	}

	public static function shutdown_test_run(): void {
		static $did_shutdown = false;

		if ( $did_shutdown ) {
			return;
		} else {
			$did_shutdown = true;
		}

		if ( App::make( 'QIT_JSON_MODE' ) !== true ) {
			echo "\nShutting down environment...\n";
		}
		
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
				$parsed_options['env_up'][ "--$option_name" ] = $option_value;
			}
		}

		return $parsed_options;
	}
}
