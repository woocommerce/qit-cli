<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class ExtensionSetTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		file_put_contents( '/tmp/qit/qit_debug.log', "Starting ExtensionSetTest::setUp\n", FILE_APPEND );
		$this->mockWooComDependencies( [ 'woocommerce', 'plugin-a', 'plugin-b' ], [], [] );
		$this->mockWooComDownloadUrls( [
			'woocommerce' => 'https://qit.woo.com/downloads/woocommerce.zip',
			'plugin-a'    => 'https://qit.woo.com/downloads/plugin-a.zip',
			'plugin-b'    => 'https://qit.woo.com/downloads/plugin-b.zip',
		] );
		foreach ( [ 'woocommerce', 'plugin-a', 'plugin-b' ] as $slug ) {
			$this->mockWpOrgPlugin( $slug, $slug === 'woocommerce' ? '8.0.0' : '1.0.0', "https://downloads.wordpress.org/plugin/{$slug}.zip" );
		}
	}

	public function test_no_extension_set(): void {
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
						[ 'slug' => 'woocommerce', 'from' => 'wporg' ],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 1, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );
		$this->assertNull( $woocommerce['added_automatically'] );
		$this->assertEquals( 'wporg', $woocommerce['from'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_empty_extension_set(): void {
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
						[ 'slug' => 'woocommerce', 'from' => 'wporg' ],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_empty_extension_set with args: " . json_encode( [ '--extension_set' => 'empty-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'empty-set' ] );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 1, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );
		$this->assertNull( $woocommerce['added_automatically'] );
		$this->assertEquals( 'wporg', $woocommerce['from'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_valid_extension_set(): void {
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
						[ 'slug' => 'woocommerce', 'from' => 'wporg' ],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_valid_extension_set with args: " . json_encode( [ '--extension_set' => 'test-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'test-set' ] );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 3, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );
		$this->assertContains( 'plugin-a', $plugin_slugs );
		$this->assertContains( 'plugin-b', $plugin_slugs );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );
		$this->assertNull( $woocommerce['added_automatically'] );
		$this->assertEquals( 'wporg', $woocommerce['from'] );

		$plugin_a = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-a' );
		$plugin_a = reset( $plugin_a );
		$this->assertIsArray( $plugin_a );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'] );
		$this->assertEquals( 'Added via extension set', $plugin_a['added_automatically'] );
		$this->assertEquals( 'wporg', $plugin_a['from'] );

		$plugin_b = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-b' );
		$plugin_b = reset( $plugin_b );
		$this->assertIsArray( $plugin_b );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'] );
		$this->assertEquals( 'Added via extension set', $plugin_b['added_automatically'] );
		$this->assertEquals( 'wporg', $plugin_b['from'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extension_set_with_duplicates(): void {
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
						[ 'slug' => 'plugin-a', 'from' => 'wporg' ],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_extension_set_with_duplicates with args: " . json_encode( [ '--extension_set' => 'test-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'test-set' ] );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 2, $plugin_slugs );
		$this->assertContains( 'plugin-a', $plugin_slugs );
		$this->assertContains( 'plugin-b', $plugin_slugs );

		$plugin_a = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-a' );
		$plugin_a = reset( $plugin_a );
		$this->assertIsArray( $plugin_a );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'] );
		$this->assertNull( $plugin_a['added_automatically'] );
		$this->assertEquals( 'wporg', $plugin_a['from'] );

		$plugin_b = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-b' );
		$plugin_b = reset( $plugin_b );
		$this->assertIsArray( $plugin_b );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'] );
		$this->assertEquals( 'Added via extension set', $plugin_b['added_automatically'] );
		$this->assertEquals( 'wporg', $plugin_b['from'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_non_existent_extension_set(): void {
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
						[ 'slug' => 'woocommerce', 'from' => 'wporg' ],
					],
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_non_existent_extension_set with args: " . json_encode( [ '--extension_set' => 'non-existent-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'non-existent-set' ] );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 1, $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );
		$this->assertNull( $woocommerce['added_automatically'] );
		$this->assertEquals( 'wporg', $woocommerce['from'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_local_plugin_b_with_dependency(): void {
		$local_path = $this->temp_dir . '/plugin-b';
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
					'plugins'       => [
						[ 'slug' => 'plugin-b', 'from' => 'local', 'path' => $local_path ],
					],
					'extension_set' => 'test-set',
				],
			],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "Starting test_local_plugin_b_with_dependency with args: " . json_encode( [ '--extension_set' => 'test-set' ] ) . "\n", FILE_APPEND );

		$env_info = $this->run_unit_test( $config, [ '--extension_set' => 'test-set' ] );

		$plugin_slugs = array_map( fn( $p ) => $p['slug'], $env_info['plugins'] );
		$this->assertCount( 3, $plugin_slugs );
		$this->assertContains( 'plugin-b', $plugin_slugs );
		$this->assertContains( 'plugin-a', $plugin_slugs );
		$this->assertContains( 'woocommerce', $plugin_slugs );

		$plugin_b = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-b' );
		$plugin_b = reset( $plugin_b );
		$this->assertIsArray( $plugin_b );
		$this->assertEquals( 'plugin-b', $plugin_b['slug'] );
		$this->assertStringContainsString( '/tmp-normalized', $plugin_b['directory'] );
		$this->assertNull( $plugin_b['added_automatically'] );
		$this->assertEquals( 'local', $plugin_b['from'] );

		$plugin_a = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'plugin-a' );
		$plugin_a = reset( $plugin_a );
		$this->assertIsArray( $plugin_a );
		$this->assertEquals( 'plugin-a', $plugin_a['slug'] );
		$this->assertEquals( 'Added via extension set', $plugin_a['added_automatically'] );
		$this->assertEquals( 'wporg', $plugin_a['from'] );

		$woocommerce = array_filter( $env_info['plugins'], fn( $p ) => $p['slug'] === 'woocommerce' );
		$woocommerce = reset( $woocommerce );
		$this->assertIsArray( $woocommerce );
		$this->assertEquals( 'woocommerce', $woocommerce['slug'] );
		$this->assertEquals( 'Added via extension set', $woocommerce['added_automatically'] );
		$this->assertEquals( 'wporg', $woocommerce['from'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}