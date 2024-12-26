<?php

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class QitRunner {
	private $logger;
	private $phpUnitRunner;
	private $liveOutput;
	private $testIndex = 1; // Added a counter for tests.

	public function __construct( Logger $logger, PhpUnitRunner $phpUnitRunner, QITLiveOutput $liveOutput ) {
		$this->logger        = $logger;
		$this->phpUnitRunner = $phpUnitRunner;
		$this->liveOutput    = $liveOutput;
	}

	public function run_test_runs( array $test_runs, array $tests_based_on_custom_tests ) {
		$this->logger->log( "Running test runs..." );

		// Start QIT runs, keyed by test_run_id
		$allTests = [];
		foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
			foreach ( $test_type_test_runs as &$t ) {
				$this->start_test_run( $t, $test_type, $tests_based_on_custom_tests, $allTests );
			}
		}

		// Generate PHPUnit files
		foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
			$this->phpUnitRunner->generate_phpunit_files( $test_type, $test_type_test_runs );
		}

		// Poll tests until completion
		$this->poll_tests( $allTests );
	}

	private function get_test_timeout_settings( string $test_type ): array {
		switch ( $test_type ) {
			case 'woo_e2e':
				// 2 hours total
				$timeout_in_seconds = 2 * 60 * 60;  // 2 hours = 7200 seconds
				$poll_interval      = 15;           // poll every 15s
				break;

			default:
				// 15 minutes total by default
				$timeout_in_seconds = 15 * 60;  // 15 minutes = 900 seconds
				$poll_interval      = 15;       // poll every 15s
				break;
		}

		// Calculate how many times we’ll attempt polling before giving up.
		$max_attempts = (int) ( $timeout_in_seconds / $poll_interval );

		return [
			'timeout_in_seconds' => $timeout_in_seconds,
			'poll_interval'      => $poll_interval,
			'max_attempts'       => $max_attempts,
		];
	}

	private function start_test_run( array &$t, string $test_type, array $tests_based_on_custom_tests, array &$allTests ) {
		// Generate test_function_name BEFORE qit run, so we can reuse JSON if QIT_REUSE_JSON=1
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

		// Assign a test index for numbering
		$t['test_index'] = $this->testIndex ++;

		// Check QIT_REUSE_JSON and existing file
		$reuse_json = ( getenv( 'QIT_REUSE_JSON' ) === '1' );
		if ( $reuse_json && file_exists( $snapshot_filepath ) ) {
			// Re-use the existing JSON, skip QIT run
			$this->logger->log( "QIT_REUSE_JSON=1 and JSON exists for {$t['test_function_name']}, skipping QIT run." );

			// Assign a fake test_run_id
			$t['test_run_id'] = hexdec( substr( md5( $t['test_function_name'] ), 0, 6 ) );
			if ( $t['test_run_id'] <= 0 ) {
				$t['test_run_id'] = rand( 100000, 999999 );
			}

			// Set environment vars as usual
			$t['env'] = [
				'QIT_TEST_PATH'            => $t['path'],
				'QIT_TEST_TYPE'            => $test_type,
				'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
				'QIT_RAN_TEST'             => false,
				'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
				'QIT_NON_JSON_OUTPUT'      => $t['non_json_output_file'] ?? tempnam( sys_get_temp_dir(), 'qit_non_json_' ),
				'QIT_POLL_INTERVAL'        => 15,
				'QIT_MAX_ATTEMPTS'         => 1,
			];

			// Add test to live output with its index
			$this->liveOutput->addTest( $t['test_run_id'], "[REUSE_JSON] {$t['type']} {$t['slug']}", $t['test_index'], $t );

			// Immediately handle "final" as if QIT succeeded
			$result = [
				'update_complete'          => true,
				'status'                   => 'success',
				'test_results_manager_url' => '',
			];

			$this->handle_qit_response_final( $t, $result, 'success' );

			return;
		}

		// If not reusing JSON, run QIT
		$php      = ( new PhpExecutableFinder() )->find( false );
		$qit      = realpath( __DIR__ . '/../../../src/qit-cli.php' );
		$sut_slug = $t['sut_slug'];

		$args = [
			$php,
			$qit,
			"run:$test_type",
			'--json',
			'--ignore-fail',
			"--zip={$t['path']}/sut.zip",
		];

		Context::$to_delete[] = "{$t['path']}/sut.zip";

		if ( Context::$debug_mode && ! in_array( $test_type, $tests_based_on_custom_tests ) ) {
			$args[] = '-vvv';
		}

		if ( ! empty( $t['php'] ) ) {
			$args[] = "--php_version={$t['php']}";
		}

		if ( ! empty( $t['wp'] ) ) {
			$args[] = "--wordpress_version={$t['wp']}";
		}

		if ( ! empty( $t['woo'] ) ) {
			$args[] = "--woocommerce_version={$t['woo']}";
		}

		if ( ! empty( $t['features'] ) ) {
			foreach ( $t['features'] as $f ) {
				$args[] = "--optional_features=$f";
			}
		}

		if ( ! empty( $t['params'] ) ) {
			foreach ( $t['params'] as $param_name => $param_value ) {
				$args[] = "$param_name={$param_value}";
			}
		}

		$this->logger->log( "Running test run command: " . implode( ' ', $args ) );
		$args[]      = $sut_slug;
		$qit_process = new Process( $args );
		maybe_echo( "\nRunning command: " . $qit_process->getCommandLine() . "\n" );
		$qit_process->setTimeout( null );
		$this->add_task_id_to_process( $qit_process, $t );
		$qit_process->mustRun();
		$output = trim( $qit_process->getOutput() );
		$this->logger->log( "Output of run:$test_type: $output" );

		$json = json_decode( $output, true );
		if ( json_last_error() !== JSON_ERROR_NONE || empty( $json['test_run_id'] ) ) {
			$this->logger->log( "Failed to get valid JSON from run command" );
			throw new RuntimeException( "Failed to get valid JSON test_run_id from qit run command:\n$output" );
		}

		$t['test_run_id'] = $json['test_run_id'];
		$this->logger->log( "Test run started with ID: " . $t['test_run_id'] );

		$timeout_settings = $this->get_test_timeout_settings( $normalized_t['type'] );

		// We destructure these for clarity:
		$poll_interval = $timeout_settings['poll_interval'];
		$max_attempts  = $timeout_settings['max_attempts'];

		$t['env'] = [
			'QIT_TEST_PATH'            => $t['path'],
			'QIT_TEST_TYPE'            => $test_type,
			'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
			'QIT_RAN_TEST'             => false,
			'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
			'QIT_NON_JSON_OUTPUT'      => $t['non_json_output_file'] ?? tempnam( sys_get_temp_dir(), 'qit_non_json_' ),
			'QIT_POLL_INTERVAL'        => $poll_interval,
			'QIT_MAX_ATTEMPTS'         => $max_attempts,
		];

		$t['max_attempts']  = $max_attempts;
		$t['poll_interval'] = $poll_interval;

		// Add test to live output with index
		$this->liveOutput->addTest( $t['test_run_id'], $qit_process->getEnv()['qit_task_id'] ?? "[{$t['type']}] {$t['slug']}", $t['test_index'], $t );

		// Store by test_run_id as the key
		$allTests[ $t['test_run_id'] ] = $t;
	}

	private function poll_tests( array $allTests ) {
		$this->logger->log( "Start polling all tests together..." );
		while ( ! empty( $allTests ) ) {
			$test_run_ids = array_keys( $allTests );
			$ids_param    = implode( ',', $test_run_ids );

			$this->logger->log( "Polling with get-multiple for IDs: $ids_param" );
			$get_process = new Process( [
				( new PhpExecutableFinder() )->find( false ),
				realpath( __DIR__ . '/../../../src/qit-cli.php' ),
				'get-multiple',
				'--json',
				$ids_param,
			] );

			$get_process->run();
			$get_output = trim( $get_process->getOutput() );
			if ( getenv( 'QIT_VERBOSE_SELF_TEST_LOG' ) ) {
				$this->logger->log( "get-multiple output: $get_output" );
			}
			$result_json = json_decode( $get_output, true );

			$still_in_progress = [];
			$completed_tests   = [];
			$unknown_tests     = [];

			if (json_last_error() !== JSON_ERROR_NONE || ! is_array($result_json)) {
				$this->logger->log("get-multiple returned invalid JSON:\n" . $get_output);
				throw new RuntimeException( "Failed to parse JSON from get-multiple. Raw output:\n" . $get_output . "\n Error output:\n . {$get_process->getErrorOutput()}" );
			} else {
				$this->logger->log( "get-multiple keys returned: " . implode( ',', array_keys( $result_json ) ) );
				foreach ( $allTests as $tid => $test ) {
					if ( ! isset( $result_json[ $tid ] ) || ! isset( $result_json[ $tid ]['status'] ) ) {
						$this->logger->log( "No status in response for test_run_id $tid" );
						$this->liveOutput->setTestStatus( $tid, 'unknown' );
						maybe_echo( "No status in response for test_run_id {$tid}.\n" );
						$unknown_tests[] = $tid;
						continue;
					}

					$tr     = $result_json[ $tid ];
					$status = $tr['status'];
					$this->logger->log( "Test_run_id $tid has status: $status" );

					// If we have the test_results_manager_url now, update test data so the URL shows up early
					if ( ! empty( $tr['test_results_manager_url'] ) ) {
						$this->liveOutput->updateTestData( $tid, [
							'test_results_manager_url' => $tr['test_results_manager_url'],
						] );
					}

					if ( isset( $tr['update_complete'] ) && $tr['update_complete'] === true ) {
						maybe_echo( "Test run ID {$tid} finished with status: {$status}\n" );
						$this->logger->log( "Test_run_id $tid completed. Handling final response..." );
						$this->handle_qit_response_final( $allTests[ $tid ], $tr, $status );
						$completed_tests[] = $tid;
					} else {
						$this->liveOutput->setTestStatus( $tid, $status );
						maybe_echo( "Test run ID {$tid} status: {$status}, still polling...\n" );
						$still_in_progress[] = $tid;
					}
				}
			}

			foreach ( $completed_tests as $tid ) {
				unset( $allTests[ $tid ] );
			}

			foreach ( $unknown_tests as $tid ) {
				$still_in_progress[] = $tid;
			}

			// Decrement max_attempts for still in progress tests
			foreach ( $still_in_progress as $tid ) {
				if ( isset( $allTests[ $tid ] ) ) {
					$allTests[ $tid ]['max_attempts'] --;
					if ( $allTests[ $tid ]['max_attempts'] <= 0 ) {
						$this->logger->log( "Test_run_id {$tid} timed out" );
						$this->liveOutput->addTestError( $tid, "Did not finish in time." );
						maybe_echo( "Test run ID {$tid} did not finish in time.\n" );
						unset( $allTests[ $tid ] );
					}
				}
			}

			if ( ! empty( $allTests ) ) {
				$someTest       = reset( $allTests );
				$sleep_interval = $someTest['poll_interval'];

				// Instead of sleeping once, sleep in a loop and update the countdown
				for ( $i = $sleep_interval; $i > 0; $i -- ) {
					$this->liveOutput->setTimeToNextPoll( $i );
					$this->liveOutput->renderOutput();
					sleep( 1 );
				}
				// After finishing the countdown, clear the next poll display
				$this->liveOutput->setTimeToNextPoll( null );
				$this->liveOutput->renderOutput();
			}
		}

		maybe_echo( "All tests completed.\n" );
		$this->logger->log( "All tests completed. Preparing final summary." );

		$failures = $this->phpUnitRunner->getFailedTestsCount();
		$this->liveOutput->printFinalSummary( $failures );

		if ( $failures > 0 ) {
			exit( 1 );
		} else {
			exit( 0 );
		}
	}

	private function handle_qit_response_final( array $test_run, array $result, string $qit_status ): void {
		// Store the raw QIT status
		$this->liveOutput->setQitRawStatus( $test_run['test_run_id'], $qit_status );

		// Run PHPUnit snapshot verification at the end
		$this->phpUnitRunner->run_phpunit_test( $test_run, $result );
	}

	private function add_task_id_to_process( Process $process, array $test_run ) {
		$task_id_parts = [
			sprintf( "[%s -", ucwords( $test_run['type'] ) ),
			sprintf( "%s]", $test_run['slug'] ),
		];

		if ( ! empty( $test_run['php'] ) ) {
			$task_id_parts[] = sprintf( "[PHP %s]", $test_run['php'] );
		}
		if ( ! empty( $test_run['wp'] ) ) {
			$task_id_parts[] = sprintf( "[WP %s]", $test_run['wp'] );
		}
		if ( ! empty( $test_run['woo'] ) ) {
			$task_id_parts[] = sprintf( "[Woo %s]", $test_run['woo'] );
		}
		if ( ! empty( $test_run['features'] ) ) {
			$task_id_parts[] = sprintf( "[Features %s]", implode( ', ', $test_run['features'] ) );
		}

		$task_id = implode( ' ', $task_id_parts ) . ": ";
		$process->setEnv( array_merge( $process->getEnv(), [ 'qit_task_id' => $task_id ] ) );
	}
}