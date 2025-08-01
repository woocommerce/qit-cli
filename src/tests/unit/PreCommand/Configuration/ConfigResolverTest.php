<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\ConfigResolver;

class ConfigResolverTest extends TestCase {

	public function test_basic_configuration_loading(): void {
		// Create a temporary config file
		$config_data = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'themes' => [ 'storefront' ]
				]
			],
			'test_types' => [
				'e2e' => [
					'default' => [
						'timeout' => 300
					]
				]
			]
		];

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_config_test_' );
		file_put_contents( $temp_file, json_encode( $config_data ) );

		try {
			$resolved = ConfigResolver::load( $temp_file );

			// Verify basic structure
			$this->assertArrayHasKey( 'environments', $resolved );
			$this->assertArrayHasKey( 'test_types', $resolved );
			$this->assertArrayHasKey( 'metadata', $resolved );

			// Verify environment configuration
			$this->assertEquals( [ 'woocommerce' ], $resolved['environments']['default']['plugins'] );
			$this->assertEquals( [ 'storefront' ], $resolved['environments']['default']['themes'] );

			// Verify test type configuration
			$this->assertEquals( 300, $resolved['test_types']['e2e']['default']['timeout'] );

			// Verify metadata
			$this->assertEquals( $temp_file, $resolved['metadata']['config_file'] );

		} finally {
			unlink( $temp_file );
		}
	}

	public function test_extends_resolution(): void {
		// Create a config with extends - using simpler structure to avoid schema issues
		$config_data = [
			'environments' => [
				'base' => [
					'plugins' => [ 'woocommerce' ]
				],
				'extended' => [
					'extends' => 'base',
					'plugins' => [ 'woocommerce', 'custom-plugin' ],
					'themes' => [ 'storefront' ]
				]
			]
		];

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_config_test_' );
		file_put_contents( $temp_file, json_encode( $config_data ) );

		try {
			$resolved = ConfigResolver::load( $temp_file );

			// Verify extends resolution
			$extended_env = $resolved['environments']['extended'];
			
			// Should have its own themes
			$this->assertEquals( [ 'storefront' ], $extended_env['themes'] );
			
			// Should have merged plugins (child takes precedence)
			$this->assertEquals( [ 'woocommerce', 'custom-plugin' ], $extended_env['plugins'] );
			
			// Should not have extends key in resolved config
			$this->assertArrayNotHasKey( 'extends', $extended_env );

		} finally {
			unlink( $temp_file );
		}
	}

	public function test_cli_overrides(): void {
		// Create a basic config
		$config_data = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'themes' => [ 'storefront' ]
				]
			]
		];

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_config_test_' );
		file_put_contents( $temp_file, json_encode( $config_data ) );

		try {
			$cli_overrides = [
				'themes' => [ 'twentytwentyfour' ],
				'plugins' => [ 'custom-plugin' ]
			];

			$resolved = ConfigResolver::load( $temp_file, $cli_overrides );

			// CLI overrides should be merged with existing values from environments
			$expected_themes = [ 'storefront', 'twentytwentyfour' ];
			$this->assertEquals( $expected_themes, $resolved['themes'] );
			
			// For list options, CLI should be merged with existing from environments
			$expected_plugins = [ 'woocommerce', 'custom-plugin' ];
			$this->assertEquals( $expected_plugins, $resolved['plugins'] );
			
			// Environment should have merged values (CLI overrides propagated back)
			$this->assertEquals( [ 'woocommerce', 'custom-plugin' ], $resolved['environments']['default']['plugins'] );
			$this->assertEquals( [ 'storefront', 'twentytwentyfour' ], $resolved['environments']['default']['themes'] );

		} finally {
			unlink( $temp_file );
		}
	}

	public function test_no_config_file(): void {
		$resolved = ConfigResolver::load( null );

		// Should have default structure
		$this->assertArrayHasKey( 'environments', $resolved );
		$this->assertArrayHasKey( 'test_types', $resolved );
		$this->assertArrayHasKey( 'metadata', $resolved );

		// Should have default environment
		$this->assertArrayHasKey( 'default', $resolved['environments'] );

		// Should have default e2e profile
		$this->assertArrayHasKey( 'e2e', $resolved['test_types'] );
		$this->assertArrayHasKey( 'default', $resolved['test_types']['e2e'] );

		// Metadata should indicate no config file
		$this->assertNull( $resolved['metadata']['config_file'] );
	}
}