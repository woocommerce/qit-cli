<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * PHP 7.2–compatible, uses snake_case variables and "thisCase" for class name.
 * Manages "Custom E2E" tests by simply calling each plugin's "npm run qit-e2e"
 * and handling shared setups, DB snapshots, etc.
 */
class SpecE2ETestRunner {
	/**
	 * @var Docker
	 */
	protected $docker;

	/**
	 * @var LocalTestRunNotifier
	 */
	protected $local_test_run_notifier;

	/**
	 * Constructor
	 *
	 * @param Docker $docker
	 * @param LocalTestRunNotifier $local_test_run_notifier
	 */
	public function __construct( Docker $docker, LocalTestRunNotifier $local_test_run_notifier ) {
		$this->docker                  = $docker;
		$this->local_test_run_notifier = $local_test_run_notifier;
	}

	/**
	 * Main entry point that coordinates all custom E2E tests for the environment.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 *
	 * @return int
	 */
	public function run_custom_e2e_tests( $env_info, $io, $up_only ) {
		// Initialize test-result tracking
		$test_result = TestResult::init_from( $env_info );
		$exit_code   = Command::SUCCESS;

		// Basic check that we have a tests array
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
			( new QuestionHelper() )->ask( App::make( InputInterface::class ), App::make( Output::class ), $question );

			return Command::SUCCESS;
		}

		// 2) Snapshot DB
		$this->db_export( $env_info, $io );

		// 3) For each item whose action = test
		$testable_items = array_filter( $env_info->tests, function ( $item ) {
			return isset( $item['action'] ) && $item['action'] === Extension::ACTIONS['test'];
		} );

		foreach ( $testable_items as $test_item ) {
			$code = $this->run_single_plugin_tests( $env_info, $test_item, $io, $test_result );
			if ( $code !== Command::SUCCESS ) {
				$exit_code = $code;
			}
		}

		// 4) Shared teardown
		$this->run_shared_teardown( $env_info, $io );

		// 5) Merge CTRF + Allure from all plugins
		$this->merge_results( $env_info, $io, $test_result );

		return $exit_code;
	}

	/**
	 * Runs any shared-setup.* (sh|php|js) script for items whose action is bootstrap or test.
	 *
	 * @param E2EEnvInfo $env_info
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
			$this->run_script_if_exists( $env_info, $test_item, 'shared-setup.sh', 'Shared Setup (Shell)', $io );
			$this->run_script_if_exists( $env_info, $test_item, 'shared-setup.php', 'Shared Setup (PHP)', $io );
			$this->run_script_if_exists( $env_info, $test_item, 'shared-setup.js', 'Shared Setup (JS)', $io );
		}
	}

	/**
	 * Exports (snapshots) the DB so we can restore it before each plugin test.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 */
	protected function db_export( $env_info, $io ) {
		$io->writeln( '<info>[db export]</info>' );
		// For example:
		$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'export', '/qit/snapshot.sql' ] );
	}

	/**
	 * Restores DB, runs the plugin's local setups, calls `npm run qit-e2e`, runs teardown,
	 * and copies artifacts out.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param SymfonyStyle $io
	 * @param TestResult $test_result
	 *
	 * @return int
	 */
	/**
	 * Restores DB, runs the plugin's local setups, calls `npm run qit-e2e`, runs teardown,
	 * and copies artifacts out.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param SymfonyStyle $io
	 * @param TestResult $test_result
	 *
	 * @return int
	 */
	protected function run_single_plugin_tests( $env_info, $test_item, $io, $test_result ) {
		$slug = isset( $test_item['slug'] ) ? $test_item['slug'] : 'unknown';

		// 1) Restore DB
		$io->writeln( '<comment>[db import] for ' . $slug . '</comment>' );
		$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'import', '/qit/snapshot.sql' ] );

		// 2) plugin-specific setup
		$this->run_script_if_exists( $env_info, $test_item, 'setup.sh', 'Plugin Setup (Shell)', $io );
		$this->run_script_if_exists( $env_info, $test_item, 'setup.php', 'Plugin Setup (PHP)', $io );
		$this->run_script_if_exists( $env_info, $test_item, 'setup.js', 'Plugin Setup (JS)', $io );

		// 3) "npm install" (if needed) and "npm run qit-e2e" on the host
		$host_path = isset( $test_item['path_in_host'] ) ? $test_item['path_in_host'] : '';
		$io->section( "Running 'npm install && npm run qit-e2e' on host for plugin: " . $slug );

		$code = Command::SUCCESS;

		try {
			// Only run "npm install" if no node_modules directory is present.
			$io->text( "No 'node_modules' found in {$host_path}, running npm install..." );
			$install_process = new Process( [ 'npm', 'install' ], $host_path );
			$install_exit    = $install_process->run( function ( $type, $buffer ) use ( $io ) {
				if ( $type === Process::ERR ) {
					$io->write( $buffer, false, OutputInterface::OUTPUT_RAW );
				} else {
					$io->write( $buffer );
				}
			} );

			if ( $install_exit !== 0 ) {
				throw new \RuntimeException(
					"npm install failed:\n" . $install_process->getErrorOutput()
				);
			}

			// Build the full command: `npm run qit-e2e -- <runnerArgs>`
			$test_cmd = [ 'npm', 'run', 'qit-e2e' ];

			// Only append `--` and the runner args if there are any runnerArgs
			if ( ! empty( $env_info->runner_args ) ) {
				$test_cmd[] = '--';
				$test_cmd   = array_merge( $test_cmd, $env_info->runner_args );
			}

			$test_process = new Process( $test_cmd, $host_path );
			$test_process->setEnv( [
				'IS_QIT'       => 'true',
				'QIT_SITE_URL' => $env_info->site_url,
			] );
			$test_exit = $test_process->run( function ( $type, $buffer ) use ( $io ) {
				if ( $type === Process::ERR ) {
					$io->write( $buffer, false, OutputInterface::OUTPUT_RAW );
				} else {
					$io->write( $buffer );
				}
			} );

			if ( $test_exit !== 0 ) {
				throw new \RuntimeException(
					"npm run qit-e2e failed:\n" . $test_process->getErrorOutput()
				);
			}
		} catch ( \Exception $e ) {
			$io->error( "Plugin $slug E2E test error on host: " . $e->getMessage() );
			$code = Command::FAILURE;
		}

		// 4) plugin teardown
		$this->run_script_if_exists( $env_info, $test_item, 'teardown.sh', 'Plugin Teardown (Shell)', $io );
		$this->run_script_if_exists( $env_info, $test_item, 'teardown.php', 'Plugin Teardown (PHP)', $io );
		$this->run_script_if_exists( $env_info, $test_item, 'teardown.js', 'Plugin Teardown (JS)', $io );

		// 5) Collect artifacts
		$this->collect_plugin_artifacts( $test_item, $test_result, $io );

		return $code;
	}

	/**
	 * Runs any shared-teardown.* scripts (sh|php|js) for items with action=bootstrap or test.
	 *
	 * @param E2EEnvInfo $env_info
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
			$this->run_script_if_exists( $env_info, $test_item, 'shared-teardown.sh', 'Shared Teardown (Shell)', $io );
			$this->run_script_if_exists( $env_info, $test_item, 'shared-teardown.php', 'Shared Teardown (PHP)', $io );
			$this->run_script_if_exists( $env_info, $test_item, 'shared-teardown.js', 'Shared Teardown (JS)', $io );
		}
	}

	/**
	 * Merges results, e.g. CTRF JSON from all tested plugins, plus optional Allure reports.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 * @param TestResult $test_result
	 */
	protected function merge_results( $env_info, $io, $test_result ) {
		$io->writeln( '<info>Merging CTRF results from all tested plugins...</info>' );
		// Example steps:
		//  1) gather all JSON from $test_result->get_results_dir() . '/ctrf'
		//  2) run "ctrf-cli merge" or "allure generate"
	}

	/**
	 * If a script (like "setup.sh") exists in "bootstrap/" for this plugin test_item, run it.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param string $script_name
	 * @param string $label
	 * @param SymfonyStyle $io
	 */
	protected function run_script_if_exists( $env_info, $test_item, $script_name, $label, $io ) {
		$slug = isset( $test_item['slug'] ) ? $test_item['slug'] : 'unknown';

		$host_path  = isset( $test_item['path_in_host'] ) ? $test_item['path_in_host'] : '';
		$docker_dir = isset( $test_item['path_in_php_container'] ) ? $test_item['path_in_php_container'] : '';

		$possible_file = rtrim( $host_path, '/' ) . '/bootstrap/' . $script_name;
		if ( ! file_exists( $possible_file ) ) {
			return; // doesn't exist, skip
		}

		$io->writeln( '<info>Running ' . $label . ' for ' . $slug . ' => ' . $script_name . '</info>' );

		// For example, if it's a .sh file:
		//   "bash -c cd /some/path/bootstrap && bash script_name"
		$this->docker->run_inside_docker(
			$env_info,
			[ 'bash', '-c', 'cd ' . $docker_dir . '/bootstrap && bash ' . $script_name ]
		);
	}

	/**
	 * Copies plugin's ./results/ctrf.json and optionally ./results/allure/
	 * from container to local for merging.
	 *
	 * @param array $test_item
	 * @param TestResult $test_result
	 * @param SymfonyStyle $io
	 */
	protected function collect_plugin_artifacts( $test_item, $test_result, $io ) {
		$results_dir = $test_result->get_results_dir();

		// Ensure we have a subfolder for CTRF, Allure, etc.
		if ( ! is_dir( $results_dir ) ) {
			@mkdir( $results_dir, 0755, true );
		}

		if ( ! is_dir( $results_dir . '/ctrf' ) ) {
			@mkdir( $results_dir . '/ctrf', 0755, true );
		}

		if ( ! is_dir( $results_dir . '/allure' ) ) {
			@mkdir( $results_dir . '/allure', 0755, true );
		}

		if ( file_exists( $test_item['path_in_host'] . '/results/ctrf.json' ) ) {
			copy(
				$test_item['path_in_host'] . '/results/ctrf.json',
				$results_dir . '/ctrf/' . $test_item['slug'] . '.json'
			);
		} else {
			$io->warning( 'Plugin ' . $test_item['slug'] . ' did not produce results/ctrf.json' );
		}

		if ( file_exists( $test_item['path_in_host'] . '/results/allure' ) ) {
			$fs = App::make( Filesystem::class );
			$fs->mirror(
				$test_item['path_in_host'] . '/results/allure',
				$results_dir . '/allure/' . $test_item['slug']
			);
		}
	}
}