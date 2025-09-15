<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Objects\SutInput;

/**
 * Test for SutInput value object - represents System-Under-Test configuration.
 */
class SutInputTest extends TestCase {
	
	/**
	 * Test default constructor values.
	 */
	public function test_default_constructor(): void {
		$sut = new SutInput();
		
		$this->assertEquals( '', $sut->slug );
		$this->assertEquals( 'plugin', $sut->type );
		$this->assertEquals( [ 'type' => 'wporg' ], $sut->source );
		$this->assertTrue( $sut->from_cli );
	}
	
	/**
	 * Test constructor with parameters.
	 */
	public function test_constructor_with_params(): void {
		$sut = new SutInput( 'woocommerce', 'theme' );
		
		$this->assertEquals( 'woocommerce', $sut->slug );
		$this->assertEquals( 'theme', $sut->type );
		$this->assertEquals( [ 'type' => 'wporg' ], $sut->source );
		$this->assertTrue( $sut->from_cli );
	}
	
	/**
	 * Test to_array conversion.
	 */
	public function test_to_array(): void {
		$sut = new SutInput( 'my-plugin', 'plugin' );
		$sut->source = [
			'type' => 'local',
			'path' => '/path/to/plugin'
		];
		
		$array = $sut->to_array();
		
		$this->assertEquals( [
			'slug' => 'my-plugin',
			'type' => 'plugin',
			'source' => [
				'type' => 'local',
				'path' => '/path/to/plugin'
			]
		], $array );
	}
	
	/**
	 * Test from_array factory method.
	 */
	public function test_from_array(): void {
		$data = [
			'slug' => 'custom-theme',
			'type' => 'theme',
			'source' => [
				'type' => 'url',
				'url' => 'https://example.com/theme.zip'
			],
			'from_cli' => false
		];
		
		$sut = SutInput::from_array( $data );
		
		$this->assertEquals( 'custom-theme', $sut->slug );
		$this->assertEquals( 'theme', $sut->type );
		$this->assertEquals( [
			'type' => 'url',
			'url' => 'https://example.com/theme.zip'
		], $sut->source );
		$this->assertFalse( $sut->from_cli );
	}
	
	/**
	 * Test from_array with missing data uses defaults.
	 */
	public function test_from_array_with_defaults(): void {
		$sut = SutInput::from_array( [] );
		
		$this->assertEquals( '', $sut->slug );
		$this->assertEquals( 'plugin', $sut->type );
		$this->assertEquals( [], $sut->source );
		$this->assertFalse( $sut->from_cli );
	}
	
	/**
	 * Test from_array with partial data.
	 */
	public function test_from_array_partial(): void {
		$data = [
			'slug' => 'woocommerce'
			// Missing other fields
		];
		
		$sut = SutInput::from_array( $data );
		
		$this->assertEquals( 'woocommerce', $sut->slug );
		$this->assertEquals( 'plugin', $sut->type ); // Default
		$this->assertEquals( [], $sut->source ); // Default empty
		$this->assertFalse( $sut->from_cli ); // Default false
	}
	
	/**
	 * Test roundtrip conversion (from_array -> to_array).
	 */
	public function test_roundtrip_conversion(): void {
		$original = [
			'slug' => 'test-plugin',
			'type' => 'plugin',
			'source' => [
				'type' => 'build',
				'command' => 'npm run build',
				'path' => './dist'
			]
		];
		
		$sut = SutInput::from_array( $original );
		$result = $sut->to_array();
		
		// Should match original (minus from_cli which isn't in to_array)
		$this->assertEquals( $original, $result );
	}
	
	/**
	 * Test various source types.
	 */
	public function test_various_source_types(): void {
		// WPOrg source
		$sut = new SutInput( 'akismet' );
		$sut->source = [ 'type' => 'wporg' ];
		$this->assertEquals( 'wporg', $sut->source['type'] );
		
		// Local source
		$sut = new SutInput( 'local-plugin' );
		$sut->source = [
			'type' => 'local',
			'path' => './my-plugin'
		];
		$this->assertEquals( 'local', $sut->source['type'] );
		$this->assertEquals( './my-plugin', $sut->source['path'] );
		
		// URL source
		$sut = new SutInput( 'remote-plugin' );
		$sut->source = [
			'type' => 'url',
			'url' => 'https://github.com/user/repo/archive/main.zip'
		];
		$this->assertEquals( 'url', $sut->source['type'] );
		$this->assertEquals( 'https://github.com/user/repo/archive/main.zip', $sut->source['url'] );
		
		// Build source
		$sut = new SutInput( 'built-plugin' );
		$sut->source = [
			'type' => 'build',
			'command' => 'composer install && npm run build',
			'path' => './build/output.zip'
		];
		$this->assertEquals( 'build', $sut->source['type'] );
		$this->assertEquals( 'composer install && npm run build', $sut->source['command'] );
	}
}