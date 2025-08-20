<?php

namespace QIT\IntegrationTests\Orchestration;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for package execution order and orchestration.
 * 
 * These tests verify that:
 * - Packages execute in the specified order
 * - Global setup runs before all packages
 * - Global teardown runs after all packages
 * - Each package's lifecycle phases execute in correct order
 * - Failures in one package don't prevent others from running
 */
class ExecutionOrderTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-order-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Cleanup handled by OS
		parent::tearDown();
	}

	/**
	 * Test that packages execute in the order specified in configuration.
	 */
	public function test_packages_execute_in_specified_order(): void {
		// Create packages that write their order to a shared file
		$outputFile = $this->fixturesDir . '/execution-order.txt';
		
		$package1 = $this->createOrderedPackage( 'first', 1, $outputFile );
		$package2 = $this->createOrderedPackage( 'second', 2, $outputFile );
		$package3 = $this->createOrderedPackage( 'third', 3, $outputFile );
		
		$config = $this->createConfig( [ $package1, $package2, $package3 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$this->assertEquals( 0, $proc->getExitCode(), 'All packages should execute successfully' );
		
		// Verify execution order from output
		$output = $proc->getOutput();
		$this->assertMatchesRegularExpression( '/PACKAGE \[1\/3\].*first.*PACKAGE \[2\/3\].*second.*PACKAGE \[3\/3\].*third/s', 
			$output, 'Packages should execute in specified order' );
	}

	/**
	 * Test that global setup runs before all packages.
	 */
	public function test_global_setup_runs_before_all_packages(): void {
		$package1 = $this->createPackageWithGlobalSetup( 'pkg1' );
		$package2 = $this->createSimplePackage( 'pkg2' );
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();
		
		// Global setup should appear before any package execution
		$globalSetupPos = strpos( $output, 'GLOBAL SETUP' );
		$package1Pos = strpos( $output, 'PACKAGE [1/2]' );
		$package2Pos = strpos( $output, 'PACKAGE [2/2]' );
		
		$this->assertLessThan( $package1Pos, $globalSetupPos, 
			'Global setup should run before first package' );
		$this->assertLessThan( $package2Pos, $globalSetupPos, 
			'Global setup should run before second package' );
	}

	/**
	 * Test that global teardown runs after all packages.
	 */
	public function test_global_teardown_runs_after_all_packages(): void {
		$package1 = $this->createSimplePackage( 'pkg1' );
		$package2 = $this->createPackageWithGlobalTeardown( 'pkg2' );
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();
		
		// Global teardown should appear after all packages
		$globalTeardownPos = strpos( $output, 'GLOBAL TEARDOWN' );
		$package1Pos = strpos( $output, 'PACKAGE [1/2]' );
		$package2Pos = strpos( $output, 'PACKAGE [2/2]' );
		
		$this->assertGreaterThan( $package1Pos, $globalTeardownPos, 
			'Global teardown should run after first package' );
		$this->assertGreaterThan( $package2Pos, $globalTeardownPos, 
			'Global teardown should run after second package' );
	}

	/**
	 * Test that failure in one package doesn't stop others from executing.
	 */
	public function test_failure_in_one_package_doesnt_stop_others(): void {
		$package1 = $this->createSimplePackage( 'success1' );
		$package2 = $this->createFailingPackage( 'failing' );
		$package3 = $this->createSimplePackage( 'success2' );
		
		$config = $this->createConfig( [ $package1, $package2, $package3 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		// Should have non-zero exit code due to failure
		$this->assertNotEquals( 0, $proc->getExitCode(), 'Should fail due to failing package' );
		
		$output = $proc->getOutput();
		
		// But all packages should have been attempted
		$this->assertStringContainsString( 'PACKAGE [1/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]', $output );
		
		// Verify the third package actually ran
		$this->assertStringContainsString( 'success2: executing', $output );
	}

	/**
	 * Test that each package's lifecycle phases execute in correct order.
	 */
	public function test_package_lifecycle_phases_execute_in_order(): void {
		$package = $this->createPackageWithAllPhases( 'full-lifecycle' );
		
		$config = $this->createConfig( [ $package ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();
		
		// Find positions of each phase in output
		$setupPos = strpos( $output, 'Setup phase executing' );
		$runPos = strpos( $output, 'Run phase executing' );
		$teardownPos = strpos( $output, 'Teardown phase executing' );
		
		// Verify order: setup < run < teardown
		$this->assertLessThan( $runPos, $setupPos, 'Setup should run before run phase' );
		$this->assertLessThan( $teardownPos, $runPos, 'Run should execute before teardown' );
	}

	// ========== Helper Methods ==========

	private function createOrderedPackage( string $name, int $order, string $outputFile ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package ' . $order,
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "' . $name . ': executing" && echo "' . $order . '" >> ' . $outputFile
					]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageWithGlobalSetup( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/bootstrap', 0755, true );
		
		// Create global setup script
		file_put_contents( $packageDir . '/bootstrap/global-setup.sh', 
			"#!/bin/bash\necho 'Global setup from $name'\nexit 0\n" );
		chmod( $packageDir . '/bootstrap/global-setup.sh', 0755 );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package with global setup',
			'test' => [
				'phases' => [
					'globalSetup' => [ './bootstrap/global-setup.sh' ],
					'run' => [ 'host: echo "Running ' . $name . '"' ]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageWithGlobalTeardown( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/bootstrap', 0755, true );
		
		// Create global teardown script
		file_put_contents( $packageDir . '/bootstrap/global-teardown.sh', 
			"#!/bin/bash\necho 'Global teardown from $name'\nexit 0\n" );
		chmod( $packageDir . '/bootstrap/global-teardown.sh', 0755 );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package with global teardown',
			'test' => [
				'phases' => [
					'run' => [ 'host: echo "Running ' . $name . '"' ],
					'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageWithAllPhases( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Package with all phases',
			'test' => [
				'phases' => [
					'setup' => [ 'host: echo "Setup phase executing"' ],
					'run' => [ 'host: echo "Run phase executing"' ],
					'teardown' => [ 'host: echo "Teardown phase executing"' ]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createSimplePackage( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Simple package',
			'test' => [
				'phases' => [
					'run' => [ 'host: echo "' . $name . ': executing"' ]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createFailingPackage( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Failing package',
			'test' => [
				'phases' => [
					'run' => [ 'host: echo "' . $name . ': executing" && exit 1' ]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createConfig( array $packages ): string {
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => $packages
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