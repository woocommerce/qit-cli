<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class PluginDependenciesTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWooComDependencies(
			[ 'bar-extension', 'baz-extension' ],
			[ 'qit-beaver' ],
			[ 'gd' ]
		);
		$this->mockWooComDownloadUrls( [
			'foo-extension' => 'https://qit.woo.com/downloads/foo-extension.zip',
			'bar-extension' => 'https://qit.woo.com/downloads/bar-extension.zip',
			'baz-extension' => 'https://qit.woo.com/downloads/baz-extension.zip',
			'plugin-b'      => 'https://qit.woo.com/downloads/plugin-b.zip',
			'plugin-a'      => 'https://qit.woo.com/downloads/plugin-a.zip',
			'woocommerce'   => 'https://qit.woo.com/downloads/woocommerce.zip',
		] );
		$this->mockWpOrgPlugin( 'woocommerce-gateway-stripe', '9.5.2', 'https://downloads.wordpress.org/plugin/woocommerce-gateway-stripe.9.5.2.zip', [ 'woocommerce' ] );
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip' );
		$this->mockWpOrgTheme( 'twentytwentyfive', '1.0', 'https://downloads.wordpress.org/theme/twentytwentyfive.1.0.zip' );
	}

	public function test_get_dependencies_cache_hit_string_config(): void {
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
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		$this->run_unit_test( $config ); // Populate cache
		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'foo-extension', $plugins );
		$this->assertContains( 'bar-extension', $plugins );
		$this->assertContains( 'baz-extension', $plugins );
		$this->assertContains( 'qit-beaver', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_api_fetch_string_config(): void {
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
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'foo-extension', $plugins );
		$this->assertContains( 'bar-extension', $plugins );
		$this->assertContains( 'baz-extension', $plugins );
		$this->assertContains( 'qit-beaver', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_none_mode_string_config(): void {
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
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config, [ '--dependencies_mode' => 'none' ] );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins );
		$this->assertContains( 'foo-extension', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_wccom(): void {
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
					'plugins' => [
						[ 'slug' => 'foo-extension', 'from' => 'wccom' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'foo-extension', $plugins );
		$this->assertContains( 'bar-extension', $plugins );
		$this->assertContains( 'baz-extension', $plugins );
		$this->assertContains( 'qit-beaver', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_wporg(): void {
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
					'plugins' => [
						[ 'slug' => 'woocommerce-gateway-stripe', 'from' => 'wporg' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'woocommerce-gateway-stripe', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_local(): void {
		$local_path = $this->temp_dir . '/custom-plugin';
		mkdir( $local_path, 0777, true );
		$this->to_delete[] = $local_path;

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
					'plugins' => [
						[ 'slug' => 'custom-plugin', 'from' => 'local', 'path' => $local_path ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins );
		$this->assertContains( 'custom-plugin', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_zip(): void {
		$zip_path = $this->temp_dir . '/premium-plugin.zip';
		touch( $zip_path );
		$this->to_delete[] = $zip_path;

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
					'plugins' => [
						[ 'slug' => 'premium-plugin', 'from' => 'zip', 'path' => $zip_path ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins );
		$this->assertContains( 'premium-plugin', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_integration_plugin_and_theme_dependencies_hybrid(): void {
		$local_path = $this->temp_dir . '/custom-plugin';
		mkdir( $local_path, 0777, true );
		$theme_path = $this->temp_dir . '/custom-theme';
		mkdir( $theme_path, 0777, true );
		$this->to_delete[] = $local_path;
		$this->to_delete[] = $theme_path;

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
					'plugins' => [
						'woocommerce-gateway-stripe',
						[ 'slug' => 'foo-extension', 'from' => 'wccom' ],
						[ 'slug' => 'custom-plugin', 'from' => 'local', 'path' => $local_path ],
					],
					'themes'  => [
						'twentytwentyfive',
						[ 'slug' => 'custom-theme', 'from' => 'local', 'path' => $theme_path ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertContains( 'woocommerce-gateway-stripe', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'foo-extension', $plugins );
		$this->assertContains( 'bar-extension', $plugins );
		$this->assertContains( 'baz-extension', $plugins );
		$this->assertContains( 'custom-plugin', $plugins );
		$this->assertContains( 'twentytwentyfive', $themes );
		$this->assertContains( 'qit-beaver', $themes );
		$this->assertContains( 'custom-theme', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_invalid_slug_string_config(): void {
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
					'plugins' => [ 'invalid-slug' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Extension \'invalid-slug\' (plugin) not found in WooCommerce.com or WordPress.org', $error );
	}

	public function test_invalid_from_object_config(): void {
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
					'plugins' => [
						[ 'slug' => 'some-plugin', 'from' => 'invalid' ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid \'from\' value \'invalid\' for \'some-plugin\' in plugins', $error );
	}

	public function test_missing_path_local_object_config(): void {
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
					'plugins' => [
						[ 'slug' => 'custom-plugin', 'from' => 'local' ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Local extension \'custom-plugin\' (plugin) must have a non-empty \'path\'', $error );
	}

	public function test_local_plugin_b_with_dependency(): void {
		$this->mockWooComDependencies( [ 'woocommerce', 'plugin-a', 'plugin-b' ], [], [] );

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
					'plugins' => [
						[ 'slug' => 'plugin-b', 'from' => 'wccom' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 3, $plugin_slugs );
		$this->assertContains( 'plugin-b', $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
		$this->assertContains( 'plugin-a', $plugin_slugs );

		$plugin_b = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-b' );
		$plugin_b = reset( $plugin_b );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'] );
		$this->assertEquals( 'wccom', $plugin_b['from'] );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );

		$plugin_a = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-a' );
		$plugin_a = reset( $plugin_a );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'] );

		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}