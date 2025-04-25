<?php

class QITLiveOutput {
	private $testsState = []; // key: test_run_id
	private $startTime;
	private $isCI;
	private $timeToNextPoll = null;
	private $isUpdateAction = false; // Detect update mode

	public function __construct() {
		$this->startTime = microtime( true );
		$this->isCI      = ( getenv( 'CI' ) === 'true' );

		// Detect if we are in update mode
		if ( function_exists( 'getenv' ) && getenv( 'QIT_ACTION' ) === 'update' ) {
			$this->isUpdateAction = true;
		} elseif ( class_exists( 'Context' ) && isset( Context::$action ) && Context::$action === 'update' ) {
			$this->isUpdateAction = true;
		}
	}

	public function setTimeToNextPoll( ?int $seconds ) {
		$this->timeToNextPoll = $seconds;
	}

	public function addTest( string $testId, string $displayName, int $testIndex, array $testData ) {
		// Ensure no trailing colon or space
		$displayName = rtrim( $displayName, ": " );

		$this->testsState[ $testId ] = [
			'displayName'         => $displayName,
			'status'              => '-',
			'startTime'           => microtime( true ),
			'endTime'             => null,
			'reportUrl'           => null,
			'nonJsonOutputPath'   => null,
			'resultMessage'       => null,
			'errors'              => [],
			'qit_raw_status'      => null,
			'test_index'          => $testIndex,
			'test_data'           => $testData,
			'final_display_lines' => [],
		];

		// If test_results_manager_url known at addTest time, set it
		if ( ! empty( $testData['test_results_manager_url'] ) ) {
			$this->testsState[ $testId ]['reportUrl'] = $testData['test_results_manager_url'];
		}

		$this->renderOutput();
	}

	public function updateTestData( string $testId, array $newData ) {
		if ( isset( $this->testsState[ $testId ] ) ) {
			$this->testsState[ $testId ]['test_data'] = array_merge( $this->testsState[ $testId ]['test_data'], $newData );
			if ( ! empty( $newData['test_results_manager_url'] ) ) {
				$this->testsState[ $testId ]['reportUrl'] = $newData['test_results_manager_url'];
			}
			$this->renderOutput();
		}
	}

	public function setTestStatus( string $testId, string $status ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		if ( $status === 'running' || $status === 'dispatched' ) {
			$status = 'running...';
		} elseif ( $status === '-' ) {
			$status = 'just started';
		}
		$this->testsState[ $testId ]['status'] = $status;
		$this->renderOutput();
	}

	public function setTestCompleted( string $testId, bool $success, ?string $reportUrl = null, ?string $nonJsonOutputPath = null, ?string $resultMessage = null ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
		$this->testsState[ $testId ]['status']  = $success
			? ( $this->isUpdateAction ? 'completed updated' : 'completed success' )
			: 'completed failed';
		$this->testsState[ $testId ]['endTime'] = microtime( true );

		if ( $reportUrl ) {
			$this->testsState[ $testId ]['reportUrl'] = $reportUrl;
		} elseif ( empty( $this->testsState[ $testId ]['reportUrl'] ) && ! empty( $this->testsState[ $testId ]['test_data']['test_results_manager_url'] ) ) {
			$this->testsState[ $testId ]['reportUrl'] = $this->testsState[ $testId ]['test_data']['test_results_manager_url'];
		}

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
		$this->renderOutput();
	}

	public function renderOutput() {
		clear_output();

		maybe_echo( "──────────────────────────────────────────────────────────────────────\n" );
		maybe_echo( " QIT Self Test Runner\n" );
		maybe_echo( " (Verbose logs are being written to last-self-test.log)\n" );
		maybe_echo( "──────────────────────────────────────────────────────────────────────\n\n" );

		$elapsed = intval( microtime( true ) - $this->startTime );
		$minutes = floor( $elapsed / 60 );
		$seconds = str_pad( $elapsed % 60, 2, '0', STR_PAD_LEFT );
		maybe_echo( "Elapsed Time: [{$minutes}:{$seconds}]\n\n" );

		if ( $this->timeToNextPoll !== null ) {
			maybe_echo( "Next poll in: {$this->timeToNextPoll} second" . ( $this->timeToNextPoll === 1 ? '' : 's' ) . "...\n\n" );
		}

		if ( empty( $this->testsState ) ) {
			maybe_echo( "No tests currently registered.\n" );

			return;
		}

		foreach ( $this->testsState as $testId => &$testInfo ) {
			$displayedLines = [];

			if ( empty( $testInfo['reportUrl'] ) && ! empty( $testInfo['test_data']['test_results_manager_url'] ) ) {
				$testInfo['reportUrl'] = $testInfo['test_data']['test_results_manager_url'];
			}

			$status      = $testInfo['status'];
			$duration    = $this->computeDuration( $testInfo );
			$displayName = rtrim( $testInfo['displayName'], ': ' );

			if ( $status === '-' ) {
				$status = 'just started';
			}

			// Use a colon only if not "just started"
			$separator = ( $status === 'just started' ) ? ' ' : ': ';
			$mainLine  = "[{$duration}] {$displayName}{$separator}{$status}";

			maybe_echo( $mainLine . "\n" );
			$displayedLines[] = $mainLine;

			if ( ! empty( $testInfo['reportUrl'] ) ) {
				$reportLine = "  Test Report: " . $testInfo['reportUrl'];
				maybe_echo( $reportLine . "\n" );
				$displayedLines[] = $reportLine;
			}

			if ( strpos( $status, 'completed' ) !== false ) {
				if ( ! empty( $testInfo['resultMessage'] ) ) {
					$isFailure = ( strpos( $status, 'failed' ) !== false );
					if ( $isFailure ) {
						maybe_echo( "  Result:\n" );
						$displayedLines[] = "  Result:";
						$indented         = $this->indentedOutputLines( $testInfo['resultMessage'] );
						foreach ( $indented as $l ) {
							maybe_echo( $l . "\n" );
							$displayedLines[] = $l;
						}
					} else {
						$filtered = $this->filterSuccessOutput( $testInfo['resultMessage'] );
						if ( ! empty( $filtered ) ) {
							maybe_echo( "  Result:\n" );
							$displayedLines[] = "  Result:";
							foreach ( $filtered as $fline ) {
								$lineToPrint = "    $fline";
								maybe_echo( $lineToPrint . "\n" );
								$displayedLines[] = $lineToPrint;
							}
						}
					}
				}
			}

			if ( ! empty( $testInfo['errors'] ) ) {
				foreach ( $testInfo['errors'] as $err ) {
					$errorLine = "  Error: $err";
					maybe_echo( $errorLine . "\n" );
					$displayedLines[] = $errorLine;
				}
			}

			$testInfo['final_display_lines'] = $displayedLines;
		}
		unset( $testInfo );

		maybe_echo( "\n──────────────────────────────────────────────────────────────────────\n" );
		maybe_echo( "Summary Section:\n" );

		$totalTests   = count( $this->testsState );
		$successCount = 0;
		$failCount    = 0;

		foreach ( $this->testsState as $info ) {
			$finalLine = $this->summaryLine( $info );
			maybe_echo( $finalLine . "\n" );

			if ( $info['status'] === 'completed success' || $info['status'] === 'completed updated' ) {
				$successCount ++;
			} elseif ( $info['status'] === 'completed failed' ) {
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

	public function printFinalSummary( int $phpUnitFailedCount ) {
		clear_output();

		echo "──────────────────────────────────────────────────────────────────────\n";
		echo " QIT Self Test Runner - Final Summary\n";
		echo "──────────────────────────────────────────────────────────────────────\n\n";

		echo "QIT Test Results (Raw):\n";
		foreach ( $this->testsState as $info ) {
			foreach ( $info['final_display_lines'] as $line ) {
				echo $line . "\n";
			}
		}

		echo "\nNote: Raw QIT results do not determine the final outcome. Snapshot tests are the final check.\n\n";

		echo "PHPUnit Verification (Snapshots):\n";
		$finalFailures = 0;
		foreach ( $this->testsState as $info ) {
			$isSuccess    = ( $info['status'] === 'completed success' || $info['status'] === 'completed updated' );
			$mainTestLine = $info['final_display_lines'][0] ?? '[Unknown test line]';

			if ( $isSuccess ) {
				$snapshotLine = $this->isUpdateAction ? "Snapshot updated (please review)" : "Snapshot matches";
				echo "✔ $mainTestLine $snapshotLine\n";
			} else {
				echo "✖ $mainTestLine Snapshot did NOT match\n";
				$finalFailures ++;
				if ( ! empty( $info['resultMessage'] ) ) {
					$this->printIndentedOutput( $info['resultMessage'] );
				} else {
					echo "  (No additional output available)\n";
				}
			}

			if ( ! empty( $info['errors'] ) ) {
				foreach ( $info['errors'] as $err ) {
					echo "  Error: $err\n";
				}
			}
		}

		echo "\nAll snapshots have been verified.\n\n";

		if ( $phpUnitFailedCount > 0 || $finalFailures > 0 ) {
			echo "Some snapshots still failed. Final outcome: ❌\n";
		} else {
			if ( $this->isUpdateAction ) {
				echo "Snapshots have been updated. Please review the updated snapshots to ensure they match expectations. Final outcome: ⚠\n";
			} else {
				echo "All snapshots matched! Final outcome: ✅\n";
			}
		}

		echo "\nFor more details, see last-self-test.log.\n";
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
		$status   = $testInfo['status'];
		if ( $status === '-' ) {
			$status = 'just started';
		}
		$displayName = rtrim( $testInfo['displayName'], ': ' );

		$separator = ( $status === 'just started' ) ? ' ' : ': ';

		return "[{$duration}] {$displayName}{$separator}{$status}";
	}

	private function printIndentedOutput( string $output ) {
		$lines = explode( "\n", $output );
		foreach ( $lines as $line ) {
			echo "    $line\n";
		}
	}

	private function indentedOutputLines( string $output ): array {
		$result = [];
		$lines  = explode( "\n", $output );
		foreach ( $lines as $line ) {
			$result[] = "    $line";
		}

		return $result;
	}

	private function successIgnorePatterns(): array {
		return [
			'/^Result:$/i',
			'/^[A-Za-z0-9_-]+ \(QITE2E\\\\[A-Za-z0-9_\\\\-]+\)$/i',
			'/^PHPUnit \d+\.\d+\.\d+ by Sebastian Bergmann and contributors\./i',
			'/^Runtime:/i',
			'/^Wooapi \(QITE2E\\\\Wooapi\)/i',
			'/^\s*✔ /i',
			'/^Normalizing debug_log\.count/i',
			'/^Time: \d+ ms, Memory: \d+\.\d+ MB/i',
			'/^OK \(\d+ test, \d+ assertions\)/i',
			'/^OK, but incomplete, skipped, or risky tests!/i',
			'/^Tests: \d+, Assertions: \d+, .*$/i',
		];
	}

	private function filterSuccessOutput( string $output ): array {
		$lines  = explode( "\n", $output );
		$result = [];

		$knownPatterns = $this->successIgnorePatterns();

		foreach ( $lines as $line ) {
			$trimmed = rtrim( $line );
			if ( $trimmed === '' ) {
				continue; // remove empty lines
			}
			$known = false;
			foreach ( $knownPatterns as $pattern ) {
				if ( preg_match( $pattern, $trimmed ) ) {
					$known = true;
					break;
				}
			}
			if ( ! $known ) {
				if ( ! $this->isCI ) {
					$trimmed = "\033[1;31m$trimmed\033[0m";
				}
				$result[] = $trimmed;
			}
		}

		return $result;
	}
}
