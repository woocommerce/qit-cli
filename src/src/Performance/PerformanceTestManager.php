<?php

namespace QIT_CLI\Performance;

use QIT_CLI\Environment\EnvironmentRunner;
use QIT_CLI\Utils\LocalTestRunNotifier;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Performance\MetricAverager;
use QIT_CLI\Performance\Result\PerformanceTestResult;
use QIT_CLI\Performance\Runner\K6Runner;
use QIT_CLI\Environment\Environments\Environment;
use Symfony\Component\Console\Output\OutputInterface;

class PerformanceTestManager {
	/** @var K6Runner */
	private $k6_runner;

	/** @var OutputInterface|null */
	private $output;

	/** @var LocalTestRunNotifier */
	private $notifier;

	/** @var MetricAverager */
	private $metric_averager;

	/** @var int */
	private $test_iterations;

	/** @var EnvironmentRunner */
	private $environment_runner;

	/** @var \QIT_CLI\Environment\Environments\Performance\PerformanceEnvironment */
	private $performance_environment;

	/** @var array<string,mixed> */
	private $env_up_options;

	/** @var array<string,mixed>|null */
	private $notification_params;

	public function __construct( K6Runner $k6_runner, LocalTestRunNotifier $notifier, EnvironmentRunner $environment_runner, \QIT_CLI\Environment\Environments\Performance\PerformanceEnvironment $performance_environment ) {
		$this->k6_runner               = $k6_runner;
		$this->notifier                = $notifier;
		$this->environment_runner      = $environment_runner;
		$this->performance_environment = $performance_environment;
		$this->metric_averager         = new MetricAverager();
		$this->test_iterations         = 3; // Default to 3 iterations for stability.
	}

	public function set_output( OutputInterface $output ): void {
		$this->output = $output;
	}

	/**
	 * Set the number of iterations for performance tests.
	 *
	 * @param int $iterations Number of iterations (minimum 1, default 3).
	 */
	public function set_test_iterations( int $iterations ): void {
		$this->test_iterations = max( 1, $iterations );
	}

	/**
	 * Set environment up options for creating environments.
	 *
	 * @param array<string,mixed> $env_up_options Array of options for environment creation.
	 */
	public function set_env_up_options( array $env_up_options ): void {
		$this->env_up_options = $env_up_options;
	}

	/**
	 * Set notification parameters for test start notification.
	 *
	 * @param array<string,mixed> $notification_params Parameters for test start notification.
	 */
	public function set_notification_params( array $notification_params ): void {
		$this->notification_params = $notification_params;
	}

	/**
	 * Set the test run notifier instance.
	 *
	 * @param LocalTestRunNotifier $notifier The notifier instance.
	 */
	public function set_test_run_notifier( LocalTestRunNotifier $notifier ): void {
		$this->notifier = $notifier;
	}

	public function run_tests( PerformanceEnvInfo $env_info ): int {
		$baseline_result = null;
		$main_exit_code  = 0;

		// Create single environment with SUT installed.
		$this->output->writeln( '<comment>Setting up test environment...</comment>' );

		$extension_env_info = null;
		try {
			// Create environment with all plugins (including SUT).
			$extension_env_info = $this->create_environment( $this->env_up_options );
			$this->copy_test_config( $extension_env_info, $env_info );

			// Run baseline tests if enabled.
			if ( $env_info->run_baseline ) {
				$this->output->writeln( '<comment>Starting baseline tests...</comment>' );
				$baseline_result = $this->run_baseline_tests_same_env( $extension_env_info );

				if ( $baseline_result === null ) {
					$this->output->writeln( '<error>Baseline tests failed, continuing with extension tests.</error>' );
				} elseif ( $baseline_result->status === 'cancelled' ) {
					$this->output->writeln( '<error>Baseline tests were cancelled, cancelling entire test run.</error>' );
					return 143;
				} else {
					$this->output->writeln( '<comment>Baseline tests completed successfully.</comment>' );
				}

				// Reset DB to clean state before SUT tests.
				$this->reset_database_to_clean_state( $extension_env_info );
			}

			// Notify test started now that environment is ready.
			$this->notify_test_started_if_configured( $extension_env_info );

			// Run SUT tests.
			$this->output->writeln( '<comment>Proceeding to extension tests...</comment>' );
			$main_result    = $this->run_extension_tests_same_env( $extension_env_info );
			$main_exit_code = $main_result['exit_code'];

			// Combine results.
			$final_result = $main_result['test_result'];
			if ( $baseline_result !== null ) {
				$final_result = $this->combine_results( $main_result['test_result'], $baseline_result );
			}

			// Upload results.
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( '<comment>Uploading test results...</comment>' );
			}
			$this->notifier->notify_test_finished( $final_result );

			// Display summary.
			$this->display_results_summary( $final_result );

			if ( $this->output->isVerbose() ) {
				$this->output->writeln( sprintf( '[Verbose] Test artifacts directory: %s', $main_result['test_result']->get_results_dir() ) );
			}

			return $main_exit_code;

		} catch ( \Exception $e ) {
			$this->output->writeln( sprintf( '<error>Failed to run tests: %s</error>', $e->getMessage() ) );
			return 1;
		} finally {
			if ( $extension_env_info ) {
				try {
					$this->output->writeln( '<comment>Tearing down environment...</comment>' );
					Environment::down( $extension_env_info );
				} catch ( \Exception $e ) {
					$this->output->writeln( sprintf( '<comment>Warning: Failed to shut down environment: %s</comment>', $e->getMessage() ) );
				}
			}
		}
	}

	/**
	 * Combine baseline and main test results.
	 *
	 * @param PerformanceTestResult      $main_result The main test result.
	 * @param PerformanceTestResult|null $baseline_result The baseline test result.
	 * @return PerformanceTestResult The combined result.
	 */
	private function combine_results( PerformanceTestResult $main_result, ?PerformanceTestResult $baseline_result ): PerformanceTestResult {
		// Use the main result as the base.
		$combined_result = $main_result;

		// If we have baseline results, add them to the combined result.
		if ( $baseline_result ) {
			$combined_result->set_baseline_result( $baseline_result );

			// Note: The baseline result is stored for comparison by the compatibility dashboard.
			// Comparison metrics and scoring are calculated server-side.
		}

		return $combined_result;
	}


	/**
	 * Run test iterations for a given environment.
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test against.
	 * @param string             $test_type Test type for logging ('baseline' or 'extension').
	 * @param bool               $is_baseline Whether this is a baseline test.
	 * @return PerformanceTestResult[] Array of test results.
	 */
	private function run_test_iterations( PerformanceEnvInfo $env_info, string $test_type, bool $is_baseline ): array {
		$results = [];

		// Run tests multiple times for stability.
		// We reuse the same Docker environment but create nested result directories.
		for ( $i = 1; $i <= $this->test_iterations; $i++ ) {
			$this->output->writeln( sprintf( '<comment>Running %s test iteration %d/%d...</comment>', $test_type, $i, $this->test_iterations ) );

			$result = $this->run_single_iteration( $env_info, $test_type, $is_baseline, $i );

			if ( $result->status === 'cancelled' ) {
				return [ $result ];
			}

			$results[] = $result;
		}

		// Log completion.
		$this->output->writeln( sprintf( '<comment>%s tests completed (%d iterations averaged).</comment>', ucfirst( $test_type ), $this->test_iterations ) );

		return $results;
	}

	/**
	 * Run a single test iteration.
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test against.
	 * @param string             $test_type Test type for logging.
	 * @param bool               $is_baseline Whether this is a baseline test.
	 * @param int                $iteration_number Current iteration number.
	 * @return PerformanceTestResult Test result.
	 */
	private function run_single_iteration( PerformanceEnvInfo $env_info, string $test_type, bool $is_baseline, int $iteration_number ): PerformanceTestResult {
		// Create nested iteration directory.
		$iteration_env_info         = clone $env_info;
		$iteration_env_info->env_id = $env_info->env_id . "/iter{$iteration_number}";

		// Get result filenames from manifest before creating the result.
		$result_filenames = $this->k6_runner->get_result_filenames_from_manifest( $env_info );

		$test_result = new PerformanceTestResult( $iteration_env_info, $result_filenames );
		$test_result->set_baseline( $is_baseline );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( '<comment>Running %s iteration %d with tests for: %s</comment>', $test_type, $iteration_number, $env_info->sut['slug'] ?? 'unknown' ) );
		}

		// Run k6 test and handle result.
		$exit_code = $this->k6_runner->run_test( $env_info, $test_result );
		$test_result->add_metric( 'k6_exit_code', $exit_code );

		if ( $exit_code === 143 ) {
			$test_result->set_status( 'cancelled' );
			$this->output->writeln( sprintf( '<error>%s iteration %d was cancelled</error>', ucfirst( $test_type ), $iteration_number ) );
			return $test_result;
		}

		$test_result->set_status( 'completed' );
		$test_result->process_results();

		if ( $this->output->isVerbose() ) {
			$metrics_count = count( $test_result->get_metrics() );
			$this->output->writeln( sprintf( '<comment>%s iteration %d completed: %d metrics collected</comment>', ucfirst( $test_type ), $iteration_number, $metrics_count ) );
		}

		return $test_result;
	}

	/**
	 * Get the final exit code from multiple test results.
	 * Returns first non-zero exit code found, or 0 if all succeeded.
	 *
	 * @param PerformanceTestResult[] $results Array of test results.
	 */
	private function get_final_exit_code( array $results ): int {
		foreach ( $results as $result ) {
			$exit_code = $result->get_metrics()['k6_exit_code'] ?? 0;
			if ( $exit_code ) {
				return $exit_code;
			}
		}
		return 0;
	}

	/**
	 * Create a performance environment with test downloading enabled.
	 *
	 * @param array<string,mixed> $env_options Environment options.
	 * @return PerformanceEnvInfo The created environment info.
	 * @throws \RuntimeException If environment creation fails or returns wrong type.
	 */
	private function create_environment( array $env_options ): PerformanceEnvInfo {
		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		try {
			$env_info_raw = $this->environment_runner->run_environment( $env_options );

			if ( ! $env_info_raw instanceof PerformanceEnvInfo ) {
				throw new \RuntimeException( 'Expected PerformanceEnvInfo but got ' . get_class( $env_info_raw ) );
			}

			return $env_info_raw;
		} finally {
			putenv( 'QIT_UP_AND_TEST' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}
	}

	/**
	 * Reset database to clean state by re-importing the base data.
	 * Delegates to PerformanceEnvironment's generate_base_data() method.
	 *
	 * @param PerformanceEnvInfo $env_info The environment to reset.
	 */
	private function reset_database_to_clean_state( PerformanceEnvInfo $env_info ): void {
		$this->output->writeln( '<comment>Resetting database to clean state...</comment>' );

		// Initialize the performance environment instance with the current env_info.
		$this->performance_environment->init( $env_info );

		// Reuse the existing generate_base_data() method which imports the cached dump.
		$this->performance_environment->generate_base_data();
	}

	/**
	 * Run baseline performance tests in the same environment (SUT deactivated).
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test.
	 * @return PerformanceTestResult|null The baseline test result or null if failed.
	 */
	private function run_baseline_tests_same_env( PerformanceEnvInfo $env_info ): ?PerformanceTestResult {
		$sut_slug = $this->get_sut_slug_from_options();

		if ( $sut_slug ) {
			// Initialize performance environment and deactivate SUT plugin.
			$this->performance_environment->init( $env_info );
			$this->performance_environment->deactivate_sut_plugin( $sut_slug );
		} else {
			$this->output->writeln( '<comment>No SUT plugin found to deactivate for baseline</comment>' );
		}

		$this->output->writeln( '<comment>Running baseline tests...</comment>' );
		$results = $this->run_test_iterations( $env_info, 'baseline', true );

		if ( ! $results ) {
			return null;
		}

		foreach ( $results as $result ) {
			if ( $result->status === 'cancelled' ) {
				$this->output->writeln( '<comment>Baseline tests were cancelled, returning cancelled result</comment>' );
				return $result;
			}
		}

		return $this->metric_averager->average_test_results( $results, $env_info );
	}

	/**
	 * Run extension performance tests in the same environment (SUT activated).
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test.
	 * @return array{test_result: PerformanceTestResult, exit_code: int} The extension test result and exit code.
	 */
	private function run_extension_tests_same_env( PerformanceEnvInfo $env_info ): array {
		$sut_slug = $this->get_sut_slug_from_options();

		if ( $sut_slug ) {
			// Initialize performance environment and activate SUT plugin.
			$this->performance_environment->init( $env_info );
			$this->performance_environment->activate_sut_plugin( $sut_slug );
		} else {
			$this->output->writeln( '<comment>No SUT plugin found to activate</comment>' );
		}

		$this->output->writeln( '<comment>Running extension tests...</comment>' );
		$results = $this->run_test_iterations( $env_info, 'extension', false );

		if ( ! $results ) {
			$failed_result = new PerformanceTestResult( $env_info );
			$failed_result->set_status( 'failed' );
			return [
				'test_result' => $failed_result,
				'exit_code'   => 1,
			];
		}

		if ( count( $results ) === 1 && $results[0]->status === 'cancelled' ) {
			return [
				'test_result' => $results[0],
				'exit_code'   => 143,
			];
		}

		return [
			'test_result' => $this->metric_averager->average_test_results( $results, $env_info ),
			'exit_code'   => $this->get_final_exit_code( $results ),
		];
	}

	/**
	 * Extract SUT slug from environment options.
	 *
	 * @return string|null The SUT slug or null if not found.
	 */
	private function get_sut_slug_from_options(): ?string {
		// Check plugins.
		if ( isset( $this->env_up_options['--plugin'] ) ) {
			foreach ( $this->env_up_options['--plugin'] as $plugin_entry ) {
				$plugin_data = json_decode( $plugin_entry, true );
				if ( is_array( $plugin_data ) && isset( $plugin_data['slug'] ) && isset( $plugin_data['priority'] ) && $plugin_data['priority'] === 'low' ) {
					return $plugin_data['slug'];
				}
			}
		}

		// Check themes.
		if ( isset( $this->env_up_options['--theme'] ) ) {
			foreach ( $this->env_up_options['--theme'] as $theme_entry ) {
				$theme_data = json_decode( $theme_entry, true );
				if ( is_array( $theme_data ) && isset( $theme_data['slug'] ) && isset( $theme_data['priority'] ) && $theme_data['priority'] === 'low' ) {
					return $theme_data['slug'];
				}
			}
		}

		return null;
	}

	/**
	 * Copy test configuration properties to environment info.
	 *
	 * @param PerformanceEnvInfo $env_info The environment info.
	 * @param PerformanceEnvInfo $test_config The test configuration.
	 */
	private function copy_test_config( PerformanceEnvInfo $env_info, PerformanceEnvInfo $test_config ): void {
		$env_info->sut          = $test_config->sut;
		$env_info->run_baseline = $test_config->run_baseline;
	}

	/**
	 * Display a summary of performance test results.
	 */
	private function display_results_summary( PerformanceTestResult $test_result ): void {

		$this->output->writeln( '' );

		// Show artifacts location.
		$artifacts_path = $test_result->get_artifacts_path();
		$this->output->writeln( sprintf( 'Artifacts saved to: <comment>%s</comment>', $artifacts_path ) );

		// Show HTML report if available.
		$report_url = $test_result->get_report_url();
		if ( $report_url ) {
			$this->output->writeln( sprintf( 'HTML report: <comment>%s</comment>', $report_url ) );
		}

		$this->output->writeln( '' );
	}

	/**
	 * Notify test started if notification parameters are configured.
	 *
	 * @param PerformanceEnvInfo $env_info The environment info after setup.
	 */
	private function notify_test_started_if_configured( PerformanceEnvInfo $env_info ): void {
		if ( $this->notification_params === null ) {
			return;
		}

		$this->notifier->notify_test_started(
			$this->notification_params['woo_id'],
			$this->notification_params['woo_version'],
			$env_info,
			$this->notification_params['is_development'],
			$this->notification_params['notify'],
			'performance'
		);
	}
}
