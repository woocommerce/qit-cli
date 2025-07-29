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

		$this->setup_test_environment();

		// Build and execute k6 test.
		$k6_args = $this->docker_config->build_k6_docker_args(
			$env_info,
			$test_result->get_results_dir(),
			"qit_env_k6_{$env_info->env_id}",
			$test_infos
		);

		$exit_code = $this->execute_k6_tests( $env_info, $k6_args );

		// Collect and process results.
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
	 * @param PerformanceEnvInfo $env_info
	 * @param array<string>      $k6_args
	 */
	private function execute_k6_tests( PerformanceEnvInfo $env_info, array $k6_args ): int {
		$test_file = $this->determine_test_file( $env_info );

		$this->output->writeln( '<info>Running k6 performance test for WooCommerce extension</info>' );

		// Execute K6 test.
		$test_args = array_merge( $k6_args, [ $test_file ] );
		$process   = new Process( $test_args );
		$process->setTimeout( 3600 ); // 1 hour timeout

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Running: ' . $process->getCommandLine() );
		}

		// Add signal handlers for graceful termination.
		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function () use ( $process ): void {
				// Stop the process gracefully.
				$process->signal( SIGTERM );
			};

			pcntl_signal( SIGINT, $signal_handler );
			pcntl_signal( SIGTERM, $signal_handler );
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

	/**
	 * Determine which test file to use based on environment info.
	 */
	private function determine_test_file( PerformanceEnvInfo $env_info ): string {
		if ( empty( $env_info->tests ) ) {
			throw new \RuntimeException( 'No test directories provided.' );
		}

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( '<info>Debug: Available tests in env_info:</info>' );
			$this->output->writeln( json_encode( $env_info->tests, JSON_PRETTY_PRINT ) );
		}

		$target_file = $env_info->k6_test_file ?? null;

		foreach ( $env_info->tests as $test_info ) {
			$test_file = $this->find_test_file_in_directory( $test_info, $target_file );
			if ( $test_file ) {
				return $test_file;
			}
		}

		// No tests found
		$error_context = $target_file ? "specific test file '{$target_file}'" : 'any K6 test files';
		throw new \RuntimeException(
			"No {$error_context} found for extension: {$env_info->sut_slug} with test tag: " . ( $env_info->test_tag ?: 'default' )
		);
	}

	/**
	 * Find a test file in the given directory.
	 *
	 * @param array<string,string> $test_info Test directory information
	 * @param string|null          $target_file Specific file to search for, or null for any K6 file
	 */
	private function find_test_file_in_directory( array $test_info, ?string $target_file ): ?string {
		$host_path = $test_info['path_in_host'];

		if ( ! is_dir( $host_path ) ) {
			return null;
		}

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( $target_file ? "<info>Debug: Searching for specific test file: {$target_file}</info>" : '<info>Debug: Searching for any K6 test file</info>' );
		}

		$directory = new \RecursiveDirectoryIterator( $host_path, \RecursiveDirectoryIterator::SKIP_DOTS );
		$iterator  = new \RecursiveIteratorIterator( $directory );

		// Apply regex filter for K6 files only if no specific file is requested
		if ( ! $target_file ) {
			$iterator = new \RegexIterator( $iterator, '/\.k6\.js$/i' );
		}

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}

			$relative_path = str_replace( $host_path . '/', '', $file->getPathname() );

			// Check if this file matches our criteria
			if ( $this->is_matching_test_file( $relative_path, $target_file ) ) {
				$container_path = $test_info['path_in_php_container'] . '/' . $relative_path;

				$test_type = $target_file ? 'specific performance' : 'performance';
				$this->output->writeln( "<info>Using {$test_type} test: {$relative_path}</info>" );

				if ( $this->output->isVerbose() ) {
					$this->output->writeln( "<info>Debug: Found test file: {$container_path}</info>" );
				}

				return $container_path;
			}
		}

		if ( $target_file && $this->output->isVerbose() ) {
			$this->output->writeln( "<info>Debug: Specific test file '{$target_file}' not found in {$host_path}</info>" );
		}

		return null;
	}

	/**
	 * Check if a file matches the search criteria.
	 */
	private function is_matching_test_file( string $relative_path, ?string $target_file ): bool {
		if ( ! $target_file ) {
			// Any K6 file is acceptable (regex filter already applied)
			return true;
		}

		// For specific files, check exact match or basename match
		return $relative_path === $target_file || basename( $relative_path ) === $target_file;
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
