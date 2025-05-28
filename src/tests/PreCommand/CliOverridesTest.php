<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;
use QIT_CLI\Environment\Extension;

class CliOverridesTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		// Mock extensions used in tests
		$this->mockExtension( 'woocommerce', 'plugin', '8.0.0', 'wporg' );
		$this->mockExtension( 'contact-form-7', 'plugin', '5.6.0', 'wporg' );
		$this->mockExtension( 'storefront', 'theme', '4.1.0', 'wporg' );
		$this->mockExtension( 'twentytwentyone', 'theme', '1.7', 'wporg' );
	}

	public function test_version_overrides(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [ [ 'slug' => 'woocommerce', 'from' => 'wporg' ] ],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
					'woo_version' => '6.0.0',
				],
			],
		];

		$cli_args = [
			'--wp_version'  => '6.1',
			'--php_version' => '8.0',
			'--woo_version' => '8.0.0',
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertEquals( '6.1', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$this->assertEquals( '8.0.0', $env_info['woo_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_plugin_and_theme_overrides(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ [ 'slug' => 'woocommerce', 'from' => 'wporg' ] ],
					'themes'  => [ [ 'slug' => 'storefront', 'from' => 'wporg' ] ],
				],
			],
		];

		$cli_args = [
			'--plugin' => [ 'contact-form-7' ],
			'--theme'  => [ 'twentytwentyone' ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$plugins  = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes   = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'contact-form-7', $plugins );
		$this->assertContains( 'storefront', $themes );
		$this->assertContains( 'twentytwentyone', $themes );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_env_and_env_file_overrides(): void {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "FILE_VAR=file\nSHARED_VAR=file" );
		$this->to_delete[] = $env_file;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins'  => [ [ 'slug' => 'woocommerce', 'from' => 'wporg' ] ],
					'env_vars' => [
						'CONFIG_VAR' => 'config',
						'SHARED_VAR' => 'config',
					],
				],
			],
		];

		$cli_args = [
			'--env'      => [ 'CLI_VAR=cli', 'SHARED_VAR=cli' ],
			'--env_file' => [ $env_file ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'config', $env_info['env']['CONFIG_VAR'] );
		$this->assertEquals( 'file', $env_info['env']['FILE_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['CLI_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['SHARED_VAR'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}