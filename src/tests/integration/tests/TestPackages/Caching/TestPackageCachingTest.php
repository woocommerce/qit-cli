<?php

namespace QIT\IntegrationTests\TestPackages\Caching;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test that package caching prevents unnecessary API calls.
 * This is critical for avoiding rate limiting issues.
 */
class TestPackageCachingTest extends TestCase {

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
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test that running the same remote package twice uses cache on second run.
	 * This verifies that cache prevents API calls for download URLs.
	 */
	public function test_remote_package_caching_prevents_api_calls(): void {
		// First, publish a test package if not already published
		$packageDir = $this->fixturesDir . '/regular-test-package-one';
		$uniqueId = substr( uniqid(), 0, 8 );
		$packageName = 'woocommerce/qit-integration-test-cache-' . $uniqueId;  // No hyphen before ID
		
		// Create a copy with unique name
		$tempDir = sys_get_temp_dir() . '/qit_cache_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		exec( "cp -r " . escapeshellarg( $packageDir ) . " " . escapeshellarg( $tempDir ) );
		
		// Update package name
		$manifestPath = $tempDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish the package
		$publishProc = qit( [
			'package:publish',
			$tempDir,
			'1.0.0'
		], return_process: true );
		
		if ( strpos( $publishProc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publishProc->getExitCode(),
			'Package should publish successfully' );
		
		// Wait a moment for registry
		sleep( 1 );
		
		// First run - will download and cache
		$config1 = $this->createConfig();
		$proc1 = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $packageName . ':1.0.0',
			'--config=' . $config1,
		], return_process: true );
		
		$output1 = $proc1->getOutput();
		
		$this->assertEquals( 0, $proc1->getExitCode(),
			'First run should succeed' );
		
		// Should show fetching/downloading
		$this->assertStringContainsString( 'Fetching package metadata', $output1,
			'First run should fetch package metadata' );
		$this->assertStringContainsString( 'Downloading package: ' . $packageName . ':1.0.0', $output1,
			'First run should download the package' );
		
		// Second run - should use cache
		$config2 = $this->createConfig();
		$proc2 = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $packageName . ':1.0.0',
			'--config=' . $config2,
			'-v', // Verbose to see cache messages
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		$this->assertEquals( 0, $proc2->getExitCode(),
			'Second run should succeed' );
		
		// Should NOT show fetching download URLs for the cached package
		// Note: It might still fetch URLs for other things like plugins
		if ( strpos( $output2, 'Using cached package: ' . $packageName . ':1.0.0' ) !== false ) {
			// Good - explicitly shows using cache
			$this->assertStringContainsString( 'Using cached package', $output2,
				'Second run should use cached package' );
		} else {
			// At minimum, should not download the same package again
			$this->assertStringNotContainsString( 'Downloading package: ' . $packageName . ':1.0.0', $output2,
				'Second run should not re-download the package' );
		}
		
		// Clean up
		qit( [
			'package:delete',
			$packageName . ':1.0.0',
			'--yes'
		], return_process: true );
	}

	/**
	 * Test that multiple subpackages from same parent share cache.
	 * The parent artifact should only be downloaded once.
	 */
	public function test_subpackages_share_parent_cache(): void {
		// Publish parent with subpackages
		$packageDir = $this->fixturesDir . '/subpackages-parent';
		$publishProc = qit( [
			'package:publish',
			$packageDir,
			'5.0.0'
		], return_process: true );
		
		if ( strpos( $publishProc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		// First subpackage - will download parent
		$config1 = $this->createConfig();
		$proc1 = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=woocommerce/qit-integration-test-checkout:5.0.0',
			'--config=' . $config1,
		], return_process: true );
		
		$output1 = $proc1->getOutput();
		
		// Should download on first run
		$this->assertStringContainsString( 'Fetching package metadata', $output1,
			'First subpackage should fetch package metadata' );
		
		// Second subpackage - should use cached parent
		$config2 = $this->createConfig();
		$proc2 = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=woocommerce/qit-integration-test-cart:5.0.0',
			'--config=' . $config2,
			'-vv',
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		// Should use cache for second subpackage (same parent)
		if ( strpos( $output2, 'Using cached package' ) !== false ) {
			$this->assertStringContainsString( 'Using cached package', $output2,
				'Second subpackage should use cached parent' );
		} else {
			// At least should not re-download
			$this->assertStringNotContainsString( 'Downloading package: woocommerce/qit-integration-test-cart:5.0.0', $output2,
				'Second subpackage should not trigger new download' );
		}
		
		// Clean up
		qit( [
			'package:delete',
			'woocommerce/qit-integration-test-e2e-suite:5.0.0',
			'--yes'
		], return_process: true );
	}

	/**
	 * Helper: Create a temporary config file
	 */
	private function createConfig(): string {
		$tempDir = sys_get_temp_dir() . '/qit_cache_cfg_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$config = [
			'$schema'      => 'https://qit.woo.com/json-schema/qit',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => 'stable'
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}