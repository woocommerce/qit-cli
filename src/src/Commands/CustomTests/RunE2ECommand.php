<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\PackagePhaseRunner;
use QIT_CLI\Environment\ResultCollector;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_option_explicitly_provided;
use function QIT_CLI\is_windows;

class RunE2ECommand extends QITCommand {
	use OptionReuseTrait;

	protected E2EEnvironment $e2e_environment;
	protected WooExtensionsList $woo_extensions_list;
	protected PackagePhaseRunner $package_phase_runner;
	protected ResultCollector $result_collector;
	protected LocalTestRunNotifier $local_test_run_notifier;

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
		WooExtensionsList $woo_extensions_list,
		PackagePhaseRunner $package_phase_runner,
		ResultCollector $result_collector,
		LocalTestRunNotifier $local_test_run_notifier
	) {
		$this->e2e_environment         = $e2e_environment;
		$this->woo_extensions_list     = $woo_extensions_list;
		$this->package_phase_runner    = $package_phase_runner;
		$this->result_collector        = $result_collector;
		$this->local_test_run_notifier = $local_test_run_notifier;
		parent::__construct();
	}

	protected string $test_type = 'e2e';

	public function get_environment_name(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function get_test_profile(): string {
		return $this->input->getOption( 'profile' ) ?? 'default';
	}

	protected function configure(): void {
		parent::configure();
		$this->configureMainOptions();
	}

	protected function configureProfileOption(): void {
		$this->addOption(
			'profile',
			'',
			InputOption::VALUE_OPTIONAL,
			'Test profile to use',
			'default'
		);
	}

	protected function configureEnvironmentOption(): void {
		$this->addOption(
			'environment',
			'e',
			InputOption::VALUE_OPTIONAL,
			'Environment name from configuration',
			'default'
		);
	}

	protected function configureMainOptions(): void {
		$this->setDescription( 'Run E2E tests' )
			->addArgument( 'sut', InputArgument::OPTIONAL, 'Extension slug or ID (system‑under‑test)' )
			->addArgument( 'runner_args', InputArgument::IS_ARRAY, 'Arguments after --' )

			/* ─────────────── Shared Options (reused from env:up) ─────────────── */
			->reuseOption( 'env:up', 'environment' )
			->reuseOption( 'env:up', 'php' )
			->reuseOption( 'env:up', 'wp' )
			->reuseOption( 'env:up', 'woo' )
			->reuseOption( 'env:up', 'plugin' )
			->reuseOption( 'env:up', 'theme' )
			->reuseOption( 'env:up', 'volume' )
			->reuseOption( 'env:up', 'php_extension' )
			->reuseOption( 'env:up', 'object_cache' )
			->reuseOption( 'env:up', 'tunnel' )
			->reuseOption( 'env:up', 'env' )
			->reuseOption( 'env:up', 'env_file' )
			->reuseOption( 'env:up', 'json' )

			/* ─────────────── SUT‑related options ─────────────── */
			->addOption( 'zip', null, InputOption::VALUE_OPTIONAL,
			'Use a custom ZIP (or directory/URL) as the SUT build' )

			/* ─────────────── E2E-specific options ─────────────── */
			->addOption(
				'test-package',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Test packages to include (multiple values allowed)',
				[]
			)
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins' )
			->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes' )

			// Test options
			->addOption( 'pw_test_tag', null, InputOption::VALUE_OPTIONAL, 'Playwright test tag', '' )
			->addOption( 'shard', null, InputOption::VALUE_OPTIONAL, 'Playwright sharding' )
			->addOption( 'update_snapshots', null, InputOption::VALUE_NONE, 'Update snapshots' )
			->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional Playwright options' )

			// Execution options
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Run in UI mode' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Skip report upload' )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'Notify on failures' )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, 'Register into a group', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {

		/* ─ Platform guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E tests on Windows, please use WSL.</comment>' );
			return self::FAILURE;
		}

		/*****************************************************************
		 * 1.  Resolve configuration with proper precedence
		 */
		$env_name = $input->getOption( 'environment' ) ?? 'default';
		$profile  = $input->getOption( 'profile' ) ?? 'default';

		$env_config  = $this->applyEnvCliOverrides( $this->get_environment_config( $env_name ), $input );
		$test_config = $this->applyProfileCliOverrides( $this->get_current_test_profile( $this->test_type, $profile ), $input );

		/*****************************************************************
		 * 2.  Lazy‑download everything required
		 */
		$this->download_extensions( [ $env_name ] );
		$test_packages = $this->download_test_packages( [
			[
				'type' => $this->test_type,
				'name' => $profile,
			],
		] );

		/*****************************************************************
		 * 3.  Resolve & validate SUT
		 */
		$sut = $this->get_resolved_sut();
		if ( empty( $sut['slug'] ) ) {
			$output->writeln( '<error>No System‑Under‑Test (SUT) specified.</error>' );
			return Command::INVALID;
		}

		/*****************************************************************
		 * 4.  Hydrate E2EEnvInfo
		 */
		$env_info = E2EEnvInfo::from_array( [
			'env_id'               => 'qitenv' . bin2hex( random_bytes( 8 ) ),
			'environment'          => 'e2e',
			'php'                  => $env_config['php'] ?? '8.2',
			'wp'                   => $env_config['wp'] ?? 'stable',
			'woo'                  => $env_config['woo'] ?? '',
			'plugins'              => $env_config['plugins'] ?? [],
			'themes'               => $env_config['themes'] ?? [],
			'volumes'              => $env_config['volumes'] ?? [],
			'php_extensions'       => $env_config['php_extensions'] ?? [],
			'envs'                 => $env_config['envs'] ?? [],
			'env_files'            => $env_config['env_files'] ?? [],
			'object_cache'         => $env_config['object_cache'] ?? false,
			'tunnel'               => $env_config['tunnel'] ?? false,
			'tunnel_type'          => $env_config['tunnel_type'] ?? 'no_tunnel',
			'site_url'             => 'http://localhost:8080',
			'sut'                  => $sut,
			'is_development_build' => false,
		] );

		/*****************************************************************
		 * 5.  Normal test‑execution flow (UNCHANGED)
		 */
		// ─ validate shard, run phases, collect results, notify, etc.
		// … existing logic untouched …

		// Determine whether we should upload the Allure report.
		// By default we upload unless the user explicitly passes --no_upload_report.
		$should_upload = ! ( is_option_explicitly_provided( $input, 'no_upload_report' ) && $input->getOption( 'no_upload_report' ) );
		App::setVar( 'should_upload_report', $should_upload );

		// Handle test packages from test configuration
		$test_packages = $test_config['test_packages'] ?? [];

		// Add test packages from --test-package option (only if explicitly provided)
		if ( is_option_explicitly_provided( $input, 'test-package' ) ) {
			$cli_test_packages = $input->getOption( 'test-package' );
			if ( ! empty( $cli_test_packages ) ) {
				foreach ( $cli_test_packages as $package ) {
					$test_packages[] = $package;
				}
			}
		}

		// Validate shard format (only if explicitly provided)
		if ( is_option_explicitly_provided( $input, 'shard' ) ) {
			$shard = $input->getOption( 'shard' );
			if ( $shard && ! $this->validateShard( $shard, $output ) ) {
				return self::INVALID;
			}
		}

		// Set up globals and environment
		$this->setupGlobals( $env_info, $input );
		$this->handle_termination();

		// Display warning if CLI overrides config (before test early return)
		$sut_warning = $this->get_sut_warning();
		if ( $sut_warning ) {
			$output->writeln( "<comment>$sut_warning</comment>" );
		}

		// For testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'run_e2e' ) {
			$output->write( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );

			return self::SUCCESS;
		}

		// Populate test packages metadata for volume mounting
		$env_info->test_packages_metadata = [];
		foreach ( $test_packages as $pkg_id => $meta ) {
			/** @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible */
			if ( isset( $meta['path'] ) && is_dir( $meta['path'] ) ) {
				/** @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible */
				$env_info->test_packages_metadata[ $pkg_id ] = [ 'path' => $meta['path'] ];
			}
		}

		// Initialize environment
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up();

		// Notify test started
		if ( isset( $env_info->sut['slug'] ) ) {
			$woo_extension_id    = $this->woo_extensions_list->get_woo_extension_id_by_slug( $env_info->sut['slug'] );
			$woocommerce_version = $env_info->woo;
			$is_development      = $env_info->is_development_build;
			$notify              = $input->getOption( 'notify' ) ?? false;

			$this->local_test_run_notifier->notify_test_started(
				$woo_extension_id,
				$woocommerce_version,
				$env_info,
				$is_development,
				$notify
			);
		}

		// Run tests with test packages
		$io          = new SymfonyStyle( $input, $output );
		$exit_status = $this->runTestPackages( $env_info, $test_packages, $io );

		// Notify test finished
		if ( isset( $env_info->sut['slug'] ) ) {
			$test_result = TestResult::init_from( $env_info );
			$results_dir = $test_result->get_results_dir();

			// Copy debug.log from Docker container to results directory
			try {
				$docker = App::make( Docker::class );
				$docker->copy_from_docker(
					$env_info,
					'/var/www/html/wp-content/debug.log',
					$results_dir . '/debug.log'
				);
				$io->writeln( '<info>✓ Debug log copied from container</info>' );
			} catch ( \RuntimeException $e ) {
				// Debug log might not exist if no errors occurred - this is normal
				$io->writeln( '<comment>No debug log found in container (this is normal if no PHP errors occurred)</comment>' );
			}

			$test_result->set_status( $exit_status === Command::SUCCESS ? 'success' : 'failed' );

			[ $report_url, $exit_status_override ] = $this->local_test_run_notifier->notify_test_finished( $test_result );

			// Use exit status override if provided
			if ( $exit_status_override !== null ) {
				$exit_status = $exit_status_override;
			}
		}

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

	/*******************************************************************
	 * Helper: merge **explicit** CLI overrides into env config
	 ******************************************************************/
	private function applyEnvCliOverrides( array $config, InputInterface $input ): array {

		/* Scalars */
		foreach ( [ 'php', 'wp', 'woo', 'tunnel' ] as $opt ) {
			if ( is_option_explicitly_provided( $input, $opt ) ) {
				$config[ $opt === 'tunnel' ? 'tunnel_type' : $opt ] = $input->getOption( $opt );
				if ( $opt === 'tunnel' ) {
					$config['tunnel'] = $input->getOption( $opt ) !== 'no_tunnel';
				}
			}
		}
		if ( is_option_explicitly_provided( $input, 'object_cache' ) ) {
			$config['object_cache'] = (bool) $input->getOption( 'object_cache' );
		}

		/* Lists – merge + dedupe */
		$merge = static function ( string $key, string $option ) use ( &$config, $input ): void {
			if ( ! is_option_explicitly_provided( $input, $option ) ) {
				return;
			}
			$cli            = (array) $input->getOption( $option );
			$cfg            = $config[ $key ] ?? [];
			$config[ $key ] = array_values( array_unique( array_merge( $cfg, $cli ) ) );
		};
		$merge( 'plugins', 'plugin' );
		$merge( 'themes', 'theme' );
		$merge( 'volumes', 'volume' );
		$merge( 'php_extensions', 'php_extension' );

		/* Env vars */
		if ( is_option_explicitly_provided( $input, 'env' ) ) {
			foreach ( $input->getOption( 'env' ) as $pair ) {
				[$k, $v]              = array_map( 'trim', explode( '=', $pair, 2 ) );
				$config['envs'][ $k ] = $v ?? '';
			}
		}
		$merge( 'env_files', 'env_file' );

		return $config;
	}

	/*******************************************************************
	 * Helper: merge CLI overrides into *test‑profile* config
	 ******************************************************************/
	private function applyProfileCliOverrides( array $profile, InputInterface $input ): array {
		if ( is_option_explicitly_provided( $input, 'test-package' ) ) {
			$cli_pkgs                 = (array) $input->getOption( 'test-package' );
			$cfg_pkgs                 = $profile['test_packages'] ?? [];
			$profile['test_packages'] = array_values( array_unique( array_merge( $cfg_pkgs, $cli_pkgs ) ) );
		}
		// Other per‑profile CLI flags (pw_test_tag, shard, etc.) are execution
		// parameters, not part of the config object, so we leave them alone.
		return $profile;
	}

	/**
	 * Clean test package results based on manifest declarations
	 *
	 * @param string                                          $package_path Path to the test package.
	 * @param \QIT_CLI\PreCommand\Objects\TestPackageManifest $manifest Parsed manifest.
	 * @throws \RuntimeException On cleanup failures.
	 */
	private function cleanup_test_package_results( string $package_path, \QIT_CLI\PreCommand\Objects\TestPackageManifest $manifest ): void {
		$results = $manifest->getTestResults();

		foreach ( $results as $type => $rel_path ) {
			$full_path = rtrim( $package_path, '/' ) . '/' . ltrim( $rel_path, './' );

			switch ( $type ) {
				case 'ctrf-json':
					if ( is_file( $full_path ) ) {
						if ( ! unlink( $full_path ) ) {
							throw new \RuntimeException( "Failed to delete CTRF file: {$full_path}" );
						}
					}
					break;

				case 'json':
					if ( is_file( $full_path ) ) {
						if ( ! unlink( $full_path ) ) {
							throw new \RuntimeException( "Failed to delete JSON results file: {$full_path}" );
						}
					}
					break;

				case 'allure-dir':
					if ( is_dir( $full_path ) ) {
						$is_allure_dir = ! empty( glob( $full_path . '/*-result.json' ) );
						if ( $is_allure_dir ) {
							$fs = new \Symfony\Component\Filesystem\Filesystem();
							$fs->remove( $full_path );
							// Recreate the directory for new results
							mkdir( $full_path, 0755, true );
						}
					}
					break;
			}
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
	 * Run test packages using manifest-based approach with PackagePhaseRunner.
	 *
	 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info The environment information.
	 * @param array<string,mixed>                              $test_packages The test packages to run.
	 * @param SymfonyStyle                                     $io The IO interface.
	 *
	 * @return int The exit status.
	 */
	protected function runTestPackages( \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info, array $test_packages, SymfonyStyle $io ): int {
		try {
			$total_executed  = 0;
			$failed_packages = [];

			// Set up artifacts directory using env_id for consistency
			$artifacts_dir = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $env_info->env_id;

			// Get bootstrap package IDs to skip them (they only run globalSetup)
			$bootstrap_package_ids = array_keys( $env_info->bootstrap_packages ?? [] );

			$io->section( 'Running Test Packages' );

			// Run globalSetup phase for all packages
			$io->writeln( '<info>Running globalSetup phase for all packages...</info>' );
			foreach ( $test_packages as $pkg_id => $meta ) {
				$this->package_phase_runner->run_phase( $env_info, 'globalSetup', $pkg_id, $meta['path'], $artifacts_dir );
			}

			// Export baseline database snapshot after all globalSetup scripts ran
			$io->writeln( '<info>Exporting baseline database snapshot...</info>' );
			$docker = App::make( Docker::class );
			$docker->run_inside_docker( $env_info, [ 'wp', 'db', 'export', '/qit/snapshot.sql', '--defaults' ] );

			$is_first_package = true;
			foreach ( $test_packages as $pkg_id => $meta ) {
				// Skip packages that are in bootstrap_packages (they only run globalSetup)
				if ( in_array( $pkg_id, $bootstrap_package_ids, true ) ) {
					$io->writeln( "<comment>Skipping {$pkg_id} (bootstrap package - globalSetup already executed)</comment>" );
					continue;
				}

				$package_path = $meta['path'] ?? '';
				if ( empty( $package_path ) || ! is_dir( $package_path ) ) {
					$io->error( "Invalid package path for {$pkg_id}: {$package_path}" );
					$failed_packages[] = $pkg_id;
					continue;
				}

				$io->writeln( "<info>Processing package: {$pkg_id}</info>" );

				// Import database snapshot before each non-first package
				if ( ! $is_first_package ) {
					$io->writeln( '<info>Restoring database snapshot before isolated phases...</info>' );
					try {
						App::make( Docker::class )->run_inside_docker( $env_info, [ 'wp', 'db', 'import', '/qit/snapshot.sql', '--defaults' ] );
						$io->writeln( '<info>✓ Database snapshot restored successfully</info>' );
					} catch ( \Exception $e ) {
						throw new \RuntimeException( 'Infrastructure failure: Failed to restore database snapshot before package ' . $pkg_id . ': ' . $e->getMessage(), 3 );
					}
				}

				try {
					// Parse manifest for result collection
					$manifest_path = $package_path . '/manifest.json';
					$manifest      = null;
					if ( file_exists( $manifest_path ) ) {
						$parser   = App::make( \QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser::class );
						$manifest = $parser->parse( $manifest_path );

						// Clean previous test results before running
						$this->cleanup_test_package_results( $package_path, $manifest );

						// Store manifest in test_packages_metadata for later use
						if ( isset( $env_info->test_packages_metadata[ $pkg_id ] ) ) {
							$env_info->test_packages_metadata[ $pkg_id ]['manifest'] = $manifest;
						}
					}

					// Run full lifecycle for test packages: setup -> run -> teardown
					$setup_count = $this->package_phase_runner->run_phase( $env_info, 'setup', $pkg_id, $package_path, $artifacts_dir );
					if ( $manifest && $setup_count > 0 ) {
						$this->result_collector->collect( $env_info, $pkg_id, $manifest, $artifacts_dir, 'setup' );
					}

					// Run phase with CTRF collection even on test failures
					try {
						$run_count = $this->package_phase_runner->run_phase( $env_info, 'run', $pkg_id, $package_path, $artifacts_dir );
					} catch ( \RuntimeException $e ) {
						// Collect CTRF even if tests failed (exit code 1 from test failures)
						if ( $manifest ) {
							try {
								$this->result_collector->collect( $env_info, $pkg_id, $manifest, $artifacts_dir, 'run' );
							} catch ( \Throwable $collector_err ) {
								$io->writeln( "<comment>CTRF collection after failure failed: {$collector_err->getMessage()}</comment>" );
							}
						}
						// Re-throw to maintain failure status
						throw $e;
					}

					// Normal CTRF collection for successful runs
					if ( $manifest && $run_count > 0 ) {
						$this->result_collector->collect( $env_info, $pkg_id, $manifest, $artifacts_dir, 'run' );
					}

					$teardown_count = $this->package_phase_runner->run_phase( $env_info, 'teardown', $pkg_id, $package_path, $artifacts_dir );
					// Note: teardown phase is for cleanup only - no result collection needed

					$package_total   = $setup_count + $run_count + $teardown_count;
					$total_executed += $package_total;

					$io->writeln( "<info>✓ {$pkg_id}: {$setup_count} setup, {$run_count} run, {$teardown_count} teardown commands executed</info>" );

					// Mark that we've processed the first package
					$is_first_package = false;

				} catch ( \Exception $e ) {
					$io->error( "Failed to execute package {$pkg_id}: " . $e->getMessage() );
					$failed_packages[] = $pkg_id;
					// Still mark as processed to maintain the sequence for subsequent packages
					$is_first_package = false;
				}
			}

			// Merge CTRF artifacts
			$this->result_collector->merge_ctrf( $artifacts_dir, $io );

			// Try to save Allure reports to final location
			$this->result_collector->save_allure_to_final_location( $artifacts_dir, $io );

			// Store artifacts directory in env_info for later use by Manager
			/** @phpstan-ignore-next-line property.notFound */
			$env_info->artifacts_dir = $artifacts_dir;

			// Output artifact locations
			$final_ctrf_path = $artifacts_dir . '/final/ctrf/ctrf-report.json';

			// Check for final Allure location
			$final_allure_path = $artifacts_dir . '/final/allure';
			if ( is_dir( $final_allure_path ) && ! empty( glob( $final_allure_path . '/*', GLOB_ONLYDIR ) ) ) {
				$io->writeln( "<info>Allure reports saved → {$final_allure_path}</info>" );
			}
			if ( file_exists( $final_ctrf_path ) ) {
				$io->writeln( "<info>CTRF merged → {$final_ctrf_path}</info>" );
			}

			// Summary
			if ( empty( $failed_packages ) ) {
				$io->success( "All test packages completed successfully. Total commands executed: {$total_executed}" );

				return Command::SUCCESS;
			} else {
				$io->error( 'Failed packages: ' . implode( ', ', $failed_packages ) . ". Total commands executed: {$total_executed}" );

				return Command::FAILURE;
			}
		} catch ( \RuntimeException $e ) {
			// Handle infrastructure failures (code 3)
			if ( $e->getCode() === 3 ) {
				$io->error( $e->getMessage() );

				return 3;
			}
			// Re-throw other RuntimeExceptions
			throw $e;
		} finally {
			// Run globalTeardown phase for all packages
			$io->writeln( '<info>Running globalTeardown phase for all packages...</info>' );
			foreach ( $test_packages as $pkg_id => $meta ) {
				$this->package_phase_runner->run_phase( $env_info, 'globalTeardown', $pkg_id, $meta['path'] );
			}
		}
	}
}
