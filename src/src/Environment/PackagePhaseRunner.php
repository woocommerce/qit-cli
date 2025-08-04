<?php
namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Executes test‑package phase commands with venue-aware execution.
 * – Supports both host and container execution based on command type
 * – Always streams output (unless --quiet was requested on the main CLI).
 * – Aborts by throwing \RuntimeException on the first non‑zero exit status.
 */
class PackagePhaseRunner {
	private Docker $docker;
	private OutputInterface $output;
	private TestPackageManifestParser $parser;

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
		$this->parser = App::make( TestPackageManifestParser::class );
	}

	/**
	 * Determine execution venue based on command type.
	 * Rule: *.sh → container | anything else → host
	 *
	 * @param string $cmd The command to analyze.
	 * @return string 'container' or 'host'
	 */
	private function determine_execution_venue( string $cmd ): string {
		return str_ends_with( trim( $cmd ), '.sh' ) ? 'container' : 'host';
	}

	/**
	 * Prepare environment variables for test execution.
	 *
	 * @param EnvInfo $env_info Environment information.
	 * @return array<string, string> Environment variables.
	 */
	private function prepare_test_env_vars( EnvInfo $env_info ): array {
		$env_vars = [];

		// Pass QIT_SITE_URL for E2E environments
		if ( $env_info instanceof E2EEnvInfo ) {
			$env_vars['QIT_SITE_URL'] = $env_info->site_url;

			// Pass SUT info
			if ( ! empty( $env_info->sut ) ) {
				$env_vars['QIT_SUT_SLUG'] = $env_info->sut['slug'] ?? '';
				$env_vars['QIT_SUT_TYPE'] = $env_info->sut['type'] ?? '';

				// Get SUT entrypoint from plugin or theme info
				if ( isset( $env_info->sut['type'] ) && $env_info->sut['type'] === 'plugin' ) {
					foreach ( $env_info->plugins as $plugin ) {
						$slug       = $plugin->slug;
						$entrypoint = $plugin->entrypoint;
						if ( $slug === ( $env_info->sut['slug'] ?? '' ) ) {
							$env_vars['QIT_SUT_ENTRYPOINT'] = $entrypoint;
							break;
						}
					}
				} elseif ( isset( $env_info->sut['type'] ) && $env_info->sut['type'] === 'theme' ) {
					foreach ( $env_info->themes as $theme ) {
						$slug       = $theme->slug;
						$entrypoint = $theme->entrypoint;
						if ( $slug === ( $env_info->sut['slug'] ?? '' ) ) {
							$env_vars['QIT_SUT_ENTRYPOINT'] = $entrypoint;
							break;
						}
					}
				}

				// Pass plugin activation stack as JSON
				$plugin_activation_stack = [];
				foreach ( array_reverse( $env_info->plugins ) as $plugin ) {
					$slug                      = $plugin->slug;
					$plugin_activation_stack[] = $slug;
				}
				$env_vars['QIT_PLUGIN_ACTIVATION_STACK'] = json_encode( $plugin_activation_stack );
			}
		}

		return $env_vars;
	}

	/**
	 * Execute a command on the host system
	 *
	 * @param string                $cmd Command to execute.
	 * @param string                $package_path Working directory for the command.
	 * @param array<string, string> $env_vars Environment variables.
	 * @return array{exit_code: int, duration: float, stdout: string, stderr: string} Execution data.
	 * @throws \RuntimeException On command failure.
	 */
	private function run_on_host( string $cmd, string $package_path, array $env_vars = [] ): array {
		$start_time = microtime( true );
		$process    = new Process( [ 'bash', '-c', $cmd ], $package_path, $env_vars, null, 300 );

		$process->run( function ( $type, $buffer ) {
			if ( ! $this->output->isQuiet() ) {
				$this->output->write( $buffer );
			}
		} );

		$end_time = microtime( true );
		$duration = ( $end_time - $start_time ) * 1000; // Convert to milliseconds

		$execution_data = [
			'exit_code' => $process->getExitCode(),
			'duration'  => $duration,
			'stdout'    => $process->getOutput(),
			'stderr'    => $process->getErrorOutput(),
		];

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException(
				"Host command failed: {$cmd}\nExit code: {$process->getExitCode()}\nOutput: {$process->getOutput()}\nError: {$process->getErrorOutput()}"
			);
		}

		return $execution_data;
	}

	/**
	 * Execute a command inside Docker container
	 *
	 * @param string                $cmd Command to execute.
	 * @param EnvInfo               $env_info Environment information.
	 * @param string                $package_id Package identifier.
	 * @param string                $workdir Working directory inside container.
	 * @param array<string, string> $env_vars Environment variables.
	 * @return array{exit_code: int, duration: float, stdout: string, stderr: string} Execution data.
	 * @throws \RuntimeException On command failure.
	 */
	private function run_in_docker( string $cmd, EnvInfo $env_info, string $package_id, string $workdir, array $env_vars = [] ): array {
		$wrapped    = [ '/bin/bash', '-c', "cd {$workdir} && {$cmd}" ];
		$start_time = microtime( true );
		$stdout     = '';
		$stderr     = '';
		$exit_code  = 0;

		try {
			$stdout = $this->docker->run_inside_docker(
				$env_info,
				$wrapped,
				$env_vars,      // extra env‑vars
				null,           // user
				300,            // timeout
				'php',          // container
				true            // force_output  → always stream
			);
		} catch ( \RuntimeException $e ) {
			// Extract exit code from exception message if possible
			if ( preg_match( '/exited with (\d+)/', $e->getMessage(), $matches ) ) {
				$exit_code = (int) $matches[1];
			} else {
				$exit_code = 1; // Default non-zero exit code
			}
			$stderr = $e->getMessage();
			// Re-throw to maintain existing behavior
			throw $e;
		}

		$end_time = microtime( true );
		$duration = ( $end_time - $start_time ) * 1000; // Convert to milliseconds

		return [
			'exit_code' => $exit_code,
			'duration'  => $duration,
			'stdout'    => $stdout,
			'stderr'    => $stderr,
		];
	}

	/**
	 * Generate individual CTRF file for a single bash script execution
	 *
	 * @param string               $package_path Package directory path.
	 * @param TestPackageManifest  $manifest Package manifest.
	 * @param string               $phase Phase name.
	 * @param array<string, mixed> $script_execution Script execution data.
	 */
	private function generate_individual_bash_script_ctrf(
		string $package_path,
		TestPackageManifest $manifest,
		string $phase,
		array $script_execution,
		?string $artifacts_dir = null
	): void {
		$debug_msg = "DEBUG: generate_individual_bash_script_ctrf called for phase: $phase, script: " . $script_execution['script'];
		file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );

		// Get CTRF file path from manifest
		$test_results = $manifest->getTestResults();
		$ctrf_path    = $test_results['ctrf-json'] ?? null;

		$debug_msg = 'DEBUG: CTRF path from manifest: ' . ( $ctrf_path ?? 'null' );
		file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );

		if ( ! $ctrf_path ) {
			$debug_msg = 'DEBUG: No CTRF configuration, returning early';
			file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );
			return; // No CTRF configuration
		}

		// Create unique filename for this script execution
		$script_name     = basename( $script_execution['script'], '.sh' );
		$unique_filename = $phase . '_' . $script_name . '_' . uniqid() . '.json';

		// Use artifacts directory if provided, otherwise fall back to package directory
		if ( $artifacts_dir ) {
			$ctrf_dir             = $artifacts_dir . '/ctrf';
			$individual_ctrf_path = $ctrf_dir . '/' . $unique_filename;
		} else {
			// Fallback to package directory (original behavior)
			$ctrf_dir             = dirname( $package_path . '/' . ltrim( $ctrf_path, './' ) );
			$individual_ctrf_path = $ctrf_dir . '/' . $unique_filename;
		}

		$debug_msg = "DEBUG: Individual CTRF path: $individual_ctrf_path (artifacts_dir: " . ( $artifacts_dir ?? 'null' ) . ')';
		file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );

		// Ensure directory exists
		if ( ! is_dir( $ctrf_dir ) ) {
			mkdir( $ctrf_dir, 0755, true );
		}

		// Generate standalone CTRF structure for this script
		$ctrf_data = [
			'results' => [
				'tool'    => [
					'name' => 'qit-bash-scripts',
				],
				'summary' => [
					'tests'   => 1,
					'passed'  => $script_execution['exit_code'] === 0 ? 1 : 0,
					'failed'  => $script_execution['exit_code'] === 0 ? 0 : 1,
					'pending' => 0,
					'skipped' => 0,
					'other'   => 0,
					'start'   => time() * 1000,
					'stop'    => time() * 1000,
					'suites'  => 0,
				],
				'tests'   => [
					[
						'name'     => basename( $script_execution['script'] ),
						'status'   => $script_execution['exit_code'] === 0 ? 'passed' : 'failed',
						'duration' => (int) round( $script_execution['duration'] ),
						'start'    => time() * 1000,
						'stop'     => time() * 1000,
						'type'     => 'script',
						'filePath' => $script_execution['script'],
						'stdout'   => array_filter( explode( "\n", $script_execution['stdout'] ) ),
						'stderr'   => array_filter( explode( "\n", $script_execution['stderr'] ) ),
						'extra'    => [
							'phase'       => $phase,
							'packageSlug' => basename( $package_path ),
							'testType'    => $manifest->getTestType(),
							'namespace'   => $manifest->getNamespace(),
							'scriptType'  => 'bash',
						],
					],
				],
			],
		];

		// Save individual CTRF file
		$debug_msg = "DEBUG: About to write CTRF file to: $individual_ctrf_path";
		file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );

		$result = file_put_contents( $individual_ctrf_path, json_encode( $ctrf_data, JSON_PRETTY_PRINT ) );

		$debug_msg = 'DEBUG: File write result: ' . ( $result !== false ? "SUCCESS ($result bytes)" : 'FAILED' );
		file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );

		if ( file_exists( $individual_ctrf_path ) ) {
			$debug_msg = 'DEBUG: File exists after write: YES';
		} else {
			$debug_msg = 'DEBUG: File exists after write: NO';
		}
		file_put_contents( '/tmp/qit_debug.log', $debug_msg . "\n", FILE_APPEND );
	}

	/**
	 * Execute a specific phase for a test package
	 *
	 * @param EnvInfo     $env_info Environment information.
	 * @param string      $phase Phase name (setup, run, teardown, globalSetup, globalTeardown).
	 * @param string      $package_id Package identifier.
	 * @param string      $package_path Package directory path.
	 * @param string|null $artifacts_dir Artifacts directory for CTRF files.
	 * @return int Number of commands that were actually executed.
	 * @throws \RuntimeException On command failure.
	 */
	public function run_phase(
		EnvInfo $env_info,
		string $phase,
		string $package_id,
		string $package_path,
		?string $artifacts_dir = null
	): int {
		$manifest_path = $package_path . '/manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			$this->output->writeln(
				"<comment>Package {$package_id} has no manifest.json – skipping {$phase} phase.</comment>"
			);
			return 0;
		}

		$manifest = $this->parser->parse( $manifest_path );
		$commands = $manifest->getPhaseCommands( $phase );

		if ( empty( $commands ) ) {
			return 0;
		}

		// Determine the container workdir
		// For local packages mounted as volumes, use the container path from metadata
		// For downloaded packages, they're extracted directly to the container, so use the package path
		if ( isset( $env_info->test_packages_metadata[ $package_id ]['container_path'] ) ) {
			// This is a volume-mounted package
			$workdir = $env_info->test_packages_metadata[ $package_id ]['container_path'];
		} else {
			// This is a downloaded package extracted in the container
			// Use the same logic as before for backwards compatibility
			$workdir = '/qit/packages/' . basename( $package_id );
		}

		$this->output->writeln( "  <info>• {$package_id} ({$phase})</info>" );

		// Debug output
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( "    Package ID: {$package_id}" );
			$this->output->writeln( "    Host path: {$package_path}" );
			$this->output->writeln( "    Container workdir: {$workdir}" );
			if ( isset( $env_info->test_packages_metadata[ $package_id ]['container_path'] ) ) {
				$this->output->writeln( '    Using volume mount' );
			} else {
				$this->output->writeln( '    Using extracted package' );
			}
		}

		$executed = 0;
		foreach ( $commands as $cmd ) {
			$venue          = $this->determine_execution_venue( $cmd );
			$is_bash_script = $venue === 'container'; // Bash scripts run in container

			// Prepare environment variables for test execution
			$env_vars = $this->prepare_test_env_vars( $env_info );

			try {
				if ( $venue === 'host' ) {
					$execution_data = $this->run_on_host( $cmd, $package_path, $env_vars );
				} else {
					$execution_data = $this->run_in_docker( $cmd, $env_info, $package_id, $workdir, $env_vars );
				}

				// Generate individual CTRF immediately for bash scripts
				if ( $is_bash_script ) {
					$script_execution = array_merge( $execution_data, [ 'script' => $cmd ] );
					$this->generate_individual_bash_script_ctrf( $package_path, $manifest, $phase, $script_execution, $artifacts_dir );
				}
			} catch ( \RuntimeException $e ) {
				// Generate CTRF for failed bash scripts too
				if ( $is_bash_script ) {
					$failed_execution = [
						'script'    => $cmd,
						'exit_code' => 1,
						'duration'  => 0,
						'stdout'    => '',
						'stderr'    => $e->getMessage(),
					];
					$this->generate_individual_bash_script_ctrf( $package_path, $manifest, $phase, $failed_execution, $artifacts_dir );
				}
				throw $e;
			}

			++$executed;
		}

		return $executed;
	}
}
