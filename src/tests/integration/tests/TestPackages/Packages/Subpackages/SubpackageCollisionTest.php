<?php

namespace QIT\IntegrationTests\TestPackages\Packages\Subpackages;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;

require_once QIT_INTEGRATION_TESTS_ROOT . '/bootstrap.php';

/**
 * Tests for subpackage collision prevention.
 * 
 * These tests ensure that subpackages from different parents don't interfere
 * with each other, which is critical for maintaining package isolation.
 */
class SubpackageCollisionTest extends TestCase {
	
	private string $fixturesDir;
	private array $tempDirs = [];
	
	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages';
	}
	
	protected function tearDown(): void {
		// Clean up all test packages from registry
		TestCleanupHelper::cleanup_all_test_packages();
		parent::tearDown();
	}
	
	/**
	 * Test that the Manager correctly rejects duplicate subpackage identifiers.
	 * 
	 * Package identifiers must be globally unique, even for subpackages.
	 * When a second parent tries to publish a subpackage with an already-taken ID,
	 * the Manager should reject it with a clear error message.
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
		if ( $publishA->getExitCode() !== 0 ) {
			if ( strpos( $publishA->getOutput() . $publishA->getErrorOutput(), 'Could not resolve host' ) !== false
				|| strpos( $publishA->getOutput() . $publishA->getErrorOutput(), 'Failed to connect' ) !== false ) {
				$this->markTestSkipped( 'Cannot connect to Manager - skipping test' );
			}
			$this->fail( 'Package publish failed: ' . $publishA->getOutput() . "\n" . $publishA->getErrorOutput() );
		}
		
		$this->assertStringContainsString( 'Package published successfully', $publishA->getOutput() );
		
		// Step 2: Create and attempt to publish Parent B with SAME subpackage ID
		$parentBDir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-b', [
			'woocommerce/qit-integration-test-shared-checkout' => [  // SAME ID as Parent A!
				'description' => 'Checkout tests from Parent B',
				'test' => [
					'phases' => [
						'globalSetup' => [
							'echo "[PARENT_B] Setting up checkout tests from PARENT B - DIFFERENT"'
						],
						'run' => [
							'echo "[PARENT_B] Running checkout tests from PARENT B - DIFFERENT"'
						]
					]
				]
			],
			'woocommerce/qit-integration-test-shared-payment' => [
				'description' => 'Payment tests from Parent B',
				'test' => [
					'phases' => [
						'run' => [
							'echo "[PARENT_B] Running payment tests"'
						]
					]
				]
			]
		] );
		
		$publishB = qit( [
			'package:publish',
			$parentBDir,
			'1.0.0',
		], return_process: true );
		
		// EXPECT FAILURE: The Manager should reject the duplicate subpackage ID
		$this->assertNotEquals( 0, $publishB->getExitCode(), 
			'Parent B publish should FAIL due to duplicate subpackage ID' );
		
		// Verify the error message clearly indicates the collision
		$output = $publishB->getOutput() . $publishB->getErrorOutput();
		$this->assertStringContainsString( 'Subpackage collision', $output,
			'Error should mention subpackage collision' );
		$this->assertStringContainsString( 'woocommerce/qit-integration-test-shared-checkout', $output,
			'Error should identify the conflicting subpackage ID' );
		$this->assertStringContainsString( 'already exists', $output,
			'Error should indicate the package ID is already taken' );
		$this->assertStringContainsString( 'qit-integration-test-parent-a', $output,
			'Error should identify which parent owns the subpackage' );
		
		// Step 3: Verify Parent B CAN publish with different subpackage IDs
		$parentBFixedDir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-b-fixed', [
			'woocommerce/qit-integration-test-parent-b-checkout' => [  // UNIQUE ID!
				'description' => 'Checkout tests from Parent B with unique ID',
				'test' => [
					'phases' => [
						'run' => [
							'echo "[PARENT_B_FIXED] Running checkout tests from Parent B"'
						]
					]
				]
			],
			'woocommerce/qit-integration-test-parent-b-payment' => [  // UNIQUE ID!
				'description' => 'Payment tests from Parent B',
				'test' => [
					'phases' => [
						'run' => [
							'echo "[PARENT_B_FIXED] Running payment tests"'
						]
					]
				]
			]
		] );
		
		$publishBFixed = qit( [
			'package:publish',
			$parentBFixedDir,
			'1.0.0',
		], return_process: true );
		
		// This should succeed with unique subpackage IDs
		$this->assertEquals( 0, $publishBFixed->getExitCode(), 
			'Parent B should succeed with unique subpackage IDs: ' . $publishBFixed->getOutput() . "\n" . $publishBFixed->getErrorOutput() );
		$this->assertStringContainsString( 'Package published successfully', $publishBFixed->getOutput() );
		
		// Step 4: Verify packages were published correctly by listing them
		$listProc = qit( [
			'package:list',
			'--namespace=woocommerce',
		], return_process: true );
		
		$this->assertEquals( 0, $listProc->getExitCode(),
			'Package list should succeed' );
		
		$listOutput = $listProc->getOutput();
		
		// Verify Parent A and its subpackage are in the registry
		$this->assertStringContainsString( 'qit-integration-test-parent-a', $listOutput,
			'Parent A should be in the registry' );
		$this->assertStringContainsString( 'qit-integration-test-shared-checkout', $listOutput,
			'Parent A\'s subpackage should be in the registry' );
		
		// Verify Parent B (fixed version) and its unique subpackages are in the registry
		$this->assertStringContainsString( 'qit-integration-test-parent-b-fixed', $listOutput,
			'Parent B (fixed) should be in the registry' );
		$this->assertStringContainsString( 'qit-integration-test-parent-b-checkout', $listOutput,
			'Parent B\'s unique checkout subpackage should be in the registry' );
	}
	
	// ========== Helper Methods ==========
	
	private function createPackageWithSubpackages( string $parentName, array $subpackages ): string {
		$tempDir = sys_get_temp_dir() . '/qit-subpkg-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		// Create parent manifest
		$manifest = [
			'package' => 'woocommerce/' . $parentName,
			'test_type' => 'e2e',
			'description' => 'Parent package ' . $parentName,
			'test' => [
				'phases' => [
					'run' => [
						'echo "Running parent package ' . $parentName . '"'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			],
			'subpackages' => $subpackages
		];
		
		// Create CTRF result for parent
		mkdir( $tempDir . '/results', 0755, true );
		$ctrf = [
			'results' => [
				'tool' => [ 'name' => $parentName ],
				'summary' => [
					'tests' => 1,
					'passed' => 1,
					'failed' => 0,
					'skipped' => 0,
					'pending' => 0,
					'other' => 0,
					'start' => 0,
					'stop' => 1000
				],
				'tests' => [
					[
						'name' => 'parent test',
						'status' => 'passed',
						'duration' => 100
					]
				]
			]
		];
		file_put_contents( $tempDir . '/results/ctrf.json', json_encode( $ctrf, JSON_PRETTY_PRINT ) );
		
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $tempDir;
	}
	
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
		
		$tempDir = sys_get_temp_dir() . '/qit-config-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}