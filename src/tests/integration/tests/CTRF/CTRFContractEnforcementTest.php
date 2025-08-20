<?php

namespace QIT\IntegrationTests\CTRF;

use QIT\IntegrationTests\Helpers\CTRFHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * CTRF Contract Enforcement Tests
 * 
 * These tests verify that the CTRF (Common Test Results Format) collection system
 * properly enforces its contracts according to the "NO FALLBACKS" philosophy.
 * 
 * Key principles tested:
 * 1. If a package declares CTRF output, it MUST produce it (no silent failures)
 * 2. Invalid CTRF data must cause test failures (no bad data propagation)
 * 3. Different package types are handled appropriately (test vs utility packages)
 * 4. Data integrity is maintained through collection and merging
 * 
 * These are defensive tests that ensure the system fails loudly and clearly
 * when contracts are violated, rather than silently continuing with missing
 * or corrupted data.
 */
class CTRFContractEnforcementTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		$this->fixturesDir = sys_get_temp_dir() . '/qit-ctrf-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		parent::tearDown();
	}

	/**
	 * Test that a package with a run phase MUST produce valid CTRF when declared.
	 * 
	 * This is the happy path - verifies that when everything is configured correctly,
	 * the CTRF file is created at the declared location with valid structure.
	 */
	public function test_package_declaring_ctrf_must_produce_valid_ctrf(): void {
		$packageDir = $this->createPackageWithValidCTRF( 'valid-ctrf-pkg' );
		
		$proc = $this->runTestPackage( $packageDir );
		
		// Package should succeed
		$this->assertEquals( 0, $proc->getExitCode(), 
			"Package with valid CTRF should succeed. Error: " . $proc->getErrorOutput() );
		
		// Verify CTRF was actually created at the declared location
		$ctrfPath = $packageDir . '/results/ctrf.json';
		$this->assertFileExists( $ctrfPath, "CTRF file MUST exist at declared location" );
		
		// Verify CTRF is valid JSON with required structure
		$ctrf = json_decode( file_get_contents( $ctrfPath ), true );
		$this->assertIsArray( $ctrf, "CTRF must be valid JSON" );
		$this->assertArrayHasKey( 'results', $ctrf, "CTRF must have 'results' key" );
		$this->assertArrayHasKey( 'summary', $ctrf['results'], "CTRF must have summary" );
		$this->assertArrayHasKey( 'tests', $ctrf['results'], "CTRF must have tests array" );
	}

	/**
	 * Test that a package declaring CTRF but not producing it MUST fail.
	 * 
	 * This enforces the NO FALLBACKS principle - if you promise CTRF output
	 * but don't deliver it, the test run must fail with a clear error.
	 * Silent failures are absolutely forbidden.
	 */
	public function test_package_declaring_ctrf_but_not_producing_it_must_fail(): void {
		$packageDir = $this->createPackageWithMissingCTRF( 'missing-ctrf-pkg' );
		
		$proc = $this->runTestPackage( $packageDir );
		
		// Test MUST fail when CTRF is declared but not created
		$this->assertNotEquals( 0, $proc->getExitCode(), 
			"Test MUST fail when declared CTRF is missing. Output: " . $proc->getOutput() );
		
		// Verify CTRF file does NOT exist
		$ctrfPath = $packageDir . '/results/ctrf.json';
		$this->assertFileDoesNotExist( $ctrfPath, "CTRF file should not exist" );
	}

	/**
	 * Test that a package producing invalid CTRF MUST fail.
	 * 
	 * Invalid data is worse than no data. If CTRF is malformed or missing
	 * required fields, the test run must fail rather than propagate bad data.
	 */
	public function test_package_producing_invalid_ctrf_must_fail(): void {
		$packageDir = $this->createPackageWithInvalidCTRF( 'invalid-ctrf-pkg' );
		
		$proc = $this->runTestPackage( $packageDir );
		
		// Test MUST fail when CTRF is invalid
		$this->assertNotEquals( 0, $proc->getExitCode(), 
			"Test MUST fail when CTRF is invalid. Output: " . $proc->getOutput() );
		
		// Verify CTRF file exists but is invalid
		$ctrfPath = $packageDir . '/results/ctrf.json';
		$this->assertFileExists( $ctrfPath, "Invalid CTRF file should exist" );
		
		// Verify it's not valid CTRF structure
		$content = json_decode( file_get_contents( $ctrfPath ), true );
		$this->assertIsArray( $content, "Should be valid JSON" );
		$this->assertArrayNotHasKey( 'results', $content, "Invalid CTRF should not have proper structure" );
	}

	/**
	 * Test that utility-only packages are rejected by run:e2e.
	 * 
	 * The run:e2e command requires at least one package with a run phase.
	 * Utility packages that only perform setup/teardown should use env:up instead.
	 * This test verifies that the system properly rejects test runs with no actual tests.
	 */
	public function test_utility_only_packages_are_rejected(): void {
		$packageDir = $this->createUtilityPackage( 'utility-pkg' );
		
		$proc = $this->runTestPackage( $packageDir );
		
		// Utility-only packages should be rejected
		$this->assertNotEquals( 0, $proc->getExitCode(), 
			"Utility-only package should be rejected by run:e2e" );
		
		// Verify the error message guides users correctly
		$output = $proc->getOutput();
		$this->assertStringContainsString( 'No test packages with run phase found', $output,
			"Should inform user that no test packages were found" );
		$this->assertStringContainsString( 'Use "env:up --global-setup"', $output,
			"Should suggest using env:up for setup-only scenarios" );
	}
	
	/**
	 * Test that mixed utility and test packages work correctly.
	 * 
	 * When running both utility packages (setup/teardown only) and test packages together,
	 * the system should accept the run and only require CTRF from packages with run phases.
	 */
	public function test_mixed_utility_and_test_packages_succeed(): void {
		$utilityPackage = $this->createUtilityPackage( 'utility-pkg' );
		$testPackage = $this->createPackageWithValidCTRF( 'test-pkg', 2 );
		
		$proc = $this->runTestPackages( [ $utilityPackage, $testPackage ] );
		
		// Should succeed because there's at least one test package
		$this->assertEquals( 0, $proc->getExitCode(), 
			"Mixed utility and test packages should succeed. Output:\n" . $proc->getOutput() );
		
		// Verify CTRF was created only by the test package
		$testCtrfPath = $testPackage . '/results/ctrf.json';
		$this->assertFileExists( $testCtrfPath, "Test package should create CTRF" );
		
		$utilityCtrfPath = $utilityPackage . '/results/ctrf.json';
		$this->assertFileDoesNotExist( $utilityCtrfPath, "Utility package should not create CTRF" );
	}

	/**
	 * Test that CTRF from a single package gets properly merged.
	 * 
	 * Even a single package goes through the merge pipeline to ensure
	 * consistency. This tests that the merge process preserves all data.
	 */
	public function test_single_package_ctrf_gets_merged_correctly(): void {
		$packageDir = $this->createPackageWithValidCTRF( 'single-pkg', 2 );
		
		$proc = $this->runTestPackage( $packageDir );
		$this->assertEquals( 0, $proc->getExitCode(), "Test should succeed" );
		
		// Get the artifacts directory to check merged CTRF
		$artifactsDir = $this->getArtifactsDirectory();
		$this->assertNotNull( $artifactsDir, "Should find artifacts directory" );
		
		// Check if merged CTRF exists
		$mergedCtrfPath = $artifactsDir . '/final/ctrf/ctrf-report.json';
		$this->assertFileExists( $mergedCtrfPath, "Merged CTRF should exist" );
		
		// Verify CTRF content
		$ctrfData = json_decode( file_get_contents( $mergedCtrfPath ), true );
		$this->assertIsArray( $ctrfData, "CTRF should be valid JSON" );
		$this->assertArrayHasKey( 'results', $ctrfData );
		$this->assertArrayHasKey( 'summary', $ctrfData['results'] );
		$this->assertEquals( 2, $ctrfData['results']['summary']['tests'], "Should have 2 tests" );
		$this->assertEquals( 2, $ctrfData['results']['summary']['passed'], "Should have 2 passed tests" );
	}

	/**
	 * Test that CTRF from multiple packages gets merged with all results preserved.
	 * 
	 * This verifies data integrity - when multiple packages produce CTRF,
	 * all test results must be preserved in the final merged report.
	 */
	public function test_multiple_packages_ctrf_merged_with_all_results(): void {
		$package1Dir = $this->createPackageWithValidCTRF( 'pkg1', 2 );
		$package2Dir = $this->createPackageWithValidCTRF( 'pkg2', 3 );
		
		$proc = $this->runTestPackages( [ $package1Dir, $package2Dir ] );
		$this->assertEquals( 0, $proc->getExitCode(), "Test should succeed" );
		
		// Get the artifacts directory
		$artifactsDir = $this->getArtifactsDirectory();
		$this->assertNotNull( $artifactsDir, "Should find artifacts directory" );
		
		// Check merged CTRF
		$mergedCtrfPath = $artifactsDir . '/final/ctrf/ctrf-report.json';
		$this->assertFileExists( $mergedCtrfPath, "Merged CTRF should exist" );
		
		// Verify CTRF contains all tests
		$ctrfData = json_decode( file_get_contents( $mergedCtrfPath ), true );
		$this->assertIsArray( $ctrfData );
		$this->assertArrayHasKey( 'results', $ctrfData );
		$this->assertArrayHasKey( 'tests', $ctrfData['results'] );
		
		// Should contain tests from both packages (2 + 3 = 5)
		$this->assertCount( 5, $ctrfData['results']['tests'], "Should have all 5 tests from both packages" );
		
		// Verify summary counts
		$this->assertEquals( 5, $ctrfData['results']['summary']['tests'], "Summary should show 5 total tests" );
		$this->assertEquals( 5, $ctrfData['results']['summary']['passed'], "Summary should show 5 passed tests" );
	}

	// ========== Helper Methods ==========

	private function runTestPackage( string $packageDir ) {
		return $this->runTestPackages( [ $packageDir ] );
	}

	private function runTestPackages( array $packageDirs ) {
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => $packageDirs
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );
		
		return qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );
	}

	private function getArtifactsDirectory(): ?string {
		$proc = qit( [
			'report',
			'--artifacts_dir',
		], return_process: true );
		
		if ( $proc->getExitCode() === 0 ) {
			return trim( $proc->getOutput() );
		}
		
		return null;
	}

	private function createPackageWithValidCTRF( string $name, int $testCount = 2 ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package with valid CTRF',
			'test' => [
				'phases' => [
					'run' => [
						'host: mkdir -p ./results ./blob-report && ' .
						'echo \'' . CTRFHelper::create_passing_report( $testCount ) . '\' > ./results/ctrf.json && ' .
						'touch ./blob-report/report.zip'
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

	private function createPackageWithMissingCTRF( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package missing CTRF',
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Running test but NOT creating CTRF" && mkdir -p ./blob-report && touch ./blob-report/report.zip'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',  // Declared but not created - MUST FAIL
					'blob-dir' => './blob-report'
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageWithInvalidCTRF( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package with invalid CTRF',
			'test' => [
				'phases' => [
					'run' => [
						'host: mkdir -p ./results ./blob-report && ' .
						'echo \'{"invalid": "not a valid CTRF structure"}\' > ./results/ctrf.json && ' .
						'touch ./blob-report/report.zip'
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
		
		$manifest = [
			'package' => 'test/' . $name,
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
}