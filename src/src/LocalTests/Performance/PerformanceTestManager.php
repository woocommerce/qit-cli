<?php

namespace QIT_CLI\LocalTests\Performance;

use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;
use QIT_CLI\LocalTests\Performance\Runner\K6Runner;
use Symfony\Component\Console\Output\OutputInterface;

class PerformanceTestManager {
	/** @var K6Runner */
	private $k6_runner;

	/** @var OutputInterface|null */
	private $output;

	/** @var LocalTestRunNotifier */
	private $notifier;

	public function __construct( K6Runner $k6_runner, LocalTestRunNotifier $notifier ) {
		$this->k6_runner = $k6_runner;
		$this->notifier  = $notifier;
	}

	public function set_output( OutputInterface $output ): void {
		$this->output = $output;
	}

	public function run_tests( PerformanceEnvInfo $env_info ): int {
		$baseline_result = null;
		$main_exit_code  = 0;

		// Run baseline tests first if enabled.
		if ( $env_info->run_baseline ) {
			$this->output->writeln( '<info>Running baseline performance tests...</info>' );

			$baseline_result = $this->run_baseline_tests( $env_info );
			if ( $baseline_result === null ) {
				$this->output->writeln( '<error>Baseline tests failed, continuing with main tests.</error>' );
			}
		}

		// Run main tests (with extension).
		if ( $env_info->run_baseline ) {
			$this->output->writeln( '<info>Running extension performance tests...</info>' );
		} else {
			$this->output->writeln( '<info>Running performance tests...</info>' );
		}

		$main_result    = $this->run_main_tests( $env_info );
		$main_exit_code = $main_result['exit_code'];

		// Combine baseline and main results if baseline was run.
		$final_result = $main_result['test_result'];
		if ( $baseline_result !== null ) {
			$final_result = $this->combine_results( $main_result['test_result'], $baseline_result );

		}

		// Upload the final combined test results.
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( '<comment>Uploading test results...</comment>' );
		}
		$this->notifier->notify_test_finished( $final_result );

		// Display results summary.
		$this->display_results_summary( $final_result );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( sprintf( '[Verbose] Test artifacts directory: %s', $main_result['test_result']->get_results_dir() ) );
		}

		return $main_exit_code;
	}

	/**
	 * Run baseline performance tests (without the extension).
	 *
	 * @param PerformanceEnvInfo $env_info The environment info.
	 * @return PerformanceTestResult|null The baseline test result or null if failed.
	 */
	private function run_baseline_tests( PerformanceEnvInfo $env_info ): ?PerformanceTestResult {
		try {
			$this->output->writeln( '<comment>Preparing baseline environment (WooCommerce only)...</comment>' );

			// Create a clean baseline environment without the SUT installed.
			$baseline_env_info = $this->create_baseline_environment( $env_info );

			$baseline_result = new PerformanceTestResult( $baseline_env_info );
			$baseline_result->set_baseline( true );

			if ( $this->output->isVerbose() ) {
				$this->output->writeln( sprintf( '<comment>Running baseline with tests for: %s</comment>', $baseline_env_info->sut_slug ) );
			}

			$exit_status_code = $this->k6_runner->run_test( $baseline_env_info, $baseline_env_info->tests, $baseline_result );

			$baseline_result->add_metric( 'k6_exit_code', $exit_status_code );

			if ( $exit_status_code === 143 ) {
				$baseline_result->set_status( 'cancelled' );
			} else {
				$baseline_result->set_status( 'completed' );
			}

			// Process baseline results to ensure metrics are calculated.
			$baseline_result->process_results();

			if ( $this->output->isVerbose() ) {
				$baseline_metrics = $baseline_result->get_metrics();
				$this->output->writeln( sprintf( '<comment>Baseline metrics collected: %d metrics</comment>', count( $baseline_metrics ) ) );
			}

			$this->output->writeln( '<comment>Baseline test completed.</comment>' );

			return $baseline_result;

		} catch ( \Exception $e ) {
			$this->output->writeln( sprintf( '<error>Failed to run baseline tests: %s</error>', $e->getMessage() ) );
			return null;
		}
	}

	/**
	 * Run main performance tests (with the extension).
	 *
	 * @param PerformanceEnvInfo $env_info The environment info.
	 * @return array{test_result: PerformanceTestResult, exit_code: int} The main test result and exit code.
	 */
	private function run_main_tests( PerformanceEnvInfo $env_info ): array {
		$test_result = new PerformanceTestResult( $env_info );
		$test_result->set_baseline( false );

		// Run k6 performance tests.
		$exit_status_code = $this->k6_runner->run_test( $env_info, $env_info->tests, $test_result );

		// Store exit code and set status based on how test finished.
		$test_result->add_metric( 'k6_exit_code', $exit_status_code );

		if ( $exit_status_code === 143 ) {
			// Test was cancelled (SIGTERM received).
			$test_result->set_status( 'cancelled' );
		} else {
			$test_result->set_status( 'completed' );
		}

		// Process main test results to ensure metrics are calculated.
		$test_result->process_results();

		return [
			'test_result' => $test_result,
			'exit_code'   => $exit_status_code,
		];
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
		}

		return $combined_result;
	}


	/**
	 * Create a baseline environment with only WooCommerce (no extension installed).
	 *
	 * @param PerformanceEnvInfo $env_info The original environment info.
	 * @return PerformanceEnvInfo The baseline environment info.
	 */
	private function create_baseline_environment( PerformanceEnvInfo $env_info ): PerformanceEnvInfo {
		$baseline_env_info = clone $env_info;

		// Create unique identifiers for baseline environment.
		$baseline_env_info->env_id = $env_info->env_id . '_baseline';

		// Keep original SUT info for test file resolution but mark as baseline.
		$baseline_env_info->sut_slug = $env_info->sut_slug;
		$baseline_env_info->sut_type = $env_info->sut_type;

		// Remove the SUT from plugins/themes lists to ensure it's not installed.
		$baseline_env_info->plugins = array_filter( $env_info->plugins, function ( $plugin ) use ( $env_info ) {
			// Keep all plugins except the SUT.
			return $plugin['slug'] !== $env_info->sut_slug;
		} );

		$baseline_env_info->themes = array_filter( $env_info->themes, function ( $theme ) use ( $env_info ) {
			// Keep all themes except the SUT.
			return $theme['slug'] !== $env_info->sut_slug;
		} );

		// Update tests array to remove SUT-specific test entries but keep same test files.
		$baseline_env_info->tests = array_map( function ( $test ) use ( $env_info ) {
			// If this test entry is for the SUT, modify it to run as baseline.
			if ( $test['slug'] === $env_info->sut_slug ) {
				$test['slug'] = 'baseline';
				$test['type'] = 'baseline';
				// Keep the same test paths and files.
			}
			return $test;
		}, $env_info->tests );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf(
				'<comment>Baseline environment: %d plugins, %d themes (SUT excluded)</comment>',
				count( $baseline_env_info->plugins ),
				count( $baseline_env_info->themes )
			) );
		}

		return $baseline_env_info;
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
}
