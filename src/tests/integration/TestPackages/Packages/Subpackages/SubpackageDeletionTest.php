<?php

namespace QIT\IntegrationTests\TestPackages\Packages\Subpackages;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

/**
 * Test that package:delete properly handles packages with subpackages.
 * 
 * When a parent package is deleted, its subpackages should also be deleted
 * to avoid orphaned subpackages in the database.
 */
class SubpackageDeletionTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = __DIR__ . '/../../../../fixtures/test-packages';
		
		// Clean up any leftover test packages before running
		// The cleanup helper checks for QIT_SELF_TESTS env var which is set by bootstrap.php
		TestCleanupHelper::cleanup_all_test_packages();
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		// Clean up any test packages created during the test
		TestCleanupHelper::cleanup_all_test_packages();
		
		parent::tearDown();
	}

	/**
	 * Test that deleting a parent package also deletes its subpackages.
	 */
	public function test_delete_parent_package_removes_subpackages(): void {
		// Create a test package with subpackages
		$tempDir = sys_get_temp_dir() . '/qit_delete_subpkg_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$packageDir = $tempDir . '/parent-with-subpackages';
		mkdir( $packageDir, 0755, true );
		
		// Create a parent package with unique name using our standard test prefix
		$parentName = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'delete-parent' );
		
		// Copy existing working manifest and modify
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/subpackages-parent' ) . "/* " . escapeshellarg( $packageDir ) );
		
		// Update the manifest with our test-specific values
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $parentName;
		
		// Give subpackages unique names using our standard test prefix
		$subpackage1 = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'delete-sub1' );
		$subpackage2 = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'delete-sub2' );
		
		// Replace the default subpackages with our uniquely named ones
		$oldSubpackages = $manifest['subpackages'];
		$manifest['subpackages'] = [];
		$manifest['subpackages'][$subpackage1] = reset($oldSubpackages); // Use first subpackage config
		$manifest['subpackages'][$subpackage2] = next($oldSubpackages);  // Use second subpackage config
		
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Remember subpackage IDs for verification
		$subpackageIds = array_keys( $manifest['subpackages'] );
		
		// Publish the parent package with subpackages
		$publishProc = qit( [
			'package:publish',
			$packageDir,
			'1.0.0'
		], return_process: true );
		
		if ( strpos( $publishProc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publishProc->getExitCode(),
			'Should publish parent package with subpackages. Output: ' . $publishProc->getOutput() );
		
		// Verify parent and subpackages exist in the database
		$parentExists = $this->checkPackageExists( $parentName . ':1.0.0' );
		$this->assertTrue( $parentExists, 'Parent package should exist in database after publish' );
		
		foreach ( $subpackageIds as $subId ) {
			$subExists = $this->checkPackageExists( $subId . ':1.0.0' );
			$this->assertTrue( $subExists, "Subpackage $subId:1.0.0 should exist in database after publish" );
		}
		
		// Now delete the parent package
		$deleteProc = qit( [
			'package:delete',
			$parentName . ':1.0.0',
			'--yes'
		], return_process: true );
		
		$this->assertEquals( 0, $deleteProc->getExitCode(),
			'Should delete parent package successfully. Output: ' . $deleteProc->getOutput() );
		
		// Verify parent package is gone
		$parentExists = $this->checkPackageExists( $parentName . ':1.0.0' );
		$this->assertFalse( $parentExists, 'Parent package should NOT exist in database after delete' );
		
		// CRITICAL: Verify subpackages are also gone
		foreach ( $subpackageIds as $subId ) {
			$subExists = $this->checkPackageExists( $subId . ':1.0.0' );
			$this->assertFalse( $subExists, 
				"Subpackage $subId:1.0.0 should NOT exist in database after parent delete (cascade delete)" );
		}
	}
	
	/**
	 * Test that deleting a specific version of parent deletes only that version's subpackages.
	 */
	public function test_delete_specific_version_removes_only_that_versions_subpackages(): void {
		// Create a test package
		$tempDir = sys_get_temp_dir() . '/qit_delete_version_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$packageDir = $tempDir . '/versioned-parent';
		mkdir( $packageDir, 0755, true );
		
		// Create a parent package with unique name using our standard test prefix
		$parentName = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'delete-version' );
		$subpackageName = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'delete-version-sub' );
		
		// Create manifest for v1.0.0
		$manifest = [
			'package' => $parentName,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 'echo "Version 1.0.0"' ]
				],
				'results' => [
					'ctrf-json' => 'results.json',
					'blob-dir' => 'blob'
				]
			],
			'subpackages' => [
				$subpackageName => [
					'test' => [
						'phases' => [
							'run' => [ 'echo "Subpackage v1.0.0"' ]
						]
					]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish v1.0.0
		$publish1 = qit( [
			'package:publish',
			$packageDir,
			'1.0.0'
		], return_process: true );
		
		if ( strpos( $publish1->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publish1->getExitCode(), 'Should publish v1.0.0' );
		
		// Modify and publish v2.0.0
		$manifest['test']['phases']['run'] = [ 'echo "Version 2.0.0"' ];
		$manifest['subpackages'][$subpackageName]['test']['phases']['run'] = [ 'echo "Subpackage v2.0.0"' ];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		$publish2 = qit( [
			'package:publish',
			$packageDir,
			'2.0.0'
		], return_process: true );
		
		$this->assertEquals( 0, $publish2->getExitCode(), 'Should publish v2.0.0' );
		
		// Verify both versions exist
		$this->assertTrue( $this->checkPackageExists( $parentName . ':1.0.0' ), 'v1.0.0 should exist' );
		$this->assertTrue( $this->checkPackageExists( $parentName . ':2.0.0' ), 'v2.0.0 should exist' );
		$this->assertTrue( $this->checkPackageExists( $subpackageName . ':1.0.0' ), 'Subpackage v1.0.0 should exist' );
		$this->assertTrue( $this->checkPackageExists( $subpackageName . ':2.0.0' ), 'Subpackage v2.0.0 should exist' );
		
		// Delete v1.0.0 only
		$deleteProc = qit( [
			'package:delete',
			$parentName . ':1.0.0',
			'--yes'
		], return_process: true );
		
		$this->assertEquals( 0, $deleteProc->getExitCode(), 'Should delete v1.0.0' );
		
		// Verify v1.0.0 is gone but v2.0.0 remains
		$this->assertFalse( $this->checkPackageExists( $parentName . ':1.0.0' ), 'Parent v1.0.0 should be deleted' );
		$this->assertTrue( $this->checkPackageExists( $parentName . ':2.0.0' ), 'Parent v2.0.0 should still exist' );
		
		// CRITICAL: Verify v1.0.0 subpackage is gone but v2.0.0 subpackage remains
		$this->assertFalse( $this->checkPackageExists( $subpackageName . ':1.0.0' ), 
			'Subpackage v1.0.0 should be deleted with parent v1.0.0' );
		$this->assertTrue( $this->checkPackageExists( $subpackageName . ':2.0.0' ), 
			'Subpackage v2.0.0 should still exist' );
		
		// Clean up v2.0.0
		qit( [
			'package:delete',
			$parentName . ':2.0.0',
			'--yes'
		], return_process: true );
	}
	
	/**
	 * Helper: Check if a package exists and is active in the database.
	 * Soft-deleted packages (status='deleted') are considered as not existing.
	 */
	private function checkPackageExists( string $packageId ): bool {
		$escapedId = addslashes( $packageId );
		$cmd = sprintf(
			'docker exec cd_php wp eval \'global $wpdb; echo count($wpdb->get_results("SELECT 1 FROM wp_qit_test_packages WHERE package_id = \\"%s\\" AND status != \\"deleted\\"", ARRAY_A));\' 2>&1',
			$escapedId
		);
		$result = trim( shell_exec( $cmd ) );
		return $result === '1';
	}
}