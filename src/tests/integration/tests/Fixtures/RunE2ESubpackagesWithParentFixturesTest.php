<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

class RunE2ESubpackagesWithParentFixturesTest extends TestCase {
	
	private array $tempDirs = [];
	private string $fixturesDir;
	
	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		// Clean up any temp directories
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$this->deleteDirectory( $dir );
			}
		}
	}
	
	private function deleteDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$objects = scandir( $dir );
		foreach ( $objects as $object ) {
			if ( $object !== '.' && $object !== '..' ) {
				if ( is_dir( $dir . '/' . $object ) ) {
					$this->deleteDirectory( $dir . '/' . $object );
				} else {
					unlink( $dir . '/' . $object );
				}
			}
		}
		rmdir( $dir );
	}
	
	private function create_test_package( string $name, array $manifest ): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . $name . '-' . uniqid();
		mkdir( $tempDir );
		$this->tempDirs[] = $tempDir;
		
		// Write manifest
		file_put_contents( "$tempDir/qit-test.json", json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Create playwright.config.js
		file_put_contents( "$tempDir/playwright.config.js", "module.exports = { testDir: '.', use: { headless: true } };" );
		
		return $tempDir;
	}
	
	private function create_test_file( string $path, string $content ): void {
		file_put_contents( $path, $content );
	}

	/**
	 * Test that parent package can run alongside its subpackages
	 */
	public function test_parent_and_subpackages_run_together() {

		// Create a test package with subpackages
		$packageDir = $this->create_test_package( 'parent-with-subpackages', [
			'package'     => 'test-vendor/parent-suite',
			'test_type'   => 'e2e',
			'description' => 'Parent test suite with subpackages',
			'test'        => [
				'phases'  => [
					'globalSetup'   => [
						'echo "[PARENT_GLOBAL_SETUP] Setting up parent environment"',
					],
					'setup'         => [
						'echo "[PARENT_SETUP] Parent-specific setup"',
					],
					'run'           => [
						'npx playwright test parent-tests.spec.js',
					],
					'teardown'      => [
						'echo "[PARENT_TEARDOWN] Parent-specific teardown"',
					],
					'globalTeardown' => [
						'echo "[PARENT_GLOBAL_TEARDOWN] Cleaning up parent environment"',
					],
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir'  => './blob-report',
				],
			],
			'subpackages' => [
				'test-vendor/child-one' => [
					'description' => 'First child subpackage',
					'tags'        => ['child1'],
					'test'        => [
						'phases' => [
							'setup' => [
								'echo "[CHILD1_SETUP] Child 1 specific setup"',
							],
							'run'   => [
								'npx playwright test child1-tests.spec.js',
							],
						],
					],
				],
				'test-vendor/child-two' => [
					'description' => 'Second child subpackage',
					'tags'        => ['child2'],
					'test'        => [
						'phases' => [
							'globalSetup' => [
								'echo "[CHILD2_GLOBAL_SETUP] Child 2 overrides global setup"',
							],
							'run'         => [
								'npx playwright test child2-tests.spec.js',
							],
						],
					],
				],
			],
		] );

		// Add test files
		$this->create_test_file( "$packageDir/parent-tests.spec.js", "test('parent test', async () => { console.log('Parent test executed'); });" );
		$this->create_test_file( "$packageDir/child1-tests.spec.js", "test('child1 test', async () => { console.log('Child 1 test executed'); });" );
		$this->create_test_file( "$packageDir/child2-tests.spec.js", "test('child2 test', async () => { console.log('Child 2 test executed'); });" );

		// Publish the package
		$publishProc = qit( [
			'package:publish',
			$packageDir,
			'1.0.0',
		], return_process: true );
		$this->assertSame( 0, $publishProc->getExitCode(), $publishProc->getOutput() . "\n" . $publishProc->getErrorOutput() );
		$this->assertStringContainsString( 'Package published successfully: test-vendor/parent-suite:1.0.0', $publishProc->getOutput() );

		// Create config for the test run
		$config1 = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config1, json_encode( [
			'test_packages' => [
				'test-vendor/parent-suite:1.0.0',
				'test-vendor/child-one:1.0.0',
			],
		] ) );
		
		// Run parent package with one subpackage - should work
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config1,
		], return_process: true );

		$this->assertSame( 0, $proc->getExitCode(), $proc->getOutput() . "\n" . $proc->getErrorOutput() );
		
		// Verify both packages ran
		$output = $proc->getOutput();
		$this->assertStringContainsString( 'test-vendor/parent-suite:1.0.0', $output );
		$this->assertStringContainsString( 'test-vendor/child-one:1.0.0', $output );
		
		// Verify globalSetup deduplication worked
		$this->assertStringContainsString( '[PARENT_GLOBAL_SETUP] Setting up parent environment', $output );
		$this->assertStringNotContainsString( 'Skipping duplicate command', $output ); // Child 1 inherits parent's globalSetup
		
		// Verify each package's specific phases ran
		$this->assertStringContainsString( '[PARENT_SETUP] Parent-specific setup', $output );
		$this->assertStringContainsString( '[CHILD1_SETUP] Child 1 specific setup', $output );
		
		// Create config for all three packages
		$config2 = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config2, json_encode( [
			'test_packages' => [
				'test-vendor/parent-suite:1.0.0',
				'test-vendor/child-one:1.0.0',
				'test-vendor/child-two:1.0.0',
			],
		] ) );
		
		// Run all three together - parent and both subpackages
		$proc2 = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config2,
		], return_process: true );

		$this->assertSame( 0, $proc2->getExitCode(), $proc2->getOutput() . "\n" . $proc2->getErrorOutput() );
		
		// Verify all three packages ran
		$output2 = $proc2->getOutput();
		$this->assertStringContainsString( 'test-vendor/parent-suite:1.0.0', $output2 );
		$this->assertStringContainsString( 'test-vendor/child-one:1.0.0', $output2 );
		$this->assertStringContainsString( 'test-vendor/child-two:1.0.0', $output2 );
		
		// Verify globalSetup override and deduplication
		$this->assertStringContainsString( '[PARENT_GLOBAL_SETUP] Setting up parent environment', $output2 );
		$this->assertStringContainsString( '[CHILD2_GLOBAL_SETUP] Child 2 overrides global setup', $output2 );
		
		// Child 2's globalSetup should be different, so both should run
		// But if parent and child1 have the same globalSetup, it should deduplicate
		
		// Clean up
		qit( [
			'package:delete',
			'test-vendor/parent-suite:1.0.0',
			'--yes',
		] );
		qit( [
			'package:delete',
			'test-vendor/child-one:1.0.0',
			'--yes',
		] );
		qit( [
			'package:delete',
			'test-vendor/child-two:1.0.0',
			'--yes',
		] );
	}

	/**
	 * Test that parent package with ALL its subpackages works
	 */
	public function test_parent_with_all_subpackages() {

		// Use the existing fixture
		$packageDir = __DIR__ . '/../../fixtures/test-packages/subpackages-parent';
		
		// Use a unique version to avoid conflicts
		$version = '3.0.' . uniqid();
		
		// Publish the package
		$publishProc = qit( [
			'package:publish',
			$packageDir,
			$version,
		], return_process: true );
		$this->assertSame( 0, $publishProc->getExitCode(), $publishProc->getOutput() . "\n" . $publishProc->getErrorOutput() );
		
		// Create config for all packages
		$config = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config, json_encode( [
			'$schema' => 'https://qit.woo.com/json-schema/qit',
			'sut' => [
				'type' => 'plugin',
				'slug' => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [
							"woocommerce/e2e-suite:$version",
							"woocommerce/checkout:$version",
							"woocommerce/cart:$version",
							"woocommerce/account:$version",
						]
					]
				]
			],
			'environments' => [
				'default' => [
					'wordpress_version' => 'latest',
					'php_version' => '7.4'
				]
			]
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Run parent with all its subpackages
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );
		
		$this->assertSame( 0, $proc->getExitCode(), $proc->getOutput() . "\n" . $proc->getErrorOutput() );
		
		// Verify all packages ran
		$output = $proc->getOutput();
		$this->assertStringContainsString( "woocommerce/e2e-suite:$version", $output );
		$this->assertStringContainsString( "woocommerce/checkout:$version", $output );
		$this->assertStringContainsString( "woocommerce/cart:$version", $output );
		$this->assertStringContainsString( "woocommerce/account:$version", $output );
		
		// Clean up
		qit( [
			'package:delete',
			"woocommerce/e2e-suite:$version",
			'--yes',
		] );
		qit( [
			'package:delete',
			"woocommerce/checkout:$version",
			'--yes',
		] );
		qit( [
			'package:delete',
			"woocommerce/cart:$version",
			'--yes',
		] );
		qit( [
			'package:delete',
			"woocommerce/account:$version",
			'--yes',
		] );
	}
}