<?php

namespace QIT_CLI_Tests;

use QIT_CLI\RequestBuilder;
use PHPUnit\Framework\TestCase;

class RequestBuilderTest extends TestCase {
	/** @var RequestBuilder $this ->sut */
	protected $sut;

	protected function setUp(): void {
		parent::setUp();
		$this->sut = new class extends RequestBuilder {
			public function wait_after_429( string $headers, int $max_wait = 60 ): int {
				return parent::wait_after_429( $headers, $max_wait );
			}
		};
	}

	public function test_retry_after_seconds() {
		$headers = "Retry-After: 59\r\nOther-Header: value";

		$this->assertEquals( 59, $this->sut->wait_after_429( $headers ) );
	}

	public function test_retry_after_http_date() {
		$dateTime = new \DateTime( '+120 seconds' );
		$httpDate = $dateTime->format( \DateTimeInterface::RFC7231 );
		$headers  = "Retry-After: $httpDate\r\nOther-Header: value";

		// Since time will pass between the creation of the date and this calculation,
		// allow a small margin in the assertion
		$expected_delay = $dateTime->getTimestamp() - time();
		$this->assertEqualsWithDelta( $expected_delay, $this->sut->wait_after_429( $headers, 130 ), 5 );
	}

	public function test_no_retry_after_header() {
		$headers = "Other-Header: value";

		$this->assertEquals( 5, $this->sut->wait_after_429( $headers ) );
	}

	public function test_invalid_retry_after_header() {
		$headers = "Retry-After: invalid\r\nOther-Header: value";

		$this->assertEquals( 5, $this->sut->wait_after_429( $headers ) );
	}

	public function test_exponential_backoff() {
		$headers = "Retry-After: invalid\r\nOther-Header: value";

		$this->assertEquals( 5, $this->sut->wait_after_429( $headers ) );
		$this->assertEquals( 10, $this->sut->wait_after_429( $headers ) );
		$this->assertEquals( 20, $this->sut->wait_after_429( $headers ) );
		$this->assertEquals( 40, $this->sut->wait_after_429( $headers ) );
		$this->assertEquals( 80, $this->sut->wait_after_429( $headers, 300 ) );
		$this->assertEquals( 160, $this->sut->wait_after_429( $headers, 300 ) );
	}
}
