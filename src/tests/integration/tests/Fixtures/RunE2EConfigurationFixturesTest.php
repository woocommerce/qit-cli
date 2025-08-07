<?php

namespace QIT\IntegrationTests\Fixtures;

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
class RunE2EConfigurationFixturesTest extends TestCase {

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
	 * Test with specific PHP/WP/WooCommerce versions
	 * This is important for compatibility testing
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
	 * Test with NO test packages - common misconfiguration
	 * Users often forget to include test packages
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
	 * Test with non-existent test package path
	 * Users often have wrong paths in their config
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
	 * Test with malformed qit.json config
	 * Users often have syntax errors in their JSON
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
	 * Test with test package missing manifest.json
	 * Common when users create their own test packages incorrectly
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
	 * Test with conflicting version requirements
	 * E.g., WooCommerce 9.0 requires WP 6.4+, but user specifies WP 6.0
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
	 * Test with custom config section that doesn't exist
	 * Users sometimes reference wrong config sections
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
	 * Test running with --verbose flag for more output
	 * Important for users debugging issues
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
		
		// Create a test file but NO manifest.json
		file_put_contents( $tempDir . '/test.spec.js', 'console.log("test");' );
		
		return $tempDir;
	}
}