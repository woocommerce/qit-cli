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
		
		// Clean up any orphaned Docker resources periodically
		static $test_count = 0;
		$test_count++;
		
		// Every 10 tests, do a more thorough cleanup
		if ( $test_count % 10 === 0 ) {
			$this->cleanupOrphanedDockerResources();
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
			// Try to stop the environment gracefully
			$proc = qit( [ 'env:down', $env_id ], return_process: true );
			
			// Force cleanup of Docker resources if graceful shutdown failed
			if ( $proc->getExitCode() !== 0 ) {
				$this->forceCleanupDockerResources( $env_id );
			}
		} catch ( \Exception $e ) {
			// Force cleanup on any error
			$this->forceCleanupDockerResources( $env_id );
		}
	}
	
	/**
	 * Force cleanup of Docker resources for an environment.
	 *
	 * @param string $env_id Environment ID
	 */
	private function forceCleanupDockerResources( string $env_id ): void {
		// Remove containers
		exec( "docker ps -a --filter 'name={$env_id}' -q | xargs -r docker rm -f 2>/dev/null" );
		
		// Remove networks  
		exec( "docker network ls --filter 'name={$env_id}' -q | xargs -r docker network rm 2>/dev/null" );
		
		// Remove volumes
		exec( "docker volume ls --filter 'name={$env_id}' -q | xargs -r docker volume rm 2>/dev/null" );
	}
	
	/**
	 * Clean up orphaned Docker resources from failed tests.
	 */
	private function cleanupOrphanedDockerResources(): void {
		// Clean up containers older than 1 hour
		exec( "docker ps -a --filter 'name=qitenv' --filter 'name=e2e-qitenv' --format '{{.ID}} {{.CreatedAt}}' | while read id created; do age=$(( $(date +%s) - $(date -d \"$created\" +%s) )); if [ $age -gt 3600 ]; then docker rm -f $id 2>/dev/null; fi; done" );
		
		// Clean up networks with no containers
		exec( "docker network ls --filter 'name=qitenv' --filter 'name=e2e-qitenv' -q | while read net; do if [ -z \"$(docker ps -q --filter network=$net)\" ]; then docker network rm $net 2>/dev/null; fi; done" );
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