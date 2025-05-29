<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class SutConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// Create minimal WooCommerce ZIP content
		$woo_zip_content = $this->createMinimalPluginZip( 'woocommerce', '8.0.0' );

		// Mock WooCommerce API response and ZIP download
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $woo_zip_content );

		// Mock empty WooCommerce.com response to prevent unmocked requests
		$this->mockWooComDownloadUrls( [] );
	}

	// Helper method to log mock status
	protected function logMockStatus( string $test_name, string $mock_key ): void {
		$mock_value = \QIT_CLI\App::getVar( $mock_key, null );
		file_put_contents( '/tmp/qit/qit_debug.log', "$test_name: Mock set for $mock_key: " . ( is_string( $mock_value ) ? "set (length: " . strlen( $mock_value ) . ")" : "not set" ) . "\n", FILE_APPEND );
	}

	// Example for test_sut_source_build
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
				'type'        => 'plugin',
				'slug'        => 'local-plugin-1',
				'source_type' => 'build',
				'command'     => 'npm run build',
				'output'      => $path,
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'local-plugin-1',
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
		$this->assertEquals( 'local-plugin-1', $env_info['extra']['sut']['slug'] );
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

		$mock_zip_content = $this->createMinimalPluginZip( 'wccom-plugin-2', '1.0.0' );

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wccom-plugin-2',
				'source_type' => 'url',
				'url'         => 'https://example.com/wccom-plugin-2.zip',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'wccom-plugin-2',
							'source_type' => 'url',
							'url'         => 'https://example.com/wccom-plugin-2.zip',
						],
					],
				],
			],
		];

		$this->mockDownloadUrl( 'https://example.com/wccom-plugin-2.zip', $mock_zip_content );
		$this->logMockStatus( 'test_sut_source_url', 'mock_https://example.com/wccom-plugin-2.zip' );

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'wccom-plugin-2', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'url', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'https://example.com/wccom-plugin-2.zip', $env_info['extra']['sut']['url'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
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

		// Set up standard extension mocks
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wporg-plugin-1',
				'source_type' => 'wporg',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'wporg-plugin-1',
							'source_type' => 'wporg',
							'version'     => 'stable',
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'wporg-plugin-1', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'wporg', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'stable', $env_info['extra']['sut']['version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_source_wccom(): void {
		$temp_dir = $this->temp_dir;

		// Set up standard extension mocks
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wccom-plugin-1',
				'source_type' => 'wccom',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'wccom-plugin-1',
							'source_type' => 'wccom',
							'version'     => 'stable',
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'wccom-plugin-1', $env_info['extra']['sut']['slug'] );
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

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'SUT must contain a "source_type" key', $result['output'], 'Expected error message not found in: ' . $result['output'] );
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

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'Invalid SUT type \'invalid\'', $result['output'], 'Expected error message not found in: ' . $result['output'] );
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

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'SUT must contain a non-empty "slug" string', $result['output'], 'Expected error message not found in: ' . $result['output'] );
	}

	public function test_sut_missing(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'SUT configuration is required', $result['output'], 'Expected error message not found in: ' . $result['output'] );
	}

	public function test_sut_invalid_source_type(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'invalid_source',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'Invalid source_type', $result['output'], 'Expected error message not found in: ' . $result['output'] );
	}

	public function test_sut_missing_required_fields(): void {
		$temp_dir = $this->temp_dir;

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'awesome-plugin',
				'source_type' => 'build', // Missing 'command' and 'output'
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'Build source must have a non-empty "command" string', $result['output'], 'Expected error message not found in: ' . $result['output'] );
	}

	public function test_sut_type_theme(): void {
		$temp_dir = $this->temp_dir;

		// Create minimal ZIP content for wccom-theme-1
		$theme_zip_content = $this->createMinimalPluginZip( 'wccom-theme-1', '1.0.0' ); // Reusing plugin ZIP for simplicity
		$this->mockStandardExtensions(); // Assuming it includes wccom-theme-1

		$config = [
			'sut'          => [
				'type'        => 'theme',
				'slug'        => 'wccom-theme-1',
				'source_type' => 'wccom',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'themes'  => [
						[
							'slug'        => 'wccom-theme-1',
							'source_type' => 'wccom',
							'version'     => 'stable',
						],
					],
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'theme', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'wccom-theme-1', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( 'wccom', $env_info['extra']['sut']['source_type'] );
		$this->assertEquals( 'stable', $env_info['extra']['sut']['version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_missing_from_environment(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wccom-plugin-1',
				'source_type' => 'wccom',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ], // SUT not included
				],
			],
		];

		$this->mockStandardExtensions();

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
		$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'wccom-plugin-1', $env_info['extra']['sut']['slug'] );
		$this->assertContains( 'wccom-plugin-1', array_map( fn( $p ) => $p['slug'], $env_info['plugins'] ), 'SUT should be added to environment plugins' );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_sut_invalid_version_wccom(): void {
		$temp_dir = $this->temp_dir;

		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wccom-plugin-1',
				'source_type' => 'wccom',
				'version'     => 'non-existent-version',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'wccom-plugin-1',
							'source_type' => 'wccom',
							'version'     => 'non-existent-version',
						],
					],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'Invalid version', $result['output'], 'Expected error message not found in: ' . $result['output'] );
	}

	public function test_duplicate_slugs_in_environment(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wccom-plugin-1',
				'source_type' => 'wccom',
				'version'     => 'stable',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'wccom-plugin-1',
							'source_type' => 'wccom',
							'version'     => 'stable',
						],
						[
							'slug'        => 'wccom-plugin-1', // Duplicate
							'source_type' => 'wccom',
							'version'     => 'stable',
						],
					],
				],
			],
		];

		$this->mockStandardExtensions();

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'], 'Expected command to fail' );
		$this->assertStringContainsString( 'Duplicate slug', $result['output'], 'Expected error message not found in: ' . $result['output'] );
	}

	public function test_sut_theme_source_directory(): void {
		$temp_dir = $this->temp_dir;
		$path     = "$temp_dir/theme-folder";
		mkdir( $path, 0777, true );
		file_put_contents( "$path/style.css", "/*\nTheme Name: Awesome Theme\n*/" );

		$config = [
			'sut'          => [
				'type'        => 'theme',
				'slug'        => 'awesome-theme',
				'source_type' => 'directory',
				'path'        => $path,
			],
			'environments' => [
				'default' => [
					'themes' => [
						'storefront',
						[
							'slug'        => 'awesome-theme',
							'source_type' => 'directory',
							'path'        => $path,
						],
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		// Assertions similar to plugin tests
	}

	public function test_sut_directory_nonexistent_path(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'test-plugin',
				'source_type' => 'directory',
				'path'        => '/nonexistent/path',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString(
			'Directory does not exist',
			$result['output']
		);
	}

	public function test_sut_wccom_invalid_version(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'wccom-plugin',
				'source_type' => 'wccom',
				'version'     => 'invalid_version',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString(
			'Invalid version specified',
			$result['output']
		);
	}

	public function test_sut_url_download_failure(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'test-plugin',
				'source_type' => 'url',
				'url'         => 'https://example.com/missing.zip',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		// Don't mock this URL to simulate failure
		$result = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString(
			'Failed to download',
			$result['output']
		);
	}

	public function test_sut_invalid_zip_file(): void {
		$temp_dir = $this->temp_dir;
		$path     = "$temp_dir/invalid.zip";
		file_put_contents( $path, "This is not a valid ZIP file" );

		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'test-plugin',
				'source_type' => 'zip',
				'path'        => $path,
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString(
			'Invalid ZIP file',
			$result['output']
		);
	}

	public function test_sut_mismatched_environment_config(): void {
		$config = [
			'sut'          => [
				'type'        => 'plugin',
				'slug'        => 'test-plugin',
				'source_type' => 'directory',
				'path'        => '/valid/path',
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[
							'slug'        => 'test-plugin',
							'source_type' => 'zip', // Mismatch!
							'path'        => '/different/path.zip',
						],
					],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString(
			'SUT configuration mismatch between main config and environment',
			$result['output']
		);
	}
}