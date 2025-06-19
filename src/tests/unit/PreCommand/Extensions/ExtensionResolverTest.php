<?php

namespace QIT_CLI_Tests\PreCommand\Extensions;

use QIT_CLI\App;
use QIT_CLI_Tests\PreCommand\PreCommandTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ExtensionResolverTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWooComDependencies(
			[ 'wccom-plugin-1', 'wccom-plugin-2', 'wccom-plugin-3' ],
			[],
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
				'wccom-theme-1'  => [
					'slug'    => 'wccom-theme-1',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-theme-1.zip',
				],
			],
		] );

		// Mock download URL for wccom-theme-1
		$this->mockDownloadUrl(
			'https://qit.woo.com/downloads/wccom-theme-1.zip',
			$this->createMinimalThemeZip( 'wccom-theme-1', '1.0.0' )
		);

		// Mock failed WPORG request for wccom-plugin-1
		$wporg_url = sprintf(
			'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=%s',
			'wccom-plugin-1'
		);
		App::setVar( 'mock_' . $wporg_url, 'exception: Plugin not found' );

		// Mock WCCOM ID lookups for wccom-plugin-1, wccom-plugin-2, wccom-plugin-3
		foreach ( [ 'wccom-plugin-1', 'wccom-plugin-2', 'wccom-plugin-3' ] as $slug ) {
			App::setVar( 'mock_woo_extension_id_' . $slug, [ 'id' => crc32( $slug ) ] );
		}

		// Existing mocks for other plugins/themes
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

		// Mock WordPress.org plugin downloads
		$woo_zip = $this->createMinimalPluginZip( 'woocommerce', '8.0.0' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $woo_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.8.0.0.zip', $woo_zip );

		$stripe_zip = $this->createMinimalPluginZip( 'wporg-plugin-1', '9.5.2' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/wporg-plugin-1.zip', $stripe_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/wporg-plugin-1.9.5.2.zip', $stripe_zip );

		foreach ( [ 'wccom-plugin-4', 'wccom-plugin-5', 'wccom-and-wporg-plugin-1', 'wccom-plugin-2', 'wccom-plugin-3' ] as $slug ) {
			$zip = $this->createMinimalPluginZip( $slug, '1.0.0' );
			$this->mockDownloadUrl( "https://downloads.wordpress.org/plugin/{$slug}.zip", $zip );
			$this->mockDownloadUrl( "https://downloads.wordpress.org/plugin/{$slug}.1.0.0.zip", $zip );
		}

		// Mock theme downloads
		$theme_zip = $this->createMinimalThemeZip( 'wporg-theme-1', '1.0' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/wporg-theme-1.zip', $theme_zip );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/wporg-theme-1.1.0.zip', $theme_zip );
	}

	public function test_resolve_extensions_cache_hit_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin',
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

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $env_info['themes'], true ) . "\n", FILE_APPEND );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertEmpty( $env_info['themes'], 'Plugins must not depend on themes' );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_resolve_extensions_api_fetch_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'wccom-plugin-1' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $env_info['themes'], true ) . "\n", FILE_APPEND );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertEmpty( $env_info['themes'], 'Plugins must not depend on themes' );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_resolve_extensions_string_config(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'wccom-plugin-1' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $env_info['themes'], true ) . "\n", FILE_APPEND );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertEmpty( $env_info['themes'], 'Plugins must not depend on themes' );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_resolve_extensions_object_config_wccom(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'wccom-plugin-1', 'source' => [ 'type' => 'wccom' ] ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $env_info['themes'], true ) . "\n", FILE_APPEND );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertEmpty( $env_info['themes'], 'Plugins must not depend on themes' );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_resolve_extensions_object_config_wporg(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'wporg-plugin-1', 'source' => [ 'type' => 'wporg' ] ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $env_info['themes'], true ) . "\n", FILE_APPEND );
		$this->assertNotEmpty( $plugins );
		$this->assertContains( 'wporg-plugin-1', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertEmpty( $env_info['themes'], 'No themes should be included for WP.org plugins' );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_resolve_extensions_object_config_local(): void {
		$local_path = $this->temp_dir . '/local-plugin-2';
		mkdir( $local_path, 0777, true );
		$this->to_delete[] = $local_path;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin', // Relative path
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'local-plugin-2', 'source' => [ 'type' => 'directory', 'path' => $local_path ] ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $env_info['themes'], true ) . "\n", FILE_APPEND );
		$this->assertCount( 2, $plugins, 'Expected SUT and one local plugin' );
		$this->assertContains( 'local-plugin-2', $plugins );
		$this->assertContains( 'local-plugin-1', $plugins );
		$this->assertEmpty( $env_info['themes'] );
		$this->assertEmpty( $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_resolve_extensions_object_config_zip(): void {
		// Create ZIP file
		$zip_path = $this->temp_dir . '/local-plugin-3.zip';
		file_put_contents( $zip_path, $this->createMinimalPluginZip( 'local-plugin-3', '1.0.0' ) );
		$this->to_delete[] = $zip_path;

		// Config with absolute ZIP path
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
						[
							'slug'   => 'local-plugin-3',
							'source' => [
								'type' => 'zip',
								'path' => $zip_path,
							],
						],
					],
				],
			],
		];

		// Debug log
		file_put_contents( '/tmp/qit/qit_debug.log', "ZIP path: $zip_path\n", FILE_APPEND );

		// Run test
		$env_info = $this->run_unit_test( $config, [], false );

		// Assert
		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 2, $plugins, 'Expected SUT and one plugin' );
		$this->assertContains( 'local-plugin-3', $plugins );
		$this->assertContains( 'local-plugin-1', $plugins );
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
					'path' => './my-awesome-plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'wporg-plugin-1',
						[ 'slug' => 'wccom-plugin-1', 'source' => [ 'type' => 'wccom' ] ],
						[ 'slug' => 'local-plugin-2', 'source' => [ 'type' => 'directory', 'path' => $local_path ] ],
					],
					'themes'  => [
						'wporg-theme-1',
						[ 'slug' => 'local-theme-1', 'source' => [ 'type' => 'directory', 'path' => $theme_path ] ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'], $env_info['themes'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\nThemes resolved: " . print_r( $themes, true ) . "\n", FILE_APPEND );
		$this->assertContains( 'wporg-plugin-1', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'wccom-plugin-1', $plugins );
		$this->assertContains( 'wccom-plugin-2', $plugins );
		$this->assertContains( 'wccom-plugin-3', $plugins );
		$this->assertContains( 'local-plugin-2', $plugins );
		$this->assertContains( 'wporg-theme-1', $themes );
		$this->assertContains( 'local-theme-1', $themes );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_invalid_slug_string_config(): void {
		// Config with invalid plugin slug
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin', // Relative to qit.json
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'nonexisting-plugin-1' ],
				],
			],
		];

		// Run the test, expecting failure
		$error = $this->run_unit_test( $config, [], true );

		// Assert the updated error message
		$this->assertStringContainsString(
			"Could not resolve source for extension 'nonexisting-plugin-1' (plugin). Not found in WPORG, WCCOM, or local sources.",
			$error['output']
		);
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
						[ 'slug' => 'nonexisting-plugin-1', 'source' => [ 'type' => 'invalid' ] ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( "Invalid source type 'invalid'", $error['output'] );
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
						[ 'slug' => 'local-plugin-2', 'source' => [ 'type' => 'directory' ] ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( "Extension 'local-plugin-2' has no directory path", $error['output'] );
	}

	public function test_local_plugin_b_with_dependency(): void {
		// Mock WCCOM dependencies
		$this->mockWooComDependencies( [ 'woocommerce', 'wccom-plugin-4', 'wccom-plugin-5' ], [], [] );
		// Mock WCCOM lookup for wccom-plugin-5
		App::setVar( 'mock_woo_extension_id_wccom-plugin-5', [ 'id' => crc32( 'wccom-plugin-5' ) ] );

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => './my-awesome-plugin',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'wccom-plugin-5', 'source' => [ 'type' => 'wccom' ] ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		file_put_contents( '/tmp/qit/qit_debug.log', "Plugins resolved: " . print_r( $plugins, true ) . "\n", FILE_APPEND );
		$this->assertCount( 4, $plugins, 'Expected SUT, wccom-plugin-5, and its dependencies' );
		$this->assertContains( 'wccom-plugin-5', $plugins );
		$this->assertContains( 'woocommerce', $plugins );
		$this->assertContains( 'wccom-plugin-4', $plugins );
		$this->assertContains( 'local-plugin-1', $plugins );

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