<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class QitRunner
 *
 * Coordinates starting QIT test runs, generating PHPUnit files,
 * and polling test statuses until completion or timeout.
 */
class QitRunner {
	/**
	 * Logger interface for structured log messages.
	 *
	 * @var Logger
	 */
	private $logger;

	/**
	 * Manages PHPUnit tests (generating files, counting failures, etc.).
	 *
	 * @var PhpUnitRunner
	 */
	private $phpunit_runner;

	/**
	 * Manages live output updates, such as test statuses and final summaries.
	 *
	 * @var QITLiveOutput
	 */
	private $live_output;

	/**
	 * Counter used to assign an index to each test for easier reference.
	 *
	 * @var int
	 */
	private $test_index = 1;

	public function __construct( Logger $logger, PhpUnitRunner $phpunit_runner, QITLiveOutput $live_output ) {
		$this->logger         = $logger;
		$this->phpunit_runner = $phpunit_runner;
		$this->live_output    = $live_output;
	}

	/**
	 * Kicks off all specified test runs and then polls them until they finish.
	 *
	 * @param array $test_runs Structured array of tests to run.
	 * @param array $tests_based_on_custom_tests Names of tests considered "custom."
	 */
	public function run_test_runs( array $test_runs, array $tests_based_on_custom_tests ) {
		$this->logger->log( 'Running test runs...' );

		// Accumulates all active tests keyed by test_run_id.
		$all_tests = [];

		// Start each test run so we have a test_run_id and environment details.
		foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
			foreach ( $test_type_test_runs as &$t ) {
				$this->start_test_run( $t, $test_type, $tests_based_on_custom_tests, $all_tests );
			}
		}

		// Generate PHPUnit test files for each test type after they've been started.
		foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
			$this->phpunit_runner->generate_phpunit_files( $test_type, $test_type_test_runs );
		}

		// Poll all active tests until they're complete or reach a timeout.
		$this->poll_tests( $all_tests );
	}

	/**
	 * Derives timeout and polling settings based on the test type.
	 *
	 * @param string $test_type The name/type of the test.
	 *
	 * @return array             Associative array with timeout_in_seconds and poll_interval.
	 */
	private function get_test_timeout_settings( string $test_type ): array {
		switch ( $test_type ) {
			case 'woo_e2e':
				// For example, the "woo_e2e" test might need up to 2 hours.
				$timeout_in_seconds = 2 * 60 * 60; // 2 hours
				$poll_interval      = 15;          // Poll every 15 seconds
				break;

			default:
				// Other test types default to 15 minutes.
				$timeout_in_seconds = 15 * 60; // 15 minutes
				$poll_interval      = 15;      // Poll every 15 seconds
				break;
		}

		return [
			'timeout_in_seconds' => $timeout_in_seconds,
			'poll_interval'      => $poll_interval,
		];
	}

	/**
	 * Starts a single test run by calling the QIT CLI, then stores its metadata in $all_tests.
	 *
	 * @param array $t Test definition, including versions, features, etc.
	 * @param string $test_type The type of test (e.g., woo_e2e, security, etc.).
	 * @param array $tests_based_on_custom_tests Array of tests considered custom, for debug logic.
	 * @param array $all_tests Reference to the master list of active test runs.
	 */
	private function start_test_run(
		array &$t,
		string $test_type,
		array $tests_based_on_custom_tests,
		array &$all_tests
	) {
		/*
		 * Generate a test_function_name used for naming JSON snapshots or referencing logs.
		 * Use a normalized version of $t for consistency in hashing.
		 */
		$normalized_t = $t;
		unset( $normalized_t['path'] );
		$normalized_t['type'] = str_replace( '-', '_', $normalized_t['type'] );

		$t['test_function_name'] = sprintf(
			'test_%s_%s_woo%s_php%s_wp%s_%s',
			$normalized_t['type'],
			$t['slug'],
			str_replace( '.', '', $t['woo'] ),
			str_replace( '.', '', $t['php'] ),
			str_replace( '.', '', $t['wp'] ),
			md5( json_encode( $normalized_t ) )
		);

		$qit_test_path     = $t['path'];
		$snapshot_filepath = sprintf( '%s/%s.json', $qit_test_path, $t['test_function_name'] );
		$t['test_index']   = $this->test_index ++;

		/*
		 * If QIT_REUSE_JSON=1 and the snapshot file already exists, we skip running QIT
		 * and just treat it as if the test completed successfully.
		 */
		$reuse_json = ( getenv( 'QIT_REUSE_JSON' ) === '1' );
		if ( $reuse_json && file_exists( $snapshot_filepath ) ) {
			$this->logger->log(
				"QIT_REUSE_JSON=1 and JSON exists for {$t['test_function_name']}, skipping QIT run."
			);

			// Generate a pseudo test_run_id if reusing JSON.
			$t['test_run_id'] = hexdec( substr( md5( $t['test_function_name'] ), 0, 6 ) );
			if ( $t['test_run_id'] <= 0 ) {
				$t['test_run_id'] = rand( 100000, 999999 );
			}

			$non_json_output_file = $t['non_json_output_file'] ?? tempnam( sys_get_temp_dir(), 'qit_non_json_' );

			$t['env'] = [
				'QIT_TEST_PATH'            => $t['path'],
				'QIT_TEST_TYPE'            => $test_type,
				'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
				'QIT_RAN_TEST'             => false,
				'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
				'QIT_NON_JSON_OUTPUT'      => $non_json_output_file,
				'QIT_POLL_INTERVAL'        => 15,
			];

			/*
			 * Add this "test" to live output, but it won't actually run because
			 * we're reusing the existing snapshot data.
			 */
			$this->live_output->addTest(
				$t['test_run_id'],
				"[REUSE_JSON] {$t['type']} {$t['slug']}",
				$t['test_index'],
				$t
			);

			/*
			 * Pretend it's already completed by passing a "success" status to handle_qit_response_final.
			 */
			$result = [
				'update_complete'          => true,
				'status'                   => 'success',
				'test_results_manager_url' => '',
			];

			$this->handle_qit_response_final( $t, $result, 'success' );

			return;
		}

		/*
		 * If we're not reusing JSON, call QIT via CLI to start the test and retrieve its new test_run_id.
		 */
		$php      = ( new PhpExecutableFinder() )->find( false );
		$qit      = realpath( __DIR__ . '/../../../src/qit-cli.php' );
		$sut_slug = $t['sut_slug'];

		$args = [
			$php,
			$qit,
			"run:{$test_type}",
			'--json',
			'--async',  // Add async flag to get old behavior (return immediately with test_run_id)
			"--zip={$t['path']}/sut.zip",
		];

		// Track files we want to clean up later.
		Context::$to_delete[] = "{$t['path']}/sut.zip";

		/*
		 * If debug mode is enabled and the test isn't custom, add verbosity.
		 */
		if ( Context::$debug_mode && ! in_array( $test_type, $tests_based_on_custom_tests, true ) ) {
			$args[] = '-vvv';
		}

		/*
		 * Append version constraints, optional features, or other parameters to the QIT CLI.
		 * Randomly choose between short and long forms (50/50) to test both in production.
		 */
		$php_param = ( rand( 0, 1 ) === 0 ) ? 'php' : 'php_version';
		$wp_param  = ( rand( 0, 1 ) === 0 ) ? 'wp' : 'wordpress_version';
		$woo_param = ( rand( 0, 1 ) === 0 ) ? 'woo' : 'woocommerce_version';

		if ( ! empty( $t['php'] ) ) {
			$args[] = "--{$php_param}={$t['php']}";
		}
		if ( ! empty( $t['wp'] ) ) {
			$args[] = "--{$wp_param}={$t['wp']}";
		}

		if ( ! empty( $t['woo'] ) ) {
			$args[] = "--{$woo_param}={$t['woo']}";
		}
		if ( ! empty( $t['features'] ) ) {
			foreach ( $t['features'] as $f ) {
				$args[] = "--optional_features={$f}";
			}
		}
		if ( ! empty( $t['params'] ) ) {
			foreach ( $t['params'] as $param_name => $param_value ) {
				$args[] = "{$param_name}={$param_value}";
			}
		}

		$this->logger->log( 'Running test run command: ' . implode( ' ', $args ) );
		$args[]      = $sut_slug;
		$qit_process = new Process( $args );
		maybe_echo( "\nRunning command: " . $qit_process->getCommandLine() . "\n" );
		$qit_process->setTimeout( null );

		$non_json_output_file = $t['non_json_output_file'] ?? tempnam( sys_get_temp_dir(), 'qit_non_json_' );

		$env = $qit_process->getEnv();
		$env['QIT_NON_JSON_OUTPUT'] = $non_json_output_file;
		$qit_process->setEnv( $env );

		$this->add_task_id_to_process( $qit_process, $t );

		// Use run() instead of mustRun() because tests are EXPECTED to fail when testing buggy plugins
		// The old --ignore-fail flag used to exit with code 0 regardless of test status
		$qit_process->run();

		// Show debug output if the process failed
		if ( $qit_process->getExitCode() !== 0 ) {
			$this->logger->log( "Process exited with code {$qit_process->getExitCode()}, checking for valid JSON output" );

			if ( file_exists( $non_json_output_file ) ) {
				$all_output_json = file_get_contents( $non_json_output_file );
				if ( ! empty( $all_output_json ) ) {
					echo "\nAll output:\n================\n" . $all_output_json . "\n";
				}
			}
		}

		$output = trim( $qit_process->getOutput() );
		$this->logger->log( "Output of run:{$test_type}: {$output}" );

		$json = json_decode( $output, true );
		if ( json_last_error() !== JSON_ERROR_NONE || empty( $json['test_run_id'] ) ) {
			$this->logger->log( 'Failed to get valid JSON from run command' );
			throw new RuntimeException(
				"Failed to get valid JSON test_run_id from qit run command:\n{$output}"
			);
		}

		/*
		 * We now have a valid test_run_id that we'll monitor in poll_tests().
		 */
		$t['test_run_id'] = $json['test_run_id'];
		$this->logger->log( 'Test run started with ID: ' . $t['test_run_id'] );

		/*
		 * Configure time-based polling by setting start_time and timeouts instead of max_attempts.
		 */
		$timeout_settings        = $this->get_test_timeout_settings( $normalized_t['type'] );
		$t['start_time']         = time();
		$t['timeout_in_seconds'] = $timeout_settings['timeout_in_seconds'];
		$t['poll_interval']      = $timeout_settings['poll_interval'];

		$non_json_output_file = $t['non_json_output_file'] ?? tempnam( sys_get_temp_dir(), 'qit_non_json_' );

		$t['env'] = [
			'QIT_TEST_PATH'            => $t['path'],
			'QIT_TEST_TYPE'            => $test_type,
			'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
			'QIT_RAN_TEST'             => false,
			'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
			'QIT_NON_JSON_OUTPUT'      => $non_json_output_file,
			'QIT_POLL_INTERVAL'        => $t['poll_interval'],
		];

		/*
		 * Inform the live output system of this new test, so it can track status and progress.
		 */
		$task_id_for_live_output = $qit_process->getEnv()['qit_task_id'] ?? "[{$t['type']}] {$t['slug']}";
		$this->live_output->addTest( $t['test_run_id'], $task_id_for_live_output, $t['test_index'], $t );

		// Store the test in $all_tests so poll_tests() can keep track of it.
		$all_tests[ $t['test_run_id'] ] = $t;
	}

	/**
	 * Polls all active tests in chunks of up to 20 test_run_ids, respecting timeouts and
	 * a normal poll interval. Each chunk poll is spaced by 3 seconds to avoid overloading.
	 *
	 * @param array $all_tests A list of all active tests keyed by test_run_id.
	 */
	private function poll_tests( array $all_tests ) {
		$this->logger->log( 'Start polling all tests in chunks of up to 20...' );

		while ( ! empty( $all_tests ) ) {
			/*
			 * Determine the poll interval from the first test in the list.
			 * We assume all tests share the same interval or choose an appropriate default.
			 */
			$first_test    = reset( $all_tests );
			$poll_interval = $first_test['poll_interval'] ?? 15;

			/*
			 * We gather all test_run_ids and poll them in chunks of up to 20 per request.
			 * After each full "cycle" of chunk polling, we sleep for $poll_interval seconds.
			 */
			$test_run_ids  = array_keys( $all_tests );
			$chunks_of_ids = array_chunk( $test_run_ids, 20 );

			foreach ( $chunks_of_ids as $chunk_of_ids ) {
				/*
				 * Each chunk is spaced by 3 seconds to avoid sending multiple get-multiple
				 * requests in quick succession if there are many test runs.
				 */
				sleep( 3 );

				$ids_param = implode( ',', $chunk_of_ids );
				$this->logger->log( "Polling with get-multiple for chunk: {$ids_param}" );

				$get_process = new Process( [
					( new PhpExecutableFinder() )->find( false ),
					realpath( __DIR__ . '/../../../src/qit-cli.php' ),
					'get-multiple',
					'--json',
					$ids_param,
				] );
				$get_process->run();

				$get_output  = trim( $get_process->getOutput() );
				$result_json = json_decode( $get_output, true );

				if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $result_json ) ) {
					$this->logger->log( "get-multiple returned invalid JSON:\n" . $get_output );
					throw new RuntimeException(
						"Failed to parse JSON from get-multiple.\nRaw output:\n" .
						$get_output . "\nError output:\n" . $get_process->getErrorOutput()
					);
				}

				$this->logger->log(
					"get-multiple returned these IDs: " . implode( ',', array_keys( $result_json ) )
				);

				$completed_test_ids = [];
				$timed_out_test_ids = [];

				/*
				 * Check each test in the chunk to see if it's completed, timed out, or still in progress.
				 */
				foreach ( $chunk_of_ids as $test_run_id ) {
					if ( ! isset( $all_tests[ $test_run_id ] ) ) {
						continue;
					}

					$start_time         = $all_tests[ $test_run_id ]['start_time'];
					$timeout_in_seconds = $all_tests[ $test_run_id ]['timeout_in_seconds'];
					$elapsed_seconds    = time() - $start_time;

					/*
					 * If we've surpassed the configured timeout, mark the test as timed out.
					 */
					if ( $elapsed_seconds >= $timeout_in_seconds ) {
						$this->logger->log(
							"Test_run_id {$test_run_id} timed out after {$timeout_in_seconds} seconds."
						);
						$this->live_output->addTestError(
							$test_run_id,
							"Did not finish within {$timeout_in_seconds} seconds."
						);
						$timed_out_test_ids[] = $test_run_id;
						continue;
					}

					/*
					 * If this test_run_id wasn't found in the get-multiple response, label it unknown.
					 */
					if ( ! isset( $result_json[ $test_run_id ] ) ) {
						$this->logger->log( "No data for test_run_id {$test_run_id}, marking as unknown." );
						$this->live_output->setTestStatus( $test_run_id, 'unknown' );
						continue;
					}

					$test_result = $result_json[ $test_run_id ];
					$status      = $test_result['status'] ?? 'unknown';

					$this->logger->log( "Test_run_id {$test_run_id} has status: {$status}" );

					/*
					 * Update the UI with the test results manager URL if available.
					 */
					if ( ! empty( $test_result['test_results_manager_url'] ) ) {
						$this->live_output->updateTestData( $test_run_id, [
							'test_results_manager_url' => $test_result['test_results_manager_url'],
						] );
					}

					/*
					 * Mark the test as done if update_complete is true, otherwise it's still in progress.
					 */
					if (
						isset( $test_result['update_complete'] ) &&
						$test_result['update_complete'] === true
					) {
						$this->logger->log( "Test_run_id {$test_run_id} completed. Handling final response..." );
						$this->handle_qit_response_final(
							$all_tests[ $test_run_id ],
							$test_result,
							$status
						);
						$completed_test_ids[] = $test_run_id;
					} else {
						$this->live_output->setTestStatus( $test_run_id, $status );
					}
				}

				/*
				 * Remove completed or timed-out tests from the active list.
				 */
				foreach ( $completed_test_ids as $test_run_id ) {
					unset( $all_tests[ $test_run_id ] );
				}
				foreach ( $timed_out_test_ids as $test_run_id ) {
					unset( $all_tests[ $test_run_id ] );
				}

				/*
				 * If all tests are removed, we can exit this chunk cycle early.
				 */
				if ( empty( $all_tests ) ) {
					break;
				}
			}

			/*
			 * If there are still active tests, wait the normal poll interval before proceeding to the next cycle.
			 */
			if ( ! empty( $all_tests ) ) {
				$this->sleep_with_countdown( $poll_interval );
			}
		}

		maybe_echo( "All tests completed.\n" );
		$this->logger->log( 'All tests completed. Preparing final summary...' );

		$failures = $this->phpunit_runner->getFailedTestsCount();
		$this->live_output->printFinalSummary( $failures );
		global $gracefulEnding;
		$gracefulEnding = true;
		exit( $failures > 0 ? 1 : 0 );
	}

	/**
	 * Sleeps for the given number of seconds, decrementing one second at a time
	 * so the user interface can display how many seconds remain before the next poll.
	 *
	 * @param int $seconds Number of seconds to sleep.
	 */
	private function sleep_with_countdown( int $seconds ) {
		for ( $i = $seconds; $i > 0; $i -- ) {
			$this->live_output->setTimeToNextPoll( $i );
			$this->live_output->renderOutput();
			sleep( 1 );
		}
		$this->live_output->setTimeToNextPoll( null );
		$this->live_output->renderOutput();
	}

	/**
	 * Handles the final phase of a test run, such as marking it complete and running any PHPunit verifications.
	 *
	 * @param array $test_run The test's metadata and environment info.
	 * @param array $result The JSON-decoded result from QIT, including status.
	 * @param string $qit_status The final status string from QIT.
	 */
	private function handle_qit_response_final( array $test_run, array $result, string $qit_status ): void {
		$this->live_output->setQitRawStatus( $test_run['test_run_id'], $qit_status );
		$this->phpunit_runner->run_phpunit_test( $test_run, $result );
	}

	/**
	 * Adds a custom "qit_task_id" environment variable to the process
	 * that helps label logs or output with relevant test info.
	 *
	 * @param Process $process The Symfony Process to modify.
	 * @param array $test_run Metadata about the test (type, slug, versions, etc.).
	 */
	private function add_task_id_to_process( Process $process, array $test_run ) {
		$task_id_parts = [
			sprintf( '[%s -', ucwords( $test_run['type'] ) ),
			sprintf( '%s]', $test_run['slug'] ),
		];

		if ( ! empty( $test_run['php'] ) ) {
			$task_id_parts[] = sprintf( '[PHP %s]', $test_run['php'] );
		}
		if ( ! empty( $test_run['wp'] ) ) {
			$task_id_parts[] = sprintf( '[WP %s]', $test_run['wp'] );
		}
		if ( ! empty( $test_run['woo'] ) ) {
			$task_id_parts[] = sprintf( '[Woo %s]', $test_run['woo'] );
		}
		if ( ! empty( $test_run['features'] ) ) {
			$task_id_parts[] = sprintf( '[Features %s]', implode( ', ', $test_run['features'] ) );
		}

		$task_id            = implode( ' ', $task_id_parts ) . ': ';
		$env                = $process->getEnv();
		$env['qit_task_id'] = $task_id;
		$process->setEnv( $env );
	}
}