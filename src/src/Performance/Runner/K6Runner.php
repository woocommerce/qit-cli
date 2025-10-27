<?php

namespace QIT_CLI\Performance\Runner;

use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Performance\Result\PerformanceTestResult;
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

		$result_filenames = $test_result->get_result_filenames();
		$k6_command       = $this->get_k6_command_from_manifest( $env_info );

		$k6_args = $this->docker_config->build_k6_docker_args(
			$env_info,
			$test_result->get_results_dir(),
			"qit_env_k6_{$env_info->env_id}",
			$k6_command,
			$result_filenames['dashboard'] ?? null
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
		$package_info = $this->get_test_package_info( $env_info );
		$manifest     = $this->read_manifest( $package_info );
		$run_command  = $manifest['test']['phases']['run'][0] ?? null;

		if ( ! $run_command ) {
			throw new \RuntimeException( 'Invalid test package manifest: missing test.phases.run' );
		}

		$command_parts = $this->parse_command_string( $run_command );
		$command_parts = $this->convert_test_file_to_container_path( $command_parts, $package_info );

		return $this->inject_output_options( $command_parts, $manifest );
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
	 * Read and parse the test package manifest.
	 *
	 * @param array{path: string, container_path: string} $pkg_info
	 * @return array<mixed>
	 */
	private function read_manifest( array $pkg_info ): array {
		$manifest_path = $pkg_info['path'] . '/qit-test.json';

		if ( ! file_exists( $manifest_path ) ) {
			throw new \RuntimeException(
				"Test package manifest not found at: {$manifest_path}"
			);
		}

		$manifest = json_decode( file_get_contents( $manifest_path ), true );
		if ( ! $manifest ) {
			throw new \RuntimeException( 'Invalid test package manifest: invalid JSON' );
		}

		return $manifest;
	}

	/**
	 * Inject k6 output options based on manifest results section.
	 *
	 * Maps manifest result keys to k6 CLI arguments.
	 *
	 * @param array<string> $command_parts The k6 command parts.
	 * @param array<mixed>  $manifest The test package manifest.
	 * @return array<string> Command with injected output options.
	 */
	private function inject_output_options( array $command_parts, array $manifest ): array {
		$results = $manifest['test']['results'] ?? [];

		if ( empty( $results ) ) {
			return $command_parts;
		}

		$output_options = [];

		if ( isset( $results['json'] ) ) {
			$output_options[] = '--out';
			$output_options[] = 'json=' . $results['json'];
		}

		if ( isset( $results['summary'] ) ) {
			$output_options[] = '--summary-export';
			$output_options[] = $results['summary'];
		}

		if ( ! empty( $output_options ) ) {
			$test_file     = array_pop( $command_parts );
			$command_parts = array_merge( $command_parts, $output_options, [ $test_file ] );
		}

		return $command_parts;
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
	 * @param array<string>                               $command_parts
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

	/**
	 * Get result filenames from the test package manifest.
	 *
	 * @param PerformanceEnvInfo $env_info
	 * @return array{summary: string, json: string, dashboard: string}
	 * @throws \RuntimeException If required result paths are not defined.
	 */
	public function get_result_filenames_from_manifest( PerformanceEnvInfo $env_info ): array {
		$pkg_info = $this->get_test_package_info( $env_info );
		$manifest = $this->read_manifest( $pkg_info );
		return $this->extract_result_filenames( $manifest );
	}

	/**
	 * Extract result filenames from manifest.
	 *
	 * @param array<mixed> $manifest
	 * @return array{summary: string, json: string, dashboard: string}
	 * @throws \RuntimeException If required result paths are not defined.
	 */
	private function extract_result_filenames( array $manifest ): array {
		// Validate that results section exists.
		if ( ! isset( $manifest['test']['results'] ) ) {
			throw new \RuntimeException(
				'Performance test manifest must define test.results section with "summary" and "json" fields. ' .
				'Example: "results": {"summary": "/results/result.json", "json": "/results/result-extended.json", "dashboard": "/results/dashboard-report.html"}'
			);
		}

		$results = $manifest['test']['results'];

		// Validate that summary is defined (required for performance tests).
		if ( ! isset( $results['summary'] ) || empty( $results['summary'] ) ) {
			throw new \RuntimeException(
				'Performance test manifest must define test.results.summary field. ' .
				'Example: "results": {"summary": "/results/result.json", "json": "/results/result-extended.json"}'
			);
		}

		// Validate that json is defined (required for detailed metrics).
		if ( ! isset( $results['json'] ) || empty( $results['json'] ) ) {
			throw new \RuntimeException(
				'Performance test manifest must define test.results.json field. ' .
				'Example: "results": {"summary": "/results/result.json", "json": "/results/result-extended.json"}'
			);
		}

		$filenames = [
			'summary' => basename( $results['summary'] ),
			'json'    => basename( $results['json'] ),
		];

		// dashboard is optional - only include if specified in manifest.
		if ( isset( $results['dashboard'] ) && ! empty( $results['dashboard'] ) ) {
			$filenames['dashboard'] = basename( $results['dashboard'] );
		}

		return $filenames;
	}
}
