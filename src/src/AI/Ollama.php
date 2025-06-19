<?php

namespace QIT_CLI\AI;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Ollama {
	private ?Process $ollama_serve_process = null;
	private string $api_base_url = 'http://localhost:11434';

	public function __construct() {
		// Check for custom Ollama host from environment
		$ollama_host = getenv( 'OLLAMA_HOST' );
		if ( $ollama_host ) {
			// Handle various formats
			if ( strpos( $ollama_host, 'http' ) !== 0 ) {
				$ollama_host = 'http://' . $ollama_host;
			}
			$this->api_base_url = rtrim( $ollama_host, '/' );
		}
	}

	public function get_api_base_url(): string {
		return $this->api_base_url;
	}

	public function is_available(): bool {
		$process = new Process( [ 'ollama', '--version' ] );
		$process->run();

		return $process->isSuccessful();
	}

	public function ensure_api_running(): bool {
		// Check if Ollama API is already running
		$ch = curl_init( $this->api_base_url . '/api/tags' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 2 );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		$response  = curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $http_code === 200 ) {
			return true; // API is already running
		}

		// If using custom host, we can't start it
		if ( getenv( 'OLLAMA_HOST' ) ) {
			throw new \RuntimeException( 'Ollama API not responding at ' . $this->api_base_url . '. Please ensure Ollama is running.' );
		}

		// Try to start Ollama serve on default port
		$this->ollama_serve_process = new Process( [ 'ollama', 'serve' ] );
		$this->ollama_serve_process->start();

		// Wait a bit for it to start
		sleep( 3 );

		// Check again
		$ch = curl_init( $this->api_base_url . '/api/tags' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		$response  = curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		return $http_code === 200;
	}

	public function stop_api(): void {
		if ( $this->ollama_serve_process && $this->ollama_serve_process->isRunning() ) {
			$this->ollama_serve_process->stop();
		}
	}

	public function ensure_model( string $model, OutputInterface $output = null ): bool {
		// Ensure API is running first
		if ( ! $this->ensure_api_running() ) {
			throw new \RuntimeException( 'Failed to start Ollama API server' );
		}

		// Check if model exists
		$check_process = new Process( [ 'ollama', 'show', $model ] );
		$check_process->run();

		if ( $check_process->isSuccessful() ) {
			return true; // Model already exists
		}

		// If we need to pull and have output, start a new line
		if ( $output ) {
			$output->writeln( '' ); // Move to next line before showing progress
		}

		// Pull the model
		$pull_process = new Process( [ 'ollama', 'pull', $model ] );
		$pull_process->setTimeout( 1800 ); // 30 minutes timeout
		$pull_process->run( function ( $type, $buffer ) use ( $output ) {
			if ( $output ) {
				// Clean the buffer and write it
				$output->write( "\r" . str_pad( trim( $buffer ), 80 ) );
			} else {
				// Fallback to echo if no output interface
				echo $buffer;
			}
		} );

		if ( $output ) {
			$output->write( "\r" . str_repeat( ' ', 80 ) . "\r" ); // Clear the line
		}

		return $pull_process->isSuccessful();
	}

	public function get_installed_models(): array {
		// Ensure API is running
		if ( ! $this->ensure_api_running() ) {
			return [];
		}

		$process = new Process( [ 'ollama', 'list' ] );
		$process->run();

		if ( ! $process->isSuccessful() ) {
			return [];
		}

		$output = $process->getOutput();
		$lines  = explode( "\n", $output );
		$models = [];

		// Skip header line
		for ( $i = 1; $i < count( $lines ); $i ++ ) {
			if ( preg_match( '/^(\S+)\s+/', $lines[ $i ], $matches ) ) {
				$model_name = explode( ':', $matches[1] )[0];
				if ( ! in_array( $model_name, $models ) ) {
					$models[] = $model_name;
				}
			}
		}

		return $models;
	}
}