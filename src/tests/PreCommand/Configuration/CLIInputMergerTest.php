<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use QIT_CLI_Tests\PreCommand\PreCommandTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use QIT_CLI\Environment\Extension;

class CLIInputMergerTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// Mock WooCommerce.com dependencies and downloads
		$this->mockWooComDependencies(
			[ 'woocommerce', 'contact-form-7' ], // Plugins
			[ 'storefront', 'twentytwentyone' ], // Themes
			[ 'gd' ] // PHP extensions
		);
		$this->mockWooComDownloadUrls( [
			'urls' => [
				'woocommerce'     => [
					'slug'    => 'woocommerce',
					'version' => '8.0.0',
					'url'     => 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip',
				],
				'contact-form-7'  => [
					'slug'    => 'contact-form-7',
					'version' => '5.6.0',
					'url'     => 'https://downloads.wordpress.org/plugin/contact-form-7.5.6.0.zip',
				],
				'storefront'      => [
					'slug'    => 'storefront',
					'version' => '4.1.0',
					'url'     => 'https://downloads.wordpress.org/theme/storefront.4.1.0.zip',
				],
				'twentytwentyone' => [
					'slug'    => 'twentytwentyone',
					'version' => '1.7',
					'url'     => 'https://downloads.wordpress.org/theme/twentytwentyone.1.7.zip',
				],
			],
		] );

		// Create and mock WooCommerce plugin
		$woo_zip = $this->createMinimalPluginZip( 'woocommerce', '8.0.0' );
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $woo_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip', $woo_zip );

		// Create and mock Contact Form 7 plugin
		$cf7_zip = $this->createMinimalPluginZip( 'contact-form-7', '5.6.0' );
		$this->mockWpOrgPlugin( 'contact-form-7', '5.6.0', 'https://downloads.wordpress.org/plugin/contact-form-7.5.6.0.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/contact-form-7.zip', $cf7_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/contact-form-7.5.6.0.zip', $cf7_zip );

		// Create and mock Storefront theme
		$storefront_zip = $this->createMinimalThemeZip( 'storefront', '4.1.0' );
		$this->mockWpOrgTheme( 'storefront', '4.1.0', 'https://downloads.wordpress.org/theme/storefront.4.1.0.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/storefront.zip', $storefront_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/storefront.4.1.0.zip', $storefront_zip );

		// Create and mock Twenty Twenty-One theme
		$twentytwentyone_zip = $this->createMinimalThemeZip( 'twentytwentyone', '1.7' );
		$this->mockWpOrgTheme( 'twentytwentyone', '1.7', 'https://downloads.wordpress.org/theme/twentytwentyone.1.7.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/twentytwentyone.zip', $twentytwentyone_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/twentytwentyone.1.7.zip', $twentytwentyone_zip );
	}

	public function test_version_overrides(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
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
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
					'themes'  => [ [ 'slug' => 'storefront', 'source' => [ 'type' => 'wporg' ] ] ],
				],
			],
		];

		$cli_args = [
			'--plugin' => [ 'contact-form-7' ],
			'--theme'  => [ 'twentytwentyone' ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$plugins  = array_map( fn( $p ) => is_object( $p ) ? $p->slug : ( is_array( $p ) ? $p['slug'] : $p ), $env_info['plugins'] );
		$themes   = array_map( fn( $t ) => is_object( $t ) ? $t->slug : ( is_array( $t ) ? $t['slug'] : $t ), $env_info['themes'] );
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
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins'  => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
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

	public function test_null_values_preserve_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
				],
			],
		];

		$cli_args = [
			'--wp_version'  => null,
			'--php_version' => '8.0',
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertEquals( '6.0', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extends_with_overrides(): void {
		$base_file   = $this->temp_dir . '/base.json';
		$base_config = [
			'environments' => [
				'default' => [
					'wp_version'  => '6.0',
					'php_version' => '7.4',
					'plugins'     => [ [ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wporg' ] ] ],
				],
			],
		];
		file_put_contents( $base_file, json_encode( $base_config ) );
		$this->to_delete[] = $base_file;

		$config = [
			'extends'      => 'base.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'php_version' => '8.0',
				],
			],
		];

		$cli_args = [
			'--wp_version' => '6.1',
			'--plugin'     => [ 'contact-form-7' ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertEquals( '6.1', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$plugins = array_map( fn( $p ) => is_object( $p ) ? $p->slug : ( is_array( $p ) ? $p['slug'] : $p ), $env_info['plugins'] );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'contact-form-7', $plugins );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}