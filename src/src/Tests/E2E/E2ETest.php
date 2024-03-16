<?php

namespace QIT_CLI\Tests\E2E;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Tests\E2E\Result\TestResult;

class E2ETest {
	/**
	 * @param EnvInfo $env_info
	 * @param string  $sut       - System Under Test.
	 * @param string  $test_mode - "default" or "full".
	 */
	public function run_tests( EnvInfo $env_info, string $sut, string $test_mode ) {
		/**
		 * 1. Iterate over the list of plugins
		 * 2. See which of them have E2E tests
		 * 3. Initialize a TestResult with the list of plugins, eg:
		 *  3.1 gutenberg:
		 *        - status: pending
		 *        - report: "/link-to-allure-report"
		 *        - total_tests: [
		 *          - test_1
		 *          - test_2
		 *          ]
		 *        - tests_run: [
		 *          - test_1
		 *          - test_2
		 *          ]
		 *        - tests_failed: [
		 *            - test_1
		 *            - test_2
		 *          ]
		 *        - debug_log: "/path/to/this-plugin-debug.log"
		 *  4. If $test_mode is "default", we run the bootstrap phase of all plugins, and the test phase of the sut.
		 *  If $test_mode is "full", we run the bootstrap phase of all plugins, and the test phase of all plugins.
		 *  5. Update the TestResult with the actual results
		 */
		$plugins      = $env_info->plugins;
		$plugins_dirs = $env_info->temporary_env . '/wp-content/plugins';

		$test_result = new TestResult();

		foreach ( $plugins as $plugin ) {
			$test_result->initialize_plugin_result( $plugin );
		}

		foreach ( $plugins as $plugin ) {
			// Set the runner.
			$runner = E2ERunner::find_runner_type( $plugins_dirs . '/' . $plugin );
		}

		foreach ( $plugins as $plugin ) {
			$this->bootstrap_plugin( $env_info, $plugin );
		}

		if ( $test_mode === 'default' ) {
			$this->run_test( $env_info, $sut );
		} else {
			foreach ( $plugins as $plugin ) {
				$this->run_test( $env_info, $plugin );
			}
		}
	}
}
