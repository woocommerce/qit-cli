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

use QIT_AI_Webserver\Lib\LLPhantBootstrap;

class NodeResponse {
	private string $status;
	/** @var array<string, mixed> */
	private array $data;
	/** @var array<string, mixed> */
	private array $meta;

	/**
	 * @param array<string, mixed> $data
	 * @param array<string, mixed> $meta
	 */
	public function __construct( string $status, array $data = [], array $meta = [] ) {
		$this->status = $status;
		$this->data   = $data;
		$this->meta   = $meta;
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function mark( string $status, array $data = [] ): array {
		return [
			'status' => $status,
			'data'   => $data,
			'meta'   => [
				'timestamp' => time(),
				'node_id'   => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			],
		];
	}

	/**
	 * @param array<string, mixed> $provider_response
	 * @param array<string, mixed> $additional
	 * @return array<string, mixed>
	 */
	public static function prompt( string $result, array $provider_response = [], array $additional = [] ): array {
		$current_provider = LLPhantBootstrap::getCurrentProvider() ?? 'unknown';

		$response = [
			'status' => 'completed',
			'result' => $result,
			'meta'   => [
				'timestamp'         => time(),
				'provider'          => $current_provider,
				'provider_response' => $provider_response,
				'node_id'           => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			],
		];

		// Add provider stats if available
		$provider_stats = Benchmark::extractProviderStats( $provider_response );
		if ( ! empty( $provider_stats ) ) {
			$response['meta']['provider_stats'] = $provider_stats;
		}

		// Merge additional data
		if ( ! empty( $additional ) ) {
			$response = array_merge_recursive( $response, $additional );
		}

		// Enhance with benchmark data
		return Benchmark::enhanceResponse( $response );
	}

	/**
	 * @param array<string, mixed> $tool_calls
	 * @param array<string, mixed> $additional
	 * @return array<string, mixed>
	 */
	public static function tool_prompt( string $result, array $tool_calls = [], array $additional = [] ): array {
		$current_provider = LLPhantBootstrap::getCurrentProvider() ?? 'unknown';

		$response = [
			'status'     => 'completed',
			'result'     => $result,
			'tool_calls' => $tool_calls,
			'meta'       => [
				'timestamp'  => time(),
				'provider'   => $current_provider,
				'tool_count' => count( $tool_calls ),
				'node_id'    => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			],
		];

		// Merge additional data
		if ( ! empty( $additional ) ) {
			$response = array_merge_recursive( $response, $additional );
		}

		// Enhance with benchmark data
		return Benchmark::enhanceResponse( $response );
	}

	/**
	 * @param array<string, mixed> $stats
	 * @param array<string, mixed> $additional
	 * @return array<string, mixed>
	 */
	public static function extraction( string $result, array $stats = [], array $additional = [] ): array {
		$response = [
			'status' => 'completed',
			'result' => $result,
			'meta'   => [
				'timestamp'        => time(),
				'extraction_stats' => $stats,
				'node_id'          => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			],
		];

		// Merge additional data
		if ( ! empty( $additional ) ) {
			$response = array_merge_recursive( $response, $additional );
		}

		// Enhance with benchmark data
		return Benchmark::enhanceResponse( $response );
	}

	/**
	 * @param array<string, mixed> $meta
	 * @return array<string, mixed>
	 */
	public static function success( string $message = 'Operation completed successfully', array $meta = [] ): array {
		$response = [
			'status'  => 'success',
			'message' => $message,
			'meta'    => array_merge( [
				'timestamp' => time(),
				'node_id'   => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			], $meta ),
		];

		// Enhance with benchmark data
		return Benchmark::enhanceResponse( $response );
	}

	/**
	 * @param array<string, mixed> $details
	 * @return array<string, mixed>
	 */
	public static function error( string $message, int $code = 500, array $details = [] ): array {
		$response = [
			'status'  => 'error',
			'message' => $message,
			'code'    => $code,
			'meta'    => [
				'timestamp' => time(),
				'node_id'   => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			],
		];

		if ( ! empty( $details ) ) {
			$response['details'] = $details;
		}

		// Enhance with benchmark data
		return Benchmark::enhanceResponse( $response );
	}

	/**
	 * @param array<string, mixed> $manager_response
	 * @return array<string, mixed>
	 */
	public static function from_manager( array $manager_response ): array {
		$response = [
			'status'           => 'completed',
			'manager_response' => $manager_response,
			'meta'             => [
				'timestamp' => time(),
				'node_id'   => getenv( 'QIT_NODE_ID' ) ?: 'unknown',
			],
		];

		// Enhance with benchmark data
		return Benchmark::enhanceResponse( $response );
	}
}

// Add the missing Benchmark class for static method calls
class Benchmark {
	/**
	 * @param array<string, mixed> $provider_response
	 * @return array<string, mixed>
	 */
	public static function extractProviderStats( array $provider_response ): array {
		// Extract provider statistics from response
		return [
			'tokens_used'   => $provider_response['tokens_used'] ?? 0,
			'model'         => $provider_response['model'] ?? 'unknown',
			'response_time' => $provider_response['response_time'] ?? 0,
		];
	}

	/**
	 * @param array<string, mixed> $response
	 * @return array<string, mixed>
	 */
	public static function enhanceResponse( array $response ): array {
		// Add benchmark/performance data to response
		$response['meta']['benchmark'] = [
			'memory_usage'      => memory_get_usage( true ),
			'peak_memory_usage' => memory_get_peak_usage( true ),
			'execution_time'    => microtime( true ) - ( $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime( true ) ),
		];

		return $response;
	}

	/**
	 * CamelCase alias for tool_prompt method
	 *
	 * @param string               $result
	 * @param array<string, mixed> $tool_calls
	 * @param array<string, mixed> $model
	 * @param array<string, mixed> $additional
	 * @return array<string, mixed>
	 */
	public static function toolPrompt( string $result, array $tool_calls = [], array $model = [], array $additional = [] ): array {
		$response = \QIT_AI_Webserver\NodeResponse::tool_prompt( $result, $tool_calls, array_merge( $model, $additional ) );
		return $response;
	}
}
