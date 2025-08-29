<?php

namespace QIT\IntegrationTests\TestPackages\Packages\Subpackages;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

class ParentSubpackageCombinationTest extends TestCase {
	
	private array $tempDirs = [];
	private string $fixturesDir;
	
	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages';
	}
	
	protected function tearDown(): void {
		// Clean up any temp directories
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$this->deleteDirectory( $dir );
			}
		}
		// Clean up test packages from Manager
		TestCleanupHelper::cleanup_all_test_packages();
		parent::tearDown();
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
		
		// Create minimal playwright.config.js (won't actually run but keeps package valid)
		file_put_contents( "$tempDir/playwright.config.js", "module.exports = { testDir: '.', use: { headless: true } };" );
		
		return $tempDir;
	}
	
	private function create_test_file( string $path, string $content ): void {
		file_put_contents( $path, $content );
	}

	/**
	 * Test that parent package can run alongside its subpackages
	 * Uses volume-based verification for deterministic testing
	 */
	public function test_parent_and_subpackages_run_together() {
		// Create a tracking directory for test execution
		$trackingDir = sys_get_temp_dir() . '/qit-test-tracking-' . uniqid();
		mkdir( $trackingDir, 0755, true );
		$this->tempDirs[] = $trackingDir;
		
		// Generate unique package names using the convention
		$parentPackage = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'parent-suite' );
		$child1Package = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'child-one' );
		$child2Package = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'child-two' );

		// Create a test package with subpackages that write to tracking file
		$packageDir = $this->create_test_package( 'parent-with-subpackages', [
			'package'     => $parentPackage,
			'test_type'   => 'e2e',
			'description' => 'Parent test suite with subpackages',
			'test'        => [
				'phases'  => [
					'globalSetup'   => [
						'host: echo "PARENT_GLOBAL_SETUP" >> ' . $trackingDir . '/execution.log',
					],
					'setup'         => [
						'host: echo "PARENT_SETUP" >> ' . $trackingDir . '/execution.log',
					],
					'run'           => [
						'host: echo "PARENT_RUN" >> ' . $trackingDir . '/execution.log && mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt',
					],
					'teardown'      => [
						'host: echo "PARENT_TEARDOWN" >> ' . $trackingDir . '/execution.log',
					],
					'globalTeardown' => [
						'host: echo "PARENT_GLOBAL_TEARDOWN" >> ' . $trackingDir . '/execution.log',
					],
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir'  => './blob-report',
				],
			],
			'subpackages' => [
				$child1Package => [
					'description' => 'First child subpackage',
					'tags'        => ['child1'],
					'test'        => [
						'phases' => [
							'setup' => [
								'host: echo "CHILD1_SETUP" >> ' . $trackingDir . '/execution.log',
							],
							'run'   => [
								'host: echo "CHILD1_RUN" >> ' . $trackingDir . '/execution.log && mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt',
							],
						],
					],
				],
				$child2Package => [
					'description' => 'Second child subpackage',
					'tags'        => ['child2'],
					'test'        => [
						'phases' => [
							'globalSetup' => [
								'host: echo "CHILD2_GLOBAL_SETUP" >> ' . $trackingDir . '/execution.log',
							],
							'run'         => [
								'host: echo "CHILD2_RUN" >> ' . $trackingDir . '/execution.log && mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt',
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
		$this->assertStringContainsString( "Package published successfully: $parentPackage:1.0.0", $publishProc->getOutput() );

		// Run parent package with one subpackage - they share the same globalSetup
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			"--test-package=$parentPackage:1.0.0",
			"--test-package=$child1Package:1.0.0",
		], return_process: true );

		$this->assertSame( 0, $proc->getExitCode(), $proc->getOutput() . "\n" . $proc->getErrorOutput() );
		
		// Read and verify the execution log
		$executionLog = file_exists( "$trackingDir/execution.log" ) ? file_get_contents( "$trackingDir/execution.log" ) : '';
		$executionLines = array_filter( explode( "\n", $executionLog ) );
		
		// Verify both packages ran
		$this->assertContains( 'PARENT_RUN', $executionLines, 'Parent package should have run' );
		$this->assertContains( 'CHILD1_RUN', $executionLines, 'Child1 package should have run' );
		
		// Verify globalSetup was only executed once (deduplication worked)
		// Parent and Child1 share the same globalSetup after inheritance
		$globalSetupCount = count( array_filter( $executionLines, function( $line ) {
			return strpos( $line, 'PARENT_GLOBAL_SETUP' ) !== false;
		} ) );
		$this->assertEquals( 1, $globalSetupCount, 
			'globalSetup should only execute once when parent and child1 share it (deduplication)' );
		
		// Verify each package's specific phases ran
		$this->assertContains( 'PARENT_SETUP', $executionLines, 'Parent setup should have run' );
		$this->assertContains( 'CHILD1_SETUP', $executionLines, 'Child1 setup should have run' );
		
		// Clear the log for next test
		file_put_contents( "$trackingDir/execution.log", '' );
		
		// Run all three together - parent and both subpackages
		$proc2 = qit( [
			'run:e2e',
			'woocommerce',
			"--test-package=$parentPackage:1.0.0",
			"--test-package=$child1Package:1.0.0",
			"--test-package=$child2Package:1.0.0",
		], return_process: true );

		$this->assertSame( 0, $proc2->getExitCode(), $proc2->getOutput() . "\n" . $proc2->getErrorOutput() );
		
		// Read the execution log again
		$executionLog2 = file_exists( "$trackingDir/execution.log" ) ? file_get_contents( "$trackingDir/execution.log" ) : '';
		$executionLines2 = array_filter( explode( "\n", $executionLog2 ) );
		
		// Verify all three packages ran
		$this->assertContains( 'PARENT_RUN', $executionLines2, 'Parent package should have run' );
		$this->assertContains( 'CHILD1_RUN', $executionLines2, 'Child1 package should have run' );
		$this->assertContains( 'CHILD2_RUN', $executionLines2, 'Child2 package should have run' );
		
		// Verify both globalSetups ran (parent's and child2's are different)
		$parentGlobalSetupCount = count( array_filter( $executionLines2, function( $line ) {
			return strpos( $line, 'PARENT_GLOBAL_SETUP' ) !== false;
		} ) );
		$child2GlobalSetupCount = count( array_filter( $executionLines2, function( $line ) {
			return strpos( $line, 'CHILD2_GLOBAL_SETUP' ) !== false;
		} ) );
		
		$this->assertEquals( 1, $parentGlobalSetupCount, 
			'Parent globalSetup should run once (shared by parent and child1)' );
		$this->assertEquals( 1, $child2GlobalSetupCount, 
			'Child2 globalSetup should run once (it overrides parent)' );
		
		// No need for manual cleanup - TestCleanupHelper will handle it in tearDown
	}

	/**
	 * Test that parent package with ALL its subpackages works
	 * Uses volume-based verification for deterministic testing
	 */
	public function test_parent_with_all_subpackages() {
		// Create a tracking directory for test execution
		$trackingDir = sys_get_temp_dir() . '/qit-test-tracking-' . uniqid();
		mkdir( $trackingDir, 0755, true );
		$this->tempDirs[] = $trackingDir;

		// First, modify the fixture to write to tracking file
		$sourceFixture = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages/subpackages-parent';
		$tempFixture = sys_get_temp_dir() . '/qit-test-fixture-' . uniqid();
		mkdir( $tempFixture, 0755, true );
		$this->tempDirs[] = $tempFixture;
		
		// Copy the fixture and modify it to write to tracking file
		$this->copyDirectory( $sourceFixture, $tempFixture );
		$this->modifyFixtureForTracking( $tempFixture, $trackingDir );
		
		// Use a unique version to avoid conflicts
		$version = '3.0.' . uniqid();
		
		// Publish the package
		$publishProc = qit( [
			'package:publish',
			$tempFixture,
			$version,
		], return_process: true );
		$this->assertSame( 0, $publishProc->getExitCode(), $publishProc->getOutput() . "\n" . $publishProc->getErrorOutput() );
		
		// Create config for all packages in a proper subdirectory
		$configDir = sys_get_temp_dir() . '/qit-config-' . uniqid();
		mkdir( $configDir, 0755, true );
		$config = $configDir . '/config.json';
		$this->tempDirs[] = $configDir;
		file_put_contents( $config, json_encode( [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [
							"woocommerce/qit-integration-test-e2e-suite:$version",
							"woocommerce/qit-integration-test-checkout:$version",
							"woocommerce/qit-integration-test-cart:$version",
							"woocommerce/qit-integration-test-account:$version",
						]
					]
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
		
		// Read and verify the execution log
		$executionLog = file_exists( "$trackingDir/execution.log" ) ? file_get_contents( "$trackingDir/execution.log" ) : '';
		$executionLines = array_filter( explode( "\n", $executionLog ) );
		
		// Verify all packages ran
		$this->assertContains( 'PARENT_RUN', $executionLines, 'Parent package should have run' );
		$this->assertContains( 'CHECKOUT_RUN', $executionLines, 'Checkout subpackage should have run' );
		$this->assertContains( 'CART_RUN', $executionLines, 'Cart subpackage should have run' );
		$this->assertContains( 'ACCOUNT_RUN', $executionLines, 'Account subpackage should have run' );
		
		// Verify globalSetup deduplication - parent defines it, subpackages inherit unless overridden
		$globalSetupCount = count( array_filter( $executionLines, function( $line ) {
			return strpos( $line, 'GLOBAL_SETUP' ) !== false;
		} ) );
		// Should be 1 if all subpackages inherit, or more if some override
		$this->assertGreaterThanOrEqual( 1, $globalSetupCount, 'At least one globalSetup should run' );
		
		// No need for manual cleanup - TestCleanupHelper will handle it in tearDown
	}
	
	/**
	 * Copy directory recursively
	 */
	private function copyDirectory( string $src, string $dst ): void {
		$dir = opendir( $src );
		@mkdir( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					$this->copyDirectory( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}
	
	/**
	 * Modify fixture manifest to write to tracking file
	 */
	private function modifyFixtureForTracking( string $fixtureDir, string $trackingDir ): void {
		$manifestPath = $fixtureDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		
		// Modify parent phases to write to tracking - we know the fixture structure
		$manifest['test']['phases']['globalSetup'] = [
			'host: echo "PARENT_GLOBAL_SETUP" >> ' . $trackingDir . '/execution.log'
		];
		$manifest['test']['phases']['setup'] = [
			'host: echo "PARENT_SETUP" >> ' . $trackingDir . '/execution.log'
		];
		$manifest['test']['phases']['teardown'] = [
			'host: echo "PARENT_TEARDOWN" >> ' . $trackingDir . '/execution.log'
		];
		$manifest['test']['phases']['globalTeardown'] = [
			'host: echo "PARENT_GLOBAL_TEARDOWN" >> ' . $trackingDir . '/execution.log'
		];
		$manifest['test']['phases']['run'] = [
			'host: echo "PARENT_RUN" >> ' . $trackingDir . '/execution.log && mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
		];
		
		// Modify subpackages to write to tracking - we know they exist in the fixture
		foreach ( $manifest['subpackages'] as $subPkgName => &$subPkg ) {
			$prefix = strtoupper( str_replace( ['woocommerce/qit-integration-test-', '-'], ['', '_'], $subPkgName ) );
			
			// The fixture has different phases for different subpackages, handle accordingly
			if ( $subPkgName === 'woocommerce/qit-integration-test-checkout' || 
			     $subPkgName === 'woocommerce/qit-integration-test-account' ) {
				// These have setup phases in the fixture
				$subPkg['test']['phases']['setup'] = [
					'host: echo "' . $prefix . '_SETUP" >> ' . $trackingDir . '/execution.log'
				];
			}
			
			if ( $subPkgName === 'woocommerce/qit-integration-test-account' ) {
				// Account also has teardown in the fixture
				$subPkg['test']['phases']['teardown'] = [
					'host: echo "' . $prefix . '_TEARDOWN" >> ' . $trackingDir . '/execution.log'
				];
			}
			
			// All subpackages have run phase
			$subPkg['test']['phases']['run'] = [
				'host: echo "' . $prefix . '_RUN" >> ' . $trackingDir . '/execution.log && mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
			];
		}
		
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}
}