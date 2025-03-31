<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * PHP 7.2–compatible, uses snake_case variables and "thisCase" for class name.
 * Manages "Custom E2E" tests orchestration by calling each plugin's "npm run qit-e2e"
 * and handling shared setups, DB snapshots, result merges, etc.
 */
class SpecCustomTestOrchestrator {
	/**
	 * @var Docker
	 */
	protected $docker;

	/**
	 * @var LocalTestRunNotifier
	 */
	protected $local_test_run_notifier;

	/**
	 * @var TestResult|null
	 */
	protected $test_result = null;

	/**
	 * @var ExtensionTestRunner
	 */
	protected $extension_test_runner;

	/**
	 * Constructor
	 *
	 * @param Docker               $docker
	 * @param LocalTestRunNotifier $local_test_run_notifier
	 */
	public function __construct( Docker $docker, LocalTestRunNotifier $local_test_run_notifier, ExtensionTestRunner $extension_test_runner ) {
		$this->docker                  = $docker;
		$this->local_test_run_notifier = $local_test_run_notifier;
		$this->extension_test_runner   = $extension_test_runner;
	}

	/**
	 * Main entry point that coordinates all custom E2E tests for the environment.
	 *
	 * @param E2EEnvInfo   $env_info
	 * @param SymfonyStyle $io
	 * @param bool         $up_only
	 *
	 * @return int
	 */
	public function run_custom_e2e_tests( E2EEnvInfo $env_info, SymfonyStyle $io, bool $up_only ) {
		$this->local_test_run_notifier->notify_test_started(
			$env_info->sut_id,
			$env_info->woo_version,
			$env_info,
			$env_info->is_development_build,
			$env_info->notify
		);

		// Initialize test-result tracking.
		$test_result       = TestResult::init_from( $env_info );
		$this->test_result = $test_result;

		// Hand the TestResult object to the PluginTestRunner.
		$this->extension_test_runner->set_test_result( $this->test_result );

		$exit_code = Command::SUCCESS;

		// Basic check that we have a tests array.
		if ( empty( $env_info->tests ) || ! is_array( $env_info->tests ) ) {
			$io->error( 'No test definitions found in $env_info->tests.' );

			return Command::FAILURE;
		}

		// 1) Run shared-setup across all items that have action=bootstrap or action=test
		$this->run_shared_setup( $env_info, $io );

		if ( $up_only ) {
			App::make( Output::class )->writeln( '' );

			$question = new Question( '<comment>Environment ready. Press "Enter" when you are done to terminate it.</comment>' );
			$question->setValidator( function ( $answer ) {
				return $answer;
			} );
			( new QuestionHelper() )->ask(
				App::make( InputInterface::class ),
				App::make( Output::class ),
				$question
			);

			return Command::SUCCESS;
		}

		// 2) Snapshot DB
		$this->db_export( $env_info, $io );

		// 3) For each item whose action = test
		$testable_items = array_filter( $env_info->tests, function ( $item ) {
			return isset( $item['action'] ) && $item['action'] === Extension::ACTIONS['test'];
		} );

		$is_first = true;

		foreach ( $testable_items as $test_item ) {
			$code = $this->extension_test_runner->run_single_plugin_tests( $env_info, $test_item, $io, $is_first );
			if ( $code !== Command::SUCCESS ) {
				$exit_code = $code;
			}
			$is_first = false;
		}

		// 4) Shared teardown
		$this->run_shared_teardown( $env_info, $io );

		// 5) Merge CTRF + Allure from all plugins
		$this->merge_results( $env_info, $io, $this->test_result );

		// 6) Upload results to QIT Manager
		try {
			[ $report_url, $override_exit ] = $this->local_test_run_notifier->notify_test_finished( $this->test_result );

			if ( $override_exit !== null ) {
				$exit_code = $override_exit;
			}

			App::make( Cache::class )->set( 'last_e2e_report', json_encode( [
				'local_playwright' => file_exists( $test_result->get_results_dir() . '/report/index.html' ) ? $test_result->get_results_dir() . '/report' : '',
				'remote_qit'       => $report_url,
			] ), MONTH_IN_SECONDS );

			// Print Report URL in a more stand-out way.
			$io->writeln( '' );
			$io->writeln( '<info>Test run finished. Report URL:</info>' );
			$io->writeln( $report_url );
			$io->writeln( '' );
		} catch ( \Exception $e ) {
			$io->error( 'Could not finalize results to QIT Manager: ' . $e->getMessage() );
			$exit_code = Command::FAILURE;
		}

		return $exit_code;
	}

	/**
	 * Runs any shared-setup.* (sh|php|js) script for items whose action is bootstrap or test.
	 *
	 * @param E2EEnvInfo   $env_info
	 * @param SymfonyStyle $io
	 */
	protected function run_shared_setup( $env_info, $io ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			// Delegates to the PluginTestRunner:
			$this->extension_test_runner->run_script_if_exists( $env_info, $test_item, 'shared-setup.sh', 'Shared Setup', $io );
		}
	}

	/**
	 * Exports (snapshots) the DB so we can restore it before each plugin test.
	 *
	 * @param E2EEnvInfo   $env_info
	 * @param SymfonyStyle $io
	 */
	protected function db_export( $env_info, $io ) {
		$io->writeln( '<info>[db export]</info>' );
		$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'export', '/qit/snapshot.sql' ] );
	}

	/**
	 * Runs any shared-teardown.* scripts (sh|php|js) for items with action=bootstrap or test.
	 *
	 * @param E2EEnvInfo   $env_info
	 * @param SymfonyStyle $io
	 */
	protected function run_shared_teardown( $env_info, $io ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			// Delegates to the PluginTestRunner:
			$this->extension_test_runner->run_script_if_exists( $env_info, $test_item, 'shared-teardown.sh', 'Shared Teardown', $io );
		}
	}

	/**
	 * Merge results (CTRF/Allure) from all plugins into final artifacts.
	 *
	 * @param E2EEnvInfo   $env_info
	 * @param SymfonyStyle $io
	 * @param TestResult   $test_result
	 */
	protected function merge_results( $env_info, $io, $test_result ) {
		$ctrf_dir        = $test_result->get_results_dir() . '/ctrf';
		$allure_root_dir = $test_result->get_results_dir() . '/allure';

		$ctrf_exists = is_dir( $ctrf_dir ) && count( glob( $ctrf_dir . '/*.json' ) ) > 0;

		$allure_exists = false;
		if ( is_dir( $allure_root_dir ) ) {
			$json_files = glob( $allure_root_dir . '/**/*.json' );
			if ( ! empty( $json_files ) ) {
				$allure_exists = true;
			}
		}

		if ( ! $ctrf_exists && ! $allure_exists ) {
			$io->writeln( '<comment>No CTRF or Allure data found to merge. Skipping...</comment>' );

			return;
		}

		$qit_dir     = Config::get_qit_dir();
		$ctrf_path   = $qit_dir . '/node_modules/.bin/ctrf';
		$allure_path = $qit_dir . '/node_modules/.bin/allure';

		// 2) CTRF merge
		if ( $ctrf_exists ) {
			if ( ! ( is_file( $ctrf_path ) && is_executable( $ctrf_path ) ) ) {
				$io->writeln( "<comment>No ctrf binary found at $ctrf_path. Attempting npm install ctrf...</comment>" );
				$install_ctrf = new Process( [ 'npm', 'install', '--prefix', $qit_dir, 'ctrf' ], $qit_dir );
				$install_ctrf->setTimeout( 300 );
				$install_ctrf->run();

				if ( ! $install_ctrf->isSuccessful() ) {
					throw new \RuntimeException(
						sprintf(
							'Failed to install CTRF. NPM error: %s',
							$install_ctrf->getErrorOutput()
						)
					);
				}
				if ( ! ( is_file( $ctrf_path ) && is_executable( $ctrf_path ) ) ) {
					throw new \RuntimeException(
						sprintf(
							'CTRF was installed, but still no executable at %s',
							$ctrf_path
						)
					);
				}
				$io->writeln( "<info>CTRF installed successfully at $ctrf_path</info>" );
			}

			$ctrf_proc = new Process( [ $ctrf_path, 'merge', $ctrf_dir ] );
			$ctrf_proc->setTimeout( 300 );
			$ctrf_proc->run();

			if ( ! $ctrf_proc->isSuccessful() ) {
				throw new \RuntimeException(
					sprintf(
						'Failed to merge CTRF results. Error: %s',
						$ctrf_proc->getErrorOutput()
					)
				);
			}

			$merged_file = $test_result->get_results_dir() . '/ctrf/ctrf-report.json';
			if ( file_exists( $merged_file ) ) {
				// Call into PluginTestRunner for post-processing
				$this->extension_test_runner->post_process_ctrf_json( $merged_file );
			} else {
				$io->warning( "No merged CTRF file found at {$merged_file}." );
			}
		}

		// 3) Allure merge & generate (if present)
		if ( $allure_exists ) {
			// In your scenario, you upload raw Allure data and handle merges in a remote pipeline.
			$io->writeln( '<info>Allure data found (raw results will be uploaded)...</info>' );
		}
	}
}
