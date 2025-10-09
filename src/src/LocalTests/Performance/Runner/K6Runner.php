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
 * This class handles k6-specific performance test execution and configuration.
 * k6-specific settings like test duration, virtual users, and test scenarios
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
	 * @param PerformanceTestResult $test_result
	 */
	public function run_test( PerformanceEnvInfo $env_info, PerformanceTestResult $test_result ): int {
		$this->performance_test_result = $test_result;

		$this->setup_test_environment();

		$k6_command = $this->get_k6_command_from_manifest( $env_info );

		$k6_args = $this->docker_config->build_k6_docker_args(
			$env_info,
			$test_result->get_results_dir(),
			"qit_env_k6_{$env_info->env_id}",
			$k6_command
		);

		$exit_code = $this->execute_k6_tests( $k6_args );

		$this->collect_results( $test_result );
		$test_result->process_results();

		return $exit_code;
	}

	/**
	 * Setup test environment - create directories.
	 */
	private function setup_test_environment(): void {
		$this->ensure_directory_exists( Config::get_qit_dir() . 'cache/k6' );
		$this->ensure_directory_exists( $this->performance_test_result->get_results_dir() );
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
		$this->output->writeln( '<info>Running k6 performance test for WooCommerce extension</info>' );
		$this->output->writeln( '<comment>Live dashboard available at: http://localhost:5665</comment>' );

		$process = new Process( $k6_args );
		$process->setTimeout( 3600 );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Running: ' . $process->getCommandLine() );
		}

		// Add signal handlers for graceful termination.
		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function () use ( $process ): void {
				$process->signal( SIGTERM );
			};

			pcntl_signal( SIGINT, $signal_handler );
			pcntl_signal( SIGTERM, $signal_handler );
		}

		$process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() || $type === Process::OUT || $type === Process::ERR ) {
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

	/**
	 * Get k6 command array from test package manifest.
	 *
	 * @return array<string> k6 command parts (e.g., ['k6', 'run', '--out', 'json=/qit/results/performance.json', 'scenarios/default.js'])
	 */
	private function get_k6_command_from_manifest( PerformanceEnvInfo $env_info ): array {
		$package_info  = $this->get_test_package_info( $env_info );
		$run_command   = $this->read_run_command_from_manifest( $package_info );
		$command_parts = $this->parse_command_string( $run_command );

		return $this->convert_test_file_to_container_path( $command_parts, $package_info );
	}

	/**
	 * Get the test package info from env_info.
	 *
	 * @param PerformanceEnvInfo $env_info
	 * @return array{path: string, container_path: string}
	 */
	private function get_test_package_info( PerformanceEnvInfo $env_info ): array {
		if ( empty( $env_info->test_packages_for_setup ) ) {
			throw new \RuntimeException(
				'No test packages available. Please provide a test package via --test-package option.'
			);
		}

		return reset( $env_info->test_packages_for_setup );
	}

	/**
	 * Read the run command from the test package manifest.
	 *
	 * @param array{path: string, container_path: string} $pkg_info
	 * @return string
	 */
	private function read_run_command_from_manifest( array $pkg_info ): string {
		$manifest_path = $pkg_info['path'] . '/qit-test.json';

		if ( ! file_exists( $manifest_path ) ) {
			throw new \RuntimeException(
				"Test package manifest not found at: {$manifest_path}"
			);
		}

		$manifest = json_decode( file_get_contents( $manifest_path ), true );
		if ( ! $manifest || ! isset( $manifest['test']['phases']['run'][0] ) ) {
			throw new \RuntimeException(
				'Invalid test package manifest: missing test.phases.run'
			);
		}

		return $manifest['test']['phases']['run'][0];
	}

	/**
	 * Parse a command string into an array of arguments.
	 *
	 * @param string $command_string
	 * @return array<string>
	 */
	private function parse_command_string( string $command_string ): array {
		preg_match_all( '/(?:"[^"]*"|\'[^\']*\'|[^\s]+)/', $command_string, $matches );

		return array_map( function ( $part ) {
			return trim( $part, '\'"' );
		}, $matches[0] );
	}

	/**
	 * Convert relative paths in command to absolute container paths.
	 *
	 * @param array<string> $command_parts
	 * @param array{path: string, container_path: string} $package_info
	 * @return array<string>
	 */
	private function convert_test_file_to_container_path( array $command_parts, array $package_info ): array {
		foreach ( $command_parts as $index => $part ) {
			// Convert relative paths (not starting with /) to absolute container paths.
			if ( str_ends_with( $part, '.js' ) && ! str_starts_with( $part, '/' ) ) {
				$command_parts[ $index ] = $package_info['container_path'] . '/' . $part;
				$this->output->writeln( "<info>Using test file: {$command_parts[$index]}</info>" );
			}
		}

		return $command_parts;
	}

	private function collect_results( PerformanceTestResult $test_result ): void {
		$source_results = $test_result->get_results_dir() . '/result-extended.json';

		if ( file_exists( $source_results ) && $this->output->isVerbose() ) {
			$this->output->writeln(
				"<info>k6 results saved to: {$test_result->get_results_dir()}/result.json</info>"
			);
		}
	}
}
