<?php

namespace integration\tests\TestPackages\Scenarios;

use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

require_once __DIR__ . '/BaseScenarioTestCase.php';

/**
 * Test Scenario 5: Debugging Failed CI Tests
 * 
 * Tests the workflow for debugging CI failures locally:
 * - After CI failure, recreate exact environment for debugging
 * - env:up creates same environment as CI
 * - Runs same globalSetup and setup
 * - Developer can debug interactively
 * - Environment matches CI state exactly
 */
class DebuggingFailedCITest extends BaseScenarioTestCase {
	
	private string $failingPackageDir;
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		$this->failingPackageDir = sys_get_temp_dir() . '/qit-test-failing-' . uniqid();
		$this->createFailingTestPackage();
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		// Environment cleanup handled by test framework
		$this->cleanupFailingTestPackage();
	}
	
	/**
	 * Create a test package that fails in CI.
	 */
	private function createFailingTestPackage(): void {
		mkdir( $this->failingPackageDir, 0777, true );
		
		// Create a test that fails under certain conditions
		file_put_contents( $this->failingPackageDir . '/qit-test.json', json_encode( [
			'package' => 'test/failing-package-' . uniqid(), // Unique package name
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'setup' => ['bash setup.sh'],
					'run' => ['bash run-tests.sh']
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blobs'
				]
			]
		] ) );
		
		// Setup script that creates specific CI state
		file_put_contents( $this->failingPackageDir . '/setup.sh', '#!/bin/bash
echo "Running setup for failing-package"
# Create a specific state that causes tests to fail
wp option update ci_debug_flag "production" --path=/var/www/html
wp option update test_environment "ci" --path=/var/www/html
exit 0
' );
		
		// Test script that fails in CI-like conditions
		file_put_contents( $this->failingPackageDir . '/run-tests.sh', '#!/bin/bash
echo "Running tests for failing-package"

# Check if running in CI-like environment
CI_FLAG=$(wp option get ci_debug_flag --path=/var/www/html 2>/dev/null || echo "")

mkdir -p results

if [ "$CI_FLAG" = "production" ]; then
    echo "ERROR: Test fails in production mode!"
    # Generate failing CTRF using helper
    php -r \'
    require_once "/storage/qit/qit-cli/src/tests/integration/tests/Helpers/CTRFHelper.php";
    use QIT\IntegrationTests\Helpers\CTRFHelper;
    
    $ctrf = CTRFHelper::generate_valid_ctrf([
        "tool" => "failing-package",
        "tests" => [
            [
                "name" => "Production mode test",
                "status" => "failed",
                "duration" => 100,
                "message" => "Test fails in production mode"
            ]
        ]
    ]);
    
    file_put_contents("results/ctrf.json", json_encode($ctrf, JSON_PRETTY_PRINT));
    \'
    exit 1
else
    echo "Test passes in debug mode"
    # Generate passing CTRF using helper
    php -r \'
    require_once "/storage/qit/qit-cli/src/tests/integration/tests/Helpers/CTRFHelper.php";
    use QIT\IntegrationTests\Helpers\CTRFHelper;
    
    $ctrf = CTRFHelper::generate_valid_ctrf([
        "tool" => "failing-package",
        "tests" => [
            [
                "name" => "Production mode test",
                "status" => "passed",
                "duration" => 100
            ]
        ]
    ]);
    
    file_put_contents("results/ctrf.json", json_encode($ctrf, JSON_PRETTY_PRINT));
    \'
    exit 0
fi
' );
		
		chmod( $this->failingPackageDir . '/setup.sh', 0755 );
		chmod( $this->failingPackageDir . '/run-tests.sh', 0755 );
	}
	
	/**
	 * Clean up the failing test package.
	 */
	private function cleanupFailingTestPackage(): void {
		// Let OS clean up temp files - no manual cleanup needed
	}
	
	/**
	 * Test simulating a CI failure.
	 */
	public function test_simulate_ci_failure() {
		// Step 1: Run in CI mode (should fail)
		$proc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->failingPackageDir
		], return_process: true );
		
		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();
		
		// Should fail - the exit code indicates the test failed as expected
		$this->assertNotEquals( 0, $exitCode, 'Test should fail in CI mode' );
	}
	
	/**
	 * Test recreating CI environment locally for debugging.
	 */
	public function test_recreate_ci_environment_for_debugging() {
		// Step 1: Simulate CI failure first
		$ciProc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->failingPackageDir
		], return_process: true );
		
		$this->assertNotEquals( 0, $ciProc->getExitCode(), 'CI should fail' );
		
		// Step 2: Developer recreates environment locally for debugging
		$envData = $this->runEnvUp( [
			'--test-package=' . $this->failingPackageDir
		] );
		$envId = $envData['env_id'];
		
		// Step 3: Verify same setup ran
		// The setup phase created specific CI state in the container
		$this->assertNotEmpty( $envData['test_packages_for_setup'] );
		
		// Step 4: Verify CI state was recreated
		$ciFlag = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get ci_debug_flag --path=/var/www/html'
		] );
		$this->assertEquals( 'production', trim( $ciFlag ), 'CI state should be recreated' );
		
		$testEnv = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get test_environment --path=/var/www/html'
		] );
		$this->assertEquals( 'ci', trim( $testEnv ), 'Test environment should match CI' );
	}
	
	/**
	 * Test debugging workflow with environment modification.
	 */
	public function test_debug_and_fix_failing_test() {
		// Step 1: Set up environment matching CI
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->failingPackageDir
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Step 2: Run test manually to confirm failure
		$testRun = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/* && bash run-tests.sh'
		], return_process: true );
		
		$this->assertNotEquals( 0, $testRun->getExitCode(), 'Test should fail initially' );
		
		// Step 3: Developer debugs by changing the flag
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option update ci_debug_flag "debug" --path=/var/www/html'
		] );
		
		// Step 4: Run test again to verify fix
		$fixedRun = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/* && bash run-tests.sh'
		], return_process: true );
		
		$this->assertEquals( 0, $fixedRun->getExitCode(), 'Test should pass after fix' );
	}
	
	/**
	 * Test environment persistence for iterative debugging.
	 */
	public function test_environment_persistence_for_debugging() {
		// Set up environment
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->failingPackageDir
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Make some changes for debugging
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option add debug_session_1 "test1" --path=/var/www/html'
		] );
		
		// Environment should persist
		$check1 = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get debug_session_1 --path=/var/www/html'
		] );
		$this->assertEquals( 'test1', trim( $check1 ) );
		
		// Make more changes
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option add debug_session_2 "test2" --path=/var/www/html'
		] );
		
		// Both changes should persist
		$check2 = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get debug_session_2 --path=/var/www/html'
		] );
		$this->assertEquals( 'test2', trim( $check2 ) );
		
		// Use env:reset to go back to post-setup state
		qit( [ 'env:reset' ] );
		
		// Original CI state should be restored
		$ciFlag = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get ci_debug_flag --path=/var/www/html'
		] );
		$this->assertEquals( 'production', trim( $ciFlag ) );
		
		// Debug session changes should be gone
		$debugCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get debug_session_1 --path=/var/www/html 2>/dev/null || echo "not_found"'
		] );
		$this->assertEquals( 'not_found', trim( $debugCheck ) );
	}
	
	/**
	 * Test complete debugging workflow from CI failure to fix.
	 */
	public function test_complete_debugging_workflow() {
		// Step 1: Simulate CI failure
		$ciProc = qit( [ 
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->failingPackageDir,
			'--json'
		], return_process: true );
		
		$ciOutput = $ciProc->getOutput();
		$ciExitCode = $ciProc->getExitCode();
		
		// CI fails - exit code indicates failure
		$this->assertNotEquals( 0, $ciExitCode, 'CI should fail' );
		
		// Step 2: Developer recreates exact environment
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->failingPackageDir
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Step 3: Get environment variables for debugging
		$sourcePath = trim( qit( [ 'env:source', $envId ] ) );
		$this->assertFileExists( $sourcePath );
		
		// Step 4: Verify environment matches CI
		// The test packages were set up with the same state
		$this->assertNotEmpty( $envData['test_packages_for_setup'] );
		
		// Step 5: Run test to reproduce failure
		$reproduceRun = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/* && bash run-tests.sh'
		], return_process: true );
		
		$this->assertNotEquals( 0, $reproduceRun->getExitCode(), 'Should reproduce CI failure' );
		
		// Step 6: Debug and identify the issue
		$debugInfo = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option list --search="*debug*" --format=json --path=/var/www/html 2>/dev/null || echo "[]"'
		] );
		
		// Step 7: Fix the issue
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option update ci_debug_flag "debug" --path=/var/www/html'
		] );
		
		// Step 8: Verify fix works
		$fixedRun = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/* && bash run-tests.sh'
		], return_process: true );
		
		$this->assertEquals( 0, $fixedRun->getExitCode(), 'Test should pass after fix' );
		
		// Step 9: Developer can now update the test or code to handle both modes
		// This would involve editing the test files to work in both environments
	}
}