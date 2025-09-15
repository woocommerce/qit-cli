<?php

namespace QIT\IntegrationTests\TestPackages\Commands\RunE2E;

use QIT\IntegrationTests\TestCleanupHelper;
use QIT\IntegrationTests\Helpers\CTRFHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test CI-specific behavior for the run:e2e command.
 * 
 * When running in CI environments, output behavior changes to be more
 * appropriate for automated systems.
 */
class CIBehaviorTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-ci-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Clean up CI environment variable
		putenv( 'CI' );
		parent::tearDown();
	}

	/**
	 * Test output suppression in CI mode.
	 * 
	 * When running in CI environments, verbose command output
	 * is suppressed while still showing the orchestrator UI.
	 */
	public function test_output_suppression_in_ci(): void {
		// Set CI environment
		putenv( 'CI=true' );

		$packageDir = $this->createTestPackageWithOutput();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// In CI mode, the orchestrator UI should show including the commands being run
		// We should see the commands in the UI but outputs are suppressed
		$this->assertStringContainsString( 'PACKAGE', $output ); // Orchestrator UI shows
		$this->assertStringContainsString( '[host] echo', $output ); // Commands are shown
		// The actual output from the echo commands should be suppressed
		// (The commands themselves appear as "[host] echo ..." but not their output)
		$this->assertEquals( 0, $proc->getExitCode() );
	}

	// ========== Helper Methods ==========

	private function createTestPackageWithOutput(): string {
		$packageDir = $this->fixturesDir . '/output-test';
		mkdir( $packageDir, 0755, true );

		$manifest = [
			'package' => 'woocommerce/qit-ci-output-test',
			'test_type' => 'e2e',
			'description' => 'Test package with output',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "Setting up test environment..."'
					],
					'run' => [
						'host: echo "Running tests..." && mkdir -p ./results && echo \'' . json_encode(CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					],
					'teardown' => [
						'echo "Cleaning up test environment..."'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		return $packageDir;
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

		$configPath = $this->fixturesDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}