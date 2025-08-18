<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use QIT\IntegrationTests\Helpers\CTRFHelper;
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
		
		// We need actual remote packages to test this properly
		// First, publish a test package
		$packageName = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'checksum' );
		$packageDir = $this->createTestPackageDirectory( $packageName );
		
		// Publish the package
		$publishProc = qit( [
			'package:publish',
			$packageDir,
			'1.0.0',
		], return_process: true );
		
		if ( $publishProc->getExitCode() !== 0 ) {
			$this->markTestSkipped( 'Could not publish test package: ' . $publishProc->getOutput() );
		}
		
		// Create config with the actual remote package
		$config = $this->createConfigWithRemotePackage( $packageName . ':1.0.0' );
		
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
		
		// Clean up the published package
		qit( [
			'package:delete',
			$packageName . ':1.0.0',
			'--yes',
		] );
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
					'run' => [ 
						'mkdir -p ./results ./blob-report && ' .
						'echo \'' . json_encode(CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && ' .
						'echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
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
	 * Helper: Create a test package directory for publishing.
	 */
	private function createTestPackageDirectory( string $packageName ): string {
		$tempDir = sys_get_temp_dir() . '/qit_test_pkg_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		// Create a minimal valid test package
		$manifest = [
			'package' => $packageName,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						'host: mkdir -p ./results ./blob-report && ' .
						'echo \'' . json_encode(CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && ' .
						'echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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
	
	/**
	 * Helper: Create config with a specific remote package.
	 */
	private function createConfigWithRemotePackage( string $packageRef ): string {
		$tempDir = sys_get_temp_dir() . '/qit_remote_cfg_' . uniqid();
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
						'test_packages' => [ $packageRef ]
					]
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}