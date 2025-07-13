<?php
namespace QIT_AI_Webserver;

/**
 * Performance benchmarking utility
 *
 * Handles detailed performance tracking separate from response formatting
 */
class Benchmark {
	private static ?float $requestStartTime  = null;
	private static array $performanceMarkers = [];
	private static ?self $instance           = null;

	/**
	 * Get singleton instance
	 */
	public static function getInstance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize benchmark tracking (call at request start)
	 */
	public static function init(): void {
		self::$requestStartTime   = microtime( true );
		self::$performanceMarkers = [];
	}

	/**
	 * Mark a performance checkpoint
	 *
	 * @param string $name Marker name
	 * @param array  $data Optional data to associate with marker
	 */
	public static function mark( string $name, array $data = [] ): void {
		self::$performanceMarkers[] = [
			'name' => $name,
			'time' => microtime( true ),
			'data' => $data,
		];
	}

	/**
	 * Get performance statistics
	 *
	 * @return array Performance data
	 */
	public static function getStats(): array {
		$endTime   = microtime( true );
		$totalTime = self::$requestStartTime ? ( $endTime - self::$requestStartTime ) * 1000 : null;

		$stats = [
			'total_duration_ms' => $totalTime ? round( $totalTime, 2 ) : null,
			'timestamp'         => time(),
			'memory_peak_mb'    => round( memory_get_peak_usage( true ) / 1048576, 2 ),
		];

		// Add markers if any
		if ( ! empty( self::$performanceMarkers ) ) {
			$markers  = [];
			$lastTime = self::$requestStartTime;

			foreach ( self::$performanceMarkers as $marker ) {
				$duration                   = ( $marker['time'] - $lastTime ) * 1000;
				$markers[ $marker['name'] ] = [
					'duration_ms'   => round( $duration, 2 ),
					'cumulative_ms' => round( ( $marker['time'] - self::$requestStartTime ) * 1000, 2 ),
				];

				if ( ! empty( $marker['data'] ) ) {
					$markers[ $marker['name'] ]['data'] = $marker['data'];
				}

				$lastTime = $marker['time'];
			}

			$stats['markers'] = $markers;
		}

		return $stats;
	}

	/**
	 * Extract token statistics from provider response
	 *
	 * @param array $providerResponse Raw provider response (Ollama, OpenAI, etc.)
	 * @return array Token statistics
	 */
	public static function extractProviderStats( array $providerResponse ): array {
		$stats = [];

		// Ollama-style response format
		if ( isset( $providerResponse['eval_count'] ) ) {
			$stats['tokens_generated'] = $providerResponse['eval_count'];
		}

		if ( isset( $providerResponse['eval_duration'] ) && $providerResponse['eval_duration'] > 0 && isset( $providerResponse['eval_count'] ) ) {
			$evalSeconds                     = $providerResponse['eval_duration'] / 1000000000;
			$stats['tokens_per_second']      = round( $providerResponse['eval_count'] / $evalSeconds, 2 );
			$stats['generation_duration_ms'] = round( $providerResponse['eval_duration'] / 1000000, 2 );
		}

		if ( isset( $providerResponse['prompt_eval_count'] ) ) {
			$stats['prompt_tokens'] = $providerResponse['prompt_eval_count'];
		}

		if ( isset( $providerResponse['prompt_eval_duration'] ) ) {
			$stats['prompt_eval_duration_ms'] = round( $providerResponse['prompt_eval_duration'] / 1000000, 2 );
		}

		if ( isset( $providerResponse['total_duration'] ) ) {
			$stats['total_duration_ms'] = round( $providerResponse['total_duration'] / 1000000, 2 );
		}

		// OpenAI-style response format (usage object)
		if ( isset( $providerResponse['usage'] ) ) {
			$usage = $providerResponse['usage'];
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
	 * @param array $response Response array to enhance with metrics
	 * @return array Enhanced response
	 */
	public static function enhanceResponse( array $response ): array {
		if ( ! isset( $response['meta'] ) ) {
			$response['meta'] = [];
		}

		$response['meta'] = array_merge(
			$response['meta'],
			self::getStats()
		);

		return $response;
	}
}
