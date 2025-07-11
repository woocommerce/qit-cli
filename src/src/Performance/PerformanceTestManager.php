<?php

namespace QIT_CLI\Performance;

use QIT_CLI\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\Performance\Runner\K6Runner;
use QIT_CLI\Performance\Result\PerformanceTestResult;
use Symfony\Component\Console\Output\OutputInterface;

class PerformanceTestManager {
	/** @var K6Runner */
	private $k6_runner;

	/** @var OutputInterface */
	private $output;

	public function __construct( K6Runner $k6_runner ) {
		$this->k6_runner = $k6_runner;
	}

	public function set_output( OutputInterface $output ): void {
		$this->output = $output;
	}

	public function run_tests( PerformanceEnvInfo $env_info ): int {
		// If no specific performance tests are configured, create a default test
		if ( empty( $env_info->tests ) ) {
			$this->output->writeln( '<info>No specific performance tests configured. Running default performance test.</info>' );
			
			// Create a default test entry
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

		$test_result = new PerformanceTestResult( $env_info );

		// Run K6 performance tests
		$exit_status_code = $this->k6_runner->run_test( $env_info, $env_info->tests, $test_result );

		// Display artifact paths and report information
		$this->display_results_summary( $test_result );

		return $exit_status_code;
	}

	private function display_results_summary( PerformanceTestResult $test_result ): void {
		if ( $this->output ) {
			$this->output->writeln( '' );
			$this->output->writeln( '<info>Performance Test Results Summary:</info>' );
			
			$artifacts_path = $test_result->get_artifacts_path();
			$this->output->writeln( 'Artifacts Path: ' . $artifacts_path );
			
			$report_url = $test_result->get_report_url();
			if ( $report_url ) {
				$this->output->writeln( 'Report URL: ' . $report_url );
				$this->output->writeln( '<comment>To view the report, open: ' . $report_url . '</comment>' );
			} else {
				$this->output->writeln( '<warning>No HTML report generated</warning>' );
			}
			
			$this->output->writeln( '' );
		}
	}
}