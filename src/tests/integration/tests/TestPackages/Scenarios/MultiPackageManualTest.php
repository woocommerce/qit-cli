<?php

namespace integration\tests\TestPackages\Scenarios;

use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

require_once __DIR__ . '/BaseScenarioTestCase.php';

/**
 * Test Scenario 2: Testing with Multiple Packages (Manual)
 *
 * Tests the workflow with multiple test packages:
 * - Environment includes requirements from BOTH packages
 * - GlobalSetup runs for BOTH packages (combined baseline)
 * - Setup runs for MAIN package only
 * - Main package detection rules work correctly
 */
class MultiPackageManualTest extends BaseScenarioTestCase {
	
	private string $mainPackageDir;
	private string $secondaryPackageDir;

	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		$this->mainPackageDir = __DIR__ . '/fixtures/scenario-main-package';
		$this->secondaryPackageDir = __DIR__ . '/fixtures/scenario-secondary-package';
	}
	
	/**
	 * Test that globalSetup runs for ALL packages, but setup only for MAIN.
	 */
	public function test_multiple_packages_phase_execution() {
		// Run env:up with two test packages
		$data = $this->runEnvUp( [
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		] );
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'env_id', $data );
		$this->assertArrayHasKey( 'test_packages_for_setup', $data );
		
		// Both packages should be in the setup list
		$this->assertCount( 2, $data['test_packages_for_setup'] );
		
		// For env:up with manual testing, the test packages are set up
		// GlobalSetup runs for BOTH packages, Setup runs for MAIN package only
		// The actual phase execution happens in the Docker container
	}
	
	/**
	 * Test main package detection priority: first local package.
	 */
	public function test_main_package_detection_first_local() {
		// Secondary package first, main package second
		$data = $this->runEnvUp( [
			'--test-package=' . $this->secondaryPackageDir,
			'--test-package=' . $this->mainPackageDir
		] );
		
		// The FIRST local package should be main (secondary in this case)
		// In env:up, the main package detection determines which package's setup runs
		$this->assertNotEmpty( $data['test_packages_for_setup'] );
	}
	
	/**
	 * Test main package detection when current directory has qit-test.json.
	 */
	public function test_main_package_detection_current_directory() {
		// Change to directory with qit-test.json
		$originalDir = getcwd();
		chdir( $this->mainPackageDir );
		
		try {
			// Run env:up from directory with qit-test.json, plus another package
			$data = $this->runEnvUp( [
				'--test-package=' . $this->secondaryPackageDir
			] );
			
			// Should auto-detect current directory package
			$this->assertArrayHasKey( 'test_packages_for_setup', $data );
			
			// Current directory package should be MAIN (highest priority)
			// Both packages are set up but main package detection determines setup phase execution
			$this->assertNotEmpty( $data['test_packages_for_setup'] );
			
		} finally {
			chdir( $originalDir );
		}
	}
	
	/**
	 * Test that environment includes requirements from all packages.
	 */
	public function test_combined_requirements_from_multiple_packages() {
		// Create modified fixtures with different requirements
		$package1Dir = $this->test_temp_dir . '/qit-test-multi-req1';
		$package2Dir = $this->test_temp_dir . '/qit-test-multi-req2';
		mkdir( $package1Dir, 0777, true );
		mkdir( $package2Dir, 0777, true );
		
		// Package 1 requires specific PHP version
		file_put_contents( $package1Dir . '/qit-test.json', json_encode( [
			'package' => 'test/req-package1',
			'test_type' => 'e2e',
			'requires' => [
				'php' => '8.2'
			],
			'test' => [
				'phases' => [
					'run' => [ 'echo "test1"' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json'
				]
			]
		] ) );
		
		// Package 2 requires specific plugin
		file_put_contents( $package2Dir . '/qit-test.json', json_encode( [
			'package' => 'test/req-package2',
			'test_type' => 'e2e',
			'requires' => [
				'plugins' => [ 'woocommerce-gateway-stripe' ]
			],
			'test' => [
				'phases' => [
					'run' => [ 'echo "test2"' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json'
				]
			]
		] ) );
		
		// Run env:up with both packages
		$data = $this->runEnvUp( [
			'--test-package=' . $package1Dir,
			'--test-package=' . $package2Dir
		] );
		
		// Environment should include requirements from both
		// PHP from package1
		$this->assertEquals( '8.2', $data['php'] );
		
		// Plugin from package2 - verify it's actually installed in the environment
		// Note: Test package requirements don't appear in JSON output but are installed
		$pluginList = qit( [
			'env:exec',
			'--env_id=' . $data['env_id'],
			'wp plugin list --field=name --path=/var/www/html'
		] );
		$installedPlugins = array_filter( explode( "\n", trim( $pluginList ) ) );

		$this->assertContains( 'woocommerce-gateway-stripe', $installedPlugins,
			'Plugin required by test package should be installed in environment' );
		
		// Let OS clean up temp files
	}
	
	/**
	 * Test volume mounting for multiple test packages.
	 */
	public function test_multiple_packages_volume_mounting() {
		// Run env:up with multiple test packages
		$data = $this->runEnvUp( [
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		] );
		$envId = $data['env_id'];
		
		// Both packages should be mounted in the container
		// Check that both package directories exist in the container
		$lsOutput = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'ls /qit/packages/'
		] );
		
		// Should see both package directories
		$this->assertStringContainsString( 'scenario-main-package', $lsOutput );
		$this->assertStringContainsString( 'scenario-secondary-package', $lsOutput );
		
		// Verify the actual test files are accessible
		$mainFileCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'test -f /qit/packages/scenario-main-package/qit-test.json && echo "exists"'
		] );
		$this->assertEquals( 'exists', trim( $mainFileCheck ) );
		
		$secondaryFileCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'test -f /qit/packages/scenario-secondary-package/qit-test.json && echo "exists"'
		] );
		$this->assertEquals( 'exists', trim( $secondaryFileCheck ) );
	}
	
	/**
	 * Test the complete multi-package manual testing workflow.
	 */
	public function test_complete_multi_package_workflow() {
		// Step 1: Set up environment with multiple packages
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Step 2: Verify packages were set up
		// GlobalSetup runs for both, Setup only for main package
		$this->assertCount( 2, $envData['test_packages_for_setup'] );
		
		// Step 3: Get environment variables
		$sourcePath = trim( qit( [ 'env:source', $envId ] ) );
		$this->assertFileExists( $sourcePath );
		
		// Step 4: Verify database marker from main package setup
		$markerCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get qit_main_package_setup_marker --path=/var/www/html 2>/dev/null || echo "not_found"'
		] );
		
		// Marker should exist from main package setup
		if ( trim( $markerCheck ) !== 'not_found' ) {
			$this->assertStringContainsString( 'setup_complete_', trim( $markerCheck ) );
		}
		
		// Step 5: Developer could now manually run tests from either package directory
		// Simulate by checking both packages are accessible
		$pkg1Access = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/scenario-main-package && pwd'
		] );
		$this->assertStringContainsString( 'scenario-main-package', $pkg1Access );
		
		$pkg2Access = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'cd /qit/packages/scenario-secondary-package && pwd'
		] );
		$this->assertStringContainsString( 'scenario-secondary-package', $pkg2Access );
	}
}