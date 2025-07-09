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
		// Discover tests to run (for performance, we'll create default k6 tests)
		$tests_to_run = $this->discover_tests( $env_info );

		if ( empty( $tests_to_run ) ) {
			if ( $this->output ) {
				$this->output->writeln( '<e>No performance tests found to run.</e>' );
			}
			return 1;
		}

		// Set up test environment
		$this->setup_test_environment( $env_info );

		// Run the performance tests
		$exit_code = $this->k6_runner->run_performance_test( $env_info );

		// Get the PerformanceTestResult from K6Runner that has the actual results
		$performance_test_result = $this->k6_runner->get_performance_test_result();

		// Display artifact paths and report information
		$this->display_results_summary( $performance_test_result );

		return $exit_code;
	}

	private function discover_tests( E2EEnvInfo $env_info ): array {
		$tests = [];

		// Look for k6 test files in the SUT
		$test_paths = [
			$env_info->sut_path . '/tests/performance',
			$env_info->sut_path . '/tests/k6',
			$env_info->sut_path . '/performance',
			$env_info->sut_path . '/k6',
		];

		foreach ( $test_paths as $test_path ) {
			if ( is_dir( $test_path ) ) {
				$test_files = glob( $test_path . '/*.js' );
				foreach ( $test_files as $test_file ) {
					$test_info = [
						'name' => basename( $test_file, '.js' ),
						'path' => $test_file,
						'path_in_host' => $test_file,
						'path_in_container' => '/tests/' . basename( $test_file ),
					];

					$tests[] = $test_info;
				}
			}
		}

		// If no tests found, create a default performance test
		if ( empty( $tests ) ) {
			$default_test = $this->create_default_test( $env_info );
			if ( $default_test ) {
				$tests[] = $default_test;
			}
		}

		return $tests;
	}

	private function create_default_test( E2EEnvInfo $env_info ): ?array {
		$test_file = $env_info->temporary_env . '/k6/default-performance-test.js';
		
		// Use K6Runner to create the default test file
		$created_test_file = $this->k6_runner->create_default_k6_test( $test_file );

		return [
			'name' => 'default-performance-test',
			'path' => $created_test_file,
			'path_in_host' => $created_test_file,
			'path_in_container' => '/tests/default-performance-test.js',
		];
	}

	private function setup_test_environment( E2EEnvInfo $env_info ): void {
		// Create k6 configuration
		$this->create_k6_config( $env_info );

		// Set up test information
		$this->update_test_info( $env_info );
	}

	private function create_k6_config( E2EEnvInfo $env_info ): void {
		$k6_config = [
			'scenarios' => [
				'default' => [
					'executor' => 'ramping-vus',
					'stages' => [
						[ 'duration' => '10s', 'target' => 5 ],
						[ 'duration' => '20s', 'target' => 10 ],
						[ 'duration' => '10s', 'target' => 0 ],
					],
				],
			],
			'thresholds' => [
				'http_req_duration' => [ 'p(95)<500' ],
				'http_req_failed' => [ 'rate<0.1' ],
			],
		];

		// Apply k6 config overrides if available
		// Note: k6_config property is not available on E2EEnvInfo, so we use default config
		// TODO: Add k6_config property to E2EEnvInfo if needed for custom k6 configuration

		$config_file = $env_info->temporary_env . '/k6/k6-config.json';
		file_put_contents( $config_file, json_encode( $k6_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	private function update_test_info( E2EEnvInfo $env_info ): void {
		$plugin_activation_stack = array_map( static function ( $plugin ) {
			return $plugin['slug'];
		}, array_reverse( $env_info->plugins ) );

		$sut_qit_config = [];
		if ( file_exists( "{$env_info->sut_path}/qit.json" ) ) {
			$sut_qit_config = json_decode( file_get_contents( "{$env_info->sut_path}/qit.json" ), true );
		}

		$test_info = [
			'SUT_SLUG' => $env_info->sut_slug,
			'SUT_TYPE' => $env_info->sut_type,
			'SUT_ENTRYPOINT' => $env_info->sut_entrypoint,
			'SUT_QIT_CONFIG' => $sut_qit_config ?: [],
			'PLUGIN_ACTIVATION_STACK' => $plugin_activation_stack,
		];

		file_put_contents( $env_info->temporary_env . '/k6/test-info.json', json_encode( $test_info ) );
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