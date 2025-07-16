<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\NodeResponse;

/**
 * Abstract Base Endpoint
 *
 * Base class for all AI endpoints providing common functionality
 * including LLM API communication, logging, and route definition.
 */
abstract class AbstractEndpoint {
	protected \LLPhant\Chat\ChatInterface $chat;

	public function __construct() {
		$this->chat = \QIT_AI_Webserver\Lib\LLPhantBootstrap::chat();
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
	 * @param array<string, mixed> $input Request input data.
	 * @return string JSON response
	 */
	abstract public function handle( array $input );

	/**
	 * Handle errors consistently across all endpoints
	 *
	 * @param Exception $e Exception to handle.
	 * @param array<string, mixed> $context Additional context for error reporting.
	 * @return string JSON error response
	 */
	protected function handle_error( Exception $e, array $context = [] ): string {
		$trace = $e->getTraceAsString();

		$error_context = array_merge( [
			'exception' => get_class( $e ),
			'trace'     => $trace,
		], $context );

		$this->log_error( 'Processing error: ' . $e->getMessage(), $error_context );

		// Report error back to manager
		$error_report = [
			'job_id'        => $context['job_id'] ?? null,
			'error_type'    => get_class( $e ),
			'error_message' => $e->getMessage(),
			'error_time'    => gmdate( 'Y-m-d H:i:s' ),
			'job_type'      => $context['job_type'] ?? 'unknown',
		];

		$this->log_info( 'Storing error for next heartbeat', [
			'job_id'     => $context['job_id'] ?? 'unknown',
			'error_type' => get_class( $e ),
		] );

		// Store error for next heartbeat
		$error_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/qit-node/errors';
		if ( ! is_dir( $error_dir ) ) {
			mkdir( $error_dir, 0700, true );
		}
		file_put_contents(
			$error_dir . '/qit-node-last-error.json',
			json_encode( $error_report )
		);

		// Use NodeResponse::error for standardized error response
		// Get JSON response as string and echo it
		return json_encode( NodeResponse::error( $e->getMessage(), 500, $error_report ) );
	}

	/**
	 * Logging methods - these would use the global logging functions
	 */
	public function log_info( string $message, array $context = [] ): void {
		log_info( $message, $context );
	}

	/**
	 * @param string $message
	 * @param array<string, mixed> $context
	 */
	public function log_debug( string $message, array $context = [] ): void {
		log_debug( $message, $context );
	}

	/**
	 * @param string $message
	 * @param array<string, mixed> $context
	 */
	public function log_error( string $message, array $context = [] ): void {
		log_error( $message, $context );
	}

	/**
	 * @param string $message
	 * @param array<string, mixed> $context
	 */
	public function log_warning( string $message, array $context = [] ): void {
		log_warning( $message, $context );
	}
}
