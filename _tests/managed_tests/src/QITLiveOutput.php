<?php

class QITLiveOutput {
	private $testsState = []; // key: test_run_id
	private $startTime;
	private $isCI;

	public function __construct() {
		$this->startTime = microtime( true );
		$this->isCI      = getenv( 'CI' ) === 'true';
	}

	public function addTest( string $testId, string $displayName ) {
		$this->testsState[ $testId ] = [
			'displayName'       => $displayName,
			'status'            => '-', // Intermediate status
			'startTime'         => microtime( true ),
			'endTime'           => null,
			'reportUrl'         => null,
			'nonJsonOutputPath' => null,
			'resultMessage'     => null,
			'errors'            => [],
			'qit_raw_status'    => null, // Will hold raw QIT test status ('failed', 'passed', etc.)
		];
		$this->renderOutput();
	}

	public function setTestStatus( string $testId, string $status ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		if ( $status === 'running' || $status === 'dispatched' ) {
			$status = 'running...';
		}
		$this->testsState[ $testId ]['status'] = $status;
		$this->renderOutput();
	}

	// Called after snapshot verification is done
	public function setTestCompleted( string $testId, bool $success, ?string $reportUrl = null, ?string $nonJsonOutputPath = null, ?string $resultMessage = null ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		// Now simpler status wording
		$this->testsState[ $testId ]['status']            = $success ? 'completed success' : 'completed failed';
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

	public function setQitRawStatus( string $testId, string $qitStatus ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		$this->testsState[ $testId ]['qit_raw_status'] = $qitStatus;
	}

	public function renderOutput() {
		if ( ! $this->isCI ) {
			// Clear screen for better live view
			if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
				system( 'cls' );
			} else {
				system( 'clear' );
			}
		}

		echo "──────────────────────────────────────────────────────────────────────\n";
		echo " QIT Parallel Test Runner\n";
		echo " (Verbose logs are being written to mass-test.log)\n";
		echo "──────────────────────────────────────────────────────────────────────\n\n";

		$elapsed = intval( microtime( true ) - $this->startTime );
		$minutes = floor( $elapsed / 60 );
		$seconds = str_pad( $elapsed % 60, 2, '0', STR_PAD_LEFT );
		echo "Elapsed Time: [{$minutes}:{$seconds}]\n\n";

		if ( empty( $this->testsState ) ) {
			echo "No tests currently registered.\n";
			return;
		}

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

		echo "\n──────────────────────────────────────────────────────────────────────\n";
		echo "Summary Section:\n";

		$totalTests   = count( $this->testsState );
		$successCount = 0;
		$failCount    = 0;

		foreach ( $this->testsState as $testId => $testInfo ) {
			$finalLine = $this->summaryLine( $testInfo );
			echo $finalLine . "\n";

			if ( $testInfo['status'] === 'completed success' ) {
				$successCount ++;
			} elseif ( $testInfo['status'] === 'completed failed' ) {
				$failCount ++;
			}
		}

		$completedCount  = $successCount + $failCount;
		$inProgressCount = $totalTests - $completedCount;

		echo "\nTotal: {$totalTests} tests. Success: {$successCount}, Fail: {$failCount}, In Progress: {$inProgressCount}\n";

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

	public function printFinalSummary( int $phpUnitFailedCount ) {
		if ( ! $this->isCI ) {
			if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
				system( 'cls' );
			} else {
				system( 'clear' );
			}
		}

		echo "──────────────────────────────────────────────────────────────────────\n";
		echo " QIT Parallel Test Runner - Final Summary\n";
		echo "──────────────────────────────────────────────────────────────────────\n\n";

		echo "QIT Test Results (Raw):\n";
		foreach ( $this->testsState as $testId => $info ) {
			$raw    = $info['qit_raw_status'] ?? 'unknown';
			$label  = $info['displayName'];
			$report = $info['reportUrl'] ? "\n  Test Report: {$info['reportUrl']}" : '';
			// Just show 'success' or 'fail' raw
			echo "$label: completed ($raw)$report\n";
		}

		echo "\nNote: Raw QIT results do not determine the final outcome. Snapshot tests are the final check.\n\n";

		echo "PHPUnit Verification (Snapshots):\n";
		$finalFailures = 0;
		foreach ( $this->testsState as $testId => $info ) {
			$label = $info['displayName'];
			if ( $info['status'] === 'completed success' ) {
				echo "✔ $label: Snapshot matches\n";
			} else {
				echo "✖ $label: Snapshot did NOT match\n";
				$finalFailures ++;
			}

			if ( ! empty( $info['errors'] ) ) {
				foreach ( $info['errors'] as $err ) {
					echo "  Error: $err\n";
				}
			}
		}

		echo "\nAll snapshots have been verified.\n\n";

		if ( $phpUnitFailedCount > 0 || $finalFailures > 0 ) {
			echo "Some snapshots failed. Final outcome: ❌\n";
		} else {
			echo "All snapshots matched! Final outcome: ✅\n";
		}

		echo "\nFor more details, see mass-test.log.\n";
	}
}
