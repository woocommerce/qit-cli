<?php

namespace integration\tests\TestPackages\Scenarios;

use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

require_once __DIR__ . '/BaseScenarioTestCase.php';

/**
 * Test Scenario 1: Developer Testing Their Own Package
 * 
 * Tests the workflow where a developer is testing their own package locally:
 * - env:up auto-detects test package from current directory
 * - GlobalSetup and Setup run for the test package
 * - Database state is saved for env:reset
 * - Developer can run tests manually
 */
class DeveloperWorkflowTest extends BaseScenarioTestCase {
	
	private string $fixtureDir;
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixtureDir = __DIR__ . '/fixtures/scenario-test-package';
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		// Environment cleanup handled by test framework
	}
	
	/**
	 * Test that env:up from a directory with qit-test.json auto-detects the test package.
	 */
	public function test_env_up_auto_detects_test_package_from_current_directory() {
		// Change to directory with qit-test.json
		$originalDir = getcwd();
		chdir( $this->fixtureDir );
		
		try {
			// Run env:up without specifying --test-package (should auto-detect)
			$data = $this->runEnvUp( [] );
			
			$this->assertIsArray( $data );
			$this->assertArrayHasKey( 'env_id', $data );
			$this->assertArrayHasKey( 'test_packages_for_setup', $data );
			
			// Should have auto-detected the test package
			$this->assertNotEmpty( $data['test_packages_for_setup'] );
			
			// Verify the test package was detected
			$testPackages = $data['test_packages_for_setup'];
			$foundPackage = false;
			foreach ( $testPackages as $path => $info ) {
				if ( strpos( $path, 'scenario-test-package' ) !== false ) {
					$foundPackage = true;
					$this->assertEquals( 'scenario/test-package', $info['package_id'] );
					break;
				}
			}
			$this->assertTrue( $foundPackage, 'Test package should be auto-detected from current directory' );
			
			// For env:up, phases run in the container
			// Verify the test package was properly set up
			$this->assertNotEmpty( $data['test_packages_for_setup'] );
			
			// The actual phase execution happens in the Docker container
			// run/teardown/globalTeardown are only for run:e2e, not env:up
			
		} finally {
			chdir( $originalDir );
		}
	}
	
	/**
	 * Test that env:up with explicit --test-package works.
	 */
	public function test_env_up_with_explicit_test_package() {
		// Run env:up with explicit test package
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->fixtureDir
		] );
		$data = json_decode( $output, true );
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'env_id', $data );
		$this->assertArrayHasKey( 'test_packages_for_setup', $data );
		
		// For env:up, phases run in the container, not on host
		// We can verify the environment is set up with test packages
		$this->assertNotEmpty( $data['test_packages_for_setup'] );
		
		// To verify phases ran, we'd need to check inside the container
		// or look at the command output for phase execution messages
	}
	
	/**
	 * Test that env:source provides environment variables for manual testing.
	 */
	public function test_env_source_provides_environment_variables() {
		// Set up environment
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->fixtureDir
		] );
		$data = json_decode( $output, true );
		$envId = $data['env_id'];
		
		// Get environment source file
		$sourceOutput = qit( [ 'env:source', $envId ] );
		
		// Should return a path to a source file
		$this->assertNotEmpty( $sourceOutput );
		$this->assertFileExists( trim( $sourceOutput ) );
		
		// Read the source file
		$sourceContent = file_get_contents( trim( $sourceOutput ) );
		
		// Should contain export statements for environment variables
		$this->assertStringContainsString( 'export QIT_SITE_URL=', $sourceContent );
		$this->assertStringContainsString( 'export QIT_ENV_ID="' . $envId . '"', $sourceContent );
		$this->assertStringContainsString( 'export QIT_', $sourceContent ); // Should have various QIT_ variables
	}
	
	/**
	 * Test that env:reset restores database to post-setup state.
	 */
	public function test_env_reset_restores_database_state() {
		// Set up environment with test package
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . __DIR__ . '/fixtures/scenario-main-package'
		] );
		$data = json_decode( $output, true );
		$envId = $data['env_id'];
		
		// The setup phase should have created a database marker
		// Let's verify it exists
		$checkMarker = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get qit_main_package_setup_marker --path=/var/www/html'
		] );
		$this->assertStringContainsString( 'setup_complete_', trim( $checkMarker ) );
		
		// Modify the database (simulate test changes)
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option update qit_main_package_setup_marker "modified_by_test" --path=/var/www/html'
		] );
		
		// Verify modification
		$modifiedMarker = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get qit_main_package_setup_marker --path=/var/www/html'
		] );
		$this->assertEquals( 'modified_by_test', trim( $modifiedMarker ) );
		
		// Run env:reset with explicit env_id for deterministic behavior
		qit( [ 'env:reset', $envId ] );
		
		// Check that database was restored to post-setup state
		$restoredMarker = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get qit_main_package_setup_marker --path=/var/www/html'
		] );
		$this->assertStringContainsString( 'setup_complete_', trim( $restoredMarker ) );
		$this->assertNotEquals( 'modified_by_test', trim( $restoredMarker ) );
	}
	
	/**
	 * Test the complete developer workflow.
	 */
	public function test_complete_developer_workflow() {
		// Step 1: Developer navigates to their test directory
		$originalDir = getcwd();
		chdir( $this->fixtureDir );
		
		try {
			// Step 2: Run env:up (auto-detects test package)
			$envData = $this->runEnvUp( [] );
			$envId = $envData['env_id'];
			
			// Step 3: Get environment variables
			$sourcePath = trim( qit( [ 'env:source', $envId ] ) );
			$this->assertFileExists( $sourcePath );
			
			// Step 4: Verify test package was set up
			$this->assertNotEmpty( $envData['test_packages_for_setup'] );
			
			// Step 5: Simulate running tests manually (would be npx playwright test)
			// We'll just verify the environment is ready
			$wpVersion = qit( [ 
				'env:exec', 
				'--env_id=' . $envId,
				'wp core version --path=/var/www/html'
			] );
			$this->assertNotEmpty( trim( $wpVersion ) );
			
			// Step 6: Reset environment for another test run
			qit( [ 'env:reset', $envId ] );
			
			// Environment should still be up and ready for more testing
			$wpVersionAfterReset = qit( [ 
				'env:exec', 
				'--env_id=' . $envId,
				'wp core version --path=/var/www/html'
			] );
			$this->assertEquals( trim( $wpVersion ), trim( $wpVersionAfterReset ) );
			
		} finally {
			chdir( $originalDir );
		}
	}
}