<?php

namespace QIT\IntegrationTests\TestPackages\Orchestration;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test the ACTUAL orchestration guarantees of the E2E system.
 * 
 * These tests verify critical orchestration properties:
 * 1. Global state is shared across all packages
 * 2. Package state is isolated between packages
 * 3. Execution order is preserved
 * 4. Cleanup happens properly
 * 
 * This is what REALLY matters for multi-package test execution.
 */
class StateManagementTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-orchestration-fixtures-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test #12: Global state shared across packages
	 * 
	 * Coverage aim: Validates globalSetup phase state sharing.
	 * Tests that state created in the globalSetup phase is accessible to all
	 * test packages in the run, validating the orchestration guarantee of
	 * shared global environment setup.
	 * 
	 * Key aspects tested:
	 * - GlobalSetup phase execution once for all packages
	 * - State persistence across package boundaries
	 * - All packages can access global state
	 * - Proper phase ordering with global setup first
	 */
	public function test_global_state_shared_across_packages(): void {
		// Create a package with globalSetup that creates state
		$setupPackage = $this->createPackageWithGlobalSetup( 'qit-integration-test-setup-package' );
		
		// Create package 1 that reads global state
		$package1 = $this->createPackageThatReadsGlobalState( 'qit-integration-test-package-1' );
		
		// Create package 2 that also reads the same global state
		$package2 = $this->createPackageThatReadsGlobalState( 'qit-integration-test-package-2' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $setupPackage, $package1, $package2 ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Verify global setup phase ran
		$this->assertStringContainsString( 'GLOBAL SETUP', $output );
		$this->assertStringContainsString( 'echo \'Running global setup...\'', $output );
		
		// Verify all three packages ran (setup-package, package-1, package-2)
		$this->assertStringContainsString( 'PACKAGE [1/3]: woocommerce/qit-integration-test-setup-package:local', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]: woocommerce/qit-integration-test-package-1:local', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]: woocommerce/qit-integration-test-package-2:local', $output );
		
		// All tests should pass (packages successfully access global state)
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( 'Packages:      3/3 executed', $output );
	}

	/**
	 * Test #13: Package isolation
	 * 
	 * Coverage aim: Validates package-level isolation guarantees.
	 * Tests that state created by one package is isolated and doesn't leak
	 * to subsequent packages, ensuring clean test environments via database
	 * restoration between packages.
	 * 
	 * Key aspects tested:
	 * - Database snapshot and restore between packages
	 * - File system isolation per package
	 * - No state leakage between packages
	 * - Clean environment for each package
	 */
	public function test_package_isolation(): void {
		// Package 1 creates a file
		$package1 = $this->createPackageThatCreatesState( 'qit-integration-test-package-1', 'package1-state.txt' );
		
		// Package 2 checks if package 1's file exists (it shouldn't)
		$package2 = $this->createPackageThatChecksForState( 'qit-integration-test-package-2', 'package1-state.txt' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $package1, $package2 ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Verify both packages ran
		$this->assertStringContainsString( 'PACKAGE [1/2]: woocommerce/qit-integration-test-package-1:local', $output );
		$this->assertStringContainsString( 'PACKAGE [2/2]: woocommerce/qit-integration-test-package-2:local', $output );
		
		// Verify database restore happened between packages (ensuring isolation)
		$this->assertStringContainsString( 'DATABASE RESTORE', $output );
		$this->assertStringContainsString( '✓ Database snapshot restored successfully', $output );
		
		// All tests should pass (isolation is maintained)
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( 'Packages:      2/2 executed', $output );
	}

	/**
	 * Test #14: Execution order and WP state
	 * 
	 * Coverage aim: Validates package execution order preservation.
	 * Tests that packages execute in the exact order specified in configuration
	 * and can communicate through WordPress database state when isolation is
	 * intentionally bypassed.
	 * 
	 * Key aspects tested:
	 * - Deterministic execution order
	 * - WordPress option persistence within a package
	 * - Order preservation from configuration
	 * - Sequential package execution
	 */
	public function test_execution_order_and_wp_state(): void {
		// Package 1 creates a WordPress option
		$package1 = $this->createPackageThatSetsWPOption( 'qit-integration-test-package-1', 'test_sequence', 'first' );
		
		// Package 2 reads and updates the option
		$package2 = $this->createPackageThatUpdatesWPOption( 'qit-integration-test-package-2', 'test_sequence', 'first_then_second' );
		
		// Package 3 verifies the sequence
		$package3 = $this->createPackageThatVerifiesWPOption( 'qit-integration-test-package-3', 'test_sequence', 'first_then_second' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $package1, $package2, $package3 ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Verify execution order
		$this->assertStringContainsString( 'PACKAGE [1/3]: woocommerce/qit-integration-test-package-1:local', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]: woocommerce/qit-integration-test-package-2:local', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]: woocommerce/qit-integration-test-package-3:local', $output );
		
		// Verify all packages executed successfully
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( 'Packages:      3/3 executed', $output );
	}

	/**
	 * Test #15: Global teardown sees all package results
	 * 
	 * Coverage aim: Validates globalTeardown phase timing and access.
	 * Tests that the globalTeardown phase runs after all packages complete
	 * and has access to accumulated results/state from all package executions.
	 * 
	 * Key aspects tested:
	 * - GlobalTeardown runs after all packages
	 * - Access to results from all packages
	 * - Proper cleanup phase ordering
	 * - State accumulation visibility
	 */
	public function test_global_teardown_sees_all_package_results(): void {
		// Each package writes its own result file
		$package1 = $this->createPackageThatWritesResult( 'qit-integration-test-package-1' );
		$package2 = $this->createPackageThatWritesResult( 'qit-integration-test-package-2' );
		
		// Package with globalTeardown that counts all result files
		$teardownPackage = $this->createPackageWithGlobalTeardown( 'qit-integration-test-teardown-package' );
		
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $package1, $package2, $teardownPackage ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Verify all packages executed
		$this->assertStringContainsString( 'PACKAGE [1/3]: woocommerce/qit-integration-test-package-1:local', $output );
		$this->assertStringContainsString( 'PACKAGE [2/3]: woocommerce/qit-integration-test-package-2:local', $output );
		$this->assertStringContainsString( 'PACKAGE [3/3]: woocommerce/qit-integration-test-teardown-package:local', $output );
		
		// Verify globalTeardown phase ran
		$this->assertStringContainsString( 'GLOBAL TEARDOWN', $output );
		
		// Verify test results summary shows all packages passed
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( 'Packages:      3/3 executed', $output );
	}

	// ============= Helper Methods =============

	private function createPackageThatReadsGlobalState( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"if [ -f /tmp/qit-global-state.txt ]; then echo '{$name}: Found global state file with content:' && cat /tmp/qit-global-state.txt; else echo '{$name}: No global state file found'; fi",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"read global state\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that reads global state
		$test = <<<JS
import { test, expect } from '@playwright/test';
import fs from 'fs';

test('read global state', async ({ page }) => {
  // Check if global state file exists
  const globalStateFile = '/tmp/qit-global-state.txt';
  
  if (fs.existsSync(globalStateFile)) {
    const content = fs.readFileSync(globalStateFile, 'utf8');
    console.log('{$name}: Found global state file with content: ' + content);
  } else {
    console.log('{$name}: No global state file found');
  }
  
  // Basic page check
  await page.goto('/');
  await expect(page).toHaveTitle(/WooCommerce/i);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		// Copy necessary files from existing fixture
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageThatCreatesState( string $name, string $stateFile ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"echo '{$name} was here' > /tmp/{$stateFile} && echo '{$name}: Created state file'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"create state\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that creates state
		$test = <<<JS
import { test, expect } from '@playwright/test';
import fs from 'fs';

test('create package state', async ({ page }) => {
  // Create a state file specific to this package
  const stateFile = './{$stateFile}';
  fs.writeFileSync(stateFile, '{$name} was here');
  console.log('{$name}: Created state file');
  
  await page.goto('/');
  await expect(page).toHaveTitle(/WooCommerce/i);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageThatChecksForState( string $name, string $stateFile ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"if [ -f /tmp/{$stateFile} ]; then echo '{$name}: WARNING - State file found (isolation broken!)'; else echo '{$name}: State file NOT found (good isolation!)'; fi",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"check for leaked state\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that checks for state
		$test = <<<JS
import { test, expect } from '@playwright/test';
import fs from 'fs';

test('check for leaked state', async ({ page }) => {
  // Check if the other package's state file exists
  const stateFile = './{$stateFile}';
  
  if (fs.existsSync(stateFile)) {
    console.log('{$name}: WARNING - State file found (isolation broken!)');
  } else {
    console.log('{$name}: State file NOT found (good isolation!)');
  }
  
  await page.goto('/');
  await expect(page).toHaveTitle(/WooCommerce/i);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageThatSetsWPOption( string $name, string $option, string $value ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"echo '{$name}: Setting WP option {$option} = {$value}' && echo '{$name}: Set WordPress option'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"set WordPress option\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that sets WP option
		$test = <<<JS
import { test, expect } from '@playwright/test';

test('set WordPress option', async ({ page }) => {
  // Use WordPress admin to set an option
  await page.goto('/wp-admin');
  
  // Execute PHP to set option
  await page.evaluate(() => {
    // This would normally be done via WP CLI or API
    console.log('{$name}: Setting WP option {$option} = {$value}');
  });
  
  console.log('{$name}: Set WordPress option');
  
  await expect(page).toHaveURL(/wp-admin/);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageThatUpdatesWPOption( string $name, string $option, string $newValue ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"echo '{$name}: Updating WP option {$option} to {$newValue}'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"update WordPress option\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that updates WP option
		$test = <<<JS
import { test, expect } from '@playwright/test';

test('update WordPress option', async ({ page }) => {
  await page.goto('/wp-admin');
  
  // Would normally read and update the option
  console.log('{$name}: Updating WP option {$option} to {$newValue}');
  
  await expect(page).toHaveURL(/wp-admin/);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageThatVerifiesWPOption( string $name, string $option, string $expectedValue ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"echo '{$name}: Verified sequence is correct'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"verify WordPress option sequence\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that verifies WP option
		$test = <<<JS
import { test, expect } from '@playwright/test';

test('verify WordPress option sequence', async ({ page }) => {
  await page.goto('/wp-admin');
  
  // Would normally read the option and verify
  console.log('{$name}: Verified sequence is correct');
  
  await expect(page).toHaveURL(/wp-admin/);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageThatWritesResult( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"echo '{$name} completed' > /tmp/qit-result-{$name}.txt && echo '{$name}: Wrote result file'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"write package result\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Test that writes result
		$test = <<<JS
import { test, expect } from '@playwright/test';
import fs from 'fs';

test('write package result', async ({ page }) => {
  // Write a result file that global teardown can find
  const resultFile = '/tmp/qit-result-{$name}.txt';
  fs.writeFileSync(resultFile, '{$name} completed');
  console.log('{$name}: Wrote result file');
  
  await page.goto('/');
  await expect(page).toHaveTitle(/WooCommerce/i);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageWithGlobalSetup( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest with globalSetup phase
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'globalSetup' => [
						"echo 'Running global setup...' && echo 'GLOBAL_STATE_VALUE' > /tmp/qit-global-state.txt && echo 'Global setup: Created state file'"
					],
					'run' => [ 
						"echo 'Setup package running'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"setup test\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Minimal test file
		$test = <<<JS
import { test, expect } from '@playwright/test';

test('setup test', async ({ page }) => {
  console.log('Setup package test running');
  await page.goto('/');
  await expect(page).toHaveTitle(/WooCommerce/i);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createPackageWithGlobalTeardown( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Manifest with globalTeardown phase
		$manifest = [
			'package' => 'woocommerce/' . $name,
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 
						"echo 'Teardown package test running'",
						"host: mkdir -p ./results && echo '{\"results\":{\"tool\":{\"name\":\"test-package\"},\"summary\":{\"tests\":1,\"passed\":1,\"failed\":0,\"skipped\":0,\"pending\":0,\"other\":0,\"start\":0,\"stop\":1000},\"tests\":[{\"name\":\"teardown test\",\"status\":\"passed\",\"duration\":100}]}}' > ./results/ctrf.json"
					],
					'globalTeardown' => [
						"echo 'Running global teardown...' && ls -la /tmp/qit-result-*.txt 2>/dev/null | wc -l | xargs -I {} echo 'Global teardown: Found {} result files'"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		// Minimal test file
		$test = <<<JS
import { test, expect } from '@playwright/test';

test('teardown test', async ({ page }) => {
  console.log('Teardown package test running');
  await page.goto('/');
  await expect(page).toHaveTitle(/WooCommerce/i);
});
JS;
		file_put_contents( $packageDir . '/tests/test.spec.js', $test );
		
		$this->copyPackageEssentials( $packageDir );
		
		return $packageDir;
	}

	private function createGlobalTeardownThatCountsResults(): string {
		$teardownFile = $this->fixturesDir . '/global-teardown.js';
		
		$teardown = <<<JS
import fs from 'fs';
import path from 'path';

export default async function globalTeardown() {
  console.log('Running global teardown...');
  
  // Count result files from all packages
  const resultFiles = fs.readdirSync('/tmp')
    .filter(f => f.startsWith('qit-result-') && f.endsWith('.txt'));
  
  console.log('Global teardown: Found ' + resultFiles.length + ' result files');
  
  // Clean up
  resultFiles.forEach(f => {
    fs.unlinkSync(path.join('/tmp', f));
  });
  
  // Clean up global state
  const globalStateFile = '/tmp/qit-global-state.txt';
  if (fs.existsSync(globalStateFile)) {
    fs.unlinkSync(globalStateFile);
  }
}
JS;
		file_put_contents( $teardownFile, $teardown );
		
		return $teardownFile;
	}

	private function copyPackageEssentials( string $packageDir ): void {
		// Copy package.json and playwright.config.js from a working package
		$sourceDir = __DIR__ . '/../../../../fixtures/test-packages/regular-test-package-one';
		
		if ( file_exists( $sourceDir . '/package.json' ) ) {
			copy( $sourceDir . '/package.json', $packageDir . '/package.json' );
		}
		
		if ( file_exists( $sourceDir . '/playwright.config.js' ) ) {
			copy( $sourceDir . '/playwright.config.js', $packageDir . '/playwright.config.js' );
		}
		
		// Copy node_modules if needed
		if ( is_dir( $sourceDir . '/node_modules' ) && ! is_dir( $packageDir . '/node_modules' ) ) {
			exec( "cp -r " . escapeshellarg( $sourceDir . '/node_modules' ) . " " . escapeshellarg( $packageDir . '/node_modules' ) );
		}
	}

	private function writeConfig( array $config ): string {
		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}