<?php

namespace QIT_CLI\Performance;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
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

	public function run_tests( E2EEnvInfo $env_info ): int {
		// Discover tests to run using the k6 runner
		$tests_to_run = $this->k6_runner->discover_tests( $env_info );

		if ( empty( $tests_to_run ) ) {
			if ( $this->output ) {
				$this->output->writeln( '<error>No performance tests found to run.</error>' );
			}
			return 1;
		}

		// Set up test environment using the k6 runner
		$this->k6_runner->setup_test_environment( $env_info );

		// Run the performance tests
		$exit_code = $this->k6_runner->run_performance_test( $env_info );

		// Get the PerformanceTestResult from K6Runner that has the actual results
		$performance_test_result = $this->k6_runner->get_performance_test_result();

		// Display artifact paths and report information
		$this->display_results_summary( $performance_test_result );

		return $exit_code;
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