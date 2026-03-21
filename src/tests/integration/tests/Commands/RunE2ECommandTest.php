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
use PHPUnit\Framework\Attributes\Group;

#[Group("docker")]
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
				'--package=woocommerce/test-e2e',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			$this->assertFileExists( $packageDir . '/qit-test.json' );
			$this->assertFileExists( $packageDir . '/package.json' );
			$this->assertFileExists( $packageDir . '/playwright.config.js' );
			$this->assertFileExists( $packageDir . '/tests/example.spec.js' );
			$this->assertDirectoryExists( $packageDir . '/bootstrap' );
			
			// Verify manifest content
			$manifest = json_decode( file_get_contents( $packageDir . '/qit-test.json' ), true );
			$this->assertEquals( 'woocommerce/test-e2e', $manifest['package'] );
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
			$this->assertStringContainsString( '[docker] ./bootstrap/global-setup.sh', $output );
			$this->assertStringContainsString( '[host] npx playwright test', $output );

			// The test should pass with tunnel enabled
			$this->assertEquals( 0, $exitCode, 'Test should succeed with tunnel enabled. Output: ' . $output );

			// Verify the test package was executed with proper phase structure
			$this->assertStringContainsString( 'GLOBAL SETUP', $output );
			$this->assertStringContainsString( '[docker] ./bootstrap/setup.sh', $output );
			// Default scaffolded package now runs 4 tests
			$this->assertStringContainsString( '4 passed', $output );
			
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
				'--package=woocommerce/woo-compat-test',
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
				'--package=woocommerce/test-cli',
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
			$this->assertStringContainsString( '[docker] ./bootstrap/global-setup.sh', $output );
			$this->assertStringContainsString( '[host] npx playwright test', $output );

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
// Add a visible element to the page
add_action( "wp_body_open", function() {
	echo "<div style=\"display:none\" id=\"test-plugin-marker\">Test plugin is active!</div>";
} );
' );
			
			// Scaffold a test package that tests for the plugin
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/plugin-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Modify the test to just check if the site loads with the plugin
			$testFile = $packageDir . '/tests/example.spec.js';
			file_put_contents( $testFile, "import { test, expect } from '@playwright/test';

test('site loads with custom plugin', async ({ page }) => {
  const response = await page.goto('/');
  expect(response?.status()).toBe(200);
  
  // Just verify the page has loaded properly
  await expect(page.locator('body')).toBeVisible();
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
				'--plugin', realpath( $pluginDir ),
				'--config', $configPath,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Verify the test ran
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			
			// The test should complete - we're testing the workflow, not the actual plugin functionality
			// The plugin might not be activated by default in the test environment
			$this->assertContains( $exitCode, [0, 1], 'Test should complete. Output: ' . $output );
			
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
		// Use qit_run_e2e which returns JSON immediately with QIT_SELF_TEST=run_e2e
		$output = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--plugin', 'hello-dolly',  // Use a simple WordPress.org plugin
			'--json',
		] );
		
		// In CI, we expect JSON output
		$this->assertJson( $output, 'CI should get JSON output' );
		
		// Parse the JSON to verify structure
		$result = json_decode( $output, true );
		$this->assertIsArray( $result );
		
		// Verify env_info structure
		$this->assertArrayHasKey( 'plugins', $result );
		$this->assertIsArray( $result['plugins'] );
		
		// Check that plugins are properly structured (not just arrays)
		foreach ( $result['plugins'] as $plugin ) {
			$this->assertIsArray( $plugin, 'Plugin should be an array in JSON' );
			$this->assertArrayHasKey( 'slug', $plugin );
			$this->assertArrayHasKey( 'type', $plugin );
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
				'--package=woocommerce/ecosystem-tests',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Run tests with both plugins
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--plugin', realpath( $plugin1Dir ),
				'--plugin', realpath( $plugin2Dir ),
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
				'--package=woocommerce/checkout-test',
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
	 * Test 9: Simple slug resolution
	 * 
	 * Scenario: Developer uses well-known WordPress.org plugins by slug
	 * - Tests basic slug resolution works with run:e2e
	 */
	public function test_simple_slug_resolution(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create a simple test package
			$packageDir = $tempDir . '/test-package';
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/slug-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Run with a well-known WordPress.org plugin slug
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--plugin', 'hello-dolly',  // WordPress.org plugin
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should complete successfully
			$this->assertContains( $exitCode, [0, 1], 'Test should complete. Output: ' . $output );
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			// Should download from WordPress.org
			$this->assertStringContainsString( 'Processing plugins and themes...', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 10: Local plugin with inferred slug
	 * 
	 * Scenario: Developer passes a local plugin directory
	 * - System should infer slug from directory name
	 */
	public function test_local_plugin_with_inferred_slug(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';
			
			// Test 1: Invalid slug inference (directory with dots in base name)
			// Version stripping removes patterns like -v2.3.4 or -1.2.3, so use dots in the base name
			$invalidPluginDir = $tempDir . '/my.awesome.plugin';
			mkdir( $invalidPluginDir, 0755, true );
			file_put_contents( $invalidPluginDir . '/my-plugin.php', '<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.2.3
 */
' );
			
			// Scaffold test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/local-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// This should fail with invalid slug error
			try {
				qit( [
					'run:e2e',
					'woocommerce',
					'--plugin', $invalidPluginDir,  // Invalid slug due to dots
					'--test-package', $packageDir,
				] );
				$this->fail( 'Expected exception for invalid slug' );
			} catch ( \RuntimeException $e ) {
				$this->assertStringContainsString( 'Inferred slug \'my.awesome.plugin\' is not valid', $e->getMessage() );
				$this->assertStringContainsString( 'Please use explicit format: slug@path', $e->getMessage() );
			}
			
			// Test 2: Valid slug inference (directory without dots)
			$validPluginDir = $tempDir . '/my-awesome-plugin';
			mkdir( $validPluginDir, 0755, true );
			file_put_contents( $validPluginDir . '/my-plugin.php', '<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.2.3
 */
' );
			
			// Run with valid local plugin path
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--plugin', $validPluginDir,  // Valid slug
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should complete with warning about inferred slug
			$this->assertEquals( 0, $exitCode, 'Test should succeed with valid slug. Output: ' . $output );
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			
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
				'--package=woocommerce/plugin-tests',
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
				'--plugin', realpath( '.' ),  // Current directory as plugin
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

	/**
	 * Test 11: Mixed plugin sources
	 * 
	 * Scenario: Developer uses plugins from different sources
	 * - WordPress.org slug
	 * - Local directory
	 * - ZIP file URL (when ZIP extraction is fixed)
	 */
	public function test_mixed_plugin_sources(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create test package
			$packageDir = $tempDir . '/test-package';
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/mixed-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Create a local plugin
			$localPlugin = $tempDir . '/my-local-plugin';
			mkdir( $localPlugin, 0755, true );
			file_put_contents( $localPlugin . '/plugin.php', '<?php /* Plugin Name: Local Plugin */' );
			
			// Run with mixed sources
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--plugin', 'hello-dolly',     // WordPress.org
				'--plugin', $localPlugin,       // Local directory
				'--test-package', $packageDir,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should handle both plugin types
			$this->assertContains( $exitCode, [0, 1], 'Test should complete. Output: ' . $output );
			$this->assertStringContainsString( 'Processing plugins and themes...', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 12: Config file integration
	 * 
	 * Scenario: Developer uses qit.json to configure plugins
	 * - Config defines plugins
	 * - CLI can override/extend
	 */
	public function test_config_file_integration(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create test package
			$packageDir = $tempDir . '/test-package';
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/config-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Create qit.json with test configuration
			$config = [
				'test_types' => [
					'e2e' => [
						'default' => [
							'test_packages' => [ $packageDir ],
							'environments' => [ 'test-env' ]
						]
					]
				],
				'environments' => [
					'test-env' => [
						'plugins' => [
							'hello-dolly',  // Only slugs allowed in config currently
							'query-monitor'
						]
					]
				]
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
			
			// Run with config
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--config', $configPath,
			], return_process: true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should use config settings
			$this->assertContains( $exitCode, [0, 1], 'Test should complete. Output: ' . $output );
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that run:e2e works without qit.json configuration.
	 * 
	 * This test ensures QIT commands can work without requiring a qit.json file
	 * when test packages are explicitly provided via --test-package.
	 * 
	 * This test would catch the bug where download_test_packages() threw an exception
	 * "test type 'e2e' not found in configuration" even when test packages
	 * were explicitly provided via the --test-package option.
	 */
	public function test_run_e2e_without_config_full(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create a local test package
			$packageDir = $tempDir . '/test-package';
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/no-config-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// No config at all - this should work because we provide test package explicitly
			// This would fail before the fix with:
			// "download_test_packages(): test type 'e2e' not found in configuration and no test packages specified"
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--test-package', $packageDir,
			], [], 0, [], true ); // return_process = true
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should complete successfully without requiring configuration
			$this->assertEquals( 0, $exitCode, 'Test should succeed without config file. Output: ' . $output );
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			// Default scaffolded package now runs 4 tests
			$this->assertStringContainsString( '4 passed', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that run:e2e works with minimal config (no test_types section).
	 * 
	 * This ensures the command can work when only environments are configured
	 * and test packages are provided via CLI.
	 */
	public function test_run_e2e_with_minimal_config_full(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create a local test package
			$packageDir = $tempDir . '/test-package';
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/minimal-config-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Create minimal config with only environments (no test_types)
			$config = [
				'environments' => [
					'default' => [
						'php' => '8.0',
						'wp' => '6.4',
					],
				],
				// No test_types section at all
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
			
			// This would fail before the fix with:
			// "download_test_packages(): test type 'e2e' not found in configuration and no test packages specified"
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--config', $configPath,
				'--test-package', $packageDir,
			], [], 0, [], true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should work with test package provided via CLI
			$this->assertEquals( 0, $exitCode, 'Test should succeed with minimal config. Output: ' . $output );
			$this->assertStringContainsString( 'Using local package: ' . $packageDir, $output );
			// The environment config should be respected even without test_types section
			$this->assertStringContainsString( 'Running Test Packages', $output );
			// Default scaffolded package now runs 4 tests
			$this->assertStringContainsString( '4 passed', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that run:e2e with multiple test packages works without config.
	 * 
	 * This specifically tests the edge case where multiple test packages are provided
	 * but no configuration exists.
	 */
	public function test_run_e2e_multiple_packages_no_config_full(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create two local test packages
			$package1Dir = $tempDir . '/test-package-1';
			qit( [
				'package:scaffold',
				$package1Dir,
				'--package=woocommerce/test1',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			$package2Dir = $tempDir . '/test-package-2';
			qit( [
				'package:scaffold',
				$package2Dir,
				'--package=woocommerce/test2',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// No config at all
			// This would fail before the fix with:
			// "download_test_packages(): test type 'e2e' not found in configuration and no test packages specified"
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--test-package', $package1Dir,
				'--test-package', $package2Dir,
			], [], 0, [], true );
			
			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();
			
			// Should handle multiple packages without config
			$this->assertEquals( 0, $exitCode, 'Test should succeed with multiple packages and no config. Output: ' . $output );
			$this->assertStringContainsString( 'Using local package: ' . $package1Dir, $output );
			$this->assertStringContainsString( 'Using local package: ' . $package2Dir, $output );
			$this->assertStringContainsString( 'Running Test Packages', $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 13: Error handling for invalid inputs
	 * 
	 * Scenario: Developer provides invalid plugin specifications
	 * - Should get clear error messages
	 */
	public function test_invalid_plugin_specifications(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create test package
			$packageDir = $tempDir . '/test-package';
			qit( [
				'package:scaffold',
				$packageDir,
				'--package=woocommerce/error-test',
				'--test-type=e2e',
				'--framework=playwright',
			] );
			
			// Test 1: Non-existent path
			try {
				qit( [
					'run:e2e',
					'woocommerce',
					'--plugin', '/tmp/does-not-exist-xyz123',
					'--test-package', $packageDir,
				] );
				$this->fail( 'Expected exception for non-existent path' );
			} catch ( \RuntimeException $e ) {
				$this->assertStringContainsString( 'Unrecognized plugin identifier', $e->getMessage() );
			}
			
			// Test 2: Invalid slug with special characters
			try {
				qit( [
					'run:e2e',
					'woocommerce',
					'--plugin', 'not-a-valid-slug!@#',
					'--test-package', $packageDir,
				] );
				$this->fail( 'Expected exception for invalid slug' );
			} catch ( \RuntimeException $e ) {
				$this->assertStringContainsString( 'Unrecognized plugin identifier', $e->getMessage() );
			}
			
			// Test 3: Non-existent WordPress.org plugin
			try {
				qit( [
					'run:e2e',
					'woocommerce',
					'--plugin', 'this-plugin-definitely-does-not-exist-xyz999',
					'--test-package', $packageDir,
				] );
				$this->fail( 'Expected exception for non-existent WPORG plugin' );
			} catch ( \RuntimeException $e ) {
				$this->assertStringContainsString( 'Not found in WPORG, WCCOM', $e->getMessage() );
			}
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

}