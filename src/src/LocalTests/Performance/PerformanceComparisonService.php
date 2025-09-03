<?php

namespace QIT_CLI\LocalTests\Performance;

use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;

class PerformanceComparisonService {
	/** @var MetricsExtractor */
	private $metrics_extractor;

	public function __construct( MetricsExtractor $metrics_extractor ) {
		$this->metrics_extractor = $metrics_extractor;
	}

	/**
	 * Calculate comparison metrics between baseline and main results.
	 *
	 * @param PerformanceTestResult $main_result The main test result.
	 * @param PerformanceTestResult $baseline_result The baseline test result.
	 */
	public function calculate_comparisons( PerformanceTestResult $main_result, PerformanceTestResult $baseline_result ): void {
		$main_metrics     = $this->load_metrics( $main_result );
		$baseline_metrics = $this->load_metrics( $baseline_result );

		foreach ( PerformanceTestConfig::METRICS_TO_COMPARE as $metric ) {
			$this->calculate_metric_comparison( $main_result, $metric, $main_metrics, $baseline_metrics );
		}
	}

	/**
	 * Load metrics from a test result.
	 * With our refactoring, metrics are always in a clean format.
	 *
	 * @param PerformanceTestResult $result The test result.
	 *
	 * @return array<string, mixed> The loaded metrics.
	 */
	private function load_metrics( PerformanceTestResult $result ): array {
		return $this->metrics_extractor->extract_metrics( $result->get_metrics() );
	}

	/**
	 * Calculate comparison for a single metric.
	 *
	 * @param PerformanceTestResult $main_result The main test result.
	 * @param string                $metric The metric name.
	 * @param array<string, mixed>  $main_metrics The main test metrics.
	 * @param array<string, mixed>  $baseline_metrics The baseline test metrics.
	 */
	private function calculate_metric_comparison(
		PerformanceTestResult $main_result,
		string $metric,
		array $main_metrics,
		array $baseline_metrics
	): void {

		if ( ! isset( $main_metrics[ $metric ] ) || ! isset( $baseline_metrics[ $metric ] ) ) {
			return;
		}

		$main_value     = $this->extract_metric_value( $main_metrics[ $metric ] );
		$baseline_value = $this->extract_metric_value( $baseline_metrics[ $metric ] );

		if ( $baseline_value <= 0 ) {
			return;
		}

		$percentage_change = ( ( $main_value - $baseline_value ) / $baseline_value ) * 100;
		$absolute_diff     = $main_value - $baseline_value;

		$main_result->add_metric( "{$metric}_vs_baseline_percent", $percentage_change );
		$main_result->add_metric( "{$metric}_vs_baseline_diff", $absolute_diff );
	}

	/**
	 * Extract a numeric value from metric data.
	 *
	 * @param mixed $metric_data The metric data.
	 *
	 * @return float The extracted value.
	 */
	private function extract_metric_value( $metric_data ): float {
		if ( ! is_array( $metric_data ) ) {
			return (float) $metric_data;
		}

		// Try different possible value locations in order of preference.
		foreach ( [ 'avg', 'value', 'med' ] as $key ) {
			if ( isset( $metric_data[ $key ] ) && is_numeric( $metric_data[ $key ] ) ) {
				return (float) $metric_data[ $key ];
			}
		}

		return 0.0;
	}
}
