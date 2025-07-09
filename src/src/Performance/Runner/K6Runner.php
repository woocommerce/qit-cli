<?php

namespace QIT_CLI\Performance\Runner;

use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Performance\Result\PerformanceTestResult;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class K6Runner extends PerformanceRunner {

	/** @var Docker */
	private $docker;

	/** @var K6DockerConfig */
	private $docker_config;

	/** @var PerformanceTestResult */
	private $performance_test_result;

	public function __construct( OutputInterface $output, Docker $docker ) {
		parent::__construct( $output );
		$this->docker = $docker;
		$this->docker_config = new K6DockerConfig( $docker );
	}

	public function run_performance_test( E2EEnvInfo $env_info ): int {
		// Create a performance test result internally
		$this->performance_test_result = new PerformanceTestResult( $env_info );
		
		// Set up k6 cache directory
		$this->setup_k6_cache();

		// Set environment variables for Docker
		$this->docker_config->set_environment_variables( $env_info );

		// Create k6 container name
		$k6_container_name = "qit_env_k6_{$env_info->env_id}";

		// Create results directory
		$results_dir = $this->performance_test_result->get_results_dir();
		if ( ! file_exists( $results_dir ) ) {
			if ( ! mkdir( $results_dir, 0755, true ) ) {
				throw new \RuntimeException( sprintf( 'Could not create results directory: %s', $results_dir ) );
			}
		}

		// Build k6 Docker arguments  
		$k6_args = $this->docker_config->build_k6_docker_args( $env_info, $results_dir, $k6_container_name );

		// Run k6 tests
		$exit_code = $this->execute_k6_tests( $k6_args );

		// Collect results
		$this->collect_results( $this->performance_test_result, $results_dir );

		// Process performance test results
		$this->performance_test_result->process_results();

		return $exit_code;
	}

	public function get_performance_test_result(): PerformanceTestResult {
		return $this->performance_test_result;
	}

	/**
	 * Discover k6 test files in the SUT or create a default test.
	 */
	public function discover_tests( E2EEnvInfo $env_info ): array {
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
			$default_test = $this->create_default_test_info( $env_info );
			if ( $default_test ) {
				$tests[] = $default_test;
			}
		}

		return $tests;
	}

	/**
	 * Create default test information for k6.
	 */
	private function create_default_test_info( E2EEnvInfo $env_info ): ?array {
		$test_file = $env_info->temporary_env . '/k6/default-performance-test.js';
		
		// Use create_default_k6_test to create the test file
		$created_test_file = $this->create_default_k6_test( $test_file );

		return [
			'name' => 'default-performance-test',
			'path' => $created_test_file,
			'path_in_host' => $created_test_file,
			'path_in_container' => '/tests/default-performance-test.js',
		];
	}

	/**
	 * Set up the k6 test environment by creating configuration and test info files.
	 */
	public function setup_test_environment( E2EEnvInfo $env_info ): void {
		// Create k6 configuration
		$this->create_k6_config( $env_info );

		// Set up test information
		$this->update_test_info( $env_info );
	}

	/**
	 * Create k6 configuration file.
	 */
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

		// Ensure k6 directory exists
		$k6_dir = $env_info->temporary_env . '/k6';
		if ( ! file_exists( $k6_dir ) ) {
			mkdir( $k6_dir, 0755, true );
		}

		$config_file = $k6_dir . '/k6-config.json';
		file_put_contents( $config_file, json_encode( $k6_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Update test information for k6 tests.
	 */
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

		// Ensure k6 directory exists
		$k6_dir = $env_info->temporary_env . '/k6';
		if ( ! file_exists( $k6_dir ) ) {
			mkdir( $k6_dir, 0755, true );
		}

		file_put_contents( $k6_dir . '/test-info.json', json_encode( $test_info ) );
	}

	private function setup_k6_cache(): void {
		$k6_cache_dir = Config::get_qit_dir() . 'cache/k6';
		if ( ! file_exists( $k6_cache_dir ) ) {
			if ( ! mkdir( $k6_cache_dir, 0755, true ) ) {
				throw new \RuntimeException( 'Could not create k6 cache directory: ' . $k6_cache_dir );
			}
		}
	}

	private function execute_k6_tests( array $k6_args ): int {
		// Create a default k6 performance test
		$this->create_default_k6_test();

		if ( $this->output ) {
			$this->output->writeln( "<info>Running k6 performance test for WooCommerce extension</info>" );
		}

		// Add the test file to k6 args
		$test_args = array_merge( $k6_args, [ '/tests/default-performance-test.js' ] );

		// Execute the test
		$process = new Process( $test_args );
		$process->setTimeout( 3600 ); // 1 hour timeout

		if ( $this->output && $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Running: ' . $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			if ( $this->output && ( $this->output->isVerbose() || $type === Process::ERR ) ) {
				$this->output->write( $buffer );
			}
		} );

		$exit_code = $process->getExitCode();

		if ( $this->output ) {
			if ( $exit_code === 0 ) {
				$this->output->writeln( "<info>✓ k6 performance test passed</info>" );
			} else {
				$this->output->writeln( "<error>✗ k6 performance test failed with exit code: $exit_code</error>" );
			}
		}

		// Show test output if verbose
		if ( $this->output && $this->output->isVerbose() ) {
			$output = $process->getOutput();
			if ( ! empty( $output ) ) {
				$this->output->writeln( "Test output:\n$output" );
			}

			$error_output = $process->getErrorOutput();
			if ( ! empty( $error_output ) ) {
				$this->output->writeln( "Test errors:\n$error_output" );
			}
		}

		return $exit_code;
	}

	public function create_default_k6_test( ?string $target_file = null ): string {
		$default_test_source = __DIR__ . '/../tests/default-performance.k6.js';
		
		if ( ! file_exists( $default_test_source ) ) {
			throw new \RuntimeException( 'Default performance test file not found: ' . $default_test_source );
		}

		// Use provided target file or default to temp location
		$test_file = $target_file ?: sys_get_temp_dir() . '/qit-k6-default-test.js';
		
		// Create directory if it doesn't exist
		if ( ! file_exists( dirname( $test_file ) ) ) {
			mkdir( dirname( $test_file ), 0755, true );
		}
		
		if ( ! copy( $default_test_source, $test_file ) ) {
			throw new \RuntimeException( 'Could not copy default performance test to: ' . $test_file );
		}
		
		return $test_file;
	}

	private function collect_results( PerformanceTestResult $performance_test_result, string $results_dir ): void {
		// Collect k6 JSON results and copy to test result directory if they exist
		$json_results_file = $results_dir . '/k6-results.json';
		if ( file_exists( $json_results_file ) ) {
			// Copy k6 results to the performance test result directory
			$target_dir = $performance_test_result->get_results_dir();
			if ( ! file_exists( $target_dir ) ) {
				mkdir( $target_dir, 0755, true );
			}
			copy( $json_results_file, $target_dir . '/k6-results.json' );
			
			if ( $this->output && $this->output->isVerbose() ) {
				$this->output->writeln( "<info>k6 results saved to: {$target_dir}/k6-results.json</info>" );
			}
		}
	}
}