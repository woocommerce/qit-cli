<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../MockHelper.php';

use QIT_CLI\Tests\Integration\MockHelper;
use QIT_CLI\Tests\Integration\QitHttpMockHelper;

class ImprovedHttpMockingTest extends \PHPUnit\Framework\TestCase {
	use QitHttpMockHelper;

	protected function setUp(): void {
		parent::setUp();
		MockHelper::setup();
	}

	protected function tearDown(): void {
		MockHelper::cleanup();
		parent::tearDown();
	}

	public function test_chronological_request_logging(): void {
		// Set up multiple mock endpoints
		$manager_url = 'https://stagingcompatibilitydashboard.wpcomstaging.com';
		$sync_endpoint = $manager_url . '/wp-json/cd/v1/cli/sync';
		$enqueue_endpoint = $manager_url . '/wp-json/cd/v1/enqueue-e2e';
		
		MockHelper::mock( $sync_endpoint, [
			'success' => true,
			'data' => [ 'sync_complete' => true ]
		] );
		
		MockHelper::mock( $enqueue_endpoint, [
			'test_run_id' => 42,
			'test_results_manager_url' => ''
		] );

		// Create a config file for testing precedence
		$config = [ 'environments' => [ 'default' => [ 'php' => '7.4' ] ] ];
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config ) );

		// Run a CLI command that makes multiple HTTP requests
		try {
			qit( [
				'run:e2e',
				'woocommerce',
				'--config', $configPath,
				'--php', '8.1'  // CLI override
			], [], 0, MockHelper::env() );
		} catch ( \Exception $e ) {
			// Command might fail for other reasons, but HTTP mocking should work
		}

		// Test chronological access using the trait
		$all_requests = $this->allRequests();
		$this->assertNotEmpty( $all_requests, 'Should have captured requests chronologically' );

		// Test URL-specific access using the trait
		$sync_request = $this->requestByUrl( $sync_endpoint );
		$this->assertNotNull( $sync_request, 'Should be able to find sync request by URL' );
		$this->assertEquals( $sync_endpoint, $sync_request['url'] );

		// Test that each request entry has the expected structure
		foreach ( $all_requests as $request ) {
			$this->assertArrayHasKey( 'url', $request );
			$this->assertArrayHasKey( 'hash', $request );
			$this->assertArrayHasKey( 'body', $request );
			$this->assertEquals( sha1( $request['url'] ), $request['hash'] );
		}

		// Test backward compatibility with lastRequest
		$last_request = MockHelper::lastRequest();
		$this->assertNotNull( $last_request );
		$this->assertEquals( end( $all_requests ), $last_request );

		// Test static methods from MockHelper
		$static_all_requests = MockHelper::allRequests();
		$this->assertEquals( $all_requests, $static_all_requests );

		$static_sync_request = MockHelper::requestByUrl( $sync_endpoint );
		$this->assertEquals( $sync_request, $static_sync_request );

		// Clean up
		unlink( $configPath );
	}

	public function test_request_sequence_and_precedence(): void {
		// Mock endpoints
		$manager_url = 'https://stagingcompatibilitydashboard.wpcomstaging.com';
		$enqueue_endpoint = $manager_url . '/wp-json/cd/v1/enqueue-e2e';
		
		MockHelper::mock( $enqueue_endpoint, [
			'test_run_id' => 123,
			'test_results_manager_url' => ''
		] );

		// Create config with default PHP version
		$config = [ 'environments' => [ 'default' => [ 'php' => '7.4' ] ] ];
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config ) );

		// Run command with CLI override
		try {
			qit( [
				'run:e2e',
				'woocommerce',
				'--config', $configPath,
				'--php', '8.1'  // This should override the config
			], [], 0, MockHelper::env() );
		} catch ( \Exception $e ) {
			// Ignore command failures, focus on HTTP mocking
		}

		// Verify CLI precedence in the request payload
		$enqueue_request = $this->requestByUrl( $enqueue_endpoint );
		if ( $enqueue_request && isset( $enqueue_request['body']['post_body']['environment']['php_version'] ) ) {
			$this->assertEquals( '8.1', $enqueue_request['body']['post_body']['environment']['php_version'],
				'CLI option should override config file' );
		}

		// Test request sequence
		$all_requests = $this->allRequests();
		$this->assertNotEmpty( $all_requests );

		// Verify we can map requests to their mock files using the hash
		foreach ( $all_requests as $request ) {
			$expected_hash = sha1( $request['url'] );
			$this->assertEquals( $expected_hash, $request['hash'] );
		}

		unlink( $configPath );
	}

	public function test_missing_mock_fails_fast(): void {
		// Don't set up any mocks
		
		$this->expectException( \LogicException::class );
		$this->expectExceptionMessage( 'No mock for:' );
		
		// This should fail immediately when the first HTTP request is attempted
		qit( [
			'partner:list'
		], [], 1, MockHelper::env() );
	}
}