<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\LocalTests\E2E\CustomE2ERunner;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;

class RunE2ECommand extends QITCommand implements LocalTestCommand {
	protected E2EEnvironment $e2e_environment;
	protected CustomE2ERunner $spec_custom_test_orchestrator;
	protected WooExtensionsList $woo_extensions_list;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * 0 is success.
	 * 1 is either Playwright failed an assertion from a user-perspective, or a PHP fatal error has been logged.
	 * 2 is reserved, so we skip it.
	 * 3 is a warning, such as a PHP error that is not fatal.
	 */
	const WARNING = 3;

	public function __construct(
		E2EEnvironment $e2e_environment,
		CustomE2ERunner $spec_custom_test_orchestrator,
		WooExtensionsList $woo_extensions_list
	) {
		$this->e2e_environment               = $e2e_environment;
		$this->spec_custom_test_orchestrator = $spec_custom_test_orchestrator;
		$this->woo_extensions_list           = $woo_extensions_list;
		parent::__construct();
	}

	/**
	 * LocalTestCommand interface implementation.
	 */
	public function get_environment_name(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function should_prepare_environment(): bool {
		// Don't prepare if just checking config or in up_only mode
		return ! $this->input->getOption( 'up_only' );
	}

	public function get_test_type(): string {
		return 'e2e';
	}

	public function get_test_profile(): string {
		return $this->input->getOption( 'profile' ) ?? 'default';
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Run E2E tests' )
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'Extension slug or ID' )
			->addArgument( 'test', InputArgument::OPTIONAL, 'Test tags or directory', 'default' )
			->addArgument( 'runner_args', InputArgument::IS_ARRAY, 'Arguments after --' )

			// SUT options
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'Source of the extension' )

			// Environment overrides
			->addOption( 'wp', null, InputOption::VALUE_OPTIONAL, 'WordPress version' )
			->addOption( 'woo', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version' )
			->addOption( 'php', null, InputOption::VALUE_OPTIONAL, 'PHP version' )
			->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
			->addOption(
				'test-package',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Test packages to include (multiple values allowed)',
				[]
			)
			->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
			->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volume mappings', [] )
			->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
			->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Object Cache' )
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins' )
			->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes' )
			->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling', 'no_tunnel' )
			->addOption( 'env', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables', [] )
			->addOption( 'env_file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment files', [] )

			// Test options
			->addOption( 'pw_test_tag', null, InputOption::VALUE_OPTIONAL, 'Playwright test tag', '' )
			->addOption( 'shard', null, InputOption::VALUE_OPTIONAL, 'Playwright sharding' )
			->addOption( 'update_snapshots', null, InputOption::VALUE_NONE, 'Update snapshots' )
			->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional Playwright options' )

			// Execution options
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'Just start environment' )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Run in UI mode' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Skip report upload' )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'Notify on failures' )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, 'Register into a group', false )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'JSON output', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E Tests on Windows, please use WSL.</comment>' );

			return self::FAILURE;
		}

		/** @var LocalTestResult $result */
		$result = $this->getPreCommandResult();

		// PreCommand has already:
		// - Resolved SUT from CLI argument (if provided)
		// - Resolved test packages based on profile/test argument
		// - Applied all CLI overrides
		// - Downloaded everything

		$env_info      = $result->env_info;
		$test_packages = $result->test_packages;

		// Validate shard format
		$shard = $input->getOption( 'shard' );
		if ( $shard ) {
			if ( ! $this->validateShard( $shard, $output ) ) {
				return self::INVALID;
			}
		}

		// Set up globals and environment
		$this->setupGlobals( $env_info, $input );
		$this->handle_termination();

		// For testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
			$output->write( json_encode( $env_info ) );

			return self::SUCCESS;
		}

		// Initialize environment
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up( 'up' );

		// Handle up_only mode
		if ( $input->getOption( 'up_only' ) ) {
			return $this->handleUpOnly( $env_info, $output );
		}

		// Run tests with test packages
		$io          = new SymfonyStyle( $input, $output );
		$exit_status = $this->runTestPackages( $env_info, $test_packages, $io );

		// Output results
		if ( $exit_status === Command::SUCCESS ) {
			$io->success( "Tests passed. Run 'qit e2e-report' to view the report." );
		} else {
			$io->error( "Tests failed. Run 'qit e2e-report' to view the report." );
		}

		return $exit_status;
	}

	protected function validateShard( string $shard, OutputInterface $output ): bool {
		if ( ! preg_match( '/^\d+\/\d+$/', $shard ) ) {
			$output->writeln( '<error>Invalid shard format. Should be current/total, e.g., 1/5.</error>' );

			return false;
		}
		[ $current, $total ] = explode( '/', $shard );
		if ( $current <= 0 || $current > $total ) {
			$output->writeln( '<error>Invalid shard format. Current must be > 0 and <= total.</error>' );

			return false;
		}

		return true;
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

	public static function shutdown_test_run(): void {
		static $did_shutdown = false;
		if ( $did_shutdown ) {
			return;
		}
		$did_shutdown = true;

		if ( App::getVar( 'QIT_JSON_MODE' ) !== true ) {
			echo "\nShutting down environment...\n";
		}

		if ( ! empty( $GLOBALS['env_to_shutdown'] ) ) {
			try {
				// Get the environment info from the environment monitor
				$env_monitor = App::make( \QIT_CLI\Environment\EnvironmentMonitor::class );
				try {
					$env_info = $env_monitor->get_env_info_by_id( $GLOBALS['env_to_shutdown'] );
					Environment::down( $env_info );
				} catch ( \Exception $e ) {
					\QIT_CLI\debug_log( 'Failed to find environment info for shutdown: ' . $GLOBALS['env_to_shutdown'] . ' - ' . $e->getMessage(), 'comment' );
				}
			} catch ( \Exception $e ) {
				// Silent fail - environment cleanup errors are non-critical
				\QIT_CLI\debug_log( 'Failed to shutdown environment: ' . $e->getMessage(), 'comment' );
			}
		}
	}

	/**
	 * Set up global variables needed for the test run.
	 *
	 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info The environment information.
	 * @param InputInterface                                   $input The input interface.
	 */
	protected function setupGlobals( \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info, InputInterface $input ): void {
		// Set up the global variable for environment shutdown
		$GLOBALS['env_to_shutdown'] = $env_info->env_id;
	}

	/**
	 * Run test packages using the CustomE2ERunner.
	 *
	 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info The environment information.
	 * @param array                                            $test_packages The test packages to run.
	 * @param SymfonyStyle                                     $io The IO interface.
	 * @return int The exit status.
	 */
		/**
		 * Run test packages using the CustomE2ERunner.
		 *
		 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info The environment information.
		 * @param array<string,mixed>                              $test_packages The test packages to run.
		 * @param SymfonyStyle                                     $io The IO interface.
		 * @return int The exit status.
		 */
	protected function runTestPackages( \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info, array $test_packages, SymfonyStyle $io ): int {
		// Run tests using the CustomE2ERunner
		return $this->spec_custom_test_orchestrator->run_custom_e2e_tests( $env_info, $io, false );
	}
}
