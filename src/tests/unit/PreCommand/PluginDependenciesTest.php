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
			[ 'wccom-plugin-1', 'wccom-plugin-2', 'wccom-plugin-3' ],
			[ 'wccom-theme-1' ],
			[ 'gd' ]
		);
		$this->mockWooComDownloadUrls( [
			'urls' => [
				'wccom-plugin-1' => [
					'slug'    => 'wccom-plugin-1',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-1.zip',
				],
				'wccom-plugin-2' => [
					'slug'    => 'wccom-plugin-2',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-2.zip',
				],
				'wccom-plugin-3' => [
					'slug'    => 'wccom-plugin-3',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-3.zip',
				],
				'wccom-plugin-5' => [
					'slug'    => 'wccom-plugin-5',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-5.zip',
				],
				'wccom-plugin-4' => [
					'slug'    => 'wccom-plugin-4',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-4.zip',
				],
				'woocommerce'    => [
					'slug'    => 'woocommerce',
					'version' => '8.0.0',
					'url'     => 'https://qit.woo.com/downloads/woocommerce.zip',
				],
			],
		] );
		$this->mockWpOrgPlugin( 'wporg-plugin-1', '9.5.2', 'https://downloads.wordpress.org/plugin/wporg-plugin-1.9.5.2.zip', [ 'woocommerce' ] );
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip' );
		$this->mockWpOrgPlugin( 'wccom-and-wporg-plugin-1', '1.0.0', 'https://downloads.wordpress.org/plugin/wccom-and-wporg-plugin-1.1.0.0.zip', [
			'wccom-plugin-2',
			'wccom-plugin-3'
		] );
		$this->mockWpOrgPlugin( 'wccom-plugin-2', '1.0.0', 'https://downloads.wordpress.org/plugin/wccom-plugin-2.1.0.0.zip' );
		$this->mockWpOrgPlugin( 'wccom-plugin-3', '1.0.0', 'https://downloads.wordpress.org/plugin/wccom-plugin-3.1.0.0.zip' );
		$this->mockWpOrgTheme( 'wporg-theme-1', '1.0', 'https://downloads.wordpress.org/theme/wporg-theme-1.1.0.zip' );

		// Mock download URLs for plugins
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-1.zip', $this->createMinimalPluginZip( 'wccom-plugin-1', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-2.zip', $this->createMinimalPluginZip( 'wccom-plugin-2', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-3.zip', $this->createMinimalPluginZip( 'wccom-plugin-3', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-5.zip', $this->createMinimalPluginZip( 'wccom-plugin-5', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-4.zip', $this->createMinimalPluginZip( 'wccom-plugin-4', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/woocommerce.zip', $this->createMinimalPluginZip( 'woocommerce', '8.0.0' ) );

		// Mock WordPress.org plugin downloads (both versioned and non-versioned URLs)
		$woo_zip = $this->createMinimalPluginZip( 'woocommerce', '8.0.0' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $woo_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip', $woo_zip );

		$stripe_zip = $this->createMinimalPluginZip( 'wporg-plugin-1', '9.5.2' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/wporg-plugin-1.zip', $stripe_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/wporg-plugin-1.9.5.2.zip', $stripe_zip );

		// Mock other plugins that might be needed
		foreach ( [ 'wccom-plugin-4', 'wccom-plugin-5', 'wccom-and-wporg-plugin-1', 'wccom-plugin-2', 'wccom-plugin-3' ] as $slug ) {
			$zip = $this->createMinimalPluginZip( $slug, '1.0.0' );
			$this->mockDownloadUrl( "https://downloads.wordpress.org/plugin/{$slug}.zip", $zip );
			$this->mockDownloadUrl( "https://downloads.wordpress.org/plugin/{$slug}.1.0.0.zip", $zip );
		}

		// Mock theme downloads
		$theme_zip = $this->createMinimalThemeZip( 'wporg-theme-1', '1.0' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/wporg-theme-1.zip', $theme_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/wporg-theme-1.1.0.zip', $theme_zip );

		// Mock plugin directory is created by the parent class
	}

	public function test_get_dependencies_cache_hit_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'wccom-plugin-1' ],
				],
			],
		];

		$this->run_unit_test( $config ); // Populate cache
		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertContains( 'wccom-theme-1', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_api_fetch_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'wccom-plugin-1' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertContains( 'wccom-theme-1', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_none_mode_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'wccom-plugin-1' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config, [ '--dependencies_mode' => 'none' ] );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_wccom(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'wccom-plugin-1', 'from' => 'wccom' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertContains( 'wccom-theme-1', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_wporg(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'wporg-plugin-1', 'from' => 'wporg' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wporg-plugin-1', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_local(): void {
		$local_path = $this->temp_dir . '/local-plugin-2';
		mkdir( $local_path, 0777, true );
		$this->to_delete[] = $local_path;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'local-plugin-2', 'from' => 'local', 'path' => $local_path ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins );
		$this->assertContains( 'local-plugin-2', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_zip(): void {
		$zip_path = $this->temp_dir . '/local-plugin-3.zip';
		touch( $zip_path );
		$this->to_delete[] = $zip_path;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'local-plugin-3', 'from' => 'zip', 'path' => $zip_path ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins );
		$this->assertContains( 'local-plugin-3', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_integration_plugin_and_theme_dependencies_hybrid(): void {
		$local_path = $this->temp_dir . '/local-plugin-2';
		mkdir( $local_path, 0777, true );
		$theme_path = $this->temp_dir . '/local-theme-1';
		mkdir( $theme_path, 0777, true );
		$this->to_delete[] = $local_path;
		$this->to_delete[] = $theme_path;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'wporg-plugin-1',
						[ 'slug' => 'wccom-plugin-1', 'from' => 'wccom' ],
						[ 'slug' => 'local-plugin-2', 'from' => 'local', 'path' => $local_path ],
					],
					'themes'  => [
						'wporg-theme-1',
						[ 'slug' => 'local-theme-1', 'from' => 'local', 'path' => $theme_path ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertContains( 'wporg-plugin-1', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertContains( 'local-plugin-2', $plugins );
		$this->assertContains( 'wporg-theme-1', $themes );
		$this->assertContains( 'wccom-theme-1', $themes );
		$this->assertContains( 'local-theme-1', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_invalid_slug_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'nonexisting-plugin-1' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Extension \'nonexisting-plugin-1\' (plugin) not found in WooCommerce.com or WordPress.org', $error['output'] );
	}

	public function test_invalid_from_object_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'nonexisting-plugin-1', 'from' => 'invalid' ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid \'from\' value \'invalid\' for \'nonexisting-plugin-1\' in plugins', $error['output'] );
	}

	public function test_missing_path_local_object_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'local-plugin-2', 'from' => 'local' ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Local extension \'local-plugin-2\' (plugin) must have a non-empty \'path\'', $error['output'] );
	}

	public function test_local_plugin_b_with_dependency(): void {
		$this->mockWooComDependencies( [ 'woocommerce', 'wccom-plugin-4', 'wccom-plugin-5' ], [], [] );

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->getMockPluginDir(),
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'wccom-plugin-5', 'from' => 'wccom' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 3, $plugin_slugs );
		$this->assertContains( 'wccom-plugin-5', $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
		$this->assertContains( 'wccom-plugin-4', $plugin_slugs );

		$plugin_b = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'wccom-plugin-5' );
		$plugin_b = reset( $plugin_b );
		$this->assertEquals( 'wccom-plugin-5', $plugin_b['slug'] );
		$this->assertEquals( 'wccom', $plugin_b['from'] );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );

		$plugin_a = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'wccom-plugin-4' );
		$plugin_a = reset( $plugin_a );
		$this->assertEquals( 'wccom-plugin-4', $plugin_a['slug'] );

		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}
