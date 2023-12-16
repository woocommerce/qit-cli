<?php

use Symfony\Component\Process\Process;

class ParallelOutput {
	private $outputBuffer;
	private $headers;

	public function __construct() {
		$this->outputBuffer = [];
		$this->headers      = [];
		register_shutdown_function( [ $this, 'onShutdown' ] );
	}

	public function processOutputCallback( $out, Process $process ) {
		// Retrieve the task ID from the process environment
		$taskId = $process->getEnv()['qit_task_id'];

		// Check and store the header for the task
		if ( ! isset( $this->headers[ $taskId ] ) ) {
			$this->headers[ $taskId ]      = $process->getEnv()['qit_task_id'];
			$this->outputBuffer[ $taskId ] = "";
		}

		// Append new output to the buffer for the task
		$this->outputBuffer[ $taskId ] .= trim( $out ) . "\n";

		if ( ! getenv( "CI" ) ) {
			$this->displayBufferedOutputs();
		}
	}

	protected function displayBufferedOutputs() {
		// Clear the terminal screen
		system( 'clear' );

		// Display the buffer with headers for each task
		foreach ( $this->outputBuffer as $taskId => $output ) {
			echo "\033[1;33m" . $this->headers[ $taskId ] . "\033[0m"; // Yellow for headers
			echo $output . "\n";
		}
	}

	public function onShutdown() {
		if ( getenv( "CI" ) ) {
			// Print the accumulated output for each task in CI environment
			foreach ( $this->outputBuffer as $taskId => $output ) {
				echo "\033[1;33m" . $this->headers[ $taskId ] . "\033[0m\n"; // Yellow for headers
				echo $output; // Print the accumulated output
			}
		}
	}
}
