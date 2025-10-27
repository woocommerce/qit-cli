<?php

namespace QIT_CLI\Performance;

class PerformanceTestConfig {
	// File size limits.
	public const MAX_REPORT_SIZE_BYTES = 200 * 1024 * 1024; // 200MB.
	public const DEBUG_LOG_SIZE_BYTES  = 8 * 1024 * 1024; // 8MB.

	// Timeouts.
	public const DEFAULT_TIMEOUT_SECONDS = 60;

	// Comparison thresholds.
	public const COMPARISON_STATUS_THRESHOLDS = [
		'negligible' => 5.0,    // <= 5% change.
		'noticeable' => 15.0,   // <= 15% change.
	];

	// Metrics to extract and compare.
	public const PERFORMANCE_METRICS = [
		'browser_web_vital_ttfb',
		'browser_web_vital_fcp',
		'browser_web_vital_lcp',
		'browser_web_vital_inp',
		'browser_web_vital_cls',
		'checks',
	];

	public const METRICS_TO_COMPARE = [
		'browser_web_vital_ttfb',
		'browser_web_vital_fcp',
		'browser_web_vital_lcp',
		'browser_web_vital_inp',
		'browser_web_vital_cls',
		'summary_http_req_duration_avg',
	];

	// Metric thresholds for performance assessment.
	public const METRIC_THRESHOLDS = [
		'browser_web_vital_ttfb' => [
			'good'              => 800,
			'needs_improvement' => 1800,
		],
		'browser_web_vital_fcp'  => [
			'good'              => 1800,
			'needs_improvement' => 3000,
		],
		'browser_web_vital_lcp'  => [
			'good'              => 2500,
			'needs_improvement' => 4000,
		],
		'browser_web_vital_inp'  => [
			'good'              => 200,
			'needs_improvement' => 500,
		],
		'browser_web_vital_cls'  => [
			'good'              => 0.1,
			'needs_improvement' => 0.25,
		],
	];

	// Test defaults.
	public const DEFAULT_TEST_TAGS = [ 'default' ];
	public const DEFAULT_SUT_TYPE  = 'plugin';
}
