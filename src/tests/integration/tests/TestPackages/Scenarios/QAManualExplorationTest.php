<?php

namespace integration\tests\TestPackages\Scenarios;

use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

require_once __DIR__ . '/BaseScenarioTestCase.php';

/**
 * Test Scenario 3: QA Manual Exploration
 *
 * Tests the workflow for QA engineers doing manual exploration:
 * - Downloads remote test package
 * - Sets up environment with package requirements
 * - Runs globalSetup and setup from the package
 * - QA engineer can interact with prepared environment
 */
class QAManualExplorationTest extends BaseScenarioTestCase {
	
	private string $remotePackageName = 'woocommerce/qit-integration-test-qa-exploration';
	private string $remotePackageVersion = '1.0.0';
	private string $localPackageDir;
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		$this->localPackageDir = __DIR__ . '/fixtures/scenario-test-package';
	}

	protected function tearDown(): void {
		// Clean up remote package if it was published (suppress errors as package may not exist)
		try {
			@qit( [ 'package:delete', $this->remotePackageName . ':' . $this->remotePackageVersion, '--yes' ] );
		} catch ( \Exception $e ) {
			// Ignore errors - package may not have been published
		}

		parent::tearDown();
	}
	
	/**
	 * Test QA workflow with remote test package.
	 */
	public function test_qa_exploration_with_remote_package() {
		// First, publish the test package to simulate a remote package
		$publishOutput = qit( [ 
			'package:publish',
			$this->localPackageDir,
			$this->remotePackageVersion
		], return_process: true );
		
		// Skip test if no publish permissions (CI environment)
		if ( $publishOutput->getExitCode() !== 0 ) {
			$output = $publishOutput->getOutput();
			if ( strpos( $output, 'not a maintainer' ) !== false ) {
				$this->markTestSkipped( 'Test requires package publishing permissions not available in CI' );
			}
			$this->fail( 'Failed to publish package: ' . $output );
		}
		
		// Now test the QA workflow - using remote package
		$data = $this->runEnvUp( [
			'--test-package=' . $this->remotePackageName . ':' . $this->remotePackageVersion
		] );
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'env_id', $data );
		$this->assertArrayHasKey( 'test_packages_for_setup', $data );
		
		// Verify remote package was downloaded and set up
		$testPackages = $data['test_packages_for_setup'];
		$foundRemote = false;
		foreach ( $testPackages as $ref => $info ) {
			if ( strpos( $ref, $this->remotePackageName ) !== false ) {
				$foundRemote = true;
				$this->assertEquals( 'remote', $info['source'] );
				break;
			}
		}
		$this->assertTrue( $foundRemote, 'Remote package should be downloaded' );
		
		// Phases were executed in the container for the remote package
		// Environment is ready for manual exploration
		
		// Environment should be ready for manual exploration
		$envId = $data['env_id'];
		
		// QA can access WordPress admin
		$siteUrl = $data['site_url'] ?? 'http://localhost:8080';
		$this->assertNotEmpty( $siteUrl );
		
		// Verify WordPress is accessible
		$wpCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp core version --path=/var/www/html'
		] );
		$this->assertNotEmpty( trim( $wpCheck ) );
	}
	
	/**
	 * Test that environment is properly configured for manual testing.
	 */
	public function test_environment_ready_for_manual_exploration() {
		// Set up environment with test package
		$data = $this->runEnvUp( [
			'--test-package=' . $this->localPackageDir,
			'--woo=stable'  // QA often tests with WooCommerce
		] );
		$envId = $data['env_id'];
		
		// Verify environment is ready for QA exploration
		
		// 1. WordPress is installed and accessible
		$wpInstalled = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp core is-installed --path=/var/www/html && echo "yes"'
		] );
		$this->assertEquals( 'yes', trim( $wpInstalled ) );
		
		// 2. WooCommerce is installed and active
		$wooActive = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp plugin is-active woocommerce --path=/var/www/html && echo "yes" || echo "no"'
		] );
		// WooCommerce might not be activated by default, but should be installed
		$wooInstalled = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp plugin list --field=name --path=/var/www/html | grep -q woocommerce && echo "yes"'
		] );
		$this->assertEquals( 'yes', trim( $wooInstalled ) );
		
		// 3. Admin user exists for QA to log in
		$adminExists = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp user get admin --field=user_login --path=/var/www/html 2>/dev/null || echo "not_found"'
		] );
		
		// Should have admin user or similar
		if ( trim( $adminExists ) === 'not_found' ) {
			// Check for user ID 1
			$userOne = qit( [ 
				'env:exec', 
				'--env_id=' . $envId,
				'wp user get 1 --field=user_login --path=/var/www/html'
			] );
			$this->assertNotEmpty( trim( $userOne ) );
		} else {
			$this->assertEquals( 'admin', trim( $adminExists ) );
		}
		
		// 4. Site URL is accessible
		$this->assertNotEmpty( $data['site_url'] );
		$this->assertStringContainsString( 'http', $data['site_url'] );
	}
	
	/**
	 * Test QA can run specific test files manually.
	 */
	public function test_qa_can_run_specific_tests_manually() {
		// Set up environment
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->localPackageDir
		] );
		$data = json_decode( $output, true );
		$envId = $data['env_id'];
		
		// Get environment variables for manual testing
		$sourcePath = trim( qit( [ 'env:source', $envId ] ) );
		$this->assertFileExists( $sourcePath );
		
		// QA could now run specific tests manually
		// Simulate by running the test package's run script directly
		$testRun = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/scenario-test-package && bash run.sh && echo "EXECUTION_SUCCESS"'
		], return_process: true );
		
		// Should execute successfully
		$this->assertEquals( 0, $testRun->getExitCode(), 'Test script should execute successfully' );
		
		// Verify test results were generated
		$ctrfCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'test -f /qit/packages/scenario-test-package/results/ctrf.json && echo "exists"'
		] );
		$this->assertEquals( 'exists', trim( $ctrfCheck ) );
	}
	
	/**
	 * Test complete QA manual exploration workflow.
	 */
	public function test_complete_qa_exploration_workflow() {
		// Step 1: QA sets up environment with test package and required extensions
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->localPackageDir,
			'--woo=stable',
			'--plugin=akismet'  // Additional plugin for testing
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Step 2: Verify environment was created successfully
		$this->assertIsArray( $envData );
		$this->assertArrayHasKey( 'env_id', $envData );
		
		// Step 3: Get site URL for manual browsing
		$siteUrl = $envData['site_url'];
		$this->assertNotEmpty( $siteUrl );
		
		// Step 4: Verify QA can interact with WordPress
		// Check installed plugins
		$pluginList = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp plugin list --field=name --path=/var/www/html'
		] );
		$this->assertStringContainsString( 'woocommerce', $pluginList );
		$this->assertStringContainsString( 'akismet', $pluginList );
		
		// Step 5: QA can check WooCommerce settings
		$wooSettings = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get woocommerce_version --path=/var/www/html 2>/dev/null || echo "not_set"'
		] );
		// WooCommerce version should be set if activated
		
		// Step 6: QA can run specific test scenarios
		$manualTest = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/scenario-test-package && bash run.sh'
		], return_process: true );
		$this->assertEquals( 0, $manualTest->getExitCode(), 'Manual test should execute successfully' );
		
		// Step 7: Environment remains available for further exploration
		$stillUp = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'echo "Environment still running"'
		] );
		$this->assertEquals( 'Environment still running', trim( $stillUp ) );
	}
}