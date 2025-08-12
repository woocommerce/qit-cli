<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test E2E command using the two real fixture packages.
 * 
 * Fixtures:
 * - regular-test-package-one: Has Allure configured
 * - regular-test-package-two: Has Allure configured
 */
class RunE2EWithFixturesTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
	}

	protected function tearDown(): void {
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				exec( "rm -rf " . escapeshellarg( $dir ) );
			}
		}
		parent::tearDown();
	}

	/**
	 * Test single package with Allure - tests pass, no upload
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
		$this->assertStringContainsString( 
			'Allure report available locally (not uploaded - tests passed)', 
			$output
		);
		
		// Extract test run ID from remote URL
		$testRunId = $this->extractTestRunId( $output );
		$this->assertNotNull( $testRunId, 'Should have a test run ID in remote URL' );
		
		// Verify local report is accessible
		$this->assertLocalReportWorks();
		
		// Verify remote test run is visible in Manager
		$this->assertRemoteTestRunExists( $testRunId );
	}

	/**
	 * Test both packages with Allure - all configured properly
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
		$this->assertStringContainsString( 
			'✓ Allure configured (uploads on test failure)', 
			$output
		);
		
		// Should NOT upload since tests passed
		$this->assertStringContainsString(
			'Allure report available locally (not uploaded - tests passed)',
			$output
		);
	}

	/**
	 * Test mixed Allure configuration - one with, one without
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
		$this->assertStringContainsString( 
			'⚠ Allure incomplete (will not upload)', 
			$output 
		);
		$this->assertStringContainsString( 
			'1 of 2 packages have Allure configured', 
			$output 
		);
	}

	/**
	 * Test no Allure configuration at all
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
		$this->assertStringContainsString( 
			'ℹ No Allure configuration found', 
			$output 
		);
		$this->assertStringContainsString(
			'Add "allure-dir" to qit-test.json for failure debugging',
			$output
		);
	}

	// ============= Helper Methods =============

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

	private function createPackageWithoutAllure( string $name = 'package-no-allure' ): string {
		$tempDir = sys_get_temp_dir() . '/qit-fixture-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$packageDir = $tempDir . '/' . $name;
		
		// Copy fixture but remove allure-dir
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/regular-test-package-two' ) . " " . escapeshellarg( $packageDir ) );
		
		// Modify manifest to remove allure-dir
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		unset( $manifest['test']['results']['allure-dir'] );
		$manifest['package'] = 'woocommerce/' . $name;
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $packageDir;
	}
	
	/**
	 * Extract test run ID from the remote URL in output
	 */
	private function extractTestRunId( string $output ): ?string {
		// Look for pattern like: qit_results=1409483.hgssojkQPM3fALbUC6tY3QRCnjHW2JrP1iWOkqzF0Ohjv4osjezjZi4jL4QYc07n
		if ( preg_match( '/qit_results=(\d+)\./', $output, $matches ) ) {
			return $matches[1];
		}
		return null;
	}
	
	/**
	 * Verify local report can be opened with report command
	 */
	private function assertLocalReportWorks(): void {
		$proc = qit( [
			'report',
			'--dir_only'  // Just output the directory path
		], return_process: true );
		
		$output = $proc->getOutput();
		
		// Should return a valid directory path
		$this->assertEquals( 0, $proc->getExitCode(), 'report command should find the local report' );
		$this->assertNotEmpty( trim( $output ), 'Should output a directory path' );
		
		// Verify the path exists
		$reportDir = trim( $output );
		$this->assertDirectoryExists( $reportDir, 'Report directory should exist' );
	}
	
	/**
	 * Verify test run exists in Manager via API
	 */
	private function assertRemoteTestRunExists( string $testRunId ): void {
		// Try to get the test run details
		$proc = qit( [
			'get',
			$testRunId
		], return_process: true );
		
		$output = $proc->getOutput();
		
		// Should return test run details
		$this->assertEquals( 0, $proc->getExitCode(), 'Should be able to get test run from Manager' );
		$this->assertStringContainsString( 
			$testRunId, 
			$output,
			'Test run ID should appear in get output'
		);
		
		// Also verify it appears in list
		$proc = qit( [
			'list-tests'
		], return_process: true );
		
		$output = $proc->getOutput();
		
		// Recent test should appear in list
		$this->assertStringContainsString( 
			$testRunId, 
			$output,
			'Test run should appear in recent list'
		);
	}
}