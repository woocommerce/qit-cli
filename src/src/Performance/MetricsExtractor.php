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
		$performance_results = [];

		foreach ( PerformanceTestConfig::PERFORMANCE_METRICS as $metric ) {
			if ( isset( $metrics[ $metric ] ) ) {
				$performance_results[ $metric ] = $metrics[ $metric ];
			}
		}

		return $performance_results;
	}
}
