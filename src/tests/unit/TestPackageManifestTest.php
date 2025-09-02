<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Critical test for TestPackageManifest value object.
 * Tests the anti-corruption layer that adapts JSON manifest formats.
 */
class TestPackageManifestTest extends TestCase {
	
	/**
	 * Test v2 package format (namespace/package in single field).
	 */
	public function test_v2_package_format(): void {
		$data = [
			'package' => 'woocommerce/checkout',
			'test' => [
				'phases' => [
					'globalSetup' => [],
					'setup' => [],
					'run' => ['echo "test"'],
					'teardown' => [],
					'globalTeardown' => []
				],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		
		$this->assertEquals( 'woocommerce/checkout', $manifest->get_package_id() );
		$this->assertEquals( 'woocommerce', $manifest->get_namespace() );
		$this->assertEquals( 'checkout', $manifest->get_package_name() );
	}
	
	/**
	 * Test v1 package format (separate namespace and package fields).
	 */
	public function test_v1_package_format(): void {
		$data = [
			'namespace' => 'woocommerce',
			'package' => 'checkout',
			'test' => [
				'phases' => [
					'globalSetup' => [],
					'setup' => [],
					'run' => ['phpunit'],
					'teardown' => [],
					'globalTeardown' => []
				],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		
		$this->assertEquals( 'woocommerce/checkout', $manifest->get_package_id() );
		$this->assertEquals( 'woocommerce', $manifest->get_namespace() );
		$this->assertEquals( 'checkout', $manifest->get_package_name() );
	}
	
	/**
	 * Test environment variable conversion to strings.
	 */
	public function test_env_var_stringification(): void {
		$data = [
			'package' => 'test/package',
			'test' => [
				'phases' => [
					'run' => ['test']
				],
				'results' => []
			],
			'envs' => [
				'DEBUG' => true,
				'VERBOSE' => false,
				'MAX_RETRIES' => 5,
				'TIMEOUT' => 30.5,
				'API_KEY' => 'secret123',
				'ZERO' => 0
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		$env_vars = $manifest->get_env();
		
		// All values should be strings
		$this->assertSame( 'true', $env_vars['DEBUG'] );
		$this->assertSame( 'false', $env_vars['VERBOSE'] );
		$this->assertSame( '5', $env_vars['MAX_RETRIES'] );
		$this->assertSame( '30.5', $env_vars['TIMEOUT'] );
		$this->assertSame( 'secret123', $env_vars['API_KEY'] );
		$this->assertSame( '0', $env_vars['ZERO'] );
	}
	
	/**
	 * Test container directory name generation.
	 */
	public function test_container_directory_name(): void {
		$data = [
			'namespace' => 'WooCommerce',
			'package' => 'My.Package_Name',
			'test' => [
				'phases' => [ 'run' => ['test'] ],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		
		// Should lowercase and preserve valid characters
		$this->assertEquals( 'woocommerce-my.package_name', $manifest->get_container_directory_name() );
		$this->assertEquals( 'woocommerce-my.package_name-1.0.0', $manifest->get_container_directory_name( '1.0.0' ) );
		$this->assertEquals( '/qit/packages/woocommerce-my.package_name', $manifest->get_container_path() );
	}
	
	/**
	 * Test subpackage detection.
	 */
	public function test_subpackage_detection(): void {
		// Parent package
		$parent_data = [
			'package' => 'woocommerce/checkout',
			'test' => [
				'phases' => [ 'run' => ['test'] ],
				'results' => []
			],
			'subpackages' => [
				'smoke' => [
					'name' => 'smoke',
					'test_type' => 'e2e'
				]
			]
		];
		
		$parent = new TestPackageManifest( $parent_data );
		$this->assertTrue( $parent->has_subpackages() );
		$this->assertFalse( $parent->is_subpackage() );
		$this->assertNotNull( $parent->get_subpackage( 'smoke' ) );
		
		// Subpackage
		$subpackage_data = [
			'package' => 'woocommerce/checkout',
			'parent_package' => 'woocommerce/checkout',
			'test' => [
				'phases' => [ 'run' => ['smoke test'] ],
				'results' => []
			]
		];
		
		$subpackage = new TestPackageManifest( $subpackage_data );
		$this->assertTrue( $subpackage->is_subpackage() );
		$this->assertEquals( 'woocommerce/checkout', $subpackage->get_parent_package() );
	}
	
	/**
	 * Test utility package detection (no run phase).
	 */
	public function test_utility_package(): void {
		$data = [
			'package' => 'woocommerce/setup-utils',
			'test' => [
				'phases' => [
					'globalSetup' => ['composer install'],
					'setup' => ['npm install'],
					'run' => [], // Empty run phase = utility package
					'teardown' => [],
					'globalTeardown' => []
				],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		$this->assertTrue( $manifest->is_utility_package() );
		$this->assertTrue( $manifest->has_global_setup() );
		$this->assertFalse( $manifest->has_global_teardown() );
	}
	
	/**
	 * Test static helper methods for container paths.
	 */
	public function test_static_container_helpers(): void {
		// With version
		$path = TestPackageManifest::create_container_directory_name( 'woocommerce/checkout:1.0.0' );
		$this->assertEquals( 'woocommerce-checkout-1.0.0', $path );
		
		// Without version
		$path = TestPackageManifest::create_container_directory_name( 'woocommerce/checkout' );
		$this->assertEquals( 'woocommerce-checkout', $path );
		
		// Full path
		$full_path = TestPackageManifest::create_container_path( 'woocommerce/checkout:2.0.0' );
		$this->assertEquals( '/qit/packages/woocommerce-checkout-2.0.0', $full_path );
	}
	
	/**
	 * Test missing required configuration.
	 */
	public function test_missing_test_configuration(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Manifest missing "test" configuration' );
		
		new TestPackageManifest( [
			'package' => 'woocommerce/checkout'
			// Missing 'test' key
		] );
	}
	
	/**
	 * Test normalized data flag (for caching).
	 */
	public function test_normalized_data_loading(): void {
		$normalized = [
			'_normalized' => true,
			'package_id' => 'woocommerce/checkout',
			'namespace' => 'woocommerce',
			'package_name' => 'checkout',
			'tags' => ['e2e', 'critical'],
			'test_type' => 'e2e',
			'test_dir' => './',
			'description' => 'Test package',
			'requires' => [],
			'phases' => [
				'globalSetup' => [],
				'setup' => [],
				'run' => ['test'],
				'teardown' => [],
				'globalTeardown' => []
			],
			'test_results' => [],
			'mu_plugins' => [],
			'env_vars' => [],
			'timeout' => 1800,
			'retry' => ['times' => 0, 'delay' => 0],
			'subpackages' => [],
			'parent_package' => null
		];
		
		$manifest = new TestPackageManifest( $normalized );
		
		// Should load directly without adaptation
		$this->assertEquals( 'woocommerce/checkout', $manifest->get_package_id() );
		$this->assertEquals( ['e2e', 'critical'], $manifest->get_tags() );
	}
}