<?php

namespace QIT_CLI_Tests\Environment;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\PackageOrchestrator;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Test that PackageOrchestrator properly adds lifecycle metadata to CTRF
 */
class PackageOrchestratorLifecycleTest extends TestCase {

	public function test_lifecycle_entries_have_metadata_flags(): void {
		// Create orchestrator with null output
		$orchestrator = new PackageOrchestrator( new NullOutput() );
		
		// Simulate showing a command first (required for recording)
		$orchestrator->show_command( 'echo "Setting up test package"', 'docker' );
		
		// Record a lifecycle command
		$orchestrator->record_lifecycle_command( 0, 'setup', 'test-package:1.0.0' );
		
		// Get the lifecycle results
		$results = $orchestrator->get_lifecycle_results();
		
		// Verify we have one entry
		$this->assertCount( 1, $results );
		
		$entry = $results[0];
		
		// Check that the entry has the expected structure
		$this->assertArrayHasKey( 'extra', $entry );
		$this->assertArrayHasKey( 'isLifecycle', $entry['extra'] );
		$this->assertArrayHasKey( 'countsTowardTotals', $entry['extra'] );
		
		// Verify the flag values
		$this->assertTrue( $entry['extra']['isLifecycle'] );
		$this->assertFalse( $entry['extra']['countsTowardTotals'] );
		
		// Verify other metadata
		$this->assertEquals( 'lifecycle', $entry['extra']['type'] );
		$this->assertEquals( 'setup', $entry['extra']['phase'] );
		$this->assertEquals( 'test-package:1.0.0', $entry['extra']['package'] );
		$this->assertEquals( 0, $entry['extra']['exitCode'] );
	}
	
	public function test_lifecycle_entries_for_failed_command(): void {
		// Create orchestrator
		$orchestrator = new PackageOrchestrator( new NullOutput() );
		
		// Simulate showing a command first
		$orchestrator->show_command( 'echo "Tearing down utility package"', 'docker' );
		
		// Record a failed lifecycle command
		$orchestrator->record_lifecycle_command( 1, 'teardown', 'utility-package:2.0.0' );
		
		// Get the lifecycle results
		$results = $orchestrator->get_lifecycle_results();
		
		$this->assertCount( 1, $results );
		
		$entry = $results[0];
		
		// Check status
		$this->assertEquals( 'failed', $entry['status'] );
		
		// Check metadata flags
		$this->assertTrue( $entry['extra']['isLifecycle'] );
		$this->assertFalse( $entry['extra']['countsTowardTotals'] );
		$this->assertEquals( 1, $entry['extra']['exitCode'] );
	}
	
	public function test_orchestrator_ctrf_generation(): void {
		// Create orchestrator
		$orchestrator = new PackageOrchestrator( new NullOutput() );
		
		// Record multiple lifecycle commands (with show_command first)
		$orchestrator->show_command( 'echo "Global setup"', 'docker' );
		$orchestrator->record_lifecycle_command( 0, 'globalSetup', 'global' );
		
		$orchestrator->show_command( 'echo "Package setup"', 'docker' );
		$orchestrator->record_lifecycle_command( 0, 'setup', 'package-a:1.0.0' );
		
		$orchestrator->show_command( 'npx playwright test', 'docker' );
		$orchestrator->record_lifecycle_command( 1, 'run', 'package-a:1.0.0' );
		
		$orchestrator->show_command( 'echo "Package teardown"', 'docker' );
		$orchestrator->record_lifecycle_command( 0, 'teardown', 'package-a:1.0.0' );
		
		$orchestrator->show_command( 'echo "Global teardown"', 'docker' );
		$orchestrator->record_lifecycle_command( 0, 'globalTeardown', 'global' );
		
		// Get results
		$results = $orchestrator->get_lifecycle_results();
		
		$this->assertCount( 5, $results );
		
		// Check all have the metadata flags
		foreach ( $results as $result ) {
			$this->assertTrue( $result['extra']['isLifecycle'] );
			$this->assertFalse( $result['extra']['countsTowardTotals'] );
		}
		
		// Create temp directory for CTRF output
		$temp_dir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		mkdir( $temp_dir, 0755, true );
		
		try {
			// Save orchestrator CTRF
			$orchestrator->save_orchestrator_ctrf( $temp_dir );
			
			// Check CTRF file was created
			$ctrf_file = $temp_dir . '/ctrf/orchestrator.json';
			$this->assertFileExists( $ctrf_file );
			
			// Load and verify CTRF content
			$ctrf = json_decode( file_get_contents( $ctrf_file ), true );
			
			$this->assertArrayHasKey( 'results', $ctrf );
			$this->assertArrayHasKey( 'tests', $ctrf['results'] );
			$this->assertCount( 5, $ctrf['results']['tests'] );
			
			// Check each test has the lifecycle flags
			foreach ( $ctrf['results']['tests'] as $test ) {
				$this->assertArrayHasKey( 'extra', $test );
				$this->assertTrue( $test['extra']['isLifecycle'] );
				$this->assertFalse( $test['extra']['countsTowardTotals'] );
			}
			
		} finally {
			// Clean up temp directory
			if ( is_dir( $temp_dir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $temp_dir ) );
			}
		}
	}
}