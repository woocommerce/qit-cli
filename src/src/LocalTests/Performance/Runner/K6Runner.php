<?php

namespace QIT_CLI\LocalTests\Performance\Runner;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;
use QIT_CLI\LocalTests\E2E\Runner\E2ERunner;
use QIT_CLI\LocalTests\E2E\Runner\PlaywrightOrchestration;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class K6Runner extends E2ERunner {

	/** @var Docker */
	private $docker;

	/** @var PerformanceTestResult */
	private $performance_test_result;

	public function __construct( OutputInterface $output, PlaywrightOrchestration $orchestration, Docker $docker ) {
		parent::__construct( $output, $orchestration );
		$this->docker = $docker;
	}

	public function run_test( E2EEnvInfo $env_info, array $test_infos, TestResult $test_result, string $test_mode, ?string $shard = null ): int {
		// Create a performance test result internally
		$this->performance_test_result = new PerformanceTestResult( $env_info );
		
		// Set up k6 cache directory
		$this->setup_k6_cache();

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
		$k6_args = $this->build_k6_docker_args( $env_info, $test_infos, $results_dir, $k6_container_name );

		// Run k6 tests
		$exit_code = $this->execute_k6_tests( $k6_args, $test_infos );

		// Collect results
		$this->collect_results( $this->performance_test_result, $results_dir );

		// Process performance test results
		$this->performance_test_result->process_results();

		// Update the E2E test result status based on performance test outcome
		$test_result->set_status( $exit_code === 0 ? 'passed' : 'failed' );

		return $exit_code;
	}

	public function get_performance_test_result(): PerformanceTestResult {
		return $this->performance_test_result;
	}

	private function setup_k6_cache(): void {
		$k6_cache_dir = Config::get_qit_dir() . 'cache/k6';
		if ( ! file_exists( $k6_cache_dir ) ) {
			if ( ! mkdir( $k6_cache_dir, 0755, true ) ) {
				throw new \RuntimeException( 'Could not create k6 cache directory: ' . $k6_cache_dir );
			}
		}
	}

	private function build_k6_docker_args( E2EEnvInfo $env_info, array $test_infos, string $results_dir, string $container_name ): array {
		$k6_args = [
			$this->docker->find_docker(),
			'run',
			"--name=$container_name",
			"--network={$env_info->docker_network}",
			'--rm',
			'--init',
			'-e',
			sprintf( 'BASE_URL=%s', $env_info->site_url ),
			'-e',
			sprintf( 'QIT_DOMAIN=%s', $env_info->domain ),
			'-e',
			sprintf( 'QIT_INTERNAL_DOMAIN=%s', sprintf( 'host.docker.internal:%s', $env_info->nginx_port ) ),
			'-e',
			sprintf( 'QIT_INTERNAL_NGINX=%s', sprintf( 'qitenvnginx%s', $env_info->env_id ) ),
			'-v',
			Config::get_qit_dir() . 'cache/k6:/k6-cache',
			'-v',
			$env_info->temporary_env . '/k6:/tests',
			'-v',
			$results_dir . ':/results',
			'-v',
			$env_info->temporary_env . '/k6/qitHelpers.js:/qitHelpers/qitHelpers.js',
			'-v',
			$env_info->temporary_env . '/k6/test-info.json:/qitHelpers/test-info.json',
			'--add-host=host.docker.internal:host-gateway',
		];

		// Add volume mount for default test file
		$default_test_file = sys_get_temp_dir() . '/qit-k6-default-test.js';
		$k6_args = array_merge( $k6_args, [
			'-v',
			"$default_test_file:/tests/default-performance-test.js",
		] );

		// Pass environment variables
		foreach ( App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?? [] as $env_key => $env_value ) {
			$k6_args[] = '-e';
			$k6_args[] = "$env_key=$env_value";
		}

		// Set Docker user if needed
		if ( Docker::should_set_user() ) {
			$k6_args[] = '--user';
			$k6_args[] = implode( ':', Docker::get_user_and_group() );
		}

		// Add k6 Docker image
		$k6_args[] = 'grafana/k6:latest';

		// Add k6 command and options
		$k6_args[] = 'run';

		// Add default k6 options for performance testing
		$k6_args[] = '--duration';
		$k6_args[] = '30s';
		$k6_args[] = '--vus';
		$k6_args[] = '10';

		// Add output options
		$k6_args[] = '--out';
		$k6_args[] = 'json=/results/k6-results.json';

		return $k6_args;
	}

	private function execute_k6_tests( array $k6_args, array $test_infos ): int {
		$overall_exit_code = 0;

		// Create a default k6 performance test
		$default_test = $this->create_default_k6_test( $test_infos );

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
				$overall_exit_code = $exit_code;
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

		return $overall_exit_code;
	}

	private function create_default_k6_test( array $test_infos ): string {
		// Create a basic k6 performance test
		$test_content = 'import { check, sleep } from "k6";
import http from "k6/http";

export let options = {
    stages: [
        { duration: "10s", target: 5 },
        { duration: "20s", target: 10 },
        { duration: "10s", target: 0 },
    ],
    thresholds: {
        http_req_duration: ["p(95)<500"],
        http_req_failed: ["rate<0.1"],
    },
};

export default function() {
    const baseUrl = __ENV.BASE_URL || "http://localhost";
    
    // Test homepage
    let response = http.get(baseUrl);
    check(response, {
        "homepage status is 200": (r) => r.status === 200,
        "homepage loads in < 500ms": (r) => r.timings.duration < 500,
    });
    
    sleep(1);
    
    // Test WooCommerce shop page
    response = http.get(`${baseUrl}/shop/`);
    check(response, {
        "shop page status is 200": (r) => r.status === 200,
        "shop page loads in < 800ms": (r) => r.timings.duration < 800,
    });
    
    sleep(1);
    
    // Test cart page
    response = http.get(`${baseUrl}/cart/`);
    check(response, {
        "cart page accessible": (r) => r.status === 200 || r.status === 404,
    });
    
    sleep(Math.random() * 2 + 1);
}';

		// Write the test to a temporary location that will be mounted in the container
		$test_file = sys_get_temp_dir() . '/qit-k6-default-test.js';
		file_put_contents( $test_file, $test_content );
		
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
	 * Create a Dockerfile for k6 if the official image doesn't work
	 */
	public function create_k6_dockerfile( string $dockerfile_path ): void {
		$dockerfile_content = 'FROM grafana/k6:latest

# Install additional dependencies if needed
# RUN apk add --no-cache curl

# Copy custom scripts or extensions
# COPY custom-scripts/ /usr/local/bin/

# Set working directory
WORKDIR /tests

# Default command
CMD ["run", "--help"]
';

		file_put_contents( $dockerfile_path, $dockerfile_content );
	}

	/**
	 * Build custom k6 Docker image
	 */
	public function build_k6_image( string $dockerfile_path, string $tag = 'qit-k6:latest' ): bool {
		$build_args = [
			$this->docker->find_docker(),
			'build',
			'-t', $tag,
			'-f', $dockerfile_path,
			dirname( $dockerfile_path ),
		];

		$process = new Process( $build_args );
		$process->setTimeout( 600 ); // 10 minutes timeout

		if ( $this->output && $this->output->isVerbose() ) {
			$this->output->writeln( 'Building k6 Docker image: ' . $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			if ( $this->output && $this->output->isVerbose() ) {
				$this->output->write( $buffer );
			}
		} );

		return $process->isSuccessful();
	}
}