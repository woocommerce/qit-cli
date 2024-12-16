<?php
/*
 * We need this to shut down the environment if the user
 * press "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Extension;
use QIT_CLI\LocalTests\ConfigurationProcessor;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\PluginDependencies;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;

class RunE2ECommand extends DynamicCommand {
	use OptionReuseTrait;

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

	/** @var ConfigurationProcessor */
	protected $configuration_processor;

	/** @var EnvironmentRunner */
	protected $environment_runner;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * 0 is success.
	 * 1 is either Playwright failed an assertion from a user-perspective, or a PHP fatal error has been logged.
	 * 2 is reserved, so we skip it.
	 * 3 is a warning, such as a PHP error that is not fatal.
	 *
	 * @link https://tldp.org/LDP/abs/html/exitcodes.html
	 */
	const WARNING = 3;

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		E2ETestManager $e2e_test_manager,
		WooExtensionsList $woo_extensions_list,
		LocalTestRunNotifier $test_run_notifier,
		PluginDependencies $dependencies,
		ConfigurationProcessor $configuration_processor,
		EnvironmentRunner $environment_runner
	) {
		$this->e2e_environment         = $e2e_environment;
		$this->cache                   = $cache;
		$this->e2e_test_manager        = $e2e_test_manager;
		$this->woo_extensions_list     = $woo_extensions_list;
		$this->test_run_notifier       = $test_run_notifier;
		$this->dependencies            = $dependencies;
		$this->configuration_processor = $configuration_processor;
		$this->environment_runner      = $environment_runner;

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
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'wp' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'woo' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'theme' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'require' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'config' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_plugins' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'tunnel' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'json' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env_file' )
			->addOption( 'shard', null, InputOption::VALUE_OPTIONAL, 'Playwright Sharding argument.' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Do not upload the report to QIT Manager.' )
			->addOption( 'update_snapshots', null, InputOption::VALUE_NONE, 'Update snapshots where applicable (eg: Playwright Snapshots).' )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'If set, failures will be notified to the author of the SUT.' )
			->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional options and parameters to pass to Playwright, eg: "--retries=0", etc.' )
			->addOption( 'dependencies', null, InputOption::VALUE_OPTIONAL, 'How to handle dependencies of the SUT and additional plugins. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['bootstrap'] )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Runs tests in UI mode.' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen.' )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it running until shut down.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->prepare_output( $output );

		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E Tests on Windows, please use WSL.</comment>' );

			return Command::FAILURE;
		}

		try {
			$options                    = $this->parse_options( $input );
			$env_up_options             = $options['env_up'];
			$env_up_options['--tunnel'] = TunnelRunner::get_tunnel_value( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		try {
			[ $test_mode, $wait ] = $this->determine_test_mode( $input );
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::INVALID;
		}

		App::setVar( 'TEST_MODE', $test_mode );

		$result = $this->validate_input( $input, $output, $wait );
		if ( $result !== Command::SUCCESS ) {
			return $result;
		}

		$this->configure_pw_options( $input );

		$this->parse_env_vars( $input->getOption( 'env' ) );

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		[ $woo_extension_id, $woo_extension_slug, $sut_type_or_code ] = $this->resolve_woo_extension( $woo_extension_raw, $output );
		if ( $sut_type_or_code === Command::INVALID ) {
			// Failed to resolve woo extension.
			return Command::INVALID;
		}
		$sut_type = $sut_type_or_code;

		if ( $input->getOption( 'skip_activating_plugins' ) ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}

		if ( ! empty( $input->getOption( 'config' ) ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
		}

		$shard = $input->getOption( 'shard' );
		if ( $shard && preg_match( '/^\d+\/\d+$/', $shard ) ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
			// Already validated above.
		}

		$additional_volumes         = [];
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

		$this->handle_termination();

		if ( ! empty( $woo_extension_slug ) ) {
			App::setVar( 'QIT_SUT', $woo_extension_id );
		}

		$env_up_options = $this->configuration_processor->process_configuration(
			$woo_extension_slug,
			$input,
			$env_up_options,
			$sut_type
		);

		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		App::setVar( 'QIT_ENV_UP_OPTIONS', $env_up_options );

		if ( getenv( 'QIT_TESTING_ENV_CONFIG' ) ) {
			$this->output->writeln( json_encode( $env_up_options, JSON_PRETTY_PRINT ) );

			return Command::SUCCESS;
		}

		if ( $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		try {
			/** @var E2EEnvInfo $env_info */
			$env_info = $this->environment_runner->run_environment( $env_up_options );

			if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
				$output->write( json_encode( $env_info ) );

				return Command::SUCCESS;
			}
		} catch ( \Exception $e ) {
			$this->output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		putenv( 'QIT_HIDE_SITE_INFO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		if ( $env_info instanceof E2EEnvInfo && ! empty( $woo_extension_id ) ) {
			$env_info->sut_slug = $woo_extension_slug;
			$env_info->sut_id   = $woo_extension_id;
			$env_info->sut_type = $sut_type;

			$this->test_run_notifier->notify_test_started(
				$woo_extension_id,
				$input->getOption( 'woo' ) ?? 'none',
				$env_info,
				$this->configuration_processor->is_development(),
				$input->getOption( 'notify' )
			);
		}

		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info ) );

			return Command::SUCCESS;
		}

		$shard            = $input->getOption( 'shard' );
		$exit_status_code = $this->e2e_test_manager->run_tests( $env_info, $test_mode, $wait, $shard );
		$io               = new SymfonyStyle( $input, $output );
		$io->setDecorated( true );

		if ( $exit_status_code === Command::SUCCESS ) {
			$io->success( "Tests passed. Run 'qit e2e-report' to view the report." );

			return Command::SUCCESS;
		} elseif ( $exit_status_code === self::WARNING ) {
			if ( $test_mode === E2ETestManager::$test_modes['headless'] ) {
				$io->warning( "Tests passed with a warning. Run 'qit e2e-report' to view the report." );
			}

			return self::WARNING;
		} else {
			if ( $test_mode === E2ETestManager::$test_modes['headless'] ) {
				$io->error( "Tests failed. Run 'qit e2e-report' to view the report." );
			}

			return Command::FAILURE;
		}
	}

	/**
	 * Prepare the output for JSON mode if needed.
	 *
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	protected function prepare_output( OutputInterface $output ): void {
		$this->output = $output;

		if ( App::getVar( 'QIT_JSON_MODE' ) === true || defined( 'UNIT_TESTS' ) ) {
			// Ensure $output is a StreamOutput to call getStream().
			if ( ! $output instanceof StreamOutput ) {
				throw new \RuntimeException( 'QIT_JSON_MODE is set, but OutputInterface is not a StreamOutput.' );
			}

			$stream = $output->getStream();
			if ( ! stream_filter_append( $stream, 'qit_json' ) ) {
				exit( 152 );
			}
		}
	}

	public static function shutdown_test_run(): void {
		static $did_shutdown = false;
		if ( $did_shutdown ) {
			return;
		}
		$did_shutdown = true;

		if ( App::getVar( 'QIT_JSON_MODE' ) !== true ) {
			echo "\nShutting down environment...\n";
		}

		if ( empty( $GLOBALS['env_to_shutdown'] ) || ! $GLOBALS['env_to_shutdown'] instanceof EnvInfo ) {
			return;
		}

		try {
			Environment::down( $GLOBALS['env_to_shutdown'] );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// no-op.
		}
	}

	private function handle_termination(): void {
		register_shutdown_function( static function () {
			static::shutdown_test_run();
		} );

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function (): void {
				static::shutdown_test_run();
				exit( 0 );
			};
			pcntl_signal( SIGINT, $signal_handler );
			pcntl_signal( SIGTERM, $signal_handler );
		}
	}

	protected function parse_options( InputInterface $input, bool $filter_to_send = true ): array {
		$options = parent::parse_options( $input, false );

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

	/**
	 * @param array<string> $env_vars
	 *
	 * @return void
	 */
	protected function parse_env_vars( array $env_vars ): void {
		$parsed_vars = [];
		foreach ( $env_vars as $env_var ) {
			$env_var = explode( '=', $env_var, 2 );
			if ( count( $env_var ) !== 2 ) {
				throw new \RuntimeException( 'Invalid environment variable format. Use "--env FOO=bar".' );
			}

			$key   = trim( $env_var[0] );
			$value = trim( $env_var[1] );

			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) ) {
				throw new \RuntimeException( 'Invalid env var name. Letters, numbers, underscores only.' );
			}

			$parsed_vars[ $key ] = $value;
		}

		App::setVar( 'QIT_PW_ENV_VARS', $parsed_vars );
	}

	/**
	 * @param string|null     $woo_extension_raw
	 * @param OutputInterface $output
	 *
	 * @return array{0:int|null,1:string|null,2:string|int|null} Array containing:
	 *                                                            [woo_extension_id, woo_extension_slug, sut_type or Command::INVALID]
	 */
	private function resolve_woo_extension( ?string $woo_extension_raw, OutputInterface $output ): array {
		if ( empty( $woo_extension_raw ) ) {
			return [ null, null, null ]; // no extension provided.
		}

		try {
			if ( is_numeric( $woo_extension_raw ) ) {
				$woo_extension_id   = (int) $woo_extension_raw;
				$woo_extension_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_extension_id );
			} else {
				$woo_extension_slug = $woo_extension_raw;
				$woo_extension_id   = $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_extension_slug );
			}
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return [ null, null, Command::INVALID ];
		}

		$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_extension_id );
		putenv( "QIT_SUT=$woo_extension_slug" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		return [ $woo_extension_id, $woo_extension_slug, $sut_type ];
	}

	/**
	 * Determine the test mode and whether to wait.
	 *
	 * @param InputInterface $input
	 *
	 * @return array{0:string,1:bool} Returns an array where:
	 *                                [0] = test_mode (string)
	 *                                [1] = wait (bool)
	 *
	 * @throws \RuntimeException If both ui and codegen are set.
	 */
	private function determine_test_mode( InputInterface $input ): array {
		if ( $input->getOption( 'ui' ) && $input->getOption( 'codegen' ) ) {
			throw new \RuntimeException( 'Cannot run tests in both "UI" and "Codegen" mode at the same time.' );
		}

		if ( $input->getOption( 'ui' ) ) {
			$test_mode = E2ETestManager::$test_modes['ui'];
		} elseif ( $input->getOption( 'codegen' ) ) {
			putenv( 'QIT_CODEGEN=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			$test_mode = E2ETestManager::$test_modes['codegen'];
		} else {
			$test_mode = E2ETestManager::$test_modes['headless'];
		}

		$wait = $input->getOption( 'up_only' ) || $test_mode === E2ETestManager::$test_modes['codegen'];

		return [ $test_mode, $wait ];
	}

	private function validate_input( InputInterface $input, OutputInterface $output, bool $wait ): int {
		$woo     = $input->getOption( 'woo' );
		$plugins = $input->getOption( 'plugin' );

		if ( ! empty( $woo ) && ! empty( $plugins ) && in_array( 'woocommerce', $plugins, true ) ) {
			$output->writeln( '<error>Cannot use both "--woo" and "--plugin woocommerce" together.</error>' );

			return Command::INVALID;
		}

		$shard = $input->getOption( 'shard' );
		if ( ! is_null( $shard ) ) {
			if ( ! preg_match( '/^\d+\/\d+$/', $shard ) ) {
				$output->writeln( '<error>Invalid shard format. Should be current/total, e.g., 1/5.</error>' );

				return Command::INVALID;
			}
			[ $current, $total ] = explode( '/', $shard );
			if ( $current <= 0 || $current > $total ) {
				$output->writeln( '<error>Invalid shard format. current must be > 0 and <= total.</error>' );

				return Command::INVALID;
			}
		}

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		if ( empty( $woo_extension_raw ) ) {
			if ( ! empty( $input->getOption( 'source' ) ) || ! empty( $input->getOption( 'sut_action' ) ) ) {
				$output->writeln( '<error>The extension parameter is required when source or sut_action is set.</error>' );

				return Command::INVALID;
			}
			if ( ! $wait ) {
				$output->writeln( '<error>The extension parameter is required unless in --up_only or --codegen mode.</error>' );

				return Command::INVALID;
			}
		}

		return Command::SUCCESS;
	}

	private function configure_pw_options( InputInterface $input ): void {
		$pw_options = $input->getOption( 'pw_options' ) ?? '';
		if ( ! empty( $pw_options ) ) {
			if ( substr( $pw_options, 0, 1 ) === '"' && substr( $pw_options, - 1 ) === '"' ) {
				$pw_options = substr( $pw_options, 1, - 1 );
			}
		}

		if ( $input->getOption( 'update_snapshots' ) ) {
			$pw_options .= ' --update-snapshots';
		}

		App::setVar( 'pw_options', $pw_options );
	}
}
