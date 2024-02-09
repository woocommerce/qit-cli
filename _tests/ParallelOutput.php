<?php

use Symfony\Component\Process\Process;

class ParallelOutput {
	private $rawOutput;
	private $outputBuffer;
	private $headers;
	private $processStatus;
	private $startTimes;

	public function __construct() {
		$this->rawOutput     = '';
		$this->outputBuffer  = [];
		$this->headers       = [];
		$this->processStatus = [];
		$this->startTimes    = [];
		$this->nonJsonOutput = [];
		register_shutdown_function( [ $this, 'onShutdown' ] );
	}

	public function addRawOutput( string $output ) {
		$this->rawOutput .= $output;
	}

	public function processOutputCallback( $out, Process $process ) {
		// Retrieve the task ID from the process environment
		$taskId = $process->getEnv()['qit_task_id'];

		// Check and store the header for the task
		if ( ! isset( $this->headers[ $taskId ] ) ) {
			$this->headers[ $taskId ]       = $process->getEnv()['qit_task_id'] . "\n";
			$this->outputBuffer[ $taskId ]  = "";
			$this->nonJsonOutput[ $taskId ] = $process->getEnv()['QIT_NON_JSON_OUTPUT'] ?? '';
			$this->startTimes[ $taskId ]    = microtime( true );
		}

		if ( empty( $this->nonJsonOutput[ $taskId ] ) && ! empty( $process->getEnv()['QIT_NON_JSON_OUTPUT'] ) ) {
			$this->nonJsonOutput[ $taskId ] = $process->getEnv()['QIT_NON_JSON_OUTPUT'];
		}

		// Append new output to the buffer for the task
		$this->outputBuffer[ $taskId ] .= trim( $out ) . "\n";

		if ( ! getenv( "CI" ) ) {
			$this->displayBufferedOutputs();
		}

		$this->updateProcessStatus( $process, $out );
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
		$seconds = str_pad( (int) ceil( $elapsed % 60 ), 2, '0', STR_PAD_LEFT );

		return "[$minutes:$seconds] $taskId $status";
	}

	protected function displayBufferedOutputs() {
		// Clear the terminal screen
		system( 'clear' );

		echo $this->rawOutput . "\n";

		// Display the buffer with headers for each task
		foreach ( $this->outputBuffer as $taskId => $output ) {
			echo "\033[1;33m" . $this->headers[ $taskId ] . "\033[0m"; // Yellow for headers
			echo $output . "\n";

			if ( ! empty( $this->nonJsonOutput[ $taskId ] ) && file_exists( $this->nonJsonOutput[ $taskId ] ) ) {
				echo file_get_contents( $this->nonJsonOutput[ $taskId ] ) . "\n";
			} else {
				echo "No non-json output file found for task $taskId\n";
			}
		}

		// Print the summary section
		echo "\n\033[1;32mSummary Section:\033[0m\n"; // Green for summary section header
		foreach ( $this->processStatus as $status ) {
			echo "$status\n";
		}
	}

	public function onShutdown() {
		if ( getenv( "CI" ) ) {
			echo $this->rawOutput . "\n";

			// Print the accumulated output for each task in CI environment
			foreach ( $this->outputBuffer as $taskId => $output ) {
				echo "\033[1;33m" . $this->headers[ $taskId ] . "\033[0m\n"; // Yellow for headers
				echo $output . "\n"; // Print the accumulated output

				if ( ! empty( $this->nonJsonOutput[ $taskId ] ) && file_exists( $this->nonJsonOutput[ $taskId ] ) ) {
					echo file_get_contents( $this->nonJsonOutput[ $taskId ] ) . "\n";
				} else {
					echo "No non-json output file found for task $taskId\n";
				}
			}

			// Print the summary section
			echo "\n\033[1;32mSummary Section:\033[0m\n"; // Green for summary section header
			foreach ( $this->processStatus as $status ) {
				echo "$status\n";
			}
		}
	}
}
