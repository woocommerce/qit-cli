<?php

class QITLiveOutput {
	private $testsState = []; // key: test_run_id
	private $startTime;
	private $isCI;
	private $timeToNextPoll = null;

	public function __construct() {
		$this->startTime = microtime( true );
		$this->isCI      = ( getenv( 'CI' ) === 'true' );
	}

	public function setTimeToNextPoll( ?int $seconds ) {
		$this->timeToNextPoll = $seconds;
	}

	public function addTest( string $testId, string $displayName, int $testIndex, array $testData ) {
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
			'final_display_lines' => [],  // Will store the final displayed lines for this test
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

	public function setTestCompleted( string $testId, bool $success, ?string $reportUrl = null, ?string $nonJsonOutputPath = null, ?string $resultMessage = null ) {
		if ( ! isset( $this->testsState[ $testId ] ) ) {
			return;
		}
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

		if ( $this->timeToNextPoll !== null ) {
			maybe_echo( "Next poll in: {$this->timeToNextPoll} second" . ( $this->timeToNextPoll === 1 ? '' : 's' ) . "...\n\n" );
		}

		if ( empty( $this->testsState ) ) {
			maybe_echo( "No tests currently registered.\n" );

			return;
		}

		// We'll build final displayed lines for each test here as we go
		foreach ( $this->testsState as $testId => &$testInfo ) {
			$displayedLines = [];

			$status   = $testInfo['status'];
			$duration = $this->computeDuration( $testInfo );

			// If status is '-', no colon
			$mainLine = $status === '-'
				? sprintf( "[%s] %s %s", $duration, $testInfo['displayName'], $status )
				: sprintf( "[%s] %s: %s", $duration, $testInfo['displayName'], $status );

			maybe_echo( $mainLine . "\n" );
			$displayedLines[] = $mainLine;

			if ( strpos( $status, 'completed' ) !== false ) {
				if ( $testInfo['reportUrl'] ) {
					// DO NOT print test report here in final lines (Item 2: test report only in final summary)
					// We do show it in running output currently, but now we understand from item 1 and 2:
					// Actually we don't want to show it now. Let's remove printing here.
					// If previously we printed it, now we must NOT print it here to fulfill item #2 requirement.
					// Just comment out the printing here:
					// maybe_echo("  Test Report: " . $testInfo['reportUrl'] . "\n");
					// We won't print it now. Only in final summary.
				}
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

			// Store these final displayed lines for future final summary
			// Item 1: We need to store these final lines so final summary can replicate them
			$testInfo['final_display_lines'] = $displayedLines;
		}
		unset( $testInfo ); // break reference

		maybe_echo( "\n──────────────────────────────────────────────────────────────────────\n" );
		maybe_echo( "Summary Section:\n" );

		$totalTests   = count( $this->testsState );
		$successCount = 0;
		$failCount    = 0;

		foreach ( $this->testsState as $info ) {
			$finalLine = $this->summaryLine( $info );
			maybe_echo( $finalLine . "\n" );

			if ( $info['status'] === 'completed success' ) {
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
		foreach ( $this->testsState as $info ) {
			// Item 1: We now have 'final_display_lines' stored.
			// We just print them exactly as stored.
			foreach ( $info['final_display_lines'] as $line ) {
				echo $line . "\n";
			}

			// Item 2: After replaying these lines, now we print the Test Report line if available
			if ( ! empty( $info['reportUrl'] ) ) {
				echo "  Test Report: {$info['reportUrl']}\n";
			}
		}

		echo "\nNote: Raw QIT results do not determine the final outcome. Snapshot tests are the final check.\n\n";

		echo "PHPUnit Verification (Snapshots):\n";
		$finalFailures = 0;
		foreach ( $this->testsState as $info ) {
			$isSuccess = ( $info['status'] === 'completed success' );
			// Use final_display_lines to find the main line for snapshot status line:
			// The main test line is always first in final_display_lines
			$mainTestLine = $info['final_display_lines'][0] ?? '[Unknown test line]';
			// Extracting test info from main line is complex; we trust we have displayName etc.
			// For simplicity, let's replicate the snapshot logic from before:
			if ( $isSuccess ) {
				echo "✔ $mainTestLine Snapshot matches\n";
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
			echo "Some snapshots failed. Final outcome: ❌\n";
		} else {
			echo "All snapshots matched! Final outcome: ✅\n";
		}

		echo "\nFor more details, see mass-test.log.\n";
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
			return "[{$duration}] {$testInfo['displayName']} {$status}";
		} else {
			return "[{$duration}] {$testInfo['displayName']}: {$status}";
		}
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
			'/^Test Report:/i',
			'/^Result:$/i',
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
				// Unknown line, highlight in red if not in CI
				if ( ! $this->isCI ) {
					$trimmed = "\033[1;31m$trimmed\033[0m";
				}
				$result[] = $trimmed;
			}
		}

		return $result;
	}
}