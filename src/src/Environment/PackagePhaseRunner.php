<?php
namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use Symfony\Component\Console\Output\OutputInterface;
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
	private EnvironmentVars $environment_vars;

	public function __construct( Docker $docker, OutputInterface $output, EnvironmentVars $environment_vars, TestPackageManifestParser $parser ) {
		$this->docker           = $docker;
		$this->output           = $output;
		$this->environment_vars = $environment_vars;
		$this->parser           = $parser;
	}

	/**
	 * Determine execution venue based on command type.
	 * Determine where a command should run based on its content.
	 *
	 * @param string $cmd The command to analyze.
	 * @return string 'docker' or 'host'
	 */
	private function determine_execution_venue( string $cmd ): string {
		$trimmed = trim( $cmd );

		// Check for explicit venue prefix
		if ( str_starts_with( $trimmed, 'host:' ) ) {
			return 'host';
		}
		if ( str_starts_with( $trimmed, 'docker:' ) ) {
			return 'docker';
		}

		// npm/npx commands should run on host (where Node.js is installed)
		if ( str_starts_with( $trimmed, 'npm ' ) || str_starts_with( $trimmed, 'npx ' ) ) {
			return 'host';
		}

		// Everything else runs in docker (WordPress environment)
		return 'docker';
	}

	/**
	 * Prepare environment variables for test execution.
	 *
	 * @param EnvInfo             $env_info Environment information.
	 * @param PackageOrchestrator $orchestrator Orchestrator with secret manager.
	 * @return array<string, string> Environment variables.
	 */
	private function prepare_test_env_vars( EnvInfo $env_info, PackageOrchestrator $orchestrator ): array {
		$env_vars = [];

		// Use centralized environment variable mapping for E2E environments
		if ( $env_info instanceof E2EEnvInfo ) {
			// Get base environment variables from centralized mapping
			$env_vars = $this->environment_vars->get_mapping( $env_info );

			// Add environment variables and secrets from EnvironmentManager if available
			$environment_manager = $orchestrator->get_environment_manager();
			if ( $environment_manager ) {
				// Get all env vars (includes both regular env vars and secrets)
				$managed_env_vars = $environment_manager->get_host_env();
				$env_vars         = array_merge( $env_vars, $managed_env_vars );
			}

			// Add SUT-specific variables (these are test-specific, not general env vars)
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
	 * @param PackageOrchestrator   $orchestrator Orchestrator for output formatting.
	 * @param array<string, string> $env_vars Environment variables.
	 * @param string                $phase The phase being executed (for timeout calculation).
	 * @param int|null              $cmd_timeout Optional command-specific timeout override.
	 * @return array{exit_code: int, duration: float, stdout: string, stderr: string} Execution data.
	 * @throws \RuntimeException On command failure.
	 */
	private function run_on_host( string $cmd, string $package_path, PackageOrchestrator $orchestrator, array $env_vars = [], string $phase = 'run', ?int $cmd_timeout = null ): array {
		$start_time = microtime( true );
		$timeout    = $cmd_timeout !== null ? $cmd_timeout : $this->get_timeout_for_phase( $phase );
		$process    = new Process( [ 'bash', '-c', $cmd ], $package_path, $env_vars, null, $timeout );

		// if on 'run' phase, add 'DEBUG=pw:api' env var
		// if ( $phase === 'run' && strpos( $cmd, 'playwright test' ) !== false && ! array_key_exists( 'DEBUG', $env_vars ) ) {
		// $process->setEnv( array_merge( $env_vars, [ 'DEBUG' => 'pw:api' ] ) );
		// }

		// Buffer to accumulate incomplete lines across chunks
		$line_buffer = '';
		$skip_mode   = false;

		// Store process in DI container so signal handler can terminate it
		App::setVar( 'qit_current_test_process', $process );

		try {
			$process->run( function ( $type, $buffer ) use ( &$line_buffer, &$skip_mode, $orchestrator ) {
				// Append new buffer to any incomplete line from previous chunk
				$full_buffer = $line_buffer . $buffer;
				$lines       = explode( "\n", $full_buffer );

				// The last element might be an incomplete line
				$line_buffer = array_pop( $lines );

				foreach ( $lines as $line ) {
					// Let orchestrator parse the line
					if ( $orchestrator->parse_line( $line ) ) {
						continue; // Line was handled by orchestrator
					}

					// Skip any line containing playwright show-report command
					if ( strpos( $line, 'npx playwright show-report' ) !== false ) {
						continue;
					}

					// Skip "To open last HTML report run:" and the line after it
					if ( strpos( $line, 'To open last HTML report run:' ) !== false ) {
						$skip_mode = true;
						continue;
					}

					// If in skip mode, skip the next non-empty line (which should be the npx command)
					if ( $skip_mode ) {
						// Skip empty lines while in skip mode
						if ( trim( $line ) === '' ) {
							continue;
						}
						// Found non-empty line, skip it and exit skip mode
						$skip_mode = false;
						continue;
					}

					// Note: All output is handled by orchestrator, no fallback output needed
				}
			} );
		} finally {
			// Clear the process reference from DI container
			App::setVar( 'qit_current_test_process', null );
		}

		// Process any remaining content in the buffer
		if ( ! $this->output->isQuiet() && ! empty( $line_buffer ) ) {
			if ( ! $skip_mode || ( strpos( $line_buffer, 'npx playwright show-report' ) === false ) ) {
				$this->output->write( $line_buffer );
			}
		}

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
				"Host command failed:\n{$cmd}\nExit code: {$process->getExitCode()}"
			);
		}

		return $execution_data;
	}

	/**
	 * Get timeout for a specific phase
	 *
	 * @param string $phase The phase name (globalSetup, setup, run, teardown, globalTeardown).
	 * @return int Timeout in seconds
	 */
	private function get_timeout_for_phase( string $phase ): int {
		switch ( $phase ) {
			case 'run':
				// Test execution phase - 30 minutes
				return 1800;
			case 'globalSetup':
			case 'setup':
			case 'teardown':
			case 'globalTeardown':
				// Setup/teardown phases - 5 minutes
				return 300;
			default:
				// Default fallback - 5 minutes
				return 300;
		}
	}

	/**
	 * Execute a command inside Docker container
	 *
	 * @param string                $cmd Command to execute.
	 * @param EnvInfo               $env_info Environment information.
	 * @param string                $package_id Package identifier.
	 * @param string                $workdir Working directory inside container.
	 * @param PackageOrchestrator   $orchestrator Orchestrator for output formatting.
	 * @param array<string, string> $env_vars Environment variables.
	 * @param string                $phase The phase being executed (globalSetup, setup, run, etc).
	 * @param int|null              $cmd_timeout Optional command-specific timeout override.
	 * @return array{exit_code: int, duration: float, stdout: string, stderr: string} Execution data.
	 * @throws \RuntimeException On command failure.
	 */
	private function run_in_docker( string $cmd, EnvInfo $env_info, string $package_id, string $workdir, PackageOrchestrator $orchestrator, array $env_vars = [], string $phase = 'run', ?int $cmd_timeout = null ): array {
		$wrapped    = [ '/bin/bash', '-c', "cd {$workdir} && {$cmd}" ];
		$start_time = microtime( true );
		$stdout     = '';
		$stderr     = '';
		$exit_code  = 0;

		// Create output callback for orchestrator
		$line_buffer     = '';
		$output_callback = function ( $type, $buffer ) use ( &$line_buffer, &$stdout, $orchestrator ) {
				$stdout .= $buffer;

				// Parse line by line for orchestrator
				$full_buffer = $line_buffer . $buffer;
				$lines       = explode( "\n", $full_buffer );
				$line_buffer = array_pop( $lines );

			foreach ( $lines as $line ) {
				if ( $orchestrator->parse_line( $line ) ) {
					continue; // Line was handled by orchestrator
				}
				// Skip playwright show-report lines
				if ( strpos( $line, 'npx playwright show-report' ) !== false ) {
					continue;
				}
				// Default output if not handled
				if ( trim( $line ) !== '' ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw test output
					echo $line . PHP_EOL;
				}
			}
		};

		try {
			$stdout = $this->docker->run_inside_docker(
				$env_info,
				$wrapped,
				$env_vars,      // extra env‑vars
				null,           // user
				$cmd_timeout !== null ? $cmd_timeout : $this->get_timeout_for_phase( $phase ),            // timeout
				'php',          // container
				true,           // force_output  → always stream
				$output_callback // custom output callback
			);
		} catch ( \RuntimeException $e ) {
			// Extract exit code from exception message if possible
			if ( preg_match( '/exited with (\d+)/', $e->getMessage(), $matches ) ) {
				$exit_code = (int) $matches[1];
			} else {
				$exit_code = 1; // Default non-zero exit code
			}
			$stderr = $e->getMessage();

			// Add more context for timeout errors
			if ( strpos( $e->getMessage(), 'timed out' ) !== false || strpos( $e->getMessage(), 'timeout' ) !== false ) {
				$timeout_seconds = $this->get_timeout_for_phase( $phase );
				$timeout_display = $timeout_seconds >= 60 ? ( $timeout_seconds / 60 ) . ' minutes' : $timeout_seconds . ' seconds';
				throw new \RuntimeException(
					"Command timed out after {$timeout_display} in {$phase} phase.\n" .
					"Command: {$cmd}\n" .
					"This may indicate the test is hanging. Check the test logs for more details.\n" .
					'Original error: ' . $e->getMessage(),
					$e->getCode()
				);
			}

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
	 * @param string               $package_id Package identifier (full ID, not basename).
	 * @param string               $package_path Package directory path.
	 * @param TestPackageManifest  $manifest Package manifest.
	 * @param string               $phase Phase name.
	 * @param array<string, mixed> $script_execution Script execution data.
	 */
	private function generate_individual_bash_script_ctrf(
		string $package_id,
		string $package_path,
		TestPackageManifest $manifest,
		string $phase,
		array $script_execution,
		?string $artifacts_dir = null
	): void {
		// Get CTRF file path from manifest
		$test_results = $manifest->get_test_results();
		$ctrf_path    = $test_results['ctrf-json'] ?? null;

		if ( ! $ctrf_path ) {
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
							'packageSlug' => $package_id,
							'testType'    => $manifest->get_test_type(),
							'namespace'   => $manifest->get_namespace(),
							'packageId'   => $manifest->get_package_id(),
							'scriptType'  => 'bash',
						],
					],
				],
			],
		];

		// Save individual CTRF file
		file_put_contents( $individual_ctrf_path, json_encode( $ctrf_data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Execute a specific phase for a test package
	 *
	 * @param EnvInfo                  $env_info Environment information.
	 * @param string                   $phase Phase name (setup, run, teardown, globalSetup, globalTeardown).
	 * @param string                   $package_id Package identifier.
	 * @param string                   $package_path Package directory path.
	 * @param string|null              $artifacts_dir Artifacts directory for CTRF files.
	 * @param PackageOrchestrator      $orchestrator Orchestrator for output formatting.
	 * @param array<string>            $passthrough_args Arguments to pass through to test framework (only used for 'run' phase).
	 * @param TestPackageManifest|null $manifest Optional manifest to use instead of loading from disk (for subpackages).
	 * @return int Number of commands that were actually executed.
	 * @throws \RuntimeException On command failure.
	 */
	public function run_phase(
		EnvInfo $env_info,
		string $phase,
		string $package_id,
		string $package_path,
		?string $artifacts_dir,
		PackageOrchestrator $orchestrator,
		array $passthrough_args = [],
		?TestPackageManifest $manifest = null
	): int {
		// If manifest is provided, use it; otherwise load from disk
		if ( $manifest === null ) {
			$manifest_path = $package_path . '/qit-test.json';
			if ( ! file_exists( $manifest_path ) ) {
				$this->output->writeln(
					"<comment>Package {$package_id} has no qit-test.json – skipping {$phase} phase.</comment>"
				);
				return 0;
			}

			$manifest = $this->parser->parse( $manifest_path );
		}

		$commands = $manifest->get_phase_commands( $phase );

		if ( empty( $commands ) ) {
			return 0;
		}

		// Determine the container workdir
		// For local packages mounted as volumes, use the container path from metadata
		// For downloaded packages, they're extracted directly to the container, so use the package path
		if ( isset( $env_info->test_packages_metadata[ $package_id ]['container_path'] ) ) {
			// This is a volume-mounted package
			// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset - We check it exists above
			$workdir = $env_info->test_packages_metadata[ $package_id ]['container_path'];
		} else {
			// This is a downloaded package extracted in the container
			// Use the centralized method for consistent naming
			$workdir = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $package_id );
		}

		$phase_timeout = $this->get_timeout_for_phase( $phase );
		// Timeout info is now handled by the orchestrator's command display

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
		foreach ( $commands as $cmd_item ) {
			// Parse command - can be string or object
			$cmd                   = '';
			$cmd_timeout           = null;
			$cmd_runs_on           = null;
			$cmd_continue_on_error = false;

			if ( is_string( $cmd_item ) ) {
				// Simple string command
				$cmd = $cmd_item;
			} elseif ( is_array( $cmd_item ) ) {
				// Object command format
				if ( ! isset( $cmd_item['command'] ) ) {
					throw new \RuntimeException( 'Invalid command format. Array must have "command" field.' );
				}
				$cmd                   = $cmd_item['command'];
				$cmd_timeout           = isset( $cmd_item['timeout'] ) ? (int) $cmd_item['timeout'] : null;
				$cmd_runs_on           = isset( $cmd_item['runs_on'] ) ? $cmd_item['runs_on'] : null;
				$cmd_continue_on_error = isset( $cmd_item['continue_on_error'] ) ? (bool) $cmd_item['continue_on_error'] : false;
			} else {
				// Invalid command format (neither string nor array)
				throw new \RuntimeException( 'Invalid command format. Must be string or array with "command" field.' );
			}

			// Append passthrough_args to commands in the 'run' phase
			if ( $phase === 'run' && ! empty( $passthrough_args ) ) {
				// Filter out shard arguments with warning
				$filtered_args  = [];
				$shard_detected = false;

				foreach ( $passthrough_args as $arg ) {
					if ( strpos( $arg, '--shard' ) === 0 || strpos( $arg, '-shard' ) === 0 ) {
						$shard_detected = true;
						// Skip this argument
					} else {
						$filtered_args[] = $arg;
					}
				}

				if ( $shard_detected ) {
					$this->output->writeln( '<warning>Warning: --shard is not supported with Test Packages.</warning>' );
					$this->output->writeln( '<warning>         Tests will run without sharding.</warning>' );
				}

				if ( ! empty( $filtered_args ) ) {
					// Append filtered args to the command
					$cmd = $cmd . ' ' . implode( ' ', array_map( 'escapeshellarg', $filtered_args ) );
				}
			}

			// Determine execution venue - use explicit runs_on if provided, otherwise auto-detect
			if ( $cmd_runs_on !== null ) {
				if ( $cmd_runs_on === 'docker' ) {
					$venue = 'docker';
				} elseif ( $cmd_runs_on === 'host' ) {
					$venue = 'host';
				} else {
					// Invalid runs_on value, fall back to auto-detect
					$venue = $this->determine_execution_venue( $cmd );
				}
			} else {
				$venue = $this->determine_execution_venue( $cmd );
			}
			$is_bash_script = $venue === 'docker'; // Bash scripts run in docker

			// Strip venue prefix from command if present (after venue detection)
			$trimmed = trim( $cmd );
			if ( str_starts_with( $trimmed, 'host:' ) ) {
				$cmd = trim( substr( $trimmed, 5 ) ); // Remove 'host:' prefix
			} elseif ( str_starts_with( $trimmed, 'docker:' ) ) {
				$cmd = trim( substr( $trimmed, 7 ) ); // Remove 'docker:' prefix
			}

			// Prepare environment variables for test execution
			$env_vars = $this->prepare_test_env_vars( $env_info, $orchestrator );

			// Show command in orchestrator
			$context = $venue; // Now venue is already 'host' or 'docker'
			$orchestrator->show_command( $cmd, $context );

			try {
				if ( $venue === 'host' ) {
					$execution_data = $this->run_on_host( $cmd, $package_path, $orchestrator, $env_vars, $phase, $cmd_timeout );
				} else {
					$execution_data = $this->run_in_docker( $cmd, $env_info, $package_id, $workdir, $orchestrator, $env_vars, $phase, $cmd_timeout );
				}

				// Record lifecycle command for orchestrator CTRF (for non-run phases)
				if ( $phase !== 'run' ) {
					$orchestrator->record_lifecycle_command(
						$execution_data['exit_code'],
						$phase,
						$package_id,
						$manifest->get_namespace(),
						$manifest->get_package_id(),
						$manifest->get_test_type()
					);
				}

				// Generate individual CTRF immediately for bash scripts
				// Only generate for 'run' phase to avoid duplication with lifecycle commands
				if ( $is_bash_script && $phase === 'run' ) {
					$script_execution = array_merge( $execution_data, [ 'script' => $cmd ] );
					// Pass package_id instead of package_path to ensure consistent identification
					$this->generate_individual_bash_script_ctrf( $package_id, $package_path, $manifest, $phase, $script_execution, $artifacts_dir );
				}
			} catch ( \RuntimeException $e ) {
				// If continue_on_error is true, log the error but don't throw
				if ( $cmd_continue_on_error ) {
					$this->output->writeln( '<warning>Command failed but continue_on_error is set, continuing...</warning>' );
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( '<warning>Error: ' . $e->getMessage() . '</warning>' );
					}
					// Record the failure but continue
					if ( $phase !== 'run' ) {
						$orchestrator->record_lifecycle_command(
							1,
							$phase,
							$package_id,
							$manifest->get_namespace(),
							$manifest->get_package_id(),
							$manifest->get_test_type()
						);
					}
					// Still generate CTRF for the failed command (only for run phase)
					if ( $is_bash_script && $phase === 'run' ) {
						$failed_execution = [
							'script'    => $cmd,
							'exit_code' => 1,
							'duration'  => 0,
							'stdout'    => '',
							'stderr'    => $e->getMessage(),
						];
						$this->generate_individual_bash_script_ctrf( $package_id, $package_path, $manifest, $phase, $failed_execution, $artifacts_dir );
					}
					// Continue to next command
					++$executed;
					continue;
				}

				// Record failed lifecycle command for orchestrator CTRF (for non-run phases)
				if ( $phase !== 'run' ) {
					$orchestrator->record_lifecycle_command(
						1,
						$phase,
						$package_id,
						$manifest->get_namespace(),
						$manifest->get_package_id(),
						$manifest->get_test_type()
					);
				}

				// Generate CTRF for failed bash scripts too (only for run phase)
				if ( $is_bash_script && $phase === 'run' ) {
					$failed_execution = [
						'script'    => $cmd,
						'exit_code' => 1,
						'duration'  => 0,
						'stdout'    => '',
						'stderr'    => $e->getMessage(),
					];
					$this->generate_individual_bash_script_ctrf( $package_id, $package_path, $manifest, $phase, $failed_execution, $artifacts_dir );
				}
				throw $e;
			}

			++$executed;
		}

		return $executed;
	}
}
