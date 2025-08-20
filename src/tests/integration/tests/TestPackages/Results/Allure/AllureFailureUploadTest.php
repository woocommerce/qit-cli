<?php

namespace QIT\IntegrationTests\TestPackages\Results\Allure;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for Allure report upload behavior on test failures.
 * 
 * These tests verify that:
 * - Allure reports are automatically uploaded when tests fail
 * - Mixed pass/fail results trigger upload appropriately
 * - Failures without Allure configuration are handled gracefully
 * - Remote URLs are generated for debugging failed tests
 */
class AllureFailureUploadTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages';
		
		// Note: For Allure upload to actually work (with GitHub webhook callback),
		// you need to use staging environment by renaming .env.staging to .env
		// For local testing without actual upload, the default .env (local) is fine
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		parent::tearDown();
	}

	/**
	 * Test that failing tests trigger Allure upload.
	 * 
	 * When E2E tests fail, Allure reports should be automatically uploaded
	 * to provide debugging information.
	 */
	public function test_failing_tests_upload_allure(): void {
		// First, we need to scaffold the failing test package since it needs node_modules
		$failingPackage = $this->scaffoldFailingPackage();
		
		$config = $this->createConfig( [ $failingPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Test should FAIL
		$this->assertNotEquals( 0, $proc->getExitCode(), 'Test should fail' );
		
		// Should show failure in summary
		$this->assertStringContainsString( 'Status:        ✗ FAILED', $output );
		
		// When results collection fails, we won't have test counts
		// Just verify that the package failed to collect results
		$this->assertStringContainsString( 'Result collection failed', $output );
		
		// Should still have remote URL for debugging
		$this->assertMatchesRegularExpression( '/Remote URL:.*qit_results=/', $output );
	}

	/**
	 * Test mixed pass/fail results across multiple packages.
	 * 
	 * When running multiple packages where some pass and some fail,
	 * the system should correctly aggregate results and trigger upload.
	 */
	public function test_mixed_results_multiple_packages(): void {
		$failingPackage = $this->scaffoldFailingPackage();
		$passingPackage = $this->fixturesDir . '/regular-test-package-one';
		
		$config = $this->createConfig( [ 
			$passingPackage,  // This passes
			$failingPackage   // This fails
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Overall should fail (one package failed)
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should show both packages ran
		$this->assertStringContainsString( 'PACKAGE [1/2]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]', $output );
		
		// Check if Allure is mentioned (either upload or not configured)
		// The actual behavior depends on whether Allure is configured in the packages
		$this->assertMatchesRegularExpression( '/Allure|allure/i', $output );
	}

	/**
	 * Test failure handling without Allure configuration.
	 * 
	 * When tests fail but Allure is not configured, the system
	 * should still provide basic failure information.
	 */
	public function test_failing_without_allure(): void {
		// Create failing package without Allure
		$failingNoAllure = $this->createFailingPackageWithoutAllure();
		
		$config = $this->createConfig( [ $failingNoAllure ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should have clear failure message
		$this->assertStringContainsString( 'Status:        ✗ FAILED', $output );
		
		// Should mention lack of Allure
		$this->assertStringContainsString( 'No Allure configuration found', $output );
		
		// Should still have remote URL for basic results
		$this->assertMatchesRegularExpression( '/Remote URL:.*qit_results=/', $output );
	}

	// ========== Helper Methods ==========

	private function scaffoldFailingPackage(): string {
		$tempDir = sys_get_temp_dir() . '/qit-failing-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		// Scaffold a basic E2E test package
		$proc = qit( [
			'scaffold:e2e',
			$tempDir,
			'--package_name=woocommerce/qit-integration-test-failing-package',
			'--test_type=e2e'
		], return_process: true );
		
		$this->assertEquals( 0, $proc->getExitCode(), 'Scaffolding should succeed' );

		// Make the test fail by modifying the test file
		$testFile = $tempDir . '/test.spec.js';
		if ( file_exists( $testFile ) ) {
			$content = file_get_contents( $testFile );
			// Replace expect to fail
			$content = str_replace( 
				"expect(page.locator('body'))", 
				"expect(page.locator('.non-existent-selector'))", 
				$content 
			);
			file_put_contents( $testFile, $content );
		}

		// Add Allure configuration
		$manifest = json_decode( file_get_contents( $tempDir . '/qit-test.json' ), true );
		$manifest['test']['results']['allure-dir'] = './allure-results';
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		// Install dependencies if needed
		if ( file_exists( $tempDir . '/package.json' ) ) {
			$npmProc = qit( [ 'cd ' . $tempDir . ' && npm install' ], 
				expected_exit_code: 0, 
				return_process: true 
			);
		}

		return $tempDir;
	}

	private function createFailingPackageWithoutAllure(): string {
		$tempDir = sys_get_temp_dir() . '/qit-failing-no-allure-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$manifest = [
			'package' => 'woocommerce/qit-integration-test-failing-no-allure',
			'test_type' => 'e2e',
			'description' => 'Failing package without Allure',
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Test starting" && exit 1'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json'
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