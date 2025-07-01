<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\NodeResponse;

/**
 * Abstract Base Endpoint
 *
 * Base class for all AI endpoints providing common functionality
 * including Ollama API communication, logging, and route definition.
 */
abstract class AbstractEndpoint {
	protected string $ollamaApiUrl;

	public function __construct( string $ollamaApiUrl ) {
		$this->ollamaApiUrl = $ollamaApiUrl;
	}

	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path (e.g., '/basic-prompt')
	 */
	abstract public function get_route(): string;

	/**
	 * Handle the request
	 *
	 * @param array $input Request input data
	 */
	abstract public function handle( array $input ): void;

	/**
	 * Call Ollama API with automatic model config application
	 *
	 * @param string $endpoint API endpoint (e.g., '/api/generate', '/api/chat')
	 * @param array $data Request data
	 * @param array $input Original input data containing potential options
	 *
	 * @return array Response data
	 * @throws Exception On API error
	 */
	protected function callOllama( string $endpoint, array $data, array $input = [] ): array {
		// Automatically apply options if this is an AI request
		if ( isset( $data['model'] ) && isset( $input['options'] ) && is_array( $input['options'] ) ) {
			$data['options'] = $input['options'];

			$this->log_debug( "Applied model options", [
				'model'   => $data['model'],
				'options' => $data['options']
			] );
		}

		// Ensure we have the full URL
		$url = $this->ollamaApiUrl . $endpoint;

		// Determine if this is a tool call or regular generation
		$hasTools = isset( $data['tools'] ) && ! empty( $data['tools'] );

		$this->log_debug( "Calling Ollama API", $data );

		// IMPORTANT: Use the correct endpoint for tool calls
		if ( $hasTools && strpos( $url, '/api/generate' ) !== false ) {
			// For tool calls, we MUST use /api/chat endpoint
			$url = str_replace( '/api/generate', '/api/chat', $url );
			$this->log_info( "Using chat endpoint for tool-enabled request", [ 'url' => $url ] );
		}

		$startTime = microtime( true );

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
		if ( ! empty( $data['format'] ) ) {
			$this->log_info( 'Setting content type for format', [
				'format' => $data['format']
			] );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ] );
		}
		curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );

		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error    = curl_error( $ch );
		$info     = curl_getinfo( $ch );
		curl_close( $ch );

		$duration = microtime( true ) - $startTime;

		$this->log_debug( "Ollama API response", [
			'http_code'        => $httpCode,
			'duration_seconds' => round( $duration, 2 ),
			'response_size'    => $response ? strlen( $response ) : 0,
			'curl_error'       => $error ?: null,
			'total_time'       => $info['total_time'] ?? null,
			'response'         => $response,
		] );

		if ( $response === false ) {
			throw new Exception( "Ollama API curl error: $error" );
		}

		if ( $httpCode !== 200 ) {
			$this->log_error( "Ollama API error response", [
				'http_code' => $httpCode,
				'response'  => substr( $response, 0, 500 )
			] );
			throw new Exception( "Ollama API error: HTTP $httpCode" );
		}

		$decoded = json_decode( $response, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Exception( "Invalid JSON response from Ollama: " . json_last_error_msg() );
		}

		// Log tool calls if present
		if ( isset( $decoded['message']['tool_calls'] ) ) {
			$this->log_info( "Ollama returned tool calls", [
				'tool_calls_count' => count( $decoded['message']['tool_calls'] ),
				'tool_names'       => array_map( function ( $tc ) {
					return $tc['function']['name'] ?? 'unknown';
				}, $decoded['message']['tool_calls'] )
			] );
		}

		// Model stopping moved to per-request level to preserve context in multi-round conversations

		return $decoded;
	}

	/**
	 * Call Ollama Generate API with automatic model config
	 *
	 * @param array $request Request data
	 * @param array $input Original input for options extraction
	 *
	 * @return array Response data
	 * @throws Exception On API error
	 */
	protected function callOllamaGenerate( array $request, array $input = [] ): array {
		$response = $this->callOllama( '/api/generate', $request, $input );

		if ( ! isset( $response['response'] ) ) {
			$this->log_error( "Invalid Ollama response structure", [
				'keys'             => array_keys( $response ),
				'response_excerpt' => substr( json_encode( $response ), 0, 500 )
			] );
			throw new Exception( 'Invalid response from Ollama' );
		}

		// Check for schema response issues
		if ( isset( $request['format'] ) ) {
			if ( is_string( $response['response'] ) ) {
				$decoded = json_decode( $response['response'], true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					$this->log_error( "Schema response is not valid JSON", [
						'job_id'           => $request['job_id'] ?? 'unknown',
						'json_error'       => json_last_error_msg(),
						'response_excerpt' => substr( $response['response'], 0, 200 )
					] );
				}
			}
		}

		return $response;
	}

	/**
	 * Call Ollama Chat API with automatic model config
	 *
	 * @param array $request Request data
	 * @param array $input Original input for options extraction
	 *
	 * @return array Response data
	 * @throws Exception On API error
	 */
	protected function callOllamaChat( array $request, array $input = [] ): array {
		return $this->callOllama( '/api/chat', $request, $input );
	}

	/**
	 * Ensure model is available
	 *
	 * @param string $model Model name
	 *
	 * @return bool True if available
	 */
	protected function ensureModelAvailable( string $model ): bool {
		// This would call the global ensure_model_available function
		// For now, we'll assume it's available
		return ensure_model_available( $model, $this->ollamaApiUrl );
	}

	/**
	 * Handle errors consistently across all endpoints
	 *
	 * @param Exception $e Exception to handle
	 * @param array $context Additional context for error reporting
	 */
	protected function handleError( Exception $e, array $context = [] ): void {
		$trace = $e->getTraceAsString();

		$errorContext = array_merge( [
			'exception' => get_class( $e ),
			'trace'     => $trace
		], $context );

		$this->log_error( 'Processing error: ' . $e->getMessage(), $errorContext );

		// Report error back to manager
		$errorReport = [
			'job_id'        => $context['job_id'] ?? null,
			'error_type'    => get_class( $e ),
			'error_message' => $e->getMessage(),
			'error_time'    => date( 'Y-m-d H:i:s' ),
			'job_type'      => $context['job_type'] ?? 'unknown'
		];

		$this->log_info( "Storing error for next heartbeat", [
			'job_id'     => $context['job_id'] ?? 'unknown',
			'error_type' => get_class( $e )
		] );

		// Store error for next heartbeat
		file_put_contents(
			sys_get_temp_dir() . '/qit-node-last-error.json',
			json_encode( $errorReport )
		);

		// Use NodeResponse::error for standardized error response
		NodeResponse::error( $e->getMessage(), 500, $errorReport );
	}

	/**
	 * Format bytes to human readable format
	 *
	 * @param int $bytes Bytes to format
	 * @param int $precision Decimal precision
	 *
	 * @return string Formatted string
	 */
	protected function formatBytes( int $bytes, int $precision = 2 ): string {
		$units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];

		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	// Logging methods - these would use the global logging functions
	protected function log_info( string $message, array $context = [] ): void {
		log_info( $message, $context );
	}

	protected function log_debug( string $message, array $context = [] ): void {
		log_debug( $message, $context );
	}

	protected function log_error( string $message, array $context = [] ): void {
		log_error( $message, $context );
	}

	protected function log_warning( string $message, array $context = [] ): void {
		log_warning( $message, $context );
	}

	/**
	 * Stop an Ollama model to free up VRAM
	 *
	 * @param string $model Model name to stop
	 */
	protected function stopOllamaModel( string $model ): void {
		try {
			$this->log_debug( "Stopping Ollama model to free VRAM", [ 'model' => $model ] );

			// Execute ollama stop command
			$command = "ollama stop " . escapeshellarg( $model ) . " 2>&1";
			$output  = shell_exec( $command );

			$this->log_debug( "Ollama stop command executed", [
				'model'   => $model,
				'command' => $command,
				'output'  => trim( $output ?: '' )
			] );
		} catch ( Exception $e ) {
			// Don't throw exceptions for stop failures - just log them
			$this->log_warning( "Failed to stop Ollama model", [
				'model' => $model,
				'error' => $e->getMessage()
			] );
		}
	}
}
