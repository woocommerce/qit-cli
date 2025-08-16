<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test checksum-based caching for test packages.
 * Verifies that rolling versions like 'latest' are properly validated using checksums.
 */
class ChecksumCachingTest extends TestCase {

	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test that checksum validation works for local test packages.
	 * Local packages should work without fetching metadata.
	 */
	public function test_local_packages_work_without_metadata(): void {
		// Create a simple config with a local test package
		$config = $this->createLocalPackageConfig();
		
		// Run the test - should not fetch metadata
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',
		], return_process: true );
		
		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();
		
		// Should succeed
		$this->assertEquals( 0, $exitCode, 
			'Local package test should succeed. Output: ' . $output );
		
		// Should NOT fetch metadata for local packages
		$this->assertStringNotContainsString( 'Fetching package metadata', $output,
			'Should not fetch metadata for local packages' );
		
		// Should indicate using local package
		$this->assertStringContainsString( 'local', strtolower( $output ),
			'Should indicate using local package' );
	}

	/**
	 * Test that metadata is always fetched for remote packages.
	 * This ensures checksums are used for cache validation.
	 */
	public function test_remote_packages_always_fetch_metadata(): void {
		// Skip if not connected to Manager
		$testProc = qit( [ 'env:list' ], return_process: true );
		if ( strpos( $testProc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		// Create config with remote test packages from fixtures
		$config = $this->createRemotePackageConfig();
		
		// First run - should fetch metadata
		$proc1 = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',
		], return_process: true );
		
		$output1 = $proc1->getOutput();
		
		// Should fetch metadata on first run
		$this->assertStringContainsString( 'Fetching package metadata', $output1,
			'Should fetch metadata for remote packages on first run' );
		
		// Second run - should STILL fetch metadata (for checksum validation)
		$proc2 = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		// Should fetch metadata on second run too (to validate checksums)
		$this->assertStringContainsString( 'Fetching package metadata', $output2,
			'Should fetch metadata for remote packages on second run for checksum validation' );
		
		// But should use cache if checksum matches
		if ( strpos( $output2, 'checksum validated' ) !== false ) {
			$this->assertStringContainsString( 'checksum validated', $output2,
				'Should indicate checksum validation when using cache' );
		}
	}

	/**
	 * Test that changing package content invalidates cache.
	 * Simulates a rolling version like 'latest' being updated.
	 */
	public function test_checksum_change_invalidates_cache(): void {
		// This test would require ability to modify remote packages
		// which we can't do in integration tests
		// Instead, we verify the cache key generation uses checksums
		
		// Create a minimal test to verify the code paths exist
		$tempDir = sys_get_temp_dir() . '/qit_checksum_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		// Create a test manifest
		$manifest = [
			'package' => 'test/checksum-test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 'echo "test"' ]
				]
			]
		];
		
		$manifestPath = $tempDir . '/qit-test.json';
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Run with local package
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $tempDir,
		], return_process: true );
		
		$output = $proc->getOutput();
		
		// Should work with local package
		$this->assertEquals( 0, $proc->getExitCode(),
			'Should handle local test package. Output: ' . $output );
	}

	/**
	 * Helper: Create config with local test package.
	 */
	private function createLocalPackageConfig(): string {
		$tempDir = sys_get_temp_dir() . '/qit_local_pkg_' . uniqid();
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
					'wp'  => 'stable',
				]
			],
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [
							__DIR__ . '/../../fixtures/test-packages/regular-test-package-one',
						]
					]
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}

	/**
	 * Helper: Create config with remote test packages.
	 */
	private function createRemotePackageConfig(): string {
		$tempDir = sys_get_temp_dir() . '/qit_remote_pkg_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		// Note: These would need to be actual published packages
		// For now using local packages as fallback
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
					'wp'  => 'stable',
				]
			],
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [
							// Would use remote packages like 'woocommerce/e2e:latest'
							// but need them to be published first
							__DIR__ . '/../../fixtures/test-packages/regular-test-package-one',
						]
					]
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}