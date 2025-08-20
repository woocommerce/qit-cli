<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test that packages execute in the correct order and maintain proper isolation.
 * 
 * These tests verify:
 * 1. Packages run in the order specified
 * 2. Each package gets its own test environment
 * 3. Results are properly collected from all packages
 * 4. Failures in one package don't prevent others from running
 */
class RunE2EPackageOrderingTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test #19: Packages execute in order
	 * 
	 * Coverage aim: Validates deterministic package execution ordering.
	 * Tests that multiple test packages execute in the exact order specified
	 * in the configuration, ensuring predictable test execution sequences.
	 * 
	 * Key aspects tested:
	 * - Sequential package execution
	 * - Order preservation from configuration
	 * - Output ordering verification
	 * - Results collection from all packages
	 */
	public function test_packages_execute_in_order(): void {
		// Create simple test packages that output unique markers
		$package1 = $this->createSimplePackage('package-1', 'PACKAGE_1_WAS_HERE');
		$package2 = $this->createSimplePackage('package-2', 'PACKAGE_2_WAS_HERE');
		$package3 = $this->createSimplePackage('package-3', 'PACKAGE_3_WAS_HERE');
		
		$config = $this->createConfig( [ $package1, $package2, $package3 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// All packages should run despite failures
		$this->assertStringContainsString( 'PACKAGE [1/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]', $output );
		
		// Look for our unique markers in order
		$package1Pos = strpos( $output, 'PACKAGE_1_WAS_HERE' );
		$package2Pos = strpos( $output, 'PACKAGE_2_WAS_HERE' );
		$package3Pos = strpos( $output, 'PACKAGE_3_WAS_HERE' );
		
		$this->assertNotFalse($package1Pos, 'PACKAGE_1_WAS_HERE not found in output');
		$this->assertNotFalse($package2Pos, 'PACKAGE_2_WAS_HERE not found in output');
		$this->assertNotFalse($package3Pos, 'PACKAGE_3_WAS_HERE not found in output');
		
		// Check they appear in the right order (position in string increases)
		$this->assertLessThan( $package2Pos, $package1Pos, 'Package 1 should appear before package 2' );
		$this->assertLessThan( $package3Pos, $package2Pos, 'Package 2 should appear before package 3' );
		
		// Results should be collected from all packages
		$this->assertStringContainsString( 'Tests:', $output );
		
		// Should pass since all packages pass
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	/**
	 * Test #20: Failure doesn't stop other packages
	 * 
	 * Coverage aim: Validates fault tolerance in multi-package execution.
	 * Tests that when one package fails, subsequent packages still execute,
	 * ensuring complete test coverage even with partial failures.
	 * 
	 * Key aspects tested:
	 * - Continuation after package failure
	 * - All packages execute regardless of failures
	 * - Proper failure isolation
	 * - Complete execution despite errors
	 */
	public function test_failure_doesnt_stop_other_packages(): void {
		// Create packages with one that fails
		$package1 = $this->createFailingPackage('failing-package', 'FAILING_PACKAGE_RAN');
		$package2 = $this->createSimplePackage('success-package-1', 'SUCCESS_1_RAN');
		$package3 = $this->createSimplePackage('success-package-2', 'SUCCESS_2_RAN');
		
		$config = $this->createConfig( [ $package1, $package2, $package3 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// All packages should still run
		$this->assertStringContainsString( 'PACKAGE [1/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]', $output );
		
		// All packages should run - check for our markers
		$this->assertStringContainsString( 'FAILING_PACKAGE_RAN', $output );
		$this->assertStringContainsString( 'SUCCESS_1_RAN', $output );
		$this->assertStringContainsString( 'SUCCESS_2_RAN', $output );
	}

	/**
	 * Test #21: Results aggregation
	 * 
	 * Coverage aim: Validates test result aggregation across packages.
	 * Tests that results from multiple packages are correctly collected,
	 * merged, and reported in the final test summary.
	 * 
	 * Key aspects tested:
	 * - Result collection from multiple packages
	 * - Accurate test count aggregation
	 * - Combined pass/fail statistics
	 * - Merged results in summary
	 */
	public function test_results_aggregation(): void {
		// Package 1: 3 tests pass
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		// Package 2: 3 tests pass  
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show combined results
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		$this->assertStringContainsString( 'Tests:         6 passed', $output ); // 3 + 3 = 6
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	/**
	 * Test #22: Mixed results aggregation
	 * 
	 * Coverage aim: Validates result aggregation with mixed outcomes.
	 * Tests that when packages have different outcomes (some tests pass,
	 * some fail), the system correctly aggregates and reports combined statistics.
	 * 
	 * Key aspects tested:
	 * - Mixed pass/fail result handling
	 * - Accurate combined statistics
	 * - Overall failure status with partial failures
	 * - Correct test count aggregation
	 */
	public function test_mixed_results_aggregation(): void {
		// Failing package: 2 pass, 1 fail
		$package1 = $this->fixturesDir . '/failing-test-package';
		// Regular package: 3 pass
		$package2 = $this->fixturesDir . '/regular-test-package-one';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should show mixed results
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		$this->assertMatchesRegularExpression( '/Tests:.*5 passed.*1 failed/s', $output ); // 2+3 passed, 1 failed
		$this->assertStringContainsString( 'Status:        ✗ FAILED', $output );
	}

	/**
	 * Test #23: CTRF merging
	 * 
	 * Coverage aim: Validates CTRF report merging across packages.
	 * Tests that Common Test Results Format reports from multiple packages
	 * are correctly merged into a single unified report.
	 * 
	 * Key aspects tested:
	 * - CTRF report collection from packages
	 * - Report merging process
	 * - Merged report generation
	 * - Post-processing of CTRF data
	 */
	public function test_ctrf_merging(): void {
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show CTRF merging
		$this->assertStringContainsString( '✓ Merging CTRF reports...', $output );
		$this->assertStringContainsString( '✓ CTRF reports merged', $output );
	}

	/**
	 * Test #24: Blob report merging
	 * 
	 * Coverage aim: Validates blob report merging for HTML generation.
	 * Tests that Playwright blob reports from multiple packages are merged
	 * to generate a unified HTML test report.
	 * 
	 * Key aspects tested:
	 * - Blob report collection
	 * - HTML report generation from blobs
	 * - Report merging process
	 * - Local report availability
	 */
	public function test_blob_report_merging(): void {
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show blob merging
		$this->assertStringContainsString( '✓ Merging blob reports into HTML...', $output );
		$this->assertStringContainsString( '✓ HTML report generated', $output );
		
		// Should provide report command
		$this->assertStringContainsString( 'Local Report:  qit report', $output );
	}

	// ============= Helper Methods =============

	private function createSimplePackage( string $name, string $marker ): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . $name . '-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		// Create a simple manifest that just echoes a marker
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [
						"echo '$marker'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"test\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json && mkdir -p ./blob-report && echo 'test' > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $tempDir;
	}

	private function createFailingPackage( string $name, string $marker ): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . $name . '-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		// Create a manifest with a test that fails
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [
						"echo '$marker'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":2,\"passed\":1,\"failed\":1,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"passing\",\"status\":\"passed\",\"duration\":100},{\"name\":\"failing\",\"status\":\"failed\",\"duration\":100}]}}' > ./results/ctrf.json && mkdir -p ./blob-report && echo 'test' > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt",
						"exit 1"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $tempDir;
	}

	private function createConfig( array $testPackages ): string {
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => $testPackages
					]
				]
			]
		];

		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}