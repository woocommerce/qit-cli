<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Utils\PackageReferenceUtils;

/**
 * Test for PackageReferenceUtils - handles package reference parsing and validation.
 */
class PackageReferenceUtilsTest extends TestCase {

	/**
	 * Test extracting package ID from various references.
	 */
	public function test_extract_package_id(): void {
		// Remote packages with version
		$this->assertEquals( 'woocommerce/e2e', PackageReferenceUtils::extract_package_id( 'woocommerce/e2e:latest' ) );
		$this->assertEquals( 'woocommerce/e2e', PackageReferenceUtils::extract_package_id( 'woocommerce/e2e:1.0.0' ) );

		// Remote packages without version
		$this->assertEquals( 'woocommerce/e2e', PackageReferenceUtils::extract_package_id( 'woocommerce/e2e' ) );

		// Subpackages
		$this->assertEquals( 'woocommerce/e2e/checkout', PackageReferenceUtils::extract_package_id( 'woocommerce/e2e/checkout:1.0.0' ) );
	}

	/**
	 * Test checking if reference is local.
	 */
	public function test_is_local_reference(): void {
		// Actual directories that exist in test environment
		$this->assertTrue( PackageReferenceUtils::is_local_reference( __DIR__ ) );  // Current test directory
		$this->assertTrue( PackageReferenceUtils::is_local_reference( '.' ) );

		// Remote references (not existing directories)
		$this->assertFalse( PackageReferenceUtils::is_local_reference( 'woocommerce/e2e:latest' ) );
		$this->assertFalse( PackageReferenceUtils::is_local_reference( 'woocommerce/e2e' ) );
		$this->assertFalse( PackageReferenceUtils::is_local_reference( 'non-existent/path' ) );
	}

	/**
	 * Test checking if reference is remote.
	 */
	public function test_is_remote_reference(): void {
		// Remote references (not existing directories)
		$this->assertTrue( PackageReferenceUtils::is_remote_reference( 'woocommerce/e2e:latest' ) );
		$this->assertTrue( PackageReferenceUtils::is_remote_reference( 'woocommerce/e2e' ) );
		$this->assertTrue( PackageReferenceUtils::is_remote_reference( 'non-existent/path' ) );

		// Local directories that exist
		$this->assertFalse( PackageReferenceUtils::is_remote_reference( __DIR__ ) );  // Current test directory
		$this->assertFalse( PackageReferenceUtils::is_remote_reference( '.' ) );
	}

	/**
	 * Test extracting version from references - NO FALLBACK to 'latest'.
	 */
	public function test_extract_version(): void {
		// With explicit version
		$this->assertEquals( 'latest', PackageReferenceUtils::extract_version( 'woocommerce/e2e:latest' ) );
		$this->assertEquals( '1.0.0', PackageReferenceUtils::extract_version( 'woocommerce/e2e:1.0.0' ) );
		$this->assertEquals( 'stable', PackageReferenceUtils::extract_version( 'woocommerce/e2e:stable' ) );
		$this->assertEquals( 'rc', PackageReferenceUtils::extract_version( 'woocommerce/e2e:rc' ) );

		// Without version - returns null (NO FALLBACK)
		$this->assertNull( PackageReferenceUtils::extract_version( 'woocommerce/e2e' ) );
		$this->assertNull( PackageReferenceUtils::extract_version( 'woocommerce/e2e/checkout' ) );

		// Local paths return null
		$this->assertNull( PackageReferenceUtils::extract_version( __DIR__ ) );
		$this->assertNull( PackageReferenceUtils::extract_version( '.' ) );
	}

	/**
	 * Test checking if reference has a version.
	 */
	public function test_has_version(): void {
		// With version
		$this->assertTrue( PackageReferenceUtils::has_version( 'woocommerce/e2e:latest' ) );
		$this->assertTrue( PackageReferenceUtils::has_version( 'woocommerce/e2e:1.0.0' ) );

		// Without version
		$this->assertFalse( PackageReferenceUtils::has_version( 'woocommerce/e2e' ) );
		$this->assertFalse( PackageReferenceUtils::has_version( 'woocommerce/e2e/checkout' ) );

		// Local paths are considered as "having a version" since they don't need one
		$this->assertTrue( PackageReferenceUtils::has_version( __DIR__ ) );
		$this->assertTrue( PackageReferenceUtils::has_version( '.' ) );
	}

	/**
	 * Test validating package references - remote packages require versions.
	 */
	public function test_validate_reference(): void {
		// Valid references with versions
		PackageReferenceUtils::validate_reference( 'woocommerce/e2e:latest' );
		PackageReferenceUtils::validate_reference( 'woocommerce/e2e:1.0.0' );
		PackageReferenceUtils::validate_reference( 'woocommerce/e2e/checkout:1.0.0' );

		// Valid local paths
		PackageReferenceUtils::validate_reference( __DIR__ );
		PackageReferenceUtils::validate_reference( '.' );

		// This should not throw - we've validated successfully
		$this->assertTrue( true );
	}

	/**
	 * Test that validation throws for remote packages without versions.
	 */
	public function test_validate_reference_throws_for_missing_version(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Package reference 'woocommerce/e2e' is missing a version number" );

		PackageReferenceUtils::validate_reference( 'woocommerce/e2e' );
	}

	/**
	 * Test that validation throws for subpackages without versions.
	 */
	public function test_validate_reference_throws_for_subpackage_missing_version(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Package reference 'woocommerce/e2e/checkout' is missing a version number" );
		$this->expectExceptionMessage( "Subpackages must include a version" );

		PackageReferenceUtils::validate_reference( 'woocommerce/e2e/checkout' );
	}

	/**
	 * Test validating multiple references.
	 */
	public function test_validate_references(): void {
		// All valid
		PackageReferenceUtils::validate_references( [
			'woocommerce/e2e:latest',
			'woocommerce/checkout:1.0.0',
			__DIR__,  // Current test directory
			'.',
		] );

		$this->assertTrue( true );
	}

	/**
	 * Test that validating multiple references throws on first invalid.
	 */
	public function test_validate_references_throws_on_invalid(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Package reference 'woocommerce/e2e' is missing a version number" );

		PackageReferenceUtils::validate_references( [
			'woocommerce/checkout:1.0.0',  // Valid
			'woocommerce/e2e',              // Invalid - missing version
			'woocommerce/api:latest',       // Valid but won't be reached
		] );
	}
}