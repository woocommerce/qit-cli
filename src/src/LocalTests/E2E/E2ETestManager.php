<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Commands\TestRuns\RunE2ECommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\E2E\Runner\E2ERunner;
use QIT_CLI\LocalTests\E2E\Runner\PlaywrightRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class E2ETestManager {
	/** @var Docker $docker */
	protected $docker;

	/** @var OutputInterface $output */
	protected $output;

	/**
	 * @var array<string, string>
	 */
	public static $test_modes = [
		'headless' => 'headless',
		'ui'       => 'ui',
		'codegen'  => 'codegen',
	];

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	/**
	 * @param E2EEnvInfo $env_info
	 * @param string     $sut - System Under Test.
	 * @param string     $compatibility_mode - "default" or "full".
	 * @param string     $test_mode One of the allowed test modes.
	 * @param bool       $bootstrap_only If true, will only bootstrap.
	 */
	public function run_tests( E2EEnvInfo $env_info, string $sut, string $compatibility_mode, string $test_mode, bool $bootstrap_only ): void {
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
		$test_result = TestResult::init_from( $env_info );

		$this->output->writeln( '<info>Bootstrapping Plugins</info>' );

		/**
		 * Bootstrap all plugins.
		 */
		foreach ( $env_info->tests as $plugin_slug => $test_info ) {
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.php' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path_in_container'] . '/bootstrap/bootstrap.php' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "php /qit/tests/e2e/$plugin_slug/bootstrap/bootstrap.php" ] );
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'processed' );
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'not_present' );
			}
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.sh' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path_in_container'] . '/bootstrap/bootstrap.sh' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "chmod +x /qit/tests/e2e/$plugin_slug/bootstrap/bootstrap.sh && /qit/tests/e2e/$plugin_slug/bootstrap/bootstrap.sh" ] );
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'processed' );
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'not_present' );
			}
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/must-use-plugin.php' ) ) {
				$this->output->writeln( sprintf( 'Moving must-use plugin of %s %s', $plugin_slug, $test_info['path_in_container'] . '/bootstrap/must-use-plugin.php' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "mv /qit/tests/e2e/$plugin_slug/bootstrap/must-use-plugin.php /var/www/html/wp-content/mu-plugins/qit-mu-$plugin_slug.php" ] );
				$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'processed' );
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'not_present' );
			}
		}

		if ( $bootstrap_only ) {
			if ( $test_mode === 'codegen' ) {
				$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

				$io->note( 'To run the Playwright Codegen, please ensure Playwright is installed on your machine.' );

				$io->text( [
					'Please run Playwright Codegen locally using the URLs above. After generating tests:',
					'  - Remove all hardcoded URLs from the generated tests.',
					'  - Assume that Playwright\'s "baseURL" is set on the environment your tests will run.',
					'  - Ensure your tests are flexible and follows good practices on choosing selectors.',
				] );

				$io->newLine();

				$io->text( 'For detailed instructions and best practices, please refer to our Codegen guide: https://qit.woo.com/docs/codegen' );
				$io->text( 'When you are done writing tests, return here and press Enter to shut down the environment.' );
				$io->success( 'Run Playwright Codegen from your computer now.' );
			}

			$this->output->writeln( '' );
			$this->output->writeln( '<comment>Environment ready. Press "Enter" when you are done to terminate it.</comment>' );
			RunE2ECommand::press_enter_to_wait_without_terminating();

			return;
		}

		$this->output->writeln( '<info>Running E2E Tests</info>' );

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
				App::make( PlaywrightRunner::class )->run_test( $env_info, $plugin_slug, $test_result, $test_mode );
				$test_result->register_test_results( $plugin_slug, $test_result->get_results_dir() . "/$plugin_slug" );
			}
		}

		$test_result->set_status('completed');

		// Print path for results and reports.
		$this->output->writeln( sprintf( 'Results and reports are available at %s', $test_result->get_results_dir() ) );
	}
}
