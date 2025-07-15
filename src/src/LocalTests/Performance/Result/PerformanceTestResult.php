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

		// Generate reports.
		$this->generate_reports();

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
	 * Generate both JSON summary and HTML reports.
	 */
	private function generate_reports(): void {
		$this->generate_summary_report();
		$this->generate_html_report();
	}

	/**
	 * Generate JSON summary report.
	 */
	private function generate_summary_report(): void {
		$duration = $this->end_time - $this->start_time;

		$summary = [
			'test_run_id'      => $this->test_run_id,
			'sut_slug'         => $this->env_info->sut_slug,
			'sut_type'         => $this->env_info->sut_type,
			'start_time'       => gmdate( 'c', $this->start_time ),
			'end_time'         => gmdate( 'c', $this->end_time ),
			'duration_seconds' => $duration,
			'site_url'         => $this->env_info->site_url,
			'status'           => $this->status,
			'metrics'          => $this->metrics,
			'result_files'     => array_keys( $this->result_files ),
		];

		file_put_contents( $this->results_dir . '/summary.json', json_encode( $summary, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Generate HTML report.
	 */
	private function generate_html_report(): void {
		$html = $this->build_html_report();
		file_put_contents( $this->results_dir . '/report.html', $html );
	}

	private function build_html_report(): string {
		$duration       = $this->end_time - $this->start_time;
		$avg_duration   = $this->metrics['summary_http_req_duration_avg'] ?? 0;
		$p95_duration   = $this->metrics['summary_http_req_duration_p95'] ?? 0;
		$failed_rate    = $this->metrics['summary_http_req_failed_rate'] ?? 0;
		$total_requests = $this->metrics['summary_http_req_total'] ?? 0;

		return sprintf(
			'<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Test Report - %s</title>
    %s
</head>
<body>
    <div class="container">
        <h1>Performance Test Report</h1>
        %s
        %s
        %s
        %s
    </div>
</body>
</html>',
			htmlspecialchars( $this->env_info->sut_slug ),
			$this->get_html_styles(),
			$this->get_test_info_html( $duration ),
			$this->get_metrics_html( $avg_duration, $p95_duration, $failed_rate, $total_requests ),
			$this->get_status_html(),
			$this->get_files_html()
		);
	}

	private function get_html_styles(): string {
		return '<style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .metric-card { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 4px; border-left: 4px solid #007cba; }
        .metric-value { font-size: 24px; font-weight: bold; color: #007cba; }
        .metric-label { color: #666; font-size: 14px; }
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0; }
        .test-info { background: #e8f4f8; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .status-pass { color: #28a745; }
        .status-fail { color: #dc3545; }
        .status-warn { color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .file-list { list-style: none; padding: 0; }
        .file-list li { padding: 8px; margin: 4px 0; background: #f8f9fa; border-radius: 4px; }
    </style>';
	}

	private function get_test_info_html( int $duration ): string {
		return sprintf(
			'<div class="test-info">
            <h2>Test Information</h2>
            <table>
                <tr><td><strong>Extension:</strong></td><td>%s</td></tr>
                <tr><td><strong>Test Run ID:</strong></td><td>%s</td></tr>
                <tr><td><strong>Start Time:</strong></td><td>%s</td></tr>
                <tr><td><strong>Duration:</strong></td><td>%d seconds</td></tr>
                <tr><td><strong>Site URL:</strong></td><td>%s</td></tr>
                <tr><td><strong>Status:</strong></td><td>%s</td></tr>
            </table>
        </div>',
			htmlspecialchars( $this->env_info->sut_slug ),
			htmlspecialchars( $this->test_run_id ),
			gmdate( 'Y-m-d H:i:s', $this->start_time ),
			$duration,
			htmlspecialchars( $this->env_info->site_url ),
			strtoupper( $this->status )
		);
	}

	private function get_metrics_html( float $avg_duration, float $p95_duration, float $failed_rate, int $total_requests ): string {
		return sprintf(
			'<h2>Performance Metrics</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value">%.2f ms</div>
                <div class="metric-label">Average Response Time</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">%.2f ms</div>
                <div class="metric-label">95th Percentile Response Time</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">%.2f%%</div>
                <div class="metric-label">Failed Request Rate</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">%d</div>
                <div class="metric-label">Total Requests</div>
            </div>
        </div>',
			$avg_duration,
			$p95_duration,
			$failed_rate * 100,
			$total_requests
		);
	}

	private function get_status_html(): string {
		switch ( $this->status ) {
			case 'success':
				$status_class = 'status-pass';
				$status_text  = '✓ PASSED';
				break;
			case 'warning':
				$status_class = 'status-warn';
				$status_text  = '⚠ WARNING';
				break;
			case 'failed':
				$status_class = 'status-fail';
				$status_text  = '✗ FAILED';
				break;
			default:
				$status_class = 'status-fail';
				$status_text  = strtoupper( $this->status );
				break;
		}

		return sprintf(
			'<h2>Test Results</h2>
        <div class="metric-card">
            <div class="metric-value %s">%s</div>
            <div class="metric-label">Overall Test Status</div>
        </div>',
			$status_class,
			$status_text
		);
	}

	private function get_files_html(): string {
		$files_html = '<h2>Result Files</h2><ul class="file-list">';

		foreach ( array_keys( $this->result_files ) as $filename ) {
			$files_html .= '<li>' . htmlspecialchars( $filename ) . '</li>';
		}

		return $files_html . '</ul>';
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
		$report_file = $this->results_dir . '/report.html';
		return file_exists( $report_file ) ? $report_file : '';
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

			$details['summary'] = ! empty( $issues ) ? implode( ', ', $issues ) : 'Performance test failed (check K6 output for details)';
		} else {
			$details['summary'] = 'Test completed successfully';
		}

		return $details;
	}
}
