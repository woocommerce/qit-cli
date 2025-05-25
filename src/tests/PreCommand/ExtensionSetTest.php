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

		// Mock WooCommerce.com get-dependencies endpoint (like PluginDependenciesTest)
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

		$plugin_slugs = array_map( fn( $plugin ) => is_object( $plugin ) ? $plugin->slug : $plugin, $env_info['plugins'] );

		$this->assertCount( 1, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
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

		$plugin_slugs = array_map( fn( $plugin ) => is_object( $plugin ) ? $plugin->slug : $plugin, $env_info['plugins'] );

		$this->assertCount( 1, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
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

		$plugin_slugs = array_map( fn( $plugin ) => is_object( $plugin ) ? $plugin->slug : $plugin, $env_info['plugins'] );

		$this->assertCount( 3, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
		$this->assertContains( 'plugin-a', $plugin_slugs );
		$this->assertContains( 'plugin-b', $plugin_slugs );
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

		$plugin_slugs = array_map( fn( $plugin ) => is_object( $plugin ) ? $plugin->slug : $plugin, $env_info['plugins'] );

		$this->assertCount( 2, $plugin_slugs );
		$this->assertContains( 'plugin-a', $plugin_slugs );
		$this->assertContains( 'plugin-b', $plugin_slugs );
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

		$plugin_slugs = array_map( fn( $plugin ) => is_object( $plugin ) ? $plugin->slug : $plugin, $env_info['plugins'] );

		$this->assertCount( 1, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}