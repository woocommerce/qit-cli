<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;

class ConfigParseTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function test_basic_config_no_extends(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
						[ 'slug' => 'local-plugin-1', 'source' => [ 'type' => 'build', 'command' => 'npm run build', 'output' => "$temp_dir/plugin.zip" ] ],
					],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
					'woo_version' => '6.0.0',
				],
			],
		];

		$plugin_zip = $this->createMinimalPluginZip( 'local-plugin-1', '1.0.0' );
		file_put_contents( "$temp_dir/plugin.zip", $plugin_zip );

		try {
			$env_info = $this->run_unit_test( $config );
			$this->assertArrayHasKey( 'extra', $env_info, 'env_info is missing the extra key' );
			$this->assertArrayHasKey( 'sut', $env_info['extra'], 'env_info.extra is missing the sut key' );
			$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'], 'SUT type mismatch' );
			$this->assertEquals( 'local-plugin-1', $env_info['extra']['sut']['slug'], 'SUT slug mismatch' );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParseTest: Exception in test_basic_config_no_extends: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	public function test_extend_config(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		$base_config = [
			'environments' => [
				'default' => [
					'plugins'     => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
					],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
					'woo_version' => '6.0.0',
				],
			],
		];
		file_put_contents( "$temp_dir/base.json", json_encode( $base_config, JSON_PRETTY_PRINT ) );

		$child_config = [
			'extends'      => 'base.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
						[ 'slug' => 'local-plugin-1', 'source' => [ 'type' => 'build', 'command' => 'npm run build', 'output' => "$temp_dir/plugin.zip" ] ],
					],
					'php_version' => '8.2',
				],
			],
		];

		$plugin_zip = $this->createMinimalPluginZip( 'local-plugin-1', '1.0.0' );
		file_put_contents( "$temp_dir/plugin.zip", $plugin_zip );

		$env_info = $this->run_unit_test( $child_config );
		$this->assertArrayHasKey( 'extra', $env_info );
		$this->assertArrayHasKey( 'sut', $env_info['extra'] );
		$this->assertEquals( 'plugin', $env_info['extra']['sut']['type'] );
		$this->assertEquals( 'local-plugin-1', $env_info['extra']['sut']['slug'] );
		$this->assertEquals( '8.2', $env_info['php_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extend_missing_base_file(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		$config = [
			'extends' => 'nonexistent.json',
			'sut'     => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Base config file 'nonexistent.json' not found", $result['output'] );
	}

	public function test_extend_missing_sut_in_child(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		$base_config = [
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
					],
				],
			],
		];
		file_put_contents( "$temp_dir/base.json", json_encode( $base_config, JSON_PRETTY_PRINT ) );

		$child_config = [
			'extends'      => 'base.json',
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
					],
				],
			],
		];

		$result = $this->run_unit_test( $child_config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( 'SUT configuration is required', $result['output'] );
	}

	public function test_extend_invalid_json(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		file_put_contents( "$temp_dir/base.json", '{invalid json' );

		$config = [
			'extends' => 'base.json',
			'sut'     => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( 'Invalid qit.json format', $result['output'] );
	}

	public function test_extend_circular_dependency(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		$config = [
			'extends' => 'self.json',
			'sut'     => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
		];
		file_put_contents( "$temp_dir/self.json", json_encode( $config, JSON_PRETTY_PRINT ) );

		$result = $this->run_unit_test( $config, [], true );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( 'Circular dependency detected in qit.json configuration', $result['output'] );
	}

	public function test_extend_test_packages_merge(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		// Create test package JSON files
		$woocommerce_package = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'WooCommerce Team',
			'description'  => 'WooCommerce default test package',
			'test_command' => 'npm run playwright --project woocommerce',
			'test_results' => [
				'ctrf' => './results/woocommerce/ctrf.json',
			],
		];

		$local_package = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Plugin Team',
			'description'  => 'Local plugin test package',
			'test_command' => 'npm run playwright --project local',
			'test_results' => [
				'ctrf' => './results/ctrf.json',
			],
		];

		// Create directories for test packages
		$test_dir = $temp_dir . '/tests/e2e';
		if (!is_dir($test_dir)) {
			mkdir($test_dir, 0777, true);
		}

		file_put_contents("$test_dir/woocommerce.json", json_encode($woocommerce_package, JSON_PRETTY_PRINT));
		file_put_contents("$test_dir/local.json", json_encode($local_package, JSON_PRETTY_PRINT));

		$base_config = [
			'environments'  => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'woocommerce',
					'file' => "tests/e2e/woocommerce.json",
				],
			],
		];
		file_put_contents( "$temp_dir/base.json", json_encode( $base_config, JSON_PRETTY_PRINT ) );

		$child_config = [
			'extends'       => 'base.json',
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
						[ 'slug' => 'local-plugin-1', 'source' => [ 'type' => 'build', 'command' => 'npm run build', 'output' => "$temp_dir/plugin.zip" ] ],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'local',
					'file' => "tests/e2e/local.json",
				],
			],
		];

		$plugin_zip = $this->createMinimalPluginZip( 'local-plugin-1', '1.0.0' );
		file_put_contents( "$temp_dir/plugin.zip", $plugin_zip );

		$env_info = $this->run_unit_test( $child_config );
		$this->assertArrayHasKey( 'extra', $env_info );
		$this->assertArrayHasKey( 'sut', $env_info['extra'] );
		$this->assertEquals( 'local-plugin-1', $env_info['extra']['sut']['slug'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extend_with_url_base(): void {
		$temp_dir = $this->temp_dir;
		$this->mockStandardExtensions();

		$base_config = [
			'environments' => [
				'default' => [
					'plugins'     => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
					],
					'wp_version'  => '6.0',
					'php_version' => '7.4',
				],
			],
		];
		$base_file   = "$temp_dir/base.json";
		file_put_contents( $base_file, json_encode( $base_config, JSON_PRETTY_PRINT ) );

		// Mock RequestBuilder response
		App::setVar( 'mock_https://example.com/base.json', file_get_contents( $base_file ) );

		$child_config = [
			'extends'      => 'https://example.com/base.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => "$temp_dir/plugin.zip",
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [
						[ 'slug' => 'woocommerce', 'source' => [ 'type' => 'wccom' ] ],
						[ 'slug' => 'local-plugin-1', 'source' => [ 'type' => 'build', 'command' => 'npm run build', 'output' => "$temp_dir/plugin.zip" ] ],
					],
					'php_version' => '8.2',
				],
			],
		];

		$plugin_zip = $this->createMinimalPluginZip( 'local-plugin-1', '1.0.0' );
		file_put_contents( "$temp_dir/plugin.zip", $plugin_zip );

		try {
			$env_info = $this->run_unit_test( $child_config );
			$this->assertArrayHasKey( 'extra', $env_info );
			$this->assertArrayHasKey( 'sut', $env_info['extra'] );
			$this->assertEquals( 'local-plugin-1', $env_info['extra']['sut']['slug'] );
			$this->assertEquals( '8.2', $env_info['php_version'] );
			$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParseTest: Exception in test_extend_with_url_base: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}
}
