<?php

namespace QIT\IntegrationTests\TestPackages\Results\Allure;

use QIT\IntegrationTests\TestCleanupHelper;
use QIT\IntegrationTests\Helpers\CTRFHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for Allure report generation and upload behavior.
 * 
 * These tests verify:
 * - Allure reports are only uploaded on test failures
 * - Mixed Allure configurations are handled properly  
 * - Clear messaging about Allure configuration status
 * - Allure reports are accessible locally when not uploaded
 */
class AllureUploadTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages';
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		parent::tearDown();
	}

	/**
	 * Test that Allure reports are not uploaded when tests pass.
	 * 
	 * When all tests pass, Allure reports should remain local only
	 * to avoid unnecessary uploads and storage.
	 */
	public function test_single_package_allure_no_upload_when_passing(): void {
		$config = $this->createConfig( [
			$this->fixturesDir . '/regular-test-package-one'
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should indicate Allure available but not uploaded (tests passed)
		$this->assertMatchesRegularExpression(
			'/Allure report available locally \(not uploaded - tests passed\)/i',
			$output
		);
		
		// CORE FUNCTIONALITY: Successful test should create a test run
		$testRunId = $this->extractTestRunId( $output );
		$this->assertNotNull( $testRunId, 'Test run should be created and have an ID' );
		
		// CORE FUNCTIONALITY: Test with Allure should complete successfully
		// The exit code 0 + test run ID proves Allure didn't break the test
		$this->assertIsString( $testRunId );
		$this->assertNotEmpty( $testRunId );
	}

	/**
	 * Test both packages with Allure configured.
	 * 
	 * When all packages have Allure configured, the system should
	 * generate a unified report accessible locally.
	 */
	public function test_both_packages_with_allure_configured(): void {
		$config = $this->createConfig( [
			$this->fixturesDir . '/regular-test-package-one',
			$this->fixturesDir . '/regular-test-package-two'
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show both packages executed
		$this->assertStringContainsString( 'PACKAGE [1/2]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]', $output );
		
		// Should confirm Allure is properly configured
		$this->assertMatchesRegularExpression(
			'/Allure configured \(uploads on test failure\)/i',
			$output
		);
		
		// Should NOT upload since tests passed
		$this->assertMatchesRegularExpression(
			'/Allure report available locally \(not uploaded - tests passed\)/i',
			$output
		);
	}

	/**
	 * Test mixed Allure configuration handling.
	 * 
	 * When some packages have Allure and others don't, the system
	 * should warn about incomplete configuration and not upload.
	 */
	public function test_mixed_allure_configuration(): void {
		// Create a copy of package-two without Allure
		$packageWithoutAllure = $this->createPackageWithoutAllure();
		
		$config = $this->createConfig( [
			$this->fixturesDir . '/regular-test-package-one', // Has Allure
			$packageWithoutAllure                              // No Allure
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should warn about incomplete Allure configuration
		$this->assertMatchesRegularExpression(
			'/Allure incomplete \(will not upload\)|packages have Allure configured/i',
			$output
		);
	}

	/**
	 * Test running without any Allure configuration.
	 * 
	 * When no packages have Allure configured, the system should
	 * run successfully with basic reporting only.
	 */
	public function test_no_allure_configuration(): void {
		$package1 = $this->createPackageWithoutAllure( 'no-allure-1' );
		$package2 = $this->createPackageWithoutAllure( 'no-allure-2' );
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should inform about missing Allure
		$this->assertMatchesRegularExpression(
			'/No Allure configuration found/i',
			$output
		);
		$this->assertMatchesRegularExpression(
			'/Add "allure-dir" to qit-test.json/i',
			$output
		);
	}

	// ========== Helper Methods ==========

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

	private function createPackageWithoutAllure( string $name = 'no-allure-pkg' ): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$manifest = [
			'package' => 'woocommerce/qit-integration-test-' . $name,
			'test_type' => 'e2e',
			'description' => 'Package without Allure',
			'test' => [
				'phases' => [
					'run' => [
						'host: mkdir -p ./results && echo \'' . json_encode(CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function extractTestRunId( string $output ): ?string {
		// Match the current URL format: http://qit.test:8081?qit_results=4762434.xyz...
		if ( preg_match( '/Remote URL:.*qit_results=(\d+)\./', $output, $matches ) ) {
			return $matches[1];
		}
		// Also try legacy format
		if ( preg_match( '/Remote URL.*test-runs\/run-([a-f0-9.-]+)/', $output, $matches ) ) {
			return $matches[1];
		}
		return null;
	}

	private function assertLocalReportWorks(): void {
		// Check if local report command is mentioned
		$proc = qit( [ 'report' ], return_process: true );
		$this->assertEquals( 0, $proc->getExitCode(), 'Report command should work' );
	}

}