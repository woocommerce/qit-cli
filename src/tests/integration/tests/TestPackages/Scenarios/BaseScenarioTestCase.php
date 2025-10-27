<?php

namespace integration\tests\TestPackages\Scenarios;

use PHPUnit\Framework\TestCase;

/**
 * Base test case for scenario tests that provides automatic cleanup of Docker resources.
 */
abstract class BaseScenarioTestCase extends TestCase {
	/** @var array Track environment IDs created during this test */
	protected array $created_env_ids = [];
	
	/** @var string Temporary directory for test files */
	protected string $test_temp_dir;
	
	/**
	 * Set up before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		
		// Create unique temp directory for this test
		$this->test_temp_dir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		mkdir( $this->test_temp_dir, 0777, true );
	}
	
	/**
	 * Clean up after each test.
	 */
	protected function tearDown(): void {
		// Clean up any created environments
		foreach ( $this->created_env_ids as $env_id ) {
			$this->cleanupEnvironment( $env_id );
		}

		// Clean up temp directory
		if ( isset( $this->test_temp_dir ) && is_dir( $this->test_temp_dir ) ) {
			$this->recursiveRemoveDirectory( $this->test_temp_dir );
		}

		parent::tearDown();
	}
	
	/**
	 * Track an environment ID for cleanup.
	 *
	 * @param string $env_id Environment ID to track
	 */
	protected function trackEnvironment( string $env_id ): void {
		$this->created_env_ids[] = $env_id;
	}
	
	/**
	 * Clean up a specific environment.
	 *
	 * @param string $env_id Environment ID to clean up
	 */
	private function cleanupEnvironment( string $env_id ): void {
		try {
			// Stop the environment - no fallbacks
			qit( [ 'env:down', $env_id ] );
		} catch ( \Exception $e ) {
			// If env:down fails, that's a problem to fix in the main code, not here
			// Just log it and move on
			fprintf( STDERR, "Warning: Failed to clean up environment %s: %s\n", $env_id, $e->getMessage() );
		}
	}
	
	/**
	 * Recursively remove a directory.
	 *
	 * @param string $dir Directory to remove
	 */
	private function recursiveRemoveDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursiveRemoveDirectory( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
	
	/**
	 * Helper to run env:up and automatically track the environment.
	 *
	 * @param array $args Command arguments
	 * @return array Decoded JSON response with env_id
	 */
	protected function runEnvUp( array $args ): array {
		// Ensure --json is included
		if ( ! in_array( '--json', $args ) ) {
			$args[] = '--json';
		}
		
		$output = qit( array_merge( [ 'env:up' ], $args ) );
		$data = json_decode( $output, true );
		
		if ( isset( $data['env_id'] ) ) {
			$this->trackEnvironment( $data['env_id'] );
		}
		
		return $data;
	}
}