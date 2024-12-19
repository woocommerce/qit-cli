<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PhpUnitRunner {
	private $logger;
	private $liveOutput;
	private $failedTestsCount = 0;

	public function __construct( Logger $logger, QITLiveOutput $liveOutput ) {
		$this->logger     = $logger;
		$this->liveOutput = $liveOutput;
	}

	public function generate_phpunit_files( string $test_type, array &$test_runs ): void {
		$this->logger->log( "Generating PHPUnit files for $test_type" );
		$class_name = ucfirst( str_replace( '-', '', $test_type ) ) . 'Test';
		$filename   = $class_name . '.php';
		$filepath   = __DIR__ . '/../tests/' . $filename;
		$tests      = '';

		foreach ( $test_runs as &$test_run ) {
			$json_name = $test_run['test_function_name'] . '.json';
			$tests     .= <<<PHP

    public function {$test_run['test_function_name']}() {
        \$this->assertMatchesSnapshot(\$this->validate_and_normalize(__DIR__ . '/../{$test_run['type']}/{$test_run['slug']}/$json_name'));
    }
PHP;
		}

		$test_file = <<<PHP
<?php

namespace QITE2E;

use QITE2E\QITE2ETestCase;
use Spatie\Snapshots\MatchesSnapshots;

class {$class_name} extends QITE2ETestCase {
    use MatchesSnapshots;
$tests
}
PHP;

		if ( file_exists( $filepath ) ) {
			if ( ! unlink( $filepath ) ) {
				$this->logger->log( "Could not delete old test file: $filepath" );
				throw new Exception( 'Could not delete old test file.' );
			} else {
				$this->logger->log( "Deleted old test file: $filepath" );
			}
		}

		if ( ! file_put_contents( $filepath, $test_file ) ) {
			$this->logger->log( "Could not write test file: $filepath" );
			throw new Exception( 'Could not write test file.' );
		} else {
			$this->logger->log( "Wrote test file: $filepath" );
		}
	}

	public function run_phpunit_test( array $test_run, array $result ): void {
		$test_function_name   = $test_run['env']['QIT_TEST_FUNCTION_NAME'];
		$test_run_id          = $test_run['test_run_id'];
		$qit_test_path        = $test_run['env']['QIT_TEST_PATH'];
		$remove_from_snapshot = $test_run['env']['QIT_REMOVE_FROM_SNAPSHOT'];
		$reuse_json           = ( getenv( 'QIT_REUSE_JSON' ) === '1' );

		$snapshot_filepath = sprintf( '%s/%s.json', $qit_test_path, $test_function_name );

		$test_file   = __DIR__ . '/../tests/' . $this->generate_test_file_name( $test_run['type'] );
		$snapshotDir = dirname( $test_file ) . '/__snapshots__';

		if ( ! is_dir( $snapshotDir ) ) {
			$this->logger->log( "__snapshots__ directory not found at $snapshotDir, attempting to create..." );
			if ( ! mkdir( $snapshotDir, 0777, true ) && ! is_dir( $snapshotDir ) ) {
				$this->logger->log( "Failed to create __snapshots__ directory at $snapshotDir" );
			} else {
				$this->logger->log( "Created __snapshots__ directory at $snapshotDir" );
			}
		}

		if ( $reuse_json && file_exists( $snapshot_filepath ) ) {
			$this->logger->log( "Reusing existing JSON for test_run_id $test_run_id: $snapshot_filepath" );
		} else {
			if ( file_exists( $snapshot_filepath ) && ! $reuse_json ) {
				if ( ! unlink( $snapshot_filepath ) ) {
					$this->logger->log( "Failed to delete snapshot file: $snapshot_filepath" );
					throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
				} else {
					$this->logger->log( "Deleted old snapshot file: $snapshot_filepath" );
				}
			}

			$human_friendly_test_result = test_result_parser( json_encode( $result ), $remove_from_snapshot );

			if ( ! file_put_contents( $snapshot_filepath, $human_friendly_test_result ) ) {
				maybe_echo( "[Test {$test_run_id}]: Failed to write test output to file.\n" );
				$this->logger->log( "Failed to write human friendly result for test_run_id $test_run_id" );
				throw new RuntimeException( 'Failed to write test output to file.' );
			} else {
				$this->logger->log( "Wrote snapshot file: $snapshot_filepath" );
			}

			if ( ! $reuse_json ) {
				Context::$to_delete[] = $snapshot_filepath;
			}
		}

		$this->logger->log( "Printing JSON content from $snapshot_filepath:" );
		$json_content = @file_get_contents( $snapshot_filepath );
		if ( $json_content !== false ) {
			$this->logger->log( "JSON Content:\n$json_content" );
		} else {
			$this->logger->log( "Could not read $snapshot_filepath" );
		}

		$this->logger->log( "Snapshot directory contents BEFORE PHPUnit:" );
		$this->logDirectoryContents( $snapshotDir );

		if ( ! empty( $result['test_results_manager_url'] ) ) {
			$this->liveOutput->updateTestData( $test_run_id, [
				'test_results_manager_url' => $result['test_results_manager_url'],
			] );
		}

		$args = [
			__DIR__ . '/../vendor/bin/phpunit',
			$test_file,
			sprintf( '--filter=::%s$', $test_function_name ),
			'--testdox',
			'-v',
			'--no-coverage',
		];

		if ( Context::$action === 'update' ) {
			$args[] = '-d';
			$args[] = '--update-snapshots';
		}

		$this->logger->log( "Running PHPUnit with command: " . implode( ' ', $args ) );

		$phpunit_process = new Process( $args );
		$phpunit_process->setTimeout( 1200 );
		$phpunit_process->setIdleTimeout( 1200 );

		$this->logger->log( "Environment variables for PHPUnit:" );
		foreach ( $phpunit_process->getEnv() as $k => $v ) {
			$this->logger->log( "$k=$v" );
		}

		try {
			$phpunit_process->mustRun();
			$resultMessage = trim( $phpunit_process->getOutput() );
			$errorMessage  = trim( $phpunit_process->getErrorOutput() );

			$this->logger->log( "PHPUnit output for test_run_id $test_run_id:\n$resultMessage" );
			if ( ! empty( $errorMessage ) ) {
				$this->logger->log( "PHPUnit error output for test_run_id $test_run_id:\n$errorMessage" );
				$this->liveOutput->addTestError( $test_run_id, $errorMessage );
			}

			$combinedMessage = $resultMessage . ( $errorMessage ? "\n$errorMessage" : '' );

			$this->liveOutput->setTestCompleted(
				$test_run_id,
				true,
				$result['test_results_manager_url'] ?? null,
				$test_run['non_json_output_file'] ?? null,
				$combinedMessage
			);
		} catch ( ProcessFailedException $e ) {
			$resultMessage = $phpunit_process->getOutput();
			$errorMessage  = $phpunit_process->getErrorOutput();

			$this->logger->log( "PHPUnit failed for test_run_id $test_run_id. Output:\n$resultMessage" );
			if ( ! empty( $errorMessage ) ) {
				$this->logger->log( "PHPUnit error output for test_run_id $test_run_id:\n$errorMessage" );
				$this->liveOutput->addTestError( $test_run_id, $errorMessage );
			}

			$combinedMessage = $resultMessage . ( $errorMessage ? "\n$errorMessage" : '' );

			$this->liveOutput->setTestCompleted(
				$test_run_id,
				false,
				$result['test_results_manager_url'] ?? null,
				$test_run['non_json_output_file'] ?? null,
				$combinedMessage
			);
			$this->failedTestsCount ++;
		}

	}

	public function getFailedTestsCount(): int {
		return $this->failedTestsCount;
	}

	private function generate_test_file_name( string $test_type ) {
		return ucfirst( str_replace( '-', '', $test_type ) ) . 'Test.php';
	}

	private function logDirectoryContents( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			$this->logger->log( "$dir does not exist or is not a directory." );

			return;
		}

		$cmd    = "ls -la " . escapeshellarg( $dir );
		$output = shell_exec( $cmd );
		$this->logger->log( "Directory listing for $dir:\n$output" );
	}
}
