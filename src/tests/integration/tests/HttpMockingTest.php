<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../MockHelper.php';

use QIT_CLI\Tests\Integration\MockHelper;

class HttpMockingTest extends \PHPUnit\Framework\TestCase {

	protected function setUp(): void {
		parent::setUp();
		MockHelper::setup();
	}

	protected function tearDown(): void {
		MockHelper::cleanup();
		parent::tearDown();
	}

	public function test_http_mocking_proof_of_concept(): void {
		// Mock a Manager API endpoint
		$manager_url = 'https://stagingcompatibilitydashboard.wpcomstaging.com';
		$sync_endpoint = $manager_url . '/wp-json/cd/v1/cli/sync';
		
		MockHelper::mock( $sync_endpoint, [
			'success' => true,
			'data' => [ 'sync_complete' => true ]
		] );

		// Run a CLI command that would make an HTTP request
		// Using a simple command that should trigger sync
		try {
			qit( [
				'partner:list'
			], [], 0, MockHelper::env() );
		} catch ( \Exception $e ) {
			// Command might fail for other reasons, but we're testing HTTP mocking
			// The important part is that the HTTP request was intercepted
		}

		// Verify the request was intercepted and stored
		$sync_request = MockHelper::requestFor( $sync_endpoint );
		
		// If mocking worked, we should have captured the request
		$this->assertNotNull( $sync_request, 'HTTP request should have been intercepted and stored' );
		$this->assertEquals( $sync_endpoint, $sync_request['url'] );
		$this->assertEquals( 'POST', $sync_request['method'] );
		
		// Verify we can also get it as the last request
		$last_request = MockHelper::lastRequest();
		$this->assertNotNull( $last_request );
		$this->assertEquals( $sync_endpoint, $last_request['url'] );
	}

	public function test_missing_mock_throws_exception(): void {
		// Don't set up any mocks
		
		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'No mock for:' );
		
		// This should fail because no mock is set up
		qit( [
			'partner:list'
		], [], 1, MockHelper::env() ); // Expect exit code 1 due to exception
	}
}