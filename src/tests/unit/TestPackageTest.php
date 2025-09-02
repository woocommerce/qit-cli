<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Objects\TestPackage;

/**
 * Test for TestPackage value object - represents test package references.
 */
class TestPackageTest extends TestCase {
	
	/**
	 * Test basic constructor.
	 */
	public function test_constructor(): void {
		$package = new TestPackage( 'woocommerce/checkout', 'stable' );
		
		$this->assertEquals( 'woocommerce/checkout', $package->slug );
		$this->assertEquals( 'stable', $package->version );
	}
	
	/**
	 * Test fromString factory with explicit version.
	 */
	public function test_from_string_with_version(): void {
		$package = TestPackage::fromString( 'woocommerce/e2e:v1.2.3' );
		
		$this->assertEquals( 'woocommerce/e2e', $package->slug );
		$this->assertEquals( 'v1.2.3', $package->version );
	}
	
	/**
	 * Test fromString factory without version returns empty string.
	 */
	public function test_from_string_without_version(): void {
		$package = TestPackage::fromString( 'woocommerce/checkout:' );
		
		$this->assertEquals( 'woocommerce/checkout', $package->slug );
		$this->assertEquals( '', $package->version );
	}
	
	/**
	 * Test fromString with various version formats.
	 */
	public function test_from_string_version_formats(): void {
		// Semantic version
		$package = TestPackage::fromString( 'my/package:1.0.0' );
		$this->assertEquals( '1.0.0', $package->version );
		
		// Version with v prefix
		$package = TestPackage::fromString( 'my/package:v2.3.4' );
		$this->assertEquals( 'v2.3.4', $package->version );
		
		// Channel versions
		$package = TestPackage::fromString( 'my/package:stable' );
		$this->assertEquals( 'stable', $package->version );
		
		$package = TestPackage::fromString( 'my/package:latest' );
		$this->assertEquals( 'latest', $package->version );
		
		$package = TestPackage::fromString( 'my/package:rc' );
		$this->assertEquals( 'rc', $package->version );
		
		$package = TestPackage::fromString( 'my/package:nightly' );
		$this->assertEquals( 'nightly', $package->version );
	}
	
	/**
	 * Test fromString with complex slugs.
	 */
	public function test_from_string_complex_slugs(): void {
		// Namespace with hyphens
		$package = TestPackage::fromString( 'woo-commerce/my-test-package:1.0' );
		$this->assertEquals( 'woo-commerce/my-test-package', $package->slug );
		$this->assertEquals( '1.0', $package->version );
		
		// Namespace with underscores
		$package = TestPackage::fromString( 'test_namespace/test_package:beta' );
		$this->assertEquals( 'test_namespace/test_package', $package->slug );
		$this->assertEquals( 'beta', $package->version );
	}
	
	/**
	 * Test fromString with subpackages.
	 */
	public function test_from_string_with_subpackage(): void {
		// Subpackage format
		$package = TestPackage::fromString( 'woocommerce/checkout/smoke:1.0.0' );
		$this->assertEquals( 'woocommerce/checkout/smoke', $package->slug );
		$this->assertEquals( '1.0.0', $package->version );
		
		// Subpackage without version
		$package = TestPackage::fromString( 'woocommerce/checkout/regression:' );
		$this->assertEquals( 'woocommerce/checkout/regression', $package->slug );
		$this->assertEquals( '', $package->version );
	}
	
	/**
	 * Test jsonSerialize for JSON encoding.
	 */
	public function test_json_serialize(): void {
		$package = new TestPackage( 'woocommerce/api', 'v3.0.0' );
		
		$json = json_encode( $package );
		$decoded = json_decode( $json, true );
		
		$this->assertEquals( [
			'slug' => 'woocommerce/api',
			'version' => 'v3.0.0'
		], $decoded );
	}
	
	/**
	 * Test that colons in version are handled correctly.
	 */
	public function test_from_string_with_colon_in_version(): void {
		// Edge case: what if version itself contains a colon?
		// The explode with limit 2 should handle this
		$package = TestPackage::fromString( 'my/package:v1:special' );
		$this->assertEquals( 'my/package', $package->slug );
		$this->assertEquals( 'v1:special', $package->version );
	}
	
	/**
	 * Test immutability - properties are public but should not be changed.
	 */
	public function test_immutability_convention(): void {
		$package = new TestPackage( 'original/slug', 'v1.0.0' );
		
		// Properties are public for simplicity but convention is to treat as immutable
		$original_slug = $package->slug;
		$original_version = $package->version;
		
		// Create new instance rather than mutating
		$new_package = new TestPackage( 'new/slug', 'v2.0.0' );
		
		// Original should remain unchanged (by convention)
		$this->assertEquals( 'original/slug', $original_slug );
		$this->assertEquals( 'v1.0.0', $original_version );
		$this->assertEquals( 'new/slug', $new_package->slug );
		$this->assertEquals( 'v2.0.0', $new_package->version );
	}
}