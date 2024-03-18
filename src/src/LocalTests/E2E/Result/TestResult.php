<?php

namespace QIT_CLI\LocalTests\E2E\Result;

class TestResult {
	/** @var string */
	public $status;

	/** @var array<mixed> */
	public $bootstrap = [];

	/** @var array<mixed> */
	public $results = [];

	public function __construct() {
		$this->status = 'pending';
	}

	/**
	 * @param string $plugin_name
	 *
	 * @return void
	 */
	public function initialize_plugin_result( string $plugin_name ) {
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
