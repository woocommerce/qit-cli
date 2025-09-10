<?php

namespace QIT_CLI\LocalTests\Performance;

use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;

/**
 * Optimized utility class for averaging metrics across multiple performance test runs.
 *
 * Provides stable metric calculation by running tests multiple times and
 * averaging the median values to reduce performance fluctuation.
 */
class MetricAverager {

	/**
	 * Average metrics from multiple test results.
	 *
	 * Takes an array of PerformanceTestResult objects and calculates the average
	 * of the median values for each metric across all runs.
	 *
	 * @param PerformanceTestResult[] $test_results Array of test results to average.
	 * @param PerformanceEnvInfo|null $original_env_info Original environment info without iteration suffix.
	 * @return PerformanceTestResult Combined result with averaged metrics.
	 */
	public function average_test_results( array $test_results, ?PerformanceEnvInfo $original_env_info = null ): PerformanceTestResult {
		if ( empty( $test_results ) ) {
			throw new \InvalidArgumentException( 'Cannot average empty test results array' );
		}

		$averaged_result = $this->create_averaged_result( $test_results[0], $original_env_info );

		// Collect and average all metrics in one step.
		foreach ( $this->collect_all_metrics( $test_results ) as $metric_name => $metric_values ) {
			$averaged_result->add_metric( $metric_name, $this->average_metric( $metric_values ) );
		}

		// Copy files and write results.
		$this->copy_result_files( end( $test_results ), $averaged_result );
		$this->write_averaged_results( $averaged_result );

		return $averaged_result;
	}

	/**
	 * Create a new PerformanceTestResult for averaged data.
	 */
	private function create_averaged_result( PerformanceTestResult $base_result, ?PerformanceEnvInfo $original_env_info = null ): PerformanceTestResult {
		$env_info = $original_env_info ?: $base_result->get_env_info();

		$averaged_result = new PerformanceTestResult( $env_info );
		$averaged_result->set_status( $base_result->status );
		$averaged_result->set_baseline( $base_result->is_baseline() );
		return $averaged_result;
	}

	/**
	 * Collect all metrics from all test results.
	 */
	private function collect_all_metrics( array $test_results ): array {
		$all_metrics = [];

		foreach ( $test_results as $result ) {
			foreach ( $result->get_metrics() as $metric_name => $metric_data ) {
				$all_metrics[ $metric_name ][] = $metric_data;
			}
		}

		return $all_metrics;
	}

	/**
	 * Average a specific metric across multiple values.
	 */
	private function average_metric( array $metric_values ) {
		// Filter out nulls once at the beginning.
		$non_null_values = $this->filter_nulls( $metric_values );

		if ( empty( $non_null_values ) ) {
			return null;
		}

		$first_value = $non_null_values[0];

		// Route based on type.
		if ( is_array( $first_value ) ) {
			return $this->average_array_values( $metric_values );
		}

		// Handle scalars (numbers/strings).
		if ( is_numeric( $first_value ) ) {
			return array_sum( $non_null_values ) / count( $non_null_values );
		}

		return $this->get_most_common_value( $non_null_values );
	}

	/**
	 * Filter out null values from array and reindex.
	 */
	private function filter_nulls( array $values ): array {
		return array_values( array_filter( $values, function ( $v ) {
			return $v !== null;
		} ) );
	}

	/**
	 * Average array-based metrics (e.g., Core Web Vitals statistics).
	 */
	private function average_array_values( array $metric_arrays ): array {
		$first_array = $metric_arrays[0];

		// Special handling for checks metric (passes/fails).
		if ( isset( $first_array['passes'] ) && isset( $first_array['fails'] ) ) {
			return $this->average_checks_metric( $metric_arrays );
		}

		// Average each key across all arrays.
		$averaged_array = [];
		foreach ( array_keys( $first_array ) as $key ) {
			$key_values             = $this->extract_key_values( $metric_arrays, $key );
			$averaged_array[ $key ] = empty( $key_values ) ? null : array_sum( $key_values ) / count( $key_values );
		}

		return $averaged_array;
	}

	/**
	 * Average checks metric (passes/fails counts).
	 */
	private function average_checks_metric( array $metric_arrays ): array {
		$count = count( $metric_arrays );
		return [
			'passes' => array_sum( array_column( $metric_arrays, 'passes' ) ) / $count,
			'fails'  => array_sum( array_column( $metric_arrays, 'fails' ) ) / $count,
		];
	}

	/**
	 * Extract numeric values for a specific key from array of arrays.
	 */
	private function extract_key_values( array $metric_arrays, string $key ): array {
		$values = array_column( $metric_arrays, $key );
		return array_filter( $values, function ( $v ) {
			return $v !== null && is_numeric( $v );
		} );
	}

	/**
	 * Get the most common non-null value from an array.
	 */
	private function get_most_common_value( array $values ) {
		if ( empty( $values ) ) {
			return null;
		}

		$value_counts       = array_count_values( array_map( 'strval', $values ) );
		$most_common_string = array_keys( $value_counts, max( $value_counts ), true )[0];

		// Return original value that matches the most common string.
		foreach ( $values as $value ) {
			if ( strval( $value ) === $most_common_string ) {
				return $value;
			}
		}

		return $most_common_string;
	}

	/**
	 * Copy result files from source to target result.
	 */
	private function copy_result_files( PerformanceTestResult $source, PerformanceTestResult $target ): void {
		$files_to_copy = [ 'k6-dashboard-report.html', 'result-extended.json' ];

		foreach ( $files_to_copy as $filename ) {
			$source_file = $source->get_results_dir() . '/' . $filename;
			if ( file_exists( $source_file ) ) {
				$target_file = $target->get_results_dir() . '/' . $filename;
				copy( $source_file, $target_file );
				$target->add_result_file( $filename, $target_file );
			}
		}

		// Register all source result files for reference.
		foreach ( $source->get_result_files() as $filename => $file_path ) {
			$target->add_result_file( $filename, $file_path );
		}
	}

	/**
	 * Write averaged results to the result directory.
	 */
	private function write_averaged_results( PerformanceTestResult $averaged_result ): void {
		$results_dir = $averaged_result->get_results_dir();

		if ( ! file_exists( $results_dir ) && ! mkdir( $results_dir, 0755, true ) ) {
			throw new \RuntimeException( "Could not create results directory: $results_dir" );
		}

		// Write averaged result.json.
		file_put_contents( $results_dir . '/result.json', json_encode( [
			'metrics'    => $averaged_result->get_metrics(),
			'averaged'   => true,
			'root_group' => [
				'name' => 'averaged-performance-test',
				'path' => '::',
				'id'   => uniqid(),
			],
		], JSON_PRETTY_PRINT ) );

		// Write summary file.
		file_put_contents( $results_dir . '/averaged-summary.txt',
			"Performance Test Results - Averaged Metrics\n" .
			"============================================\n\n" .
			"This directory contains averaged performance test results from multiple iterations.\n" .
			"Individual iteration results are stored in subdirectories (iter1/, iter2/, iter3/).\n" .
			'Generated at: ' . gmdate( 'Y-m-d H:i:s' ) . " UTC\n"
		);
	}
}
