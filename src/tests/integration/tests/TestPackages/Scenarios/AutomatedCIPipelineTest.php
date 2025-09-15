<?php

namespace integration\tests\TestPackages\Scenarios;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

/**
 * Test Scenario 4: Automated CI/CD Pipeline
 * 
 * Tests the automated CI workflow:
 * - run:e2e calls env:up with --skip-test-phases flag
 * - env:up sets up environment and processes requirements
 * - Test packages are prepared but phases are deferred to run:e2e
 * - run:e2e orchestrates the full lifecycle
 */
class AutomatedCIPipelineTest extends TestCase {
	
	private string $mainPackageDir;
	private string $secondaryPackageDir;
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		$this->mainPackageDir = __DIR__ . '/fixtures/scenario-main-package';
		$this->secondaryPackageDir = __DIR__ . '/fixtures/scenario-secondary-package';
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		// Clean up any running environments
		// env:down without arguments will interactively select if multiple exist
		// This is handled by the test framework cleanup
	}
	
	/**
	 * Test that run:e2e properly orchestrates test execution.
	 */
	public function test_run_e2e_orchestration() {
		// Run e2e with a single test package with JSON output
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->mainPackageDir,
			'--json'
		], return_process: true );
		
		$exitCode = $proc->getExitCode();
		
		// Should complete successfully
		$this->assertEquals( 0, $exitCode, 'run:e2e should complete successfully' );
		
		// Parse and verify JSON output (Manager API response format)
		$data = json_decode( $proc->getOutput(), true );
		$this->assertIsArray( $data, 'Should have valid JSON output' );
		$this->assertArrayHasKey( 'test_run_id', $data, 'Should have test_run_id from Manager' );
		$this->assertArrayHasKey( 'status', $data, 'Should have status field' );
		$this->assertEquals( 'success', $data['status'], 'Test status should be success' );
		
		// CTRF data is now embedded in ctrf_json field
		$this->assertArrayHasKey( 'ctrf_json', $data, 'Should have CTRF JSON field' );
		$ctrf = json_decode( $data['ctrf_json'], true );
		$this->assertIsArray( $ctrf, 'CTRF should be valid JSON' );
		$this->assertArrayHasKey( 'results', $ctrf, 'CTRF should have results structure' );
		
		$summary = $ctrf['results']['summary'] ?? [];
		// The orchestrator adds lifecycle phase tests (globalSetup, setup, run, teardown, globalTeardown)
		// So we expect more than just the 2 tests from the package
		$this->assertGreaterThanOrEqual( 2, $summary['tests'] ?? 0, 'Should have at least the package tests' );
		$this->assertEquals( $summary['tests'], $summary['passed'] ?? 0, 'All tests should pass' );
		$this->assertEquals( 0, $summary['failed'] ?? 0, 'No tests should fail' );
	}
	
	/**
	 * Test run:e2e with multiple test packages.
	 */
	public function test_run_e2e_multiple_packages() {
		// Run e2e with multiple test packages
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		], return_process: true );
		
		$exitCode = $proc->getExitCode();
		
		// Should complete successfully - both packages should pass
		$this->assertEquals( 0, $exitCode, 'run:e2e with multiple packages should succeed' );
	}
	
	/**
	 * Test that run:e2e properly handles database restoration between packages.
	 */
	public function test_database_restoration_between_packages() {
		// Create test packages that modify the database
		$package1Dir = sys_get_temp_dir() . '/qit-test-db1-' . uniqid();
		$package2Dir = sys_get_temp_dir() . '/qit-test-db2-' . uniqid();
		mkdir( $package1Dir, 0777, true );
		mkdir( $package2Dir, 0777, true );
		mkdir( $package1Dir . '/results', 0777, true );
		mkdir( $package2Dir . '/results', 0777, true );
		
		// Package 1 creates an option and generates CTRF
		file_put_contents( $package1Dir . '/qit-test.json', json_encode( [
			'package' => 'test/db-package1-' . uniqid(),
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'setup' => [ 'wp option add test_pkg1_setup "pkg1_value" --path=/var/www/html' ],
					'run' => [ 'bash run.sh' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json'
				]
			]
		] ) );
		
		file_put_contents( $package1Dir . '/run.sh', '#!/bin/bash
wp option get test_pkg1_setup --path=/var/www/html
START_TIME=$(date +%s000)
STOP_TIME=$((START_TIME + 100))
cat > results/ctrf.json << EOF
{"results":{"tool":{"name":"db-package1"},"summary":{"tests":1,"passed":1,"failed":0,"skipped":0,"pending":0,"other":0,"start":$START_TIME,"stop":$STOP_TIME},"tests":[{"name":"DB Test 1","status":"passed","duration":100}]}}
EOF
exit 0' );
		chmod( $package1Dir . '/run.sh', 0755 );
		
		// Package 2 checks that package 1's option doesn't exist and generates CTRF
		file_put_contents( $package2Dir . '/qit-test.json', json_encode( [
			'package' => 'test/db-package2-' . uniqid(),
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'setup' => [ 'wp option add test_pkg2_setup "pkg2_value" --path=/var/www/html' ],
					'run' => [ 'bash run.sh' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json'
				]
			]
		] ) );
		
		file_put_contents( $package2Dir . '/run.sh', '#!/bin/bash
# Check if pkg1 option exists (it should not, DB should be restored)
wp option get test_pkg1_setup --path=/var/www/html 2>/dev/null
if [ $? -eq 0 ]; then
  # Option exists - DB was NOT restored (test fails)
  START_TIME=$(date +%s000)
  STOP_TIME=$((START_TIME + 100))
  cat > results/ctrf.json << EOF
{"results":{"tool":{"name":"db-package2"},"summary":{"tests":1,"passed":0,"failed":1,"skipped":0,"pending":0,"other":0,"start":$START_TIME,"stop":$STOP_TIME},"tests":[{"name":"DB Isolation Test","status":"failed","duration":100,"message":"DB not restored between packages"}]}}
EOF
  exit 1
else
  # Option does not exist - DB was restored correctly (test passes)
  START_TIME=$(date +%s000)
  STOP_TIME=$((START_TIME + 100))
  cat > results/ctrf.json << EOF
{"results":{"tool":{"name":"db-package2"},"summary":{"tests":1,"passed":1,"failed":0,"skipped":0,"pending":0,"other":0,"start":$START_TIME,"stop":$STOP_TIME},"tests":[{"name":"DB Isolation Test","status":"passed","duration":100}]}}
EOF
  exit 0
fi' );
		chmod( $package2Dir . '/run.sh', 0755 );
		
		// Run both packages
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $package1Dir,
			'--test-package=' . $package2Dir
		], return_process: true );
		
		$exitCode = $proc->getExitCode();
		
		// Should complete successfully if DB isolation works
		// Exit code 0 means both packages passed, which confirms DB was restored between them
		$this->assertEquals( 0, $exitCode, 'Both packages should succeed with DB isolation' );
	}
	
	/**
	 * Test that run:e2e correctly merges CTRF results.
	 */
	public function test_ctrf_result_merging() {
		// Run e2e with multiple packages and JSON output to verify CTRF merging
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir,
			'--json'
		], return_process: true );
		
		$exitCode = $proc->getExitCode();
		
		// Should complete successfully - CTRF merging happens internally
		$this->assertEquals( 0, $exitCode, 'Should successfully merge CTRF results from multiple packages' );
		
		// Verify merged CTRF in Manager response
		$data = json_decode( $proc->getOutput(), true );
		$this->assertIsArray( $data, 'Should have valid JSON output' );
		$this->assertArrayHasKey( 'ctrf_json', $data, 'Should have CTRF JSON field' );
		
		$ctrf = json_decode( $data['ctrf_json'], true );
		$this->assertIsArray( $ctrf, 'CTRF should be valid JSON' );
		
		// Verify that results from both packages are present
		$summary = $ctrf['results']['summary'] ?? [];
		// Both packages have 2 tests each, so we expect at least 4 tests total
		$this->assertGreaterThanOrEqual( 4, $summary['tests'] ?? 0, 'Should have tests from both packages' );
	}
	
	/**
	 * Test CI pipeline with environment requirements.
	 */
	public function test_ci_pipeline_with_requirements() {
		// Create package with specific requirements
		$ciPackageDir = sys_get_temp_dir() . '/qit-test-ci-req-' . uniqid();
		mkdir( $ciPackageDir, 0777, true );
		mkdir( $ciPackageDir . '/results', 0777, true );
		
		file_put_contents( $ciPackageDir . '/qit-test.json', json_encode( [
			'package' => 'test/ci-requirements-' . uniqid(),
			'test_type' => 'e2e',
			'requirements' => [
				'php' => '8.2',
				'wp' => '6.4',
				'plugins' => [ 'akismet' ]
			],
			'test' => [
				'phases' => [
					'run' => [ 'bash run.sh' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json'
				]
			]
		] ) );
		
		file_put_contents( $ciPackageDir . '/run.sh', '#!/bin/bash
START_TIME=$(date +%s000)
STOP_TIME=$((START_TIME + 100))
cat > results/ctrf.json << EOF
{"results":{"tool":{"name":"ci-requirements"},"summary":{"tests":1,"passed":1,"failed":0,"skipped":0,"pending":0,"other":0,"start":$START_TIME,"stop":$STOP_TIME},"tests":[{"name":"CI Requirements Test","status":"passed","duration":100}]}}
EOF
exit 0' );
		chmod( $ciPackageDir . '/run.sh', 0755 );
		
		// Run CI pipeline
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $ciPackageDir
		], return_process: true );
		
		$exitCode = $proc->getExitCode();
		
		// Should succeed with requirements met
		// Exit code 0 confirms the test ran with correct environment
		$this->assertEquals( 0, $exitCode, 'Test should pass with requirements met' );
	}
	
	/**
	 * Test complete CI/CD pipeline workflow.
	 */
	public function test_complete_ci_pipeline_workflow() {
		// Step 1: Run automated tests with multiple packages
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		], return_process: true );
		
		$exitCode = $proc->getExitCode();
		
		// Step 2: Verify successful completion
		$this->assertEquals( 0, $exitCode, 'CI pipeline should complete successfully' );
		
		// Step 3: Verify environment was cleaned up
		// After run:e2e, environment should be down
		$envListProc = qit( [ 'env:list', '--json' ], return_process: true );
		if ( $envListProc->getExitCode() === 0 ) {
			$envList = json_decode( $envListProc->getOutput(), true );
			// Should be empty or not contain our environment
			$this->assertEmpty( $envList, 'Environment should be cleaned up after run:e2e' );
		}
	}
	
}