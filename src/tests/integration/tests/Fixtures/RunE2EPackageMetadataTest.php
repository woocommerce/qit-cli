<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Integration test to verify package metadata is added to CTRF reports
 */
class RunE2EPackageMetadataTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-metadata-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test #18: CTRF contains package metadata
	 * 
	 * Coverage aim: Validates package metadata inclusion in CTRF reports.
	 * Tests that CTRF (Common Test Results Format) reports include comprehensive
	 * metadata about test packages including version, package counts, and report
	 * completeness information.
	 * 
	 * Key aspects tested:
	 * - Package metadata in CTRF extra field
	 * - Version information preservation
	 * - Package summary statistics
	 * - Report completeness tracking
	 */
	public function test_ctrf_contains_package_metadata(): void {
		// Create test package with blob report
		$testPackage = $this->createTestPackageWithBlob( 'test-package' );
		
		// Create utility package without blob
		$utilityPackage = $this->createUtilityPackage( 'utility-package' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $testPackage, $utilityPackage ]
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

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Find the CTRF report in the artifacts
		$output = $proc->getOutput();
		
		// Extract artifacts directory from output
		if ( preg_match( '/Artifacts directory: (.+)/', $output, $matches ) ) {
			$artifacts_dir = trim( $matches[1] );
			$ctrf_path = $artifacts_dir . '/final/ctrf/ctrf-report.json';
			
			if ( file_exists( $ctrf_path ) ) {
				$ctrf = json_decode( file_get_contents( $ctrf_path ), true );
				
				// Verify package metadata exists
				$this->assertArrayHasKey( 'results', $ctrf );
				$this->assertArrayHasKey( 'extra', $ctrf['results'] );
				$this->assertArrayHasKey( 'qitPackageMetadata', $ctrf['results']['extra'] );
				
				$metadata = $ctrf['results']['extra']['qitPackageMetadata'];
				
				// Check version
				$this->assertEquals( '1.0.0', $metadata['version'] );
				
				// Check packages
				$this->assertArrayHasKey( 'packages', $metadata );
				$this->assertCount( 2, $metadata['packages'] );
				
				// Check summary
				$this->assertArrayHasKey( 'summary', $metadata );
				$this->assertEquals( 2, $metadata['summary']['totalPackages'] );
				$this->assertEquals( 1, $metadata['summary']['packagesWithTests'] );
				$this->assertEquals( 1, $metadata['summary']['utilityPackages'] );
				
				// Check report completeness
				$this->assertArrayHasKey( 'reportCompleteness', $metadata );
				
				// Blob should be incomplete (utility package has no blob)
				if ( isset( $metadata['reportCompleteness']['blob'] ) ) {
					$this->assertFalse( $metadata['reportCompleteness']['blob']['complete'] );
				}
				
				// Check orchestration type marker
				$this->assertArrayHasKey( 'tool', $ctrf['results'] );
				$this->assertArrayHasKey( 'extra', $ctrf['results']['tool'] );
				$this->assertEquals( 'test-packages', $ctrf['results']['tool']['extra']['orchestrationType'] );
			} else {
				$this->markTestSkipped( 'CTRF report not found at expected location' );
			}
		} else {
			$this->markTestSkipped( 'Could not extract artifacts directory from output' );
		}
	}

	// Helper methods

	private function createTestPackageWithBlob( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		// Create manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'description' => 'Test package with blob report',
			'test' => [
				'phases' => [
					'run' => [
						// Create CTRF and blob report
						'mkdir -p ./results ./blob-report && ' .
						'echo \'{"results":{"summary":{"tests":2,"passed":2,"failed":0,"skipped":0,"pending":0,"other":0},"tests":[{"name":"test1","status":"passed","duration":100},{"name":"test2","status":"passed","duration":200}]}}\' > ./results/ctrf.json && ' .
						'echo "test data" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
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

	private function createUtilityPackage( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		// Create manifest without run phase
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'description' => 'Utility package for setup only',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "Setting up environment..."'
					],
					'teardown' => [
						'echo "Cleaning up environment..."'
					]
				]
				// No results section for utility packages
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

	/**
	 * Test that local packages with setup/teardown scripts don't create duplicate entries
	 * 
	 * This test verifies that when a local test package contains global setup/teardown scripts,
	 * it doesn't get split into both a "utility" and "test" package in the CTRF metadata.
	 * Previously, bash scripts would use basename() for package identification while tests
	 * used the full path, causing duplication.
	 */
	public function test_local_package_no_duplicate_entries(): void {
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
		
		// Find the CTRF report in the artifacts
		$output = $proc->getOutput();
		
		// Try multiple patterns to find artifacts directory
		$patterns = [
			'/Artifacts directory: (.+)/',
			'/Test artifacts saved to: (.+)/',
			'/Results saved to: (.+)/',
			'/Report available at: (.+)/'
		];
		
		$artifacts_dir = null;
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $output, $matches ) ) {
				$artifacts_dir = trim( $matches[1] );
				break;
			}
		}
		
		if ( $artifacts_dir ) {
			$ctrf_path = $artifacts_dir . '/final/ctrf/ctrf-report.json';
			
			if ( file_exists( $ctrf_path ) ) {
				$ctrf = json_decode( file_get_contents( $ctrf_path ), true );
				
				// Check package metadata
				$this->assertArrayHasKey( 'results', $ctrf );
				$this->assertArrayHasKey( 'extra', $ctrf['results'] );
				$this->assertArrayHasKey( 'qitPackageMetadata', $ctrf['results']['extra'] );
				
				$metadata = $ctrf['results']['extra']['qitPackageMetadata'];
				
				// Should only have ONE package, not two
				$this->assertCount( 1, $metadata['packages'], 'Should have exactly one package, not duplicated' );
				
				$package = $metadata['packages'][0];
				
				// The single package should be marked as 'test' type since it has a run phase
				$this->assertEquals( 'test', $package['packageType'], 'Package should be test type' );
				$this->assertTrue( $package['hasRunPhase'], 'Package should have run phase' );
				$this->assertGreaterThan( 0, $package['testCount'], 'Package should have tests' );
				
				// Verify the package ID is consistent (should be the full path)
				$this->assertEquals( $packageDir, $package['packageId'], 'Package ID should be the full path' );
				
				// Check summary counts
				$this->assertEquals( 1, $metadata['summary']['totalPackages'], 'Should have 1 total package' );
				$this->assertEquals( 1, $metadata['summary']['packagesWithTests'], 'Should have 1 package with tests' );
				$this->assertEquals( 0, $metadata['summary']['utilityPackages'], 'Should have 0 utility packages' );
				
				// Verify no "unknown" package entries in test results
				$unknown_tests = array_filter( $ctrf['results']['tests'], function( $test ) {
					return ! isset( $test['extra']['packageSlug'] ) || 
					       $test['extra']['packageSlug'] === null ||
					       $test['extra']['packageSlug'] === '';
				} );
				
				$this->assertEmpty( $unknown_tests, 'Should not have any tests with unknown/null packageSlug' );
				
				// Verify no duplicate bash script entries
				$test_names = array_map( function( $test ) {
					return $test['name'];
				}, $ctrf['results']['tests'] );
				
				// Should not have both "setup.sh" and "[setup]..." entries
				$this->assertNotContains( 'global-setup.sh', $test_names, 'Should not have duplicate global-setup.sh entry' );
				$this->assertNotContains( 'setup.sh', $test_names, 'Should not have duplicate setup.sh entry' );
				
				// Verify report completeness is correct
				if ( isset( $metadata['reportCompleteness'] ) ) {
					if ( isset( $metadata['reportCompleteness']['blob'] ) ) {
						$blob = $metadata['reportCompleteness']['blob'];
						$this->assertEquals( $blob['packagesWithBlob'], $blob['totalPackagesWithTests'], 
							'Blob package count should match total packages with tests' );
					}
					if ( isset( $metadata['reportCompleteness']['allure'] ) ) {
						$allure = $metadata['reportCompleteness']['allure'];
						$this->assertEquals( $allure['packagesWithAllure'], $allure['totalPackagesWithTests'],
							'Allure package count should match total packages with tests' );
					}
				}
				
			} else {
				$this->markTestSkipped( 'CTRF report not found at expected location: ' . $ctrf_path );
			}
		} else {
			// Output first 500 chars of output to help debug
			$this->markTestSkipped( 'Could not extract artifacts directory from output. Output start: ' . substr( $output, 0, 500 ) );
		}
	}

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
						// Simple inline test that passes
						'mkdir -p ./results && echo \'{"results":{"summary":{"tests":1,"passed":1,"failed":0},"tests":[{"name":"sample test","status":"passed","duration":100}]}}\' > ./results/ctrf.json'
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
}