<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test that packages execute in the correct order and maintain proper isolation.
 * 
 * These tests verify:
 * 1. Packages run in the order specified
 * 2. Each package gets its own test environment
 * 3. Results are properly collected from all packages
 * 4. Failures in one package don't prevent others from running
 */
class RunE2EPackageOrderingTest extends TestCase {

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
	 * Test that packages execute in the specified order
	 */
	public function test_packages_execute_in_order(): void {
		// Use our existing fixtures
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		$package3 = $this->fixturesDir . '/failing-test-package'; // Has 2 pass, 1 fail
		
		$config = $this->createConfig( [ $package1, $package2, $package3 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true ); // Exit code 1 because one package has failures

		$output = $proc->getOutput();

		// All packages should run despite failures
		$this->assertStringContainsString( 'PACKAGE [1/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]', $output );
		
		// Package names should appear in order
		$package1Pos = strpos( $output, 'my-woo-test-package' );
		$package2Pos = strpos( $output, 'second-test-package' );
		$package3Pos = strpos( $output, 'failing-test-package' );
		
		$this->assertLessThan( $package2Pos, $package1Pos, 'Package 1 should run before package 2' );
		$this->assertLessThan( $package3Pos, $package2Pos, 'Package 2 should run before package 3' );
		
		// Results should be collected from all packages
		$this->assertStringContainsString( 'Tests:', $output );
		
		// Should fail overall because package 3 has failures
		$this->assertStringContainsString( 'Status:        ✗ FAILED', $output );
	}

	/**
	 * Test that failures in one package don't stop other packages
	 */
	public function test_failure_doesnt_stop_other_packages(): void {
		// Put failing package first
		$package1 = $this->fixturesDir . '/failing-test-package';
		$package2 = $this->fixturesDir . '/regular-test-package-one';
		$package3 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2, $package3 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// All packages should still run
		$this->assertStringContainsString( 'PACKAGE [1/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]', $output );
		
		// Package 2 and 3 should pass even though package 1 failed
		// Look for the execution of each package
		$this->assertMatchesRegularExpression( '/failing-test-package.*1 failed/s', $output );
		$this->assertMatchesRegularExpression( '/my-woo-test-package.*3 passed/s', $output );
		$this->assertMatchesRegularExpression( '/regular-test-package-two.*3 passed/s', $output );
	}

	/**
	 * Test that results are properly aggregated from all packages
	 */
	public function test_results_aggregation(): void {
		// Package 1: 3 tests pass
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		// Package 2: 3 tests pass  
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show combined results
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		$this->assertStringContainsString( 'Tests:         6 passed', $output ); // 3 + 3 = 6
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	/**
	 * Test mixed results aggregation (some pass, some fail)
	 */
	public function test_mixed_results_aggregation(): void {
		// Failing package: 2 pass, 1 fail
		$package1 = $this->fixturesDir . '/failing-test-package';
		// Regular package: 3 pass
		$package2 = $this->fixturesDir . '/regular-test-package-one';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should show mixed results
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
		$this->assertMatchesRegularExpression( '/Tests:.*5 passed.*1 failed/s', $output ); // 2+3 passed, 1 failed
		$this->assertStringContainsString( 'Status:        ✗ FAILED', $output );
	}

	/**
	 * Test that CTRF reports are merged correctly
	 */
	public function test_ctrf_merging(): void {
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show CTRF merging
		$this->assertStringContainsString( '✓ Merging CTRF reports...', $output );
		$this->assertStringContainsString( '✓ CTRF reports merged', $output );
	}

	/**
	 * Test that blob reports are merged for HTML generation
	 */
	public function test_blob_report_merging(): void {
		$package1 = $this->fixturesDir . '/regular-test-package-one';
		$package2 = $this->fixturesDir . '/regular-test-package-two';
		
		$config = $this->createConfig( [ $package1, $package2 ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should show blob merging
		$this->assertStringContainsString( '✓ Merging blob reports into HTML...', $output );
		$this->assertStringContainsString( '✓ HTML report generated', $output );
		
		// Should provide report command
		$this->assertStringContainsString( 'Local Report:  qit report', $output );
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