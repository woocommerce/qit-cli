<?php
/*
 * Check the README.md for documentation about this file.
 *
 * - Iterates over each test in each test-type
 * - Create a zip file for the SUT
 * - Create a PHPUnit test file for each test
 * - Run all tests in parallel
 * - Checks that the result matches the snapshot
 */

use Jack\Symfony\ProcessManager;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/ProcessManagerFork.php';
require_once __DIR__ . '/QITLiveOutput.php';

// These test types cannot run in parallel.
$tests_based_on_custom_tests = [ 'activation' ];

class Context {
	public static $action;
	public static $test_types;
	public static $running_test_based_on_custom_test;
	public static $scenarios;
	public static $env_filters;
	public static $debug_mode;

	public static $to_delete = [];

	public static $extension_slug = 'woocommerce-product-feeds';
	public static $theme_slug = 'bistro';
}

$params = $GLOBALS['argv'];

if ( ( $debugKey = array_search( '--debug', $params, true ) ) !== false ) {
	Context::$debug_mode = true;
	unset( $params[ $debugKey ] );
}

Context::$action = $params[1] ?? 'run';

# Comma-separated list of test-types to run, eg: woo-e2e,woo-api
if ( isset( $params[2] ) ) {
	Context::$test_types = array_map( 'trim', explode( ',', $params[2] ) );

	if ( count( Context::$test_types ) > 1 ) {
		foreach ( $tests_based_on_custom_tests as $custom_test ) {
			if ( in_array( $custom_test, Context::$test_types, true ) ) {
				echo "Cannot run tests based on custom tests in parallel with other tests.\n";
				die( 1 );
			}
		}
	}
} else {
	Context::$test_types = null;
}

Context::$running_test_based_on_custom_test = ! is_null( Context::$test_types ) && count( array_intersect( Context::$test_types, $tests_based_on_custom_tests ) ) > 0;

# Comma-separated list of scenarios to run, eg: no_op,no_op_php82,delete_products
if ( isset( $params[3] ) ) {
	Context::$scenarios = array_map( 'trim', explode( ',', $params[3] ) );
	Context::$scenarios = array_filter( Context::$scenarios, static function ( $v ) {
		return strpos( $v, "--" ) !== 0;
	} );

	if ( empty( Context::$scenarios ) ) {
		Context::$scenarios = null;
	}
} else {
	Context::$scenarios = null;
}

Context::$env_filters = [];

foreach (
	array_filter( $params, static function ( $param ) {
		return strpos( $param, '--env_filter=' ) === 0;
	} ) as $env_filter
) {
	[ $key, $value ] = explode( '=', substr( $env_filter, 13 ), 2 );

	if ( array_key_exists( $key, Context::$env_filters ) ) {
		echo "Duplicate key '{$key}' found in env filters.";
		die( 1 );
	}

	Context::$env_filters[ $key ] = $value;
}

require_once __DIR__ . '/test-result-parser.php';

register_shutdown_function( function () {
	$to_delete = array_unique( Context::$to_delete );
	foreach ( $to_delete as $file ) {
		if ( file_exists( $file ) ) {
			if ( ! unlink( $file ) ) {
				throw new RuntimeException( "Failed to delete file: $file" );
			}
		}
	}
} );

try {
	validate_context();

	require_once __DIR__ . '/vendor/autoload.php';

	$GLOBALS['qitLiveOutput'] = new QITLiveOutput();

	$test_types = get_test_types();

	if ( ! is_null( Context::$test_types ) ) {
		$test_types = array_filter( $test_types, function ( $test_type_path ) {
			return in_array( basename( $test_type_path ), Context::$test_types, true );
		} );
	} else {
		$test_types = array_filter( $test_types, function ( $test_type_path ) use ( $tests_based_on_custom_tests ) {
			return ! in_array( basename( $test_type_path ), $tests_based_on_custom_tests, true );
		} );

		if ( count( $test_types ) !== count( get_test_types() ) ) {
			echo sprintf( "Skipping tests based on custom tests, which must run in a dedicated process: \n - %s", implode( "\n - ", array_map( 'basename', array_diff( get_test_types(), $test_types ) ) ) ) . "\n";
		}
	}

	if ( getenv( 'QIT_SKIP_E2E' ) === 'yes' ) {
		$test_types = array_filter( $test_types, function ( $test_type_path ) {
			return basename( $test_type_path ) !== 'woo-e2e';
		} );
	}

	if ( empty( $test_types ) ) {
		throw new Exception( 'No test types found.' );
	}

	run_test_runs( generate_test_runs( $test_types ), $tests_based_on_custom_tests );
} catch ( \Exception $e ) {
	echo $e->getMessage() . "\nExiting by exception\n";
	die( 1 );
}


function validate_context(): void {
	if ( ! file_exists( __DIR__ . '/vendor' ) ) {
		throw new RuntimeException( 'Please run "composer install" on the directory: ' . __DIR__ );
	}

	if ( ! in_array( Context::$action, [ 'run', 'update' ], true ) ) {
		throw new RuntimeException( 'Invalid action. Please use "run" or "update".' );
	}

	if ( ! file_exists( __DIR__ . '/../../qit' ) ) {
		throw new RuntimeException( '"qit" binary does not exist in the parent-parent directory.' . dirname( __DIR__ ) );
	}
}

function get_test_types(): array {
	$test_types = [];
	$ignore     = [ 'vendor', 'tests' ];

	$it = new DirectoryIterator( __DIR__ );
	foreach ( $it as $file ) {
		if ( $file->isDir() && ! $file->isDot() && ! in_array( $file->getBaseName(), $ignore, true ) ) {
			$test_types[] = $file->getPathname();
		}
	}

	return $test_types;
}

function get_tests_in_test_type( string $path ) {
	$tests = [];
	$it    = new DirectoryIterator( $path );
	foreach ( $it as $file ) {
		if ( $file->isDir() && ! $file->isDot() ) {
			if ( stripos( $file->getBasename(), '-' ) !== false ) {
				throw new \UnexpectedValueException( sprintf( 'Please rename the test "%s" to "%s"', $file->getBasename(), str_replace( '-', '_', $file->getBasename() ) ) );
			}
			$tests[] = $file->getPathname();
		}
	}

	return $tests;
}

function generate_test_runs( array $test_types ): array {
	$tests_to_run = [];

	foreach ( $test_types as $test_type ) {
		$tests_to_run[ basename( $test_type ) ] = [];
		foreach ( get_tests_in_test_type( $test_type ) as $test ) {
			if ( ! is_null( Context::$scenarios ) ) {
				if ( ! in_array( basename( $test ), Context::$scenarios ) ) {
					echo sprintf( "Skipping %s, running only %s\n", basename( $test ), implode( ',', Context::$scenarios ) );
					continue;
				}
			}

			$env = require $test . '/env.php';

			$wp_versions  = isset( $env['wp'] ) ? explode( ',', $env['wp'] ) : [ '' ];
			$woo_versions = isset( $env['woo'] ) ? explode( ',', $env['woo'] ) : [ '' ];
			$php_versions = isset( $env['php'] ) ? explode( ',', $env['php'] ) : [ '' ];

			foreach ( $wp_versions as $wp_version ) {
				foreach ( $woo_versions as $woo_version ) {
					foreach ( $php_versions as $php_version ) {

						if ( file_exists( $test . '/' . Context::$extension_slug ) ) {
							$sut_slug = Context::$extension_slug;
						} else {
							$sut_slug = Context::$theme_slug;
						}

						if ( ! empty( Context::$env_filters ) ) {
							$env_matches = true;
							foreach ( Context::$env_filters as $key => $value ) {
								if ( ! isset( $env[ $key ] ) ) {
									$env_matches = false;
									break;
								}

								switch ( $key ) {
									case 'wp':
										$env_matches = $value === $wp_version;
										break;
									case 'woo':
										$env_matches = $value === $woo_version;
										break;
									case 'php':
										$env_matches = $value === $php_version;
										break;
									default:
										$env_matches = $value === $env[ $key ];
										break;
								}

								if ( ! $env_matches ) {
									break;
								}
							}

							if ( ! $env_matches ) {
								echo sprintf( "Skipping %s, does not match env filters\n", basename( $test ) );
								continue;
							}
						}

						$tests_to_run[ basename( $test_type ) ][] = [
							'type'                 => basename( $test_type ),
							'slug'                 => basename( $test ),
							'php'                  => $php_version,
							'wp'                   => $wp_version,
							'woo'                  => $woo_version,
							'features'             => $env['features'] ?? '',
							'remove_from_snapshot' => $env['remove_from_snapshot'] ?? '',
							'params'               => $env['params'] ?? [],
							'path'                 => $test,
							'sut_slug'             => $sut_slug,
						];
					}
				}
			}
		}
	}

	return $tests_to_run;
}

function add_task_id_to_process( Process $process, array $test_run ) {
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

function run_test_runs( array $test_runs, array $tests_based_on_custom_tests ) {
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		generate_zips( $test_type_test_runs );
	}

	// Run all tests and collect them
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		foreach ( $test_type_test_runs as &$t ) {
			$php      = ( new PhpExecutableFinder() )->find( false );
			$qit      = realpath( __DIR__ . '/../../src/qit-cli.php' );
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

			$args[] = $sut_slug;

			$qit_process = new Process( $args );
			echo "\nRunning command: " . $qit_process->getCommandLine() . "\n";

			$qit_process->setTimeout( null );

			add_task_id_to_process( $qit_process, $t );

			$qit_process->mustRun();
			$output = trim( $qit_process->getOutput() );

			$json = json_decode( $output, true );
			if ( json_last_error() !== JSON_ERROR_NONE || empty( $json['test_run_id'] ) ) {
				throw new RuntimeException( "Failed to get valid JSON test_run_id from qit run command:\n$output" );
			}

			$t['test_run_id'] = $json['test_run_id'];

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

			$t['non_json_output_file'] = tempnam( sys_get_temp_dir(), 'qit_non_json_' );

			// Set poll intervals and attempts based on test type.
			// Normal tests: ~30m max => 30m / 30s = 60 attempts
			// Woo-e2e tests: ~2h max => 120m / 0.5 min = 240 attempts
			$poll_interval = 30; // 30 seconds per attempt
			if ( strpos( $normalized_t['type'], 'e2e' ) !== false ) {
				$max_attempts = 240; // 2h
			} else {
				$max_attempts = 60; // 30m
			}

			$t['env'] = [
				'QIT_TEST_PATH'            => $t['path'],
				'QIT_TEST_TYPE'            => $test_type,
				'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
				'QIT_RAN_TEST'             => false,
				'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
				'QIT_NON_JSON_OUTPUT'      => $t['non_json_output_file'],
				'QIT_POLL_INTERVAL'        => $poll_interval,
				'QIT_MAX_ATTEMPTS'         => $max_attempts,
			];

			$GLOBALS['qitLiveOutput']->addTest( $t['test_run_id'], $qit_process->getEnv()['qit_task_id'] ?? "[{$t['type']}] {$t['slug']}" );
		}
	}

	// After running all tests, generate PHPUnit files
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		generate_phpunit_files( $test_type, $test_type_test_runs );
	}

	// Flatten all tests into a single array for parallel polling
	$allTests = [];
	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
		foreach ( $test_type_test_runs as $t ) {
			// Store per-test max attempts as well
			$t['max_attempts'] = $t['env']['QIT_MAX_ATTEMPTS'];
			$allTests[]        = $t;
		}
	}

	// Poll all tests together using get-multiple
	while ( ! empty( $allTests ) ) {
		$test_run_ids = array_map( function ( $test ) {
			return $test['test_run_id'];
		}, $allTests );

		$ids_param = implode( ',', $test_run_ids );

		$get_process = new Process( [
			( new PhpExecutableFinder() )->find( false ),
			realpath( __DIR__ . '/../../src/qit-cli.php' ),
			'get-multiple',
			'--json',
			$ids_param,
		] );

		$get_process->run();
		$get_output  = trim( $get_process->getOutput() );
		$result_json = json_decode( $get_output, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $result_json ) ) {
			// Mark all as unknown
			foreach ( $allTests as $key => $test ) {
				$GLOBALS['qitLiveOutput']->setTestStatus( $test['test_run_id'], 'unknown' );
				echo "Failed to parse JSON or invalid response for test_run_id {$test['test_run_id']}.\n";
				// Decrement attempts
				$allTests[ $key ]['max_attempts'] --;
			}
		} else {
			// Update each test from the single response
			foreach ( $allTests as $key => $test ) {
				$test_run_id = $test['test_run_id'];
				if ( ! isset( $result_json[ $test_run_id ] ) || ! isset( $result_json[ $test_run_id ]['status'] ) ) {
					$GLOBALS['qitLiveOutput']->setTestStatus( $test_run_id, 'unknown' );
					echo "No status in response for test_run_id {$test_run_id}.\n";
					$allTests[ $key ]['max_attempts'] --;
					continue;
				}

				$tr     = $result_json[ $test_run_id ];
				$status = $tr['status'];

				if ( isset( $tr['update_complete'] ) && $tr['update_complete'] === true ) {
					// Test finished
					echo "Test run ID {$test_run_id} finished with status: {$status}\n";
					handle_qit_response_final( $test, $tr );
					unset( $allTests[ $key ] );
				} else {
					// Still in progress
					$GLOBALS['qitLiveOutput']->setTestStatus( $test_run_id, $status );
					echo "Test run ID {$test_run_id} status: {$status}, still polling...\n";
					$allTests[ $key ]['max_attempts'] --;
				}
			}
		}

		// Remove any tests that ran out of attempts.
		foreach ( $allTests as $key => $test ) {
			if ( $test['max_attempts'] <= 0 ) {
				$GLOBALS['qitLiveOutput']->addTestError( $test['test_run_id'], "Did not finish in time." );
				echo "Test run ID {$test['test_run_id']} did not finish in time.\n";
				unset( $allTests[ $key ] );
			}
		}

		if ( ! empty( $allTests ) ) {
			// Sleep before next polling round
			// Use the poll interval from the first test (they all have same intervals anyway)
			$sleep_interval = $allTests[ array_key_first( $allTests ) ]['env']['QIT_POLL_INTERVAL'];
			sleep( $sleep_interval );
		}
	}

	echo "All tests completed.\n";
}

function make_test_result_json_filename( Process $process ): string {
	return "{$process->getEnv()['QIT_TEST_FUNCTION_NAME']}.json";
}

function make_test_result_json_filepath( Process $process ): string {
	return sprintf( '%s/%s', $process->getEnv()['QIT_TEST_PATH'], make_test_result_json_filename( $process ) );
}

function generate_test_file_name( string $test_type ) {
	return ucfirst( str_replace( '-', '', $test_type ) ) . 'Test.php';
}

function handle_qit_response_final( array $test_run, array $result ): void {
	$qit_test_path        = $test_run['env']['QIT_TEST_PATH'];
	$remove_from_snapshot = $test_run['env']['QIT_REMOVE_FROM_SNAPSHOT'];
	$test_function_name   = $test_run['env']['QIT_TEST_FUNCTION_NAME'];
	$test_run_id          = $test_run['test_run_id'];

	$snapshot_filepath = sprintf( '%s/%s.json', $qit_test_path, $test_function_name );

	if ( file_exists( $snapshot_filepath ) ) {
		if ( ! unlink( $snapshot_filepath ) ) {
			throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
		}
	}

	$human_friendly_test_result = test_result_parser( json_encode( $result ), $remove_from_snapshot );

	if ( ! file_put_contents( $snapshot_filepath, $human_friendly_test_result ) ) {
		echo "[Test {$test_run['test_run_id']}]: Failed to write test output to file.\n";
		throw new RuntimeException( 'Failed to write test output to file.' );
	}

	Context::$to_delete[] = $snapshot_filepath;

	$args = [
		__DIR__ . '/vendor/bin/phpunit',
		__DIR__ . '/tests/' . generate_test_file_name( $test_run['type'] ),
		sprintf( '--filter=::%s$', $test_function_name ),
		'--testdox',
	];

	if ( Context::$action === 'update' ) {
		$args[] = '-d';
		$args[] = '--update-snapshots';
	}

	$phpunit_process = new Process( $args );
	$phpunit_process->setTimeout( 1200 );
	$phpunit_process->setIdleTimeout( 1200 );

	try {
		$phpunit_process->mustRun();
		$resultMessage = trim( $phpunit_process->getOutput() );

		$success = true; // If mustRun() didn't throw, test passed.
		$GLOBALS['qitLiveOutput']->setTestCompleted( $test_run_id, $success, $result['test_results_manager_url'] ?? null, $test_run['non_json_output_file'] ?? null, $resultMessage );
	} catch ( ProcessFailedException $e ) {
		$resultMessage = $phpunit_process->getOutput();
		echo "The test {$test_function_name} failed.\n";
		$GLOBALS['qitLiveOutput']->setTestCompleted( $test_run_id, false, $result['test_results_manager_url'] ?? null, $test_run['non_json_output_file'] ?? null, $resultMessage );
		die( 1 );
	}
}

function generate_phpunit_files( string $test_type, array &$test_runs ): void {
	$name     = str_replace( '.php', '', generate_test_file_name( $test_type ) );
	$filepath = __DIR__ . '/tests/' . generate_test_file_name( $test_type );
	$tests    = '';

	foreach ( $test_runs as &$test_run ) {
		$json_name = $test_run['test_function_name'] . '.json';
		$tests     .= <<<PHP

    public function {$test_run['test_function_name']}() {
        \$this->assertMatchesSnapshot( \$this->validate_and_normalize( __DIR__ . '/../{$test_run['type']}/{$test_run['slug']}/$json_name' ) );
    }
PHP;
	}

	$test_file = <<<PHP
<?php

namespace QITE2E;

use QITE2E\QITE2ETestCase;
use Spatie\Snapshots\MatchesSnapshots;

class $name extends QITE2ETestCase {
    use MatchesSnapshots;
$tests
}
PHP;

	if ( file_exists( $filepath ) ) {
		if ( ! unlink( $filepath ) ) {
			throw new Exception( 'Could not delete old test file.' );
		}
	}

	if ( ! file_put_contents( $filepath, $test_file ) ) {
		throw new Exception( 'Could not write test file.' );
	}
}

function generate_zips( array $test_type_test_runs ) {
	$zip_processes  = [];
	$generated_zips = [];
	foreach ( $test_type_test_runs as $t ) {
		$path = $t['path'];
		$slug = $t['sut_slug'];

		if ( in_array( md5( $path . $slug ), $generated_zips, true ) ) {
			echo "[INFO] Skipping zip generation for test in {$t['path']} (Another test in same dir already zipped)\n";
			continue;
		}

		$generated_zips[] = md5( $path . $slug );

		$args = [
			"docker",
			'run',
			'--rm',
			'--user',
			posix_getuid() . ":" . posix_getgid(),
			'-w',
			'/app',
			'-v',
			"$path:/app",
			'joshkeegan/zip:latest',
			'sh',
			'-c',
			"rm -f sut.zip && zip -r sut.zip $slug",
		];

		$zip_process = new Process( $args );
		add_task_id_to_process( $zip_process, $t );
		$zip_processes[] = $zip_process;
	}

	$zip_processes_manager = new ProcessManager();

	$zip_processes_manager->runParallel(
		$zip_processes,
		25,
		10000,
		function ( string $type, string $out, Process $process ) {
			echo $out;
		}
	);

	foreach ( $zip_processes as $zip_process ) {
		if ( ! $zip_process->isSuccessful() ) {
			throw new RuntimeException( "Failed to create zip file for test: {$zip_process->getEnv()['qit_task_id']}" );
		}
	}
}