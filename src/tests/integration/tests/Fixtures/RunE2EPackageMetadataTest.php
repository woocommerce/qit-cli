<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\Helpers\CTRFHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Integration test to verify package metadata is correctly included in CTRF reports
 * 
 * This test file focuses on verifying that package metadata (package IDs, types, 
 * versions, etc.) is correctly tracked and included in the final CTRF reports.
 * 
 * For CTRF contract enforcement tests (missing CTRF, invalid CTRF, etc.), 
 * see tests/CTRF/CTRFContractEnforcementTest.php
 */
class RunE2EPackageMetadataTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		$this->fixturesDir = sys_get_temp_dir() . '/qit-metadata-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		parent::tearDown();
	}

	/**
	 * Test that local packages with setup/teardown scripts don't create duplicate entries
	 * 
	 * This test verifies that when a local test package contains global setup/teardown scripts,
	 * it doesn't get split into both a "utility" and "test" package in the CTRF metadata.
	 * Previously, bash scripts would use basename() for package identification while tests
	 * used the full path, causing duplication.
	 * 
	 * @group metadata
	 */
	public function test_local_package_no_duplicate_entries_in_metadata(): void {
		$this->markTestSkipped( 'This test requires checking CTRF content which is uploaded to Manager and not accessible locally' );
		
		// Create a test package with both test and setup/teardown phases
		$packageDir = $this->createLocalPackageWithLifecycle( 'local-test-pkg' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $packageDir ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		// Run the test
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		$this->assertEquals( 0, $proc->getExitCode(), 'Test should pass. Output: ' . $proc->getOutput() . "\n\nError: " . $proc->getErrorOutput() );
		
		// TODO: Once we have a way to access the uploaded CTRF metadata, verify:
		// 1. Only ONE package entry exists (not duplicated)
		// 2. Package is marked as 'test' type (has run phase)
		// 3. Package ID is consistent across all references
		// 4. No "unknown" package entries exist
	}

	/**
	 * Test that package metadata includes version information
	 * 
	 * @group metadata
	 */
	public function test_package_metadata_includes_version(): void {
		$this->markTestIncomplete( 'Need to implement version tracking in package metadata' );
		
		// TODO: Create a versioned test package
		// TODO: Run the test
		// TODO: Verify the CTRF metadata includes the correct version
	}

	/**
	 * Test that package metadata correctly identifies local vs remote packages
	 * 
	 * @group metadata
	 */
	public function test_package_metadata_distinguishes_local_vs_remote(): void {
		$this->markTestIncomplete( 'Need to implement local/remote tracking in metadata' );
		
		// TODO: Run test with both local and published packages
		// TODO: Verify metadata correctly identifies which are local and which are from registry
	}

	// ========== Helper Methods ==========

	/**
	 * Create a local test package with setup/teardown lifecycle phases
	 */
	private function createLocalPackageWithLifecycle( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/bootstrap', 0755, true );
		
		// Create bash scripts for lifecycle phases
		file_put_contents( $packageDir . '/bootstrap/global-setup.sh', '#!/bin/bash
echo "[globalSetup] Starting global configuration..."
echo "[globalSetup] Done."
exit 0
' );
		chmod( $packageDir . '/bootstrap/global-setup.sh', 0755 );
		
		file_put_contents( $packageDir . '/bootstrap/setup.sh', '#!/bin/bash
echo "[setup] Creating sample data ..."
echo "[setup] Done."
exit 0
' );
		chmod( $packageDir . '/bootstrap/setup.sh', 0755 );
		
		file_put_contents( $packageDir . '/bootstrap/global-teardown.sh', '#!/bin/bash
echo "[globalTeardown] Cleaning up ..."
echo "[globalTeardown] Done."
exit 0
' );
		chmod( $packageDir . '/bootstrap/global-teardown.sh', 0755 );
		
		// Create manifest with all phases
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'description' => 'Test package with lifecycle phases',
			'test' => [
				'phases' => [
					'globalSetup' => [ './bootstrap/global-setup.sh' ],
					'setup' => [ './bootstrap/setup.sh' ],
					'run' => [
						// Simple inline test that passes using helper
						'host: mkdir -p ./results && echo \'' . CTRFHelper::create_passing_report(1) . '\' > ./results/ctrf.json'
					],
					'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $packageDir;
	}

	private function writeConfig( array $config ): string {
		$tempDir = sys_get_temp_dir() . '/qit-config-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}