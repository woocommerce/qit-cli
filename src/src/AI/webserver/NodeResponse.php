<?php
/**
 * Standardized Node Response Handler
 *
 * Provides consistent response formatting for the core Node APIs:
 * - Basic Prompt (simple AI inference)
 * - Tool Prompt (AI with tool execution)
 * - ZIP Extraction (file extraction utility)
 *
 * Complex orchestration is handled by the Manager service.
 * Performance tracking is handled by the Benchmark class.
 */

namespace QIT_AI_Webserver;

class NodeResponse {

	/**
	 * Initialize response tracking (call at request start)
	 *
	 * @deprecated Use Benchmark::init() instead
	 */
	public static function init(): void {
		Benchmark::init();
	}

	/**
	 * Mark a performance checkpoint
	 *
	 * @param string $name Marker name
	 * @param array  $data Optional data to associate with marker
	 * @deprecated Use Benchmark::mark() instead
	 */
	public static function mark( string $name, array $data = [] ): void {
		Benchmark::mark( $name, $data );
	}

	/**
	 * Basic prompt response (single AI inference)
	 * Used by BasicPromptEndpoint
	 *
	 * @param string $response AI response text
	 * @param string $model Model used
	 * @param array  $providerResponse Raw provider response for token stats
	 * @param array  $additional Additional data (job_id, etc.)
	 *
	 * @return string JSON response string
	 */
	public static function prompt( string $response, string $model, array $providerResponse = [], array $additional = [] ): string {
		// Get current provider and suggested model from request
		$currentProvider = \QIT_AI_Webserver\Lib\LLPhantBootstrap::getCurrentProvider();
		$suggestedModel  = $_REQUEST['model'] ?? null;

		$data = array_merge([
			'response'         => $response,
			'model'            => $model,  // Actual model used by the node
			'provider'         => $currentProvider,  // Actual provider used by the node
			'suggested_model'  => $suggestedModel,  // Model suggested by Manager
			'model_resolution' => [
				'suggested'        => $suggestedModel,
				'actual'           => $model,
				'provider'         => $currentProvider,
				'resolved_by_node' => true,
			],
		], $additional);

		// Add token statistics if available
		$tokenStats = Benchmark::extractProviderStats( $providerResponse );
		if ( ! empty( $tokenStats ) ) {
			$data['token_stats'] = $tokenStats;
		}

		$response = [
			'status' => 'success',
			'type'   => 'prompt',
			'data'   => $data,
			'meta'   => [],
		];

		// Add performance metrics
		$response = Benchmark::enhanceResponse( $response );

		// Log the FULL response
		log_info( 'NodeResponse - Sending prompt response', $response );

		return json_encode( $response );
	}

	/**
	 * Tool execution response (AI with tools)
	 *
	 * @param string $response Final AI response
	 * @param array  $toolCalls Tool execution records
	 * @param string $model Model used
	 * @param array  $additional Additional data
	 *
	 * @return string JSON response string
	 */
	public static function toolPrompt( string $response, array $toolCalls, string $model, array $additional = [] ): string {
		// Get current provider and suggested model from request
		$currentProvider = \QIT_AI_Webserver\Lib\LLPhantBootstrap::getCurrentProvider();
		$suggestedModel  = $_REQUEST['model'] ?? null;

		$data = array_merge([
			'response'         => $response,
			'model'            => $model,  // Actual model used by the node
			'provider'         => $currentProvider,  // Actual provider used by the node
			'suggested_model'  => $suggestedModel,  // Model suggested by Manager
			'tool_calls'       => $toolCalls,
			'tool_count'       => count( $toolCalls ),
			'model_resolution' => [
				'suggested'        => $suggestedModel,
				'actual'           => $model,
				'provider'         => $currentProvider,
				'resolved_by_node' => true,
			],
		], $additional);

		$response = [
			'status' => 'success',
			'type'   => 'tool_prompt',
			'data'   => $data,
			'meta'   => [],
		];

		// Add performance metrics
		$response = Benchmark::enhanceResponse( $response );

		// Log the FULL response
		log_info( 'NodeResponse - Sending tool prompt response', $response );

		return json_encode( $response );
	}

	/**
	 * ZIP extraction response
	 * Used by ZipExtractionEndpoint
	 *
	 * @param string $extractPath Extraction directory path
	 * @param array  $stats Extraction statistics
	 * @param string $sessionId Session identifier
	 * @param array  $additional Additional data
	 *
	 * @return string JSON response string
	 */
	public static function extraction( string $extractPath, array $stats, string $sessionId, array $additional = [] ): string {
		$data = array_merge([
			'extract_path' => $extractPath,
			'session_id'   => $sessionId,
			'stats'        => $stats,
		], $additional);

		$response = [
			'status' => 'success',
			'type'   => 'extraction',
			'data'   => $data,
			'meta'   => [],
		];

		// Add performance metrics
		$response = Benchmark::enhanceResponse( $response );

		// Log the FULL response
		log_info( 'NodeResponse - Sending extraction response', $response );

		return json_encode( $response );
	}

	/**
	 * Generic success response (for future extensions)
	 *
	 * @param mixed  $data Response data
	 * @param string $type Response type identifier
	 * @param array  $meta Additional metadata
	 *
	 * @return string JSON response string
	 */
	public static function success( $data, string $type = 'generic', array $meta = [] ): string {
		$response = [
			'status' => 'success',
			'type'   => $type,
			'data'   => $data,
			'meta'   => $meta,
		];

		// Add performance metrics
		$response = Benchmark::enhanceResponse( $response );

		// Log the FULL response
		log_info( 'NodeResponse - Sending success response', $response );

		return json_encode( $response );
	}

	/**
	 * Error response
	 *
	 * @param string $message Error message
	 * @param int    $code HTTP status code
	 * @param array  $details Error details
	 *
	 * @return string JSON response string
	 */
	public static function error( string $message, int $code = 500, array $details = [] ): string {
		$errorData = [
			'message' => $message,
			'code'    => $code,
		];

		if ( ! empty( $details ) ) {
			$errorData['details'] = $details;
		}

		$response = [
			'status' => 'error',
			'type'   => 'error',
			'error'  => $errorData,
			'meta'   => [],
		];

		// Add performance metrics
		$response = Benchmark::enhanceResponse( $response );

		// Log the FULL response
		log_info( 'NodeResponse - Sending error response', $response );

		return json_encode( $response );
	}

	/**
	 * Raw response for Manager-orchestrated responses
	 * The Manager can use this to return pre-structured responses while still
	 * getting performance metrics added.
	 *
	 * @param array $managerResponse Response structure from Manager
	 *
	 * @return string JSON response string
	 */
	public static function fromManager( array $managerResponse ): string {
		// Manager provides the response structure, we just add performance meta
		$response = $managerResponse;

		// Add performance metrics
		$response = Benchmark::enhanceResponse( $response );

		// Ensure we have required fields
		if ( ! isset( $response['status'] ) ) {
			$response['status'] = 'success';
		}
		if ( ! isset( $response['type'] ) ) {
			$response['type'] = 'manager_orchestrated';
		}

		// Log the FULL response
		log_info( 'NodeResponse - Sending manager response', $response );

		return json_encode( $response );
	}
}
