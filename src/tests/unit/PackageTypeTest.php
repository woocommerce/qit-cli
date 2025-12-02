<?php

namespace QIT_CLI_Tests\Unit;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Objects\PackageType;

class PackageTypeTest extends TestCase {

	public function test_test_constant_value(): void {
		$this->assertEquals( 'test', PackageType::TEST );
	}

	public function test_utility_constant_value(): void {
		$this->assertEquals( 'utility', PackageType::UTILITY );
	}

	public function test_get_default_returns_test(): void {
		$this->assertEquals( 'test', PackageType::get_default() );
	}

	public function test_all_returns_both_types(): void {
		$all = PackageType::all();

		$this->assertIsArray( $all );
		$this->assertCount( 2, $all );
		$this->assertContains( 'test', $all );
		$this->assertContains( 'utility', $all );
	}

	public function test_is_valid_with_test_type(): void {
		$this->assertTrue( PackageType::is_valid( 'test' ) );
	}

	public function test_is_valid_with_utility_type(): void {
		$this->assertTrue( PackageType::is_valid( 'utility' ) );
	}

	public function test_is_valid_with_invalid_type(): void {
		$this->assertFalse( PackageType::is_valid( 'invalid' ) );
	}

	public function test_is_valid_with_empty_string(): void {
		$this->assertFalse( PackageType::is_valid( '' ) );
	}

	public function test_is_valid_case_sensitive(): void {
		$this->assertFalse( PackageType::is_valid( 'TEST' ) );
		$this->assertFalse( PackageType::is_valid( 'Test' ) );
		$this->assertFalse( PackageType::is_valid( 'UTILITY' ) );
		$this->assertFalse( PackageType::is_valid( 'Utility' ) );
	}

	public function test_all_values_are_valid(): void {
		foreach ( PackageType::all() as $type ) {
			$this->assertTrue( PackageType::is_valid( $type ) );
		}
	}

	public function test_default_is_valid(): void {
		$default = PackageType::get_default();
		$this->assertTrue( PackageType::is_valid( $default ) );
	}

	public function test_default_is_in_all(): void {
		$default = PackageType::get_default();
		$this->assertContains( $default, PackageType::all() );
	}
}
