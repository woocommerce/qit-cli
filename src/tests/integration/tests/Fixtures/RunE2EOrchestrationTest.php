<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test orchestration guarantees using test packages that verify the guarantees themselves.
 * 
 * The test packages do the actual verification and report via console.log.
 * We just need to run them and check their output.
 */
class RunE2EOrchestrationTest extends TestCase {

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
		
		parent::tearDown();
	}

	/**
	 * Test #16: Orchestration guarantees
	 * 
	 * Coverage aim: Validates core orchestration guarantees using real packages.
	 * Tests fundamental orchestration properties including database state management,
	 * filesystem sharing, and execution order using fixture packages that verify
	 * these guarantees internally.
	 * 
	 * Key aspects tested:
	 * - Database snapshot/restore mechanism
	 * - Filesystem sharing between packages
	 * - Execution order preservation
	 * - Global setup/teardown phases
	 */
	public function test_orchestration_guarantees(): void {
		$package1 = $this->fixturesDir . '/orchestration-test-package-1';
		$package2 = $this->fixturesDir . '/orchestration-test-package-2';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Both packages should run in order
		$this->assertStringContainsString( 'PACKAGE [1/2]: woocommerce/orchestration-package-1:local', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]: woocommerce/orchestration-package-2:local', $output );
		
		// Verify database restore happens between packages (proving isolation and orchestration)
		$this->assertStringContainsString( 'DATABASE RESTORE', $output );
		$this->assertStringContainsString( 'Restoring database snapshot for test isolation', $output );
		$this->assertStringContainsString( '✓ Database snapshot restored successfully', $output );
		
		// Verify all tests passed (proving the packages work correctly)
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		$this->assertStringContainsString( 'Tests:         7 passed, 0 failed', $output );
		
		// Verify proper orchestration phases
		$this->assertStringContainsString( 'GLOBAL SETUP', $output );
		$this->assertStringContainsString( 'GLOBAL TEARDOWN', $output );
	}

	/**
	 * Test #17: Orchestration reverse order
	 * 
	 * Coverage aim: Validates orchestration with reversed package order.
	 * Tests that orchestration guarantees hold regardless of package execution
	 * order, ensuring the system handles different package sequences correctly.
	 * 
	 * Key aspects tested:
	 * - Order independence of orchestration
	 * - Database restore regardless of order
	 * - Consistent results with different sequences
	 * - Package isolation in any order
	 */
	public function test_orchestration_reverse_order(): void {
		$package1 = $this->fixturesDir . '/orchestration-test-package-1';
		$package2 = $this->fixturesDir . '/orchestration-test-package-2';
		
		// Run in reverse order
		$config = $this->createConfig( [ $package2, $package1 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Both should still run successfully
		// Packages should run in the specified order (package-2 first, then package-1)
		$this->assertStringContainsString( 'PACKAGE [1/2]: woocommerce/orchestration-package-2:local', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]: woocommerce/orchestration-package-1:local', $output );
		
		// Verify database restore happens between packages
		$this->assertStringContainsString( 'DATABASE RESTORE', $output );
		$this->assertStringContainsString( '✓ Database snapshot restored successfully', $output );
		
		// All tests should still pass even with reversed order
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		$this->assertStringContainsString( 'Tests:         7 passed, 0 failed', $output );
	}

	// ============= Helper Methods =============

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

		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}