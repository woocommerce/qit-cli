<?php

namespace QIT_CLI\Performance;

use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
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
		if ( empty( $env_info->tests ) ) {
			$this->output->writeln( '<error>No performance tests found to run.</error>' );
			return 1;
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