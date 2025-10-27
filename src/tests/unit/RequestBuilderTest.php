<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\AssertionFailedError;
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
