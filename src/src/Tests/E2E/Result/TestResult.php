<?php

namespace QIT_CLI\Tests\E2E\Result;

class TestResult {
	private $results;

	public function __construct() {
		$this->results = [];
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

	public function update_report_link( $plugin_name, $link ) {
		$this->results[ $plugin_name ]['report'] = $link;
	}

	public function add_test( $plugin_name, $test_name ) {
		$this->results[ $plugin_name ]['total_tests'][] = $test_name;
	}

	public function add_test_run( $plugin_name, $test_name ) {
		$this->results[ $plugin_name ]['tests_run'][] = $test_name;
	}

	public function add_test_failure( $plugin_name, $test_name ) {
		$this->results[ $plugin_name ]['tests_failed'][] = $test_name;
	}

	public function update_status( $plugin_name, $status ) {
		$this->results[ $plugin_name ]['status'] = $status;
	}

	public function set_debug_log( $plugin_name, $log_path ) {
		$this->results[ $plugin_name ]['debug_log'] = $log_path;
	}

	public function get_results() {
		return $this->results;
	}
}
