<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Commands\GetCommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\EnvironmentRunner;
use QIT_CLI\Utils\LocalTestRunNotifier;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Performance\PerformanceTestManager;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use QIT_CLI\TestGroup;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class RunPerformanceTestCommand extends DynamicCommand {
	use OptionReuseTrait;

	/** @var Cache */
	protected $cache;

	/** @var PerformanceTestManager */
	protected $performance_test_manager;

	/** @var EnvironmentRunner */
	protected $environment_runner;

	/** @var LocalTestRunNotifier */
	protected $test_run_notifier;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var TestGroup */
	protected $test_group;

	/** @var Upload */
	protected $upload;

	protected static $defaultName = 'run:performance'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	protected string $test_type   = 'performance';

	public function __construct(
		Cache $cache,
		PerformanceTestManager $performance_test_manager,
		EnvironmentRunner $environment_runner,
		LocalTestRunNotifier $test_run_notifier,
		WooExtensionsList $woo_extensions_list,
		TestGroup $test_group,
		Upload $upload
	) {
		$this->cache                    = $cache;
		$this->performance_test_manager = $performance_test_manager;
		$this->environment_runner       = $environment_runner;
		$this->test_run_notifier        = $test_run_notifier;
		$this->woo_extensions_list      = $woo_extensions_list;
		$this->test_group               = $test_group;
		$this->upload                   = $upload;

		parent::__construct( $this->test_type );
	}

	protected function configure(): void {
		parent::configure();

		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['performance']['properties'] ) ) {
			throw new \RuntimeException( 'Performance schema not set or incomplete.' );
		}

		$this
			->setDescription( 'Run k6 performance tests (local or remote)' )
			->setHelp( 'Run k6 performance tests against a given extension.' );

		DynamicCommandCreator::add_schema_to_command(
			$this,
			$schemas['performance'],
			[],
			[]  // No whitelist - include all properties.
		);

		$this
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'Extension slug or WooCommerce.com ID' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, '(Optional) Local ZIP / dir / URL build to test' )
			->addOption( 'async', null, InputOption::VALUE_NEGATABLE, '(Optional) Enqueue test and return immediately without waiting', false )
			->addOption( 'wait', null, InputOption::VALUE_NEGATABLE, '(Deprecated) Wait for test completion - this is now the default behavior', false )
			->addOption( 'print-report-url', null, InputOption::VALUE_NEGATABLE, '(Optional) Print the test report URL (contains sensitive data - use cautiously in public logs)', false )
			->addOption( 'timeout', null, InputOption::VALUE_OPTIONAL, '(Optional) Wait timeout in seconds', null )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the run into a group', false )
			->addOption( 'no_baseline', null, InputOption::VALUE_NONE, 'Skip running baseline performance tests' )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'If set, failures will be notified to the author of the SUT' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'theme' )
			->addOption( 'local', null, InputOption::VALUE_NONE, 'Run tests locally instead of on QIT infrastructure' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_version' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'tunnel' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'json' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env_file' )
			->addOption( 'test-package', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Test packages to include (multiple values allowed)', [] )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Do not upload the report to QIT Manager' )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, just start the environment and keep it running until shut down' )
			->addOption( 'no_group', 'ng', InputOption::VALUE_NEGATABLE, 'If set, the CLI will not attempt to match the local test run with a group', false );

			$this->add_option_to_send( 'source' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Initialize common test context.
		$context = $this->initialize_test_context( $input, $output );

		// Early return on validation failure.
		if ( $context['validation_result'] !== Command::SUCCESS ) {
			return $context['validation_result'];
		}

		// Early return if invalid extension.
		if ( $context['sut_type'] === Command::INVALID ) {
			return Command::INVALID;
		}

		// Handle group registration (same for local and remote).
		$is_local     = $input->getOption( 'local' );
		$group_result = $this->handle_group_registration(
			$input,
			$output,
			$context['woo_id'],
			$is_local
		);
		if ( $group_result !== null ) {
			return $group_result;
		}

		// Route to appropriate execution path.
		return $is_local
			? $this->execute_local_test( $input, $output, $context )
			: $this->execute_remote_test( $input, $output, $context );
	}

	/**
	 * Initialize common test context for both local and remote execution.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return array<string,mixed> Test context including options, validation result, and extension info.
	 */
	protected function initialize_test_context( InputInterface $input, OutputInterface $output ): array {
		try {
			// Parse options once.
			$options                    = $this->parse_options( $input );
			$env_up_options             = $options['env_up'];
			$env_up_options['--tunnel'] = TunnelRunner::get_tunnel_value( $input );

			// Validate input once.
			$wait              = $input->getOption( 'up_only' );
			$validation_result = $this->validate_input( $input, $output, $wait );

			// Resolve extension once.
			$woo_extension_raw                = $input->getArgument( 'woo_extension' );
			[ $woo_id, $woo_slug, $sut_type ] = $this->resolve_woo_extension( $woo_extension_raw, $output );

			return compact(
				'options',
				'env_up_options',
				'validation_result',
				'woo_id',
				'woo_slug',
				'sut_type',
				'wait'
			);
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
			return [
				'validation_result' => Command::FAILURE,
				'options'           => [],
				'env_up_options'    => [],
				'woo_id'            => null,
				'woo_slug'          => null,
				'sut_type'          => null,
				'wait'              => false,
			];
		}
	}

	/**
	 * Handle group registration for both local and remote tests.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param int|null        $woo_id
	 * @param bool            $is_local
	 * @return int|null Command exit code or null if no group option.
	 */
	protected function handle_group_registration( InputInterface $input, OutputInterface $output, ?int $woo_id, bool $is_local ): ?int {
		if ( ! $input->getOption( 'group' ) ) {
			return null;
		}

		$group_options = [
			'woo_id' => $woo_id,
			'local'  => $is_local,
		];

		if ( ! empty( $input->getOption( 'extension_set' ) ) ) {
			$group_options['extension_set'] = $input->getOption( 'extension_set' );
		}

		try {
			$this->test_group->create_or_update(
				$group_options,
				'performance',
				$output,
				$input,
				getenv()
			);
			$output->writeln( '<info>Group item successfully added.</info>' );
			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );
			return Command::FAILURE;
		}
	}

	/**
	 * Execute performance tests locally.
	 *
	 * @param InputInterface      $input
	 * @param OutputInterface     $output
	 * @param array<string,mixed> $context Test context from initialize_test_context.
	 * @return int Command exit code.
	 */
	protected function execute_local_test( InputInterface $input, OutputInterface $output, array $context ): int {
		// Parse environment variables for local execution.
		$this->parse_env_vars( $input->getOption( 'env' ) );

		// Warn about unimplemented --async flag.
		if ( $input->getOption( 'async' ) && ! $input->getOption( 'json' ) ) {
			$output->writeln( '<comment>Warning: The --async flag is not implemented for local performance tests yet.</comment>' );
			$output->writeln( '<comment>Local performance tests always run synchronously.</comment>' );
			$output->writeln( '' );
		}

		// Setup local environment configuration.
		$env_up_options = $this->setup_local_environment( $input, $output, $context );

		// Create test configuration.
		$env_info = $this->create_test_configuration( $input, $context );

		// Handle self-test mode.
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info ) );
			return Command::SUCCESS;
		}

		// Handle up_only mode.
		if ( $context['wait'] ) {
			return Command::SUCCESS;
		}

		// Prepare notification parameters for the test manager.
		$notification_params = null;
		if ( ! empty( $context['woo_id'] ) ) {
			$notification_params = [
				'woo_id'         => $context['woo_id'],
				'woo_version'    => $input->getOption( 'woocommerce_version' ) ?? 'latest',
				'is_development' => $input->getOption( 'source' ) && file_exists( $input->getOption( 'source' ) ),
				'notify'         => $input->getOption( 'notify' ),
			];
		}

		// Execute the actual performance tests.
		return $this->execute_performance_tests( $input, $env_info, $env_up_options, $output, $notification_params );
	}

	/**
	 * Setup local environment configuration.
	 *
	 * @param InputInterface      $input
	 * @param OutputInterface     $output
	 * @param array<string,mixed> $context Test context.
	 * @return array<string,mixed> Environment up options.
	 */
	protected function setup_local_environment( InputInterface $input, OutputInterface $output, array $context ): array {
		$env_up_options = $context['env_up_options'];

		// Setup volumes.
		$env_up_options['--volume'] = [];
		$env_up_options['--json']   = true;

		// Configure verbosity.
		if ( $output->isVerbose() ) {
			$env_up_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$env_up_options['--very-verbose'] = true;
		}

		// Configure object cache.
		if ( $input->getOption( 'object_cache' ) ) {
			$env_up_options['--object_cache'] = true;
		}

		// Handle test packages.
		$test_package_ids = $this->get_test_package_ids( $input, $output );

		// Download test packages for local execution.
		$test_packages = [];
		foreach ( $test_package_ids as $package_id ) {
			try {
				$test_package_downloader = App::make( TestPackageDownloader::class );
				$cache_dir               = Config::get_qit_dir() . 'cache';

				$test_package_downloader->download_single( $package_id, $cache_dir );
				$metadata = $test_package_downloader->get_package_metadata( $package_id );

				if ( ! empty( $metadata['downloaded_path'] ) ) {
					$test_packages[] = $metadata['downloaded_path'];
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>Failed to download test package "%s": %s</error>', $package_id, $e->getMessage() ) );
			}
		}

		if ( ! empty( $test_packages ) ) {
			$env_up_options['--test-package'] = $test_packages;
		}

		// Setup termination handling.
		$this->handle_termination();

		// Set SUT variables.
		if ( ! empty( $context['woo_slug'] ) ) {
			App::setVar( 'QIT_SUT', $context['woo_id'] );
			App::setVar( 'QIT_SUT_SLUG', $context['woo_slug'] );
		}

		// Add SUT to environment options.
		$env_up_options = $this->add_sut_to_env_up_options(
			$input,
			$env_up_options,
			$context['woo_slug'],
			$context['sut_type']
		);

		// Configure upload settings.
		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		App::setVar( 'QIT_ENV_UP_OPTIONS', $env_up_options );

		// Configure environment display.
		if ( $context['wait'] ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		// Set performance environment flags.
		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_ENVIRONMENT_TYPE=performance' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		$env_up_options['--environment_type'] = 'performance';

		return $env_up_options;
	}

	/**
	 * Create test configuration for performance tests.
	 *
	 * @param InputInterface      $input
	 * @param array<string,mixed> $context Test context.
	 * @return PerformanceEnvInfo Test configuration.
	 */
	protected function create_test_configuration( InputInterface $input, array $context ): PerformanceEnvInfo {
		$no_baseline = $input->getOption( 'no_baseline' );

		$env_info      = new PerformanceEnvInfo();
		$env_info->sut = [
			'slug' => $context['woo_slug'],
			'id'   => $context['woo_id'],
			'type' => $context['sut_type'],
		];

		$env_info->run_baseline = ! $no_baseline;
		$env_info->php_version  = $input->getOption( 'php_version' );

		return $env_info;
	}

	/**
	 * Execute the actual performance tests using the test manager.
	 *
	 * @param InputInterface           $input
	 * @param PerformanceEnvInfo       $env_info Test configuration.
	 * @param array<string,mixed>      $env_up_options Environment options.
	 * @param OutputInterface          $output
	 * @param array<string,mixed>|null $notification_params Notification parameters or null to skip notification.
	 * @return int Command exit code.
	 */
	protected function execute_performance_tests( InputInterface $input, PerformanceEnvInfo $env_info, array $env_up_options, OutputInterface $output, ?array $notification_params = null ): int {
		// Run tests with complete environment lifecycle management.
		$this->performance_test_manager->set_output( $output );
		$this->performance_test_manager->set_env_up_options( $env_up_options );

		if ( $notification_params !== null ) {
			$this->performance_test_manager->set_notification_params( $notification_params );
			$this->performance_test_manager->set_test_run_notifier( $this->test_run_notifier );
		}

		$exit_status_code = $this->performance_test_manager->run_tests( $env_info );

		if ( $exit_status_code === Command::SUCCESS ) {
			$output->writeln( '<info>Performance tests passed.</info>' );
			return Command::SUCCESS;
		} else {
			$output->writeln( '<error>Performance tests failed.</error>' );
			return Command::FAILURE;
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

		App::setVar( 'QIT_PERFORMANCE_ENV_VARS', $parsed_vars );
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

	private function validate_input( InputInterface $input, OutputInterface $output, bool $wait ): int {
		$woo       = $input->getOption( 'woocommerce_version' );
		$plugins   = $input->getOption( 'plugin' );
		$run_local = $input->getOption( 'local' );

		if ( ! empty( $woo ) && ! empty( $plugins ) && in_array( 'woocommerce', $plugins, true ) ) {
			$output->writeln( '<error>Cannot use both "--woocommerce_version" and "--plugin woocommerce" together.</error>' );

			return Command::INVALID;
		}

		// Remote tests don't support --up_only mode.
		if ( ! $run_local && $input->getOption( 'up_only' ) ) {
			$output->writeln( '<error>--up_only is only supported for local tests (--local).</error>' );

			return Command::INVALID;
		}

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		if ( empty( $woo_extension_raw ) ) {
			if ( ! empty( $input->getOption( 'source' ) ) ) {
				$output->writeln( '<error>The extension parameter is required when source is set.</error>' );

				return Command::INVALID;
			}
			if ( ! $wait && $run_local ) {
				$output->writeln( '<error>The extension parameter is required unless in --up_only mode.</error>' );

				return Command::INVALID;
			}
			if ( ! $run_local ) {
				$output->writeln( '<error>The extension parameter is required for remote tests.</error>' );

				return Command::INVALID;
			}
		}

		return Command::SUCCESS;
	}

	/**
	 * Add SUT to env:up options.
	 *
	 * @param InputInterface $input
	 * @param array<mixed>   $env_up_options
	 * @param string|null    $woo_extension_slug
	 * @param string|null    $sut_type 'plugin', 'theme', or null.
	 *
	 * @return array<mixed> Updated env_up_options.
	 */
	private function add_sut_to_env_up_options( InputInterface $input, array $env_up_options, ?string $woo_extension_slug, ?string $sut_type ): array {
		if ( ! $woo_extension_slug ) {
			return $env_up_options;
		}

		// When WooCommerce itself is the SUT and --woocommerce_version is set,
		// skip adding it here. resolve_woo() in UpEnvironmentCommand will add
		// WooCommerce with the correct version from --woocommerce_version.
		$cli_source = $input->getOption( 'source' );
		if ( ! $cli_source && $woo_extension_slug === 'woocommerce' && $input->getOption( 'woocommerce_version' ) ) {
			return $env_up_options;
		}

		if ( ! $sut_type ) {
			$sut_type = 'plugin';
		}

		$key = ( $sut_type === 'theme' ) ? '--theme' : '--plugin';

		$extension_identifier = $cli_source ?: $woo_extension_slug;

		if ( ! isset( $env_up_options[ $key ] ) ) {
			$env_up_options[ $key ] = [];
		}

		// Avoid duplicates.
		if ( ! in_array( $extension_identifier, $env_up_options[ $key ], true ) ) {
			$env_up_options[ $key ][] = $extension_identifier;
		}

		return $env_up_options;
	}

	/**
	 * Handle termination signals and register shutdown functions to ensure environment cleanup.
	 */
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

	/**
	 * Shutdown the test environment if one is running.
	 */
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
		} catch ( \Exception $e ) { // phpcs:ignore
			// no-op.
		}
	}

	/**
	 * Execute performance tests remotely on QIT infrastructure.
	 *
	 * @param InputInterface           $input
	 * @param OutputInterface          $output
	 * @param array<string,mixed>|null $context Optional pre-computed test context.
	 *
	 * @return int Command exit code
	 */
	protected function execute_remote_test( InputInterface $input, OutputInterface $output, ?array $context = null ): int {
		// Use pre-computed context if available, otherwise initialize.
		if ( ! $context ) {
			$context = $this->initialize_test_context( $input, $output );
			if ( $context['validation_result'] !== Command::SUCCESS ) {
				return $context['validation_result'];
			}
			if ( $context['sut_type'] === Command::INVALID ) {
				return Command::INVALID;
			}
		}

		// Parse options that should be sent to the API.
		$parsed_options = parent::parse_options( $input, true );

		// Map user-friendly aliases to API parameter names
		$option_aliases = [
			'wp'  => 'wordpress_version',
			'woo' => 'woocommerce_version',
			'php' => 'php_version',
		];

		foreach ( $option_aliases as $alias => $real_name ) {
			if ( $input->hasOption( $alias ) ) {
				$alias_value = $input->getOption( $alias );
				if ( $alias_value !== null && $alias_value !== '' ) {
					$parsed_options[ $real_name ] = $alias_value;
				}
			}
		}

		// Build options for remote API request.
		$options = array_merge( $parsed_options, [
			'woo_id' => $context['woo_id'],
		] );

		// Handle test packages option for remote execution.
		$test_packages = $this->get_test_package_ids( $input, $output );
		if ( ! empty( $test_packages ) ) {
			$options['test_packages'] = $test_packages;
		}

		// Handle ZIP upload if testing local file (following CreateRunCommands pattern).
		$source = $input->getOption( 'source' );
		if ( ! empty( $source ) && file_exists( $source ) ) {
			try {
				$options['upload_id'] = $this->upload->upload_build( 'build', $options['woo_id'], $source, $output );
				$options['event']     = 'cli_development_extension_test';
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>Failed to upload file: %s</error>', $e->getMessage() ) );
				return Command::FAILURE;
			}
		} else {
			$options['event'] = 'cli_published_extension_test';
		}

		if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
			$output->write( json_encode( $options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			return Command::SUCCESS;
		}

		// Enqueue the remote test following managed test pattern.
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/enqueue-performance' ) )
				->with_method( 'POST' )
				->with_post_body( $options )
				->request();

		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );
			return Command::FAILURE;
		}

		// Process response following managed test pattern.
		$response = json_decode( $json, true );

		if ( ! is_array( $response ) ) {
			$output->writeln( '<error>Invalid response from server.</error>' );
			return Command::FAILURE;
		}

		if ( ! isset( $response['test_run_id'] ) || ! isset( $response['test_results_manager_url'] ) ) {
			$output->writeln( '<error>Unexpected response. Missing "test_run_id" or "test_results_manager_url".</error>' );
			return Command::FAILURE;
		}

		$test_run_id = $response['test_run_id'];
		$output->writeln( sprintf( '<info>Test enqueued with ID: %s</info>', $test_run_id ) );
		$output->writeln( sprintf( '<info>Test URL: %s</info>', $response['test_results_manager_url'] ) );

		// Wait for completion if requested.
		$wait = $input->getOption( 'wait' );
		if ( $wait ) {
			return $this->wait_for_remote_test_completion( $test_run_id, $input, $output );
		}

		return Command::SUCCESS;
	}

	/**
	 * Wait for remote test completion and show results using managed test pattern.
	 *
	 * @param string          $test_run_id
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int Command exit code
	 */
	protected function wait_for_remote_test_completion( string $test_run_id, InputInterface $input, OutputInterface $output ): int {
		// Configure timeout following managed test pattern.
		$timeout = $input->getOption( 'timeout' ) ?? null;

		if ( is_null( $timeout ) ) {
			$timeout = 1800; // 30 minutes for performance tests (less than woo-e2e's 2 hours).
		}

		// Minimum timeout is 10 seconds, maximum is 2 hours.
		$timeout = max( 10, $timeout );
		$timeout = min( 3600 * 2, $timeout );

		// Get polling interval from environment or default.
		$poll_interval = (int) ( getenv( 'QIT_POLL_INTERVAL' ) ?: 10 );
		$poll_interval = max( 1, $poll_interval );

		// Register signal handlers for graceful interruption (following CreateRunCommands pattern).
		if ( function_exists( 'pcntl_signal' ) ) {
			$handler = static function () use ( $output ) {
				$output->writeln( '<comment>Received termination signal. Exiting gracefully...</comment>' );
				exit( 130 );
			};
			pcntl_signal( SIGINT, $handler );
			pcntl_signal( SIGTERM, $handler );
		}

		$start_time  = time();
		$get_command = App::make( GetCommand::class );

		do {
			sleep( $poll_interval );

			if ( function_exists( 'pcntl_signal_dispatch' ) ) {
				pcntl_signal_dispatch();
			}

			// Use GetCommand for status checking (following CreateRunCommands pattern).
			try {
				$finished = $get_command->run(
					new ArrayInput( [
						'test_run_id'      => $test_run_id,
						'--check_finished' => true,
					] ),
					$output
				);

				if ( $finished === 0 ) {
					// Test finished, get final results.
					return $get_command->run(
						new ArrayInput( [ 'test_run_id' => $test_run_id ] ),
						$output
					);
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<comment>Error checking test status: %s</comment>', $e->getMessage() ) );
			}

			// Check timeout.
			$elapsed_time = time() - $start_time;
			if ( $elapsed_time >= $timeout ) {
				$output->writeln( sprintf( '<error>Test did not complete within %d seconds.</error>', $timeout ) );
				return Command::FAILURE;
			}
		} while ( true );
	}

	/**
	 * Get test package IDs from input or default.
	 * Returns package IDs only - does NOT download anything.
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return array<string> Array of test package IDs (e.g., ["woocommerce/performance-tests:latest"]).
	 */
	private function get_test_package_ids( InputInterface $input, OutputInterface $output ): array {
		$test_packages = $input->getOption( 'test-package' ) ?: [];

		// Use default test package if none provided.
		if ( empty( $test_packages ) ) {
			$default_packages = $this->cache->get_manager_sync_data( 'default_test_packages' );
			$default_package  = $default_packages['performance'] ?? null;
			if ( $default_package ) {
				$test_packages = [ $default_package ];
				$output->writeln( "<comment>Using default test package: {$default_package}</comment>" );
			}
		}

		return $test_packages;
	}
}
