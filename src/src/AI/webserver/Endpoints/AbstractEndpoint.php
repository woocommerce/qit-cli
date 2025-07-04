<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\Lib\LLPhantBootstrap;
use QIT_AI_Webserver\ToolRegistry;
use LLPhant\Chat\Message;

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

	/** Optional helper to map raw message arrays → LL‑Phant messages */
	protected function toMessages( array $raw ): array {
		return array_map(
			fn( $m ) => \LLPhant\Chat\Message::{$m['role']}( $m['content'] ),
			$raw
		);
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
