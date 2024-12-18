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
		$name     = ucfirst( str_replace( '-', '', $test_type ) ) . 'Test.php';
		$filepath = __DIR__ . '/../tests/' . $name;
		$tests    = '';

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

class {$name} extends QITE2ETestCase {
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

		$snapshot_filepath = sprintf( '%s/%s.json', $qit_test_path, $test_function_name );

		if ( file_exists( $snapshot_filepath ) ) {
			if ( ! unlink( $snapshot_filepath ) ) {
				$this->logger->log( "Failed to delete snapshot file: $snapshot_filepath" );
				throw new RuntimeException( "Failed to delete snapshot file: $snapshot_filepath" );
			} else {
				$this->logger->log( "Deleted old snapshot file: $snapshot_filepath" );
			}
		}

		$human_friendly_test_result = test_result_parser( json_encode( $result ), $remove_from_snapshot );

		if ( ! file_put_contents( $snapshot_filepath, $human_friendly_test_result ) ) {
			echo "[Test {$test_run_id}]: Failed to write test output to file.\n";
			$this->logger->log( "Failed to write human friendly result for test_run_id $test_run_id" );
			throw new RuntimeException( 'Failed to write test output to file.' );
		} else {
			$this->logger->log( "Wrote snapshot file: $snapshot_filepath" );
		}

		Context::$to_delete[] = $snapshot_filepath;

		$args = [
			__DIR__ . '/../vendor/bin/phpunit',
			__DIR__ . '/../tests/' . $this->generate_test_file_name( $test_run['type'] ),
			sprintf( '--filter=::%s$', $test_function_name ),
			'--testdox',
		];

		if ( Context::$action === 'update' ) {
			$args[] = '-d';
			$args[] = '--update-snapshots';
		}

		$this->logger->log( "Running PHPUnit: " . implode( ' ', $args ) );
		$phpunit_process = new Process( $args );
		$phpunit_process->setTimeout( 1200 );
		$phpunit_process->setIdleTimeout( 1200 );

		try {
			$phpunit_process->mustRun();
			$resultMessage = trim( $phpunit_process->getOutput() );
			$this->logger->log( "PHPUnit output for test_run_id $test_run_id: $resultMessage" );

			$success = true;
			$this->liveOutput->setTestCompleted( $test_run_id, $success, $result['test_results_manager_url'] ?? null, $test_run['non_json_output_file'] ?? null, $resultMessage );
		} catch ( ProcessFailedException $e ) {
			$this->failedTestsCount++;
			$resultMessage = $phpunit_process->getOutput();
			echo "The test {$test_function_name} failed.\n";
			$this->logger->log( "Test_run_id $test_run_id failed in PHPUnit: $resultMessage" );
			$this->liveOutput->setTestCompleted(
				$test_run_id,
				false,
				$result['test_results_manager_url'] ?? null,
				$test_run['non_json_output_file'] ?? null,
				$resultMessage
			);
		}

	}

	public function getFailedTestsCount(): int {
		return $this->failedTestsCount;
	}

	private function generate_test_file_name( string $test_type ) {
		return ucfirst( str_replace( '-', '', $test_type ) ) . 'Test.php';
	}
}
