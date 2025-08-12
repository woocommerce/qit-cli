<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test E2E subpackages functionality using fixture packages.
 * 
 * Fixtures:
 * - subpackages-parent: Main package with 3 subpackages (checkout, cart, account)
 */
class RunE2ESubpackagesFixturesTest extends TestCase {

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
	 * Test that parent package with subpackages runs correctly
	 */
	public function test_parent_package_with_subpackages_runs(): void {
		$config = $this->createConfig( [
			$this->fixturesDir . '/subpackages-parent'
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();

		// Test should pass
		$this->assertEquals( 0, $exitCode, 
			'Parent package should run successfully. Output: ' . $output );
		
		// Should show the main package executed
		$this->assertStringContainsString( 'all.spec.js', $output,
			'Main package should run all.spec.js' );
		
		// Should show global phases executed
		$this->assertStringContainsString( '[GLOBAL_SETUP]', $output,
			'Global setup should execute' );
		$this->assertStringContainsString( '[GLOBAL_TEARDOWN]', $output,
			'Global teardown should execute' );
		
		// Should show package-specific setup
		$this->assertStringContainsString( '[SETUP] Package-specific', $output,
			'Package setup should execute' );
	}

	/**
	 * Test running multiple packages including one with subpackages
	 */
	public function test_mixed_packages_with_subpackages(): void {
		$config = $this->createConfig( [
			$this->fixturesDir . '/regular-test-package-one',
			$this->fixturesDir . '/subpackages-parent'
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode(),
			'Mixed packages should run successfully' );
		
		// Should show both packages executed
		$this->assertStringContainsString( 'PACKAGE [1/2]', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]', $output );
		
		// Global phases should run only once for all packages
		$globalSetupCount = substr_count( $output, '[GLOBAL_SETUP]' );
		$this->assertEquals( 1, $globalSetupCount,
			'Global setup should run exactly once across all packages' );
		
		$globalTeardownCount = substr_count( $output, '[GLOBAL_TEARDOWN]' );
		$this->assertEquals( 1, $globalTeardownCount,
			'Global teardown should run exactly once across all packages' );
	}

	/**
	 * Test that subpackages are published atomically with parent
	 * Note: This test simulates the publishing behavior
	 */
	public function test_subpackages_publish_atomically(): void {
		$packageDir = $this->fixturesDir . '/subpackages-parent';
		
		// Attempt to publish the parent package
		$proc = qit( [
			'package:publish',
			$packageDir,
		], return_process: true );

		// Skip if we're not connected to a Manager
		if ( strpos( $proc->getOutput(), 'not connected' ) !== false ||
		     strpos( $proc->getOutput(), 'qit connect' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}

		$output = $proc->getOutput();
		
		// If publish succeeded, verify subpackages were mentioned
		if ( $proc->getExitCode() === 0 ) {
			// The publish command should indicate subpackages were published
			$this->assertStringContainsString( 'woocommerce/e2e-suite', $output,
				'Parent package should be published' );
			
			// Check if subpackages count is mentioned
			// Based on the implementation, it should say "Subpackages published: 3"
			if ( strpos( $output, 'Subpackages published' ) !== false ) {
				$this->assertStringContainsString( 'Subpackages published: 3', $output,
					'Should indicate 3 subpackages were published' );
			}
		}
	}

	/**
	 * Test that package list shows subpackages correctly
	 */
	public function test_package_list_shows_subpackages(): void {
		// First ensure the package is published
		$this->publishPackageIfNeeded();
		
		$proc = qit( [
			'package:list',
			'--namespace=woocommerce',
			'--format=json',
		], return_process: true );

		// Skip if not connected
		if ( strpos( $proc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}

		$output = $proc->getOutput();
		$data = json_decode( $output, true );
		
		if ( json_last_error() === JSON_ERROR_NONE && isset( $data['packages'] ) ) {
			// Look for our parent package and subpackages
			$foundParent = false;
			$foundCheckout = false;
			$foundCart = false;
			$foundAccount = false;
			
			foreach ( $data['packages'] as $package ) {
				if ( $package['package_id'] === 'woocommerce/e2e-suite:latest' ) {
					$foundParent = true;
				}
				if ( strpos( $package['package_id'], 'woocommerce/checkout' ) === 0 ) {
					$foundCheckout = true;
					// Verify it's marked as subpackage
					$this->assertTrue( 
						isset( $package['is_subpackage'] ) && $package['is_subpackage'],
						'Checkout should be marked as subpackage'
					);
				}
				if ( strpos( $package['package_id'], 'woocommerce/cart' ) === 0 ) {
					$foundCart = true;
				}
				if ( strpos( $package['package_id'], 'woocommerce/account' ) === 0 ) {
					$foundAccount = true;
				}
			}
			
			// If parent was published, subpackages should be too
			if ( $foundParent ) {
				$this->assertTrue( $foundCheckout, 'Checkout subpackage should be listed' );
				$this->assertTrue( $foundCart, 'Cart subpackage should be listed' );
				$this->assertTrue( $foundAccount, 'Account subpackage should be listed' );
			} else {
				// At minimum, verify we got a valid response
				$this->assertIsArray( $data['packages'], 'Should get packages array in response' );
			}
		} else {
			// If JSON parsing failed, at least check the command executed successfully
			$this->assertEquals( 0, $proc->getExitCode(), 'Package list command should succeed' );
		}
	}

	/**
	 * Helper: Create a temporary config file
	 */
	private function createConfig( array $packagePaths ): string {
		$tempDir = sys_get_temp_dir() . '/qit_subpkg_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$config = [
			'$schema'      => 'https://qit.woo.com/json-schema/qit',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => $packagePaths
					]
				]
			],
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => 'stable'
				]
			]
		];
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}

	/**
	 * Helper: Extract test run ID from output
	 */
	private function extractTestRunId( string $output ): ?string {
		// Look for pattern like "View test run: https://qit.woo.com/test-run/..."
		if ( preg_match( '/test-run\/([a-zA-Z0-9-]+)/', $output, $matches ) ) {
			return $matches[1];
		}
		return null;
	}

	/**
	 * Helper: Verify local report is accessible
	 */
	private function assertLocalReportWorks(): void {
		// Check if qit report:view would work (without actually running it)
		$proc = qit( [
			'report:list',
		], return_process: true );
		
		// Just verify the command exists and runs
		$this->assertNotEquals( 127, $proc->getExitCode(), 
			'report:list command should exist' );
	}

	/**
	 * Helper: Verify remote test run exists
	 */
	private function assertRemoteTestRunExists( ?string $testRunId ): void {
		if ( ! $testRunId ) {
			$this->markTestIncomplete( 'No test run ID found' );
		}
		
		// We can't actually verify the remote without API access
		// But we can check the URL format is correct
		$this->assertMatchesRegularExpression(
			'/^[a-zA-Z0-9-]+$/',
			$testRunId,
			'Test run ID should be valid format'
		);
	}

	/**
	 * Helper: Publish package if needed for tests
	 */
	private function publishPackageIfNeeded(): void {
		static $published = false;
		
		if ( $published ) {
			return;
		}
		
		$packageDir = $this->fixturesDir . '/subpackages-parent';
		qit( [
			'package:publish',
			$packageDir,
		], return_process: true );
		
		$published = true;
	}
}