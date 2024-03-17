<?php

namespace QIT_CLI\Tests\E2E\Result;

class TestResult {
	public $status;

	public $bootstrap = [];

	public function __construct() {
		$this->status = 'pending';
	}

	public function initialize_plugin_result( $plugin_name ) {
		$this->results[ $plugin_name ] = [
			'status'       => 'pending',
			'report'       => '',
			'test_runner'  => '',
			'total_tests'  => [],
			'tests_run'    => [],
			'tests_failed' => [],
			'debug_log'    => '',
		];
	}
}
