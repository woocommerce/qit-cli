<?php

namespace QIT_CLI\Performance\Runner;

use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Performance\Result\PerformanceTestResult;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * K6 Performance Test Runner
 * 
 * This class handles K6-specific performance test execution and configuration.
 * K6-specific settings like test duration, virtual users, and test scenarios
 * are managed internally by this runner, keeping the PerformanceEnvInfo
 * framework-agnostic.
 */
class K6Runner {

	/** @var OutputInterface */
	protected $output;

	/** @var Docker */
	private $docker;

	/** @var K6DockerConfig */
	private $docker_config;

	/** @var PerformanceTestResult */
	private $performance_test_result;

	/** @var int Duration of performance test in seconds */
	private $test_duration = 300; // 5 minutes default

	/** @var int Number of virtual users */
	private $virtual_users = 10;

	/** @var string Performance test scenario (ramp-up, steady-state, spike, etc.) */
	private $test_scenario = 'steady-state';

	public function __construct( OutputInterface $output, Docker $docker ) {
		$this->output = $output;
		$this->docker = $docker;
		$this->docker_config = new K6DockerConfig( $docker );
	}

	public function run_test( PerformanceEnvInfo $env_info, array $test_infos, PerformanceTestResult $test_result ): int {
		// Store the test result object
		$this->performance_test_result = $test_result;
		
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

	/**
	 * Create k6 configuration file.
	 */
	private function create_k6_config( PerformanceEnvInfo $env_info ): void {
		// Calculate stages based on test duration and scenario
		$stages = $this->get_test_stages();

		$k6_config = [
			'scenarios' => [
				'default' => [
					'executor' => 'ramping-vus',
					'stages' => $stages,
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
	private function update_test_info( PerformanceEnvInfo $env_info ): void {
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

	/**
	 * Set the test duration in seconds.
	 */
	public function set_test_duration( int $duration ): void {
		$this->test_duration = $duration;
	}

	/**
	 * Get the test duration in seconds.
	 */
	public function get_test_duration(): int {
		return $this->test_duration;
	}

	/**
	 * Set the number of virtual users.
	 */
	public function set_virtual_users( int $users ): void {
		$this->virtual_users = $users;
	}

	/**
	 * Get the number of virtual users.
	 */
	public function get_virtual_users(): int {
		return $this->virtual_users;
	}

	/**
	 * Set the test scenario.
	 */
	public function set_test_scenario( string $scenario ): void {
		$this->test_scenario = $scenario;
	}

	/**
	 * Get the test scenario.
	 */
	public function get_test_scenario(): string {
		return $this->test_scenario;
	}

	/**
	 * Configure K6 test parameters.
	 */
	public function configure_test( ?int $duration = null, ?int $virtual_users = null, ?string $scenario = null ): void {
		if ( $duration !== null ) {
			$this->test_duration = $duration;
		}
		if ( $virtual_users !== null ) {
			$this->virtual_users = $virtual_users;
		}
		if ( $scenario !== null ) {
			$this->test_scenario = $scenario;
		}
	}

	/**
	 * Generate K6 test stages based on configuration.
	 */
	private function get_test_stages(): array {
		switch ( $this->test_scenario ) {
			case 'ramp-up':
				return [
					[ 'duration' => '30s', 'target' => $this->virtual_users ],
					[ 'duration' => ( $this->test_duration - 60 ) . 's', 'target' => $this->virtual_users ],
					[ 'duration' => '30s', 'target' => 0 ],
				];
			
			case 'spike':
				$spike_users = $this->virtual_users * 2;
				return [
					[ 'duration' => '10s', 'target' => $this->virtual_users ],
					[ 'duration' => '30s', 'target' => $spike_users ],
					[ 'duration' => '10s', 'target' => $this->virtual_users ],
					[ 'duration' => ( $this->test_duration - 60 ) . 's', 'target' => $this->virtual_users ],
					[ 'duration' => '10s', 'target' => 0 ],
				];
			
			case 'steady-state':
			default:
				return [
					[ 'duration' => '10s', 'target' => $this->virtual_users ],
					[ 'duration' => ( $this->test_duration - 20 ) . 's', 'target' => $this->virtual_users ],
					[ 'duration' => '10s', 'target' => 0 ],
				];
		}
	}
}