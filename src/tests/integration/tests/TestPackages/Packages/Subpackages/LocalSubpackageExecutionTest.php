<?php

namespace QIT\IntegrationTests\TestPackages\Packages\Subpackages;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

/**
 * Test execution of local subpackages selected via the --subpackage option.
 *
 * Fixtures:
 * - subpackages-parent: Main package with 3 subpackages (checkout, cart, account)
 */
#[Group( 'docker' )]
class LocalSubpackageExecutionTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];
	private string $originalCwd;

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages';
		$this->originalCwd = getcwd();

		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
	}

	protected function tearDown(): void {
		chdir( $this->originalCwd );

		foreach ( $this->tempDirs as $dir ) {
			$config_path = $dir . '/qit.json';
			if ( file_exists( $config_path ) ) {
				unlink( $config_path );
			}
			if ( is_dir( $dir ) ) {
				rmdir( $dir );
			}
		}
		$this->tempDirs = [];

		// Clean up any test packages created during the test
		TestCleanupHelper::cleanup_all_test_packages();

		parent::tearDown();
	}

	/**
	 * Test running a single subpackage of a local test package.
	 */
	public function test_single_local_subpackage_runs_only_its_tests(): void {
		$config = $this->createConfig( [
			$this->fixturesDir . '/subpackages-parent',
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--subpackage=woocommerce/qit-integration-test-checkout',
		], return_process: true );

		$output   = $proc->getOutput();
		$exitCode = $proc->getExitCode();

		$this->assertEquals( 0, $exitCode,
			'Subpackage selection should run successfully. Output: ' . $output );

		// Only the selected subpackage's run phase should execute.
		$this->assertStringContainsString( 'checkout.spec.js', $output,
			'Checkout subpackage test should execute' );
		$this->assertStringNotContainsString( 'all.spec.js', $output,
			'Parent run phase should NOT execute' );
		$this->assertStringNotContainsString( 'cart.spec.js', $output,
			'Cart subpackage should NOT execute' );
		$this->assertStringNotContainsString( 'account.spec.js', $output,
			'Account subpackage should NOT execute' );

		// Inherited phases from the parent should execute.
		$this->assertStringContainsString( '[GLOBAL_SETUP]', $output,
			'Inherited globalSetup should execute' );
		$this->assertStringContainsString( '[SETUP] Package-specific', $output,
			'Inherited setup should execute' );

		// Global setup should run exactly once.
		$this->assertEquals( 1, substr_count( $output, '[GLOBAL_SETUP]' ),
			'Global setup should run exactly once' );

		// Only one package should execute.
		$this->assertStringContainsString( 'PACKAGE [1/1]', $output,
			'Should show only 1 package executing' );

		// The subpackage runs as a local package.
		$this->assertStringContainsString( 'woocommerce/qit-integration-test-checkout:local', $output,
			'Subpackage should be identified as a local package' );
	}

	/**
	 * Test running multiple subpackages of the same local parent.
	 */
	public function test_multiple_local_subpackages_run_with_dedup(): void {
		$config = $this->createConfig( [
			$this->fixturesDir . '/subpackages-parent',
		] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--subpackage=woocommerce/qit-integration-test-checkout',
			'--subpackage=woocommerce/qit-integration-test-cart',
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode(),
			'Multiple subpackage selections should run successfully. Output: ' . $output );

		// Both selected subpackages should run.
		$this->assertStringContainsString( 'checkout.spec.js', $output,
			'Checkout subpackage test should execute' );
		$this->assertStringContainsString( 'cart.spec.js', $output,
			'Cart subpackage test should execute' );

		// Parent and unselected subpackage should not run.
		$this->assertStringNotContainsString( 'all.spec.js', $output,
			'Parent run phase should NOT execute' );
		$this->assertStringNotContainsString( 'account.spec.js', $output,
			'Account subpackage should NOT execute' );

		// Two packages should execute.
		$this->assertStringContainsString( 'PACKAGE [1/2]', $output,
			'Should show package 1 of 2' );
		$this->assertStringContainsString( 'PACKAGE [2/2]', $output,
			'Should show package 2 of 2' );

		// Shared inherited globalSetup should be deduplicated (runs once).
		$this->assertEquals( 1, substr_count( $output, '[GLOBAL_SETUP]' ),
			'Inherited globalSetup should run exactly once (deduplicated)' );
	}

	/**
	 * Test running a subpackage with the parent auto-detected from cwd.
	 */
	public function test_subpackage_with_cwd_auto_detected_parent(): void {
		// Run from inside the fixture - no --test-package or config needed.
		chdir( $this->fixturesDir . '/subpackages-parent' );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--subpackage=woocommerce/qit-integration-test-checkout',
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode(),
			'Auto-detected parent with --subpackage should run successfully. Output: ' . $output );

		$this->assertStringContainsString( 'checkout.spec.js', $output,
			'Checkout subpackage test should execute' );
		$this->assertStringNotContainsString( 'all.spec.js', $output,
			'Parent run phase should NOT execute' );
	}

	/**
	 * Helper: Create a temporary config file
	 */
	private function createConfig( array $packagePaths ): string {
		$tempDir = sys_get_temp_dir() . '/qit_local_subpkg_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );

		$config = [
			'$schema'      => 'https://raw.githubusercontent.com/woocommerce/qit-cli/trunk/src/src/PreCommand/Schemas/qit-schema.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ],
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => $packagePaths,
					],
				],
			],
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => 'stable',
				],
			],
		];

		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

		return $configPath;
	}
}
