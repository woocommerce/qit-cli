<?php

class QITLiveOutput {
	private $testsState = []; // key: test_run_id
	private $startTime;
	private $isCI;
	private $timeToNextPoll = null;

	public function __construct() {
		$this->startTime = microtime( true );
		$this->isCI      = getenv( 'CI' ) === 'true';
	}

	public function setTimeToNextPoll( ?int $seconds ) {
		$this->timeToNextPoll = $seconds;
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
			if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
				system( 'cls' );
			} else {
				system( 'clear' );
			}
		}

		maybe_echo( "──────────────────────────────────────────────────────────────────────\n" );
		maybe_echo( " QIT Parallel Test Runner\n" );
		maybe_echo( " (Verbose logs are being written to mass-test.log)\n" );
		maybe_echo( "──────────────────────────────────────────────────────────────────────\n\n" );

		$elapsed = intval( microtime( true ) - $this->startTime );
		$minutes = floor( $elapsed / 60 );
		$seconds = str_pad( $elapsed % 60, 2, '0', STR_PAD_LEFT );
		maybe_echo( "Elapsed Time: [{$minutes}:{$seconds}]\n\n" );

		// Display next poll info
		if ( $this->timeToNextPoll !== null ) {
			maybe_echo( "Next poll in: {$this->timeToNextPoll} second" . ( $this->timeToNextPoll === 1 ? '' : 's' ) . "...\n\n" );
		}

		if ( empty( $this->testsState ) ) {
			maybe_echo( "No tests currently registered.\n" );

			return;
		}

		foreach ( $this->testsState as $testId => $testInfo ) {
			$status   = $testInfo['status'];
			$duration = $this->computeDuration( $testInfo );
			$line     = sprintf( "[%s] %s: %s", $duration, $testInfo['displayName'], $status );
			maybe_echo( $line . "\n" );

			if ( strpos( $status, 'completed' ) !== false ) {
				if ( $testInfo['reportUrl'] ) {
					maybe_echo( "  Test Report: " . $testInfo['reportUrl'] . "\n" );
				}
				if ( $testInfo['resultMessage'] ) {
					maybe_echo( "  Result: " . $testInfo['resultMessage'] . "\n" );
				}
			}

			if ( ! empty( $testInfo['errors'] ) ) {
				foreach ( $testInfo['errors'] as $err ) {
					maybe_echo( "  Error: $err\n" );
				}
			}
		}

		maybe_echo( "\n──────────────────────────────────────────────────────────────────────\n" );
		maybe_echo( "Summary Section:\n" );

		$totalTests   = count( $this->testsState );
		$successCount = 0;
		$failCount    = 0;

		foreach ( $this->testsState as $testId => $testInfo ) {
			$finalLine = $this->summaryLine( $testInfo );
			maybe_echo( $finalLine . "\n" );

			if ( $testInfo['status'] === 'completed success' ) {
				$successCount ++;
			} elseif ( $testInfo['status'] === 'completed failed' ) {
				$failCount ++;
			}
		}

		$completedCount  = $successCount + $failCount;
		$inProgressCount = $totalTests - $completedCount;

		maybe_echo( "\nTotal: {$totalTests} tests. Success: {$successCount}, Fail: {$failCount}, In Progress: {$inProgressCount}\n" );

		if ( $inProgressCount === 0 ) {
			if ( $failCount > 0 ) {
				maybe_echo( "Some tests have failed. Please check the errors above.\n" );
			} else {
				maybe_echo( "All tests completed successfully!\n" );
			}
		} else {
			maybe_echo( "Tests still in progress...\n" );
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

		maybe_echo( "──────────────────────────────────────────────────────────────────────\n" );
		maybe_echo( " QIT Parallel Test Runner - Final Summary\n" );
		maybe_echo( "──────────────────────────────────────────────────────────────────────\n\n" );

		maybe_echo( "QIT Test Results (Raw):\n" );
		foreach ( $this->testsState as $testId => $info ) {
			$raw    = $info['qit_raw_status'] ?? 'unknown';
			$label  = $info['displayName'];
			$report = $info['reportUrl'] ? "\n  Test Report: {$info['reportUrl']}" : '';
			// Just show 'success' or 'fail' raw
			maybe_echo( "$label: completed ($raw)$report\n" );
		}

		maybe_echo( "\nNote: Raw QIT results do not determine the final outcome. Snapshot tests are the final check.\n\n" );

		maybe_echo( "PHPUnit Verification (Snapshots):\n" );
		$finalFailures = 0;
		foreach ( $this->testsState as $testId => $info ) {
			$label = $info['displayName'];
			if ( $info['status'] === 'completed success' ) {
				maybe_echo( "✔ $label: Snapshot matches\n" );
			} else {
				maybe_echo( "✖ $label: Snapshot did NOT match\n" );
				$finalFailures ++;
			}

			if ( ! empty( $info['errors'] ) ) {
				foreach ( $info['errors'] as $err ) {
					maybe_echo( "  Error: $err\n" );
				}
			}
		}

		maybe_echo( "\nAll snapshots have been verified.\n\n" );

		if ( $phpUnitFailedCount > 0 || $finalFailures > 0 ) {
			maybe_echo( "Some snapshots failed. Final outcome: ❌\n" );
		} else {
			maybe_echo( "All snapshots matched! Final outcome: ✅\n" );
		}

		maybe_echo( "\nFor more details, see mass-test.log.\n" );
	}
}
