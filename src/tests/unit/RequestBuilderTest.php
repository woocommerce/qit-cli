<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\AssertionFailedError;
use QIT_CLI\App;
use QIT_CLI\RequestBuilder;
use PHPUnit\Framework\TestCase;

class RequestBuilderTest extends TestCase {
	/** @var RequestBuilder $this ->sut */
	protected $sut;

	protected function setUp(): void {
		parent::setUp();
		$this->sut = new class extends RequestBuilder {
			public $retry_429 = 5;

			public function wait_after_429( string $headers, int $max_wait = 60 ): int {
				return parent::wait_after_429( $headers, $max_wait );
			}

			public function parse_retry_after_header_public( string $headers ): ?int {
				return parent::parse_retry_after_header( $headers );
			}

			public function calculate_retry_delay_public( ?int $retry_after_header, int $attempt, int $max_wait = 180 ): int {
				return parent::calculate_retry_delay( $retry_after_header, $attempt, $max_wait );
			}
		};
	}

	protected function assertRetryDelayWithinRange( $expected, $actual, $delta ) {
		if ( $actual < $expected ) {
			$this->fail( "Expected value is $expected, actual value is $actual, which is less than expected." );
		} elseif ( $actual > $expected + $delta ) {
			$this->fail( "Expected value is $expected, actual value is $actual, which is greater than expected + delta ($delta)." );
		} else {
			// If the actual value is within the acceptable range, explicitly assert true.
			$this->assertTrue( true );
		}
	}

	public function test_retry_after_seconds() {
		$headers = "Retry-After: 59\r\nOther-Header: value";

		$this->assertRetryDelayWithinRange( 59, $this->sut->wait_after_429( $headers ), 5 );
	}

	public function test_retry_after_http_date() {
		$dateTime = new \DateTime( '+120 seconds' );
		$httpDate = $dateTime->format( \DateTimeInterface::RFC7231 );
		$headers  = "Retry-After: $httpDate\r\nOther-Header: value";

		// Since time will pass between the creation of the date and this calculation,
		// allow a small margin in the assertion
		$expected_delay = $dateTime->getTimestamp() - time();
		$this->assertRetryDelayWithinRange( $expected_delay, $this->sut->wait_after_429( $headers, 130 ), 5 );
	}

	public function test_no_retry_after_header() {
		$headers = "Other-Header: value";

		$this->assertRetryDelayWithinRange( 5, $this->sut->wait_after_429( $headers ), 5 );
	}

	public function test_invalid_retry_after_header() {
		$headers = "Retry-After: invalid\r\nOther-Header: value";

		$this->assertRetryDelayWithinRange( 5, $this->sut->wait_after_429( $headers ), 5 );
	}

	public function test_exponential_backoff() {
		$headers = "Retry-After: invalid\r\nOther-Header: value";

		$this->assertRetryDelayWithinRange( 5, $this->sut->wait_after_429( $headers ), 5 );
		// Mimick integration-level test.
		$this->sut->retry_429 --;
		$this->assertRetryDelayWithinRange( 10, $this->sut->wait_after_429( $headers ), 5 );
		$this->sut->retry_429 --;
		$this->assertRetryDelayWithinRange( 20, $this->sut->wait_after_429( $headers ), 5 );
		$this->sut->retry_429 --;
		$this->assertRetryDelayWithinRange( 40, $this->sut->wait_after_429( $headers ), 5 );
		$this->sut->retry_429 --;
		$this->assertRetryDelayWithinRange( 80, $this->sut->wait_after_429( $headers, 200 ), 5 );
		$this->sut->retry_429 --;
		$this->assertRetryDelayWithinRange( 160, $this->sut->wait_after_429( $headers, 200 ), 5 );
	}

	public function test_parse_retry_after_numeric() {
		$this->assertSame( 120, $this->sut->parse_retry_after_header_public( "Retry-After: 120\r\nOther: x" ) );
	}

	public function test_parse_retry_after_is_case_insensitive() {
		// HTTP/2 lower-cases header names; we must still find it.
		$this->assertSame( 30, $this->sut->parse_retry_after_header_public( "retry-after: 30\r\nOther: x" ) );
		$this->assertSame( 30, $this->sut->parse_retry_after_header_public( "RETRY-AFTER: 30\r\nOther: x" ) );
	}

	public function test_parse_retry_after_absent_returns_null() {
		$this->assertNull( $this->sut->parse_retry_after_header_public( "Other-Header: value" ) );
	}

	public function test_parse_retry_after_invalid_returns_null() {
		$this->assertNull( $this->sut->parse_retry_after_header_public( "Retry-After: not-a-date\r\nOther: x" ) );
	}

	public function test_parse_retry_after_past_date_clamps_to_zero() {
		$past    = ( new \DateTime( '-2 hours' ) )->format( \DateTimeInterface::RFC7231 );
		$headers = "Retry-After: $past\r\nOther: x";

		$this->assertSame( 0, $this->sut->parse_retry_after_header_public( $headers ) );
	}

	public function test_calculate_retry_delay_honors_header_within_cap() {
		// Header value is returned (plus 0-5s jitter) when under the cap.
		$this->assertRetryDelayWithinRange( 90, $this->sut->calculate_retry_delay_public( 90, 0, 180 ), 5 );
	}

	public function test_calculate_retry_delay_caps_long_header() {
		// A large header value is capped at max_wait (plus jitter).
		$this->assertRetryDelayWithinRange( 180, $this->sut->calculate_retry_delay_public( 3600, 0, 180 ), 5 );
	}

	public function test_calculate_retry_delay_backoff_when_no_header() {
		// 0-based attempts: 5, 10, 20, 40 ...
		$this->assertRetryDelayWithinRange( 5, $this->sut->calculate_retry_delay_public( null, 0, 180 ), 5 );
		$this->assertRetryDelayWithinRange( 10, $this->sut->calculate_retry_delay_public( null, 1, 180 ), 5 );
		$this->assertRetryDelayWithinRange( 20, $this->sut->calculate_retry_delay_public( null, 2, 180 ), 5 );
		$this->assertRetryDelayWithinRange( 40, $this->sut->calculate_retry_delay_public( null, 3, 180 ), 5 );
	}

	public function test_retry_429_budget_resets_each_request() {
		// A reused builder (e.g. Upload.php sends every chunk through one instance) must not
		// carry an exhausted 429 budget into the next request. Simulate a prior request having
		// burned the whole budget, then assert the next request() starts fresh.
		$url = 'https://example.com/reset-budget-' . __FUNCTION__;
		App::setVar( 'mock_' . $url, '{"ok":true}' );

		$sut = new class( $url ) extends RequestBuilder {
			public $retry_429 = 0;
		};

		$sut->request();

		$this->assertSame( 5, $sut->retry_429 );

		App::offsetUnset( 'mock_' . $url );
	}

	//
	// Some tests for our custom assertion.
	//
	public function testExactValue() {
		$this->assertRetryDelayWithinRange( 10, 10, 1 ); // Delta of 1
	}

	public function testWithinDelta() {
		$this->assertRetryDelayWithinRange( 10, 11, 1 ); // Within delta of 1
	}

	public function testExceedsDelta() {
		$this->expectException( AssertionFailedError::class );
		$this->assertRetryDelayWithinRange( 10, 12, 1 ); // Exceeds delta of 1
	}

	public function testBelowExpected() {
		$this->expectException( AssertionFailedError::class );
		$this->assertRetryDelayWithinRange( 10, 9, 1 ); // Below expected
	}
}
