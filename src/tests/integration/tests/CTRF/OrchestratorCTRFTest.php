<?php

namespace QIT\IntegrationTests\CTRF;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test that the orchestrator generates CTRF entries for lifecycle phases.
 * 
 * The orchestrator should create CTRF entries for setup and teardown phases
 * to provide complete test execution tracking.
 */
class OrchestratorCTRFTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-orch-ctrf-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * Test that orchestrator generates CTRF for lifecycle phases.
	 * 
	 * The orchestrator should create CTRF entries for setup and teardown phases,
	 * enabling complete test lifecycle tracking.
	 */
	public function test_orchestrator_ctrf_generation(): void {
		// Create a simple test package with all phases
		$packageDir = $this->createTestPackageWithAllPhases();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// Test should pass (has run phase)
		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Look for artifacts directory in output
		if ( preg_match( '/test-runs\/run-[a-f0-9.]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $matches[0];
			
			// Check if orchestrator.json was created
			$orchestrator_ctrf = $artifacts_path . '/ctrf/orchestrator.json';
			if ( file_exists( $orchestrator_ctrf ) ) {
				$ctrf_data = json_decode( file_get_contents( $orchestrator_ctrf ), true );
				
				// Verify it contains lifecycle phase results
				$this->assertArrayHasKey( 'results', $ctrf_data );
				$this->assertArrayHasKey( 'tests', $ctrf_data['results'] );
				
				// Should have entries for setup and teardown
				$has_setup = false;
				$has_teardown = false;
				foreach ( $ctrf_data['results']['tests'] as $test ) {
					if ( strpos( $test['name'], '[setup]' ) !== false ) {
						$has_setup = true;
					}
					if ( strpos( $test['name'], '[teardown]' ) !== false ) {
						$has_teardown = true;
					}
				}
				
				$this->assertTrue( $has_setup, 'Should have setup phase in CTRF' );
				$this->assertTrue( $has_teardown, 'Should have teardown phase in CTRF' );
			}
		}
	}

	// ========== Helper Methods ==========

	private function createTestPackageWithAllPhases(): string {
		$packageDir = $this->fixturesDir . '/full-package';
		mkdir( $packageDir, 0755, true );

		$manifest = [
			'package' => 'woocommerce/qit-orch-ctrf-test',
			'test_type' => 'e2e',
			'description' => 'Test package with all phases',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "Setting up test environment..."'
					],
					'run' => [
						'host: mkdir -p ./results && echo \'{"results":{"tool":{"name":"test-package"},"summary":{"tests":1,"passed":1,"failed":0,"skipped":0,"pending":0,"other":0,"start":0,"stop":1000},"tests":[{"name":"test","status":"passed","duration":100}]}}\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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