<?php

namespace QIT\IntegrationTests\TestPackages\Commands\RunE2E\Validation;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test validation of test packages with run phases.
 * 
 * These tests verify:
 * 1. Packages with run phases must produce test results
 * 2. Packages without run phases are treated as utility packages
 * 3. Proper error messages are shown for invalid configurations
 */
class RunE2EValidationTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../../../fixtures/test-packages';
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test that a package with a run phase but no tests throws an error
	 */
	public function test_package_with_run_phase_but_no_tests_fails(): void {
		// Create a test package with empty run phase
		$packageDir = $this->createEmptyTestPackage();
		
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();
		$errorOutput = $proc->getErrorOutput();
		$combinedOutput = $output . "\n" . $errorOutput;

		// Debug: output what we're getting
		if (!str_contains($combinedOutput, 'declared a run phase but produced 0 test results')) {
			echo "\n=== DEBUG OUTPUT ===\n";
			echo "Exit code: " . $proc->getExitCode() . "\n";
			echo "Combined output (last 2000 chars):\n";
			echo substr($combinedOutput, -2000) . "\n";
			echo "=== END DEBUG ===\n";
		}

		// Should fail with our specific error message (check both stdout and stderr)
		$this->assertStringContainsString( 'declared a run phase but produced 0 test results', $combinedOutput );
		$this->assertStringContainsString( 'Either add a real test, or remove the run phase if this is a pure setup package', $combinedOutput );
	}

	/**
	 * Test that a package without a run phase (utility package) fails with run:e2e
	 * Since run:e2e is for running tests, utility-only packages should use env:up instead
	 */
	public function test_utility_package_without_run_phase_fails(): void {
		// Create a utility package with only setup/teardown
		$packageDir = $this->createUtilityPackage();
		
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );  // Should fail for utility-only packages

		$output = $proc->getOutput();

		// Should fail with appropriate message
		$this->assertEquals( 1, $proc->getExitCode() );
		$this->assertStringContainsString( 'No test packages with run phase found', $output );
		// Should not complain about missing test results
		$this->assertStringNotContainsString( 'produced 0 test results', $output );
	}

	/**
	 * Test #31: Mixed utility and test packages
	 * 
	 * Coverage aim: Validates mixing utility and test packages.
	 * Tests that utility packages (setup only) and test packages can be
	 * executed together, with utility packages providing setup capabilities.
	 * 
	 * Key aspects tested:
	 * - Mixed package type execution
	 * - Utility package setup contribution
	 * - Test package execution after utility setup
	 * - Proper orchestration of mixed types
	 */
	public function test_mixed_utility_and_test_packages(): void {
		// Create one utility package and one test package
		$utilityPackage = $this->createUtilityPackage();
		$testPackage = $this->fixturesDir . '/regular-test-package-one';
		
		$config = $this->createConfig( [ $utilityPackage, $testPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// Should succeed
		$this->assertEquals( 0, $proc->getExitCode() );
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		// Test count includes orchestrator-generated lifecycle entries (setup/teardown)
		// Regular test package has 3 tests + 2 lifecycle entries = 5 total
		$this->assertStringContainsString( 'Tests:         5 passed', $output );
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	// ============= Helper Methods =============

	private function createEmptyTestPackage(): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-empty-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		// Create manifest with run phase but no actual tests
		$manifest = [
			'package' => 'woocommerce/qit-integration-test-empty-package',
			'test_type' => 'e2e',
			'description' => 'Package with run phase but no tests',
			'test' => [
				'phases' => [
					'run' => [
						// Create results directory and CTRF file with 0 tests
						'host: mkdir -p ./results && echo \'{"results":{"tool":{"name":"test-package"},"summary":{"tests":0,"passed":0,"failed":0,"skipped":0,"pending":0,"other":0,"start":0,"stop":1000},"tests":[]}}\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function createUtilityPackage(): string {
		$tempDir = sys_get_temp_dir() . '/qit-utility-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		// Create manifest with only setup/teardown phases (no run phase)
		$manifest = [
			'package' => 'woocommerce/qit-integration-test-utility-package',
			'test_type' => 'e2e',
			'description' => 'Utility package for setup/teardown only',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "Setting up test environment..."'
					],
					'teardown' => [
						'echo "Cleaning up test environment..."'
					]
				]
				// No results needed for utility packages
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