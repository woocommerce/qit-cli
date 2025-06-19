<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class EnvironmentConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// Create minimal WooCommerce ZIP content
		$woo_zip_content = $this->createMinimalPluginZip( 'woocommerce', '8.0.0' );

		// Mock WooCommerce API response and ZIP download
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $woo_zip_content );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip', $woo_zip_content );

		// Mock Storefront theme
		$storefront_zip_content = $this->createMinimalThemeZip( 'storefront', '4.5.0' );
		$this->mockWpOrgTheme( 'storefront', '4.5.0', 'https://downloads.wordpress.org/theme/storefront.4.5.0.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/storefront.zip', $storefront_zip_content );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/storefront.4.5.0.zip', $storefront_zip_content );

		// Mock Twenty Twenty-One theme
		$twentytwentyone_zip_content = $this->createMinimalThemeZip( 'twentytwentyone', '1.0.0' );
		$this->mockWpOrgTheme( 'twentytwentyone', '1.0.0', 'https://downloads.wordpress.org/theme/twentytwentyone.1.0.0.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/twentytwentyone.zip', $twentytwentyone_zip_content );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/twentytwentyone.1.0.0.zip', $twentytwentyone_zip_content );
	}

	public function test_environment_with_env_vars(): void {
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
					'plugins'  => [ 'woocommerce' ],
					'env_vars' => [
						'QIT_DEBUG' => 'true',
						'APP_ENV'   => 'test',
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'true', $env_info['env']['QIT_DEBUG'] );
		$this->assertEquals( 'test', $env_info['env']['APP_ENV'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_environment_with_plugins_and_themes(): void {
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
					'plugins'     => [
						'woocommerce',
						[ 'slug' => 'custom-plugin', 'from' => 'local', 'path' => '/normalized/path/custom-plugin' ],
					],
					'themes'      => [
						'storefront',
						[ 'slug' => 'custom-theme', 'from' => 'local', 'path' => '/normalized/path/custom-theme' ],
					],
					'php_version' => '8.2',
					'wp_version'  => 'stable',
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$plugins  = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes   = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'custom-plugin', $plugins );
		$this->assertContains( 'storefront', $themes );
		$this->assertContains( 'custom-theme', $themes );
		$this->assertEquals( '8.2', $env_info['php_version'] );
		$this->assertEquals( 'latest', $env_info['wp_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_environment_with_setup_test_packages(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'test_packages' => [
				'e2e' => [
					'default' => [
						'test_dir'     => './tests/e2e',
						'test_command' => 'npm run playwright',
					],
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'setup'   => [
						'test_packages' => [ 'local/default' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'setup', $env_info );
		$this->assertEquals( [ 'local/default' ], $env_info['setup']['test_packages'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_skip_activation_flags(): void {
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
					'plugins' => [ 'woocommerce' ],
					'themes'  => [ 'storefront' ],
				],
			],
		];

		$cli_args = [
			'--skip_activating_plugins' => true,
			'--skip_activating_themes'  => true,
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( $env_info['skip_activating_plugins'] );
		$this->assertTrue( $env_info['skip_activating_themes'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}
