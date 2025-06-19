<?php

namespace QIT_CLI_Tests\PreCommand2;

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class PluginDependenciesTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// Mock wccom get-dependencies endpoint
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'bar-extension', 'baz-extension' ],
				'themes'         => [ 'qit-beaver' ],
				'php_extensions' => [ 'gd' ],
			] )
		);

		// Mock wporg plugin API
		App::setVar(
			sprintf( 'mock_%s', 'https://api.wordpress.org/plugins/info/1.0/woocommerce-gateway-stripe' ),
			serialize( (object) [
				'slug'             => 'woocommerce-gateway-stripe',
				'version'          => '9.5.2',
				'download_link'    => 'https://downloads.wordpress.org/plugin/woocommerce-gateway-stripe.9.5.2.zip',
				'requires_plugins' => [ 'woocommerce' ],
			] )
		);

		// Mock wporg theme API
		App::setVar(
			sprintf( 'mock_%s', 'https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]=twentytwentyfive' ),
			json_encode( [
				'slug'          => 'twentytwentyfive',
				'version'       => '1.0',
				'download_link' => 'https://downloads.wordpress.org/theme/twentytwentyfive.1.0.zip',
			] )
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		// Clear mocks
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), null );
		App::setVar( sprintf( 'mock_%s', 'https://api.wordpress.org/plugins/info/1.0/woocommerce-gateway-stripe' ), null );
		App::setVar( sprintf( 'mock_%s', 'https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]=twentytwentyfive' ), null );
	}

	public function test_get_dependencies_cache_hit_string_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		// Run once to populate cache
		$this->run_unit_test( $config );

		// Run again to hit cache
		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins, "No plugins found: " . json_encode( $plugins ) );
		$this->assertContains( 'bar-extension', $plugins, "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'baz-extension', $plugins, "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'qit-beaver', $themes, "qit-beaver not found: " . json_encode( $themes ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
	}

	public function test_get_dependencies_api_fetch_string_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins, "No plugins found: " . json_encode( $plugins ) );
		$this->assertContains( 'bar-extension', $plugins, "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'baz-extension', $plugins, "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'qit-beaver', $themes, "qit-beaver not found: " . json_encode( $themes ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
	}

	public function test_get_dependencies_none_mode_string_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config, [ '--dependencies_mode' => 'none' ] );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins, "Expected only one plugin: " . json_encode( $plugins ) );
		$this->assertContains( 'foo-extension', $plugins, "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertEmpty( $env_info['themes'], "Themes not empty: " . json_encode( $env_info['themes'] ) );
		$this->assertEmpty( $env_info['php_extensions'], "php_extensions not empty: " . json_encode( $env_info['php_extensions'] ) );
	}

	public function test_get_dependencies_object_config_wccom(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'foo-extension',
							'source' => [ 'from' => 'wccom' ]
						]
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertNotEmpty( $plugins, "No plugins found: " . json_encode( $plugins ) );
		$this->assertContains( 'foo-extension', $plugins, "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'bar-extension', $plugins, "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'baz-extension', $plugins, "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'qit-beaver', $themes, "qit-beaver not found: " . json_encode( $themes ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_wporg(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'woocommerce-gateway-stripe',
							'source' => [ 'from' => 'wporg' ]
						]
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertNotEmpty( $plugins, "No plugins found: " . json_encode( $plugins ) );
		$this->assertContains( 'woocommerce-gateway-stripe', $plugins, "woocommerce-gateway-stripe not found: " . json_encode( $plugins ) );
		$this->assertContains( 'woocommerce', $plugins, "woocommerce dependency not found: " . json_encode( $plugins ) );
		$this->assertEmpty( $env_info['themes'], "Themes not empty: " . json_encode( $env_info['themes'] ) );
		$this->assertEmpty( $env_info['php_extensions'], "php_extensions not empty: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_local(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'custom-plugin',
							'source' => [ 'from' => 'local', 'path' => '/path/to/custom-plugin' ]
						]
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins, "Expected only one plugin: " . json_encode( $plugins ) );
		$this->assertContains( 'custom-plugin', $plugins, "custom-plugin not found: " . json_encode( $plugins ) );
		$this->assertEmpty( $env_info['themes'], "Themes not empty: " . json_encode( $env_info['themes'] ) );
		$this->assertEmpty( $env_info['php_extensions'], "php_extensions not empty: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_get_dependencies_object_config_zip(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'premium-plugin',
							'source' => [ 'from' => 'zip', 'url' => 'https://example.com/premium-plugin.zip' ]
						]
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins, "Expected only one plugin: " . json_encode( $plugins ) );
		$this->assertContains( 'premium-plugin', $plugins, "premium-plugin not found: " . json_encode( $plugins ) );
		$this->assertEmpty( $env_info['themes'], "Themes not empty: " . json_encode( $env_info['themes'] ) );
		$this->assertEmpty( $env_info['php_extensions'], "php_extensions not empty: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_integration_plugin_and_theme_dependencies_hybrid(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce-gateway-stripe',
						[
							'slug'   => 'foo-extension',
							'source' => [ 'from' => 'wccom' ]
						],
						[
							'slug'   => 'custom-plugin',
							'source' => [ 'from' => 'local', 'path' => '/path/to/custom-plugin' ]
						]
					],
					'themes'  => [
						'twentytwentyfive',
						[
							'slug'   => 'custom-theme',
							'source' => [ 'from' => 'local', 'path' => '/path/to/custom-theme' ]
						]
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertContains( 'woocommerce-gateway-stripe', $plugins, "woocommerce-gateway-stripe not found: " . json_encode( $plugins ) );
		$this->assertContains( 'woocommerce', $plugins, "woocommerce dependency not found: " . json_encode( $plugins ) );
		$this->assertContains( 'foo-extension', $plugins, "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'bar-extension', $plugins, "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'baz-extension', $plugins, "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'custom-plugin', $plugins, "custom-plugin not found: " . json_encode( $plugins ) );
		$this->assertContains( 'twentytwentyfive', $themes, "twentytwentyfive not found: " . json_encode( $themes ) );
		$this->assertContains( 'qit-beaver', $themes, "qit-beaver not found: " . json_encode( $themes ) );
		$this->assertContains( 'custom-theme', $themes, "custom-theme not found: " . json_encode( $themes ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_integration_with_bootstrap_hybrid(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins'   => [
						'woocommerce-gateway-stripe',
						[
							'slug'   => 'foo-extension',
							'source' => [ 'from' => 'wccom' ]
						]
					],
					'bootstrap' => [
						[
							'slug'         => 'qit-beaver',
							'test_package' => 'helpers:default'
						]
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$themes  = array_map( fn( $t ) => $t['slug'] ?? $t, $env_info['themes'] );
		$this->assertContains( 'woocommerce-gateway-stripe', $plugins, "woocommerce-gateway-stripe not found: " . json_encode( $plugins ) );
		$this->assertContains( 'woocommerce', $plugins, "woocommerce dependency not found: " . json_encode( $plugins ) );
		$this->assertContains( 'foo-extension', $plugins, "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'bar-extension', $plugins, "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'baz-extension', $plugins, "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertContains( 'qit-beaver', $themes, "qit-beaver not found: " . json_encode( $themes ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_invalid_slug_string_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'invalid-slug' ],
				],
			],
		];

		try {
			$this->run_unit_test( $config );
			$this->fail( 'Expected command to fail due to invalid slug.' );
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( "Error loading config: Extension 'invalid-slug' (plugin) not found in WooCommerce.com or WordPress.org.", $e->getMessage() );
		}
	}

	public function test_invalid_from_object_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'some-plugin',
							'source' => [ 'from' => 'invalid' ]
						]
					],
				],
			],
		];

		try {
			$this->run_unit_test( $config );
			$this->fail( 'Expected command to fail due to invalid from value.' );
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( "Error loading config: Invalid 'from' value 'invalid' for 'some-plugin' in plugins. Must be one of: wporg, wccom, local, zip", $e->getMessage() );
		}
	}

	public function test_missing_path_local_object_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'custom-plugin',
							'source' => [ 'from' => 'local' ]
						]
					],
				],
			],
		];

		try {
			$this->run_unit_test( $config );
			$this->fail( 'Expected command to fail due to missing path.' );
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( "Error loading config: Local source for 'custom-plugin' in plugins must have a non-empty 'path' string.", $e->getMessage() );
		}
	}

	public function test_local_plugin_b_with_dependency(): void {
		// Mock wccom get-dependencies endpoint to include plugin-b as a dependency
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'woocommerce', 'plugin-a', 'plugin-b' ],
				'themes'         => [],
				'php_extensions' => [],
			] )
		);

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'plugin-b',
							'source' => [ 'from' => 'wccom' ], // Change to wccom
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 3, $plugin_slugs, "Expected three plugins: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-b', $plugin_slugs, "plugin-b not found: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'woocommerce', $plugin_slugs, "woocommerce not found: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-a', $plugin_slugs, "plugin-a not found: " . json_encode( $plugin_slugs ) );

		// Verify plugin-b is an Extension object
		$plugin_b = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => $plugin['slug'] === 'plugin-b'
		);
		$plugin_b = reset( $plugin_b );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'], "plugin-b slug incorrect" );
		$this->assertEquals( 'wccom', $plugin_b['from'], "plugin-b source incorrect" );

		// Verify woocommerce is an Extension object
		$woocommerce = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => $plugin['slug'] === 'woocommerce'
		);
		$woocommerce = reset( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'], "woocommerce slug incorrect" );

		// Verify plugin-a is an Extension object
		$plugin_a = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => $plugin['slug'] === 'plugin-a'
		);
		$plugin_a = reset( $plugin_a );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'], "plugin-a slug incorrect" );

		// Verify no unexpected themes or PHP extensions
		$this->assertEmpty( $env_info['themes'], "Themes not empty: " . json_encode( $env_info['themes'] ) );
		$this->assertEmpty( $env_info['php_extensions'], "php_extensions not empty: " . json_encode( $env_info['php_extensions'] ) );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}