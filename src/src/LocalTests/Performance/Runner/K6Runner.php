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
			throw new \RuntimeException( "Default performance test file not found: $source" );
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
