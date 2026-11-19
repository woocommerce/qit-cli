<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\RemoteTestRunner;

/**
 * Coverage for QIT-991: a failed status poll must back off with jitter instead of
 * retrying once per second (which produced the polling bursts the edge limiter 429s on).
 */
class RemoteTestRunnerPollTest extends TestCase {
	private function poll_retry_delay( int $failures, int $poll_interval ): int {
		$ref = new \ReflectionMethod( RemoteTestRunner::class, 'poll_retry_delay' );
		$ref->setAccessible( true );

		return $ref->invoke( null, $failures, $poll_interval );
	}

	public function test_first_failure_backs_off_at_least_two_seconds(): void {
		// The old behaviour was a flat sleep(1); the floor is now 2s (+ jitter).
		$delay = $this->poll_retry_delay( 1, 15 );

		$this->assertGreaterThanOrEqual( 2, $delay );
	}

	public function test_backoff_grows_with_consecutive_failures(): void {
		// Base (pre-jitter) delay for failures 1..3 is 2, 4, 8 — so even with up to 5s of
		// jitter the third failure cannot be quicker than the first's floor.
		$this->assertGreaterThanOrEqual( 2, $this->poll_retry_delay( 1, 30 ) );
		$this->assertGreaterThanOrEqual( 4, $this->poll_retry_delay( 2, 30 ) );
		$this->assertGreaterThanOrEqual( 8, $this->poll_retry_delay( 3, 30 ) );
	}

	public function test_backoff_never_exceeds_poll_interval_plus_jitter(): void {
		// Many failures cap the base delay at the poll interval; only jitter is added on top.
		$poll_interval = 15;
		for ( $failures = 1; $failures <= 10; $failures++ ) {
			$delay = $this->poll_retry_delay( $failures, $poll_interval );
			$this->assertLessThanOrEqual( $poll_interval + 5, $delay );
		}
	}
}
