<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class SutConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// Create minimal WooCommerce ZIP content
		$woo_zip_content = $this->createMinimalPluginZip( 'woocommerce', '8.0.0' );

		// Mock WooCommerce API response and ZIP download
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $woo_zip_content );

		// Mock Storefront theme for tests requiring themes
		$storefront_zip_content = $this->createMinimalThemeZip( 'storefront', '4.5.0' );
		$this->mockWpOrgTheme( 'storefront', '4.5.0', 'https://downloads.wordpress.org/theme/storefront.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/theme/storefront.zip', $storefront_zip_content );

		// Mock empty WooCommerce.com response to prevent unmocked requests
		$this->mockWooComDownloadUrls( [] );
	}

	// Helper method to log mock status
	protected function logMockStatus( string $test_name, string $mock_key ): void {
		$mock_value = \QIT_CLI\App::getVar( $mock_key, null );
		file_put_contents( '/tmp/qit/qit_debug.log', "$test_name: Mock set for $mock_key: " . ( is_string( $mock_value ) ? "set (length: " . strlen( $mock_value ) . ")" : "not set" ) . "\n", FILE_APPEND );
	}

	// Helper method to create minimal theme ZIP
	protected function createMinimalThemeZip( string $slug, string $version ): string {
		$filename = "style.css";
		$content  = "/*\nTheme Name: " . ucwords( str_replace( '-', ' ', $slug ) ) . "\nVersion: {$version}\n*/";

		$zip  = new \ZipArchive();
		$temp = tempnam( sys_get_temp_dir(), 'zip' );
		if ( $temp === false ) {
			$this->fail( "Failed to create temporary file for ZIP" );
		}
		try {
			if ( ! $zip->open( $temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
				$this->fail( "Failed to create ZIP file at $temp" );
			}
			$zip->addFromString( "{$slug}/{$filename}", $content );
			$zip->close();

			$zipContent = file_get_contents( $temp );
			if ( $zipContent === false ) {
				$this->fail( "Failed to read ZIP content from $temp" );
			}

			return $zipContent;
		} finally {
			unlink( $temp );
		}
	}

	public function test_sut_source_build(): void {
		$temp_dir = $this->temp_dir;

		// Create minimal ZIP content for local-plugin-1
		$plugin_zip = $this->createMinimalPluginZip( 'local-plugin-1', '1.0.0' );
		$path       = "$temp_dir/plugin.zip";
		file_put_contents( $path, $plugin_zip );
		$this->to_delete[] = $path;
		file_put_contents( '/tmp/qit/qit_debug.log', "Created ZIP file for build test: $path\n", FILE_APPEND );

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => $path,
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'local-plugin-1',
							'source' => [
								'type'    => 'build',
								'command' => 'npm run build',
								'output'  => $path,
							],
						],
					],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'local-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'build', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( 'npm run build', $env_info['extra']['sut']['source']['command'] );
			$this->assertEquals( '/normalized/path/plugin.zip', $env_info['extra']['sut']['source']['output'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_build: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
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
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $path,
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'awesome-plugin',
							'source' => [
								'type' => 'directory',
								'path' => $path,
							],
						],
					],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'directory', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( '/normalized/path/plugin-folder', $env_info['extra']['sut']['source']['path'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_directory: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_source_url(): void {
		$temp_dir = $this->temp_dir;

		$mock_zip_content = $this->createMinimalPluginZip( 'wccom-plugin-2', '1.0.0' );

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wccom-plugin-2',
				'source' => [
					'type' => 'url',
					'url'  => 'https://example.com/wccom-plugin-2.zip',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'wccom-plugin-2',
							'source' => [
								'type' => 'url',
								'url'  => 'https://example.com/wccom-plugin-2.zip',
							],
						],
					],
				],
			],
		];

		$this->mockDownloadUrl( 'https://example.com/wccom-plugin-2.zip', $mock_zip_content );
		$this->logMockStatus( 'test_sut_source_url', 'mock_https://example.com/wccom-plugin-2.zip' );

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wccom-plugin-2', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'url', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( 'https://example.com/wccom-plugin-2.zip', $env_info['extra']['sut']['source']['url'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_url: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_source_zip(): void {
		$temp_dir = $this->temp_dir;

		// Create minimal ZIP content for awesome-plugin
		$plugin_zip = $this->createMinimalPluginZip( 'awesome-plugin', '1.0.0' );
		$path       = "$temp_dir/plugin.zip";
		file_put_contents( $path, $plugin_zip );
		$this->to_delete[] = $path;
		file_put_contents( '/tmp/qit/qit_debug.log', "Created ZIP file for zip test: $path\n", FILE_APPEND );

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'zip',
					'path' => $path,
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'awesome-plugin',
							'source' => [
								'type' => 'zip',
								'path' => $path,
							],
						],
					],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'awesome-plugin', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'zip', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( '/normalized/path/plugin.zip', $env_info['extra']['sut']['source']['path'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_zip: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_source_wporg(): void {
		$temp_dir = $this->temp_dir;

		// Set up standard extension mocks
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wporg-plugin-1',
				'source' => [
					'type'    => 'wporg',
					'version' => 'stable',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'wporg-plugin-1',
							'source' => [
								'type'    => 'wporg',
								'version' => 'stable',
							],
						],
					],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wporg-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'wporg', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( 'stable', $env_info['extra']['sut']['source']['version'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_wporg: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_source_wccom(): void {
		$temp_dir = $this->temp_dir;

		// Set up standard extension mocks
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wccom-plugin-1',
				'source' => [
					'type'    => 'wccom',
					'version' => 'stable',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'wccom-plugin-1',
							'source' => [
								'type'    => 'wccom',
								'version' => 'stable',
							],
						],
					],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wccom-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'wccom', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( 'stable', $env_info['extra']['sut']['source']['version'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_source_wccom: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_only_slug_wporg(): void {
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type' => 'plugin',
				'slug' => 'wporg-plugin-1',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'wporg-plugin-1' ],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wporg-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'wporg', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( 'stable', $env_info['extra']['sut']['source']['version'] );
			$this->assertContains( 'wporg-plugin-1', array_map( fn( $p ) => $p['slug'], $env_info['plugins'] ) );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_only_slug_wporg: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_only_slug_wccom(): void {
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type' => 'plugin',
				'slug' => 'wccom-plugin-1',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'wccom-plugin-1' ],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wccom-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'wccom', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( 'stable', $env_info['extra']['sut']['source']['version'] );
			$this->assertContains( 'wccom-plugin-1', array_map( fn( $p ) => $p['slug'], $env_info['plugins'] ) );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_only_slug_wccom: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_only_slug_not_found(): void {
		$config = [
			'sut'          => [
				'type' => 'plugin',
				'slug' => 'non-existent-plugin',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString(
				'SUT \'non-existent-plugin\' not found in WordPress.org or WooCommerce.com',
				$result['output'],
				'Expected error message not found in: ' . $result['output']
			);
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_only_slug_not_found: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			$this->assertStringContainsString(
				'SUT \'non-existent-plugin\' not found in WordPress.org or WooCommerce.com',
				$e->getMessage(),
				'Expected error message not found in exception: ' . $e->getMessage()
			);
		}
	}

	public function test_sut_invalid_type(): void {
		$config = [
			'sut'          => [
				'type'   => 'invalid',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( 'Invalid SUT type \'invalid\'', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_invalid_type: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_empty_slug(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => '',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( 'SUT must contain a non-empty "slug" string', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_empty_slug: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_missing(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( 'SUT configuration is required', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_missing: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_invalid_source_type(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'invalid_source',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( ' Invalid source type \'invalid_source\'', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_invalid_source_type: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_missing_required_fields(): void {
		$temp_dir = $this->temp_dir;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'build', // Missing 'command' and 'output'
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( 'Build source must contain a non-empty "command" string', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_missing_required_fields: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_type_theme(): void {
		$temp_dir = $this->temp_dir;

		$theme_zip_content = $this->createMinimalThemeZip( 'wccom-theme-1', '1.0.0' );
		$this->mockWooComDownloadUrls( [
			'urls' => [
				'wccom-theme-1' => [
					'slug'    => 'wccom-theme-1',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-theme-1.zip',
				],
			],
		] );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-theme-1.zip', $theme_zip_content );
		$this->logMockStatus( 'test_sut_type_theme', 'mock_https://qit.woo.com/downloads/wccom-theme-1.zip' );

		$config = [
			'sut'          => [
				'type'   => 'theme',
				'slug'   => 'wccom-theme-1',
				'source' => [
					'type'    => 'wccom',
					'version' => '1.0.0', // Match mocked version
				],
			],
			'environments' => [
				'default' => [
					'themes'  => [
						[
							'slug'   => 'storefront',
							'source' => [
								'type'    => 'wporg',
								'version' => '4.5.0',
							],
						],
						[
							'slug'   => 'wccom-theme-1',
							'source' => [
								'type'    => 'wccom',
								'version' => '1.0.0',
							],
						],
					],
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'theme', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wccom-theme-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'wccom', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( '1.0.0', $env_info['extra']['sut']['source']['version'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_type_theme: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_missing_from_environment(): void {
		$temp_dir = $this->temp_dir;

		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wccom-plugin-1',
				'source' => [
					'type'    => 'wccom',
					'version' => 'stable',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ], // SUT not included
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'wccom-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertContains( 'wccom-plugin-1', array_map( fn( $p ) => $p['slug'], $env_info['plugins'] ), 'SUT should be added to environment plugins' );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_missing_from_environment: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_invalid_version_wccom(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wccom-plugin-1',
				'source' => [
					'type'    => 'wccom',
					'version' => 'non-existent-version',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'wccom-plugin-1',
							'source' => [
								'type'    => 'wccom',
								'version' => 'non-existent-version',
							],
						],
					],
				],
			],
		];

		$this->mockWooComDownloadUrls( [] );
		$this->logMockStatus( 'test_sut_invalid_version_wccom', 'mock_' . get_manager_url() . '/wp-json/cd/v1/cli/download-urls' );

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString(
				'Download failed for \'wccom-plugin-1\'',
				$result['output'],
				'Expected error message not found in: ' . $result['output']
			);
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_invalid_version_wccom: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			$this->assertStringContainsString(
				'Download failed for \'wccom-plugin-1\'',
				$e->getMessage(),
				'Expected error message not found in exception: ' . $e->getMessage()
			);
		}
	}

	public function test_sut_wccom_invalid_version(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wccom-plugin-1',
				'source' => [
					'type'    => 'wccom',
					'version' => 'invalid_version',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$this->mockWooComDownloadUrls( [] );
		$this->logMockStatus( 'test_sut_wccom_invalid_version', 'mock_' . get_manager_url() . '/wp-json/cd/v1/cli/download-urls' );

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString(
				'Download failed for \'wccom-plugin-1\'',
				$result['output'],
				'Expected error message not found in: ' . $result['output']
			);
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_wccom_invalid_version: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			$this->assertStringContainsString(
				'Download failed for \'wccom-plugin-1\'',
				$e->getMessage(),
				'Expected error message not found in exception: ' . $e->getMessage()
			);
		}
	}

	public function test_duplicate_slugs_in_environment(): void {
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'wccom-plugin-1',
				'source' => [
					'type'    => 'wccom',
					'version' => 'stable',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'wccom-plugin-1',
							'source' => [
								'type'    => 'wccom',
								'version' => 'stable',
							],
						],
						[
							'slug'   => 'wccom-plugin-1',
							'source' => [
								'type'    => 'wccom',
								'version' => 'stable',
							],
						],
					],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString(
				'Error loading config: Duplicate slug \'wccom-plugin-1\' in plugins for environment \'default\'',
				$result['output'],
				'Expected error message not found in: ' . $result['output']
			);
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_duplicate_slugs_in_environment: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			$this->assertStringContainsString(
				'Error loading config: Duplicate slug \'wccom-plugin-1\' in plugins for environment \'default\'',
				$e->getMessage(),
				'Expected error message not found in exception: ' . $e->getMessage()
			);
		}
	}

	public function test_sut_theme_source_directory(): void {
		$temp_dir = $this->temp_dir;

		// Create directory with consistent name
		$path = "$temp_dir/theme-folder";
		mkdir( $path, 0777, true );
		file_put_contents( "$path/style.css", "/*\nTheme Name: Awesome Theme\n*/" );
		$this->to_delete[] = $path;

		$config = [
			'sut'          => [
				'type'   => 'theme',
				'slug'   => 'awesome-theme',
				'source' => [
					'type' => 'directory',
					'path' => $path,
				],
			],
			'environments' => [
				'default' => [
					'themes'  => [
						[
							'slug'   => 'storefront',
							'source' => [
								'type'    => 'wporg',
								'version' => '4.5.0',
							],
						],
						[
							'slug'   => 'awesome-theme',
							'source' => [
								'type' => 'directory',
								'path' => $path,
							],
						],
					],
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'theme', $env_info['extra']['sut']['type'] );
			$this->assertEquals( 'awesome-theme', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( 'directory', $env_info['extra']['sut']['source']['type'] );
			$this->assertEquals( '/normalized/path/theme-folder', $env_info['extra']['sut']['source']['path'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_theme_source_directory: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_directory_nonexistent_path(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'test-plugin',
				'source' => [
					'type' => 'directory',
					'path' => '/nonexistent/path',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( 'Directory does not exist: /nonexistent/path', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_directory_nonexistent_path: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_sut_url_download_failure(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'test-plugin',
				'source' => [
					'type' => 'url',
					'url'  => 'https://example.com/missing.zip',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		// Mock a failed download with a string
		$this->mockDownloadUrl( 'https://example.com/missing.zip', 'exception: Failed to download plugin from URL: https://example.com/missing.zip' );
		$this->logMockStatus( 'test_sut_url_download_failure', 'mock_https://example.com/missing.zip' );

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString(
				"Download failed for 'test-plugin': Failed to download plugin from URL: https://example.com/missing.zip",
				$result['output'],
				'Expected error message not found in: ' . $result['output']
			);
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_url_download_failure: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			$this->assertStringContainsString(
				"Download failed for 'test-plugin': Failed to download plugin from URL: https://example.com/missing.zip",
				$e->getMessage(),
				'Expected error message not found in exception: ' . $e->getMessage()
			);
		}
	}

	public function test_sut_invalid_zip_file(): void {
		$temp_dir = $this->temp_dir;
		$path     = "$temp_dir/invalid.zip";
		file_put_contents( $path, "This is not a valid ZIP file" );
		$this->to_delete[] = $path;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'test-plugin',
				'source' => [
					'type' => 'zip',
					'path' => $path,
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString(
				"Download failed for 'test-plugin': Invalid ZIP file: $path",
				$result['output'],
				'Expected error message not found in: ' . $result['output']
			);
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_invalid_zip_file: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			$this->assertStringContainsString(
				"Download failed for 'test-plugin': Invalid ZIP file: $path",
				$e->getMessage(),
				'Expected error message not found in exception: ' . $e->getMessage()
			);
		}
	}

	public function test_sut_mismatched_environment_config(): void {
		$temp_dir = $this->temp_dir;
		$path     = "$temp_dir/plugin-folder";
		mkdir( $path, 0777, true );
		file_put_contents( "$path/test-plugin.php", "<?php\n// Plugin Name: Test Plugin" );
		$this->to_delete[] = $path;

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'test-plugin',
				'source' => [
					'type' => 'directory',
					'path' => $path,
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'   => 'test-plugin',
							'source' => [
								'type' => 'zip', // Mismatch!
								'path' => '/different/path.zip',
							],
						],
					],
				],
			],
		];

		try {
			$result = $this->run_unit_test( $config, [], true );
			$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
			$this->assertStringContainsString( 'SUT configuration mismatch between main config and environment', $result['output'], 'Expected error message not found in: ' . $result['output'] );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "test_sut_mismatched_environment_config: Exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}
}