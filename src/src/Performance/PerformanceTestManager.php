<?php

namespace QIT_CLI\Performance;

use QIT_CLI\Environment\EnvironmentRunner;
use QIT_CLI\Utils\LocalTestRunNotifier;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Performance\Result\PerformanceTestResult;
use QIT_CLI\Performance\Runner\K6Runner;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvironment;
use Symfony\Component\Console\Output\OutputInterface;

class PerformanceTestManager {
	/** @var K6Runner */
	private $k6_runner;

	/** @var OutputInterface|null */
	private $output;

	/** @var LocalTestRunNotifier */
	private $notifier;

	/** @var EnvironmentRunner */
	private $environment_runner;

	/** @var PerformanceEnvironment */
	private $performance_environment;

	/** @var array<string,mixed> */
	private $env_up_options;

	/** @var array<string,mixed>|null */
	private $notification_params;

	public function __construct( K6Runner $k6_runner, LocalTestRunNotifier $notifier, EnvironmentRunner $environment_runner, PerformanceEnvironment $performance_environment ) {
		$this->k6_runner               = $k6_runner;
		$this->notifier                = $notifier;
		$this->environment_runner      = $environment_runner;
		$this->performance_environment = $performance_environment;
	}

	public function set_output( OutputInterface $output ): void {
		$this->output = $output;
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

	/**
	 * Run performance tests in a fresh environment.
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test against.
	 * @return int Exit code of the test run.
	 */
	public function run_tests( PerformanceEnvInfo $env_info ): int {
		$baseline_result = null;
		$main_exit_code  = 0;

		$this->output->writeln( '<comment>Setting up test environment...</comment>' );

		$extension_env_info = null;
		try {
			$extension_env_info = $this->create_environment( $this->env_up_options );
			$this->copy_test_config( $extension_env_info, $env_info );

			if ( $env_info->run_baseline ) {
				$this->output->writeln( '<comment>Starting baseline tests...</comment>' );
				$baseline_result = $this->run_baseline_tests( $extension_env_info );

				if ( $baseline_result->status === 'cancelled' ) {
					$this->output->writeln( '<error>Baseline tests were cancelled, cancelling entire test run.</error>' );
					return 143;
				}

				$this->output->writeln( '<comment>Baseline tests completed successfully.</comment>' );
				$this->reset_database_to_clean_state( $extension_env_info );
			}

			$this->notify_test_started_if_configured( $extension_env_info );

			$this->output->writeln( '<comment>Proceeding to SUT tests...</comment>' );
			$main_result    = $this->run_sut_tests( $extension_env_info );
			$main_exit_code = $main_result['exit_code'];

			$final_result = $main_result['test_result'];
			if ( $baseline_result !== null ) {
				$final_result = $this->combine_results( $main_result['test_result'], $baseline_result );
			}

			if ( $this->output->isVerbose() ) {
				$this->output->writeln( '<comment>Uploading test results...</comment>' );
			}
			$this->notifier->notify_test_finished( $final_result );

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
		$combined_result = $main_result;

		if ( $baseline_result ) {
			$combined_result->set_baseline_result( $baseline_result );
		}

		return $combined_result;
	}

	/**
	 * Run a performance test.
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test against.
	 * @param string             $test_type Test type for logging ('baseline' or 'extension').
	 * @param bool               $is_baseline Whether this is a baseline test.
	 * @return PerformanceTestResult Test result.
	 */
	private function run_test( PerformanceEnvInfo $env_info, string $test_type, bool $is_baseline ): PerformanceTestResult {
		$result_filenames = $this->k6_runner->get_result_filenames_from_manifest( $env_info );

		$test_result = new PerformanceTestResult( $env_info, $result_filenames );
		$test_result->set_baseline( $is_baseline );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( '<comment>Running %s tests for: %s</comment>', $test_type, $env_info->sut['slug'] ?? 'unknown' ) );
		}

		$exit_code = $this->k6_runner->run_test( $env_info, $test_result );
		$test_result->add_metric( 'k6_exit_code', $exit_code );

		if ( $exit_code === 143 ) {
			$test_result->set_status( 'cancelled' );
			$this->output->writeln( sprintf( '<error>%s tests were cancelled</error>', ucfirst( $test_type ) ) );
			return $test_result;
		}

		$test_result->set_status( 'completed' );
		$test_result->process_results();

		if ( $this->output->isVerbose() ) {
			$metrics_count = count( $test_result->get_metrics() );
			$this->output->writeln( sprintf( '<comment>%s tests completed: %d metrics collected</comment>', ucfirst( $test_type ), $metrics_count ) );
		}

		return $test_result;
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
	 *
	 * @param PerformanceEnvInfo $env_info The environment to reset.
	 */
	private function reset_database_to_clean_state( PerformanceEnvInfo $env_info ): void {
		$this->output->writeln( '<comment>Resetting database to clean state...</comment>' );

		$this->performance_environment->init( $env_info );
		$this->performance_environment->generate_base_data();
	}

	/**
	 * Run baseline performance tests (SUT deactivated).
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test.
	 * @return PerformanceTestResult The baseline test result.
	 */
	private function run_baseline_tests( PerformanceEnvInfo $env_info ): PerformanceTestResult {
		$sut_slug = $this->get_sut_slug_from_options();

		if ( $sut_slug ) {
			$this->performance_environment->init( $env_info );
			$this->performance_environment->deactivate_sut_plugin( $sut_slug );
		} else {
			$this->output->writeln( '<comment>No SUT plugin found to deactivate for baseline</comment>' );
		}

		$this->output->writeln( '<comment>Running baseline tests...</comment>' );

		return $this->run_test( $env_info, 'baseline', true );
	}

	/**
	 * Run SUT performance tests (SUT activated).
	 *
	 * @param PerformanceEnvInfo $env_info The environment to test.
	 * @return array{test_result: PerformanceTestResult, exit_code: int} The SUT test result and exit code.
	 */
	private function run_sut_tests( PerformanceEnvInfo $env_info ): array {
		$sut_slug = $this->get_sut_slug_from_options();

		if ( $sut_slug ) {
			$this->performance_environment->init( $env_info );
			$this->performance_environment->activate_sut_plugin( $sut_slug );
		} else {
			$this->output->writeln( '<comment>No SUT plugin found to activate</comment>' );
		}

		$this->output->writeln( '<comment>Running extension tests...</comment>' );
		$result = $this->run_test( $env_info, 'extension', false );

		$exit_code = $result->status === 'cancelled' ? 143 : ( $result->get_metrics()['k6_exit_code'] ?? 0 );

		return [
			'test_result' => $result,
			'exit_code'   => $exit_code,
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
		$env_info->sut['id']    = $test_config->sut['id'] ?? null;
		$env_info->sut['type']  = $test_config->sut['type'] ?? 'plugin';
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
