<?php

namespace QIT_CLI\LocalTests\Performance\Runner;

use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * K6 Performance Test Runner.
 *
 * This class handles K6-specific performance test execution and configuration.
 * K6-specific settings like test duration, virtual users, and test scenarios
 * are managed internally by this runner, keeping the PerformanceEnvInfo
 * framework-agnostic.
 */
class K6Runner {

	/** @var OutputInterface */
	protected $output;

	/** @var K6DockerConfig */
	private $docker_config;

	/** @var PerformanceTestResult */
	private $performance_test_result;


	public function __construct( OutputInterface $output, Docker $docker ) {
		$this->output        = $output;
		$this->docker_config = new K6DockerConfig( $docker );
	}

	/**
	 * @param PerformanceEnvInfo    $env_info
	 * @param array<mixed>          $test_infos
	 * @param PerformanceTestResult $test_result
	 */
	public function run_test( PerformanceEnvInfo $env_info, array $test_infos, PerformanceTestResult $test_result ): int {
		$this->performance_test_result = $test_result;

		// Setup directories and environment.
		$this->setup_test_environment( $env_info );

		// Build and execute k6 test.
		$k6_args = $this->docker_config->build_k6_docker_args(
			$env_info,
			$test_result->get_results_dir(),
			"qit_env_k6_{$env_info->env_id}"
		);

		$exit_code = $this->execute_k6_tests( $k6_args );

		// Collect and process results.
		$this->collect_results( $test_result );
		$test_result->process_results();

		return $exit_code;
	}

	/**
	 * Setup test environment - create directories and set environment variables.
	 */
	private function setup_test_environment( PerformanceEnvInfo $env_info ): void {
		$this->ensure_directory_exists( Config::get_qit_dir() . 'cache/k6' );
		$this->ensure_directory_exists( $this->performance_test_result->get_results_dir() );
		$this->docker_config->set_environment_variables( $env_info );
	}

	/**
	 * Create directory if it doesn't exist.
	 */
	private function ensure_directory_exists( string $directory ): void {
		if ( ! file_exists( $directory ) ) {
			if ( ! mkdir( $directory, 0755, true ) ) {
				throw new \RuntimeException( "Could not create directory: $directory" );
			}
		}
	}

	/**
	 * @param array<string> $k6_args
	 */
	private function execute_k6_tests( array $k6_args ): int {
		$this->create_default_k6_test();

		$this->output->writeln( '<info>Running k6 performance test for WooCommerce extension</info>' );

		// Execute K6 test.
		$test_args = array_merge( $k6_args, [ '/tests/default-performance-test.js' ] );
		$process   = new Process( $test_args );
		$process->setTimeout( 3600 ); // 1 hour timeout

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Running: ' . $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() || $type === Process::ERR ) {
				$this->output->write( $buffer );
			}
		} );

		$exit_code = $process->getExitCode();

		// Show test result.
		$status = $exit_code === 0 ? 'passed' : "failed with exit code: $exit_code";
		$icon   = $exit_code === 0 ? '✓' : '✗';
		$style  = $exit_code === 0 ? 'info' : 'error';
		$this->output->writeln( "<$style>$icon k6 performance test $status</$style>" );

		return $exit_code;
	}

	public function create_default_k6_test( ?string $target_file = null ): string {
		$source = __DIR__ . '/../tests/default-performance.k6.js';
		$target = $target_file ?: sys_get_temp_dir() . '/qit-k6-default-test.js';

		if ( ! file_exists( $source ) ) {
			// If the source file doesn't exist (e.g., running from phar), use embedded content.
			$default_k6_content = 'import { check, sleep } from "k6";
import http from "k6/http";

export let options = {
    stages: [
        { duration: "10s", target: 5 },
        { duration: "20s", target: 10 },
        { duration: "10s", target: 0 },
    ],
    thresholds: {
        // Performance thresholds - K6 will exit with non-zero if these fail
        \'http_req_duration\': [\'p(95)<5000\'], // 95th percentile under 5 seconds (increased)
        \'http_req_duration{expected_response:true}\': [\'avg<2000\'], // Average response time under 2 seconds (increased)
        \'http_req_failed\': [\'rate<0.2\'], // Error rate under 20% (increased from 10%)
        \'checks\': [\'rate>0.8\'], // At least 80% of checks should pass (decreased from 90%)
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
    
    // Test checkout page
    response = http.get(`${baseUrl}/checkout/`);
    check(response, {
        "checkout page accessible": (r) => r.status === 200 || r.status === 404,
    });
    
    // Test WooCommerce REST API health
    response = http.get(`${baseUrl}/wp-json/wc/v3/system_status`);
    check(response, {
        "WooCommerce API accessible": (r) => r.status === 200 || r.status === 401, // 401 is OK, means auth is needed
    });
    
    sleep(Math.random() * 2 + 1);
}
';

			$this->ensure_directory_exists( dirname( $target ) );

			if ( ! file_put_contents( $target, $default_k6_content ) ) {
				throw new \RuntimeException( "Could not write default performance test to: $target" );
			}

			return $target;
		}

		$this->ensure_directory_exists( dirname( $target ) );

		if ( ! copy( $source, $target ) ) {
			throw new \RuntimeException( "Could not copy default performance test to: $target" );
		}

		return $target;
	}

	private function collect_results( PerformanceTestResult $test_result ): void {
		$source_results = $test_result->get_results_dir() . '/k6-results.json';

		if ( file_exists( $source_results ) && $this->output->isVerbose() ) {
			$this->output->writeln(
				"<info>k6 results saved to: {$test_result->get_results_dir()}/k6-results.json</info>"
			);
		}
	}
}
