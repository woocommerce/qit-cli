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
	 * @param array $input Request input data
	 */
	abstract public function handle( array $input ): void;

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
		$errorDir = rtrim(sys_get_temp_dir(), '/\\') . '/qit-node/errors';
		if (!is_dir($errorDir)) {
			mkdir($errorDir, 0700, true);
		}
		file_put_contents(
			$errorDir . '/qit-node-last-error.json',
			json_encode( $errorReport )
		);

		// Use NodeResponse::error for standardized error response
		NodeResponse::error( $e->getMessage(), 500, $errorReport );
	}

	// Logging methods - these would use the global logging functions
	public function log_info( string $message, array $context = [] ): void {
		log_info( $message, $context );
	}

	public function log_debug( string $message, array $context = [] ): void {
		log_debug( $message, $context );
	}

	public function log_error( string $message, array $context = [] ): void {
		log_error( $message, $context );
	}

	public function log_warning( string $message, array $context = [] ): void {
		log_warning( $message, $context );
	}

}
