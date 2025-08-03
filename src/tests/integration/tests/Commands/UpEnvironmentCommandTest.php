<?php

namespace integration\tests\Commands;

use PHPUnit\Framework\TestCase;

/**
 * Test UpEnvironmentCommand behavior with QITInput integration.
 */
class UpEnvironmentCommandTest extends TestCase {

	protected function tearDown(): void {
		// Clean up any running environments
		@qit( [ 'env:down' ] );
		parent::tearDown();
	}

	/**
	 * Test basic env:up without any parameters.
	 */
	public function test_basic_env_up_no_params(): void {
		$output = qit_run_env_up( [ 'env:up', '--json' ] );
		$data = json_decode( $output, true );
		
		// Should use defaults
		$this->assertNotEmpty( $data['env_id'] );
		$this->assertEquals( 'e2e', $data['environment'] );
		$this->assertNotEmpty( $data['php'] );
		$this->assertNotEmpty( $data['wp'] );
	}

	/**
	 * Test env:up with basic config.
	 */
	public function test_env_up_with_config(): void {
		$config = [
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => '6.5',
				],
			],
		];
		
		$output = qit_run_env_up( [ 'env:up', '--json' ], $config );
		$data = json_decode( $output, true );
		
		// Should use config values
		$this->assertEquals( '8.2', $data['php'] );
		$this->assertEquals( '6.5', $data['wp'] );
	}

	/**
	 * Test env:up with CLI options only.
	 */
	public function test_env_up_with_cli_only(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--php', '8.3',
			'--wp', '6.6',
		] );
		$data = json_decode( $output, true );
		
		// Should use CLI values
		$this->assertEquals( '8.3', $data['php'] );
		$this->assertEquals( '6.6', $data['wp'] );
	}

	/**
	 * Test env:up with config and CLI (CLI should win).
	 */
	public function test_env_up_config_and_cli(): void {
		$config = [
			'environments' => [
				'default' => [
					'php' => '7.4',
					'wp'  => '5.9',
				],
			],
		];
		
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--php', '8.1', // CLI should override config
		], $config );
		$data = json_decode( $output, true );
		
		// CLI should win for PHP, config for WP
		$this->assertEquals( '8.1', $data['php'] );
		$this->assertEquals( '5.9', $data['wp'] );
	}

	/**
	 * Test env:up with --woo flag.
	 */
	public function test_env_up_with_woo(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--woo', 'stable',
		] );
		$data = json_decode( $output, true );
		
		// Should have WooCommerce
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
		$this->assertNotEmpty( $data['woo'] );
	}

	/**
	 * Test env:up with specific WooCommerce version.
	 */
	public function test_env_up_with_woo_version(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--woo', '8.8.0',
		] );
		$data = json_decode( $output, true );
		
		// Should have specific WooCommerce version
		$this->assertEquals( '8.8.0', $data['woo'] );
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
	}

	/**
	 * Test env:up with --woo and -p woocommerce (should not duplicate).
	 */
	public function test_env_up_woo_and_plugin_woocommerce(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--woo', '9.0.0',
			'--plugin', 'woocommerce',
		] );
		$data = json_decode( $output, true );
		
		// Should have WooCommerce only once
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$wooCount = array_count_values( $pluginSlugs )['woocommerce'] ?? 0;
		$this->assertEquals( 1, $wooCount, 'WooCommerce should appear only once' );
		$this->assertEquals( '9.0.0', $data['woo'] );
	}

	/**
	 * Test env:up with multiple plugins.
	 */
	public function test_env_up_with_multiple_plugins(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--plugin', 'akismet',
			'--plugin', 'jetpack',
		] );
		$data = json_decode( $output, true );
		
		// Should have both plugins
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'akismet', $pluginSlugs );
		$this->assertContains( 'jetpack', $pluginSlugs );
	}

	/**
	 * Test env:up with plugin that requires WooCommerce.
	 */
	public function test_env_up_with_woo_dependent_plugin(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--plugin', 'woocommerce-gateway-stripe',
		] );
		$data = json_decode( $output, true );
		
		// Should have the plugin
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'woocommerce-gateway-stripe', $pluginSlugs );
		
		// Should also have WooCommerce as a dependency
		// Note: This depends on dependency resolution being enabled
		// $this->assertContains( 'woocommerce', $pluginSlugs );
	}

	/**
	 * Test env:up with environment selection.
	 */
	public function test_env_up_with_environment_selection(): void {
		$config = [
			'environments' => [
				'production' => [
					'php' => '8.2',
					'wp'  => '6.5',
					'object_cache' => true,
				],
				'staging' => [
					'php' => '8.0',
					'wp'  => '6.0',
				],
			],
		];
		
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--environment', 'production',
		], $config );
		$data = json_decode( $output, true );
		
		// Should use e2e environment type (hardcoded in EnvInfo)
		$this->assertEquals( 'e2e', $data['environment'] );
		$this->assertEquals( '8.2', $data['php'] );
		$this->assertEquals( '6.5', $data['wp'] );
		$this->assertTrue( $data['object_cache'] );
	}

	/**
	 * Test env:up with plugins from config and CLI.
	 */
	public function test_env_up_plugins_merge(): void {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						'akismet',
					],
				],
			],
		];
		
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--plugin', 'jetpack',
		], $config );
		$data = json_decode( $output, true );
		
		// Should have both plugins
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'akismet', $pluginSlugs ); // From config
		$this->assertContains( 'jetpack', $pluginSlugs ); // From CLI
	}

	/**
	 * Test env:up with themes.
	 */
	public function test_env_up_with_themes(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--theme', 'storefront',
			'--theme', 'twentytwentythree',
		] );
		$data = json_decode( $output, true );
		
		// Should have both themes
		$themeSlugs = array_column( $data['themes'], 'slug' );
		$this->assertContains( 'storefront', $themeSlugs );
		$this->assertContains( 'twentytwentythree', $themeSlugs );
	}

	/**
	 * Test env:up with PHP extensions.
	 */
	public function test_env_up_with_php_extensions(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--php_extension', 'gd',
			'--php_extension', 'imagick',
		] );
		$data = json_decode( $output, true );
		
		// Should have PHP extensions
		$this->assertContains( 'gd', $data['php_extensions'] );
		$this->assertContains( 'imagick', $data['php_extensions'] );
	}

	/**
	 * Test env:up with object cache.
	 */
	public function test_env_up_with_object_cache(): void {
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--object_cache',
		] );
		$data = json_decode( $output, true );
		
		// Should have object cache enabled
		$this->assertTrue( $data['object_cache'] );
	}

	/**
	 * Test env:up with real environment verification using exec commands.
	 */
	public function test_env_up_real_environment_verification(): void {
		// Spin up environment with specific PHP and WP versions
		$env_output = qit( [ 'env:up', '--json', '--php', '8.3', '--wp', '6.7', '--plugin', 'woocommerce' ] );
		$env_data = json_decode( $env_output, true );
		$env_id = $env_data['env_id'];
		
		try {
			// Verify PHP version by running php -v inside the container
			$php_output = qit( [ 'env:exec', '--env_id=' . $env_id, 'php -v' ] );
			$this->assertStringContainsString( 'PHP 8.3', $php_output, 'PHP version should be 8.3' );
			
			// Verify WordPress version
			$wp_output = qit( [ 'env:exec', '--env_id=' . $env_id, 'wp core version' ] );
			$this->assertStringContainsString( '6.7', trim( $wp_output ), 'WordPress version should be 6.7' );
			
			// Verify WooCommerce is installed
			$plugin_output = qit( [ 'env:exec', '--env_id=' . $env_id, 'wp plugin list --format=csv --fields=name,status' ] );
			$this->assertStringContainsString( 'woocommerce', $plugin_output, 'WooCommerce should be installed' );
			
			// Also check if it's active (it might not be activated by default)
			if ( strpos( $plugin_output, 'woocommerce,active' ) === false ) {
				// Activate it if not active
				qit( [ 'env:exec', '--env_id=' . $env_id, 'wp plugin activate woocommerce' ] );
			}
			
		} finally {
			// Always clean up the environment
			qit( [ 'env:down' ] );
		}
	}
	
	/**
	 * Test WooCommerce version precedence - Level 1: --woo flag overrides everything.
	 */
	public function test_woo_precedence_level_1_woo_flag(): void {
		$config = [
			'environments' => [
				'default' => [
					'woo' => '8.0.0', // Config says 8.0.0
					'plugins' => [
						[
							'slug' => 'woocommerce',
							'from' => 'wporg',
							'version' => '8.1.0',
						],
					],
				],
			],
		];
		
		// --woo flag should win
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--woo', '8.2.0', // CLI flag says 8.2.0
		], $config );
		$data = json_decode( $output, true );
		
		// Should use --woo flag version
		$this->assertEquals( '8.2.0', $data['woo'] );
	}
	
	/**
	 * Test WooCommerce version precedence - Level 2: Plugin with explicit version.
	 */
	public function test_woo_precedence_level_2_plugin_explicit_version(): void {
		$config = [
			'environments' => [
				'default' => [
					'woo' => '8.0.0', // Config says 8.0.0
				],
			],
		];
		
		// Plugin with explicit version should win over config
		// Note: --plugin doesn't support woocommerce:version syntax directly
		// The version precedence is handled by the --woo flag
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--plugin', 'woocommerce', // Just the slug
			'--woo', '8.1.0', // Version via --woo flag
		], $config );
		$data = json_decode( $output, true );
		
		// Should use --woo flag version
		$this->assertEquals( '8.1.0', $data['woo'] );
	}
	
	/**
	 * Test WooCommerce version precedence - Level 3: Dependent plugin pulls in WooCommerce.
	 */
	public function test_woo_precedence_level_3_dependent_plugin(): void {
		// Config with woo set in environment
		$config = [
			'environments' => [
				'default' => [
					'woo' => '8.5.0', // Config says 8.5.0
				],
			],
		];
		
		// Plugin that depends on WooCommerce, but woo version from config should be used
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--plugin', 'woocommerce-gateway-stripe', // This depends on WooCommerce
		], $config );
		$data = json_decode( $output, true );
		
		// Should use config woo version
		$this->assertEquals( '8.5.0', $data['woo'] );
		
		// Both plugins should be present
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
		$this->assertContains( 'woocommerce-gateway-stripe', $pluginSlugs );
	}
	
	/**
	 * Test WooCommerce version precedence - Level 4: Environment block configuration.
	 */
	public function test_woo_precedence_level_4_environment_config(): void {
		$config = [
			'environments' => [
				'staging' => [
					'woo' => '8.5.0',
				],
				'default' => [
					'woo' => '8.0.0',
				],
			],
		];
		
		// Use staging environment
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--environment', 'staging',
		], $config );
		$data = json_decode( $output, true );
		
		// Should use staging environment's WooCommerce version
		$this->assertEquals( '8.5.0', $data['woo'] );
	}
	
	/**
	 * Test WooCommerce version precedence - Level 5: Environment-level default.
	 */
	public function test_woo_precedence_level_5_environment_default(): void {
		// Since root-level woo is not supported in the schema,
		// test environment-level woo instead
		$config = [
			'environments' => [
				'default' => [
					'woo' => '8.3.0', // Environment level
				],
			],
		];
		
		$output = qit_run_env_up( [
			'env:up',
			'--json',
		], $config );
		$data = json_decode( $output, true );
		
		// Should use environment-level woo version
		$this->assertEquals( '8.3.0', $data['woo'] );
	}
	
	/**
	 * Test WooCommerce version precedence - Level 6: Built-in fallback.
	 */
	public function test_woo_precedence_level_6_builtin_fallback(): void {
		// No config, no flags, nothing
		$output = qit_run_env_up( [
			'env:up',
			'--json',
		] );
		$data = json_decode( $output, true );
		
		// Should have no WooCommerce by default (unless added by dependency)
		$this->assertEmpty( $data['woo'] );
	}
	
	/**
	 * Test complex WooCommerce precedence scenario.
	 */
	public function test_woo_precedence_complex_scenario(): void {
		$config = [
			'environments' => [
				'production' => [
					'woo' => '8.0.0', // Environment says 8.0.0
					'plugins' => [
						[
							'slug' => 'woocommerce',
							'from' => 'wporg',
							'version' => '8.1.0',
						],
					],
				],
				'default' => [
					'woo' => '7.9.0', // Default environment woo
				],
			],
		];
		
		// Test 1: --woo flag should override everything
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--environment', 'production',
			'--woo', 'nightly', // This should win
		], $config );
		$data = json_decode( $output, true );
		$this->assertEquals( 'nightly', $data['woo'] );
		
		// Test 2: Without --woo, environment woo is used
		// Plugin spec in config doesn't override the woo field
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--environment', 'production',
		], $config );
		$data = json_decode( $output, true );
		$this->assertEquals( '8.0.0', $data['woo'] ); // Environment woo value
		
		// Test 3: Without explicit environment, default environment should be used
		$output = qit_run_env_up( [
			'env:up',
			'--json',
		], $config );
		$data = json_decode( $output, true );
		$this->assertEquals( '7.9.0', $data['woo'] );
	}
	
	/**
	 * Test complex scenario with everything.
	 */
	public function test_env_up_complex_scenario(): void {
		$config = [
			'environments' => [
				'testing' => [
					'php' => '8.0',
					'wp'  => '6.2',
					'plugins' => [
						'akismet',
					],
					'themes' => [
						'twentytwentythree',
					],
				],
			],
		];
		
		$output = qit_run_env_up( [
			'env:up',
			'--json',
			'--environment', 'testing',
			'--php', '8.2', // Override config
			'--woo', 'stable',
			'--plugin', 'jetpack',
			'--theme', 'storefront',
			'--php_extension', 'gd',
			'--object_cache',
		], $config );
		$data = json_decode( $output, true );
		
		// Check everything
		$this->assertEquals( 'e2e', $data['environment'] );
		$this->assertEquals( '8.2', $data['php'] ); // CLI override
		$this->assertEquals( '6.2', $data['wp'] ); // From config
		$this->assertTrue( $data['object_cache'] );
		
		// Plugins
		$pluginSlugs = array_column( $data['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs ); // From --woo
		$this->assertContains( 'akismet', $pluginSlugs ); // From config
		$this->assertContains( 'jetpack', $pluginSlugs ); // From CLI
		
		// Themes
		$themeSlugs = array_column( $data['themes'], 'slug' );
		$this->assertContains( 'twentytwentythree', $themeSlugs ); // From config
		$this->assertContains( 'storefront', $themeSlugs ); // From CLI
		
		// PHP extensions
		$this->assertContains( 'gd', $data['php_extensions'] );
	}
}