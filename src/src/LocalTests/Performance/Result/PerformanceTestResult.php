<?php

namespace QIT_CLI\LocalTests\Performance\Result;

use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;

class PerformanceTestResult {
	/** @var PerformanceEnvInfo */
	private $env_info;

	/** @var string */
	private $results_dir;

	/** @var array<string, string> */
	private $result_files = [];

	/** @var array<string, mixed> */
	private $metrics = [];

	/** @var int */
	private $start_time;

	/** @var int */
	private $end_time;

	/** @var string */
	private $test_run_id;

	/** @var string */
	public $status = 'pending';

	/** @var array<mixed> */
	public $bootstrap = [];

	/** @var bool */
	private $results_processed = false;

	public function __construct( PerformanceEnvInfo $env_info ) {
		$this->env_info    = $env_info;
		$this->start_time  = time();
		$this->test_run_id = uniqid( 'perf_test_' );
		$this->results_dir = $this->create_results_directory();
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

	public function process_results(): void {
		// Only process results once.
		if ( $this->results_processed ) {
			return;
		}

		$this->end_time = time();

		// Process k6 JSON results.
		$this->process_k6_results();

		$this->results_processed = true;
	}

	private function create_results_directory(): string {
		if ( ! empty( getenv( 'QIT_RESULTS_DIR' ) ) ) {
			$results_dir = \QIT_CLI\normalize_path( getenv( 'QIT_RESULTS_DIR' ) );
		} else {
			$results_dir = \QIT_CLI\normalize_path( sys_get_temp_dir() ) . "qit-results-{$this->env_info->env_id}";
		}

		if ( ! file_exists( $results_dir ) ) {
			if ( ! mkdir( $results_dir, 0755, true ) ) {
				throw new \RuntimeException( 'Could not create results directory: ' . $results_dir );
			}
		}

		return $results_dir;
	}

	private function process_k6_results(): void {
		$k6_results_file = $this->results_dir . '/k6-results.json';

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
			if ( ! $data ) {
				continue;
			}

			if ( $data['type'] === 'Metric' ) {
				$this->add_metric( $data['metric'], $data['data'] );
			} elseif ( $data['type'] === 'Point' ) {
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

		foreach ( $points as $point ) {
			if ( isset( $point['metric'] ) && $point['metric'] === 'http_req_duration' && isset( $point['data']['value'] ) ) {
				$http_req_durations[] = $point['data']['value'];
			} elseif ( isset( $point['metric'] ) && $point['metric'] === 'http_req_failed' && isset( $point['data']['value'] ) ) {
				$http_req_failed += $point['data']['value'];
			} elseif ( isset( $point['metric'] ) && $point['metric'] === 'http_reqs' && isset( $point['data']['value'] ) ) {
				// Count actual HTTP requests, not all data points.
				$http_req_total += $point['data']['value'];
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
		$k6_dashboard_report = $this->results_dir . '/k6-dashboard-report.html';
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
}
