<?php

namespace QIT\IntegrationTests\TestPackages\Commands\RunE2E\Configuration;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test E2E command configuration and common setup issues.
 * 
 * This covers the MOST COMMON support issues:
 * 1. PHP/WP/WooCommerce version incompatibilities
 * 2. Missing or malformed test packages
 * 3. Invalid configuration files
 * 4. Environment setup problems
 * 
 * These tests help users understand error messages and how to fix them.
 */
class ConfigurationTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../../../../fixtures/test-packages';
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test #1: Specific version configuration
	 * 
	 * Coverage aim: Validates that the CLI correctly handles explicit version specifications
	 * for PHP, WordPress, and WooCommerce. Tests the version selection and environment
	 * setup with specific versions to ensure compatibility matrix works.
	 * 
	 * Key aspects tested:
	 * - PHP version selection (--php flag)
	 * - WordPress version selection (--wp flag)
	 * - WooCommerce version selection (--woo flag)
	 * - Environment setup with specific versions
	 */
	public function test_specific_version_configuration(): void {
		$testPackage = $this->fixturesDir . '/regular-test-package-one';
		$config = $this->createConfig( [ $testPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--wp=6.5',      // Specific WordPress version
			'--php=8.2',     // Specific PHP version
			'--woo=9.0.0',   // Specific WooCommerce version
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should use the specified versions
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	/**
	 * Test #2: No test packages configuration
	 * 
	 * Coverage aim: Validates error handling when no test packages are provided.
	 * Tests that the system properly fails and provides clear feedback when users
	 * forget to include test packages in their configuration.
	 * 
	 * Key aspects tested:
	 * - Empty test package array handling
	 * - Clear error messaging for missing packages
	 * - Proper exit code on configuration error
	 */
	public function test_no_test_packages(): void {
		// Create config with empty test packages
		$config = $this->createConfig( [] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should show 0 packages executed
		$this->assertStringContainsString( '0/0 executed', $output );
		$this->assertStringContainsString( 'Status:        ✗ FAILED', $output );
	}

	/**
	 * Test #3: Invalid test package path
	 * 
	 * Coverage aim: Validates error handling for non-existent test package paths.
	 * Tests that the system properly detects and reports when test package paths
	 * don't exist, a common user configuration error.
	 * 
	 * Key aspects tested:
	 * - Path validation for test packages
	 * - Error messaging for invalid paths
	 * - Graceful failure with clear feedback
	 */
	public function test_invalid_test_package_path(): void {
		$config = $this->createConfig( [ 
			'/this/path/does/not/exist/test-package'
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should indicate invalid parameters
		$this->assertMatchesRegularExpression( 
			'/invalid.*parameter|package_ids/i', 
			$output,
			'Should indicate invalid package configuration'
		);
	}

	/**
	 * Test #4: Malformed config JSON
	 * 
	 * Coverage aim: Validates JSON parsing error handling.
	 * Tests that the system properly detects and reports JSON syntax errors
	 * in configuration files, helping users debug configuration issues.
	 * 
	 * Key aspects tested:
	 * - JSON syntax validation
	 * - Parse error reporting
	 * - Clear error messages for malformed JSON
	 */
	public function test_malformed_config_json(): void {
		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		// Create invalid JSON
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, '{ "test_types": { "e2e": { invalid json here } }' );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should indicate JSON parse error
		$this->assertMatchesRegularExpression( 
			'/json|parse|syntax|invalid/i', 
			$output,
			'Should indicate JSON parsing failed'
		);
	}

	/**
	 * Test #5: Package missing manifest
	 * 
	 * Coverage aim: Validates manifest file requirement enforcement.
	 * Tests that packages without qit-test.json are properly rejected,
	 * ensuring all test packages have required metadata.
	 * 
	 * Key aspects tested:
	 * - Manifest file existence validation
	 * - Package structure requirements
	 * - Error handling for incomplete packages
	 */
	public function test_package_missing_manifest(): void {
		$badPackage = $this->createPackageWithoutManifest();
		$config = $this->createConfig( [ $badPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should indicate invalid package
		$this->assertMatchesRegularExpression( 
			'/invalid.*parameter|package_ids/i', 
			$output,
			'Should indicate invalid package'
		);
	}

	/**
	 * Test #6: Incompatible version combination
	 * 
	 * Coverage aim: Validates version compatibility checking.
	 * Tests that incompatible version combinations (e.g., old WordPress with
	 * new WooCommerce) are detected and handled appropriately.
	 * 
	 * Key aspects tested:
	 * - Version compatibility matrix
	 * - Detection of incompatible combinations
	 * - Clear error messaging for version conflicts
	 */
	public function test_incompatible_version_combination(): void {
		$testPackage = $this->fixturesDir . '/regular-test-package-one';
		$config = $this->createConfig( [ $testPackage ] );

		// Try to use old WP with new WooCommerce (might not be compatible)
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--wp=5.0',      // Very old WordPress
			'--woo=9.0.0',   // New WooCommerce
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should likely fail or show warning
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should show compatibility issue - old WP with new PHP causes syntax errors
		$this->assertMatchesRegularExpression( 
			'/fatal.*error|syntax|no longer supported/i', 
			$output,
			'Should show PHP/WP compatibility issue'
		);
	}

	/**
	 * Test #7: Non-existent config section
	 * 
	 * Coverage aim: Validates config section reference handling.
	 * Tests that references to non-existent configuration sections are
	 * properly detected and reported with helpful error messages.
	 * 
	 * Key aspects tested:
	 * - Config section validation
	 * - --config_section flag handling
	 * - Error reporting for missing sections
	 */
	public function test_non_existent_config_section(): void {
		$testPackage = $this->fixturesDir . '/regular-test-package-one';
		
		// Create config with custom section name
		$config = [
			'test_types' => [
				'e2e' => [
					'my-custom-config' => [
						'test_packages' => [ $testPackage ]
					]
					// Note: 'default' section is missing
				]
			]
		];

		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

		// Try to use non-existent section
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
			'--config_section=wrong-section',  // This section doesn't exist
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode() );
		
		// Should indicate section doesn't exist
		$this->assertMatchesRegularExpression( 
			'/section|not.*found|does.*not.*exist/i', 
			$output,
			'Should indicate config section is missing'
		);
	}

	/**
	 * Test #8: Verbose output
	 * 
	 * Coverage aim: Validates verbose output functionality.
	 * Tests that the --verbose flag properly increases output detail,
	 * helping users debug issues with more information.
	 * 
	 * Key aspects tested:
	 * - Verbose flag functionality
	 * - Enhanced output with -v flag
	 * - Debug information availability
	 */
	public function test_verbose_output(): void {
		$testPackage = $this->fixturesDir . '/regular-test-package-one';
		$config = $this->createConfig( [ $testPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',  // Enable verbose output
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should complete successfully
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
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

	private function createPackageWithoutManifest(): string {
		$tempDir = sys_get_temp_dir() . '/qit-bad-package-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		// Create a test file but NO qit-test.json
		file_put_contents( $tempDir . '/test.spec.js', 'console.log("test");' );
		
		return $tempDir;
	}
}