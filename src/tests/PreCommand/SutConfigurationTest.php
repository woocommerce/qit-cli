<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class SutConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
		// Mock empty WooCommerce.com response to prevent unmocked requests
		$this->mockWooComDownloadUrls( [] );
	}

	public function test_sut_source_build(): void {
		$temp_dir = $this->temp_dir;

		// Create ZIP with consistent name
		$path = "$temp_dir/plugin.zip";
		$zip  = new \ZipArchive();
		if ( ! $zip->open( $path, \ZipArchive::CREATE ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "Failed to create ZIP file at $path\n", FILE_APPEND );
			$this->fail( "Failed to create ZIP file at $path" );
		}
		$zip->addFromString( 'awesome-plugin/awesome-plugin.php', "<?php\n// Plugin Name: Awesome Plugin" );
		$zip->close();
		if ( ! file_exists( $path ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ZIP file not found after creation: $path\n", FILE_APPEND );
			$this->fail( "ZIP file not found after creation: $path" );
		}
		$this->to_delete[] = $path;
		file_put_contents( '/tmp/qit/qit_debug.log', "Created ZIP file for build test: $path\n", FILE_APPEND );

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'build',
				'command'     => 'npm run build',
				'output'      => $path,
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'awesome-plugin',
							'source_type' => 'build',
							'command'     => 'npm run build',
							'output'      => $path,
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'build', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'npm run build', $env_info['extra']['sut']['command'] );
		$this->assertEquals( '/normalized/path/plugin.zip', $env_info['extra']['sut']['output'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_source_directory(): void {
		$temp_dir = $this->temp_dir;

		// Create directory with consistent name
		$path = "$temp_dir/plugin-folder";
		mkdir( $path, 0777, true );
		file_put_contents( "$path/awesome-plugin.php", "<?php\n// Plugin Name: Awesome Plugin" );
		$this->to_delete[] = $path;

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'directory',
				'path'        => $path,
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'awesome-plugin',
							'source_type' => 'directory',
							'path'        => $path,
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'directory', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( '/normalized/path/plugin-folder', $env_info['extra']['sut']['path'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_source_url(): void {
		$temp_dir = $this->temp_dir;

		// Read the valid ZIP file contents for mocking
		$zip_path         = __DIR__ . '/../data/plugins/my-awesome-plugin.zip';
		$mock_zip_content = file_get_contents( $zip_path );
		if ( $mock_zip_content === false ) {
			$this->fail( "Failed to read ZIP file at $zip_path" );
		}

		// Log the ZIP content length for debugging
		file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_url: ZIP content length: " . strlen( $mock_zip_content ) . "\n", FILE_APPEND );

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'my-awesome-plugin',
				'source_type' => 'url',
				'url'         => 'https://example.com/plugin.zip',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'my-awesome-plugin',
							'source_type' => 'url',
							'url'         => 'https://example.com/plugin.zip',
						],
					],
				],
			],
		];

		// Mock the WordPress.org download URL for woocommerce
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $mock_zip_content );

		// Mock the URL download for my-awesome-plugin
		$this->mockDownloadUrl( 'https://example.com/plugin.zip', $mock_zip_content );

		// Log to confirm mocks are set
		$mock_urls = [
			'mock_https://downloads.wordpress.org/plugin/woocommerce.zip',
			'mock_https://example.com/plugin.zip',
		];
		foreach ( $mock_urls as $mock_key ) {
			$mock_value = \QIT_CLI\App::getVar( $mock_key, null );
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_url: Mock set for $mock_key: " . ( is_string( $mock_value ) ? "set (length: " . strlen( $mock_value ) . ")" : "not set" ) . "\n", FILE_APPEND );
		}

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'my-awesome-plugin', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'url', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'https://example.com/plugin.zip', $env_info['extra']['sut']['url'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_source_zip(): void {
		$temp_dir = $this->temp_dir;

		// Create ZIP with consistent name
		$path = "$temp_dir/plugin.zip";
		$zip  = new \ZipArchive();
		if ( ! $zip->open( $path, \ZipArchive::CREATE ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "Failed to create ZIP file at $path\n", FILE_APPEND );
			$this->fail( "Failed to create ZIP file at $path" );
		}
		$zip->addFromString( 'awesome-plugin/awesome-plugin.php', "<?php\n// Plugin Name: Awesome Plugin" );
		$zip->close();
		if ( ! file_exists( $path ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ZIP file not found after creation: $path\n", FILE_APPEND );
			$this->fail( "ZIP file not found after creation: $path" );
		}
		$this->to_delete[] = $path;
		file_put_contents( '/tmp/qit/qit_debug.log', "Created ZIP file for zip test: $path\n", FILE_APPEND );

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'zip',
				'path'        => $path,
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'awesome-plugin',
							'source_type' => 'zip',
							'path'        => $path,
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'zip', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( '/normalized/path/plugin.zip', $env_info['extra']['sut']['path'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_source_wporg(): void {
		$temp_dir = $this->temp_dir;

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'wporg',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'awesome-plugin',
							'source_type' => 'wporg',
							'version'     => 'stable',
						],
					],
				],
			],
		];

		$this->mockWpOrgPlugin( 'awesome-plugin', '1.0.0', 'https://downloads.wordpress.org/plugin/awesome-plugin.zip' );

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'wporg', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'stable', $env_info['extra']['sut']['version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_source_wccom(): void {
		$temp_dir = $this->temp_dir;

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'wccom',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'awesome-plugin',
							'source_type' => 'wccom',
							'version'     => 'stable',
						],
					],
				],
			],
		];

		$this->mockWooComDownloadUrls( [ 'awesome-plugin' => 'https://qit.woo.com/downloads/awesome-plugin.zip' ] );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/awesome-plugin.zip', 'mocked-zip-content' );

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'wccom', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'stable', $env_info['extra']['sut']['version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_missing_source_type(): void {
		$config = [
			'sut'          => [
				'type' => 'plugin',
				'slug' => 'awesome-plugin',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'SUT must contain a "source_type" key', $error );
	}

	public function test_sut_invalid_type(): void {
		$config = [
			'sut'          => [
				'type'        => 'invalid',
				'slug'        => 'awesome-plugin',
				'source_type' => 'directory',
				'path'        => './plugin-folder',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid SUT type \'invalid\'', $error );
	}

	public function test_sut_empty_slug(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => '',
				'source_type' => 'directory',
				'path'        => './plugin-folder',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'SUT must contain a non-empty "slug" string', $error );
	}
}