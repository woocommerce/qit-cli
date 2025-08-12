<?php

namespace QIT\IntegrationTests\Fixtures;

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
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
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
	 * Test that orchestration guarantees are maintained:
	 * - Database state persists between packages
	 * - Filesystem is shared between packages
	 * - Packages execute in order
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
	 * Test packages in reverse order to ensure they handle it gracefully
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