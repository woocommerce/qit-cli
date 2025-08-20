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
	 * Test that subpackages from different parents don't collide.
	 * 
	 * When two different parent packages define subpackages with the same name,
	 * they must not interfere with each other.
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
		
		// Step 2: Create and publish Parent B with SAME subpackage names but DIFFERENT content
		$parentBDir = $this->createPackageWithSubpackages( 'qit-integration-test-parent-b', [
			'woocommerce/qit-integration-test-shared-checkout' => [
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
		
		$this->assertEquals( 0, $publishB->getExitCode(), 
			'Parent B publish should succeed: ' . $publishB->getOutput() . "\n" . $publishB->getErrorOutput() );
		$this->assertStringContainsString( 'Package published successfully', $publishB->getOutput() );
		
		// Step 3: Run subpackage from Parent A - should get PARENT_A content
		$configA = $this->createConfig( [
			'woocommerce/qit-integration-test-parent-a/qit-integration-test-shared-checkout:1.0.0'
		] );
		
		$runA = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configA,
		], return_process: true );
		
		$this->assertEquals( 0, $runA->getExitCode(), 
			'Run A should succeed: ' . $runA->getOutput() . "\n" . $runA->getErrorOutput() );
		
		// CRITICAL: Must see PARENT_A content, not PARENT_B
		$this->assertStringContainsString( '[PARENT_A]', $runA->getOutput() );
		$this->assertStringContainsString( 'PARENT A - ORIGINAL', $runA->getOutput() );
		$this->assertStringNotContainsString( '[PARENT_B]', $runA->getOutput() );
		$this->assertStringNotContainsString( 'PARENT B - DIFFERENT', $runA->getOutput() );
		
		// Step 4: Run subpackage from Parent B - should get PARENT_B content
		$configB = $this->createConfig( [
			'woocommerce/qit-integration-test-parent-b/qit-integration-test-shared-checkout:1.0.0'
		] );
		
		$runB = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configB,
		], return_process: true );
		
		$this->assertEquals( 0, $runB->getExitCode(), 
			'Run B should succeed: ' . $runB->getOutput() . "\n" . $runB->getErrorOutput() );
		
		// CRITICAL: Must see PARENT_B content, not PARENT_A
		$this->assertStringContainsString( '[PARENT_B]', $runB->getOutput() );
		$this->assertStringContainsString( 'PARENT B - DIFFERENT', $runB->getOutput() );
		$this->assertStringNotContainsString( '[PARENT_A]', $runB->getOutput() );
		$this->assertStringNotContainsString( 'PARENT A - ORIGINAL', $runB->getOutput() );
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
					'ctrf-json' => './results/ctrf.json'
				]
			],
			'subpackages' => []
		];
		
		// Add subpackages
		foreach ( $subpackages as $name => $config ) {
			$manifest['subpackages'][] = array_merge( 
				[ 'package' => $name ],
				$config
			);
		}
		
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