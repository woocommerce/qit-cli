<?php

namespace QIT_CLI\LocalTests\Performance\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Commands\GetCommand;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\PerformanceTestConfig;
use QIT_CLI\LocalTests\Performance\PerformanceTestManager;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\PluginDependencies;
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

	/** @var PluginDependencies */
	protected $dependencies;

	/** @var TestGroup */
	protected $test_group;

	protected static $defaultName = 'run:performance'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		Cache $cache,
		PerformanceTestManager $performance_test_manager,
		EnvironmentRunner $environment_runner,
		LocalTestRunNotifier $test_run_notifier,
		WooExtensionsList $woo_extensions_list,
		PluginDependencies $dependencies,
		TestGroup $test_group
	) {
		$this->cache                    = $cache;
		$this->performance_test_manager = $performance_test_manager;
		$this->environment_runner       = $environment_runner;
		$this->test_run_notifier        = $test_run_notifier;
		$this->woo_extensions_list      = $woo_extensions_list;
		$this->dependencies             = $dependencies;
		$this->test_group               = $test_group;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['performance']['properties'] ) ) {
			throw new \RuntimeException( 'Performance schema not set or incomplete.' );
		}

		$this
			->setDescription( 'Run Performance tests.' )
			->setHelp( 'Run k6 performance tests against a given extension.' );

		// Apply schema-driven options like other managed tests.
		DynamicCommandCreator::add_schema_to_command(
			$this,
			$schemas['performance'],
			[], // No exceptions - include all schema properties.
			[]  // No whitelist - include all properties.
		);

		// Add performance-specific arguments and options not covered by schema.
		$this
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'The slug or WooCommerce ID of the main extension under test.' )
			->addArgument( 'test', InputArgument::OPTIONAL, '(Optional) The tests for the main extension under test. Accepts test tags, or a test directory. If not set, will use the "default" test tag of this extension.' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL. If not provided, the source will be the slug.' )
			->addOption( 'sut_action', null, InputOption::VALUE_OPTIONAL, 'What action to take on the SUT. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['test'] );

		// Add performance-specific options that might not be in the current schema.
		// These are needed for local execution and will also work for remote execution.
		$this
			->addOption( 'k6_test_file', null, InputOption::VALUE_OPTIONAL, 'The k6 test file to run.', '' )
			->addOption( 'no_baseline', null, InputOption::VALUE_NONE, 'Skip running baseline performance tests before the main tests.' )
			->addOption( 'iterations', null, InputOption::VALUE_OPTIONAL, 'Number of test iterations to run for metric stability (default: 3)', 3 )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'If set, failures will be notified to the author of the SUT.' );

		// Add version aliases that local code expects (reuse from UpEnvironmentCommand).
		$this
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'wp' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'woo' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_version' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'theme' );

		// Local execution specific options (not part of remote schema).
		$this
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'require' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'config' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_plugins' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_themes' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'tunnel' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'json' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env_file' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Do not upload the report to QIT Manager.' )
			->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'How to handle dependencies for recognized WooCommerce plugins. Possible values: ' . implode( ', ', PluginDependencies::DEPENDENCY_MODES['env_test'] ), PluginDependencies::DEPENDENCY_MODES['env_test']['bootstrap'] )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it running until shut down.' );

		// Hybrid execution options.
		$this
			->addOption( 'local', null, InputOption::VALUE_NONE, 'Run tests locally instead of on QIT infrastructure' )
			->addOption( 'wait', null, InputOption::VALUE_NONE, 'Wait for remote test completion and display results' )
			->addOption( 'timeout', null, InputOption::VALUE_OPTIONAL, 'Timeout in seconds for waiting for test completion (min: 10, max: 7200)', null );

		// Group options.
		$this
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the test run into a group.', false )
			->addOption( 'no_group', 'ng', InputOption::VALUE_NEGATABLE, 'If set, the CLI will not attempt to match the local test run with a group.', false );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
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

		// Setup local environment configuration.
		$env_up_options = $this->setup_local_environment( $input, $output, $context );

		// Create test configuration.
		$env_info = $this->create_test_configuration( $input, $context );

		// Notify test started.
		if ( ! empty( $context['woo_id'] ) ) {
			$this->test_run_notifier->notify_test_started(
				$context['woo_id'],
				$input->getOption( 'woo' ) ?? 'latest',
				$env_info,
				$input->getOption( 'source' ) && file_exists( $input->getOption( 'source' ) ),
				$input->getOption( 'notify' )
			);
		}

		// Handle self-test mode.
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info ) );
			return Command::SUCCESS;
		}

		// Handle up_only mode.
		if ( $context['wait'] ) {
			return Command::SUCCESS;
		}

		// Execute the actual performance tests.
		return $this->execute_performance_tests( $input, $env_info, $env_up_options, $output );
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

		// Configure app settings.
		if ( ! empty( $input->getOption( 'config' ) ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
		}

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
		$test_tag     = $input->getArgument( 'test' ) ?? '';
		$k6_test_file = $input->getOption( 'k6_test_file' ) ?? '';
		$no_baseline  = $input->getOption( 'no_baseline' );

		$env_info               = new PerformanceEnvInfo();
		$env_info->sut_slug     = $context['woo_slug'];
		$env_info->sut_id       = $context['woo_id'];
		$env_info->sut_type     = $context['sut_type'];
		$env_info->test_tag     = $test_tag;
		$env_info->k6_test_file = $k6_test_file;
		$env_info->run_baseline = ! $no_baseline;

		return $env_info;
	}

	/**
	 * Execute the actual performance tests using the test manager.
	 *
	 * @param InputInterface      $input
	 * @param PerformanceEnvInfo  $env_info Test configuration.
	 * @param array<string,mixed> $env_up_options Environment options.
	 * @param OutputInterface     $output
	 * @return int Command exit code.
	 */
	protected function execute_performance_tests( InputInterface $input, PerformanceEnvInfo $env_info, array $env_up_options, OutputInterface $output ): int {
		// Get iterations from input.
		$iterations = (int) ( $input->getOption( 'iterations' ) ?? 3 );

		// Validate iterations parameter.
		if ( $iterations < 1 || $iterations > 10 ) {
			$output->writeln( '<error>Iterations must be between 1 and 10.</error>' );
			return Command::FAILURE;
		}

		// Run tests with complete environment lifecycle management.
		$this->performance_test_manager->set_output( $output );
		$this->performance_test_manager->set_test_iterations( $iterations );
		$this->performance_test_manager->set_env_up_options( $env_up_options );
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
		$woo       = $input->getOption( 'woo' );
		$plugins   = $input->getOption( 'plugin' );
		$run_local = $input->getOption( 'local' );

		if ( ! empty( $woo ) && ! empty( $plugins ) && in_array( 'woocommerce', $plugins, true ) ) {
			$output->writeln( '<error>Cannot use both "--woo" and "--plugin woocommerce" together.</error>' );

			return Command::INVALID;
		}

		// Remote tests don't support --up_only mode.
		if ( ! $run_local && $input->getOption( 'up_only' ) ) {
			$output->writeln( '<error>--up_only is only supported for local tests (--local).</error>' );

			return Command::INVALID;
		}

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		if ( empty( $woo_extension_raw ) ) {
			if ( ! empty( $input->getOption( 'source' ) ) || ! empty( $input->getOption( 'sut_action' ) ) ) {
				$output->writeln( '<error>The extension parameter is required when source or sut_action is set.</error>' );

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

		// If no explicit SUT type, default to 'plugin'.
		if ( ! $sut_type ) {
			$sut_type = 'plugin';
		}

		$key = ( $sut_type === 'theme' ) ? '--theme' : '--plugin';

		// Gather CLI overrides.
		$cli_action    = $input->getOption( 'sut_action' );
		$cli_test      = $input->getArgument( 'test' );
		$cli_test_tags = $cli_test ? explode( ',', $cli_test ) : [];
		$cli_source    = $input->getOption( 'source' );

		// STEP 1: Find & parse any existing entry for this slug from qit.yml or earlier merges.
		$old_index     = null;
		$existing_data = [];

		if ( ! empty( $env_up_options[ $key ] ) ) {
			foreach ( $env_up_options[ $key ] as $i => $entry ) {
				$decoded = json_decode( $entry, true );
				// If it's valid JSON with the correct slug.
				if ( is_array( $decoded ) && ! empty( $decoded['slug'] ) && $decoded['slug'] === $woo_extension_slug ) {
					$old_index     = $i;
					$existing_data = $decoded;
					break;
				}
			}
		}

		// STEP 2: Remove that old entry if found, so we don't have duplicates.
		if ( $old_index !== null ) {
			array_splice( $env_up_options[ $key ], $old_index, 1 );
		}

		// STEP 3: Partially merge the user's CLI overrides onto the existing data.
		// If there was no old entry, $existing_data is empty → we start fresh.
		// Ensure we at least have 'slug'.
		$extension_data         = $existing_data;
		$extension_data['slug'] = $woo_extension_slug;

		// If CLI explicitly sets a source, override the old one.
		if ( $cli_source ) {
			$extension_data['source'] = $cli_source;
		}

		// If CLI explicitly sets an action, override the old one.
		if ( $cli_action ) {
			$extension_data['action'] = $cli_action;
		}

		// Currently, CLI test tags override any existing test_tags.
		// We could change it to array_merge below to merge instead.
		if ( ! empty( $cli_test_tags ) ) {
			$extension_data['test_tags'] = $cli_test_tags;
		} else {
			// If we never set test_tags, ensure it at least exists.
			if ( empty( $extension_data['test_tags'] ) ) {
				$extension_data['test_tags'] = PerformanceTestConfig::DEFAULT_TEST_TAGS;
			}
		}

		$extension_data['priority'] = Extension::PRIORITY_LOW;

		// STEP 4: Re‐insert this final single definition for that slug.
		$env_up_options[ $key ][] = json_encode( $extension_data );

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

		// Add woo_id to options (following managed test pattern).
		$options           = $context['options'];
		$options['woo_id'] = $context['woo_id'];

		// Add test tag to options for remote execution.
		$test_argument = $input->getArgument( 'test' );
		if ( ! empty( $test_argument ) ) {
			$options['test'] = $test_argument;
		}

		// Handle ZIP upload if testing local file (following CreateRunCommands pattern).
		$source = $input->getOption( 'source' );
		if ( ! empty( $source ) && file_exists( $source ) ) {
			try {
				$upload_instance      = App::make( Upload::class );
				$options['upload_id'] = $upload_instance->upload_build( 'build', $options['woo_id'], $source, $output );
				$options['event']     = 'cli_development_extension_test';
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>Failed to upload file: %s</error>', $e->getMessage() ) );
				return Command::FAILURE;
			}
		} else {
			$options['event'] = 'cli_published_extension_test';
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
}
