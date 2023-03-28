<?php

class Context {
	public static $action;
	public static $suite;
	public static $sut_slug;
}

Context::$action = $GLOBALS['argv'][1] ?? 'run';
Context::$suite  = $GLOBALS['argv'][2] ?? null;

/*
 * To run tests in QIT, we need to assign the results to a plugin in the Marketplace.
 * We use "woocommerce-product-feeds", because it's a plugin owned by the test user in Staging.
 */
Context::$sut_slug = 'woocommerce-product-feeds';

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
			$env = require $test . '/env.php';

			$tests_to_run[ basename( $test_type ) ][] = [
				'type' => basename( $test_type ),
				'slug' => basename( $test ),
				'php'  => $env['php'] ?? '',
				'wp'   => $env['wp'] ?? '',
				'woo'  => $env['woo'] ?? '',
				'path' => $test,
			];
		}
	}

	return $tests_to_run;
}

function run_test_runs( array $test_runs ) {
	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
		generate_test_files( $test_type, $test_type_test_runs );
	}

	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
		make_zips( $test_type_test_runs );
	}

	$processes = [];

	// Dispatch all tests in parallel using the qit binary.
	foreach ( $test_runs as $test_type => $test_type_test_runs ) {
		foreach ( $test_type_test_runs as $t ) {
			$args = [
				__DIR__ . '/../qit',
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

			$args[] = Context::$sut_slug;

			var_dump( $args );

			$p = new Symfony\Component\Process\Process( $args );
			var_dump( (string) $p->getCommandLine() );
			$p->setEnv( [
				'QIT_TEST_PATH' => $t['path'],
				'QIT_TEST_SLUG' => $t['slug'],
				'QIT_TEST_TYPE' => $test_type,
			] );

			$processes[] = $p;
		}
	}

	$process_manager = new \Jack\Symfony\ProcessManager();
	$process_manager->runParallel( $processes, 10, 1000, function ( string $type, string $out, \Symfony\Component\Process\Process $process ) {
		if ( ! is_null( json_decode( $out, true ) ) ) {
			$snapshot_filepath = $process->getEnv()['QIT_TEST_PATH'] . '/test-result.json';

			if ( file_exists( $snapshot_filepath ) ) {
				if ( ! unlink( $snapshot_filepath ) ) {
					throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
				}
			}

			if ( ! file_put_contents( $process->getEnv()['QIT_TEST_PATH'] . '/test-result.json', $out ) ) {
				echo "[Process {$process->getPid()}]: Failed to write test output to file.\n";
				throw new RuntimeException( 'Failed to write test output to file.' );
			}

			// Run the test itself.
			//php ./vendor/bin/phpunit tests/ActivationTest.php --filter=test_php81_activation -d --update-snapshots
			$args = [
				__DIR__ . '/vendor/bin/phpunit',
				__DIR__ . '/tests/' . ucfirst( $process->getEnv()['QIT_TEST_TYPE'] ) . 'Test.php',
				'--filter=test_' . $process->getEnv()['QIT_TEST_TYPE'] . '_' . $process->getEnv()['QIT_TEST_SLUG'],
			];

			if ( Context::$action === 'update' ) {
				$args[] = '-d --update-snapshots';
			}

			$process_id = $process->getPid();

			$test_process = new Symfony\Component\Process\Process( $args );
			$test_process->mustRun( function ( $type, $out ) use ( $process_id ) {
				echo "[Process $process_id]: $out\n";
			} );
		} else {
			echo "[Process {$process->getPid()}]: $out\n";
		}
	} );
}

function generate_test_files( string $test_type, array $test_runs ): void {
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

function make_zips( array $test_type_test_runs ) {
	$processes = [];
	foreach ( $test_type_test_runs as $t ) {
		$path = $t['path'];
		$slug = Context::$sut_slug;

		$relative_path = str_replace( __DIR__, '', $path );

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

	$process_manager = new \Jack\Symfony\ProcessManager();
	$process_manager->runParallel( $processes, 10, 1000, function ( string $type, string $out, \Symfony\Component\Process\Process $process ) {
		echo $out;
	} );
}