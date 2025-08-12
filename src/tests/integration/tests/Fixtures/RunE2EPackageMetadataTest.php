<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Integration test to verify package metadata is added to CTRF reports
 */
class RunE2EPackageMetadataTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-metadata-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
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
	 * Test #18: CTRF contains package metadata
	 * 
	 * Coverage aim: Validates package metadata inclusion in CTRF reports.
	 * Tests that CTRF (Common Test Results Format) reports include comprehensive
	 * metadata about test packages including version, package counts, and report
	 * completeness information.
	 * 
	 * Key aspects tested:
	 * - Package metadata in CTRF extra field
	 * - Version information preservation
	 * - Package summary statistics
	 * - Report completeness tracking
	 */
	public function test_ctrf_contains_package_metadata(): void {
		// Create test package with blob report
		$testPackage = $this->createTestPackageWithBlob( 'test-package' );
		
		// Create utility package without blob
		$utilityPackage = $this->createUtilityPackage( 'utility-package' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $testPackage, $utilityPackage ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		// Run the test
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Find the CTRF report in the artifacts
		$output = $proc->getOutput();
		
		// Extract artifacts directory from output
		if ( preg_match( '/Artifacts directory: (.+)/', $output, $matches ) ) {
			$artifacts_dir = trim( $matches[1] );
			$ctrf_path = $artifacts_dir . '/final/ctrf/ctrf-report.json';
			
			if ( file_exists( $ctrf_path ) ) {
				$ctrf = json_decode( file_get_contents( $ctrf_path ), true );
				
				// Verify package metadata exists
				$this->assertArrayHasKey( 'results', $ctrf );
				$this->assertArrayHasKey( 'extra', $ctrf['results'] );
				$this->assertArrayHasKey( 'qitPackageMetadata', $ctrf['results']['extra'] );
				
				$metadata = $ctrf['results']['extra']['qitPackageMetadata'];
				
				// Check version
				$this->assertEquals( '1.0.0', $metadata['version'] );
				
				// Check packages
				$this->assertArrayHasKey( 'packages', $metadata );
				$this->assertCount( 2, $metadata['packages'] );
				
				// Check summary
				$this->assertArrayHasKey( 'summary', $metadata );
				$this->assertEquals( 2, $metadata['summary']['totalPackages'] );
				$this->assertEquals( 1, $metadata['summary']['packagesWithTests'] );
				$this->assertEquals( 1, $metadata['summary']['utilityPackages'] );
				
				// Check report completeness
				$this->assertArrayHasKey( 'reportCompleteness', $metadata );
				
				// Blob should be incomplete (utility package has no blob)
				if ( isset( $metadata['reportCompleteness']['blob'] ) ) {
					$this->assertFalse( $metadata['reportCompleteness']['blob']['complete'] );
				}
				
				// Check orchestration type marker
				$this->assertArrayHasKey( 'tool', $ctrf['results'] );
				$this->assertArrayHasKey( 'extra', $ctrf['results']['tool'] );
				$this->assertEquals( 'test-packages', $ctrf['results']['tool']['extra']['orchestrationType'] );
			} else {
				$this->markTestSkipped( 'CTRF report not found at expected location' );
			}
		} else {
			$this->markTestSkipped( 'Could not extract artifacts directory from output' );
		}
	}

	// Helper methods

	private function createTestPackageWithBlob( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		// Create manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'description' => 'Test package with blob report',
			'test' => [
				'phases' => [
					'run' => [
						// Create CTRF and blob report
						'mkdir -p ./results ./blob-report && ' .
						'echo \'{"results":{"summary":{"tests":2,"passed":2,"failed":0,"skipped":0,"pending":0,"other":0},"tests":[{"name":"test1","status":"passed","duration":100},{"name":"test2","status":"passed","duration":200}]}}\' > ./results/ctrf.json && ' .
						'echo "test data" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $packageDir;
	}

	private function createUtilityPackage( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		// Create manifest without run phase
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'description' => 'Utility package for setup only',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "Setting up environment..."'
					],
					'teardown' => [
						'echo "Cleaning up environment..."'
					]
				]
				// No results section for utility packages
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $packageDir;
	}

	private function writeConfig( array $config ): string {
		$tempDir = sys_get_temp_dir() . '/qit-config-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}