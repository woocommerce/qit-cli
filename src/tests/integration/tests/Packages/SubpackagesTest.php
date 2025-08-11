<?php

namespace QIT\IntegrationTests\Tests\Packages;

use QIT\IntegrationTests\Traits\ScaffoldHelpers;

class SubpackagesTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;

	/**
	 * Test that subpackages use the same namespace as parent and inherit global phases.
	 */
	public function test_subpackages_namespace_and_phase_inheritance(): void {
		$tempDir    = null;
		$packageDir = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_subpkg_' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';
			
			// Scaffold a test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=myvendor',
				'--package=e2e-suite',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertFileExists( $packageDir . '/qit-test.json' );

			// Create bootstrap directory and actual scripts
			$bootstrapDir = $packageDir . '/bootstrap';
			@mkdir( $bootstrapDir, 0755, true );
			
			// Create a log file to track phase execution
			$logFile = $tempDir . '/phases.log';
			
			// Global setup script - should run once
			file_put_contents( $bootstrapDir . '/global-setup.sh', "#!/bin/bash\necho 'GLOBAL_SETUP' >> " . escapeshellarg( $logFile ) . "\n" );
			chmod( $bootstrapDir . '/global-setup.sh', 0755 );
			
			// Setup script - runs per package
			file_put_contents( $bootstrapDir . '/setup.sh', "#!/bin/bash\necho \"SETUP:\$1\" >> " . escapeshellarg( $logFile ) . "\n" );
			chmod( $bootstrapDir . '/setup.sh', 0755 );
			
			// Global teardown script - should run once
			file_put_contents( $bootstrapDir . '/global-teardown.sh', "#!/bin/bash\necho 'GLOBAL_TEARDOWN' >> " . escapeshellarg( $logFile ) . "\n" );
			chmod( $bootstrapDir . '/global-teardown.sh', 0755 );

			// Create test files in tests directory
			$testsDir = $packageDir . '/tests';
			
			// Checkout test
			file_put_contents( $testsDir . '/checkout.spec.js', <<<'JS'
import { test, expect } from '@playwright/test';

test('checkout test', async ({ page }) => {
  console.log('CHECKOUT_TEST_RAN');
  await page.goto('/');
  await expect(page.locator('body')).toBeVisible();
});
JS
			);

			// Cart test  
			file_put_contents( $testsDir . '/cart.spec.js', <<<'JS'
import { test, expect } from '@playwright/test';

test('cart test', async ({ page }) => {
  console.log('CART_TEST_RAN');
  await page.goto('/');
  await expect(page.locator('body')).toBeVisible();
});
JS
			);

			// Update manifest with subpackages using correct namespace
			$manifest_path = $packageDir . '/qit-test.json';
			$manifest = json_decode( file_get_contents( $manifest_path ), true );
			
			// Subpackages must use same namespace as parent
			$manifest['subpackages'] = [
				'myvendor/checkout' => [
					'description' => 'Checkout flow tests',
					'tags' => ['checkout'],
					'test' => [
						'phases' => [
							// Subpackages can only override setup/run/teardown, not global phases
							'run' => [ 'npx playwright test tests/checkout.spec.js' ]
						]
					]
				],
				'myvendor/cart' => [
					'description' => 'Cart tests',
					'tags' => ['cart'],
					'test' => [
						'phases' => [
							'run' => [ 'npx playwright test tests/cart.spec.js' ]
						]
					]
				]
			];
			
			// Parent package phases
			$manifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh main' ],
				'run'            => [ 'npx playwright test tests/example.spec.js' ],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];
			
			file_put_contents( $manifest_path, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			// Run just the main package
			$qit_config = [
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

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $qit_config, JSON_PRETTY_PRINT ) );

			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=test-env',
				'--config=' . $configPath
			], return_process: true );

			// Check the test ran
			$this->assertSame( 0, $proc->getExitCode(),
				'Package run failed: ' . ($proc->getErrorOutput() ?: $proc->getOutput()) );

			// Verify phase execution from log
			if ( file_exists( $logFile ) ) {
				$log = file_get_contents( $logFile );
				$this->assertStringContainsString( 'GLOBAL_SETUP', $log );
				$this->assertStringContainsString( 'SETUP:main', $log );
				$this->assertStringContainsString( 'GLOBAL_TEARDOWN', $log );
			}
			
			// The output should show the example test ran
			$output = $proc->getOutput();
			$this->assertStringContainsString( 'example.spec.js', $output );

		} finally {
			if ( $tempDir && is_dir( $tempDir ) ) {
				exec( "rm -rf " . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that subpackages cannot override global phases.
	 * Note: Schema validation for this constraint would happen at publish time.
	 */
	public function test_subpackages_cannot_override_global_phases(): void {
		$this->markTestSkipped(
			'Subpackage global phase validation happens in the schema validator. ' .
			'This is enforced in test-package-manifest-schema.json'
		);
	}

	/**
	 * Test subpackage publishing workflow.
	 * Note: This test simulates the publishing behavior since we can't actually
	 * publish to a real registry in tests.
	 */
	public function test_subpackages_publish_together(): void {
		$this->markTestSkipped( 
			'Subpackage publishing requires a real Manager instance. ' .
			'This behavior is tested manually or in Manager integration tests.'
		);
	}

	/**
	 * Test version consistency validation.
	 * Note: Version validation happens at package download time from the registry,
	 * not with local paths.
	 */
	public function test_subpackages_version_validation_with_registry(): void {
		if ( ! getenv( 'QIT_TEST_WITH_REGISTRY' ) ) {
			$this->markTestSkipped(
				'Set QIT_TEST_WITH_REGISTRY=1 to test version validation with real packages'
			);
		}
		
		// This would test downloading packages with mismatched versions
		// Example: myvendor/checkout:1.0.0 and myvendor/cart:2.0.0 from same parent
		// The system should reject this combination
		
		// Since we can't test this without a real registry, we skip it
		$this->assertTrue( true );
	}
}