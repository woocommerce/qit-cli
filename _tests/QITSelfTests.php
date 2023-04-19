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

try {
	validate_context();

	require_once __DIR__ . '/vendor/autoload.php';

	$test_types = get_test_types();

	if ( ! is_null( Context::$suite ) ) {
		$test_types = array_filter( $test_types, function ( $test_type_path ) {
			return basename( $test_type_path ) === Context::$suite;
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
		throw new RuntimeException( 'Please run "composer install".' );
	}

	if ( ! in_array( Context::$action, [ 'run', 'update' ], true ) ) {
		throw new RuntimeException( 'Invalid action. Please use "run" or "update".' );
	}

	if ( ! file_exists( __DIR__ . '/../qit' ) ) {
		throw new RuntimeException( '"qit" binary does not exist in the parent directory.' );
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

			$tests_to_run[ basename( $test_type ) ][] = [
				'type'                 => basename( $test_type ),
				'slug'                 => basename( $test ),
				'php'                  => $env['php'] ?? '',
				'wp'                   => $env['wp'] ?? '',
				'woo'                  => $env['woo'] ?? '',
				'features'             => $env['features'] ?? '',
				'remove_from_snapshot' => $env['remove_from_snapshot'] ?? '',
				'path'                 => $test,
			];
		}
	}

	return $tests_to_run;
}

function run_test_runs( array $test_runs ) {
	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
		generate_phpunit_files( $test_type, $test_type_test_runs );
	}

	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
		generate_zips( $test_type_test_runs );
	}

	$processes = [];

	// Dispatch all tests in parallel using the qit binary.
	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
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

			$args[] = Context::$sut_slug;

			$p = new Process( $args );
			$p->setTimeout( null ); // Let QIT CLI handle timeouts.

			echo "[INFO] Preparing to run command {$p->getCommandLine()}\n";

			$p->setEnv( [
				'QIT_TEST_PATH'            => $t['path'],
				'QIT_TEST_SLUG'            => $t['slug'],
				'QIT_TEST_TYPE'            => $test_type,
				'QIT_RAN_TEST'             => false,
				'QIT_REMOVE_FROM_SNAPSHOT' => $t['remove_from_snapshot'],
			] );

			$processes[] = $p;
		}
	}

	$process_manager = new ProcessManager();

	echo "Starting tests in Parallel...\n";

	$process_manager->runParallel( $processes, 25, 10000, function ( string $type, string $out, Process $process ) {
		// This is the callback that will be called when a process generates output.
		// Ignore any output that is JSON, or is too long, and is possibly a chunked JSON.
		if ( is_null( json_decode( $out, true ) ) && strlen( $out ) < PipesInterface::CHUNK_SIZE * 0.9 ) {
			// Print anything that is not JSON.
			echo "[Process {$process->getPid()}]: $out\n";
		}
	} );

	echo "All tests finished. Processing results...\n";

	/**
	 * After the process has finished, iterate over the output.
	 * We do this when it finishes because the output that is generated while
	 * the process is running (as in the callback above) is chunked to 16kb,
	 * which might cut the JSON.
	 *
	 * @see \Symfony\Component\Process\Pipes\PipesInterface::CHUNK_SIZE
	 */
	foreach ( $processes as $p ) {
		$it = $p->getIterator( Process::ITER_SKIP_ERR | Process::ITER_KEEP_OUTPUT );
		foreach ( $it as $out ) {
			if ( ! is_null( json_decode( $out, true ) ) ) {
				/*
				 * Here we received a JSON output, so it must be the result.
				 * - Save the output in a file.
				 * - Run the PHPUnit test.
				 */
				$snapshot_filepath = $p->getEnv()['QIT_TEST_PATH'] . '/test-result.json';

				if ( file_exists( $snapshot_filepath ) ) {
					if ( ! unlink( $snapshot_filepath ) ) {
						throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
					}
				}

				$human_friendly_test_result = test_result_parser( $out, $p->getEnv()['QIT_REMOVE_FROM_SNAPSHOT'] );

				if ( ! file_put_contents( $p->getEnv()['QIT_TEST_PATH'] . '/test-result.json', $human_friendly_test_result ) ) {
					echo "[Process {$p->getPid()}]: Failed to write test output to file.\n";
					throw new RuntimeException( 'Failed to write test output to file.' );
				}

				// Run the test itself.
				//php ./vendor/bin/phpunit tests/ActivationTest.php --filter=test_php81_activation -d --update-snapshots
				$args = [
					__DIR__ . '/vendor/bin/phpunit',
					__DIR__ . '/tests/' . ucfirst( $p->getEnv()['QIT_TEST_TYPE'] ) . 'Test.php',
					/*
					 * Match a single test method by regex.
					 * Example test method: TestNamespace\TestCaseClass::testMethod
					 * Example regex: --filter=::testMethod$
					 * The "$" at the end is so that it does an exact match. For instance, the above regex would not match:
					 * TestNamespace\TestCaseClass::testMethodPhp82
					 */
					sprintf( '--filter=::test_%s_%s$', $p->getEnv()['QIT_TEST_TYPE'], $p->getEnv()['QIT_TEST_SLUG'] ),
					'--testdox', // Nice formatting.
				];

				if ( Context::$action === 'update' ) {
					$args[] = '-d';
					$args[] = '--update-snapshots';
				}

				$phpunit_process = new Process( $args );

				echo "[INFO] Preparing to run test: {$phpunit_process->getCommandLine()}\n";

				$phpunit_process->mustRun( function ( $type, $out ) {
					echo substr( $out, 0, 500 ) . "\n";
				} );

				cleanup_test( $p->getEnv()['QIT_TEST_PATH'] );

				$p->setEnv( [ 'QIT_RAN_TEST' => true ] );
			}
		}
	}

	foreach ( $processes as $p ) {
		if ( ! $p->getEnv()['QIT_RAN_TEST'] ) {
			throw new RuntimeException( "[Process {$p->getPid()}]: Test {$p->getEnv()['QIT_TEST_PATH']} did not run.\n" );
		}
	}
}

function generate_phpunit_files( string $test_type, array $test_runs ): void {
	$name     = ucfirst( $test_type ) . 'Test';
	$filepath = __DIR__ . '/tests/' . $name . '.php';
	$tests    = '';

	foreach ( $test_runs as $test_run ) {
		$tests .= <<<PHP

	public function test_{$test_run['type']}_{$test_run['slug']}() {
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
	$processes = [];
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

		$processes[] = new Symfony\Component\Process\Process( $args );
	}

	$process_manager = new ProcessManager();
	$process_manager->runParallel( $processes, 25, 10000, function ( string $type, string $out, Process $process ) {
		echo "[Process {$process->getPid()}] $out";
	} );
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