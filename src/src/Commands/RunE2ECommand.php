<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo; // @phan-suppress-current-line PhanUnreferencedUseNormal - Used in PHPDoc
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\PackagePhaseRunner;
use QIT_CLI\Environment\ResultCollector;
use QIT_CLI\E2E\Result\TestResult;
use QIT_CLI\Environment\EnvironmentRunner;
use QIT_CLI\Utils\LocalTestRunNotifier;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use function QIT_CLI\get_manager_url;
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
			->addArgument( 'sut', InputArgument::OPTIONAL, 'Extension identifier: plugin/theme slug or WooCommerce.com ID' )
			->addArgument( 'passthrough', InputArgument::IS_ARRAY, 'Arguments after --' )
			/* ─────────────── SUT‑related options ─────────────── */
			->addOption( 'zip', null, InputOption::VALUE_OPTIONAL,
			'Custom source for the plugin/theme (local ZIP, local directory, or URL to a .zip file)' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL,
			'[Deprecated] Use --zip instead' )
			->addOption( 'async', null, InputOption::VALUE_NEGATABLE,
			'Enqueue test and return immediately without waiting', false )
			->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE,
			'(Deprecated) Wait for test completion - this is now the default behavior', false )
			->addOption( 'passthrough_target', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
			'Test packages that should receive passthrough arguments (multiple allowed)', [] )

			/*
			─────────────── Shared Options (reused from env:up) ───────────────
			*/
			/* ─────────────── E2E-specific options ─────────────── */
			->addOption(
				'test-package',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Test packages to include (multiple values allowed)',
				[]
			)
			->reuseOption( 'env:up', 'environment' )
			->reuseOption( 'env:up', 'php_version' )
			->reuseOption( 'env:up', 'wordpress_version' )
			->reuseOption( 'env:up', 'woocommerce_version' )
			->reuseOption( 'env:up', 'plugin' )
			->reuseOption( 'env:up', 'theme' )
			->reuseOption( 'env:up', 'volume' )
			->reuseOption( 'env:up', 'php_extension' )
			->reuseOption( 'env:up', 'object_cache' )
			->reuseOption( 'env:up', 'tunnel' )
			->reuseOption( 'env:up', 'offline' )
			->reuseOption( 'env:up', 'online' )
			->reuseOption( 'env:up', 'env' )
			->reuseOption( 'env:up', 'env_file' )
			->reuseOption( 'env:up', 'json' )
			->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins' )
			->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes' )

			// Execution options
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'Notify on failures' )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, 'Register into a group', false )
			->addOption( 'print-report-url', null, InputOption::VALUE_NONE, 'Print the test report URL (contains sensitive data - use cautiously in public logs)' )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Run tests in Playwright UI mode' )
			->addOption( 'keep-env', null, InputOption::VALUE_NONE, 'Keep the environment running after tests complete (for debugging with Playwright MCP)' );
	}

	/**
	 * Execute the command.
	 *
	 * @param QITInput        $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 */
	protected function doExecute( QITInput $input, OutputInterface $output ): int {

		/* ─ Platform guard ─ */
		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E tests on Windows, please use WSL.</comment>' );

			return self::FAILURE;
		}

		/* ─ Warn about unimplemented --async flag ─ */
		if ( $input->getOption( 'async' ) && ! $input->getOption( 'json' ) ) {
			$output->writeln( '<comment>Warning: The --async flag is not implemented for E2E tests yet.</comment>' );
			$output->writeln( '<comment>E2E tests always run synchronously (they execute locally).</comment>' );
			$output->writeln( '' );
		}

		/*****************************************************************
		 * 1. Get environment options for delegation to env:up
		 */
		$env_up_options = $input->get_environment_options();

		// Handle activation test scenario
		$test_packages = $input->get_test_packages();
		if ( $input->getArgument( 'sut' ) === 'woocommerce' &&
			array_filter( $test_packages, fn( $pkg ) => str_starts_with( $pkg, 'woocommerce/activation:' ) ) ) {
			$output->writeln( '<info>Running activation test scenario.</info>' );
			App::setVar( 'QIT_ACTIVATION_TEST', 'yes' );
			$input->setOption( 'skip_activating_plugins', true );
			$input->setOption( 'skip_activating_themes', true );
		}

		// Set default test mode to headless
		$test_mode = 'headless';
		App::setVar( 'TEST_MODE', $test_mode );

		// Parse environment variables from --env and --env_file
		$cli_env_vars = $input->hasOption( 'env' ) ? (array) $input->getOption( 'env' ) : [];
		$env_files    = $input->hasOption( 'env_file' ) ? (array) $input->getOption( 'env_file' ) : [];

		// Use EnvParser to handle both --env and --env_file consistently
		$env_parser      = App::make( \QIT_CLI\Environment\EnvParser::class );
		$parsed_env_vars = $env_parser->parse( $cli_env_vars, $env_files );

		// Add SUT to env:up options if provided
		$sut_info = $input->get_sut();
		$sut_slug = $sut_info['slug'] ?? null;
		$sut_id   = null;
		$sut_type = null;

		// Warn if CLI slug overrides qit.json SUT
		$cli_sut_arg     = $input->getArgument( 'sut' );
		$config_sut_slug = $input->get_resolved_config()['sut']['slug'] ?? null;
		if ( $cli_sut_arg && $config_sut_slug && $cli_sut_arg !== $config_sut_slug ) {
			$stderr = $output instanceof \Symfony\Component\Console\Output\ConsoleOutput
				? $output->getErrorOutput()
				: $output;
			$stderr->writeln( sprintf(
				'<comment>Using CLI slug "%s" instead of qit.json value "%s".</comment>',
				$cli_sut_arg,
				$config_sut_slug
			) );
		}

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

		if ( ! $sut_slug ) {
			$output->writeln( '<error>No System Under Test specified. Provide a slug as the first argument or set "sut" in qit.json.</error>' );

			return Command::FAILURE;
		}

		// Set environment exposure
		putenv( 'QIT_HIDE_SITE_INFO=1' );
		// Don't set QIT_EXPOSE_ENVIRONMENT_TO=DOCKER for E2E tests
		// because Playwright runs on the host and needs to access the site
		putenv( 'QIT_UP_AND_TEST=1' );

		// Set global variables
		App::setVar( 'QIT_SUT', $sut_id );
		App::setVar( 'QIT_SUT_SLUG', $sut_slug );

		// Skip downloading test packages here - env:up will handle it
		// We'll reconstruct test_packages from env_info after env:up runs

		// Pass network mode options to env:up (it will determine based on test packages)
		if ( $input->hasOption( 'offline' ) && $input->getOption( 'offline' ) ) {
			$env_up_options['--offline'] = true;
		} elseif ( $input->hasOption( 'online' ) && $input->getOption( 'online' ) ) {
			$env_up_options['--online'] = true;
		}
		// If neither flag is set, env:up will use auto mode and check test package requirements

		// We'll build the package local map after env:up when we have the packages

		// env:up will now handle adding test package volumes automatically

		// Pass original test package references to env:up for requirement processing
		// env:up will handle downloading (or cache hits) and requirement extraction
		$original_test_packages = $input->get_test_packages(); // Get the original refs from input

		// Merge utility packages from the selected environment
		// Check for 'utilities' (preferred) and 'global_setup' (legacy) fields
		$env_name         = $input->getOption( 'environment' ) ?? 'default';
		$env_config       = $this->get_environment_config( $env_name );
		$utility_packages = [];
		if ( isset( $env_config['utilities'] ) && is_array( $env_config['utilities'] ) ) {
			$utility_packages = $env_config['utilities'];
		}
		if ( isset( $env_config['global_setup'] ) && is_array( $env_config['global_setup'] ) ) {
			$utility_packages = array_merge( $utility_packages, $env_config['global_setup'] );
		}
		if ( ! empty( $utility_packages ) ) {
			// Prepend utility packages so they run first
			$original_test_packages = array_merge( $utility_packages, $original_test_packages );
		}

		// Add original test package references to env:up options
		if ( ! empty( $original_test_packages ) ) {
			if ( ! isset( $env_up_options['--test-package'] ) ) {
				$env_up_options['--test-package'] = [];
			}
			// Pass the original references directly - env:up will download them (likely cache hits)
			$env_up_options['--test-package'] = array_merge( $env_up_options['--test-package'], $original_test_packages );

			// CRITICAL: Tell env:up to skip test phases since run:e2e will handle them
			// This prevents double execution of globalSetup and setup phases
			$env_up_options['--skip-test-phases'] = true;
		}

		// Always output JSON for parsing
		$env_up_options['--json'] = true;

		// Run env:up and get the environment info
		try {
			/** @var E2EEnvInfo $env_info */
			$env_info = $this->environment_runner->run_environment( $env_up_options );
			App::singleton( EnvInfo::class, $env_info );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		} finally {
			// Clean up temporary environment variables
			putenv( 'QIT_HIDE_SITE_INFO' );
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' );
		}

		// Add SUT info to env_info (preserve existing version from env:up)
		$env_info->sut = array_merge( $env_info->sut, [
			'slug' => $sut_slug,
			'id'   => $sut_id,
			'type' => $sut_type,
		] );

		// Reconstruct test_packages from env_info for validation and display
		$test_packages = [];
		if ( ! empty( $env_info->test_packages_for_setup ) ) {
			foreach ( $env_info->test_packages_for_setup as $original_ref => $pkg_info ) {
				// Use original_ref as the key since that's what was passed to env:up
				// The package_id might be different (e.g., for local packages)
				// Check if this package has manifest data (added by env:up)
				if ( ! empty( $pkg_info['manifest'] ) ) {
					// Determine version based on source
					$version = 'local'; // Default for local packages
					if ( $pkg_info['source'] === 'registry' && str_contains( $original_ref, ':' ) ) {
						// Extract version from registry package reference
						[ , $version ] = explode( ':', $original_ref, 2 );
					}

					// Reconstruct the test_packages array format expected by the rest of the code
					$test_packages[ $original_ref ] = [
						'manifest' => new \QIT_CLI\PreCommand\Objects\TestPackageManifest( $pkg_info['manifest'] ),
						'path'     => $pkg_info['path'],
						'metadata' => [
							'downloaded_path' => $pkg_info['path'],
							'version'         => $version,
							'remote'          => $pkg_info['source'] === 'registry',  // Set remote based on source
						],
					];
				}
			}
		}

		// Validate that packages from utilities/global_setup don't have run phase
		if ( ! empty( $utility_packages ) && ! empty( $test_packages ) ) {
			$invalid_utilities = [];
			foreach ( $utility_packages as $package_ref ) {
				if ( isset( $test_packages[ $package_ref ] ) ) {
					$manifest = $test_packages[ $package_ref ]['manifest'];
					if ( $manifest instanceof \QIT_CLI\PreCommand\Objects\TestPackageManifest && $manifest->has_phase( 'run' ) ) {
						$package_id          = $manifest->get_package_id();
						$invalid_utilities[] = $package_id;
					}
				}
			}

			if ( ! empty( $invalid_utilities ) ) {
				$package_list = implode( "\n  - ", $invalid_utilities );
				$output->writeln( "<error>Validation Error: Packages in 'utilities' or 'global_setup' must NOT have a 'run' phase.</error>" );
				$output->writeln( '' );
				$output->writeln( 'The following packages have a run phase and cannot be used as utilities:' );
				foreach ( $invalid_utilities as $pkg ) {
					$output->writeln( "  - {$pkg}" );
				}
				$output->writeln( '' );
				$output->writeln( '<comment>To fix this:</comment>' );
				$output->writeln( '1. Remove the \'run\' phase from these packages to make them utilities, OR' );
				$output->writeln( '2. Move them to \'test_packages\' in your test configuration, OR' );
				$output->writeln( '3. Remove them from the \'utilities\'/\'global_setup\' array' );
				$output->writeln( '' );
				$output->writeln( 'Utility packages are for environment setup only and should not execute tests.' );

				return Command::FAILURE;
			}
		}

		// Duplicate detection has been moved to UpEnvironmentCommand to fail earlier
		// before any expensive operations like environment setup

		// Now validate version consistency for subpackages
		if ( ! empty( $test_packages ) ) {
			$this->validate_subpackage_versions( $test_packages );
		}

		// Build package local map for ResultCollector
		$package_local_map = [];
		if ( ! empty( $test_packages ) ) {
			foreach ( $test_packages as $pkg_id => $meta ) {
				if ( ! empty( $meta['path'] ) ) {
					$is_local                     = file_exists( $pkg_id ) && is_dir( $pkg_id );
					$package_local_map[ $pkg_id ] = $is_local;
				}
			}
		}
		// Set the map in ResultCollector for later use
		\QIT_CLI\Environment\ResultCollector::set_package_local_map( $package_local_map );

		// Display environment summary (similar to env:up)
		if ( ! $input->getOption( 'json' ) ) {
			$this->renderEnvironmentSummary( $output, $env_info, $test_packages );
			\QIT_CLI\Utils\UtilitySuggestions::render( $output, $env_info );
		}

		/*****************************************************************
		 * 3. Normal test execution flow
		 */
		// ─ validate shard, run phases, collect results, notify, etc.
		// … existing logic untouched …

		// No shard validation needed - pass through to test framework via runner_args

		// Set up globals and environment
		$this->setupGlobals( $env_info, $input );
		$this->handle_termination();

		// For testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'run_e2e' || getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info, JSON_UNESCAPED_SLASHES ) );

			return Command::SUCCESS;
		}

		// Build test packages metadata from reconstructed test_packages
		$test_packages_metadata = [];
		if ( ! empty( $test_packages ) ) {
			foreach ( $test_packages as $pkg_id => $meta ) {
				$test_packages_metadata[ $pkg_id ] = [
					'path'           => $meta['path'],
					'container_path' => $env_info->test_packages_for_setup[ $pkg_id ]['container_path'] ?? '',
				];
			}
		}
		$env_info->test_packages_metadata = $test_packages_metadata;

		// Initialize e2e environment with the info from env:up
		$this->e2e_environment->init( $env_info );

		// Notify test started
		$test_run_id = null;
		if ( isset( $env_info->sut['slug'] ) ) {
			$woo_extension_id    = $this->woo_extensions_list->get_woo_extension_id_by_slug( $env_info->sut['slug'] );
			$woocommerce_version = $env_info->woocommerce_version;
			$notify              = $input->getOption( 'notify' ) ?? false;

			// Determine if this is a development build by checking if SUT is from a marketplace.
			$is_development = false;
			$sut_extension  = null;

			// Find the SUT Extension object in plugins.
			foreach ( $env_info->plugins as $plugin ) {
				if ( $plugin->slug === $env_info->sut['slug'] ) {
					$sut_extension = $plugin;
					break;
				}
			}

			// If not found in plugins, check themes.
			if ( ! $sut_extension ) {
				foreach ( $env_info->themes as $theme ) {
					if ( $theme->slug === $env_info->sut['slug'] ) {
						$sut_extension = $theme;
						break;
					}
				}
			}

			// Development build = NOT from a recognized marketplace (wporg or wccom).
			if ( $sut_extension && ! in_array( $sut_extension->from, [ 'wporg', 'wccom' ], true ) ) {
				$is_development = true;
			}

			$this->local_test_run_notifier->notify_test_started(
				$woo_extension_id,
				$woocommerce_version,
				$env_info,
				$is_development,
				$notify,
				$this->test_type
			);

			// Get the test run ID from the notifier
			$test_run_id = App::getVar( 'test_run_id' );
		}

		// Get passthrough args to pass through to test framework
		$passthrough_args = $input->getArgument( 'passthrough' ) ?? [];

		// If --ui flag is set, prepend Playwright UI mode options
		$ui_mode_enabled = $input->getOption( 'ui' );
		if ( $ui_mode_enabled ) {
			$ui_args          = [ '--ui', '--ui-port=8086', '--ui-host=0.0.0.0' ];
			$passthrough_args = array_merge( $ui_args, $passthrough_args );
			App::setVar( 'ui_mode_enabled', true );
		}

		// Get passthrough targets for explicit control
		$passthrough_targets = $input->getOption( 'passthrough_target' ) ?? [];

		// Run tests with test packages
		$io = new SymfonyStyle( $input, $output );
		[ $exit_status, $orchestrator_from_run, $artifacts_dir ] = $this->runTestPackages( $env_info, $test_packages, $io, $passthrough_args, $parsed_env_vars, $passthrough_targets );

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
				unset( $e ); // Unused but expected
			}

			$test_result->set_status( $exit_status === Command::SUCCESS ? 'success' : 'failed' );

			// Post-processing section - merging reports and uploading
			// Use orchestrator from runTestPackages
			$orchestrator = $orchestrator_from_run;

			$orchestrator->post_processing_start();

			// Save orchestrator CTRF for lifecycle phases
			$orchestrator->save_orchestrator_ctrf( $artifacts_dir );

			// Merge CTRF artifacts (including orchestrator.json)
			$this->result_collector->merge_ctrf( $artifacts_dir, $io, $orchestrator );

			// Read merged CTRF report to get accurate test counts
			$ctrf_report_path = $artifacts_dir . '/final/ctrf/ctrf-report.json';
			if ( file_exists( $ctrf_report_path ) ) {
				$ctrf_data = json_decode( file_get_contents( $ctrf_report_path ), true );
				if ( isset( $ctrf_data['results']['summary'] ) ) {
					$orchestrator->update_test_stats( $ctrf_data['results']['summary'] );
				}
			}

			// Merge blob reports into HTML
			$this->result_collector->merge_blob( $artifacts_dir, $io, $orchestrator );

			// Try to save Allure reports to final location
			$allure_status = $this->result_collector->save_allure_to_final_location( $artifacts_dir, $io, $orchestrator );

			// Get Allure tracking data to determine if we should upload
			$allure_tracking = $this->result_collector->get_allure_tracking();

			// Validate Allure configuration completeness
			$should_skip_allure_upload = false;
			if ( $allure_tracking ) {
				$total_packages          = $allure_tracking['total_packages'];
				$packages_with_allure    = $allure_tracking['packages_with_allure'];
				$packages_without_allure = $allure_tracking['packages_without_allure'];

				if ( $packages_with_allure > 0 && $packages_with_allure < $total_packages ) {
					// Mixed state - some packages have Allure, some don't
					$should_skip_allure_upload = true;

					$orchestrator->post_processing_message( '⚠ Allure incomplete (will not upload)', false );
					$orchestrator->post_processing_message( sprintf( '  %d of %d packages have Allure configured', $packages_with_allure, $total_packages ), false );
					if ( count( $packages_without_allure ) <= 3 ) {
						$orchestrator->post_processing_message( '  Missing: ' . implode( ', ', $packages_without_allure ), false );
					} else {
						$orchestrator->post_processing_message( sprintf( '  Missing: %s and %d more',
							implode( ', ', array_slice( $packages_without_allure, 0, 3 ) ),
							count( $packages_without_allure ) - 3
						), false );
					}
					$orchestrator->post_processing_message( '  All packages must have Allure for remote reports', false );

					// Set flag to prevent upload
					App::setVar( 'skip_allure_upload', true );
				} elseif ( $packages_with_allure === 0 && $total_packages > 0 ) {
					// No packages have Allure configured
					$orchestrator->post_processing_message( 'ℹ No Allure configuration found', false );
					$orchestrator->post_processing_message( '  Add "allure-dir" to qit-test.json for failure debugging', false );
				} elseif ( $packages_with_allure === $total_packages && $total_packages > 0 ) {
					// All packages have Allure configured - perfect!
					$orchestrator->post_processing_message( 'Allure configured (uploads on test failure)' );
				}
			}

			// Update cache with final HTML report location for e2e-report command
			$final_html_report = $artifacts_dir . '/final/html-report/index.html';
			if ( file_exists( $final_html_report ) ) {
				$report_dir = dirname( $final_html_report );
				$this->cache->set( 'last_e2e_report', json_encode( [
					'local_playwright' => $report_dir,
				] ), DAY_IN_SECONDS );
			}

			// Pass orchestrator to notify_test_finished for upload progress (still in POST-PROCESSING)
			[ $report_url, $exit_status_override ] = $this->local_test_run_notifier->notify_test_finished( $test_result, $orchestrator );

			// End post-processing after upload
			$orchestrator->post_processing_end();

			// Use exit status override if provided
			if ( $exit_status_override !== null ) {
				$exit_status = $exit_status_override;
			}
		}

		// Show orchestrator summary (always available from runTestPackages)
		$orchestrator = $orchestrator_from_run;

		// Check if we're in CI mode
		$is_ci = ! empty( getenv( 'CI' ) );

		// Only show remote URL if explicitly requested in CI, or always in non-CI
		$should_show_url = ! $is_ci || $input->getOption( 'print-report-url' );

		$final_html_report_path = $artifacts_dir . '/final/html-report/index.html';

		$summary_data = [
			'status'           => $exit_status === Command::SUCCESS ? 'passed' : 'failed',
			'local_command'    => 'qit report',
			'remote_url'       => $should_show_url ? ( $report_url ?? '' ) : '',
			'html_report_path' => file_exists( $final_html_report_path ) ? $final_html_report_path : '',
			'artifacts_dir'    => $artifacts_dir,
			'keep_env'         => $input->getOption( 'keep-env' ) ? [
				'env_id'   => $env_info->env_id,
				'site_url' => $env_info->site_url,
			] : null,
		];

		// Set JSON mode flag for shutdown handler
		if ( $input->getOption( 'json' ) ) {
			App::setVar( 'QIT_JSON_MODE', true );
		}

		// Output JSON if requested, otherwise show summary
		if ( $input->getOption( 'json' ) ) {
			// Fetch the test report from Manager API, just like 'qit get' does
			if ( $test_run_id ) {
				try {
					$json = App::make( RequestBuilder::class )
						->with_url( get_manager_url() . '/wp-json/cd/v1/get-single' )
						->with_method( 'POST' )
						->with_post_body( [
							'test_run_id' => $test_run_id,
						] )
						->with_retry( 3 )
						->request();

					// Output the Manager API response directly
					$output->write( $json );
				} catch ( \Exception $e ) {
					// If we can't fetch from Manager, output minimal info
					$output->write( json_encode( [
						'test_run_id' => $test_run_id,
						'status'      => $exit_status === Command::SUCCESS ? 'success' : 'failed',
						'error'       => 'Could not fetch full test report from Manager',
					] ) );
				}
			} else {
				// No test_run_id available (no SUT), output minimal info
				$output->write( json_encode( [
					'status'  => $exit_status === Command::SUCCESS ? 'success' : 'failed',
					'message' => 'No test run ID available (test run without SUT)',
				] ) );
			}
		} else {
			// Normal output - show summary
			$orchestrator->summary( $summary_data );
		}

		// Always try to save debug.log to artifacts directory for inspection
		try {
			$docker         = App::make( Docker::class );
			$debug_log_path = $artifacts_dir . '/wordpress-debug.log';
			$docker->copy_from_docker(
				$env_info,
				'/var/www/html/wp-content/debug.log',
				$debug_log_path
			);
			if ( file_exists( $debug_log_path ) && filesize( $debug_log_path ) > 0 ) {
				$output->writeln( '<info>✓ WordPress debug log saved to artifacts</info>' );
			}
		} catch ( \RuntimeException $e ) {
			// Debug log might not exist if no errors occurred - this is normal.
			unset( $e );
		}

		// Save run information for qit ai:context failed-e2e command
		$this->save_run_info( $env_info, $test_packages, $exit_status, $artifacts_dir, $report_url ?? null );

		return $exit_status;
	}

	/**
	 * Save run information for the qit ai:context failed-e2e command.
	 *
	 * This creates a JSON file with all the information an AI agent would need to debug a test failure:
	 * - Environment configuration to understand the test context
	 * - Test package locations to examine the actual test code
	 * - Artifacts directory to find logs, screenshots, and reports
	 * - Pre-formatted debug commands that can be executed directly
	 *
	 * Example scenario for an AI agent:
	 * 1. AI runs: qit run:e2e --plugin=my-plugin
	 * 2. Tests fail with "1 failed" in the summary
	 * 3. AI sees "Agentic AI: qit ai:context failed-e2e" in the output
	 * 4. AI runs: qit ai:context failed-e2e
	 * 5. AI receives JSON with:
	 *    - WordPress version that was tested (e.g., "6.7-RC1")
	 *    - Failed test package path to read the test code
	 *    - Artifacts directory with screenshots of the failure
	 *    - Command to re-run the specific test: "qit env:source qitenv123 && npx playwright test"
	 * 6. AI can now analyze the failure, read logs, and provide specific fixes
	 *
	 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info Environment information.
	 * @param array<string,array{path?:string}>                $test_packages Test packages that were run.
	 * @param int                                              $exit_status Exit status of the test run.
	 * @param string                                           $artifacts_dir Path to artifacts directory.
	 * @param string|null                                      $report_url Remote report URL if available.
	 */
	private function save_run_info( \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info, array $test_packages, int $exit_status, string $artifacts_dir, ?string $report_url ): void {
		$run_id = uniqid( 'run-', true );

		// Prepare test packages info with status
		$packages_info = [];
		foreach ( $test_packages as $pkg_id => $meta ) {
			$packages_info[] = [
				'id'     => $pkg_id,
				'path'   => $meta['path'] ?? null,
				'status' => 'completed', // We don't track individual package status currently
			];
		}

		// Prepare debug commands
		$debug_commands = [];

		// Command to view HTML report
		$final_html_report = $artifacts_dir . '/final/html-report/index.html';
		if ( file_exists( $final_html_report ) ) {
			$debug_commands[] = [
				'description' => 'View HTML test report',
				'command'     => sprintf( 'npx playwright show-report "%s"', dirname( $final_html_report ) ),
			];
		}

		// Command to browse artifacts
		$debug_commands[] = [
			'description' => 'Browse test artifacts',
			'command'     => sprintf( 'ls -la "%s"', $artifacts_dir ),
		];

		// Command to re-run tests with same environment
		$debug_commands[] = [
			'description' => 'Re-run tests with same environment',
			'command'     => sprintf( 'qit env:source %s && npx playwright test', $env_info->env_id ),
		];

		// Build the run info data
		$run_info = [
			'run_id'         => $run_id,
			'timestamp'      => gmdate( 'c' ),
			'status'         => $exit_status === Command::SUCCESS ? 'passed' : 'failed',
			'environment'    => [
				'id'                  => $env_info->env_id,
				'url'                 => $env_info->site_url,
				'wordpress_version'   => $env_info->wordpress_version,
				'php_version'         => $env_info->php_version,
				'woocommerce_version' => $env_info->woocommerce_version ?: null,
				'sut'                 => isset( $env_info->sut ) ? [
					'slug' => $env_info->sut['slug'] ?? null,
					'id'   => $env_info->sut['id'] ?? null,
					'type' => $env_info->sut['type'] ?? 'plugin',
				] : null,
				'plugins'             => array_map( function ( Extension $plugin ) {
					return [
						'slug'    => $plugin->slug,
						'version' => $plugin->version,
					];
				}, $env_info->plugins ),
				'themes'              => array_map( function ( Extension $theme ) {
					return [
						'slug'    => $theme->slug,
						'version' => $theme->version,
					];
				}, $env_info->themes ),
			],
			'test_packages'  => $packages_info,
			'artifacts'      => [
				'directory' => $artifacts_dir,
				'reports'   => [],
			],
			'debug_commands' => $debug_commands,
		];

		// Add report locations
		if ( file_exists( $final_html_report ) ) {
			$run_info['artifacts']['reports'][] = [
				'type' => 'html',
				'path' => dirname( $final_html_report ),
			];
		}

		$ctrf_report = $artifacts_dir . '/final/ctrf/ctrf-report.json';
		if ( file_exists( $ctrf_report ) ) {
			$run_info['artifacts']['reports'][] = [
				'type' => 'ctrf',
				'path' => $ctrf_report,
			];
		}

		$allure_results = $artifacts_dir . '/final/allure-results';
		if ( is_dir( $allure_results ) ) {
			$run_info['artifacts']['reports'][] = [
				'type' => 'allure',
				'path' => $allure_results,
			];
		}

		// Add debug log if it exists
		$debug_log = $artifacts_dir . '/wordpress-debug.log';
		if ( file_exists( $debug_log ) ) {
			$run_info['artifacts']['reports'][] = [
				'type' => 'debug_log',
				'path' => $debug_log,
			];
		}

		// Add remote report URL if available
		if ( $report_url ) {
			$run_info['remote_report'] = $report_url;
		}

		// Save to last-run.json
		$last_run_file = Config::get_qit_dir() . '/last-run.json';
		file_put_contents( $last_run_file, json_encode( $run_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		// Also save with run ID for history
		$runs_dir = Config::get_qit_dir() . '/runs';
		if ( ! is_dir( $runs_dir ) ) {
			mkdir( $runs_dir, 0755, true );
		}
		$run_file = $runs_dir . '/' . $run_id . '.json';
		file_put_contents( $run_file, json_encode( $run_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		// Clean up old run files (keep last 10)
		$run_files = glob( $runs_dir . '/*.json' );
		if ( count( $run_files ) > 10 ) {
			// Sort by modification time
			usort( $run_files, function ( $a, $b ) {
				return filemtime( $a ) - filemtime( $b );
			});
			// Remove oldest files
			$to_remove = count( $run_files ) - 10;
			for ( $i = 0; $i < $to_remove; $i++ ) {
				unlink( $run_files[ $i ] );
			}
		}
	}


	/**
	 * Clean test package results based on manifest declarations
	 *
	 * @param string                                          $package_path Path to the test package.
	 * @param \QIT_CLI\PreCommand\Objects\TestPackageManifest $manifest Parsed manifest.
	 *
	 * @throws \RuntimeException On cleanup failures.
	 */
	private function cleanup_test_package_results( string $package_path, \QIT_CLI\PreCommand\Objects\TestPackageManifest $manifest ): void {
		$results = $manifest->get_test_results();

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
	 * Validate version consistency for subpackages from the same parent.
	 *
	 * @param array<string,array{manifest:TestPackageManifest,path:?string,metadata:array<string,mixed>}> $test_packages
	 * @throws \RuntimeException If version mismatch is detected.
	 */
	private function validate_subpackage_versions( array $test_packages ): void {
		$parent_versions = [];

		foreach ( $test_packages as $pkg_id => $meta ) {
			$manifest = $meta['manifest'];

			// Check if this is a subpackage
			if ( $manifest->is_subpackage() ) {
				$parent = $manifest->get_parent_package();

				// Extract version from package ID (format: namespace/name:version)
				$version = 'latest'; // Default
				if ( str_contains( $pkg_id, ':' ) ) {
					[ , $version ] = explode( ':', $pkg_id, 2 );
				}

				// Check for version consistency
				if ( isset( $parent_versions[ $parent ] ) ) {
					if ( $parent_versions[ $parent ] !== $version ) {
						throw new \RuntimeException(
							sprintf(
								'Cannot mix versions of subpackages from %s. Found versions: %s and %s. All subpackages from the same parent must use the same version.',
								$parent,
								$parent_versions[ $parent ],
								$version
							)
						);
					}
				} else {
					$parent_versions[ $parent ] = $version;
				}
			}
		}
	}


	private function handle_termination(): void {
		register_shutdown_function( static function () {
			static::shutdown_test_run();
		} );

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function ( $signo ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
				echo "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
				echo "⚠️  Test interrupted by user (Ctrl+C)\n";
				echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

				// Mark that we were interrupted
				App::setVar( 'qit_test_interrupted', true );

				// Forward SIGINT to Playwright and wait for graceful shutdown
				$current_process = App::getVar( 'qit_current_test_process' );
				if ( $current_process instanceof Process ) {
					try {
						// Send SIGINT to Playwright
						$current_process->signal( SIGINT );

						// Wait for Playwright to finish gracefully (up to 30 seconds)
						// This allows Playwright to show failure details and generate reports
						$timeout = 30;
						$start   = time();
						while ( $current_process->isRunning() && ( time() - $start ) < $timeout ) {
							usleep( 100000 ); // 0.1 second
						}

						// If still running after timeout, force stop
						if ( $current_process->isRunning() ) {
							$current_process->stop( 0 );
						}
					} catch ( \Exception $e ) {
						// Ignore errors during process termination
						unset( $e ); // Mark as intentionally unused
					}
				}

				// Playwright has finished, now show our additional info
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

		// Only show report information if we're in an abnormal shutdown
		// (normal flow already shows this information)
		$show_summary = App::getVar( 'qit_test_interrupted', false );

		// Show report information before shutting down (skip in JSON mode)
		$artifacts_dir = App::getVar( 'qit_test_artifacts_dir' );
		if ( $show_summary && ! empty( $artifacts_dir ) && is_dir( $artifacts_dir ) && ! App::getVar( 'QIT_JSON_MODE' ) ) {
			// Don't try to generate/merge reports on interrupt - show raw reports instead
			// Wait a moment for any final output from Playwright
			usleep( 500000 ); // 0.5 seconds

			echo "\n";
			echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
			echo "📋 Test Information\n";
			echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

			// Just show where the test packages were running
			$test_packages = App::getVar( 'qit_test_packages' );
			if ( ! empty( $test_packages ) ) {
				echo "\nTest package location:\n";
				foreach ( $test_packages as $pkg_id => $meta ) {
					if ( isset( $meta['path'] ) && is_dir( $meta['path'] ) ) {
						echo '  ' . $meta['path'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}

				echo "\nTest interrupted before completion - no post-processing performed.\n";
				echo "You can check for any partial results in the test package directory above.\n";
			}

			echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
		}

		$env_to_shutdown = App::getVar( 'env_to_shutdown' );

		// Show environment shutdown message (only in non-JSON mode and when actually shutting down)
		if ( ! App::getVar( 'QIT_JSON_MODE' ) && ! empty( $env_to_shutdown ) ) {
			echo "\nShutting down environment...\n";
		}

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
		// Set up the DI container variable for environment shutdown.
		// When --keep-env is set, skip registering for shutdown so the environment stays alive for debugging.
		if ( $input->getOption( 'keep-env' ) ) {
			App::setVar( 'env_to_shutdown', '' );
		} else {
			App::setVar( 'env_to_shutdown', $env_info->env_id );
		}
	}

	/**
	 * Run test packages using manifest-based approach with PackagePhaseRunner.
	 *
	 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info The environment information.
	 * @param array<string,mixed>                              $test_packages The test packages to run.
	 * @param SymfonyStyle                                     $io The IO interface.
	 * @param array<string>                                    $passthrough_args Arguments to pass to test framework after --.
	 * @param array<string,string>                             $parsed_env_vars Parsed environment variables passed from CLI/env files.
	 * @param array<string>                                    $passthrough_targets Explicit packages to receive passthrough_args.
	 *
	 * @return array{int, \QIT_CLI\Environment\PackageOrchestrator, string} Returns [exit_status, orchestrator, artifacts_dir].
	 */
	protected function runTestPackages( \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info, array $test_packages, SymfonyStyle $io, array $passthrough_args = [], array $parsed_env_vars = [], array $passthrough_targets = [] ): array {
		// Create orchestrator early so it's available in catch/finally blocks
		$ctrf_validator = App::make( \QIT_CLI\Environment\CTRFValidator::class );
		$orchestrator   = new \QIT_CLI\Environment\PackageOrchestrator( $io, $ctrf_validator );

		// Create EnvironmentManager for centralized env var and secret handling
		$env_manager = new \QIT_CLI\Environment\EnvironmentManager();

		// Check if we have any packages at all
		if ( empty( $test_packages ) ) {
			$io->writeln( '<error>No test packages configured</error>' );
			return [ Command::FAILURE, $orchestrator, '' ];
		}

		// Collect all required secrets from test packages
		$all_required_secrets = [];
		$has_test_packages    = false;
		$has_any_packages     = false;

		foreach ( $test_packages as $pkg_id => $meta ) {
			// Get manifest from meta['manifest'] which is already loaded
			if ( ! isset( $meta['manifest'] ) ) {
				continue;
			}

			$manifest = $meta['manifest'];
			if ( $manifest instanceof \QIT_CLI\PreCommand\Objects\TestPackageManifest ) {
				$has_any_packages = true;

				// Check for secrets
				$requires = $manifest->get_requires();
				if ( ! empty( $requires['secrets'] ) ) {
					$all_required_secrets = array_merge( $all_required_secrets, $requires['secrets'] );
				}

				// Check if this is a test package (has run phase)
				if ( $manifest->has_phase( 'run' ) ) {
					$has_test_packages = true;
				}
			}
		}

		// Initialize EnvironmentManager with all env vars and validate secrets
		if ( ! empty( $all_required_secrets ) ) {
			$all_required_secrets = array_unique( $all_required_secrets );
		}

		try {
			// Use the parsed env vars passed from execute method
			$cli_env_vars = $parsed_env_vars;

			// Initialize the environment manager with CLI vars and required secrets
			$env_manager->initialize( $cli_env_vars, [], $all_required_secrets );

			if ( ! empty( $all_required_secrets ) ) {
				$io->writeln( '<info>✓ All required secrets validated</info>' );
			}
		} catch ( \RuntimeException $e ) {
			$io->writeln( '<error>' . $e->getMessage() . '</error>' );
			return [ Command::FAILURE, $orchestrator, '' ];
		}

		// Set environment manager on orchestrator for redaction and env distribution
		$orchestrator->set_environment_manager( $env_manager );

		// Also set environment manager on Docker for proper secret handling
		$docker = App::make( \QIT_CLI\Environment\Docker::class );
		$docker->set_environment_manager( $env_manager );

		// Check if we have valid packages
		if ( ! $has_any_packages ) {
			$io->writeln( '<error>No valid test packages found</error>' );
			return [ Command::FAILURE, $orchestrator, '' ];
		}

		// Check if all packages are utility packages (no run phases)
		// run:e2e requires at least one test package with a run phase
		if ( ! $has_test_packages ) {
			$io->writeln( '<error>No test packages with run phase found. All packages are utility packages.</error>' );
			$io->writeln( '<comment>Use "env:up --global-setup" if you only need to set up an environment.</comment>' );
			return [ Command::FAILURE, $orchestrator, '' ];
		}

		// Use unique artifacts directory per run to avoid mixing results
		$run_id                = uniqid( 'run-', true );
		$artifacts_dir         = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $env_info->env_id . '-' . $run_id;
		$normal_flow_completed = false;

		// Create fresh artifacts directory for this run
		if ( ! is_dir( $artifacts_dir ) ) {
			mkdir( $artifacts_dir, 0755, true );
		}

		try {
			$total_executed  = 0;
			$failed_packages = [];

			// Store in DI container for signal handler access
			App::setVar( 'qit_test_artifacts_dir', $artifacts_dir );

			// Get global setup package IDs to skip them (they only run globalSetup)
			// Utility packages are those WITHOUT a run phase
			$utility_package_ids = [];
			foreach ( $test_packages as $pkg_id => $meta ) {
				$manifest = $meta['manifest'] ?? null;
				if ( $manifest && $manifest instanceof \QIT_CLI\PreCommand\Objects\TestPackageManifest && $manifest->is_utility_package() ) {
					$utility_package_ids[] = $pkg_id;
				}
			}
			$global_setup_package_ids = $utility_package_ids; // For backwards compatibility

			$io->section( 'Running Test Packages' );

			// Count only test packages (exclude utility packages)
			$test_package_count = count( array_diff( array_keys( $test_packages ), $global_setup_package_ids ) );
			$orchestrator->start( $env_info->env_id, $test_package_count );

			// Store test packages in DI container for signal handler access
			App::setVar( 'qit_test_packages', $test_packages );

			// Reset tracking for this test run
			$this->result_collector->reset_tracking();
			App::setVar( 'skip_allure_upload', false );

			// Run globalSetup phase with runtime deduplication
			$orchestrator->global_setup_start();

			// Track executed commands to avoid duplicates
			$executed_commands = [];
			$total_commands    = 0;
			$skipped_commands  = 0;

			// Run globalSetup for each package, skipping duplicates
			foreach ( $test_packages as $pkg_id => $meta ) {
				$manifest = $meta['manifest'] ?? null;
				if ( ! $manifest ) {
					continue;
				}

				$phases                = $manifest->get_phases();
				$global_setup_commands = $phases['globalSetup'] ?? [];

				if ( empty( $global_setup_commands ) ) {
					continue;
				}

				$package_path = $meta['path'] ?? '';

				foreach ( $global_setup_commands as $command ) {
					++$total_commands;

					// Hash includes package_path so different packages with the same
					// relative script name (e.g. ./bootstrap/global-setup.sh) don't
					// collide. Subpackages from the same parent share the same path,
					// so their inherited commands still dedup correctly.
					$command_hash = md5( $package_path . '::' . json_encode( $command ) );

					if ( isset( $executed_commands[ $command_hash ] ) ) {
						// Command already executed, skip with info message
						++$skipped_commands;
						$orchestrator->global_setup_message(
							sprintf( '⏭ Skipping duplicate command (already executed by %s)',
							$executed_commands[ $command_hash ] )
						);
						continue;
					}

					// Mark as executed and run the command
					$executed_commands[ $command_hash ] = $pkg_id;

					// Run the command using package phase runner
					$temp_manifest_data = [
						'package' => $pkg_id,
						'test'    => [
							'phases' => [
								'globalSetup' => [ $command ],
							],
						],
					];
					$temp_manifest      = new TestPackageManifest( $temp_manifest_data );

					$this->package_phase_runner->run_phase(
						$env_info,
						'globalSetup',
						$pkg_id,
						$package_path,
						$artifacts_dir,
						$orchestrator,
						[],
						$temp_manifest
					);
				}
			}

			if ( $total_commands === 0 ) {
				$orchestrator->global_setup_message( 'No globalSetup commands to run.' );
			} elseif ( $skipped_commands > 0 ) {
				$orchestrator->global_setup_message(
					sprintf( 'Executed %d unique commands, skipped %d duplicates',
					count( $executed_commands ), $skipped_commands )
				);
			}

			// Export baseline database snapshot only if we have multiple test packages (excluding utilities)
			$has_multiple_packages = $test_package_count > 1;
			if ( $has_multiple_packages ) {
				$orchestrator->global_setup_message( 'Exporting baseline database snapshot...' );
				$docker = App::make( Docker::class );
				$docker->run_inside_docker( $env_info, [ 'wp', 'db', 'export', '/qit/snapshot.sql', '--defaults' ] );
			}
			$orchestrator->global_setup_end();

			$package_index         = 0;
			$is_first_package      = true;
			$package_display_names = []; // Track display names for error messages
			foreach ( $test_packages as $pkg_id => $meta ) {
				// Skip utility packages (they only contribute to globalSetup, no run phase)
				if ( in_array( $pkg_id, $global_setup_package_ids, true ) ) {
					$io->writeln( "<comment>Skipping {$pkg_id} (utility package - globalSetup already executed)</comment>" );
					continue;
				}

				// Increment package index for non-global-setup packages
				++$package_index;

				$package_path = $meta['path'] ?? '';
				if ( empty( $package_path ) || ! is_dir( $package_path ) ) {
					$io->error( "Invalid package path for {$pkg_id}: {$package_path}" );
					$failed_packages[] = $pkg_id;
					continue;
				}

				// Get the manifest and metadata from the downloaded packages
				if ( ! isset( $meta['manifest'] ) ) {
					throw new \RuntimeException( "No manifest found for package {$pkg_id}" );
				}

				$manifest     = $meta['manifest'];
				$metadata     = $meta['metadata'] ?? [];
				$package_path = $meta['path'];

				// Build display name from manifest - this is the canonical package identifier
				$display_name = $manifest->get_package_id();

				// Version MUST be set in metadata - either 'local' or a specific version
				if ( ! isset( $metadata['version'] ) ) {
					throw new \RuntimeException( "Package {$pkg_id} is missing version information in metadata" );
				}

				$display_name                    .= ':' . $metadata['version'];
				$package_display_names[ $pkg_id ] = $display_name; // Store for error messages

				// Store manifest in test_packages_metadata for later use
				if ( isset( $env_info->test_packages_metadata[ $pkg_id ] ) ) {
					$env_info->test_packages_metadata[ $pkg_id ]['manifest'] = $manifest;
				}

				// Restore full isolation (database + browser state) before each non-first package
				if ( ! $is_first_package ) {
					$orchestrator->isolation_restore_start();
					$docker = App::make( Docker::class );

					// 1. Clear browser state: cached cookies, auth tokens, Playwright state
					$orchestrator->isolation_restore_message( 'Clearing browser state...' );
					try {
						$docker->run_inside_docker( $env_info, [ 'rm', '-f', '/tmp/qit_env_helper.json' ] );
						$docker->run_inside_docker( $env_info, [ 'sh', '-c', 'rm -rf /qit/tests/e2e/state/*' ] );
						$orchestrator->isolation_restore_message( '✓ Browser state cleared' );
					} catch ( \Exception $e ) {
						$orchestrator->isolation_restore_message( '✗ Browser state cleanup failed: ' . $e->getMessage() );
						$orchestrator->isolation_restore_end();
						throw new \RuntimeException( 'Infrastructure failure: Failed to clear browser state before package ' . $pkg_id . ': ' . $e->getMessage(), 3 );
					}

					// 2. Restore database snapshot
					$orchestrator->isolation_restore_message( 'Restoring database snapshot...' );
					try {
						$docker->run_inside_docker( $env_info, [ 'wp', 'db', 'import', '/qit/snapshot.sql', '--defaults' ] );
						$orchestrator->isolation_restore_message( '✓ Database snapshot restored' );
					} catch ( \Exception $e ) {
						$orchestrator->isolation_restore_message( '✗ Database restore failed' );
						$orchestrator->isolation_restore_end();
						throw new \RuntimeException( 'Infrastructure failure: Failed to restore database snapshot before package ' . $pkg_id . ': ' . $e->getMessage(), 3 );
					}

					$orchestrator->isolation_restore_end();
				}

				// Determine package type from metadata
				$package_type = ( isset( $metadata['remote'] ) && $metadata['remote'] === false ) ? 'Local Package' : 'Remote Package';
				$orchestrator->package_start( $package_index, $display_name, $package_type );

				try {
					// Manifest was already loaded above and stored in metadata
					if ( ! isset( $env_info->test_packages_metadata[ $pkg_id ]['manifest'] ) ) {
						throw new \RuntimeException( "Manifest not loaded for package {$pkg_id}" );
					}
					// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset - We check it exists above
					$manifest = $env_info->test_packages_metadata[ $pkg_id ]['manifest'];

					// Clean previous test results before running
					$this->cleanup_test_package_results( $package_path, $manifest );

					// Run full lifecycle for test packages: setup -> run -> teardown
					$orchestrator->phase_start( 'setup' );
					$setup_count = $this->package_phase_runner->run_phase( $env_info, 'setup', $pkg_id, $package_path, $artifacts_dir, $orchestrator, [], $manifest );
					if ( $setup_count > 0 && $manifest->has_results() ) {
						$this->result_collector->collect( $env_info, $pkg_id, $manifest, $artifacts_dir, 'setup' );
					}

					// Run phase with CTRF collection even on test failures
					if ( $manifest->has_phase( 'run' ) ) {
						try {
							$orchestrator->phase_start( 'run' );

							// Determine if this package should receive passthrough_args
							$should_pass_args     = false;
							$has_explicit_targets = ! empty( $passthrough_targets );

							if ( $has_explicit_targets ) {
								// Explicit targets specified - check if this package matches
								// Support multiple ways to identify the package
								$package_identifiers = [
									$pkg_id,                           // Internal ID
									$package_path,                      // Full path
									$manifest->get_package_id(),       // Package ID from manifest
									$display_name,                      // Full display name with version
								];

								foreach ( $package_identifiers as $identifier ) {
									if ( in_array( $identifier, $passthrough_targets, true ) ) {
										$should_pass_args = true;
										break;
									}
								}
							} else {
								// No explicit targets - check for UI mode or use default behavior (local packages only)
								$ui_mode_enabled = App::getVar( 'ui_mode_enabled', false );

								if ( $ui_mode_enabled ) {
									// UI mode: pass args to all packages so Playwright UI works
									$should_pass_args = true;
								} else {
									// Check if package is local based on metadata
									// Metadata has 'remote' => false for local packages
									$is_local = isset( $metadata['remote'] ) && $metadata['remote'] === false;

									$should_pass_args = $is_local;
								}
							}

							$package_passthrough_args = $should_pass_args ? $passthrough_args : [];
							$run_count                = $this->package_phase_runner->run_phase( $env_info, 'run', $pkg_id, $package_path, $artifacts_dir, $orchestrator, $package_passthrough_args, $manifest );
							// Normal CTRF collection for successful runs
							if ( $manifest->has_results() ) {
								$this->result_collector->collect( $env_info, $pkg_id, $manifest, $artifacts_dir, 'run' );
							}
						} catch ( \RuntimeException $e ) {
							// Collect CTRF even if tests failed (exit code 1 from test failures)
							if ( $manifest->has_results() ) {
								try {
									$this->result_collector->collect( $env_info, $pkg_id, $manifest, $artifacts_dir, 'run' );
								} catch ( \Throwable $collector_err ) {
									$error_msg = $collector_err->getMessage();
									$io->writeln( "<error>Result collection failed: {$error_msg}</error>" );

									// Check if it's a blob reporter error
									if ( strpos( $error_msg, 'blob-dir' ) !== false ) {
										$io->writeln( '<error>Test terminated abnormally - Blob reporter output is required</error>' );
										$io->writeln( '' );
										$io->writeln( '<comment>To fix this issue, ensure your test package generates blob reports:</comment>' );
										$io->writeln( '  1. Configure blob reporter in playwright.config.js:' );
										$io->writeln( '     reporter: [' );
										$io->writeln( "       ['list']," );
										$io->writeln( "       ['blob', {outputDir: './blob-report'}]," );
										$io->writeln( "       ['playwright-ctrf-json-reporter', {outputDir: './results', outputFile: 'ctrf.json'}]" );
										$io->writeln( '     ]' );
										$io->writeln( '  2. Update qit-test.json to point to both report directories:' );
										$io->writeln( '     "results": {' );
										$io->writeln( '       "ctrf-json": "./results/ctrf.json",' );
										$io->writeln( '       "blob-dir": "./blob-report"' );
										$io->writeln( '     }' );
									} elseif ( strpos( $error_msg, 'ctrf-json' ) !== false ) {
										$io->writeln( '<error>Test terminated abnormally - CTRF output is required</error>' );
										$io->writeln( '' );
										$io->writeln( '<comment>To fix this issue, ensure your test package generates CTRF reports:</comment>' );
										$io->writeln( '  1. Install the CTRF reporter: npm install --save-dev playwright-ctrf-json-reporter' );
										$io->writeln( '  2. Configure it in playwright.config.js:' );
										$io->writeln( "     reporter: [['playwright-ctrf-json-reporter', {outputDir: './results', outputFile: 'ctrf.json'}]]" );
										$io->writeln( '  3. Update qit-test.json to point to the CTRF file:' );
										$io->writeln( '     "results": {"ctrf-json": "./results/ctrf.json"}' );
									}
									throw new \RuntimeException( 'Test failed to produce required output: ' . $collector_err->getMessage() );
								}
							}
							// Re-throw to maintain failure status
							throw $e;
						}
					} else {
						// No run phase - this is a utility package
						$run_count = 0;
					}

					// Validate that packages with run phase produce test results
					// Note: If a package has a run phase, it MUST have results defined per schema
					if ( $manifest->has_phase( 'run' ) && $manifest->has_results() ) {
						$ctrf_path = $artifacts_dir . '/ctrf/' . ltrim( str_replace( [ '/', ':' ], '_', $pkg_id ), '._' ) . '.json';
						if ( file_exists( $ctrf_path ) ) {
							$ctrf_data  = json_decode( file_get_contents( $ctrf_path ), true );
							$test_count = 0;
							if ( isset( $ctrf_data['results']['tests'] ) && is_array( $ctrf_data['results']['tests'] ) ) {
								$test_count = count( $ctrf_data['results']['tests'] );
							}

							if ( $test_count === 0 ) {
								throw new \RuntimeException(
									"Package \"{$display_name}\" declared a run phase but produced 0 test results.\n" .
									'  • Either add a real test, or remove the run phase if this is a pure setup package.'
								);
							}
						}
					}

					$orchestrator->phase_start( 'teardown' );
					$teardown_count = $this->package_phase_runner->run_phase( $env_info, 'teardown', $pkg_id, $package_path, $artifacts_dir, $orchestrator, [], $manifest );
					// Note: teardown phase is for cleanup only - no result collection needed

					$package_total   = $setup_count + $run_count + $teardown_count;
					$total_executed += $package_total;

					// Command count is tracked internally, no need to display here
					// End package in orchestrator
					$orchestrator->package_end();

					// Mark that we've processed the first package
					$is_first_package = false;

				} catch ( \Exception $e ) {
					// Always show the actual error for debugging
					$io->writeln( '' );
					$io->writeln( '<error>Package failed: ' . $e->getMessage() . '</error>' );

					// Track the failed package using its display name
					$failed_packages[] = $package_display_names[ $pkg_id ] ?? $pkg_id;

					// End package with failure status
					$orchestrator->package_end();

					// Still mark as processed to maintain the sequence for subsequent packages
					$is_first_package = false;
				}
			}

			// Reports merging will happen after test_runner completes

			// Store artifacts directory in env_info for later use by Manager
			/** @phpstan-ignore-next-line property.notFound */
			$env_info->artifacts_dir = $artifacts_dir;

			// Mark that normal flow completed successfully
			$normal_flow_completed = true;

			// Return appropriate exit code
			if ( empty( $failed_packages ) ) {
				return [ Command::SUCCESS, $orchestrator, $artifacts_dir ];
			} else {
				// Don't show redundant error message - already shown in output
				return [ Command::FAILURE, $orchestrator, $artifacts_dir ];
			}
		} catch ( \RuntimeException $e ) {
			// Handle infrastructure failures (code 3)
			if ( $e->getCode() === 3 ) {
				$io->error( $e->getMessage() );

				return [ 3, $orchestrator, $artifacts_dir ];
			}
			// Re-throw other RuntimeExceptions
			throw $e;
		} finally {
			// Run globalTeardown phase with runtime deduplication
			$orchestrator->global_teardown_start();

			// Track executed commands to avoid duplicates
			$executed_teardown_commands = [];
			$total_teardown_commands    = 0;
			$skipped_teardown_commands  = 0;

			// Run globalTeardown for each package, skipping duplicates
			foreach ( $test_packages as $pkg_id => $meta ) {
				$manifest = $meta['manifest'] ?? null;
				if ( ! $manifest ) {
					continue;
				}

				$phases                   = $manifest->get_phases();
				$global_teardown_commands = $phases['globalTeardown'] ?? [];

				if ( empty( $global_teardown_commands ) ) {
					continue;
				}

				$package_path = $meta['path'] ?? '';

				foreach ( $global_teardown_commands as $command ) {
					++$total_teardown_commands;

					// Hash includes package_path so different packages with the same
					// relative script name don't collide. Subpackages from the same
					// parent share the same path, so their commands still dedup.
					$command_hash = md5( $package_path . '::' . json_encode( $command ) );

					if ( isset( $executed_teardown_commands[ $command_hash ] ) ) {
						// Command already executed, skip with info message
						++$skipped_teardown_commands;
						$orchestrator->global_teardown_message(
							sprintf( '⏭ Skipping duplicate command (already executed by %s)',
							$executed_teardown_commands[ $command_hash ] )
						);
						continue;
					}

					// Mark as executed and run the command
					$executed_teardown_commands[ $command_hash ] = $pkg_id;

					try {
						// Run the command using package phase runner
						$temp_manifest_data = [
							'package' => $pkg_id,
							'test'    => [
								'phases' => [
									'globalTeardown' => [ $command ],
								],
							],
						];
						$temp_manifest      = new TestPackageManifest( $temp_manifest_data );

						$this->package_phase_runner->run_phase(
							$env_info,
							'globalTeardown',
							$pkg_id,
							$package_path,
							null, // No artifacts dir for teardown
							$orchestrator,
							[],
							$temp_manifest
						);
					} catch ( \Throwable $e ) {
						// Log but don't fail the entire test run if globalTeardown fails
						$orchestrator->global_teardown_message(
							sprintf( '⚠ Command failed: %s', $e->getMessage() )
						);
					}
				}
			}

			if ( $total_teardown_commands === 0 ) {
				$orchestrator->global_teardown_message( 'No globalTeardown commands to run.' );
			} elseif ( $skipped_teardown_commands > 0 ) {
				$orchestrator->global_teardown_message(
					sprintf( 'Executed %d unique commands, skipped %d duplicates',
					count( $executed_teardown_commands ), $skipped_teardown_commands )
				);
			}

			// Close global teardown section
			$orchestrator->global_teardown_end();

			// Only try to generate reports if the normal flow didn't complete (e.g., on exception)
			if ( ! $normal_flow_completed && is_dir( $artifacts_dir ) ) {
				$io->writeln( "\n<info>Test Artifacts:</info>" );
				$io->writeln( "Location: <comment>{$artifacts_dir}</comment>" );

				try {
					// Try to merge any partial results
					$this->result_collector->merge_ctrf( $artifacts_dir, $io, $orchestrator );
					$this->result_collector->merge_blob( $artifacts_dir, $io, $orchestrator );

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
						$io->writeln( '  <comment>qit report</comment>' );
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
	 * Add SUT to env:up options.
	 *
	 * @param InputInterface $input
	 * @param array<mixed>   $env_up_options
	 * @param string         $woo_extension_slug
	 * @param string|null    $sut_type
	 *
	 * @return array<mixed>
	 */
	private function add_sut_to_env_up_options( InputInterface $input, array $env_up_options, string $woo_extension_slug, ?string $sut_type ): array {
		if ( ! $sut_type ) {
			$sut_type = 'plugin';
		}

		$key = ( $sut_type === 'theme' ) ? '--theme' : '--plugin';

		// Check if a custom source is provided via --zip or --source option
		$custom_source = $input->getOption( 'zip' ) ?: $input->getOption( 'source' );
		if ( $custom_source ) {
			// Append the custom source to the slug using @ separator
			// This accepts: local ZIP files, local directories, or URLs
			$woo_extension_slug = $woo_extension_slug . '@' . $custom_source;
		}

		// Add to env:up options
		if ( ! isset( $env_up_options[ $key ] ) ) {
			$env_up_options[ $key ] = [];
		}
		$env_up_options[ $key ][] = $woo_extension_slug;

		return $env_up_options;
	}

	/**
	 * Display environment summary similar to env:up output.
	 *
	 * @param OutputInterface                                  $output
	 * @param \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info
	 * @param array<string,array{path?:string}>                $test_packages
	 */
	private function renderEnvironmentSummary( OutputInterface $output, \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo $env_info, array $test_packages ): void {
		$output->writeln( '' );
		$output->writeln( sprintf( '<info>✅ Environment ready: %s</info>', $env_info->env_id ) );
		$output->writeln( '' );

		// URL and credentials
		$output->writeln( sprintf( '  URL:         %s', $env_info->site_url ) );
		$output->writeln( '  Credentials: admin/password' );

		// Stack information
		$stack_parts   = [];
		$stack_parts[] = sprintf( 'WordPress %s', $env_info->wordpress_version );
		$stack_parts[] = sprintf( 'PHP %s', $env_info->php_version );
		$output->writeln( sprintf( '  Stack:       %s', implode( ', ', $stack_parts ) ) );

		// SUT (System Under Test) if present
		if ( isset( $env_info->sut ) ) {
			$sut_type  = $env_info->sut['type'] ?? 'plugin';
			$sut_label = ucfirst( $sut_type ) . ' Under Test';
			$output->writeln( sprintf( '  %s: %s', str_pad( $sut_label, 11 ), $env_info->sut['slug'] ) );
		}

		// Plugins line (only if plugins exist)
		$plugin_names = [];
		foreach ( $env_info->plugins as $plugin ) {
			// Skip the SUT if it's a plugin
			if ( isset( $env_info->sut ) && $env_info->sut['type'] === 'plugin' && $plugin->slug === $env_info->sut['slug'] ) {
				continue;
			}
			if ( $plugin->slug === 'woocommerce' && $env_info->woocommerce_version ) {
				$plugin_names[] = sprintf( 'WooCommerce %s', $env_info->woocommerce_version );
			} elseif ( $plugin->slug !== 'woocommerce' ) {
				$plugin_names[] = $this->format_extension_name( $plugin->slug ) . ( $plugin->version ? ' ' . $plugin->version : '' );
			}
		}
		if ( ! empty( $plugin_names ) ) {
			$output->writeln( sprintf( '  Plugins:     %s', implode( ', ', $plugin_names ) ) );
		}

		// Theme line (only if non-default theme exists)
		$theme_names = [];
		foreach ( $env_info->themes as $theme ) {
			// Skip default themes and SUT if it's a theme
			if ( isset( $env_info->sut ) && $env_info->sut['type'] === 'theme' && $theme->slug === $env_info->sut['slug'] ) {
				continue;
			}
			if ( ! in_array( $theme->slug, [ 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo' ], true ) ) {
				$theme_names[] = $this->format_extension_name( $theme->slug ) . ( $theme->version ? ' ' . $theme->version : '' );
			}
		}
		if ( ! empty( $theme_names ) ) {
			$output->writeln( sprintf( '  Theme:       %s', implode( ', ', $theme_names ) ) );
		}

		// Test packages summary
		if ( ! empty( $test_packages ) ) {
			$package_count      = count( $test_packages );
			$global_setup_count = count( $env_info->global_setup_packages ?? [] );
			$test_count         = $package_count - $global_setup_count;

			if ( $test_count > 0 || $global_setup_count > 0 ) {
				$output->writeln( '' );
				if ( $test_count > 0 ) {
					$output->writeln( sprintf( '  Test Packages: %d package%s ready', $test_count, $test_count === 1 ? '' : 's' ) );
				}
				if ( $global_setup_count > 0 ) {
					$output->writeln( sprintf( '  Global Setup:  %d package%s completed', $global_setup_count, $global_setup_count === 1 ? '' : 's' ) );
				}
			}
		}

		$output->writeln( '' );
	}

	/**
	 * Format extension name for display.
	 *
	 * @param string $slug The extension slug.
	 * @return string Formatted name.
	 */
	private function format_extension_name( string $slug ): string {
		// Convert slug to title case
		$name = str_replace( [ '-', '_' ], ' ', $slug );
		return ucwords( $name );
	}
}
