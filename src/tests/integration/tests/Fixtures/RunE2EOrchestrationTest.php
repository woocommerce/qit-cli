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
		
		// Both packages should run
		$this->assertStringContainsString( 'PACKAGE [1/2]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]', $output );
		
		// Package 1 should modify state
		$this->assertStringContainsString( 'Package 1: Modified site title', $output );
		$this->assertStringContainsString( 'Package 1: Created shared file', $output );
		
		// Package 2 should see Package 1's changes (proving persistence and order)
		$this->assertStringContainsString( 'Package 2: SUCCESS', $output );
		$this->assertStringContainsString( 'ORCHESTRATION VERIFIED', $output );
		
		// Check for specific verifications
		$verifications = [
			'WordPress database state persists between packages',
			'Filesystem is shared between packages'
		];
		
		foreach ( $verifications as $verification ) {
			$this->assertStringContainsString( 
				$verification, 
				$output,
				"Failed to verify: $verification"
			);
		}
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
		$this->assertStringContainsString( 'PACKAGE [1/2]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]', $output );
		
		// Package 2 running first should handle missing Package 1 state gracefully
		$this->assertStringContainsString( 'Package 2:', $output );
		
		// Package 1 running second should still work
		$this->assertStringContainsString( 'Package 1: Modified site title', $output );
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