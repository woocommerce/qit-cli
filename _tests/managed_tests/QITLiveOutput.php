<?php

class QITLiveOutput {
	private $testsState = []; // key: test_run_id, value: associative array of test data
	private $startTime;
	private $isCI;

	public function __construct() {
		$this->startTime = microtime( true );
		$this->isCI      = getenv( 'CI' ) === 'true';
	}

	public function addTest( string $testId, string $displayName ) {
		$this->testsState[ $testId ] = [
			'displayName'       => $displayName,
			'status'            => '-',
			'startTime'         => microtime( true ),
			'endTime'           => null,
			'reportUrl'         => null,
			'nonJsonOutputPath' => null,
			'resultMessage'     => null,
			'errors'            => [],
		];
		$this->renderOutput();
	}

	public function setTestStatus( string $testId, string $status ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		$this->testsState[ $testId ]['status'] = $status;
		// We don't assume anything about the status here, just store it.
		$this->renderOutput();
	}

	public function setTestCompleted( string $testId, bool $success, ?string $reportUrl = null, ?string $nonJsonOutputPath = null, ?string $resultMessage = null ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		$this->testsState[ $testId ]['status']            = $success ? 'completed (success)' : 'completed (failed)';
		$this->testsState[ $testId ]['endTime']           = microtime( true );
		$this->testsState[ $testId ]['reportUrl']         = $reportUrl;
		$this->testsState[ $testId ]['nonJsonOutputPath'] = $nonJsonOutputPath;
		$this->testsState[ $testId ]['resultMessage']     = $resultMessage;
		$this->renderOutput();
	}

	public function addTestError( string $testId, string $errorMessage ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		$this->testsState[ $testId ]['errors'][] = $errorMessage;
		$this->renderOutput();
	}

	public function renderOutput() {
		if ( ! $this->isCI ) {
			system( 'clear' );
		}

		echo "──────────────────────────────────────────────────────────────────────\n";
		echo " QIT Parallel Test Runner\n";
		echo "──────────────────────────────────────────────────────────────────────\n\n";

		$elapsed = intval( microtime( true ) - $this->startTime );
		$minutes = floor( $elapsed / 60 );
		$seconds = str_pad( $elapsed % 60, 2, '0', STR_PAD_LEFT );
		echo "Elapsed Time: [{$minutes}:{$seconds}]\n\n";

		if ( empty( $this->testsState ) ) {
			echo "No tests currently registered.\n";

			return;
		}

		// Display each test
		foreach ( $this->testsState as $testId => $testInfo ) {
			$status   = $testInfo['status'];
			$duration = $this->computeDuration( $testInfo );
			$line     = sprintf( "[%s] %s: %s", $duration, $testInfo['displayName'], $status );
			echo $line . "\n";

			if ( strpos( $status, 'completed' ) !== false ) {
				if ( $testInfo['reportUrl'] ) {
					echo "  Test Report: " . $testInfo['reportUrl'] . "\n";
				}
				if ( $testInfo['resultMessage'] ) {
					echo "  Result: " . $testInfo['resultMessage'] . "\n";
				}
			}

			if ( ! empty( $testInfo['errors'] ) ) {
				foreach ( $testInfo['errors'] as $err ) {
					echo "  Error: $err\n";
				}
			}
		}

		// Summary Section
		echo "\n──────────────────────────────────────────────────────────────────────\n";
		echo "Summary Section:\n";

		$totalTests   = count( $this->testsState );
		$successCount = 0;
		$failCount    = 0;

		foreach ( $this->testsState as $testId => $testInfo ) {
			$finalLine = $this->summaryLine( $testInfo );
			echo $finalLine . "\n";

			// Count final outcomes
			if ( $testInfo['status'] === 'completed (success)' ) {
				$successCount ++;
			} elseif ( $testInfo['status'] === 'completed (failed)' ) {
				$failCount ++;
			}
		}

		$completedCount  = $successCount + $failCount;
		$inProgressCount = $totalTests - $completedCount;

		echo "\nTotal: {$totalTests} tests. Success: {$successCount}, Fail: {$failCount}, In Progress: {$inProgressCount}\n";

		// If all are completed
		if ( $inProgressCount === 0 ) {
			if ( $failCount > 0 ) {
				echo "Some tests have failed. Please check the errors above.\n";
			} else {
				echo "All tests completed successfully!\n";
			}
		} else {
			echo "Tests still in progress...\n";
		}
	}

	private function computeDuration( array $testInfo ): string {
		$start   = $testInfo['startTime'] ?? $this->startTime;
		$end     = $testInfo['endTime'] ?? microtime( true );
		$elapsed = max( 0, intval( $end - $start ) );
		$minutes = floor( $elapsed / 60 );
		$seconds = str_pad( $elapsed % 60, 2, '0', STR_PAD_LEFT );

		return "{$minutes}:{$seconds}";
	}

	private function summaryLine( array $testInfo ): string {
		$duration = $this->computeDuration( $testInfo );

		return sprintf( "[%s] %s: %s", $duration, $testInfo['displayName'], $testInfo['status'] );
	}
}
