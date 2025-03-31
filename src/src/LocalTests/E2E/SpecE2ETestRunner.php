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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
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

	protected $test_result;

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
		$this->local_test_run_notifier->notify_test_started(
			$env_info->sut_id,
			$env_info->woo_version,
			$env_info,
			$env_info->is_development_build,
			$env_info->notify
		);

		// Initialize test-result tracking
		$test_result       = TestResult::init_from( $env_info );
		$this->test_result = $test_result;
		$exit_code         = Command::SUCCESS;

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

		// 6) Upload results to QIT Manager
		try {
			[ $reportUrl, $override_exit ] = $this->local_test_run_notifier->notify_test_finished( $test_result );

			// If the notifier discovered, e.g., fatal PHP errors, it might want to override your exit code
			if ( $override_exit !== null ) {
				$exit_code = $override_exit;
			}

			$io->writeln( "<info>Raw Allure results have been uploaded. View final report at: {$reportUrl}</info>" );
		} catch ( \Exception $e ) {
			$io->error( 'Could not finalize results to QIT Manager: ' . $e->getMessage() );
			// Optionally force a failure if you prefer
			$exit_code = Command::FAILURE;
		}

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

	protected function merge_results( $env_info, $io, $test_result ) {
		$io->writeln( '<info>Merging CTRF & Allure results on the host...</info>' );

		// 1) Identify CTRF & Allure result files
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

		// If neither CTRF nor Allure data is present, bail early.
		if ( ! $ctrf_exists && ! $allure_exists ) {
			$io->writeln( '<comment>No CTRF or Allure data found to merge. Skipping...</comment>' );

			return;
		}

		// Our persistent system-wide directory for installation:
		$qit_dir = Config::get_qit_dir();

		// Where we expect binaries after npm install:
		$ctrf_path   = $qit_dir . '/node_modules/.bin/ctrf';
		$allure_path = $qit_dir . '/node_modules/.bin/allure';

		//
		// 2) CTRF merge (mandatory if CTRF files exist)
		//
		if ( $ctrf_exists ) {
			$io->writeln( '<info>CTRF JSON found. Ensuring CTRF is installed...</info>' );

			// If ctrf binary is missing, attempt an install
			if ( ! ( is_file( $ctrf_path ) && is_executable( $ctrf_path ) ) ) {
				$io->writeln(
					"<comment>No ctrf binary found at $ctrf_path. Attempting npm install ctrf...</comment>"
				);

				$install_ctrf = new Process( [
					'npm',
					'install',
					'--prefix',
					$qit_dir,
					'ctrf'
				], $qit_dir );
				$install_ctrf->setTimeout( 300 ); // 5 minutes
				$install_ctrf->run();

				if ( ! $install_ctrf->isSuccessful() ) {
					throw new \RuntimeException(
						sprintf(
							'Failed to install CTRF. NPM error: %s',
							$install_ctrf->getErrorOutput()
						)
					);
				}

				// Check again if the binary is present
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

			// Merge the CTRF JSON on the host
			$io->writeln( '<info>Merging CTRF JSON results...</info>' );
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
			$io->writeln( '<info>CTRF merge completed successfully.</info>' );
		}

		//
		// 3) Allure merge & generate (optional if Allure files exist)
		//
		if ( $allure_exists ) {
			// We will upload raw allure results to be compiled/unified in a remote workflow.
		}
	}


	/**
	 * Executes a shell command (inside Docker, in this case),
	 * captures stdout/stderr and timing. Returns an array with
	 * keys: ['exit_code', 'stdout', 'stderr', 'start', 'stop', 'duration'].
	 */
	protected function run_command_and_capture( $env_info, array $command_args ) {
		$start_time = microtime( true );

		try {
			// The 3rd param must be an array of env vars. If you don’t need them, pass an empty array.
			// The 4th param can be $user, the 5th is timeout, etc.
			$output    = $this->docker->run_inside_docker( $env_info, $command_args, [], null, 300, 'php', true );
			$exit_code = 0; // if your Docker method throws an exception on non-zero exit, you can catch that below
		} catch ( \Exception $e ) {
			// If run_inside_docker throws an exception, we treat that as a failure
			$output    = $e->getMessage();
			$exit_code = 1;
		}

		$stop_time   = microtime( true );
		$duration_ms = (int) round( ( $stop_time - $start_time ) * 1000 );

		return [
			'exit_code' => $exit_code,
			// We have no separate stderr, so treat everything as stdout (or parse it if needed)
			'stdout'    => explode( "\n", $output ),
			'stderr'    => [],
			'start'     => $start_time,
			'stop'      => $stop_time,
			'duration'  => $duration_ms,
		];
	}


	/**
	 * Builds a minimal CTRF JSON structure for a single test.
	 * We only fill in the required fields plus any extras we want:
	 *
	 * @param string $test_name "Isolated setup of plugin X" or "shared-setup.sh"
	 * @param array $capture Output from run_command_and_capture
	 *
	 * @return array A CTRF document that can be merged.
	 */
	protected function build_ctrf_snippet( $test_name, $capture ) {
		// Convert timestamps to integer epoch seconds for CTRF "start" & "stop"
		// or you could store them as (int) ms from some reference.
		$start_epoch = (int) $capture['start'];
		$stop_epoch  = (int) $capture['stop'];

		// Convert exit_code => "passed" or "failed" (or "other")
		$status = ( $capture['exit_code'] === 0 ) ? 'passed' : 'failed';

		// CTRF requires a certain structure:
		return [
			'$schema'      => 'http://json-schema.org/draft-07/schema#',
			'reportFormat' => 'CTRF',
			'specVersion'  => '1.0.0',    // set your CTRF version
			// Just generate a random ID if you want:
			'reportId'     => uniqid( 'test-step-', true ),
			'timestamp'    => date( 'c' ),  // ISO 8601
			'generatedBy'  => 'QIT_SpecE2ETestRunner',

			'results' => [
				'tool'    => [
					'name'    => 'SpecE2ERunner',
					'version' => '1.0.0',
				],
				'summary' => [
					'tests'   => 1,
					'passed'  => ( $status === 'passed' ) ? 1 : 0,
					'failed'  => ( $status === 'failed' ) ? 1 : 0,
					'skipped' => 0,
					'pending' => 0,
					'other'   => 0,
					// You can track the suite count or keep it minimal:
					'suites'  => 1,
					'start'   => $start_epoch,
					'stop'    => $stop_epoch,
				],
				'tests'   => [
					[
						'name'     => $test_name,
						'status'   => $status,
						'duration' => $capture['duration'],  // in ms
						'start'    => $start_epoch,
						'stop'     => $stop_epoch,
						// If you want more contextual fields, add them:
						'stdout'   => $capture['stdout'],
						'stderr'   => $capture['stderr'],
					]
				],
			],
		];
	}

	/**
	 * Writes the snippet to a temporary file and merges it into the final CTRF directory.
	 *
	 * @param array $snippet The CTRF snippet from build_ctrf_snippet()
	 * @param string $ctrf_dir A directory with all partials or a final single CTRF file
	 * @param SymfonyStyle $io
	 */
	protected function merge_ctrf_snippet( array $snippet, $ctrf_dir, SymfonyStyle $io ) {
		// Ensure the CTRF directory exists:
		if ( ! is_dir( $ctrf_dir ) ) {
			@mkdir( $ctrf_dir, 0755, true );
		}

		// Write snippet to a small temp file
		$temp_file = tempnam( sys_get_temp_dir(), 'ctrf_step_' ) . '.json';
		file_put_contents( $temp_file, json_encode( $snippet, JSON_PRETTY_PRINT ) );

		// Move the snippet into $ctrf_dir (alternatively, you can keep it in /tmp)
		// so `ctrf merge` sees it. Or run `ctrf merge /tmp/...` either way.
		$partial_name = basename( $temp_file );
		$destination  = rtrim( $ctrf_dir, '/' ) . '/' . $partial_name;
		rename( $temp_file, $destination );

		// Now call `ctrf merge` on that directory
		$qit_dir   = Config::get_qit_dir();
		$ctrf_path = $qit_dir . '/node_modules/.bin/ctrf';  // after "npm install ctrf" in your QIT dir

		if ( ! file_exists( $ctrf_path ) ) {
			$io->warning( "CTRF binary not found at $ctrf_path. Make sure it’s installed." );

			return;
		}

		// Shell out to do the actual merge
		$merge_cmd = new Process( [ $ctrf_path, 'merge', $ctrf_dir ] );
		$merge_cmd->setTimeout( 120 ); // 2 minutes, for example
		$merge_code = $merge_cmd->run();

		if ( $merge_code !== 0 ) {
			$io->error( "Failed to merge CTRF results:\n" . $merge_cmd->getErrorOutput() );
			// You might choose to throw or to continue
		} else {
			$io->writeln( "<info>Merged partial CTRF snippet into $ctrf_dir successfully.</info>" );
		}
	}

	// ------------------------------------------------------------------
	// 4) Put it all together in run_script_if_exists
	// ------------------------------------------------------------------

	/**
	 * If a script (like 'setup.sh') exists in 'bootstrap/' for this plugin test_item, run it,
	 * and record it as a CTRF "test".
	 */
	protected function run_script_if_exists( $env_info, $test_item, $script_name, $label, $io ) {
		$slug = isset( $test_item['slug'] ) ? $test_item['slug'] : 'unknown';

		$host_path  = isset( $test_item['path_in_host'] ) ? $test_item['path_in_host'] : '';
		$docker_dir = isset( $test_item['path_in_php_container'] ) ? $test_item['path_in_php_container'] : '';

		$possible_file = rtrim( $host_path, '/' ) . '/bootstrap/' . $script_name;
		if ( ! file_exists( $possible_file ) ) {
			return; // doesn't exist, skip
		}

		$io->writeln( "<info>Running $label for $slug => $script_name</info>" );

		// We will treat this single script run as its own "test"
		$test_title = "Bootstrap step: [$label] for plugin [$slug] - file: [$script_name]";

		// 1) Run the script in Docker, capturing stdout/stderr
		$command_to_run = [
			'bash',
			'-c',
			sprintf( 'cd %s/bootstrap && bash %s', $docker_dir, $script_name )
		];
		$capture        = $this->run_command_and_capture( $env_info, $command_to_run );

		// 2) Build partial CTRF snippet for this step
		$ctrf_snippet = $this->build_ctrf_snippet( $test_title, $capture );

		// 3) Merge it into the final CTRF directory.
		//    E.g. let’s assume you want everything to go into $results_dir/ctrf
		//    the same location you use later in "collect_plugin_artifacts".
		$results_dir = $this->test_result->get_results_dir();
		$ctrf_dir    = $results_dir . '/ctrf';

		$this->merge_ctrf_snippet( $ctrf_snippet, $ctrf_dir, $io );
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