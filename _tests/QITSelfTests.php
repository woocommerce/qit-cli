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

class Context {
	public static $action;
	public static $suite;
	public static $test;
	public static $sut_slug;
}

Context::$action = $GLOBALS['argv'][1] ?? 'run';
Context::$suite  = $GLOBALS['argv'][2] ?? null;
Context::$test   = $GLOBALS['argv'][3] ?? null;

/*
 * To run tests in QIT, we need to assign the results to a plugin in the Marketplace.
 * We use "woocommerce-product-feeds", because it's a plugin owned by the test user in Staging.
 */
Context::$sut_slug = 'woocommerce-product-feeds';

require_once __DIR__ . '/test-result-parser.php';
require_once __DIR__ . '/ParallelOutput.php';

try {
	validate_context();

	require_once __DIR__ . '/vendor/autoload.php';

	$GLOBALS['parallelOutput'] = new ParallelOutput();

	$test_types = get_test_types();

	if ( ! is_null( Context::$suite ) ) {
		$suites = explode( ',', Context::$suite );

		$test_types = array_filter( $test_types, function ( $test_type_path ) use ( $suites ) {
			return in_array( basename( $test_type_path ), $suites );
		} );
	}

	if ( getenv( 'QIT_SKIP_E2E' ) === 'yes' ) {
		$test_types = array_filter( $test_types, function ( $test_type_path ) {
			return basename( $test_type_path ) !== 'e2e';
		} );
	}

	if ( empty( $test_types ) ) {
		throw new Exception( 'No test suites found.' );
	}

	run_test_runs( generate_test_runs( $test_types ) );
} catch ( \Exception $e ) {
	echo $e->getMessage();
	die( 1 );
}


function validate_context(): void {
	if ( ! file_exists( __DIR__ . '/vendor' ) ) {
		throw new RuntimeException( 'Please run "composer install" on the directory: ' . __DIR__ );
	}

	if ( ! in_array( Context::$action, [ 'run', 'update' ], true ) ) {
		throw new RuntimeException( 'Invalid action. Please use "run" or "update".' );
	}

	if ( ! file_exists( __DIR__ . '/../qit' ) ) {
		throw new RuntimeException( '"qit" binary does not exist in the parent directory.' . dirname( __DIR__ ) );
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
			if ( ! is_null( Context::$test ) ) {
				if ( basename( $test ) !== Context::$test ) {
					echo sprintf( "Skipping %s, running only %s\n", basename( $test ), Context::$test );
					continue;
				}
			}

			$env = require $test . '/env.php';

			$woo_versions = isset( $env['woo'] ) ? explode( ',', $env['woo'] ) : [ '' ];  // default to empty string if no versions.
			$php_versions = isset( $env['php'] ) ? explode( ',', $env['php'] ) : [ '' ];  // default to empty string if no versions.

			foreach ( $woo_versions as $woo_version ) {
				foreach ( $php_versions as $php_version ) {
					$tests_to_run[ basename( $test_type ) ][] = [
						'type'                 => basename( $test_type ),
						'slug'                 => basename( $test ),
						'php'                  => $php_version,
						'wp'                   => $env['wp'] ?? '',
						'woo'                  => $woo_version,
						'features'             => $env['features'] ?? '',
						'remove_from_snapshot' => $env['remove_from_snapshot'] ?? '',
						'params'               => $env['params'] ?? [],
						'path'                 => $test,
					];
				}
			}
		}
	}

	return $tests_to_run;
}

function add_task_id_to_process( Process $process, array $test_run ) {
	$task_id = sprintf(
		"[Test Type %s [Slug %s] [PHP %s] [WP %s] [Woo %s] [Features %s])]: \n",
		$test_run['type'],
		$test_run['slug'],
		$test_run['php'],
		$test_run['wp'],
		$test_run['woo'],
		$test_run['features']
	);

	$process->setEnv( array_merge( $process->getEnv(), [ 'qit_task_id' => $task_id ] ) );
}

function copy_task_id_to_process( Process $process_with_task_id, Process $process_without_task_id ) {
	$process_without_task_id->setEnv( array_merge( $process_without_task_id->getEnv(), [ 'qit_task_id' => $process_with_task_id->getEnv()['qit_task_id'] ] ) );
}

function run_test_runs( array $test_runs ) {
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		generate_phpunit_files( $test_type, $test_type_test_runs );
	}

	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		generate_zips( $test_type_test_runs );
	}

	$qit_run_processes = [];

	// Dispatch all tests in parallel using the qit binary.
	foreach ( $test_runs as $test_type => &$test_type_test_runs ) {
		foreach ( $test_type_test_runs as $t ) {
			$php = ( new PhpExecutableFinder() )->find( false );
			$qit = realpath( __DIR__ . '/../qit' );

			$args = [
				$php,
				'-d',
				'xdebug.mode=off',
				// Run QIT with Xdebug disabled to avoid "Max concurrent settings" on PHPStorm from bottlenecking parallelism.
				$qit,
				"run:$test_type",
				'--wait',
				'--json',
				'--ignore-fail',
				"--zip={$t['path']}/sut.zip"
			];

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
				$args[] = "--optional_features={$t['features']}";
			}

			if ( ! empty( $t['params'] ) ) {
				foreach ( $t['params'] as $param_name => $param_value ) {
					$args[] = "$param_name={$param_value}";
				}
			}

			$args[] = Context::$sut_slug;

			$qit_process = new Process( $args );
			$qit_process->setTimeout( null ); // Let QIT CLI handle timeouts.

			// echo "[INFO] Preparing to run command {$qit_process->getCommandLine()}\n";

			$qit_process->setEnv( [
				'QIT_TEST_PATH'            => $t['path'],
				'QIT_TEST_TYPE'            => $test_type,
				'QIT_TEST_FUNCTION_NAME'   => $t['test_function_name'],
				'QIT_WAIT_BEFORE_REQUEST'  => 'yes',
				'QIT_RAN_TEST'             => false,
				'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
			] );

			add_task_id_to_process( $qit_process, $t );

			$qit_run_processes[] = $qit_process;
		}
	}

	$qit_run_processes_manager = new ProcessManager();

	echo sprintf( "\nRunning %d tests in parallel...\n", count( $qit_run_processes ) );

	$json_buffer  = [];
	$failed_tests = [];

	$qit_run_processes_manager->runParallel( $qit_run_processes, 20, 10000, function ( string $type, string $out, Process $process ) use ( &$failed_tests, &$json_buffer ) {
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
	} );

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

function handle_qit_response( Process $qit_process, string $out, array &$failed_tests ): void {
	$result = json_decode( $out, true );

	/*
	 * Here we received a JSON output, so it must be the result.
	 * - Save the output in a file.
	 * - Run the PHPUnit test.
	 */
	$snapshot_filepath = $qit_process->getEnv()['QIT_TEST_PATH'] . '/test-result.json';

	if ( file_exists( $snapshot_filepath ) ) {
		if ( ! unlink( $snapshot_filepath ) ) {
			throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
		}
	}

	$human_friendly_test_result = test_result_parser( $out, $qit_process->getEnv()['QIT_REMOVE_FROM_SNAPSHOT'] );

	if ( ! file_put_contents( $qit_process->getEnv()['QIT_TEST_PATH'] . '/test-result.json', $human_friendly_test_result ) ) {
		echo "[Process {$qit_process->getPid()}]: Failed to write test output to file.\n";
		throw new RuntimeException( 'Failed to write test output to file.' );
	}

	// Run the test itself.
	//php ./vendor/bin/phpunit tests/ActivationTest.php --filter=test_php81_activation -d --update-snapshots
	$args = [
		__DIR__ . '/vendor/bin/phpunit',
		__DIR__ . '/tests/' . ucfirst( $qit_process->getEnv()['QIT_TEST_TYPE'] ) . 'Test.php',
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

	copy_task_id_to_process( $qit_process, $phpunit_process );

	// echo "[INFO] Preparing to run test: {$phpunit_process->getCommandLine()}\n";

	try {
		$phpunit_process->mustRun( function ( $type, $out ) use ( $phpunit_process ) {
			$GLOBALS['parallelOutput']->processOutputCallback( $out, $phpunit_process );
		} );
	} catch ( ProcessFailedException $e ) {
		$failed_tests[] = $e;
	} finally {
		cleanup_test( $qit_process->getEnv()['QIT_TEST_PATH'] );

		$qit_process->setEnv( [ 'QIT_RAN_TEST' => true ] );
	}

	$GLOBALS['parallelOutput']->processOutputCallback( "\n\nTest Report: " . $result['test_results_manager_url'] . "\n", $phpunit_process );
}

function generate_phpunit_files( string $test_type, array &$test_runs ): void {
	$name     = ucfirst( $test_type ) . 'Test';
	$filepath = __DIR__ . '/tests/' . $name . '.php';
	$tests    = '';

	foreach ( $test_runs as &$test_run ) {
		$test_run['test_function_name'] = sprintf( 'test_%s_%s_%s_%s', $test_run['type'], $test_run['slug'], $test_run['woo'], str_replace( '.', '', $test_run['php'] ) );
		$tests                          .= <<<PHP

	public function {$test_run['test_function_name']}() {
		\$this->assertMatchesSnapshot( \$this->validate_and_normalize( __DIR__ . '/../{$test_run['type']}/{$test_run['slug']}/test-result.json' ) );
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
	$zip_processes = [];
	foreach ( $test_type_test_runs as $t ) {
		$path = $t['path'];
		$slug = Context::$sut_slug;

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

function cleanup_test( $test_type_path ) {
	foreach ( [ 'test-result.json', 'sut.zip' ] as $file ) {
		if ( file_exists( "$test_type_path/$file" ) ) {
			if ( ! unlink( "$test_type_path/$file" ) ) {
				throw new RuntimeException( "Failed to delete test result file: $test_type_path/$file" );
			} else {
				echo "[Cleanup] Deleted file $test_type_path/$file\n";
			}
		} else {
			echo "[Cleanup] File does not exist $test_type_path/$file\n";
		}
	}
}