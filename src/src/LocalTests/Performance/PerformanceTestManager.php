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

	/** @var OutputInterface */
	private $output;

	/** @var LocalTestRunNotifier */
	private $notifier;

	public function __construct( K6Runner $k6_runner, LocalTestRunNotifier $notifier ) {
		$this->k6_runner = $k6_runner;
		$this->notifier = $notifier;
	}

	public function set_output( OutputInterface $output ): void {
		$this->output = $output;
	}

	public function run_tests( PerformanceEnvInfo $env_info ): int {
		$this->ensure_default_tests( $env_info );

		$test_result = new PerformanceTestResult( $env_info );

		// Run K6 performance tests
		$exit_status_code = $this->k6_runner->run_test( $env_info, $env_info->tests, $test_result );

		// Store exit code and mark as completed (like E2E tests do)
		$test_result->add_metric( 'k6_exit_code', $exit_status_code );
		$test_result->set_status( 'completed' );

		// Notify test finished and get final status
		[ $report_url, $exit_status_code_override ] = $this->notifier->notify_test_finished( $test_result );

		// Display results summary with final status
		$this->display_results_summary( $test_result );

		// Use override exit code if provided
		return $exit_status_code_override ?? $exit_status_code;
	}

	/**
	 * Ensure default tests are configured if none are specified.
	 */
	private function ensure_default_tests( PerformanceEnvInfo $env_info ): void {
		if ( ! empty( $env_info->tests ) ) {
			return;
		}

		$this->output->writeln( '<info>No specific performance tests configured. Running default performance test.</info>' );
		
		$env_info->tests = [
			[
				'slug' => $env_info->sut_slug,
				'test_tag' => $env_info->test_tag ?: 'default',
				'type' => $env_info->sut_type,
				'action' => 'activate',
				'path_in_php_container' => '',
				'path_in_host' => '',
			],
		];
	}

	/**
	 * Display a summary of performance test results.
	 */
	private function display_results_summary( PerformanceTestResult $test_result ): void {
		if ( ! $this->output ) {
			return;
		}

		$this->output->writeln( '' );
		
		// Show artifacts location
		$artifacts_path = $test_result->get_artifacts_path();
		$this->output->writeln( sprintf( 'Artifacts saved to: <comment>%s</comment>', $artifacts_path ) );
		
		// Show HTML report if available
		$report_url = $test_result->get_report_url();
		if ( $report_url ) {
			$this->output->writeln( sprintf( 'HTML report: <comment>%s</comment>', $report_url ) );
		}
		
		// Show metrics summary
		$metrics = $test_result->get_metrics();
		if ( ! empty( $metrics['summary_http_req_duration_avg'] ) ) {
			$this->output->writeln( sprintf( 
				'Average response time: <comment>%.2fms</comment>',
				$metrics['summary_http_req_duration_avg']
			) );
		}
		
		$this->output->writeln( '' );
	}
}