<?php

namespace QIT_CLI\Performance\Result;

use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use function QIT_CLI\normalize_path;

class PerformanceTestResult {
	/** @var PerformanceEnvInfo */
	private $env_info;

	/** @var string */
	private $results_dir;

	/** @var array<string, string> */
	private $result_files = [];

	/** @var array<string, mixed> */
	private $metrics = [];

	/**
	 * Result filenames from manifest.
	 *
	 * - 'summary' (required): Aggregated statistics JSON
	 * - 'json' (required): Extended k6 JSON output with detailed metrics and Core Web Vitals
	 * - 'dashboard' (optional): HTML dashboard report if configured
	 *
	 * @var array{summary: string, json: string, dashboard?: string}
	 */
	private $result_filenames = [
		'summary' => 'result.json',
		'json'    => 'result-extended.json',
	];

	/** @var int
	 * @phpstan-ignore-next-line
	 */
	private $start_time;

	/** @var int
	 * @phpstan-ignore-next-line
	 */
	private $end_time;

	/** @var string */
	private $test_run_id;

	/** @var string */
	public $status = 'pending';

	/** @var array<mixed> */
	public $bootstrap = [];

	/** @var bool */
	private $results_processed = false;

	/** @var bool */
	private $is_baseline = false;

	/** @var PerformanceTestResult|null */
	private $baseline_result = null;

	/**
	 * @param PerformanceEnvInfo                                           $env_info
	 * @param array{summary: string, json: string, dashboard: string}|null $result_filenames Optional result filenames from manifest.
	 */
	public function __construct( PerformanceEnvInfo $env_info, ?array $result_filenames = null ) {
		$this->env_info    = $env_info;
		$this->start_time  = time();
		$this->test_run_id = uniqid( 'perf_test_' );
		$this->results_dir = $this->create_results_directory();

		if ( $result_filenames !== null ) {
			$this->result_filenames = $result_filenames;
		}
	}

	public function get_results_dir(): string {
		return $this->results_dir;
	}

	public function get_test_run_id(): string {
		return $this->test_run_id;
	}

	public function add_result_file( string $filename, string $file_path ): void {
		$this->result_files[ $filename ] = $file_path;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public function add_metric( string $name, $value ): void {
		$this->metrics[ $name ] = $value;
	}

	/**
	 * Set result filenames from manifest.
	 *
	 * @param array{summary: string, json: string, dashboard: string} $filenames
	 */
	public function set_result_filenames( array $filenames ): void {
		$this->result_filenames = $filenames;
	}

	/**
	 * Get result filenames.
	 *
	 * @return array{summary: string, json: string, dashboard?: string}
	 */
	public function get_result_filenames(): array {
		return $this->result_filenames;
	}

	public function process_results(): void {
		// Only process results once.
		if ( $this->results_processed ) {
			return;
		}

		$this->end_time = time();

		// Process k6 JSON results.
		$this->process_k6_results();

		// Write processed metrics to result.json for compatibility.
		$this->write_result_json();

		$this->results_processed = true;
	}

	private function create_results_directory(): string {
		$base_dir = ! empty( getenv( 'QIT_RESULTS_DIR' ) )
			? getenv( 'QIT_RESULTS_DIR' )
			: sys_get_temp_dir() . '/qit-results';

		$results_dir = normalize_path( $base_dir, false ) . '/' . $this->env_info->env_id;

		if ( ! file_exists( $results_dir ) ) {
			if ( ! mkdir( $results_dir, 0777, true ) ) {
				throw new \RuntimeException( 'Could not create results directory: ' . $results_dir );
			}
		}

		return $results_dir;
	}

	private function process_k6_results(): void {
		$k6_results_file = $this->results_dir . '/' . $this->result_filenames['json'];

		if ( ! file_exists( $k6_results_file ) ) {
			return;
		}

		$results_content = file_get_contents( $k6_results_file );
		$results_lines   = explode( "\n", trim( $results_content ) );

		$points = [];

		foreach ( $results_lines as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$data = json_decode( $line, true );
			if ( ! $data || ! isset( $data['type'] ) ) {
				continue;
			}

			// We only need Point data - Metric entries just contain metadata.
			if ( $data['type'] === 'Point' ) {
				$points[] = $data;
			}
		}

		// Calculate summary statistics from points.
		$this->calculate_summary_statistics( $points );
	}

	/**
	 * @param array<mixed> $points
	 */
	private function calculate_summary_statistics( array $points ): void {
		$http_req_durations = [];
		$http_req_failed    = 0;
		$http_req_total     = 0;

		// Collect Web Vitals and other metrics.
		$metrics_data = [];

		foreach ( $points as $point ) {
			if ( ! isset( $point['metric'] ) || ! isset( $point['data']['value'] ) ) {
				continue;
			}

			$metric = $point['metric'];
			$value  = $point['data']['value'];

			// Collect values for each metric.
			if ( ! isset( $metrics_data[ $metric ] ) ) {
				$metrics_data[ $metric ] = [];
			}
			$metrics_data[ $metric ][] = $value;

			// Special handling for specific metrics.
			if ( $metric === 'http_req_duration' ) {
				$http_req_durations[] = $value;
			} elseif ( $metric === 'http_req_failed' ) {
				$http_req_failed += $value;
			} elseif ( $metric === 'http_reqs' ) {
				$http_req_total += $value;
			}
		}

		if ( ! empty( $http_req_durations ) ) {
			sort( $http_req_durations );
			$count = count( $http_req_durations );

			$this->add_metric( 'summary_http_req_duration_avg', array_sum( $http_req_durations ) / $count );
			$this->add_metric( 'summary_http_req_duration_min', min( $http_req_durations ) );
			$this->add_metric( 'summary_http_req_duration_max', max( $http_req_durations ) );
			$this->add_metric( 'summary_http_req_duration_median', $this->calculate_percentile( $http_req_durations, 50 ) );
			$this->add_metric( 'summary_http_req_duration_p95', $this->calculate_percentile( $http_req_durations, 95 ) );
			$this->add_metric( 'summary_http_req_duration_p99', $this->calculate_percentile( $http_req_durations, 99 ) );
		}

		if ( $http_req_total > 0 ) {
			$this->add_metric( 'summary_http_req_failed_rate', $http_req_failed / $http_req_total );
		}

		$this->add_metric( 'summary_http_req_total', $http_req_total );
		$this->add_metric( 'summary_http_req_failed', $http_req_failed );

		// Process all collected metrics to calculate statistics.
		foreach ( $metrics_data as $metric_name => $values ) {
			// Calculate statistics for this metric.
			sort( $values );
			$count = count( $values );

			$stats = [
				'avg' => array_sum( $values ) / $count,
				'med' => $this->calculate_percentile( $values, 50 ),
				'min' => min( $values ),
				'max' => max( $values ),
				'p95' => $this->calculate_percentile( $values, 95 ),
			];

			// For checks metric, calculate pass/fail counts.
			if ( $metric_name === 'checks' ) {
				$passes = array_sum( array_filter( $values, function ( $v ) {
					return $v > 0;
				} ) );
				$fails  = count( $values ) - $passes;
				$stats  = [
					'passes' => $passes,
					'fails'  => $fails,
				];
			}

			// Store the processed metric with clean structure.
			$this->add_metric( $metric_name, $stats );
		}
	}

	/**
	 * @param array<float> $values
	 */
	private function calculate_percentile( array $values, float $percentile ): float {
		$count = count( $values );
		$index = ( $percentile / 100 ) * ( $count - 1 );

		if ( $index === floor( $index ) ) {
			return $values[ (int) $index ];
		} else {
			$lower = $values[ (int) floor( $index ) ];
			$upper = $values[ (int) ceil( $index ) ];
			return $lower + ( $upper - $lower ) * ( $index - floor( $index ) );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_metrics(): array {
		return $this->metrics;
	}


	/**
	 * @return array<string, string>
	 */
	public function get_result_files(): array {
		return $this->result_files;
	}

	public function get_artifacts_path(): string {
		return $this->results_dir;
	}

	public function get_report_url(): string {
		if ( ! isset( $this->result_filenames['dashboard'] ) ) {
			return '';
		}

		$k6_dashboard_report = $this->results_dir . '/' . $this->result_filenames['dashboard'];
		return file_exists( $k6_dashboard_report ) ? $k6_dashboard_report : '';
	}

	/**
	 * Get environment info - required for LocalTestRunNotifier compatibility.
	 */
	public function get_env_info(): PerformanceEnvInfo {
		return $this->env_info;
	}

	/**
	 * Set test status - required for LocalTestRunNotifier compatibility.
	 */
	public function set_status( string $status ): void {
		$this->status = $status;
	}

	/**
	 * Get basic failure information.
	 *
	 * @return array<string, mixed>
	 */
	public function get_failure_details(): array {
		$details = [
			'failed_thresholds' => [],
			'failed_checks'     => [],
			'summary'           => '',
		];

		// For failed tests, provide basic failure information.
		if ( $this->status === 'failed' ) {
			$failed_rate  = $this->metrics['summary_http_req_failed_rate'] ?? 0;
			$p95_duration = $this->metrics['summary_http_req_duration_p95'] ?? 0;

			$issues = [];

			if ( $failed_rate > 0.1 ) { // >10% failure rate.
				$issues[] = sprintf( 'High failure rate: %.1f%%', $failed_rate * 100 );
			}

			if ( $p95_duration > 5000 ) { // >5 second response time.
				$issues[] = sprintf( 'Slow response time: %.0fms (p95)', $p95_duration );
			}

			$details['summary'] = ! empty( $issues ) ? implode( ', ', $issues ) : 'Performance test failed (check k6 output for details)';
		} else {
			$details['summary'] = 'Test completed successfully';
		}

		return $details;
	}

	/**
	 * Set whether this is a baseline test result.
	 */
	public function set_baseline( bool $is_baseline ): void {
		$this->is_baseline = $is_baseline;
	}

	/**
	 * Check if this is a baseline test result.
	 */
	public function is_baseline(): bool {
		return $this->is_baseline;
	}

	/**
	 * Set the baseline test result for comparison.
	 */
	public function set_baseline_result( PerformanceTestResult $baseline_result ): void {
		$this->baseline_result = $baseline_result;
	}

	/**
	 * Get the baseline test result for comparison.
	 */
	public function get_baseline_result(): ?PerformanceTestResult {
		return $this->baseline_result;
	}

	/**
	 * Write processed metrics to result.json for compatibility.
	 * Preserves original k6 data and adds our processed metrics.
	 */
	private function write_result_json(): void {
		$result_file = $this->results_dir . '/' . $this->result_filenames['summary'];

		// Preserve original k6 data if it exists.
		if ( file_exists( $result_file ) ) {
			$original_content = file_get_contents( $result_file );
			if ( $original_content !== false ) {
				$original_data = json_decode( $original_content, true );
				if ( is_array( $original_data ) ) {
					// Add our processed metrics to the original k6 data.
					$original_data['processed_metrics'] = $this->metrics;
					file_put_contents( $result_file, json_encode( $original_data, JSON_PRETTY_PRINT ) );
					return;
				}
			}
		}

		// Fallback: Create minimal structure if no original k6 data.
		$result_data = [
			'metrics'    => $this->metrics,
			'root_group' => [
				'name'   => $this->env_info->sut['slug'] ?? 'performance-test',
				'path'   => '::',
				'id'     => uniqid(),
				'groups' => [],
				'checks' => [],
			],
		];

		file_put_contents( $result_file, json_encode( $result_data, JSON_PRETTY_PRINT ) );
	}
}
