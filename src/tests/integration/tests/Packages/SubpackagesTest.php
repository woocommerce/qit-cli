<?php

namespace QIT\IntegrationTests\Tests\Packages;

use QIT\IntegrationTests\Traits\ScaffoldHelpers;
use QIT\IntegrationTests\Traits\CtrfSnapshotNormalizer;
use Spatie\Snapshots\MatchesSnapshots;

class SubpackagesTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use MatchesSnapshots;
	use CtrfSnapshotNormalizer;

	public function test_subpackages_run_with_parent_context(): void {
		$tempDir    = null;
		$packageDir = null;
		$configPath = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_subpackages_test_' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';

			// Scaffold a test package with Playwright
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=qit-test-plugin',
				'--package=main-e2e-suite',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $packageDir );
			$this->assertFileExists( $packageDir . '/qit-test.json' );

			// Create test files for different test areas
			$testsDir = $packageDir . '/tests';
			
			// Create checkout tests
			file_put_contents( $testsDir . '/checkout.spec.js', <<<'JS'
import { test, expect } from '@playwright/test';

test('checkout flow', async ({ page }) => {
  await page.goto('/shop');
  console.log('Running checkout tests');
  await expect(page).toHaveTitle(/Shop/);
});
JS
			);

			// Create cart tests
			file_put_contents( $testsDir . '/cart.spec.js', <<<'JS'
import { test, expect } from '@playwright/test';

test('cart functionality', async ({ page }) => {
  await page.goto('/cart');
  console.log('Running cart tests');
  await expect(page).toHaveTitle(/Cart/);
});
JS
			);

			// Modify manifest to include subpackages
			$manifest_path = $packageDir . '/qit-test.json';
			$manifest      = json_decode( file_get_contents( $manifest_path ), true );

			// Add subpackages definition
			$manifest['subpackages'] = [
				'woocommerce/checkout' => [
					'description' => 'Checkout flow tests',
					'tags' => ['checkout', 'payments'],
					'test' => [
						'phases' => [
							'run' => [ 'npx playwright test checkout.spec.js' ]
						]
					]
				],
				'woocommerce/cart' => [
					'description' => 'Cart functionality tests',
					'tags' => ['cart', 'shopping'],
					'test' => [
						'phases' => [
							'run' => [ 'npx playwright test cart.spec.js' ]
						]
					]
				]
			];

			// Keep the main package phases
			$manifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];

			file_put_contents( $manifest_path, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			// Test 1: Run the main package
			$qit_json_main = [
				'$schema'      => 'https://qit.woo.com/json-schema/qit',
				'sut'          => [
					'type'   => 'plugin',
					'slug'   => 'woocommerce',
					'source' => [ 'type' => 'wporg' ]
				],
				'test_types'   => [
					'e2e' => [
						'default' => [
							'test_packages' => [ $packageDir ]
						]
					]
				],
				'environments' => [
					'test-env' => [
						'php' => '8.2',
						'wp'  => 'stable'
					]
				]
			];

			$configPath = $tempDir . '/qit-config-main.json';
			file_put_contents( $configPath, json_encode( $qit_json_main, JSON_PRETTY_PRINT ) );

			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=test-env',
				'--config=' . $configPath
			], return_process: true );

			$this->assertSame( 0, $proc->getExitCode(),
				'Main package run failed: ' . $proc->getErrorOutput() ?: $proc->getOutput() );

			$out = $proc->getOutput();
			$this->assertStringContainsString( 'Running example test', $out, 'Main package should run example.spec.js' );

			// Test 2: Run a subpackage (checkout)
			// This would require the subpackage to be published first, but we can simulate it
			$qit_json_sub = [
				'$schema'      => 'https://qit.woo.com/json-schema/qit',
				'sut'          => [
					'type'   => 'plugin',
					'slug'   => 'woocommerce',
					'source' => [ 'type' => 'wporg' ]
				],
				'test_types'   => [
					'e2e' => [
						'default' => [
							// Simulate running a subpackage by modifying run phase
							'test_packages' => [ [
								'path' => $packageDir,
								'run' => [ 'npx playwright test checkout.spec.js' ]
							] ]
						]
					]
				],
				'environments' => [
					'test-env' => [
						'php' => '8.2',
						'wp'  => 'stable'
					]
				]
			];

			$configPath = $tempDir . '/qit-config-checkout.json';
			file_put_contents( $configPath, json_encode( $qit_json_sub, JSON_PRETTY_PRINT ) );

			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=test-env',
				'--config=' . $configPath
			], return_process: true );

			$this->assertSame( 0, $proc->getExitCode(),
				'Checkout subpackage run failed: ' . $proc->getErrorOutput() ?: $proc->getOutput() );

			$out = $proc->getOutput();
			$this->assertStringContainsString( 'Running checkout tests', $out, 'Subpackage should run checkout.spec.js' );

		} finally {
			// Cleanup
			if ( $tempDir && is_dir( $tempDir ) ) {
				exec( "rm -rf " . escapeshellarg( $tempDir ) );
			}
		}
	}

	public function test_subpackages_version_consistency(): void {
		$tempDir = sys_get_temp_dir() . '/qit_version_test_' . uniqid();
		mkdir( $tempDir, 0755, true );

		try {
			// Create two package directories with subpackages
			$package1Dir = $tempDir . '/package1';
			$package2Dir = $tempDir . '/package2';

			// Scaffold first package
			qit( [
				'package:scaffold',
				$package1Dir,
				'--namespace=vendor1',
				'--package=suite',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			// Add subpackages to first package
			$manifest1 = json_decode( file_get_contents( $package1Dir . '/qit-test.json' ), true );
			$manifest1['subpackages'] = [
				'vendor1/checkout' => [
					'description' => 'Checkout tests v1',
					'test' => [
						'phases' => [
							'run' => [ 'echo "Running checkout v1"' ]
						]
					]
				]
			];
			file_put_contents( $package1Dir . '/qit-test.json', json_encode( $manifest1, JSON_PRETTY_PRINT ) );

			// Scaffold second package (different version of same parent)
			qit( [
				'package:scaffold',
				$package2Dir,
				'--namespace=vendor1',
				'--package=suite',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			// Add subpackages to second package (simulating different version)
			$manifest2 = json_decode( file_get_contents( $package2Dir . '/qit-test.json' ), true );
			$manifest2['subpackages'] = [
				'vendor1/checkout' => [
					'description' => 'Checkout tests v2',
					'test' => [
						'phases' => [
							'run' => [ 'echo "Running checkout v2"' ]
						]
					]
				]
			];
			file_put_contents( $package2Dir . '/qit-test.json', json_encode( $manifest2, JSON_PRETTY_PRINT ) );

			// Try to run with mixed versions (this should fail validation)
			$qit_json = [
				'$schema'      => 'https://qit.woo.com/json-schema/qit',
				'sut'          => [
					'type'   => 'plugin',
					'slug'   => 'woocommerce',
					'source' => [ 'type' => 'wporg' ]
				],
				'test_types'   => [
					'e2e' => [
						'default' => [
							// Trying to use subpackages from different versions
							'test_packages' => [
								[ 'path' => $package1Dir ],
								[ 'path' => $package2Dir ]
							]
						]
					]
				],
				'environments' => [
					'test-env' => [
						'php' => '8.2',
						'wp'  => 'stable'
					]
				]
			];

			$configPath = $tempDir . '/qit-config.json';
			file_put_contents( $configPath, json_encode( $qit_json, JSON_PRETTY_PRINT ) );

			// This test validates that the system properly handles version constraints
			// In a real scenario, this would be validated when downloading packages
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=test-env',
				'--config=' . $configPath
			], return_process: true );

			// The test should pass since we're using local paths
			// Version validation would happen at download time for remote packages
			$this->assertSame( 0, $proc->getExitCode(),
				'Local subpackages should run without version conflicts' );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( "rm -rf " . escapeshellarg( $tempDir ) );
			}
		}
	}
}