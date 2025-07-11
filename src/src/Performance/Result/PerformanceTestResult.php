<?php

namespace QIT_CLI\Performance\Result;

use QIT_CLI\Config;
use QIT_CLI\Performance\Environment\PerformanceEnvInfo;

class PerformanceTestResult {
	/** @var PerformanceEnvInfo */
	private $env_info;

	/** @var string */
	private $results_dir;

	/** @var array */
	private $result_files = [];

	/** @var array */
	private $metrics = [];

	/** @var int */
	private $start_time;

	/** @var int */
	private $end_time;

	/** @var string */
	private $test_run_id;

	public function __construct( PerformanceEnvInfo $env_info ) {
		$this->env_info = $env_info;
		$this->start_time = time();
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

	public function add_metric( string $name, $value ): void {
		$this->metrics[ $name ] = $value;
	}

	public function process_results(): void {
		$this->end_time = time();

		// Process k6 JSON results
		$this->process_k6_results();

		// Generate summary report
		$this->generate_summary_report();

		// Generate HTML report
		$this->generate_html_report();
	}

	private function create_results_directory(): string {
		$timestamp = date( 'Y-m-d_H-i-s' );
		$results_dir = Config::get_qit_dir() . "results/performance/{$this->env_info->sut_slug}_{$timestamp}_{$this->test_run_id}";

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
		$results_lines = explode( "\n", trim( $results_content ) );

		$metrics = [];
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
				$metrics[ $data['metric'] ] = $data['data'];
			} elseif ( $data['type'] === 'Point' ) {
				$points[] = $data['data'];
			}
		}

		// Process metrics
		foreach ( $metrics as $metric_name => $metric_data ) {
			$this->add_metric( $metric_name, $metric_data );
		}

		// Calculate summary statistics
		$this->calculate_summary_statistics( $points );
	}

	private function calculate_summary_statistics( array $points ): void {
		$http_req_durations = [];
		$http_req_failed = 0;
		$http_req_total = 0;

		foreach ( $points as $point ) {
			if ( isset( $point['metric'] ) && $point['metric'] === 'http_req_duration' && isset( $point['value'] ) ) {
				$http_req_durations[] = $point['value'];
			} elseif ( isset( $point['metric'] ) && $point['metric'] === 'http_req_failed' && isset( $point['value'] ) ) {
				$http_req_failed += $point['value'];
			}
			$http_req_total++;
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

	private function generate_summary_report(): void {
		$duration = $this->end_time - $this->start_time;
		
		$summary = [
			'test_run_id' => $this->test_run_id,
			'sut_slug' => $this->env_info->sut_slug,
			'sut_type' => $this->env_info->sut_type,
			'start_time' => date( 'c', $this->start_time ),
			'end_time' => date( 'c', $this->end_time ),
			'duration_seconds' => $duration,
			'site_url' => $this->env_info->site_url,
			'metrics' => $this->metrics,
			'result_files' => array_keys( $this->result_files ),
		];

		file_put_contents( $this->results_dir . '/summary.json', json_encode( $summary, JSON_PRETTY_PRINT ) );
	}

	private function generate_html_report(): void {
		$html = $this->build_html_report();
		file_put_contents( $this->results_dir . '/report.html', $html );
	}

	private function build_html_report(): string {
		$duration = $this->end_time - $this->start_time;
		$avg_duration = $this->metrics['summary_http_req_duration_avg'] ?? 0;
		$p95_duration = $this->metrics['summary_http_req_duration_p95'] ?? 0;
		$failed_rate = $this->metrics['summary_http_req_failed_rate'] ?? 0;
		$total_requests = $this->metrics['summary_http_req_total'] ?? 0;

		$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Test Report - ' . htmlspecialchars( $this->env_info->sut_slug ) . '</title>
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Performance Test Report</h1>
        
        <div class="test-info">
            <h2>Test Information</h2>
            <table>
                <tr><td><strong>Extension:</strong></td><td>' . htmlspecialchars( $this->env_info->sut_slug ) . '</td></tr>
                <tr><td><strong>Test Run ID:</strong></td><td>' . htmlspecialchars( $this->test_run_id ) . '</td></tr>
                <tr><td><strong>Start Time:</strong></td><td>' . date( 'Y-m-d H:i:s', $this->start_time ) . '</td></tr>
                <tr><td><strong>Duration:</strong></td><td>' . $duration . ' seconds</td></tr>
                <tr><td><strong>Site URL:</strong></td><td>' . htmlspecialchars( $this->env_info->site_url ) . '</td></tr>
            </table>
        </div>

        <h2>Performance Metrics</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value">' . number_format( $avg_duration, 2 ) . ' ms</div>
                <div class="metric-label">Average Response Time</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">' . number_format( $p95_duration, 2 ) . ' ms</div>
                <div class="metric-label">95th Percentile Response Time</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">' . number_format( $failed_rate * 100, 2 ) . '%</div>
                <div class="metric-label">Failed Request Rate</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">' . $total_requests . '</div>
                <div class="metric-label">Total Requests</div>
            </div>
        </div>

        <h2>Test Results</h2>
        <div class="metric-card">
            <div class="metric-value ' . ( $failed_rate < 0.01 ? 'status-pass' : 'status-fail' ) . '">
                ' . ( $failed_rate < 0.01 ? '✓ PASS' : '✗ FAIL' ) . '
            </div>
            <div class="metric-label">Overall Test Status</div>
        </div>

        <h2>Result Files</h2>
        <ul class="file-list">';

		foreach ( $this->result_files as $filename => $file_path ) {
			$html .= '<li>' . htmlspecialchars( $filename ) . '</li>';
		}

		$html .= '</ul>
    </div>
</body>
</html>';

		return $html;
	}

	public function get_metrics(): array {
		return $this->metrics;
	}

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
}