<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\PackagePhaseRunner;
use QIT_CLI\Environment\ResultCollector;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_option_explicitly_provided;
use function QIT_CLI\is_windows;

class RunE2ECommand extends QITCommand {
	use OptionReuseTrait;

	protected E2EEnvironment $e2e_environment;
	protected WooExtensionsList $woo_extensions_list;
	protected PackagePhaseRunner $package_phase_runner;
	protected ResultCollector $result_collector;
	protected LocalTestRunNotifier $local_test_run_notifier;
	protected EnvironmentRunner $environment_runner;
	protected Cache $cache;

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
		LocalTestRunNotifier $local_test_run_notifier,
		EnvironmentRunner $environment_runner,
		Cache $cache
	) {
		$this->e2e_environment         = $e2e_environment;
		$this->woo_extensions_list     = $woo_extensions_list;
		$this->package_phase_runner    = $package_phase_runner;
		$this->result_collector        = $result_collector;
		$this->local_test_run_notifier = $local_test_run_notifier;
		$this->environment_runner      = $environment_runner;
		$this->cache                   = $cache;
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
		/** @var \QIT_CLI\QITInput $input */

		/* ─ Platform guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E tests on Windows, please use WSL.</comment>' );
			return self::FAILURE;
		}

		/*****************************************************************
		 * 1. Get environment options for delegation to env:up
		 */
		$env_up_options = $input->getEnvironmentOptions();

		// Handle activation test scenario
		$test_packages = $input->getTestPackages();
		if ( $input->getArgument( 'sut' ) === 'woocommerce' &&
			array_filter( $test_packages, fn( $pkg ) => str_starts_with( $pkg, 'woocommerce/activation:' ) ) ) {
			$output->writeln( '<info>Running activation test scenario.</info>' );
			App::setVar( 'QIT_ACTIVATION_TEST', 'yes' );
			$input->setOption( 'skip_activating_plugins', true );
			$input->setOption( 'skip_activating_themes', true );
		}

		// Determine test mode and wait behavior
		try {
			[ $test_mode, $wait ] = $this->determine_test_mode( $input );
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
			return Command::INVALID;
		}
		App::setVar( 'TEST_MODE', $test_mode );

		// Configure PW options
		$this->configure_pw_options( $input );

		// Parse environment variables
		if ( $input->hasOption( 'env' ) && $input->getOption( 'env' ) ) {
			$this->parse_env_vars( $input->getOption( 'env' ) );
		}

		// Add SUT to env:up options if provided
		$sut_info = $input->getSut();
		$sut_slug = $sut_info['slug'] ?? null;
		$sut_id   = null;
		$sut_type = null;
		if ( $sut_slug ) {
			// Resolve SUT ID and type
			try {
				if ( is_numeric( $sut_slug ) ) {
					$sut_id   = (int) $sut_slug;
					$sut_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( $sut_id );
				} else {
					$sut_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $sut_slug );
				}
				$sut_type = $this->woo_extensions_list->get_woo_extension_type( $sut_id );
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
				return Command::INVALID;
			}

			// Add SUT to env:up options using the complex format from old code
			$env_up_options = $this->add_sut_to_env_up_options( $input, $env_up_options, $sut_slug, $sut_type );
		}

		// Set environment exposure based on wait mode
		if ( $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' );
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' );
			// Don't set QIT_EXPOSE_ENVIRONMENT_TO=DOCKER for E2E tests
			// because Playwright runs on the host and needs to access the site
		}
		putenv( 'QIT_UP_AND_TEST=1' );

		// Set global variables
		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		if ( $sut_slug ) {
			App::setVar( 'QIT_SUT', $sut_id );
			App::setVar( 'QIT_SUT_SLUG', $sut_slug );
		}

		// Download test packages BEFORE env:up so we can mount them as volumes
		$test_packages = $this->download_test_packages(
			[
				[
					'type' => $this->test_type,
					'name' => $input->getProfileName(),
				],
			],
			$input->getTestPackages() // This includes both profile and CLI packages
		);

		// Prepare test package metadata with container paths BEFORE env:up
		$test_packages_metadata = [];
		$seen_remote_packages   = []; // Track remote packages for deduplication
		$local_package_counter  = []; // Track local packages with same namespace/package

		foreach ( $test_packages as $pkg_id => $meta ) {
			if ( isset( $meta['path'] ) ) {
				$is_local = file_exists( $pkg_id ) && is_dir( $pkg_id );

				if ( $is_local ) {
					// Local packages - never deduplicate, but need unique names
					$container_name = $this->container_name_from_manifest( $pkg_id, $local_package_counter );

					$test_packages_metadata[ $pkg_id ] = [
						'path'           => $meta['path'],
						'container_path' => '/qit/packages/' . $container_name,
					];
				} else {
					// Remote packages - deduplicate by package ID
					if ( isset( $seen_remote_packages[ $pkg_id ] ) ) {
						if ( $output->isVerbose() ) {
							$output->writeln( "<comment>Reusing existing mount for remote package: {$pkg_id}</comment>" );
						}
						// Reuse the existing metadata
						$test_packages_metadata[ $pkg_id ] = $seen_remote_packages[ $pkg_id ];
						continue;
					}

					// For remote packages, include version in container name to avoid conflicts
					$container_name = $this->container_name_for_remote_package( $pkg_id );

					$test_packages_metadata[ $pkg_id ] = [
						'path'           => $meta['path'],
						'container_path' => '/qit/packages/' . $container_name,
					];

					$seen_remote_packages[ $pkg_id ] = $test_packages_metadata[ $pkg_id ];
				}

				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Package mapping: {$pkg_id} -> /qit/packages/{$container_name}" );
				}
			}
		}

		// Add local test packages as volumes
		foreach ( $test_packages as $pkg_id => $meta ) {
			if ( isset( $meta['path'] ) && is_dir( $meta['path'] ) ) {
				// Skip if this was a duplicate that we already processed
				if ( ! isset( $test_packages_metadata[ $pkg_id ] ) ) {
					continue;
				}

				// This is a local path - add it as a volume
				$container_path = $test_packages_metadata[ $pkg_id ]['container_path'];

				if ( ! isset( $env_up_options['--volume'] ) ) {
					$env_up_options['--volume'] = [];
				}
				$env_up_options['--volume'][] = $meta['path'] . ':' . $container_path . ':ro';
			}
		}

		// Always output JSON for parsing
		$env_up_options['--json'] = true;

		// Run env:up and get the environment info
		try {
			/** @var E2EEnvInfo $env_info */
			$env_info = $this->environment_runner->run_environment( $env_up_options );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to start environment: %s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		} finally {
			putenv( 'QIT_HIDE_SITE_INFO' );
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' );
		}

		// Add SUT info to env_info if provided
		if ( $sut_slug ) {
			$env_info->sut = [
				'slug' => $sut_slug,
				'id'   => $sut_id,
				'type' => $sut_type,
			];
		}

		/*****************************************************************
		 * 3. Normal test execution flow
		 */
		// ─ validate shard, run phases, collect results, notify, etc.
		// … existing logic untouched …

		// Determine whether we should upload the Allure report.
		// By default we upload unless the user explicitly passes --no_upload_report.
		$should_upload = ! ( $input->hasOption( 'no_upload_report' ) && $input->getOption( 'no_upload_report' ) );
		App::setVar( 'should_upload_report', $should_upload );

		// Validate shard format (only if explicitly provided)
		if ( $input->hasOption( 'shard' ) ) {
			$shard = $input->getOption( 'shard' );
			if ( $shard && ! $this->validateShard( $shard, $output ) ) {
				return self::INVALID;
			}
		}

		// Set up globals and environment
		$this->setupGlobals( $env_info, $input );
		$this->handle_termination();

		// For testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'run_e2e' || getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );
			return Command::SUCCESS;
		}

		// Use the pre-calculated test packages metadata
		$env_info->test_packages_metadata = $test_packages_metadata;

		// Initialize e2e environment with the info from env:up
		$this->e2e_environment->init( $env_info );

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

		// If up_only or codegen mode, we're done
		if ( $wait ) {
			return Command::SUCCESS;
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

	/**
	 * Generate a container name for a remote package.
	 *
	 * @param string $package_id The remote package reference.
	 * @return string The container-safe directory name.
	 * @throws \InvalidArgumentException If package reference is invalid.
	 */
	private function container_name_for_remote_package( string $package_id ): string {
		$counter = null; // Not used for remote packages
		return $this->container_name_from_manifest( $package_id, $counter, true );
	}

	/**
	 * Generate a container name from manifest.json or package reference.
	 *
	 * For local packages: reads namespace/package from manifest.json
	 * For remote packages: parses namespace/package/version from reference
	 *
	 * @param string                 $package_id The package ID (local path or remote reference).
	 * @param array<string,int>|null &$counter Counter array for local packages to ensure uniqueness.
	 * @param bool                   $include_version Whether to include version in the container name (for remote packages).
	 * @return string The container-safe directory name.
	 * @throws \InvalidArgumentException If manifest is missing or invalid.
	 */
	private function container_name_from_manifest( string $package_id, ?array &$counter = null, bool $include_version = false ): string {
		$namespace = '';
		$package   = '';

		// Check if this is a local path
		if ( file_exists( $package_id ) && is_dir( $package_id ) ) {
			// Local package - read manifest.json
			$manifest_path = rtrim( $package_id, '/\\' ) . '/manifest.json';

			if ( ! file_exists( $manifest_path ) ) {
				throw new \InvalidArgumentException(
					"Test package directory must contain manifest.json: {$package_id}"
				);
			}

			$manifest_content = file_get_contents( $manifest_path );
			$manifest         = json_decode( $manifest_content, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \InvalidArgumentException(
					"Invalid JSON in manifest.json: {$package_id} - " . json_last_error_msg()
				);
			}

			if ( empty( $manifest['namespace'] ) || empty( $manifest['package'] ) ) {
				throw new \InvalidArgumentException(
					"Manifest must contain 'namespace' and 'package' fields: {$package_id}"
				);
			}

			$namespace = $manifest['namespace'];
			$package   = $manifest['package'];
			$version   = null; // Local packages don't have versions
		} else {
			// Remote package reference - parse the format
			// Expected formats:
			// - namespace/package:version
			// - namespace/package
			if ( ! preg_match( '/^([^\/]+)\/([^:]+)(?::(.+))?$/', $package_id, $matches ) ) {
				throw new \InvalidArgumentException(
					"Invalid package reference format. Expected 'namespace/package[:version]', got: {$package_id}"
				);
			}

			$namespace = $matches[1];
			$package   = $matches[2];
			$version   = isset( $matches[3] ) ? $matches[3] : null;
		}

		// Sanitize for container safety
		$safe_namespace = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $namespace ) );
		$safe_package   = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $package ) );
		$base_name      = trim( "{$safe_namespace}-{$safe_package}", '-' );

		// Add version to container name if requested (for remote packages that need version distinction)
		if ( $include_version && $version !== null ) {
			$safe_version = strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $version ) );
			$base_name   .= '-' . $safe_version;
		}

		// For local packages, add counter if needed to ensure uniqueness
		if ( $counter !== null ) {
			$key = $base_name;
			if ( ! isset( $counter[ $key ] ) ) {
				$counter[ $key ] = 0;
			}
			++$counter[ $key ];

			if ( $counter[ $key ] > 1 ) {
				$base_name .= '-' . $counter[ $key ];
			}
		}

		return $base_name;
	}

	private function handle_termination(): void {
		register_shutdown_function( static function () {
			static::shutdown_test_run();
		} );

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function ( $signo ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
				echo "\n\nTest interrupted by user (Ctrl+C). Cleaning up...\n";

				// Terminate the current test process if it exists
				$current_process = App::getVar( 'qit_current_test_process' );
				if ( $current_process instanceof Process ) {
					try {
						// Send SIGTERM to allow graceful shutdown
						$current_process->stop( 5, SIGTERM );
						// If still running after 5 seconds, force kill
						if ( $current_process->isRunning() ) {
							$current_process->stop( 0, SIGKILL );
						}
					} catch ( \Exception $e ) {
						// Ignore errors during process termination
						unset( $e ); // Mark as intentionally unused
					}
				}

				// Small delay to let final output flush
				usleep( 500000 ); // 0.5 seconds

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

		// Don't show "Shutting down" message here - show it after our info

		// Show report information before shutting down
		$artifacts_dir = App::getVar( 'qit_test_artifacts_dir' );
		if ( ! empty( $artifacts_dir ) && is_dir( $artifacts_dir ) ) {
			// Wait a moment for any final output from Playwright
			usleep( 500000 ); // 0.5 seconds

			echo "\n";
			echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
			echo "QIT Test Information:\n";
			echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
			echo "\nTest artifacts directory:\n";
			echo "  $artifacts_dir\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			// Try to find HTML reports in multiple locations
			$html_reports = [];

				// Look in artifacts directory
			if ( is_dir( $artifacts_dir ) ) {
				$html_reports = array_merge( $html_reports, glob( $artifacts_dir . '/**/index.html', GLOB_BRACE ) );
			}

				// Also check if there are test package results directories
				$test_packages = App::getVar( 'qit_test_packages' );
			if ( ! empty( $test_packages ) ) {
				foreach ( $test_packages as $pkg_id => $meta ) {
					if ( isset( $meta['path'] ) && is_dir( $meta['path'] ) ) {
						// Check common report locations in test packages
						$pkg_reports = glob( $meta['path'] . '/playwright-report/index.html' );
						if ( empty( $pkg_reports ) ) {
							$pkg_reports = glob( $meta['path'] . '/test-results/*/index.html', GLOB_BRACE );
						}
						if ( empty( $pkg_reports ) ) {
							$pkg_reports = glob( $meta['path'] . '/results/*/index.html', GLOB_BRACE );
						}
						$html_reports = array_merge( $html_reports, $pkg_reports );
					}
				}
			}

				$report_found = false;
			if ( ! empty( $html_reports ) ) {
				// Find the best report (prefer playwright-report directories)
				$best_report = null;
				foreach ( $html_reports as $report ) {
					if ( strpos( $report, 'playwright-report' ) !== false ) {
						$best_report = $report;
						break;
					}
				}
				if ( ! $best_report ) {
					$best_report = reset( $html_reports );
				}

				// Update cache for e2e-report command
				try {
					$cache = App::make( \QIT_CLI\Cache::class );
					$cache->set( 'last_e2e_report', json_encode( [
						'local_playwright' => dirname( $best_report ),
					] ), DAY_IN_SECONDS );
					$report_found = true;
				} catch ( \Exception $e ) {
					// Cache update failure is non-critical
					unset( $e ); // Mark as intentionally unused
				}
			}

			if ( $report_found ) {
				echo "\nView test report with:\n";
				echo "  qit e2e-report\n";
			} else {
				echo "\nNo HTML reports generated yet. Check the artifacts directory.\n";

				// Look for CTRF reports as alternative
				$ctrf_reports = glob( $artifacts_dir . '/**/*ctrf*.json', GLOB_BRACE );
				if ( ! empty( $ctrf_reports ) ) {
					echo "\nCTRF reports available:\n";
					foreach ( array_slice( $ctrf_reports, 0, 2 ) as $ctrf ) {
						echo '  - ' . basename( $ctrf ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			}

				echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
		}
	}

	echo "\nShutting down environment...\n";

		$env_to_shutdown = App::getVar( 'env_to_shutdown' );
	if ( ! empty( $env_to_shutdown ) ) {
		try {
			// Get the environment info from the environment monitor
			$env_monitor = App::make( \QIT_CLI\Environment\EnvironmentMonitor::class );
			try {
				$env_info = $env_monitor->get_env_info_by_id( $env_to_shutdown );
				Environment::down( $env_info );
			} catch ( \Exception $e ) {
				\QIT_CLI\debug_log( 'Failed to find environment info for shutdown: ' . $env_to_shutdown . ' - ' . $e->getMessage(), 'comment' );
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
	// Set up the DI container variable for environment shutdown
	App::setVar( 'env_to_shutdown', $env_info->env_id );
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

		// Store in DI container for signal handler access
		App::setVar( 'qit_test_artifacts_dir', $artifacts_dir );

		// Get bootstrap package IDs to skip them (they only run globalSetup)
		$bootstrap_package_ids = array_keys( $env_info->bootstrap_packages ?? [] );

		$io->section( 'Running Test Packages' );

		// Store test packages in DI container for signal handler access
		App::setVar( 'qit_test_packages', $test_packages );

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
							// CTRF is mandatory for the run phase - if collection fails, the test is invalid
							$io->writeln( "<error>CTRF collection failed: {$collector_err->getMessage()}</error>" );
							$io->writeln( '<error>Test terminated abnormally - CTRF output is required</error>' );
							throw new \RuntimeException( 'Test failed to produce required CTRF output: ' . $collector_err->getMessage() );
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

		// Merge blob reports into HTML
		$this->result_collector->merge_blob( $artifacts_dir, $io );

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

		// Check for merged HTML report
		$final_html_report = $artifacts_dir . '/final/html-report/index.html';
		if ( file_exists( $final_html_report ) ) {
			$io->writeln( "<info>HTML report → {$final_html_report}</info>" );
			$io->writeln( "<info>Open in browser: file://{$final_html_report}</info>" );

			// Show how to open the report with a simple PHP server
			$report_dir = dirname( $final_html_report );
			$io->writeln( "<info>Or serve with: php -S localhost:8000 -t {$report_dir}</info>" );
			$io->writeln( '<info>Then open: http://localhost:8000</info>' );

			// Store in cache for e2e-report command
			$this->cache->set( 'last_e2e_report', json_encode( [
				'local_playwright' => $report_dir,
			] ), DAY_IN_SECONDS );
			$io->writeln( '<info>Or use: qit e2e-report</info>' );
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
			try {
				$this->package_phase_runner->run_phase( $env_info, 'globalTeardown', $pkg_id, $meta['path'] );
			} catch ( \Throwable $e ) {
				// Continue with other teardowns even if one fails
				$io->writeln( "<comment>Warning: globalTeardown failed for {$pkg_id}: {$e->getMessage()}</comment>" );
			}
		}

		// Always try to generate and show reports, even if tests were interrupted
		if ( is_dir( $artifacts_dir ) ) {
			$io->writeln( "\n<info>Test Artifacts:</info>" );
			$io->writeln( "Location: <comment>{$artifacts_dir}</comment>" );

			try {
				// Try to merge any partial results
				$this->result_collector->merge_ctrf( $artifacts_dir, $io );
				$this->result_collector->merge_blob( $artifacts_dir, $io );

				// Look for any HTML reports (individual or merged)
				$report_found = false;

				// Check for merged HTML report first
				$final_html_report = $artifacts_dir . '/final/html-report/index.html';
				if ( file_exists( $final_html_report ) ) {
					$report_dir = dirname( $final_html_report );
					$this->cache->set( 'last_e2e_report', json_encode( [
						'local_playwright' => $report_dir,
					] ), DAY_IN_SECONDS );
					$report_found = true;
				} else {
					// Check for individual package HTML reports
					$html_reports = glob( $artifacts_dir . '/**/index.html', GLOB_BRACE );
					if ( ! empty( $html_reports ) ) {
						// Find the most relevant report (prefer playwright-report directories)
						$best_report = null;
						foreach ( $html_reports as $report ) {
							if ( strpos( $report, 'playwright-report' ) !== false || strpos( $report, 'html-report' ) !== false ) {
								$best_report = $report;
								break;
							}
						}
						if ( ! $best_report ) {
							$best_report = reset( $html_reports );
						}

						$report_dir = dirname( $best_report );
						$this->cache->set( 'last_e2e_report', json_encode( [
							'local_playwright' => $report_dir,
						] ), DAY_IN_SECONDS );
						$report_found = true;
					}
				}

				if ( $report_found ) {
					$io->writeln( "\n<info>View test report with:</info>" );
					$io->writeln( '  <comment>qit e2e-report</comment>' );
				}

				// Also show other useful artifacts
				$ctrf_reports = glob( $artifacts_dir . '/**/*ctrf*.json', GLOB_BRACE );
				if ( ! empty( $ctrf_reports ) ) {
					$io->writeln( "\n<info>CTRF reports found:</info>" );
					foreach ( array_slice( $ctrf_reports, 0, 3 ) as $ctrf ) {
						$io->writeln( '  - ' . basename( dirname( $ctrf ) ) . '/' . basename( $ctrf ) );
					}
					if ( count( $ctrf_reports ) > 3 ) {
						$io->writeln( '  ... and ' . ( count( $ctrf_reports ) - 3 ) . ' more' );
					}
				}
			} catch ( \Throwable $e ) {
				$io->writeln( "<comment>Warning: Could not process test reports: {$e->getMessage()}</comment>" );
				$io->writeln( "<info>Raw artifacts available at: {$artifacts_dir}</info>" );
			}
		}
	}
}


	/**
	 * Determine the test mode and whether to wait.
	 *
	 * @param InputInterface $input
	 * @return array{0:string,1:bool} Returns [test_mode, wait]
	 * @throws \RuntimeException If both ui and codegen are set.
	 */
private function determine_test_mode( InputInterface $input ): array {
	if ( $input->getOption( 'ui' ) && $input->getOption( 'codegen' ) ) {
		throw new \RuntimeException( 'Cannot run tests in both "UI" and "Codegen" mode at the same time.' );
	}

	if ( $input->getOption( 'ui' ) ) {
		$test_mode = 'ui';
	} elseif ( $input->getOption( 'codegen' ) ) {
		putenv( 'QIT_CODEGEN=1' );
		$test_mode = 'codegen';
	} else {
		$test_mode = 'headless';
	}

	$wait = $test_mode === 'codegen';

	return [ $test_mode, $wait ];
}

	/**
	 * Configure Playwright options.
	 *
	 * @param InputInterface $input
	 */
private function configure_pw_options( InputInterface $input ): void {
	$pw_options = $input->getOption( 'pw_options' ) ?? '';
	if ( ! empty( $pw_options ) ) {
		// Strip surrounding quotes if present.
		if ( substr( $pw_options, 0, 1 ) === '"' && substr( $pw_options, -1 ) === '"' ) {
			$pw_options = substr( $pw_options, 1, -1 );
		}
	}

	if ( $input->getOption( 'update_snapshots' ) ) {
		$pw_options .= ' --update-snapshots';
	}

	App::setVar( 'pw_options', $pw_options );
}

	/**
	 * Parse environment variables.
	 *
	 * @param array<string> $env_vars
	 * @throws \RuntimeException If invalid format.
	 */
private function parse_env_vars( array $env_vars ): void {
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
	 * Add SUT to env:up options.
	 *
	 * @param InputInterface $input
	 * @param array<mixed>   $env_up_options
	 * @param string         $woo_extension_slug
	 * @param string|null    $sut_type
	 * @return array<mixed>
	 */
private function add_sut_to_env_up_options( InputInterface $input, array $env_up_options, string $woo_extension_slug, ?string $sut_type ): array {
	if ( ! $sut_type ) {
		$sut_type = 'plugin';
	}

	$key = ( $sut_type === 'theme' ) ? '--theme' : '--plugin';

	// Add to env:up options
	if ( ! isset( $env_up_options[ $key ] ) ) {
		$env_up_options[ $key ] = [];
	}
	$env_up_options[ $key ][] = $woo_extension_slug;

	return $env_up_options;
}
}
