<?php

namespace integration\tests\Commands;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test RunE2ECommand with scaffolded packages.
 * 
 * These tests focus on real-world developer workflows:
 * 1. Quick smoke test for a plugin before release
 * 2. Testing against specific WooCommerce versions
 * 3. Developing and iterating on custom tests
 * 4. Testing multiple plugins together
 * 5. CI/CD friendly test execution
 */
class RunE2ECommandTest extends TestCase {

	/**
	 * Workflow 1: Developer creates their first custom E2E test
	 * 
	 * Scenario: A developer wants to add custom E2E tests to their plugin
	 * - They scaffold a test package
	 * - The scaffolded test works out-of-the-box
	 * - They can immediately see results
	 */
	public function test_developer_creates_first_custom_test(): void {
		$tempDir = null;
		$packageDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';
			
			// Scaffold a full test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=woocommerce',
				'--package=test-e2e',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			$this->assertFileExists( $packageDir . '/manifest.json' );
			$this->assertFileExists( $packageDir . '/package.json' );
			$this->assertFileExists( $packageDir . '/playwright.config.js' );
			$this->assertFileExists( $packageDir . '/tests/example.spec.js' );
			$this->assertDirectoryExists( $packageDir . '/bootstrap' );
			
			// Verify manifest content
			$manifest = json_decode( file_get_contents( $packageDir . '/manifest.json' ), true );
			$this->assertEquals( 'woocommerce', $manifest['namespace'] );
			$this->assertEquals( 'test-e2e', $manifest['package'] );
			$this->assertEquals( 'e2e', $manifest['test_type'] );
			
			// Create qit.json configuration
			$qit_json = [
				'test_types' => [
					'e2e' => [
						'default' => [
							'test_packages' => [ $packageDir ]
						]
					]
				]
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $qit_json, JSON_PRETTY_PRINT ) );
			
			// Run the test with tunnel enabled so Playwright can access the site
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--config', $configPath,
				'--tunnel',
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Verify it ran our scaffolded package
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			$this->assertStringContainsString( $packageDir . ' (globalSetup)', $output );
			$this->assertStringContainsString( $packageDir . ' (run)', $output );
			
			// The test should pass with tunnel enabled
			$this->assertEquals( 0, $exitCode, 'Test should succeed with tunnel enabled. Output: ' . $output );
			
			// Verify the test package was executed properly
			$this->assertStringContainsString( '[globalSetup] Starting global configuration...', $output );
			$this->assertStringContainsString( '[setup] Creating sample data ...', $output );
			$this->assertStringContainsString( 'Running 1 test using 1 worker', $output );
			$this->assertStringContainsString( '1 passed', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Workflow 2: Developer tests plugin compatibility with specific WooCommerce version
	 * 
	 * Scenario: Customer reported issue with WooCommerce 8.0.0
	 * - Developer needs to test against that specific version
	 * - Creates a simple test to reproduce the issue
	 */
	public function test_developer_tests_specific_woo_version(): void {
		$tempDir = null;
		$packageDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/compatibility-test';
			
			// Scaffold a test package for compatibility testing
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=mycompany',
				'--package=woo-compat-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Run the test against a specific WooCommerce version
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--woo', '8.0.0',
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Verify the test ran with the specified version
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'WooCommerce: 8.0.0', $output );
			
			// The test should complete (we're testing the workflow, not the actual compatibility)
			$this->assertContains( $exitCode, [0, 1], 'Test should complete. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Workflow 3: Developer runs quick smoke test before release
	 * 
	 * Scenario: About to release plugin update
	 * - Wants to quickly verify nothing is broken
	 * - Uses the default QIT tests without custom tests
	 */
	public function test_developer_quick_smoke_test(): void {
		// This would typically just run:
		// qit run:e2e my-plugin
		// But for this test, we'll use a minimal custom test
		
		$tempDir = null;
		$packageDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';
			
			// Scaffold a test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=woocommerce',
				'--package=test-cli',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Run the test using --test-package CLI option
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Verify the package was recognized
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			$this->assertStringContainsString( $packageDir . ' (globalSetup)', $output );
			$this->assertStringContainsString( $packageDir . ' (run)', $output );
			
			// The test should pass
			$this->assertEquals( 0, $exitCode, 'Test should succeed with CLI option. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test running e2e with local test package and custom plugin.
	 */
	public function test_run_e2e_with_local_package_and_custom_plugin(): void {
		$tempDir = null;
		$packageDir = null;
		$pluginDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';
			$pluginDir = $tempDir . '/test-plugin';
			
			// Create a simple test plugin
			mkdir( $pluginDir, 0755, true );
			file_put_contents( $pluginDir . '/test-plugin.php', '<?php
/**
 * Plugin Name: Test Plugin
 * Version: 1.0.0
 */
add_action( "init", function() {
	if ( defined( "WP_CLI" ) && WP_CLI ) {
		return;
	}
	wp_die( "Test plugin is active!" );
} );
' );
			
			// Scaffold a test package that tests for the plugin
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=custom',
				'--package=plugin-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Modify the test to check for our plugin message
			$testFile = $packageDir . '/tests/example.spec.js';
			file_put_contents( $testFile, "import { test, expect } from '@playwright/test';

test('plugin is active', async ({ page }) => {
  const response = await page.goto('/');
  expect(response?.status()).toBe(200);
  
  // Check that our test plugin is active
  await expect(page.locator('body')).toContainText('Test plugin is active!');
});
" );
			
			// Create qit.json configuration
			$qit_json = [
				'test_types' => [
					'e2e' => [
						'default' => [
							'test_packages' => [ $packageDir ]
						]
					]
				]
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $qit_json, JSON_PRETTY_PRINT ) );
			
			// Run the test with our custom plugin
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--plugin', $pluginDir,
				'--config', $configPath,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Verify the test ran
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			
			// The test should pass (our plugin message should be visible)
			$this->assertEquals( 0, $exitCode, 'Test should succeed with custom plugin. Output: ' . $output );
			$this->assertStringContainsString( '1 passed', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Workflow 4: CI/CD Integration
	 * 
	 * Scenario: Developer sets up automated testing in GitHub Actions
	 * - Needs JSON output for parsing results
	 * - Wants to fail the build on test failures
	 * - May need to test against matrix of versions
	 */
	public function test_ci_cd_integration_workflow(): void {
		$tempDir = null;
		$packageDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/ci-test-package';
			
			// Scaffold a test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=mycompany',
				'--package=ci-tests',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Run with JSON output (common in CI)
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--test-package', $packageDir,
				'--json',
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// In CI, we expect JSON output
			$this->assertJson( $output, 'CI should get JSON output' );
			
			// Parse the JSON to verify structure
			$result = json_decode( $output, true );
			$this->assertIsArray( $result );
			
			// Exit code should reflect test results
			$this->assertEquals( 0, $exitCode, 'CI build should pass when tests pass' );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Workflow 5: Testing plugin ecosystem
	 * 
	 * Scenario: Developer has multiple plugins that work together
	 * - Needs to test them all together
	 * - Wants to ensure no conflicts
	 */
	public function test_plugin_ecosystem_testing(): void {
		$tempDir = null;
		$packageDir = null;
		$plugin1Dir = null;
		$plugin2Dir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/ecosystem-tests';
			$plugin1Dir = $tempDir . '/payment-gateway';
			$plugin2Dir = $tempDir . '/shipping-addon';
			
			// Create two simple plugins that work together
			mkdir( $plugin1Dir, 0755, true );
			file_put_contents( $plugin1Dir . '/payment-gateway.php', '<?php
/**
 * Plugin Name: My Payment Gateway
 * Version: 1.0.0
 */
// Payment gateway implementation
' );
			
			mkdir( $plugin2Dir, 0755, true );
			file_put_contents( $plugin2Dir . '/shipping-addon.php', '<?php
/**
 * Plugin Name: My Shipping Addon
 * Version: 1.0.0
 */
// Shipping addon that integrates with payment gateway
' );
			
			// Scaffold test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=mycompany',
				'--package=ecosystem-tests',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Run tests with both plugins
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--plugin', $plugin1Dir,
				'--plugin', $plugin2Dir,
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Verify both plugins were loaded
			$this->assertStringContainsString( 'Activating plugins', $output );
			
			// Tests should run successfully
			$this->assertEquals( 0, $exitCode, 'Ecosystem test should pass. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Realistic Workflow: Developer copies test from docs/tutorial
	 * 
	 * Most developers start by copying an example test and modifying it
	 */
	public function test_developer_copies_example_test(): void {
		$tempDir = null;
		$packageDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/my-tests';
			
			// Developer scaffolds a package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=myshop',
				'--package=checkout-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Developer replaces the example test with something they found in docs
			$testFile = $packageDir . '/tests/example.spec.js';
			file_put_contents( $testFile, "import { test, expect } from '@playwright/test';

// Copied from WooCommerce docs
test('can add product to cart', async ({ page }) => {
  // This test probably won't work but developer will iterate
  await page.goto('/shop');
  await expect(page).toHaveTitle(/Shop/);
});
" );
			
			// Developer runs it to see what happens
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			
			// Developer's test might fail, but the infrastructure works
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Realistic Workflow: Developer keeps test package in their plugin repo
	 * 
	 * Many developers create a qit-tests folder in their plugin repository
	 */
	public function test_developer_tests_in_plugin_repo(): void {
		$tempDir = null;
		$pluginDir = null;
		$testDir = null;
		
		try {
			$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
			mkdir( $tempDir, 0755, true );
			
			// Simulate a plugin repository structure
			$pluginDir = $tempDir . '/my-awesome-plugin';
			mkdir( $pluginDir, 0755, true );
			
			// Plugin files
			file_put_contents( $pluginDir . '/my-awesome-plugin.php', '<?php
/**
 * Plugin Name: My Awesome Plugin
 */
' );
			
			// Developer creates qit-tests subdirectory
			$testDir = $pluginDir . '/qit-tests';
			
			// Scaffold tests in the plugin directory
			qit( [
				'package:scaffold',
				$testDir,
				'--namespace=myawesome',
				'--package=plugin-tests',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Developer runs from plugin root with relative path
			$originalCwd = getcwd();
			chdir( $pluginDir );
			
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--test-package', './qit-tests',
				'--plugin', '.',  // Current directory as plugin
			], return_process: true );
			
			chdir( $originalCwd );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should work with relative paths
			$this->assertStringContainsString( 'Running Test Packages', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

}