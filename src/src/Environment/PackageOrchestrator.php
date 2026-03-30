<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\CTRFValidator;
use QIT_CLI\Environment\EnvironmentManager;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * Orchestrates test package execution with beautiful UI using native Symfony Console components.
 */
class PackageOrchestrator {
	private OutputInterface $output;
	private ?ConsoleSectionOutput $package_section = null;
	private ?ConsoleSectionOutput $status_section  = null;
	private int $terminal_width;
	private ?EnvironmentManager $environment_manager = null;
	private bool $suppress_output                    = false;
	private CTRFValidator $ctrf_validator;
	/** @var array<string> */
	private array $suppressed_lines = [];
	private bool $in_ci_environment = false;

	/**
	 * @var array<array{name: string, id: string, status: string, duration: float, extra: array{type: string, phase: string, package: string, exitCode: int, output: string}}>
	 */
	private array $lifecycle_results       = [];
	private ?float $current_phase_start    = null;
	private ?string $current_phase_command = null;
	/** @var array<string> */
	private array $current_phase_output = [];

	/**
	 * @var array{
	 *   current_package: string|null,
	 *   current_phase: string|null,
	 *   packages_total: int,
	 *   packages_completed: int,
	 *   test_totals: array{total: int, passed: int, failed: int, skipped: int, flaky: int},
	 *   current_tests: array<string>,
	 *   test_results: array<array{name: string, status: string, duration: int}>,
	 *   start_time: float|null,
	 *   phase_start_time: float|null,
	 *   test_count: int,
	 *   tests_completed: int,
	 *   phase_lines: array<string>,
	 *   suppress_output: bool,
	 *   current_command?: string|null,
	 *   has_output?: bool
	 * }
	 */
	private array $state = [
		'current_package'    => null,
		'current_phase'      => null,
		'packages_total'     => 0,
		'packages_completed' => 0,
		'test_totals'        => [
			'total'   => 0,
			'passed'  => 0,
			'failed'  => 0,
			'skipped' => 0,
			'flaky'   => 0,
		],
		'current_tests'      => [],
		'test_results'       => [],
		'start_time'         => null,
		'phase_start_time'   => null,
		'test_count'         => 0,
		'tests_completed'    => 0,
		'phase_lines'        => [],
		'suppress_output'    => false,
	];

	public function __construct( OutputInterface $output, CTRFValidator $ctrf_validator ) {
		$this->output         = $output;
		$this->ctrf_validator = $ctrf_validator;

		// Get terminal width for dynamic sizing
		$terminal             = new Terminal();
		$this->terminal_width = min( $terminal->getWidth(), 120 ); // Cap at 120 for readability

		// Create sections if output supports it
		if ( $output instanceof ConsoleOutputInterface ) {
			$this->package_section = $output->section();
			$this->status_section  = $output->section();
		}

		// Detect CI environment
		$this->in_ci_environment = (bool) getenv( 'CI' );

		// Default output suppression based on environment
		$this->configure_output_suppression();
	}

	/**
	 * Configure output suppression based on environment and verbosity.
	 */
	private function configure_output_suppression(): void {
		// In CI, suppress by default unless verbose or failure
		if ( $this->in_ci_environment ) {
			$this->suppress_output = ! $this->output->isVerbose();
		} else {
			// Local development: show output by default, suppress only in quiet mode
			$this->suppress_output = $this->output->isQuiet();
		}

		// Allow override via environment variable
		if ( getenv( 'QIT_SUPPRESS_OUTPUT' ) === 'true' ) {
			$this->suppress_output = true;
		} elseif ( getenv( 'QIT_SUPPRESS_OUTPUT' ) === 'false' ) {
			$this->suppress_output = false;
		}
	}

	/**
	 * Set the environment manager for redaction and env distribution.
	 *
	 * @param EnvironmentManager $environment_manager
	 */
	public function set_environment_manager( EnvironmentManager $environment_manager ): void {
		$this->environment_manager = $environment_manager;
	}

	/**
	 * Get the environment manager.
	 *
	 * @return EnvironmentManager|null
	 */
	public function get_environment_manager(): ?EnvironmentManager {
		return $this->environment_manager;
	}

	/**
	 * Enable or disable output suppression.
	 *
	 * @param bool $suppress
	 */
	public function set_suppress_output( bool $suppress ): void {
		$this->suppress_output = $suppress;
	}

	/**
	 * Start orchestration display
	 */
	public function start( string $environment, int $total_packages ): void {
		$this->state['packages_total'] = $total_packages;
		$this->state['start_time']     = microtime( true );
		// Minimal start - no header needed
	}

	/**
	 * Display global setup phase
	 */
	public function global_setup_start(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '┌─ GLOBAL SETUP ' . str_repeat( '─', max( 0, $line_width - 16 ) ) );
		$this->state['phase_start_time'] = microtime( true );
		$this->state['current_phase']    = 'GLOBAL_SETUP';
	}

	public function global_setup_message( string $message ): void {
		$out = $this->package_section ?? $this->output;
		$out->writeln( '│ ' . $message );
	}

	public function global_setup_end(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $line_width ) );
		$out->writeln( '' );
	}

	/**
	 * Start a new package
	 */
	public function package_start( int $index, string $package_id, string $type = 'Local Package' ): void {
		$this->state['packages_completed'] = $index - 1;
		$this->state['current_package']    = $package_id;
		$this->state['test_results']       = [];
		$this->state['current_command']    = null;

		$out = $this->package_section ?? $this->output;

		// Package header with box drawing (no right border)
		$header     = sprintf( 'PACKAGE [%d/%d]: %s', $index, $this->state['packages_total'], $package_id );
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '┌─ ' . $header . ' ' . str_repeat( '─', max( 0, $line_width - strlen( $header ) - 3 ) ) );
		$out->writeln( '│ Type: ' . $type );
		$out->writeln( '├' . str_repeat( '─', $line_width ) );
	}

	/**
	 * Display phase start
	 */
	public function phase_start( string $phase ): void {
		$this->state['current_phase']    = strtoupper( $phase );
		$this->state['phase_start_time'] = microtime( true );
		// No phase headers - commands will be shown directly
	}

	/**
	 * Show command being executed
	 */
	public function show_command( string $command, string $context = 'docker' ): void {
		$out                            = $this->package_section ?? $this->output;
		$this->state['current_command'] = $command;

		// Track for lifecycle CTRF
		$this->current_phase_command = $command;
		$this->current_phase_start   = microtime( true );
		$this->current_phase_output  = [];

		// Add spacing before new command (except first)
		if ( isset( $this->state['has_output'] ) && $this->state['has_output'] ) {
			$out->writeln( '│' );
		}

		// Redact secrets from the command display if environment manager is available
		$display_command = $command;
		if ( $this->environment_manager ) {
			$display_command = $this->environment_manager->redact( $command );
		}

		$out->writeln( '│ [' . $context . '] ' . $display_command );
		$this->state['has_output'] = false;
	}

	/**
	 * Parse and beautify line output
	 */
	public function parse_line( string $line, bool $is_error = false ): bool {
		// Skip empty lines
		if ( trim( $line ) === '' ) {
			return false;
		}

		// Skip unwanted output lines
		if ( strpos( $line, 'npx playwright show-report' ) !== false ) {
			return true;
		}
		if ( strpos( $line, 'playwright-ctrf-json-reporter: successfully written' ) !== false ) {
			return true;
		}
		if ( strpos( $line, 'To open last HTML report run:' ) !== false ) {
			return true;
		}
		// Skip PHP warnings about debug log
		if ( strpos( $line, 'PHP Warning' ) !== false && strpos( $line, 'qit_debug.log' ) !== false ) {
			return true;
		}
		if ( strpos( $line, 'failed to open stream: No such file or directory' ) !== false && strpos( $line, 'qit_debug.log' ) !== false ) {
			return true;
		}

		// Redact secrets if environment manager is available
		if ( $this->environment_manager ) {
			$line = $this->environment_manager->redact( $line );
		}

		// Track output for lifecycle CTRF
		$this->current_phase_output[] = $line;

		// Handle output suppression
		if ( $this->suppress_output && ! $is_error ) {
			// Store suppressed lines for potential later display (e.g., on failure)
			$this->suppressed_lines[] = $line;
			// Keep only last 100 lines to avoid memory issues
			if ( count( $this->suppressed_lines ) > 100 ) {
				array_shift( $this->suppressed_lines );
			}
			return true; // Line was handled but not displayed
		}

		// Output the line
		$out = $this->package_section ?? $this->output;
		$out->writeln( '│   ' . $line );
		$this->state['has_output'] = true;

		return true;
	}

	/**
	 * Show suppressed output (e.g., on failure).
	 *
	 * @param int $max_lines Maximum number of lines to show.
	 */
	public function show_suppressed_output( int $max_lines = 50 ): void {
		if ( empty( $this->suppressed_lines ) ) {
			return;
		}

		$out = $this->package_section ?? $this->output;
		$out->writeln( '│ <comment>[Showing last ' . min( $max_lines, count( $this->suppressed_lines ) ) . ' lines of suppressed output]</comment>' );

		$lines_to_show = array_slice( $this->suppressed_lines, -$max_lines );
		foreach ( $lines_to_show as $line ) {
			$out->writeln( '│   ' . $line );
		}

		// Clear suppressed lines after showing
		$this->suppressed_lines = [];
	}

	/**
	 * Get current orchestrator state
	 *
	 * @return array{
	 *   current_package: string|null,
	 *   current_phase: string|null,
	 *   packages_total: int,
	 *   packages_completed: int,
	 *   test_totals: array{total: int, passed: int, failed: int, skipped: int, flaky: int},
	 *   current_tests: array<string>,
	 *   test_results: array<array{name: string, status: string, duration: int}>,
	 *   start_time: float|null,
	 *   phase_start_time: float|null,
	 *   test_count: int,
	 *   tests_completed: int,
	 *   phase_lines: array<string>,
	 *   suppress_output: bool,
	 *   current_command?: string|null,
	 *   has_output?: bool
	 * }
	 */
	public function get_state(): array {
		return $this->state;
	}

	/**
	 * Update test statistics from CTRF data
	 *
	 * @param array{tests?: int, passed?: int, failed?: int, skipped?: int} $ctrf_summary
	 */
	public function update_test_stats( array $ctrf_summary ): void {
		if ( isset( $ctrf_summary['tests'] ) ) {
			$this->state['test_totals']['total']   = $ctrf_summary['tests'];
			$this->state['test_totals']['passed']  = $ctrf_summary['passed'] ?? 0;
			$this->state['test_totals']['failed']  = $ctrf_summary['failed'] ?? 0;
			$this->state['test_totals']['skipped'] = $ctrf_summary['skipped'] ?? 0;
		}
	}

	/**
	 * End current package
	 */
	public function package_end(): void {
		$out = $this->package_section ?? $this->output;

		// Increment completed count
		++$this->state['packages_completed'];

		// Package footer (no right border)
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $line_width ) );
		$out->writeln( '' );
	}

	/**
	 * Display isolation restore section (database + browser state)
	 */
	public function isolation_restore_start(): void {
		$out = $this->package_section ?? $this->output;
		$out->writeln( '┌─ ISOLATION RESTORE ──────────────────────────────────────────────────────' );
	}

	public function isolation_restore_message( string $message ): void {
		$out = $this->package_section ?? $this->output;
		$out->writeln( '│ ' . $message );
	}

	public function isolation_restore_end(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $line_width ) );
		$out->writeln( '' );
	}


	/**
	 * Display post-processing section
	 */
	public function post_processing_start(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '┌─ POST-PROCESSING ' . str_repeat( '─', max( 0, $line_width - 19 ) ) );
		$this->state['phase_start_time'] = microtime( true );
		$this->state['current_phase']    = 'POST_PROCESSING';
	}

	public function post_processing_message( string $message, bool $success = true ): void {
		$out    = $this->package_section ?? $this->output;
		$symbol = $success ? '✓' : '✗';
		$out->writeln( "│ $symbol $message" );
	}

	public function post_processing_end(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $line_width ) );
		$out->writeln( '' );
	}

	/**
	 * Display global teardown
	 */
	public function global_teardown_start(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '┌─ GLOBAL TEARDOWN ' . str_repeat( '─', max( 0, $line_width - 19 ) ) );
		$this->state['phase_start_time'] = microtime( true );
		$this->state['current_phase']    = 'GLOBAL_TEARDOWN';
	}

	public function global_teardown_message( string $message ): void {
		$out = $this->package_section ?? $this->output;
		$out->writeln( '│ ' . $message );
	}

	public function global_teardown_end(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $line_width ) );
		$out->writeln( '' );
	}

	/**
	 * Display final summary
	 *
	 * @param array{status?: string, local_command?: string, remote_url?: string, html_report_path?: string, artifacts_dir?: string, keep_env?: array{env_id: string, site_url: string}|null} $results
	 */
	public function summary( array $results ): void {
		$duration = microtime( true ) - $this->state['start_time'];
		$out      = $this->status_section ?? $this->output;

		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '' );
		$out->writeln( str_repeat( '═', $line_width ) );
		$out->writeln( 'TEST RESULTS SUMMARY' );
		$out->writeln( str_repeat( '═', $line_width ) );

		// Status
		$status       = isset( $results['status'] ) && $results['status'] === 'passed' ? '✓ PASSED' : '✗ FAILED';
		$status_color = isset( $results['status'] ) && $results['status'] === 'passed' ? 'info' : 'error';
		$out->writeln( sprintf( '<' . $status_color . '>Status:        %s</' . $status_color . '>', $status ) );

		// Package stats
		$out->writeln( sprintf( 'Packages:      %d/%d executed', $this->state['packages_completed'], $this->state['packages_total'] ) );

		// Test stats if available
		$passed  = $this->state['test_totals']['passed'] ?? 0;
		$failed  = $this->state['test_totals']['failed'] ?? 0;
		$skipped = $this->state['test_totals']['skipped'] ?? 0;
		$total   = $this->state['test_totals']['total'] ?? 0;
		if ( $total > 0 ) {
			$out->writeln( sprintf( 'Tests:         %d passed, %d failed, %d skipped', $passed, $failed, $skipped ) );
		}

		$out->writeln( sprintf( 'Duration:      %s', $this->format_duration( $duration ) ) );

		// View results section
		$is_ci = ! empty( getenv( 'CI' ) );
		if ( ! empty( $results['local_command'] ) || ! empty( $results['remote_url'] ) || ( isset( $results['status'] ) && $results['status'] === 'failed' ) || $is_ci ) {
			$out->writeln( '' );
			$out->writeln( 'View Results:' );
			if ( ! empty( $results['local_command'] ) ) {
				$out->writeln( '• Local Report:  <comment>' . $results['local_command'] . '</comment>' );
			}
			if ( ! empty( $results['html_report_path'] ) ) {
				$out->writeln( '• HTML Report:   <comment>file://' . $results['html_report_path'] . '</comment>' );
			}
			if ( ! empty( $results['remote_url'] ) ) {
				$out->writeln( '• Remote URL:    <comment>' . $results['remote_url'] . '</comment>' );
			} elseif ( $is_ci && empty( $results['remote_url'] ) && isset( $results['status'] ) ) {
				// In CI mode with no URL shown, add a note
				$out->writeln( '• Remote URL:    <comment>Hidden in CI (use --print-report-url to show)</comment>' );
			}
			// Add debug info for failures
			if ( isset( $results['status'] ) && $results['status'] === 'failed' ) {
				if ( ! empty( $results['artifacts_dir'] ) ) {
					$out->writeln( '• Artifacts:     <comment>' . $results['artifacts_dir'] . '</comment>' );
				}
				if ( ! empty( $results['keep_env'] ) ) {
					$out->writeln( '' );
					$out->writeln( 'Debug with Playwright MCP:' );
					$out->writeln( '• Site URL:      <comment>' . $results['keep_env']['site_url'] . '</comment>' );
					$out->writeln( '• Re-run tests:  <comment>source $(qit env:source ' . $results['keep_env']['env_id'] . ') && npx playwright test</comment>' );
					$out->writeln( '• Shut down:     <comment>qit env:down ' . $results['keep_env']['env_id'] . '</comment>' );
				} else {
					$out->writeln( '• Tip:           <comment>Re-run with --keep-env to debug with Playwright MCP</comment>' );
				}
			}
		}
		$out->writeln( str_repeat( '═', $line_width ) );
	}

	/**
	 * Format duration in human readable format
	 */
	private function format_duration( float $seconds ): string {
		if ( $seconds < 1 ) {
			return round( $seconds * 1000 ) . 'ms';
		} elseif ( $seconds < 60 ) {
			return round( $seconds, 1 ) . 's';
		} else {
			$minutes = floor( $seconds / 60 );
			$secs    = round( fmod( $seconds, 60 ) );
			return "{$minutes}m {$secs}s";
		}
	}

	/**
	 * Record a lifecycle command completion for CTRF generation.
	 *
	 * @param int    $exit_code Exit code of the command.
	 * @param string $phase     Phase name (globalSetup, setup, run, teardown, globalTeardown).
	 * @param string $package    Package identifier (filesystem path).
	 * @param string $pkg_namespace  Namespace from manifest.
	 * @param string $package_id Package ID from manifest (required).
	 * @param string $test_type  Test type from manifest.
	 */
	public function record_lifecycle_command( int $exit_code, string $phase, string $package, string $pkg_namespace, string $package_id, string $test_type = 'unknown' ): void {
		if ( ! $this->current_phase_command || ! $this->current_phase_start ) {
			return;
		}

		// Validate required fields
		if ( empty( $package_id ) ) {
			throw new \InvalidArgumentException( 'packageId is required for lifecycle commands' );
		}

		$duration = ( microtime( true ) - $this->current_phase_start ) * 1000; // Convert to milliseconds

		// Truncate output to first 1KB
		$output = implode( "\n", array_slice( $this->current_phase_output, 0, 20 ) );
		if ( strlen( $output ) > 1000 ) {
			$output = substr( $output, 0, 997 ) . '...';
		}

		$this->lifecycle_results[] = [
			'name'     => $this->current_phase_command,
			'id'       => sprintf( '%s-%s-%d', $package, $phase, count( $this->lifecycle_results ) ),
			'status'   => $exit_code === 0 ? 'passed' : 'failed',
			'duration' => round( $duration ),
			'extra'    => [
				'type'               => 'lifecycle',
				'phase'              => $phase,
				'package'            => $package,
				'packageSlug'        => $package,  // Add packageSlug for CTRF merger.
				'namespace'          => $pkg_namespace, // Add namespace from manifest
				'packageId'          => $package_id, // Always use manifest packageId - no fallback
				'testType'           => $test_type, // Add test type from manifest
				'exitCode'           => $exit_code,
				'output'             => $output ?: '[No output]',
				'isLifecycle'        => true,
				'countsTowardTotals' => false,
			],
		];

		// Reset for next command
		$this->current_phase_command = null;
		$this->current_phase_start   = null;
		$this->current_phase_output  = [];
	}

	/**
	 * Save orchestrator CTRF results to file.
	 *
	 * @param string $artifacts_dir Directory to save the CTRF file.
	 */
	public function save_orchestrator_ctrf( string $artifacts_dir ): void {
		if ( empty( $this->lifecycle_results ) ) {
			return;
		}

		$ctrf_dir = $artifacts_dir . '/ctrf';
		if ( ! is_dir( $ctrf_dir ) ) {
			mkdir( $ctrf_dir, 0755, true );
		}

		// Calculate start and stop times
		// Since lifecycle_results don't have start/stop fields, use state times
		$start_time = (int) ( ( $this->state['start_time'] ?? microtime( true ) ) * 1000 );
		$stop_time  = (int) ( microtime( true ) * 1000 );

		$ctrf_data = [
			'reportFormat' => 'CTRF',
			'specVersion'  => '0.1.0',
			'results'      => [
				'tool'    => [
					'name' => 'qit-orchestrator',
				],
				'summary' => [
					'tests'   => count( $this->lifecycle_results ),
					'passed'  => count( array_filter( $this->lifecycle_results, fn( $r ) => $r['status'] === 'passed' ) ),
					'failed'  => count( array_filter( $this->lifecycle_results, fn( $r ) => $r['status'] === 'failed' ) ),
					'skipped' => count( array_filter( $this->lifecycle_results, fn( $r ) => $r['status'] === 'skipped' ) ),
					'pending' => count( array_filter( $this->lifecycle_results, fn( $r ) => $r['status'] === 'pending' ) ),
					'other'   => count( array_filter( $this->lifecycle_results, fn( $r ) => $r['status'] === 'other' ) ),
					'start'   => $start_time,
					'stop'    => $stop_time,
				],
				'tests'   => $this->lifecycle_results,
			],
		];

		// Validate CTRF before saving - this is our own generation so it MUST be valid
		$validation = $this->ctrf_validator->validate( $ctrf_data );

		if ( ! $validation['valid'] ) {
			throw new \RuntimeException( 'Orchestrator generated invalid CTRF: ' . $validation['errors'] );
		}

		$ctrf_file = $ctrf_dir . '/orchestrator.json';
		file_put_contents( $ctrf_file, json_encode( $ctrf_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Get lifecycle results for testing.
	 *
	 * @return array<array{name: string, id: string, status: string, duration: float, extra: array{type: string, phase: string, package: string, exitCode: int, output: string}}>
	 */
	public function get_lifecycle_results(): array {
		return $this->lifecycle_results;
	}
}
