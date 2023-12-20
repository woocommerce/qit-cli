<?php

use Symfony\Component\Process\Process;

class ParallelOutput {
	private $outputBuffer;
	private $headers;
	private $processStatus;
	private $startTimes;

	public function __construct() {
		$this->outputBuffer  = [];
		$this->headers       = [];
		$this->processStatus = [];
		$this->startTimes    = [];
		register_shutdown_function( [ $this, 'onShutdown' ] );
	}

	public function processOutputCallback( $out, Process $process ) {
		// Retrieve the task ID from the process environment
		$taskId = $process->getEnv()['qit_task_id'];

		// Check and store the header for the task
		if ( ! isset( $this->headers[ $taskId ] ) ) {
			$this->headers[ $taskId ]      = $process->getEnv()['qit_task_id'] . "\n";
			$this->outputBuffer[ $taskId ] = "";
			$this->startTimes[ $taskId ]   = microtime( true );
		}

		// Append new output to the buffer for the task
		$this->outputBuffer[ $taskId ] .= trim( $out ) . "\n";

		if ( ! getenv( "CI" ) ) {
			$this->displayBufferedOutputs();
		}

		$this->updateProcessStatus( $process, $out ); // You need to implement this method
	}

	protected function updateProcessStatus( Process $process, $output ) {
		$taskId = $process->getEnv()['qit_task_id'];

		if ( $process->isRunning() ) {
			$status = "\u{23F3} Running...";  // Running emoji
		} elseif ( $process->isSuccessful() ) {
			$status = "\u{2705} Success";      // Check mark emoji
		} else {
			$status = "\u{274C} Failed";       // Cross mark emoji
		}

		// Format the status with time before storing it
		$this->processStatus[ $taskId ] = $this->formatStatusWithTime( $taskId, $status );
	}

	protected function formatStatusWithTime( $taskId, $status ) {
		$elapsed = microtime( true ) - $this->startTimes[ $taskId ];
		$minutes = floor( $elapsed / 60 );
		$seconds = str_pad( floor( $elapsed % 60 ), 2, '0', STR_PAD_LEFT );

		return "[$minutes:$seconds] $taskId $status";
	}

	protected function displayBufferedOutputs() {
		// Clear the terminal screen
		system( 'clear' );

		// Display the buffer with headers for each task
		foreach ( $this->outputBuffer as $taskId => $output ) {
			echo "\033[1;33m" . $this->headers[ $taskId ] . "\033[0m"; // Yellow for headers
			echo $output . "\n";
		}

		// Print the summary section
		echo "\n\033[1;32mSummary Section:\033[0m\n"; // Green for summary section header
		foreach ( $this->processStatus as $status ) {
			echo "$status\n";
		}
	}

	public function onShutdown() {
		if ( getenv( "CI" ) ) {
			// Print the accumulated output for each task in CI environment
			foreach ( $this->outputBuffer as $taskId => $output ) {
				echo "\033[1;33m" . $this->headers[ $taskId ] . "\033[0m\n"; // Yellow for headers
				echo $output; // Print the accumulated output
			}

			// Print the summary section
			echo "\n\033[1;32mSummary Section:\033[0m\n"; // Green for summary section header
			foreach ( $this->processStatus as $status ) {
				echo "$status\n";
			}
		}
	}
}
