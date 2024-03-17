<?php

namespace QIT_CLI\Tests\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Tests\E2E\Result\TestResult;
use QIT_CLI\Tests\E2E\Runner\E2ERunner;
use QIT_CLI\Tests\E2E\Runner\PlaywrightRunner;
use Symfony\Component\Console\Output\OutputInterface;

class E2ETestManager {
	/** @var Docker $docker */
	protected $docker;

	/** @var OutputInterface $output */
	protected $output;

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	/**
	 * @param E2EEnvInfo $env_info
	 * @param string $sut - System Under Test.
	 * @param string $compatibility_mode - "default" or "full".
	 */
	public function run_tests( E2EEnvInfo $env_info, string $sut, string $compatibility_mode ) {
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

		if ( ! in_array( $compatibility_mode, [ 'default', 'full' ] ) ) {
			throw new \InvalidArgumentException( 'Invalid compability mode' );
		}

		$test_result = new TestResult();

		/**
		 * Bootstrap all plugins.
		 */
		foreach ( $env_info->tests as $plugin_slug => $test_info ) {
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.php' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path'] . '/bootstrap/bootstrap.php' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "php /qit/tests/$plugin_slug/e2e/bootstrap/bootstrap.php" ] );
			}
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.sh' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path'] . '/bootstrap/bootstrap.sh' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "/qit/tests/$plugin_slug/e2e/bootstrap/bootstrap.sh" ] );
			}
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/must-use-plugin.php' ) ) {
				$this->output->writeln( sprintf( 'Moving must-use plugin of %s %s', $plugin_slug, $test_info['path'] . '/bootstrap/must-use-plugin.php' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "mv /qit/tests/$plugin_slug/e2e/bootstrap/must-use-plugin.php /var/www/html/wp-content/mu-plugins/$plugin_slug.php" ] );
			}
		}

		/**
		 * Run the tests.
		 */
		foreach ( $env_info->tests as $plugin_slug => $test_info ) {
			$is_sut = $plugin_slug === $sut;

			if ( $compatibility_mode === 'default' && ! $is_sut ) {
				continue;
			}

			$this->output->writeln( sprintf( 'Running tests for %s %s', $plugin_slug, $test_info['path_in_host'] ) );
			if ( E2ERunner::find_runner_type( $test_info['path_in_host'] ) === 'playwright' ) {
				App::make( PlaywrightRunner::class )->run_test( $env_info, $plugin_slug, $test_result );
			}
		}
	}
}