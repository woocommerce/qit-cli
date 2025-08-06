<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

/**
 * Orchestrates test package execution with beautiful UI using native Symfony Console components.
 */
class PackageOrchestrator {
	private OutputInterface $output;
	private ?ConsoleSectionOutput $headerSection = null;
	private ?ConsoleSectionOutput $packageSection = null;
	private ?ConsoleSectionOutput $statusSection = null;
	private ?ProgressBar $progressBar = null;
	private ?Table $currentTable = null;
	private int $terminalWidth;
	
	private array $state = [
		'current_package' => null,
		'current_phase' => null,
		'packages_total' => 0,
		'packages_completed' => 0,
		'test_totals' => [
			'total' => 0,
			'passed' => 0,
			'failed' => 0,
			'skipped' => 0,
			'flaky' => 0,
		],
		'current_tests' => [],
		'test_results' => [],
		'start_time' => null,
		'phase_start_time' => null,
		'test_count' => 0,
		'tests_completed' => 0,
		'phase_lines' => [],
		'suppress_output' => false,
	];
	
	public function __construct( OutputInterface $output ) {
		$this->output = $output;
		
		// Get terminal width for dynamic sizing
		$terminal = new Terminal();
		$this->terminalWidth = min( $terminal->getWidth(), 120 ); // Cap at 120 for readability
		
		// Create sections if output supports it
		if ( $output instanceof ConsoleOutputInterface ) {
			$this->headerSection = $output->section();
			$this->packageSection = $output->section();
			$this->statusSection = $output->section();
		}
	}
	
	/**
	 * Start orchestration display
	 */
	public function start( string $environment, int $total_packages ): void {
		$this->state['packages_total'] = $total_packages;
		$this->state['start_time'] = microtime( true );
		// Minimal start - no header needed
	}
	
	/**
	 * Display global setup phase
	 */
	public function globalSetupStart(): void {
		$out = $this->packageSection ?? $this->output;
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '┌─ GLOBAL SETUP ' . str_repeat( '─', max( 0, $lineWidth - 16 ) ) );
		$this->state['phase_start_time'] = microtime( true );
		$this->state['current_phase'] = 'GLOBAL_SETUP';
	}
	
	public function globalSetupMessage( string $message ): void {
		$out = $this->packageSection ?? $this->output;
		$out->writeln( '│ ' . $message );
	}
	
	public function globalSetupEnd(): void {
		$out = $this->packageSection ?? $this->output;
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $lineWidth ) );
		$out->writeln( '' );
	}
	
	/**
	 * Start a new package
	 */
	public function packageStart( int $index, string $package_id, string $type = 'Local Package' ): void {
		$this->state['packages_completed'] = $index - 1;
		$this->state['current_package'] = $package_id;
		$this->state['test_results'] = [];
		$this->state['current_command'] = null;
		
		$out = $this->packageSection ?? $this->output;
		
		// Package header with box drawing (no right border)
		$header = sprintf( 'PACKAGE [%d/%d]: %s', $index, $this->state['packages_total'], $package_id );
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '┌─ ' . $header . ' ' . str_repeat( '─', max( 0, $lineWidth - strlen( $header ) - 3 ) ) );
		$out->writeln( '│ Type: ' . $type );
		$out->writeln( '├' . str_repeat( '─', $lineWidth ) );
	}
	
	/**
	 * Display phase start
	 */
	public function phaseStart( string $phase ): void {
		$this->state['current_phase'] = strtoupper( $phase );
		$this->state['phase_start_time'] = microtime( true );
		// No phase headers - commands will be shown directly
	}
	
	/**
	 * Show command being executed
	 */
	public function showCommand( string $command, string $context = 'docker' ): void {
		$out = $this->packageSection ?? $this->output;
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
	public function parseLine( string $line ): bool {
		$out = $this->packageSection ?? $this->output;
		
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
		
		// Track test stats if it's a test result line
		if ( preg_match( '/^\s*(✓|✗|○|-)\s+/', $line ) ) {
			$this->state['test_totals']['total']++;
			if ( strpos( $line, '✓' ) !== false ) {
				$this->state['test_totals']['passed']++;
			} elseif ( strpos( $line, '✗' ) !== false ) {
				$this->state['test_totals']['failed']++;
			} elseif ( strpos( $line, '○' ) !== false || strpos( $line, '-' ) !== false ) {
				$this->state['test_totals']['skipped']++;
			}
		}
		
		return true;
	}
	
	/**
	 * End current package
	 */
	public function packageEnd( bool $success = true ): void {
		$out = $this->packageSection ?? $this->output;
		
		// Increment completed count
		$this->state['packages_completed']++;
		
		// Package footer (no right border)
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $lineWidth ) );
		$out->writeln( '' );
		
		if ( ! $success ) {
			$out->writeln( '[Package failed - Database restore skipped]' );
		} else {
			$out->writeln( '[Database restored to snapshot for next package]' );
		}
		$out->writeln( '' );
	}
	
	/**
	 * Display post-processing section
	 */
	public function postProcessingStart(): void {
		$out = $this->packageSection ?? $this->output;
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '' );
		$out->writeln( '┌─ POST-PROCESSING ' . str_repeat( '─', max( 0, $lineWidth - 19 ) ) );
		$this->state['phase_start_time'] = microtime( true );
		$this->state['current_phase'] = 'POST_PROCESSING';
	}
	
	public function postProcessingMessage( string $message, bool $success = true ): void {
		$out = $this->packageSection ?? $this->output;
		$symbol = $success ? '✓' : '✗';
		$out->writeln( "│ $symbol $message" );
	}
	
	public function postProcessingEnd(): void {
		$out = $this->packageSection ?? $this->output;
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $lineWidth ) );
		$out->writeln( '' );
	}
	
	/**
	 * Display global teardown
	 */
	public function globalTeardownStart(): void {
		$out = $this->packageSection ?? $this->output;
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '┌─ GLOBAL TEARDOWN ' . str_repeat( '─', max( 0, $lineWidth - 19 ) ) );
		$this->state['phase_start_time'] = microtime( true );
		$this->state['current_phase'] = 'GLOBAL_TEARDOWN';
	}
	
	public function globalTeardownMessage( string $message ): void {
		$out = $this->packageSection ?? $this->output;
		$out->writeln( '│ ' . $message );
	}
	
	public function globalTeardownEnd(): void {
		$out = $this->packageSection ?? $this->output;
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '└' . str_repeat( '─', $lineWidth ) );
		$out->writeln( '' );
	}
	
	/**
	 * Display final summary
	 */
	public function summary( array $results ): void {
		$duration = microtime( true ) - $this->state['start_time'];
		$out = $this->statusSection ?? $this->output;
		
		$lineWidth = min( $this->terminalWidth - 5, 75 );
		$out->writeln( '' );
		$out->writeln( str_repeat( '═', $lineWidth ) );
		$out->writeln( 'TEST RESULTS SUMMARY' );
		$out->writeln( str_repeat( '═', $lineWidth ) );
		
		// Status
		$status = isset( $results['status'] ) && $results['status'] === 'passed' ? '✓ PASSED' : '✗ FAILED';
		$statusColor = isset( $results['status'] ) && $results['status'] === 'passed' ? 'info' : 'error';
		$out->writeln( sprintf( '<' . $statusColor . '>Status:        %s</' . $statusColor . '>', $status ) );
		
		// Package stats
		$out->writeln( sprintf( 'Packages:      %d/%d executed', $this->state['packages_completed'], $this->state['packages_total'] ) );
		
		// Test stats if available
		$passed = $this->state['test_totals']['passed'] ?? 0;
		$failed = $this->state['test_totals']['failed'] ?? 0;
		$skipped = $this->state['test_totals']['skipped'] ?? 0;
		$total = $this->state['test_totals']['total'] ?? 0;
		if ( $total > 0 ) {
			$out->writeln( sprintf( 'Tests:         %d passed, %d failed, %d skipped', $passed, $failed, $skipped ) );
		}
		
		$out->writeln( sprintf( 'Duration:      %s', $this->formatDuration( $duration ) ) );
		
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
		$out->writeln( str_repeat( '═', $lineWidth ) );
	}
	
	/**
	 * Format duration in human readable format
	 */
	private function formatDuration( float $seconds ): string {
		if ( $seconds < 1 ) {
			return round( $seconds * 1000 ) . 'ms';
		} elseif ( $seconds < 60 ) {
			return round( $seconds, 1 ) . 's';
		} else {
			$minutes = floor( $seconds / 60 );
			$secs = round( $seconds % 60 );
			return "{$minutes}m {$secs}s";
		}
	}
}