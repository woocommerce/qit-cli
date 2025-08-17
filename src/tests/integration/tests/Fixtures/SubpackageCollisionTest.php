<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Critical tests for subpackage collision bugs.
 * 
 * These tests ensure that subpackages from different parents don't interfere
 * with each other, which was a critical bug in the system.
 */
class SubpackageCollisionTest extends TestCase {
	
	private string $fixturesDir;
	private array $tempDirs = [];
	
	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
	}
	
	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		// Clean up all test packages from registry using the helper
		// This will clean up all packages with the qit-integration-test- prefix
		TestCleanupHelper::cleanup_all_test_packages();
		
		parent::tearDown();
	}
	
	/**
	 * Critical Test: Subpackages from different parents must not collide.
	 * 
	 * This test verifies that when two different parent packages define
	 * subpackages with the same name, they don't interfere with each other.
	 */
	public function test_subpackage_collision_across_different_parents(): void {
		// Step 1: Create and publish Parent A with subpackages
		$parentADir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-a', [
			'woocommerce/qit-integration-test-shared-checkout' => [
				'description' => 'Checkout tests from Parent A',
				'test' => [
					'phases' => [
						'globalSetup' => [
							'echo "[PARENT_A] Setting up checkout tests from PARENT A - ORIGINAL"'
						],
						'run' => [
							'echo "[PARENT_A] Running checkout tests from PARENT A - ORIGINAL"'
						]
					]
				]
			],
			'woocommerce/qit-integration-test-shared-cart' => [
				'description' => 'Cart tests from Parent A',
				'test' => [
					'phases' => [
						'run' => [
							'echo "[PARENT_A] Running cart tests"'
						]
					]
				]
			]
		] );
		
		$publishA = qit( [
			'package:publish',
			$parentADir,
			'1.0.0',
		],  return_process: true );
		
		// Skip if not connected to manager
		if ( strpos( $publishA->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publishA->getExitCode(), 
			'Parent A should publish successfully. Output: ' . $publishA->getOutput() );
		
		// Step 2: Verify Parent A's subpackages work
		$testA = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=woocommerce/qit-integration-test-shared-checkout:1.0.0',
		], expected_exit_code: 1, return_process: true ); // Exit 1 expected due to missing result file
		
		// Check that the correct globalSetup was executed
		$output = $testA->getOutput();
		$this->assertStringContainsString( 'PARENT A - ORIGINAL', $output,
			'Parent A checkout subpackage should execute with correct content' );
		
		// Step 3: Create and publish Parent B with SAME subpackage names
		$parentBDir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-b', [
			'woocommerce/qit-integration-test-shared-checkout' => [
				'description' => 'Checkout tests from Parent B - DIFFERENT CONTENT',
				'test' => [
					'phases' => [
						'globalSetup' => [
							'echo "[PARENT_B] Setting up checkout tests - DIFFERENT"'
						],
						'run' => [
							'echo "[PARENT_B] Running checkout tests - DIFFERENT"'
						]
					]
				]
			],
			'woocommerce/qit-integration-test-shared-cart' => [
				'description' => 'Cart tests from Parent B',
				'test' => [
					'phases' => [
						'run' => [
							'echo "[PARENT_B] Running cart tests - DIFFERENT"'
						]
					]
				]
			]
		] );
		
		$publishB = qit( [
			'package:publish',
			$parentBDir,
			'1.0.0',
		], expected_exit_code: 1, return_process: true );
		
		// Parent B's publish should FAIL with a collision error
		$this->assertEquals( 1, $publishB->getExitCode(),
			'Parent B should fail to publish due to subpackage collision' );
		
		// The error message should clearly indicate the collision
		$this->assertStringContainsString( 'collision', strtolower( $publishB->getOutput() ),
			'Should indicate collision as reason for failure' );
		$this->assertStringContainsString( 'woocommerce/qit-integration-test-shared-checkout:1.0.0', $publishB->getOutput(),
			'Should specify which subpackage caused the collision' );
		
		// Step 4: Critical Check - Parent A's subpackage should STILL work correctly
		$testA2 = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=woocommerce/qit-integration-test-shared-checkout:1.0.0',
		], expected_exit_code: 1, return_process: true ); // Exit 1 expected due to missing result file
		
		// Parent A's subpackage should still be intact and functional
		$output2 = $testA2->getOutput();
		$this->assertStringContainsString( 'PARENT A - ORIGINAL', $output2,
			'Parent A checkout subpackage should STILL work after Parent B fails to publish' );
		
		// Verify Parent B's code was never installed
		$this->assertStringNotContainsString( '[PARENT_B]', $output2,
			'Parent A subpackage should not execute Parent B code' );
	}
	
	/**
	 * Test that subpackage storage integrity is maintained.
	 * 
	 * Verifies that when packages share subpackage names, their storage
	 * doesn't get corrupted or deleted.
	 */
	public function test_subpackage_storage_integrity(): void {
		// Create Parent A with a subpackage
		$parentADir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-a', [
			'woocommerce/qit-integration-test-shared-checkout' => [
				'description' => 'Checkout from Parent A',
				'test' => [
					'phases' => [
						'run' => [ 'echo "Parent A checkout"' ]
					]
				]
			]
		] );
		
		$publishA = qit( [
			'package:publish',
			$parentADir,
			'1.0.0',
		],  return_process: true );
		
		if ( strpos( $publishA->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publishA->getExitCode() );
		
		// Get storage info for Parent A's subpackage
		$storageInfoA = $this->getPackageStorageInfo( 'woocommerce/qit-integration-test-shared-checkout:1.0.0' );
		if ( $storageInfoA === null ) {
			// Debug: Check what's in the database
			$debug = shell_exec( "docker exec cd_php bash -c 'wp db query \"SELECT package_id FROM wp_qit_test_packages\"' 2>&1" );
			$this->fail( "Parent A subpackage should exist in database. Current packages: " . $debug );
		}
		$this->assertNotNull( $storageInfoA, 'Parent A subpackage should exist in database' );
		// Check file exists in container
		$fileCheckCmd = sprintf( 'docker exec cd_php test -f %s && echo "exists" || echo "missing"', escapeshellarg( $storageInfoA['file_path'] ) );
		$fileExists = trim( shell_exec( $fileCheckCmd ) ) === 'exists';
		$this->assertTrue( $fileExists, 'Parent A storage file should exist in container' );
		
		// Create Parent B with SAME subpackage name
		$parentBDir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-b', [
			'woocommerce/qit-integration-test-shared-checkout' => [
				'description' => 'Checkout from Parent B',
				'test' => [
					'phases' => [
						'run' => [ 'echo "Parent B checkout"' ]
					]
				]
			]
		] );
		
		$publishB = qit( [
			'package:publish',
			$parentBDir,
			'1.0.0',
		], expected_exit_code: 1, return_process: true );
		
		// After Parent B's failed publish, check Parent A's storage
		$storageInfoA2 = $this->getPackageStorageInfo( 'woocommerce/qit-integration-test-shared-checkout:1.0.0' );
		
		// Critical assertions:
		if ( $publishB->getExitCode() === 0 ) {
			// If B was allowed to publish, A's storage should NOT be affected
			$this->assertEquals( $storageInfoA['storage_token'], $storageInfoA2['storage_token'],
				'Parent A storage token should not change when Parent B publishes' );
			// Check file still exists in container
			$fileCheckCmd2 = sprintf( 'docker exec cd_php test -f %s && echo "exists" || echo "missing"', escapeshellarg( $storageInfoA['file_path'] ) );
			$fileStillExists = trim( shell_exec( $fileCheckCmd2 ) ) === 'exists';
			$this->assertTrue( $fileStillExists,
				'Parent A storage file should still exist after Parent B publishes' );
		} else {
			// If B was rejected, that's also acceptable behavior
			$this->assertStringContainsString( 'collision', strtolower( $publishB->getOutput() ),
				'Should indicate collision as reason for rejection' );
		}
	}
	
	/**
	 * Helper: Create a package with subpackages
	 */
	private function createPackageWithSubpackages( string $packageName, array $subpackages ): string {
		$tempDir = sys_get_temp_dir() . '/qit_collision_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		
		// Copy base fixtures
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/subpackages-parent' ) . " " . escapeshellarg( $tempDir ) );
		
		// Update manifest
		$manifestPath = $tempDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = 'woocommerce/' . $packageName;
		$manifest['subpackages'] = $subpackages;
		
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		return $tempDir;
	}
	
	/**
	 * Helper: Get storage info for a package
	 */
	private function getPackageStorageInfo( string $packageId ): ?array {
		// Use docker exec to query the Manager database via wp eval
		$escapedId = addslashes( $packageId );
		$cmd = sprintf(
			'docker exec cd_php wp eval \'global $wpdb; echo json_encode($wpdb->get_results("SELECT storage_token, storage_path, parent_package FROM wp_qit_test_packages WHERE package_id = \\"%s\\"", ARRAY_A));\' 2>&1',
			$escapedId
		);
		$result = shell_exec( $cmd );
		$data = json_decode( $result, true );
		
		if ( empty( $data ) ) {
			return null;
		}
		
		$package = $data[0];
		$uploadDir = '/var/www/html/wp-content/uploads/';
		
		return [
			'storage_token' => $package['storage_token'],
			'storage_path' => $package['storage_path'],
			'parent_package' => $package['parent_package'],
			'file_path' => $uploadDir . $package['storage_path'],
		];
	}
	
}