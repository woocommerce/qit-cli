<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test E2E command failure scenarios - critical for 95% of users.
 * 
 * Most users will encounter test failures and need to understand:
 * 1. How failures are reported
 * 2. When Allure uploads happen
 * 3. What exit codes mean
 * 4. How to debug failures
 */
class RunE2EFailureFixturesTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
		
		// Note: For Allure upload to actually work (with GitHub webhook callback),
		// you need to use staging environment by renaming .env.staging to .env
		// For local testing without actual upload, the default .env (local) is fine
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
	 * Test that failing tests trigger Allure upload
	 * This is THE most important test - users need debugging info when tests fail
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
		$this->assertMatchesRegularExpression( '/Tests:.*1 failed/', $output );
		
		// Critical: Allure should upload because tests failed
		$this->assertStringContainsString( 
			'Uploading Allure report (tests failed)', 
			$output,
			'Allure should upload when tests fail'
		);
		
		// Should still have remote URL for debugging
		$this->assertMatchesRegularExpression( '/Remote URL:.*qit_results=/', $output );
	}

	/**
	 * Test mixed pass/fail with multiple packages
	 * Common scenario: some packages pass, some fail
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
		
		// Allure should upload (failure detected)
		$this->assertStringContainsString( 'Uploading Allure report', $output );
	}

	/**
	 * Test failure with missing Allure configuration
	 * User story: "My tests failed but I don't have Allure configured"
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
		
		// Should inform about missing Allure (important for debugging)
		$this->assertStringContainsString( 
			'ℹ No Allure configuration found', 
			$output 
		);
		
		// Should NOT attempt upload
		$this->assertStringNotContainsString( 'Uploading Allure', $output );
		
		// But should still have basic results
		$this->assertMatchesRegularExpression( '/Remote URL:.*qit_results=/', $output );
	}

	// ============= Helper Methods =============

	private function scaffoldFailingPackage(): string {
		$tempDir = sys_get_temp_dir() . '/qit-fixture-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$packageDir = $tempDir . '/failing-package';
		
		// Use the pre-created failing fixture
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/failing-test-package' ) . " " . escapeshellarg( $packageDir ) );
		
		// Copy package.json and playwright config from working package
		// This overwrites any incorrect package.json that might have ES modules configured
		$filesToCopy = [ 'package.json', 'package-lock.json', 'playwright.config.js' ];
		foreach ( $filesToCopy as $file ) {
			$source = $this->fixturesDir . '/regular-test-package-one/' . $file;
			if ( file_exists( $source ) ) {
				copy( $source, $packageDir . '/' . $file );
			}
		}
		
		// Scaffold node_modules if needed
		if ( ! is_dir( $packageDir . '/node_modules' ) ) {
			// Copy from a working package
			$sourceModules = $this->fixturesDir . '/regular-test-package-one/node_modules';
			if ( is_dir( $sourceModules ) ) {
				exec( "cp -r " . escapeshellarg( $sourceModules ) . " " . escapeshellarg( $packageDir . '/node_modules' ) );
			}
		}
		
		return $packageDir;
	}

	private function createFailingPackageWithoutAllure(): string {
		$package = $this->scaffoldFailingPackage();
		
		// Remove allure-dir from manifest
		$manifestPath = $package . '/qit-package.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		unset( $manifest['test']['results']['allure-dir'] );
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $package;
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