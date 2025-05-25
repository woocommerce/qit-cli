<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Exceptions\NetworkErrorException;
use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class PluginDependenciesTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// Mock the get-dependencies endpoint (default response)
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'bar-extension', 'baz-extension' ],
				'themes'         => [ 'qit-beaver' ],
				'php_extensions' => [ 'gd' ],
			] )
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		// Clear API mock for isolation
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), null );
	}

	public function test_get_dependencies_cache_hit(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		// Run command once to populate cache
		$this->run_unit_test( $config );

		// Run again to hit cache
		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertNotEmpty( $plugins, "No plugins found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'bar-extension', $plugins ), "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'baz-extension', $plugins ), "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'qit-beaver', $env_info['themes'], true ), "qit-beaver not found: " . json_encode( $env_info['themes'] ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
	}

	public function test_get_dependencies_api_fetch(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		// Run command to trigger API fetch
		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertNotEmpty( $plugins, "No plugins found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'bar-extension', $plugins ), "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'baz-extension', $plugins ), "baz-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'qit-beaver', $env_info['themes'], true ), "qit-beaver not found: " . json_encode( $env_info['themes'] ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
	}

	public function test_get_dependencies_none_mode(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
				],
			],
		];

		// Pass dependencies_mode as CLI arg
		$env_info = $this->run_unit_test( $config, [ '--dependencies_mode' => 'none' ] );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertCount( 1, $plugins, "Expected only one plugin: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'foo-extension', $plugins ), "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertEmpty( $env_info['themes'], "Themes not empty: " . json_encode( $env_info['themes'] ) );
		$this->assertEmpty( $env_info['php_extensions'], "php_extensions not empty: " . json_encode( $env_info['php_extensions'] ) );
	}

	public function test_integration_plugin_and_theme_dependencies(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'foo-extension' ],
					'themes'  => [ 'twentytwentyone' ],
				],
			],
		];

		// Mock response
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'bar-extension' ],
				'themes'         => [ 'qit-beaver' ],
				'php_extensions' => [ 'gd' ],
			] )
		);

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertTrue( in_array( 'foo-extension', $plugins ), "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'bar-extension', $plugins ), "bar-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'twentytwentyone', $env_info['themes'], true ), "twentytwentyone not found: " . json_encode( $env_info['themes'] ) );
		$this->assertTrue( in_array( 'qit-beaver', $env_info['themes'], true ), "qit-beaver not found: " . json_encode( $env_info['themes'] ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_integration_with_bootstrap(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins'   => [ 'foo-extension' ],
					'bootstrap' => [
						[
							'slug'         => 'qit-beaver',
							'test_package' => 'helpers:default',
						],
					],
				],
			],
		];

		// Mock response
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'bar-extension' ],
				'themes'         => [ 'qit-beaver' ],
				'php_extensions' => [ 'gd' ],
			] )
		);

		$env_info = $this->run_unit_test( $config );

		$plugins = array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] );
		$this->assertTrue( in_array( 'foo-extension', $plugins ), "foo-extension not found: " . json_encode( $plugins ) );
		$this->assertTrue( in_array( 'bar-extension', $plugins ), "bar-extension not found: " . json_encode( $plugins ) );
		// Expect qit-beaver as a theme, not a plugin, since it's listed as a theme in sync.json
		$this->assertTrue( in_array( 'qit-beaver', $env_info['themes'], true ), "qit-beaver not found: " . json_encode( $env_info['themes'] ) );
		$this->assertEquals( [ 'gd' ], $env_info['php_extensions'], "php_extensions mismatch: " . json_encode( $env_info['php_extensions'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}