<?php

namespace QIT\IntegrationTests\TestPackages\Packages\Registry;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for package identifier uniqueness constraints.
 * 
 * Package identifiers must be globally unique within a version.
 * Format: namespace/package-name:version
 * 
 * This is a fundamental constraint that ensures:
 * - No ambiguity when requesting packages
 * - Clear ownership of package identifiers
 * - Proper versioning support
 */
class PackageUniqueIdentifierTest extends TestCase {
	
	private array $tempDirs = [];
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
	}
	
	protected function tearDown(): void {
		// Clean up temp directories
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$this->deleteDirectory( $dir );
			}
		}
		TestCleanupHelper::cleanup_all_test_packages();
		parent::tearDown();
	}
	
	/**
	 * Test that same owner can replace their own packages.
	 * Packages are mutable - owners should be able to update them.
	 */
	public function test_same_owner_can_replace_package() {
		$packageName = 'replace-test-' . substr( uniqid(), 0, 8 );
		
		// Create and publish first version
		$packageDir1 = $this->createTestPackage( $packageName, 'Original content' );
		
		$proc1 = qit( [
			'package:publish',
			$packageDir1,
			'1.0.0',
		], return_process: true );
		
		$this->skipIfNotConnected( $proc1 );
		$this->assertSame( 0, $proc1->getExitCode(), 
			'First publish should succeed: ' . $proc1->getOutput() );
		
		// Modify the package content
		$packageDir2 = $this->createTestPackage( $packageName, 'Updated content' );
		
		// Same owner should be able to replace
		$proc2 = qit( [
			'package:publish',
			$packageDir2,
			'1.0.0',  // SAME version - replacing
		], return_process: true );
		
		// Should SUCCEED for same owner
		$this->assertSame( 0, $proc2->getExitCode(), 
			'Same owner should be able to replace their package: ' . $proc2->getOutput() );
		
		$output = $proc2->getOutput();
		$this->assertStringContainsString( 
			'published successfully', 
			$output,
			'Should indicate successful replacement' 
		);
	}
	
	/**
	 * Test that different owners cannot claim the same package ID.
	 * This test simulates different ownership by testing the validation logic.
	 */
	public function test_different_owner_cannot_claim_package() {
		// Note: In real scenarios, different owners would have different auth tokens.
		// Since we're using the same test account, we'll test the blocking behavior
		// by attempting to publish with a namespace we don't own.
		
		// This test validates the concept - actual blocking happens at namespace level
		$this->markTestIncomplete(
			'Different owner blocking is enforced via namespace ownership. ' .
			'Cannot fully test without multiple test accounts.'
		);
	}
	
	/**
	 * Test that different versions of the same package CAN be published.
	 * This ensures versioning works properly.
	 */
	public function test_same_package_different_versions_allowed() {
		$packageName = 'versioned-' . substr( uniqid(), 0, 8 );
		$packageDir = $this->createTestPackage( $packageName, 'Versioned package' );
		
		// Publish version 1.0.0
		$proc1 = qit( [
			'package:publish',
			$packageDir,
			'1.0.0',
		], return_process: true );
		
		$this->skipIfNotConnected( $proc1 );
		$this->assertSame( 0, $proc1->getExitCode(), 
			'Version 1.0.0 should publish: ' . $proc1->getOutput() );
		
		// Publish version 2.0.0 of the SAME package
		$proc2 = qit( [
			'package:publish',
			$packageDir,
			'2.0.0',
		], return_process: true );
		
		$this->assertSame( 0, $proc2->getExitCode(), 
			'Version 2.0.0 should also publish: ' . $proc2->getOutput() );
		
		// Verify both versions exist
		$listProc = qit( [
			'package:list',
			'--namespace=woocommerce',
		], return_process: true );
		
		$listOutput = $listProc->getOutput();
		$this->assertStringContainsString( "$packageName:1.0.0", $listOutput );
		$this->assertStringContainsString( "$packageName:2.0.0", $listOutput );
	}
	
	/**
	 * Test that subpackage ownership is locked to the first parent.
	 * Once a parent claims a subpackage name, no other parent can use it (any version).
	 */
	public function test_subpackage_ownership_locked_to_parent() {
		// Create Parent A with a subpackage
		$parentADir = $this->createPackageWithSubpackages( 'parent-a-unique', [
			'woocommerce/qit-integration-test-shared-sub-unique' => [
				'description' => 'Subpackage from Parent A',
				'test' => [
					'phases' => [
						'run' => [
							'host: echo "Parent A subpackage" && mkdir -p ./results && echo \'' . 
							json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
							'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
						]
					]
				]
			]
		]);
		
		// Publish Parent A
		$procA = qit( [
			'package:publish',
			$parentADir,
			'1.0.0',
		], return_process: true );
		
		$this->skipIfNotConnected( $procA );
		$this->assertSame( 0, $procA->getExitCode(), 
			'Parent A should publish: ' . $procA->getOutput() );
		
		// Create Parent B trying to use SAME subpackage identifier
		$parentBDir = $this->createPackageWithSubpackages( 'parent-b-unique', [
			'woocommerce/qit-integration-test-shared-sub-unique' => [  // SAME ID!
				'description' => 'Subpackage from Parent B - should fail',
				'test' => [
					'phases' => [
						'run' => [
							'host: echo "Parent B subpackage" && mkdir -p ./results && echo \'' . 
							json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
							'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
						]
					]
				]
			]
		]);
		
		// Attempt to publish Parent B - should FAIL
		$procB = qit( [
			'package:publish',
			$parentBDir,
			'1.0.0',
		], return_process: true );
		
		$this->assertNotEquals( 0, $procB->getExitCode(), 
			'Parent B should fail due to duplicate subpackage identifier' );
		
		$output = $procB->getOutput() . $procB->getErrorOutput();
		$this->assertMatchesRegularExpression(
			'/subpackage.*collision|already exists|duplicate.*subpackage|owned by/i',
			$output,
			'Error should mention subpackage collision or ownership'
		);
	}
	
	/**
	 * Test that a parent can publish multiple versions of its own subpackages.
	 */
	public function test_parent_can_publish_multiple_versions_of_own_subpackages() {
		$uniqueId = substr( uniqid(), 0, 8 );
		$parentName = 'parent-versions-' . $uniqueId;
		$subpackageName = 'woocommerce/sub-versions-' . $uniqueId;
		
		// Create parent with subpackage
		$parentDir = $this->createPackageWithSubpackages( $parentName, [
			$subpackageName => [
				'description' => 'Versioned subpackage',
				'test' => [
					'phases' => [
						'run' => [
							'host: echo "Version test" && mkdir -p ./results && echo \'' . 
							json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
							'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
						]
					]
				]
			]
		]);
		
		// Publish version 1.0.0
		$proc1 = qit( [
			'package:publish',
			$parentDir,
			'1.0.0',
		], return_process: true );
		
		$this->skipIfNotConnected( $proc1 );
		$this->assertSame( 0, $proc1->getExitCode(), 
			'Version 1.0.0 should publish: ' . $proc1->getOutput() );
		
		// Same parent should be able to publish version 2.0.0
		$proc2 = qit( [
			'package:publish',
			$parentDir,
			'2.0.0',
		], return_process: true );
		
		$this->assertSame( 0, $proc2->getExitCode(), 
			'Same parent should be able to publish version 2.0.0: ' . $proc2->getOutput() );
		
		// Verify both versions exist
		$listProc = qit( [
			'package:list',
			'--namespace=woocommerce',
		], return_process: true );
		
		$listOutput = $listProc->getOutput();
		$this->assertStringContainsString( "$subpackageName:1.0.0", $listOutput );
		$this->assertStringContainsString( "$subpackageName:2.0.0", $listOutput );
	}
	
	/**
	 * Test that different parent cannot claim an already-owned subpackage name, even with different version.
	 */
	public function test_different_parent_cannot_claim_owned_subpackage_different_version() {
		$uniqueId = substr( uniqid(), 0, 8 );
		$subpackageName = 'woocommerce/owned-sub-' . $uniqueId;
		
		// Parent A publishes subpackage version 1.0.0
		$parentADir = $this->createPackageWithSubpackages( 'owner-parent-' . $uniqueId, [
			$subpackageName => [
				'description' => 'Owned by Parent A',
				'test' => [
					'phases' => [
						'run' => [
							'host: echo "Parent A owns this" && mkdir -p ./results && echo \'' . 
							json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
							'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
						]
					]
				]
			]
		]);
		
		$procA = qit( [
			'package:publish',
			$parentADir,
			'1.0.0',
		], return_process: true );
		
		$this->skipIfNotConnected( $procA );
		$this->assertSame( 0, $procA->getExitCode(), 
			'Parent A should publish: ' . $procA->getOutput() );
		
		// Parent B tries to publish SAME subpackage name but DIFFERENT version
		$parentBDir = $this->createPackageWithSubpackages( 'intruder-parent-' . $uniqueId, [
			$subpackageName => [  // Same subpackage name!
				'description' => 'Parent B trying to steal the name',
				'test' => [
					'phases' => [
						'run' => [
							'host: echo "Parent B wants this" && mkdir -p ./results && echo \'' . 
							json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
							'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
						]
					]
				]
			]
		]);
		
		// Try with version 2.0.0 - should still FAIL because ownership is locked
		$procB = qit( [
			'package:publish',
			$parentBDir,
			'2.0.0',  // Different version!
		], return_process: true );
		
		$output = $procB->getOutput() . $procB->getErrorOutput();
		
		// Check for the ownership conflict error
		$this->assertStringContainsString( 
			'Subpackage ownership conflict', 
			$output,
			'Should show subpackage ownership conflict error' 
		);
		
		$this->assertMatchesRegularExpression(
			'/owned by parent package.*owner-parent/is',  // Added 's' flag for dotall
			$output,
			'Error should indicate subpackage is owned by another parent'
		);
	}
	
	/**
	 * Test that "latest" version handling works with uniqueness.
	 */
	public function test_latest_version_uniqueness() {
		$packageName = 'test-latest-' . substr( uniqid(), 0, 8 );
		$packageDir = $this->createTestPackage( $packageName, 'Latest version test' );
		
		// Publish without version (defaults to "latest")
		$proc1 = qit( [
			'package:publish',
			$packageDir,
		], return_process: true );
		
		$this->skipIfNotConnected( $proc1 );
		$this->assertSame( 0, $proc1->getExitCode(), 
			'First publish with latest should succeed' );
		
		// Try to publish again without version - should update/replace "latest"
		// This behavior depends on implementation - it might replace or reject
		$proc2 = qit( [
			'package:publish', 
			$packageDir,
		], return_process: true );
		
		// Document the actual behavior
		if ( $proc2->getExitCode() === 0 ) {
			$this->markTestIncomplete( 
				'Publishing to "latest" twice succeeds - it replaces the previous latest version' 
			);
		} else {
			$output = $proc2->getOutput() . $proc2->getErrorOutput();
			$this->assertMatchesRegularExpression(
				'/already exists|latest.*exists/is',  // Added 's' flag for dotall
				$output,
				'Should indicate latest version already exists'
			);
		}
	}
	
	/**
	 * Test that deleting a parent package frees up its subpackage names.
	 * This ensures the subpackage lifecycle is properly managed.
	 */
	public function test_deleting_parent_frees_subpackage_names() {
		$uniqueId = substr( uniqid(), 0, 8 );
		
		// Step 1: Create Parent A with a subpackage
		$parentADir = $this->createPackageWithSubpackages(
			"parent-a-lifecycle-$uniqueId",
			[
				"woocommerce/shared-subpkg-$uniqueId" => [
					'description' => 'Shared subpackage from Parent A',
					'test' => [ 
						'phases' => [
							'run' => ['echo "shared subpackage from Parent A"']
						]
					]
				]
			]
		);
		
		// Publish Parent A (which includes its subpackage)
		$proc1 = qit( [
			'package:publish',
			$parentADir,
			'1.0.0',
			'--no-interaction',
		], return_process: true );
		
		$this->skipIfNotConnected( $proc1 );
		$this->assertSame( 0, $proc1->getExitCode(), 
			'Parent A should publish successfully: ' . $proc1->getOutput() );
		
		// Step 2: Create Parent B trying to use the same subpackage name
		$parentBDir = $this->createPackageWithSubpackages(
			"parent-b-lifecycle-$uniqueId",
			[
				"woocommerce/shared-subpkg-$uniqueId" => [
					'description' => 'Parent B trying to claim the same subpackage',
					'test' => [ 
						'phases' => [
							'run' => ['echo "Parent B trying to steal"']
						]
					]
				]
			]
		);
		
		// Try to publish Parent B - should FAIL (subpackage owned by Parent A)
		$proc2 = qit( [
			'package:publish',
			$parentBDir,
			'1.0.0',
			'--no-interaction',  // Avoid prompts
		], return_process: true );
		
		$this->assertNotSame( 0, $proc2->getExitCode(),
			'Parent B should fail when trying to use Parent A\'s subpackage' );
		
		// Just verify it failed - don't test specific output text
		// The behavior (rejection) is what matters, not the exact error message
		
		// Step 3: Delete Parent A
		$proc3 = qit( [
			'package:delete',
			"woocommerce/qit-integration-test-parent-a-lifecycle-$uniqueId:1.0.0",
			'--yes',  // Skip confirmation
		], return_process: true );
		
		$this->assertSame( 0, $proc3->getExitCode(),
			'Parent A should be deleted successfully: ' . $proc3->getOutput() );
		
		// Step 4: Now Parent B should be able to publish (subpackage name is free)
		$proc4 = qit( [
			'package:publish',
			$parentBDir,
			'1.0.0',
			'--no-interaction',
		], return_process: true );
		
		$this->assertSame( 0, $proc4->getExitCode(),
			'Parent B should now succeed after Parent A is deleted: ' . $proc4->getOutput() );
		
		// Cleanup: Delete Parent B
		qit( [
			'package:delete',
			"woocommerce/qit-integration-test-parent-b-lifecycle-$uniqueId:1.0.0",
			'--yes',
		] );
	}
	
	// === Helper Methods ===
	
	private function skipIfNotConnected( $process ): void {
		if ( $process->getExitCode() !== 0 ) {
			$output = $process->getOutput() . $process->getErrorOutput();
			if ( strpos( $output, 'Could not resolve host' ) !== false ||
			     strpos( $output, 'Failed to connect' ) !== false ||
			     strpos( $output, 'not connected' ) !== false ) {
				$this->markTestSkipped( 'Cannot connect to Manager - skipping test' );
			}
		}
	}
	
	private function createTestPackage( string $name, string $description ): string {
		$tempDir = sys_get_temp_dir() . '/qit-unique-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		// Use the test package prefix so cleanup works
		$packageName = 'woocommerce/qit-integration-test-' . $name;
		
		$manifest = [
			'package' => $packageName,
			'test_type' => 'e2e',
			'description' => $description,
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Running ' . $name . '" && mkdir -p ./results && echo \'' . 
						json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
						'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Add a minimal test file
		file_put_contents( $tempDir . '/test.spec.js', "test('dummy', async () => { });" );
		
		return $tempDir;
	}
	
	private function createPackageWithSubpackages( string $parentName, array $subpackages ): string {
		$tempDir = sys_get_temp_dir() . '/qit-subpkg-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		// Use the test prefix for cleanup
		$packageName = 'woocommerce/qit-integration-test-' . $parentName;
		
		$manifest = [
			'package' => $packageName,
			'test_type' => 'e2e',
			'description' => 'Parent package ' . $parentName,
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Running parent ' . $parentName . '" && mkdir -p ./results && echo \'' . 
						json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . 
						'\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			],
			'subpackages' => $subpackages
		];
		
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		file_put_contents( $tempDir . '/test.spec.js', "test('parent', async () => { });" );
		
		return $tempDir;
	}
	
	private function deleteDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$objects = scandir( $dir );
		foreach ( $objects as $object ) {
			if ( $object !== '.' && $object !== '..' ) {
				if ( is_dir( $dir . '/' . $object ) ) {
					$this->deleteDirectory( $dir . '/' . $object );
				} else {
					unlink( $dir . '/' . $object );
				}
			}
		}
		rmdir( $dir );
	}
}