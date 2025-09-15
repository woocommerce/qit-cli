<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../MockHelper.php';

use QIT_CLI\RequestBuilder;
use QIT_CLI\Tests\Integration\MockHelper;
use QIT_CLI\Tests\Integration\QitHttpMockHelper;

class DirectRequestBuilderTest extends \PHPUnit\Framework\TestCase {
	use QitHttpMockHelper;

	protected function setUp(): void {
		parent::setUp();
		MockHelper::setup();
	}

	protected function tearDown(): void {
		MockHelper::cleanup();
		parent::tearDown();
	}

	public function test_direct_request_builder_mocking(): void {
		// Set environment variable to enable mocking
		$env_vars = MockHelper::env();
		foreach ($env_vars as $key => $value) {
			putenv("$key=$value");
		}

		$test_url = 'https://example.com/api/test';
		$mock_response = ['success' => true, 'data' => 'mocked'];
		
		// Set up mock
		MockHelper::mock($test_url, $mock_response);
		
		// Make request directly with RequestBuilder
		$request_builder = new RequestBuilder($test_url);
		$request_builder->with_method('POST');
		$request_builder->with_post_body(['test' => 'data']);
		
		$response = $request_builder->request();
		
		// Verify mock response was returned
		$this->assertEquals(json_encode($mock_response), $response);
		
		// Test chronological logging
		$all_requests = $this->allRequests();
		$this->assertCount(1, $all_requests, 'Should have one request in chronological log');
		
		$logged_request = $all_requests[0];
		$this->assertEquals($test_url, $logged_request['url']);
		$this->assertEquals(sha1($test_url), $logged_request['hash']);
		$this->assertArrayHasKey('body', $logged_request);
		
		// Test URL-specific access
		$specific_request = $this->requestByUrl($test_url);
		$this->assertNotNull($specific_request);
		$this->assertEquals($logged_request, $specific_request);
		
		// Test backward compatibility
		$last_request = MockHelper::lastRequest();
		$this->assertEquals($logged_request, $last_request);
		
		// Clean up environment
		foreach ($env_vars as $key => $value) {
			putenv($key);
		}
	}

	public function test_multiple_requests_chronological_order(): void {
		// Set environment variable to enable mocking
		$env_vars = MockHelper::env();
		foreach ($env_vars as $key => $value) {
			putenv("$key=$value");
		}

		$url1 = 'https://example.com/api/first';
		$url2 = 'https://example.com/api/second';
		$url3 = 'https://example.com/api/third';
		
		// Set up mocks
		MockHelper::mock($url1, ['order' => 1]);
		MockHelper::mock($url2, ['order' => 2]);
		MockHelper::mock($url3, ['order' => 3]);
		
		// Make requests in order
		$rb1 = new RequestBuilder($url1);
		$rb1->request();
		
		$rb2 = new RequestBuilder($url2);
		$rb2->request();
		
		$rb3 = new RequestBuilder($url3);
		$rb3->request();
		
		// Verify chronological order
		$all_requests = $this->allRequests();
		$this->assertCount(3, $all_requests);
		
		$this->assertEquals($url1, $all_requests[0]['url']);
		$this->assertEquals($url2, $all_requests[1]['url']);
		$this->assertEquals($url3, $all_requests[2]['url']);
		
		// Verify each has correct hash
		foreach ($all_requests as $request) {
			$this->assertEquals(sha1($request['url']), $request['hash']);
		}
		
		// Test URL-specific access for each
		$this->assertNotNull($this->requestByUrl($url1));
		$this->assertNotNull($this->requestByUrl($url2));
		$this->assertNotNull($this->requestByUrl($url3));
		
		// Last request should be the third one
		$last_request = MockHelper::lastRequest();
		$this->assertEquals($url3, $last_request['url']);
		
		// Clean up environment
		foreach ($env_vars as $key => $value) {
			putenv($key);
		}
	}

	public function test_missing_mock_exception(): void {
		// Set environment variable to enable mocking
		$env_vars = MockHelper::env();
		foreach ($env_vars as $key => $value) {
			putenv("$key=$value");
		}

		$test_url = 'https://example.com/api/no-mock';
		
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('No mock for: ' . $test_url);
		
		$request_builder = new RequestBuilder($test_url);
		$request_builder->request();
		
		// Clean up environment
		foreach ($env_vars as $key => $value) {
			putenv($key);
		}
	}
}