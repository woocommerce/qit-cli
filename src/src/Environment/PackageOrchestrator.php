<?php

namespace QIT_CLI\Environment;

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

	public function __construct( OutputInterface $output ) {
		$this->output = $output;

		// Get terminal width for dynamic sizing
		$terminal             = new Terminal();
		$this->terminal_width = min( $terminal->getWidth(), 120 ); // Cap at 120 for readability

		// Create sections if output supports it
		if ( $output instanceof ConsoleOutputInterface ) {
			$this->package_section = $output->section();
			$this->status_section  = $output->section();
		}
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

		// Add spacing before new command (except first)
		if ( isset( $this->state['has_output'] ) && $this->state['has_output'] ) {
			$out->writeln( '│' );
		}

		$out->writeln( '│ [' . $context . '] ' . $command );
		$this->state['has_output'] = false;
	}

	/**
	 * Parse and beautify line output
	 */
	public function parse_line( string $line ): bool {
		$out = $this->package_section ?? $this->output;

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

		// Format output line with proper indentation (no right border)
		$out->writeln( '│   ' . $line );
		$this->state['has_output'] = true;

		return true;
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
	 * Display database restore section
	 */
	public function database_restore_start(): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		$out->writeln( '┌─ DATABASE RESTORE ───────────────────────────────────────────────────────' );
		$out->writeln( '│ Restoring database snapshot for test isolation...' );
	}

	public function database_restore_end( bool $success = true ): void {
		$out        = $this->package_section ?? $this->output;
		$line_width = min( $this->terminal_width - 5, 75 );
		if ( $success ) {
			$out->writeln( '│ ✓ Database snapshot restored successfully' );
		} else {
			$out->writeln( '│ ✗ Database restore failed' );
		}
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
	 * @param array{status?: string, local_command?: string, remote_url?: string} $results
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
		if ( ! empty( $results['local_command'] ) || ! empty( $results['remote_url'] ) ) {
			$out->writeln( '' );
			$out->writeln( 'View Results:' );
			if ( ! empty( $results['local_command'] ) ) {
				$out->writeln( '• Local Report:  <comment>' . $results['local_command'] . '</comment>' );
			}
			if ( ! empty( $results['remote_url'] ) ) {
				$out->writeln( '• Remote URL:    <comment>' . $results['remote_url'] . '</comment>' );
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
			$secs    = round( $seconds % 60 );
			return "{$minutes}m {$secs}s";
		}
	}
}
