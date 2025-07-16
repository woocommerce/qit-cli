<?php
namespace QIT_AI_Webserver;

/**
 * Performance benchmarking utility
 *
 * Handles detailed performance tracking separate from response formatting
 */
class Benchmark {
	private static ?float $request_start_time = null;
	/** @var array<array{name: string, time: float, data: array<string, mixed>}> */
	private static array $performance_markers = [];
	private static ?self $instance            = null;

	/**
	 * Get singleton instance
	 */
	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize benchmark tracking (call at request start)
	 */
	public static function init(): void {
		self::$request_start_time  = microtime( true );
		self::$performance_markers = [];
	}

	/**
	 * Mark a performance checkpoint
	 *
	 * @param string               $name Marker name.
	 * @param array<string, mixed> $data Optional data to associate with marker.
	 */
	public static function mark( string $name, array $data = [] ): void {
		self::$performance_markers[] = [
			'name' => $name,
			'time' => microtime( true ),
			'data' => $data,
		];
	}

	/**
	 * Get performance statistics
	 *
	 * @return array<string, mixed> Performance data
	 */
	public static function get_stats(): array {
		$end_time   = microtime( true );
		$total_time = self::$request_start_time ? ( $end_time - self::$request_start_time ) * 1000 : null;

		$stats = [
			'total_duration_ms' => $total_time ? round( $total_time, 2 ) : null,
			'timestamp'         => time(),
			'memory_peak_mb'    => round( memory_get_peak_usage( true ) / 1048576, 2 ),
		];

		// Add markers if any
		if ( ! empty( self::$performance_markers ) ) {
			$markers   = [];
			$last_time = self::$request_start_time;

			foreach ( self::$performance_markers as $marker ) {
				$duration                   = ( $marker['time'] - $last_time ) * 1000;
				$markers[ $marker['name'] ] = [
					'duration_ms'   => round( $duration, 2 ),
					'cumulative_ms' => round( ( $marker['time'] - self::$request_start_time ) * 1000, 2 ),
				];

				if ( ! empty( $marker['data'] ) ) {
					$markers[ $marker['name'] ]['data'] = $marker['data'];
				}

				$last_time = $marker['time'];
			}

			$stats['markers'] = $markers;
		}

		return $stats;
	}

	/**
	 * Extract token statistics from provider response
	 *
	 * @param array<string, mixed> $provider_response Raw provider response (Ollama, OpenAI, etc.).
	 * @return array<string, mixed> Token statistics
	 */
	public static function extract_provider_stats( array $provider_response ): array {
		$stats = [];

		// Ollama-style response format
		if ( isset( $provider_response['eval_count'] ) ) {
			$stats['tokens_generated'] = $provider_response['eval_count'];
		}

		if ( isset( $provider_response['eval_duration'] ) && $provider_response['eval_duration'] > 0 && isset( $provider_response['eval_count'] ) ) {
			$eval_seconds                    = $provider_response['eval_duration'] / 1000000000;
			$stats['tokens_per_second']      = round( $provider_response['eval_count'] / $eval_seconds, 2 );
			$stats['generation_duration_ms'] = round( $provider_response['eval_duration'] / 1000000, 2 );
		}

		if ( isset( $provider_response['prompt_eval_count'] ) ) {
			$stats['prompt_tokens'] = $provider_response['prompt_eval_count'];
		}

		if ( isset( $provider_response['prompt_eval_duration'] ) ) {
			$stats['prompt_eval_duration_ms'] = round( $provider_response['prompt_eval_duration'] / 1000000, 2 );
		}

		if ( isset( $provider_response['total_duration'] ) ) {
			$stats['total_duration_ms'] = round( $provider_response['total_duration'] / 1000000, 2 );
		}

		// OpenAI-style response format (usage object)
		if ( isset( $provider_response['usage'] ) ) {
			$usage = $provider_response['usage'];
			if ( isset( $usage['prompt_tokens'] ) ) {
				$stats['prompt_tokens'] = $usage['prompt_tokens'];
			}
			if ( isset( $usage['completion_tokens'] ) ) {
				$stats['tokens_generated'] = $usage['completion_tokens'];
			}
			if ( isset( $usage['total_tokens'] ) ) {
				$stats['total_tokens'] = $usage['total_tokens'];
			}
		}

		return $stats;
	}

	/**
	 * Add performance metrics to a response
	 *
	 * @param array<string, mixed> $response Response array to enhance with metrics.
	 * @return array<string, mixed> Enhanced response
	 */
	public static function enhance_response( array $response ): array {
		if ( ! isset( $response['meta'] ) ) {
			$response['meta'] = [];
		}

		$response['meta'] = array_merge(
			$response['meta'],
			self::get_stats()
		);

		return $response;
	}

	/**
	 * Add performance metrics to a response (camelCase alias)
	 *
	 * @param array<string, mixed> $response Response array to enhance with metrics.
	 * @return array<string, mixed> Enhanced response
	 */
	public static function enhanceResponse( array $response ): array {
		return self::enhance_response( $response );
	}

	/**
	 * Extract token statistics from provider response (camelCase alias)
	 *
	 * @param array<string, mixed> $provider_response Raw provider response (Ollama, OpenAI, etc.).
	 * @return array<string, mixed> Token statistics
	 */
	public static function extractProviderStats( array $provider_response ): array {
		return self::extract_provider_stats( $provider_response );
	}

	/**
	 * Alias for tool_prompt method
	 *
	 * @param array<string, mixed> $response
	 * @return array<string, mixed>
	 */
	public static function tool_prompt( array $response ): array {
		return self::enhance_response( $response );
	}
}
