<?php

use QIT_CLI\RequestBuilder;

class RequestBuilderMockingTest extends \QIT_CLI_Tests\QITTestCase {

	private $mock_dir;

	public function setUp(): void {
		parent::setUp();
		
		// Create a temporary directory for mocks
		$this->mock_dir = sys_get_temp_dir() . '/qit-mock-test-' . uniqid();
		mkdir( $this->mock_dir );
		
		// Set the environment variable to enable mocking
		putenv( 'QIT_MOCK_DIR=' . $this->mock_dir );
	}

	protected function tearDown(): void {
		// Clean up
		if ( is_dir( $this->mock_dir ) ) {
			$files = glob( $this->mock_dir . '/*' );
			if ( $files ) {
				array_map( 'unlink', $files );
			}
			rmdir( $this->mock_dir );
		}
		
		putenv( 'QIT_MOCK_DIR' );
		parent::tearDown();
	}

	public function test_file_based_http_mocking(): void {
		$test_url = 'https://example.com/api/test';
		$mock_response = [ 'success' => true, 'data' => 'mocked' ];
		
		// Create mock file
		$mock_file = $this->mock_dir . '/' . md5( $test_url ) . '.json';
		file_put_contents( $mock_file, json_encode( $mock_response ) );
		
		// Make request using RequestBuilder
		$request_builder = new RequestBuilder( $test_url );
		$request_builder->with_method( 'POST' );
		$request_builder->with_post_body( [ 'test' => 'data' ] );
		
		$response = $request_builder->request();
		
		// Verify mock response was returned
		$this->assertEquals( json_encode( $mock_response ), $response );
		
		// Verify request was stored for inspection
		$request_file = $this->mock_dir . '/request_' . md5( $test_url ) . '.json';
		$this->assertFileExists( $request_file );
		
		$stored_request = json_decode( file_get_contents( $request_file ), true );
		$this->assertEquals( $test_url, $stored_request['url'] );
		$this->assertEquals( 'POST', $stored_request['method'] );
		$this->assertEquals( [ 'test' => 'data', 'client' => 'qit_cli' ], $stored_request['post_body'] );
		
		// Verify last request was also stored
		$last_request_file = $this->mock_dir . '/last_request.json';
		$this->assertFileExists( $last_request_file );
		
		$last_request = json_decode( file_get_contents( $last_request_file ), true );
		$this->assertEquals( $stored_request, $last_request );
	}

	public function test_missing_mock_throws_exception(): void {
		$test_url = 'https://example.com/api/no-mock';
		
		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'No mock for: ' . $test_url );
		
		$request_builder = new RequestBuilder( $test_url );
		$request_builder->request();
	}

	public function test_mocking_disabled_when_env_var_not_set(): void {
		// Remove the environment variable
		putenv( 'QIT_MOCK_DIR' );
		
		$test_url = 'https://httpbin.org/get';
		
		$request_builder = new RequestBuilder( $test_url );
		
		// This should make a real HTTP request (or fail trying)
		// We expect it to not use the mocking system
		try {
			$response = $request_builder->request();
			// If we get here, it made a real request
			$this->assertNotEmpty( $response );
		} catch ( \Exception $e ) {
			// Real HTTP request might fail for various reasons, that's OK
			// The important thing is it didn't try to use mocking
			$this->assertStringNotContainsString( 'No mock for:', $e->getMessage() );
		}
	}
}