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
use Symfony\Component\Process\Pipes\PipesInterface;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/ProcessManagerFork.php';

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

	/*
	 * To run tests in QIT, we need to assign the results to a plugin in the Marketplace.
	 * We use the extension "woocommerce-product-feeds" and theme "storefront", because they're owned by the test user in Staging.
	 */
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
	// Filter any values that start with "--".
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
	// Check for "--env_filter=<KEY>=<VALUE>"
	array_filter( $params, static function ( $param ) {
		return strpos( $param, '--env_filter=' ) === 0;
	} ) as $env_filter
) {
	// Remove the '--env_filter=' prefix and then split the remaining string by '='
	[ $key, $value ] = explode( '=', substr( $env_filter, 13 ), 2 );

	if ( array_key_exists( $key, Context::$env_filters ) ) {
		echo "Duplicate key '{$key}' found in env filters.";
		die( 1 );
	}

	Context::$env_filters[ $key ] = $value;
}

require_once __DIR__ . '/test-result-parser.php';
require_once __DIR__ . '/ParallelOutput.php';

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

	$GLOBALS['parallelOutput'] = new ParallelOutput();

	$test_types = get_test_types();

	if ( ! is_null( Context::$test_types ) ) {
		$test_types = array_filter( $test_types, function ( $test_type_path ) {
			return in_array( basename( $test_type_path ), Context::$test_types, true );
		} );
	} else {
		// Remove tests based on custom tests if no specific test type is defined.
		$test_types = array_filter( $test_types, function ( $test_type_path ) use ( $tests_based_on_custom_tests ) {
			return ! in_array( basename( $test_type_path ), $tests_based_on_custom_tests, true );
		} );

		// If anything is removed, print what was removed.
		if ( count( $test_types ) !== count( get_test_types() ) ) {
			$GLOBALS['parallelOutput']->addRawOutput( sprintf( "Skipping tests based on custom tests, which must run in a dedicated process: \n - %s", implode( "\n - ", array_map( 'basename', array_diff( get_test_types(), $test_types ) ) ) ) );
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
	if ( isset( $GLOBALS['parallelOutput'] ) && $GLOBALS['parallelOutput'] instanceof ParallelOutput ) {
		$GLOBALS['parallelOutput']->addRawOutput( $e->getMessage() );
	} else {
		echo $e->getMessage();
	}
	echo "Exiting by exception\n";
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

/**
 * @return array<string> The list of test types.
 */
function get_test_types(): array {
	$test_types = [];
	$ignore     = [
		'vendor',
		'tests'
	];

	$it = new DirectoryIterator( __DIR__ );
	/** @var DirectoryIterator $file */
	foreach ( $it as $file ) {
		if ( $file->isDir() && ! $file->isDot() && ! in_array( $file->getBaseName(), $ignore, true ) ) {
			$test_types[] = $file->getPathname();
		}
	}

	return $test_types;
}

/**
 * @param string $path The path to a test type directory.
 *
 * @return array<string> The list of tests in the given test type.
 */
function get_tests_in_test_type( string $path ) {
	$tests = [];
	$it    = new DirectoryIterator( $path );
	/** @var DirectoryIterator $file */
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
			// If we defined a single test to run, skip those that are not it.
			if ( ! is_null( Context::$scenarios ) ) {
				if ( ! in_array( basename( $test ), Context::$scenarios ) ) {
					$GLOBALS['parallelOutput']->addRawOutput( sprintf( "Skipping %s, running only %s\n", basename( $test ), implode( ',', Context::$scenarios ) ) );
					continue;
				}
			}

			$env = require $test . '/env.php';

			$wp_versions = isset( $env['wp'] ) ? explode( ',', $env['wp'] ) : [ '' ];  // default to empty string if no versions.
			$woo_versions = isset( $env['woo'] ) ? explode( ',', $env['woo'] ) : [ '' ];  // default to empty string if no versions.
			$php_versions = isset( $env['php'] ) ? explode( ',', $env['php'] ) : [ '' ];  // default to empty string if no versions.

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
								$GLOBALS['parallelOutput']->addRawOutput( sprintf( "Skipping %s, does not match env filters\n", basename( $test ) ) );
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
		sprintf( "%s]", $test_run['slug'] )
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

function copy_task_id_to_process( Process $process_with_task_id, Process $process_without_task_id ) {
	$process_without_task_id->setEnv( array_merge( $process_without_task_id->getEnv(), [ 'qit_task_id' => $process_with_task_id->getEnv()['qit_task_id'] ] ) );
}

function run_test_runs( array $test_runs, $tests_based_on_custom_tests ) {
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		generate_zips( $test_type_test_runs );
	}

	$qit_run_processes = [];

	// Dispatch all tests in parallel using the qit binary.
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		foreach ( $test_type_test_runs as &$t ) {
			$php      = ( new PhpExecutableFinder() )->find( false );
			$qit      = realpath( __DIR__ . '/../../src/qit-cli.php' );
			$sut_slug = $t['sut_slug'];

			$args = [
				$php,
				// Run QIT with Xdebug disabled to avoid "Max concurrent settings" on PHPStorm from bottlenecking parallelism.
				$qit,
				"run:$test_type",
				'--wait',
				'--json',
				'--ignore-fail',
				"--zip={$t['path']}/sut.zip"
			];

			Context::$to_delete[] = "{$t['path']}/sut.zip";

			if ( Context::$debug_mode ) {
				$args[] = '-vvv';
			}

			if ( ! empty( $t['php'] ) ) {
				$args[] = "--php_version={$t['php']}";
			}

			if ( ! empty( $t['wp'] ) ) {
				if ( in_array( $test_type, $tests_based_on_custom_tests ) ) {
					$args[] = "--wp={$t['wp']}";
				} else {
					$args[] = "--wordpress_version={$t['wp']}";
				}
			}

			if ( ! empty( $t['woo'] ) ) {
				if ( in_array( $test_type, $tests_based_on_custom_tests ) ) {
					$args[] = "--woo={$t['woo']}";
				} else {
					$args[] = "--woocommerce_version={$t['woo']}";
				}
			}

			if ( in_array( $test_type, $tests_based_on_custom_tests ) ) {
				$args[] = "--no_upload_report";
			}

			if ( ! empty( $t['features'] ) ) {
				if ( ! is_array( $t['features'] ) ) {
					throw new \LogicException( 'Features must be an array.' );
				}
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
			$GLOBALS['parallelOutput']->addRawOutput( sprintf( "\nRunning command: %s", $qit_process->getCommandLine() ) );

			$qit_process->setTimeout( null ); // Let QIT CLI handle the timeouts.

			$normalized_t = $t;
			unset( $normalized_t['path'] );
			$normalized_t['type'] = str_replace( '-', '_', $normalized_t['type'] );

			/*
			 * Here we need a unique name that is human-readable, so that we can easily identify the test in the output.
			 * We use the md5 of the test data to make sure it's unique.
			 */
			$t['test_function_name'] = sprintf(
				'test_%s_%s_woo%s_php%s_wp%s_%s',
				$normalized_t['type'],
				$t['slug'],
				str_replace( '.', '', $t['woo'] ),
				str_replace( '.', '', $t['php'] ),
				str_replace( '.', '', $t['wp'] ),
				md5( json_encode( $normalized_t )
				)
			);
			
			$t['non_json_output_file'] = tempnam( sys_get_temp_dir(), 'qit_non_json_' );

			$poll_interval = rand( 20, 40 );

			if ( strpos( $normalized_t['type'], 'e2e' ) !== false ) {
				$poll_interval = rand( 60, 90 );
			}

			$env = [
				'QIT_TEST_PATH'            => $t['path'],
				'QIT_TEST_TYPE'            => $test_type,
				'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
				'QIT_RAN_TEST'             => false,
				'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
				'QIT_NON_JSON_OUTPUT'      => $t['non_json_output_file'],
				'QIT_POLL_INTERVAL'        => $poll_interval,
			];

			$qit_process->setEnv( $env );

			add_task_id_to_process( $qit_process, $t );

			$qit_run_processes[] = $qit_process;

			$t['qit_process'] = $qit_process;
		}
	}

	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		generate_phpunit_files( $test_type, $test_type_test_runs );
	}

	$qit_run_processes_manager = new ProcessManagerFork();

	$GLOBALS['parallelOutput']->addRawOutput( sprintf( "\nRunning %d tests in parallel...\n", count( $qit_run_processes ) ) );

	$json_buffer  = [];
	$failed_tests = [];

	$max_parallel = Context::$running_test_based_on_custom_test ? 1 : 20;

	$qit_run_processes_manager->runParallel( $qit_run_processes, $max_parallel, 10000, function ( string $type, string $out, Process $process ) use ( &$failed_tests, &$json_buffer ) {
		/*
		 * Callback function for handling process output.
		 * Outputs are chunked to 16kb, requiring special handling for large JSON outputs.
		 */

		// Initialize the JSON buffer. We will only need it if the JSON exceeds 16kb.
		if ( ! array_key_exists( $process->getPid(), $json_buffer ) ) {
			$json_buffer[ $process->getPid() ] = '';
		}

		// If there is something in the JSON buffer, consider all subsequent outputs as part of the same JSON.
		if ( ! empty( $json_buffer[ $process->getPid() ] ) ) {
			$json_buffer[ $process->getPid() ] .= $out;
		}

		// If the JSON buffer is valid JSON, then we can process it.
		if ( ! is_null( json_decode( $json_buffer[ $process->getPid() ], true ) ) ) {
			handle_qit_response( $process, $json_buffer[ $process->getPid() ], $failed_tests );

			return;
		}

		/*
		 * If we got here, it means the JSON buffer is not a valid JSON, so there is three possibilities:
		 * 1. It's either not a JSON, eg: "Running test..."
		 * 2. It's a chunked JSON part that exceeds 16kb, eg: "{"test":"test1","result":"passed"...(more than 16kb)
		 * 3. It's a full JSON that fits into the 16kb chunk, eg: "{"test":"test1","result":"passed","snapshot":"..."})
		 */
		$json_out = json_decode( $out, true );

		if ( is_null( $json_out ) && strlen( $out ) < PipesInterface::CHUNK_SIZE * 0.9 ) {
			// Scenario 1: Not a JSON.
			$GLOBALS['parallelOutput']->processOutputCallback( $out, $process );
		} elseif ( is_null( $json_out ) && strlen( $out ) > PipesInterface::CHUNK_SIZE * 0.9 ) {
			// Scenario 2: Possibly a chunked JSON.
			$json_buffer[ $process->getPid() ] .= $out;
		} elseif ( ! is_null( $json_out ) ) {
			// Scenario 3: Full JSON that fits the chunk.
			handle_qit_response( $process, $out, $failed_tests );
		}
	}, 10 );

	$did_not_run = [];

	foreach ( $qit_run_processes as $qit_process ) {
		if ( ! $qit_process->getEnv()['QIT_RAN_TEST'] ) {
			$did_not_run[] = [
				'process'     => $qit_process,
				'json_buffer' => $json_buffer[ $qit_process->getPid() ] ?? '',
			];
		}
	}

	if ( ! empty( $did_not_run ) ) {
		echo "The following tests did not run:\n" . implode( "\n", array_map( function ( $test ) {
				$header = $test['process']->getEnv()['qit_task_id'] ?? '';
				$buffer = $test['json_buffer'];

				return "[$header]: Test did not run.\n" . ( ! empty( $buffer ) ? "JSON Buffer: $buffer\n" : '' );
			}, $did_not_run ) );
		die( 1 );
	}

	if ( ! empty( $failed_tests ) ) {
		echo "The following tests failed:\n";
		foreach ( $failed_tests as $failed_test ) {
			echo $failed_test->getMessage() . "\n";
		}
		die( 1 );
	}
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

function handle_qit_response( Process $qit_process, string $out, array &$failed_tests ): void {
	$result = json_decode( $out, true );

	/*
	 * Here we received a JSON output, so it must be the result.
	 * - Save the output in a file.
	 * - Run the PHPUnit test.
	 */
	$snapshot_filepath = make_test_result_json_filepath( $qit_process );

	if ( file_exists( $snapshot_filepath ) ) {
		if ( ! unlink( $snapshot_filepath ) ) {
			throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
		}
	}

	$human_friendly_test_result = test_result_parser( $out, $qit_process->getEnv()['QIT_REMOVE_FROM_SNAPSHOT'] );

	if ( ! file_put_contents( $snapshot_filepath, $human_friendly_test_result ) ) {
		echo "[Process {$qit_process->getPid()}]: Failed to write test output to file.\n";
		throw new RuntimeException( 'Failed to write test output to file.' );
	}

	Context::$to_delete[] = $snapshot_filepath;

	// Run the test itself.
	//php ./vendor/bin/phpunit tests/ActivationTest.php --filter=test_php81_activation -d --update-snapshots
	$args = [
		__DIR__ . '/vendor/bin/phpunit',
		__DIR__ . '/tests/' . generate_test_file_name( $qit_process->getEnv()['QIT_TEST_TYPE'] ),
		/*
		 * Match a single test method by regex.
		 * Example test method: TestNamespace\TestCaseClass::testMethod
		 * Example regex: --filter=::testMethod$
		 * The "$" at the end is so that it does an exact match. For instance, the above regex would not match:
		 * TestNamespace\TestCaseClass::testMethodPhp82
		 */
		sprintf( '--filter=::%s$', $qit_process->getEnv()['QIT_TEST_FUNCTION_NAME'] ),
		'--testdox', // Nice formatting.
	];

	if ( Context::$action === 'update' ) {
		$args[] = '-d';
		$args[] = '--update-snapshots';
	}

	$phpunit_process = new Process( $args );
	$phpunit_process->setTimeout(1200);
	$phpunit_process->setIdleTimeout(1200);


	copy_task_id_to_process( $qit_process, $phpunit_process );

	// echo "[INFO] Preparing to run test: {$phpunit_process->getCommandLine()}\n";

	try {
		$phpunit_process->mustRun();
		$GLOBALS['parallelOutput']->processOutputCallback( $phpunit_process->getOutput(), $phpunit_process );
	} catch ( ProcessFailedException $e ) {
		$failed_tests[] = $e;
		$GLOBALS['parallelOutput']->processOutputCallback( $phpunit_process->getOutput(), $phpunit_process );
	} finally {
		$qit_process->setEnv( array_merge( $qit_process->getEnv(), [ 'QIT_RAN_TEST' => true, ] ) );
	}

	$GLOBALS['parallelOutput']->processOutputCallback( "\n\nTest Report: " . $result['test_results_manager_url'] . "\n", $phpunit_process );
}

function generate_phpunit_files( string $test_type, array &$test_runs ): void {
	$name = str_replace( '.php', '', generate_test_file_name( $test_type ) );
	$filepath = __DIR__ . '/tests/' . generate_test_file_name( $test_type );
	$tests    = '';

	foreach ( $test_runs as &$test_run ) {
		$json_name = make_test_result_json_filename( $test_run['qit_process'] );

		$tests .= <<<PHP

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
			$GLOBALS['parallelOutput']->addRawOutput( "[INFO] Skipping zip generation for test in {$t['path']} (Another test in same dir already zipped)\n" );
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


		$zip_process = new Symfony\Component\Process\Process( $args );

		add_task_id_to_process( $zip_process, $t );

		$zip_processes[] = $zip_process;
	}

	$zip_processes_manager = new ProcessManager();

	$zip_processes_manager->runParallel(
		$zip_processes,
		25,
		10000,
		function ( string $type, string $out, Process $process ) {
			// Pass the output to the ParallelOutput instance
			$GLOBALS['parallelOutput']->processOutputCallback( $out, $process );
		}
	);

	// Validate all of them finished successfully.
	foreach ( $zip_processes as $zip_process ) {
		if ( ! $zip_process->isSuccessful() ) {
			throw new RuntimeException( "Failed to create zip file for test: {$zip_process->getEnv()['qit_task_id']}" );
		}
	}
}