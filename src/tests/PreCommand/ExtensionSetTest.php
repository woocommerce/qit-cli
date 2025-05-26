<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class ExtensionSetTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		file_put_contents( '/tmp/qit/qit_debug.log', "Starting ExtensionSetTest::setUp\n", FILE_APPEND );
		parent::setUp();

		// Mock WooCommerce.com get-dependencies endpoint
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'woocommerce', 'plugin-a', 'plugin-b' ],
				'themes'         => [],
				'php_extensions' => [],
			] )
		);

		// Mock WordPress.org plugin API for all plugins
		foreach ( [ 'woocommerce', 'plugin-a', 'plugin-b' ] as $slug ) {
			App::setVar(
				sprintf( 'mock_%s', "https://api.wordpress.org/plugins/info/1.0/{$slug}" ),
				serialize( (object) [
					'slug'             => $slug,
					'version'          => $slug === 'woocommerce' ? '8.0.0' : '1.0.0',
					'download_link'    => "https://downloads.wordpress.org/plugin/{$slug}.zip",
					'requires_plugins' => [],
				] )
			);
		}
	}

	public function tearDown(): void {
		parent::tearDown();
		// Clear mocks
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), null );
		foreach ( [ 'woocommerce', 'plugin-a', 'plugin-b' ] as $slug ) {
			App::setVar( sprintf( 'mock_%s', "https://api.wordpress.org/plugins/info/1.0/{$slug}" ), null );
		}
		file_put_contents( '/tmp/qit/qit_debug.log', "Finished ExtensionSetTest::tearDown\n", FILE_APPEND );
	}

	public function test_no_extension_set(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'woocommerce',
							'source' => [ 'from' => 'wporg' ],
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 1, $plugin_slugs, "Expected one plugin: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'woocommerce', $plugin_slugs, "woocommerce not found: " . json_encode( $plugin_slugs ) );

		// Verify woocommerce is an array and not dynamic
		$woocommerce = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'woocommerce'
		);
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce, "woocommerce is not an array" );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'], "woocommerce slug incorrect" );
		$this->assertNull( $woocommerce['added_automatically'], "woocommerce should have no dynamic reason" );
		$this->assertEquals( 'wporg', $woocommerce['from'], "woocommerce should be from wporg" );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_empty_extension_set(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'woocommerce',
							'source' => [ 'from' => 'wporg' ],
						],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_empty_extension_set with args: " . json_encode( [ '--extension_set' => 'empty-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'empty-set' ] );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 1, $plugin_slugs, "Expected one plugin: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'woocommerce', $plugin_slugs, "woocommerce not found: " . json_encode( $plugin_slugs ) );

		// Verify woocommerce is an array and not dynamic
		$woocommerce = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'woocommerce'
		);
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce, "woocommerce is not an array" );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'], "woocommerce slug incorrect" );
		$this->assertNull( $woocommerce['added_automatically'], "woocommerce should have no dynamic reason" );
		$this->assertEquals( 'wporg', $woocommerce['from'], "woocommerce should be from wporg" );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_valid_extension_set(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'woocommerce',
							'source' => [ 'from' => 'wporg' ],
						],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_valid_extension_set with args: " . json_encode( [ '--extension_set' => 'test-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'test-set' ] );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 3, $plugin_slugs, "Expected three plugins: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'woocommerce', $plugin_slugs, "woocommerce not found: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-a', $plugin_slugs, "plugin-a not found: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-b', $plugin_slugs, "plugin-b not found: " . json_encode( $plugin_slugs ) );

		// Verify woocommerce is an array and not dynamic
		$woocommerce = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'woocommerce'
		);
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce, "woocommerce is not an array" );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'], "woocommerce slug incorrect" );
		$this->assertNull( $woocommerce['added_automatically'], "woocommerce should have no dynamic reason" );
		$this->assertEquals( 'wporg', $woocommerce['from'], "woocommerce should be from wporg" );

		// Verify plugin-a is an array and dynamic
		$plugin_a = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'plugin-a'
		);
		$plugin_a = reset( $plugin_a );
		$this->assertIsArray( $plugin_a, "plugin-a is not an array" );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'], "plugin-a slug incorrect" );
		$this->assertEquals( 'Added via extension set', $plugin_a['added_automatically'], "plugin-a dynamic reason incorrect" );
		$this->assertEquals( 'wporg', $plugin_a['from'], "plugin-a should be from wporg" );

		// Verify plugin-b is an array and dynamic
		$plugin_b = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'plugin-b'
		);
		$plugin_b = reset( $plugin_b );
		$this->assertIsArray( $plugin_b, "plugin-b is not an array" );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'], "plugin-b slug incorrect" );
		$this->assertEquals( 'Added via extension set', $plugin_b['added_automatically'], "plugin-b dynamic reason incorrect" );
		$this->assertEquals( 'wporg', $plugin_b['from'], "plugin-b should be from wporg" );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extension_set_with_duplicates(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'plugin-a',
							'source' => [ 'from' => 'wporg' ],
						],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_extension_set_with_duplicates with args: " . json_encode( [ '--extension_set' => 'test-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'test-set' ] );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 2, $plugin_slugs, "Expected two plugins: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-a', $plugin_slugs, "plugin-a not found: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-b', $plugin_slugs, "plugin-b not found: " . json_encode( $plugin_slugs ) );

		// Verify plugin-a is an array and not dynamic
		$plugin_a = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'plugin-a'
		);
		$plugin_a = reset( $plugin_a );
		$this->assertIsArray( $plugin_a, "plugin-a is not an array" );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'], "plugin-a slug incorrect" );
		$this->assertNull( $plugin_a['added_automatically'], "plugin-a should have no dynamic reason" );
		$this->assertEquals( 'wporg', $plugin_a['from'], "plugin-a should be from wporg" );

		// Verify plugin-b is an array and dynamic
		$plugin_b = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'plugin-b'
		);
		$plugin_b = reset( $plugin_b );
		$this->assertIsArray( $plugin_b, "plugin-b is not an array" );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'], "plugin-b slug incorrect" );
		$this->assertEquals( 'Added via extension set', $plugin_b['added_automatically'], "plugin-b dynamic reason incorrect" );
		$this->assertEquals( 'wporg', $plugin_b['from'], "plugin-b should be from wporg" );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_non_existent_extension_set(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[
							'slug'   => 'woocommerce',
							'source' => [ 'from' => 'wporg' ],
						],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_non_existent_extension_set with args: " . json_encode( [ '--extension_set' => 'non-existent-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'non-existent-set' ] );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 1, $plugin_slugs, "Expected one plugin: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'woocommerce', $plugin_slugs, "woocommerce not found: " . json_encode( $plugin_slugs ) );

		// Verify woocommerce is an array and not dynamic
		$woocommerce = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'woocommerce'
		);
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce, "woocommerce is not an array" );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'], "woocommerce slug incorrect" );
		$this->assertNull( $woocommerce['added_automatically'], "woocommerce should have no dynamic reason" );
		$this->assertEquals( 'wporg', $woocommerce['from'], "woocommerce should be from wporg" );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_local_plugin_b_with_dependency(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins'       => [
						[
							'slug'   => 'plugin-b',
							'source' => [ 'from' => 'local', 'path' => '/path/to/plugin-b' ],
						],
					],
					'extension_set' => 'test-set', // Includes plugin-a, plugin-b
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_local_plugin_b_with_dependency with args: " . json_encode( [ '--extension_set' => 'test-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'test-set' ] );

		$plugin_slugs = array_map( fn( $plugin ) => $plugin['slug'], $env_info['plugins'] );

		// Verify plugins
		$this->assertCount( 2, $plugin_slugs, "Expected two plugins: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-b', $plugin_slugs, "plugin-b not found: " . json_encode( $plugin_slugs ) );
		$this->assertContains( 'plugin-a', $plugin_slugs, "plugin-a not found: " . json_encode( $plugin_slugs ) );

		// Verify plugin-b is an array with directory and not dynamic
		$plugin_b = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'plugin-b'
		);
		$plugin_b = reset( $plugin_b );
		$this->assertIsArray( $plugin_b, "plugin-b is not an array" );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'], "plugin-b slug incorrect" );
		$this->assertEquals( '/path/to/plugin-b', $plugin_b['directory'], "plugin-b directory property not preserved" );
		$this->assertNull( $plugin_b['added_automatically'], "plugin-b should have no dynamic reason" );
		$this->assertEquals( 'local', $plugin_b['from'], "plugin-b should be from local" );

		// Verify plugin-a is an array and dynamic
		$plugin_a = array_filter(
			$env_info['plugins'],
			fn( $plugin ) => is_array( $plugin ) && $plugin['slug'] === 'plugin-a'
		);
		$plugin_a = reset( $plugin_a );
		$this->assertIsArray( $plugin_a, "plugin-a is not an array" );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'], "plugin-a slug incorrect" );
		$this->assertEquals( 'Added via extension set', $plugin_a['added_automatically'], "plugin-a dynamic reason incorrect" );
		$this->assertEquals( 'wporg', $plugin_a['from'], "plugin-a should be from wporg" );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}