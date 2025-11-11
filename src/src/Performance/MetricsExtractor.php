<?php

namespace QIT_CLI\Performance;

class MetricsExtractor {
	/**
	 * Extract performance metrics based on configured metrics list.
	 * With our refactoring, all metrics are stored in a clean, processed format.
	 *
	 * @param array<string, mixed> $metrics The metrics array.
	 *
	 * @return array<string, mixed> The extracted performance metrics.
	 */
	public function extract_metrics( array $metrics ): array {
		// Debug: Log all available metrics before extraction.
		error_log( '[MetricsExtractor] Available metrics: ' . implode( ', ', array_keys( $metrics ) ) );
		error_log( '[MetricsExtractor] Total metrics count: ' . count( $metrics ) );

		$performance_results = [];
		$missing_metrics     = [];

		foreach ( PerformanceTestConfig::PERFORMANCE_METRICS as $metric ) {
			if ( isset( $metrics[ $metric ] ) ) {
				$performance_results[ $metric ] = $metrics[ $metric ];
				error_log( sprintf( '[MetricsExtractor] ✓ Extracted metric: %s', $metric ) );
			} else {
				$missing_metrics[] = $metric;
				error_log( sprintf( '[MetricsExtractor] ✗ Missing expected metric: %s', $metric ) );
			}
		}

		// Summary logging.
		error_log( sprintf(
			'[MetricsExtractor] Extraction summary: %d/%d metrics extracted, %d missing',
			count( $performance_results ),
			count( PerformanceTestConfig::PERFORMANCE_METRICS ),
			count( $missing_metrics )
		) );

		if ( ! empty( $missing_metrics ) ) {
			error_log( '[MetricsExtractor] Missing metrics: ' . implode( ', ', $missing_metrics ) );
		}

		return $performance_results;
	}
}
