<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Orchestrator for global setup packages that provides framed output.
 */
class GlobalSetupOrchestrator extends PackageOrchestrator {
	private OutputInterface $output;
	private bool $in_frame = false;

	public function __construct( OutputInterface $output, CTRFValidator $ctrf_validator ) {
		parent::__construct( $output, $ctrf_validator );
		$this->output = $output;
	}

	/**
	 * Start output for a global setup package.
	 *
	 * @param string                            $package_id Package identifier (e.g., vendor/package:version).
	 * @param array{path:string,source?:string} $info Package information.
	 */
	public function start_package( string $package_id, array $info ): void {
		$this->in_frame = true;

		// Start the frame
		$header  = '┌─ ' . $package_id . ' ';
		$header .= str_repeat( '─', max( 0, 70 - strlen( $header ) ) );
		$this->output->writeln( $header );

		// Show source information
		if ( isset( $info['source'] ) && $info['source'] === 'local' ) {
			$this->output->writeln( '│ Using local package from ' . $info['path'] );
		} else {
			$this->output->writeln( '│ Downloading from package registry...' );
		}

		$this->output->writeln( '│ Running globalSetup phase...' );
	}

	/**
	 * End output for a global setup package.
	 *
	 * @param string $package_id Package identifier.
	 * @param bool   $success Whether the package executed successfully.
	 * @param int    $commands_run Number of commands executed.
	 * @param string $error_message Error message if failed.
	 */
	public function end_package( string $package_id, bool $success, int $commands_run, string $error_message = '' ): void {
		if ( ! $success && $error_message ) {
			$this->output->writeln( '│ ERROR: ' . $error_message );
			$this->output->writeln( '│ Global setup failed' );
		} elseif ( $commands_run === 0 ) {
			$this->output->writeln( '│ No setup commands found' );
		}

		// End the frame
		$footer = '└' . str_repeat( '─', 69 );
		$this->output->writeln( $footer );
		$this->output->writeln( '' );

		$this->in_frame = false;
	}

	/**
	 * Parse a line of output from the test runner.
	 *
	 * @param string $line The line to parse.
	 * @param bool   $is_error Whether this is an error line.
	 * @return bool True if the line was handled, false otherwise.
	 */
	public function parse_line( string $line, bool $is_error = false ): bool {
		// Let parent handle special markers
		if ( parent::parse_line( $line, $is_error ) ) {
			return true;
		}

		// If we're in a frame, prefix all output with the frame border
		if ( $this->in_frame && trim( $line ) !== '' ) {
			$this->output->writeln( '│ ' . $line );
			return true;
		}

		return false;
	}

	/**
	 * Show a command being executed.
	 *
	 * @param string $command The command being executed.
	 * @param string $context Execution context ('host' or 'docker').
	 */
	public function show_command( string $command, string $context = 'host' ): void {
		// Don't show individual commands for global setup packages
		// The output from the commands themselves is more important
	}
}
