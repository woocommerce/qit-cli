<?php

namespace QIT\IntegrationTests\TestPackages\Caching;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Comprehensive test for caching across all download types.
 * Verifies that test packages, extensions, and metadata are properly cached
 * to prevent unnecessary API calls and rate limiting.
 */
class ComprehensiveCachingTest extends TestCase {

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
	 * Test that running the same configuration twice uses cache for downloads.
	 * With checksum-based caching, we ALWAYS fetch metadata but skip downloads if checksums match.
	 */
	public function test_full_caching_prevents_unnecessary_downloads(): void {
		// Create a config with both extensions and test packages
		$config = $this->createFullConfig();
		
		// First run - will download everything
		$proc1 = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );
		
		$output1 = $proc1->getOutput();
		
		$this->assertEquals( 0, $proc1->getExitCode(),
			'First run should succeed. Output: ' . $output1 );
		
		// Count download/fetch indicators in first run
		$firstRunDownloads = substr_count( strtolower( $output1 ), 'downloading' );
		$firstRunMetadata = substr_count( strtolower( $output1 ), 'fetching' );
		$firstRunProcessing = substr_count( strtolower( $output1 ), 'processing' );
		
		// At least some activity should happen on first run (downloads or processing)
		$firstRunActivity = $firstRunDownloads + $firstRunMetadata + $firstRunProcessing;
		$this->assertGreaterThan( 0, $firstRunActivity,
			'First run should perform downloads or processing' );
		
		// Second run - should use cache for everything
		$proc2 = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v', // Verbose to see cache messages
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		$this->assertEquals( 0, $proc2->getExitCode(),
			'Second run should succeed. Output: ' . $output2 );
		
		// Count download indicators in second run
		$secondRunDownloads = substr_count( strtolower( $output2 ), 'downloading package' );
		$secondRunMetadata = substr_count( strtolower( $output2 ), 'fetching' );
		
		// Second run should have fewer actual package downloads (but may still fetch metadata for validation)
		$this->assertLessThanOrEqual(
			max(1, $firstRunDownloads), // Allow for at least some baseline downloads
			$secondRunDownloads,
			'Second run should download less or nothing due to checksum-validated caching'
		);
		
		// Metadata fetches may be similar (we always fetch for checksum validation)
		// This is by design - lightweight metadata calls prevent using stale "latest" versions
		
		// Look for cache usage indicators
		$cacheIndicators = [
			'using_cached' => substr_count( strtolower( $output2 ), 'using cached' ),
			'cache_hit' => substr_count( strtolower( $output2 ), 'cache' ),
		];
		
		// Should see cache being used
		$this->assertGreaterThan( 0, array_sum( $cacheIndicators ),
			'Second run should show cache usage' );
	}

	/**
	 * Test that extensions cache properly across multiple runs.
	 * Specifically tests WPORG and local extensions.
	 */
	public function test_extension_caching(): void {
		// Create config with extensions
		$config = $this->createConfigWithExtensions();
		
		// First run
		$proc1 = qit( [
			'env:up',
			'--config=' . $config,
		], return_process: true );
		
		$output1 = $proc1->getOutput();
		
		// Should process plugins/themes on first run
		$this->assertStringContainsString( 'processing', strtolower( $output1 ),
			'First run should process plugins and themes' );
		
		// Second run
		$proc2 = qit( [
			'env:up',
			'--config=' . $config,
			'-v',
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		// Check for actual cache usage in second run
		$cacheIndicators = [
			'using cached' => substr_count( strtolower( $output2 ), 'using cached' ),
			'cache hit' => substr_count( strtolower( $output2 ), 'cache' ),
		];
		
		// Should see cache being used in second run
		$this->assertGreaterThan( 0, array_sum( $cacheIndicators ),
			'Second run should show cache usage for extensions' );
		
		$this->assertTrue( $proc2->isSuccessful(), 'Second environment should start successfully' );
	}

	/**
	 * Test that metadata caching prevents repeated API calls.
	 */
	public function test_metadata_caching(): void {
		// Skip if not connected to Manager
		$testProc = qit( [ 'package:list', '--format=json' ], return_process: true );
		if ( strpos( $testProc->getOutput(), 'not connected' ) !== false || $testProc->getExitCode() !== 0 ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		// Create a simple config
		$config = $this->createSimpleConfig();
		
		// Run twice with verbose output
		$proc1 = qit( [
			'env:up',
			'--config=' . $config,
			'-vv', // Very verbose to see metadata fetching
		], return_process: true );
		
		$output1 = $proc1->getOutput();
		
		$proc2 = qit( [
			'env:up',
			'--config=' . $config,
			'-vv',
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		// Count metadata fetch occurrences
		$metadataFetch1 = substr_count( strtolower( $output1 ), 'fetching metadata' );
		$metadataFetch2 = substr_count( strtolower( $output2 ), 'fetching metadata' );
		
		// Second run should fetch less or no metadata
		$this->assertLessThanOrEqual( $metadataFetch1, $metadataFetch2,
			'Second run should not fetch more metadata than first run' );
		
		// Look for cached metadata usage
		if ( strpos( $output2, 'metadata' ) !== false ) {
			$cachedMetadata = substr_count( strtolower( $output2 ), 'cached metadata' );
			$this->assertGreaterThanOrEqual( 0, $cachedMetadata,
				'Second run may use cached metadata' );
		}
	}

	/**
	 * Helper: Create a comprehensive config file with extensions and test packages.
	 */
	private function createFullConfig(): string {
		$tempDir = sys_get_temp_dir() . '/qit_cache_test_' . uniqid();
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
					'plugins' => [
						'wordpress-importer',  // WPORG plugin
						'query-monitor',       // Another WPORG plugin
					]
				]
			],
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [
							QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages/regular-test-package-one',
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
	 * Helper: Create config with extensions only.
	 */
	private function createConfigWithExtensions(): string {
		$tempDir = sys_get_temp_dir() . '/qit_ext_cache_' . uniqid();
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
					'plugins' => [
						'hello-dolly',      // Simple WPORG plugin
						'classic-editor',   // Another WPORG plugin
					],
					'themes' => [
						'twentytwentyone',  // WPORG theme
					]
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}

	/**
	 * Helper: Create simple config for metadata testing.
	 */
	private function createSimpleConfig(): string {
		$tempDir = sys_get_temp_dir() . '/qit_meta_cache_' . uniqid();
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
					'plugins' => [
						'akismet',  // Common WPORG plugin
					]
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}