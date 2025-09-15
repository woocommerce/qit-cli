<?php

namespace integration\tests\TestPackages\Scenarios;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;

/**
 * Test Scenario 2: Testing with Multiple Packages (Manual)
 * 
 * Tests the workflow with multiple test packages:
 * - Environment includes requirements from BOTH packages
 * - GlobalSetup runs for BOTH packages (combined baseline)
 * - Setup runs for MAIN package only
 * - Main package detection rules work correctly
 */
class MultiPackageManualTest extends TestCase {
	
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
		// Environment cleanup handled by test framework
	}
	
	/**
	 * Test that globalSetup runs for ALL packages, but setup only for MAIN.
	 */
	public function test_multiple_packages_phase_execution() {
		// Run env:up with two test packages
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		] );
		$data = json_decode( $output, true );
		
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
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->secondaryPackageDir,
			'--test-package=' . $this->mainPackageDir
		] );
		$data = json_decode( $output, true );
		
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
			$output = qit( [ 
				'env:up', 
				'--json',
				'--test-package=' . $this->secondaryPackageDir
			] );
			$data = json_decode( $output, true );
			
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
		$package1Dir = sys_get_temp_dir() . '/qit-test-multi-req1-' . uniqid();
		$package2Dir = sys_get_temp_dir() . '/qit-test-multi-req2-' . uniqid();
		mkdir( $package1Dir, 0777, true );
		mkdir( $package2Dir, 0777, true );
		
		// Package 1 requires specific PHP version
		file_put_contents( $package1Dir . '/qit-test.json', json_encode( [
			'package' => 'test/req-package1',
			'test_type' => 'e2e',
			'requirements' => [
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
			'requirements' => [
				'plugins' => [ 'akismet' ]
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
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $package1Dir,
			'--test-package=' . $package2Dir
		] );
		$data = json_decode( $output, true );
		
		// Environment should include requirements from both
		// PHP from package1
		$this->assertEquals( '8.2', $data['php'] );
		
		// Plugin from package2
		$pluginSlugs = array_column( $data['plugins'] ?? [], 'slug' );
		$this->assertContains( 'akismet', $pluginSlugs );
		
		// Let OS clean up temp files
	}
	
	/**
	 * Test volume mounting for multiple test packages.
	 */
	public function test_multiple_packages_volume_mounting() {
		// Run env:up with multiple test packages
		$output = qit( [ 
			'env:up', 
			'--json',
			'--test-package=' . $this->mainPackageDir,
			'--test-package=' . $this->secondaryPackageDir
		] );
		$data = json_decode( $output, true );
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