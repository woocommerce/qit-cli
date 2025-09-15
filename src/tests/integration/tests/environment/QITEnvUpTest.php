<?php

use PHPUnit\Framework\TestCase;

class QITEnvUpTest extends TestCase {

	private $testPluginDir;

	protected function setUp(): void {
		parent::setUp();
		// Create test plugin directory
		$this->testPluginDir = sys_get_temp_dir() . '/test-plugin-' . uniqid();
		mkdir( $this->testPluginDir, 0777, true );

		// Create a basic plugin file
		file_put_contents( $this->testPluginDir . '/test-plugin.php', '<?php
/**
 * Plugin Name: Test Plugin
 * Description: A test plugin for QIT
 * Version: 1.0.0
 */' );
	}

	protected function tearDown(): void {
		qit( [ 'env:down' ] );

		// Clean up test plugin directory with safety checks
		if ( $this->testPluginDir && is_dir( $this->testPluginDir ) ) {
			$this->safeRemoveDirectory( $this->testPluginDir );
		}

		parent::tearDown();
	}

	private function safeRemoveDirectory( $dir ) {
		// Safety checks
		if ( ! is_dir( $dir ) ) {
			return;
		}

		// Ensure we're only deleting from temp directory
		$tempDir   = realpath( sys_get_temp_dir() );
		$targetDir = realpath( $dir );

		if ( $tempDir === false || $targetDir === false ) {
			return;
		}

		// Check that target directory is within temp directory
		if ( strpos( $targetDir, $tempDir ) !== 0 ) {
			trigger_error( "Attempted to delete directory outside of temp: $targetDir", E_USER_WARNING );

			return;
		}

		// Additional safety: ensure the directory name contains our test prefix
		if ( strpos( basename( $targetDir ), 'test-plugin-' ) !== 0 ) {
			trigger_error( "Directory doesn't match expected pattern: $targetDir", E_USER_WARNING );

			return;
		}

		// Don't delete if it's too short (like "/" or "/tmp")
		if ( strlen( $targetDir ) < strlen( $tempDir ) + 10 ) {
			trigger_error( "Path too short, might be dangerous: $targetDir", E_USER_WARNING );

			return;
		}

		// Safe to proceed with deletion
		$this->recursiveDelete( $targetDir );
	}

	private function recursiveDelete( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			if ( is_dir( $path ) ) {
				$this->recursiveDelete( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}

	/**
	 * Test basic env:up without any parameters
	 */
	public function testBasicEnvUp() {
		$output = qit( [ 'env:up', '--json' ], [
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => 'stable'
				]
			]
		] );
		$result = json_decode( $output, true );

		$this->assertArrayHasKey( 'env_id', $result );
		$this->assertArrayHasKey( 'site_url', $result );
		$this->assertArrayHasKey( 'wp_version', $result );
		$this->assertArrayHasKey( 'php_version', $result );

		// Default values
		$this->assertEquals( 'stable', $result['wp_version'] );
		$this->assertEquals( '8.2', $result['php_version'] );
		$this->assertEquals( 'stable', $result['woo_version'] ?? '' );
	}

	/**
	 * Test env:up with CLI parameters
	 */
	public function testEnvUpWithCliParameters() {
		$output = qit( [
			'env:up',
			'--json',
			'--wp',
			'6.4',
			'--php',
			'8.1',
			'--woo',
			'stable',
			'--object_cache'
		] );
		$result = json_decode( $output, true );

		$this->assertEquals( '6.4', $result['wp_version'] );
		$this->assertEquals( '8.1', $result['php_version'] );
		$this->assertEquals( 'stable', $result['woo_version'] );
		$this->assertTrue( $result['object_cache'] );
	}

	/**
	 * Test env:up with plugins and themes
	 */
	public function testEnvUpWithPluginsAndThemes() {
		$output = qit( [
			'env:up',
			'--json',
			'--plugin',
			'woocommerce',
			'--plugin',
			'wordpress-importer',
			'--theme',
			'storefront',
			'--theme',
			'twentytwentyone'
		] );
		$result = json_decode( $output, true );

		// Check plugins
		$this->assertArrayHasKey( 'plugins', $result );
		$pluginSlugs = array_column( $result['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
		$this->assertContains( 'wordpress-importer', $pluginSlugs );

		// Check themes
		$this->assertArrayHasKey( 'themes', $result );
		$themeSlugs = array_column( $result['themes'], 'slug' );
		$this->assertContains( 'storefront', $themeSlugs );
		$this->assertContains( 'twentytwentyone', $themeSlugs );
	}

	/**
	 * Test env:up with qit.json configuration
	 */
	public function testEnvUpWithQitJson() {
		$output = qit( [ 'env:up', '--json' ], [
			'sut'          => [
				'slug'   => 'test-plugin',
				'type'   => 'plugin',
				'source' => [
					'type' => 'local',
					'path' => $this->testPluginDir
				]
			],
			'environments' => [
				'default' => [
					'wp'  => '6.3',
					'php' => '8.0',
					'woo' => 'stable',
					'plugins'     => [ 'woocommerce', 'wordpress-importer' ],
					'themes'      => [ 'storefront' ]
				]
			]
		] );
		$result = json_decode( $output, true );

		$this->assertEquals( '6.3', $result['wp_version'] );
		$this->assertEquals( '8.0', $result['php_version'] );
		$this->assertEquals( 'stable', $result['woo_version'] );

		$pluginSlugs = array_column( $result['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
		$this->assertContains( 'wordpress-importer', $pluginSlugs );

		$themeSlugs = array_column( $result['themes'], 'slug' );
		$this->assertContains( 'storefront', $themeSlugs );
	}

	/**
	 * Test CLI overrides qit.json
	 */
	public function testCliOverridesQitJson() {
		$output = qit( [
			'env:up',
			'--json',
			'--wp',
			'6.4',
			'--php',
			'8.2',
			'--plugin',
			'wordpress-importer'
		], [
			'environments' => [
				'default' => [
					'wp'  => '6.3',
					'php' => '8.0',
					'plugins'     => [ 'woocommerce' ]
				]
			]
		] );
		$result = json_decode( $output, true );

		// CLI should override qit.json
		$this->assertEquals( '6.4', $result['wp_version'] );
		$this->assertEquals( '8.2', $result['php_version'] );

		// Both plugins should be present (merged)
		$pluginSlugs = array_column( $result['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
		$this->assertContains( 'wordpress-importer', $pluginSlugs );
	}

	/**
	 * Test different environment configurations
	 */
	public function testDifferentEnvironments() {
		$qitJson = [
			'environments' => [
				'default' => [
					'wp_version'  => '6.3',
					'php_version' => '8.0'
				],
				'testing' => [
					'wp_version'  => '6.4',
					'php_version' => '8.1',
					'woo_version' => 'stable'
				]
			]
		];

		// Test default environment
		$output = qit( [ 'env:up', '--json' ], $qitJson );
		$result = json_decode( $output, true );
		$this->assertEquals( '6.3', $result['wp_version'] );
		$this->assertEquals( '8.0', $result['php_version'] );

		qit( [ 'env:down' ] );

		// Test specific environment
		$output = qit( [ 'env:up', '--json', '--environment', 'testing' ], $qitJson );
		$result = json_decode( $output, true );
		$this->assertEquals( '6.4', $result['wp_version'] );
		$this->assertEquals( '8.1', $result['php_version'] );
		$this->assertEquals( 'stable', $result['woo_version'] );
	}

	/**
	 * Test with PHP extensions
	 */
	public function testWithPhpExtensions() {
		$output = qit( [
			'env:up',
			'--json',
			'--php_extension',
			'gd',
			'--php_extension',
			'imagick'
		] );
		$result = json_decode( $output, true );

		$this->assertArrayHasKey( 'php_extensions', $result );
		$this->assertContains( 'gd', $result['php_extensions'] );
		$this->assertContains( 'imagick', $result['php_extensions'] );
	}

	/**
	 * Test with volumes
	 */
	public function testWithVolumes() {
		// Create a test file to mount
		$testFile = sys_get_temp_dir() . '/test-plugin.php';
		file_put_contents( $testFile, '<?php // Test plugin' );

		$output = qit( [
			'env:up',
			'--json',
			'--volume',
			$testFile . ':/var/www/html/wp-content/plugins/test-plugin.php'
		] );
		$result = json_decode( $output, true );

		$this->assertArrayHasKey( 'volumes', $result );
		$this->assertNotEmpty( $result['volumes'] );

		// Cleanup
		unlink( $testFile );
	}

	/**
	 * Test with environment variables
	 */
	public function testWithEnvironmentVariables() {
		$output = qit( [
			'env:up',
			'--json',
			'--env',
			'MY_VAR=test_value',
			'--env',
			'ANOTHER_VAR=another_value'
		] );
		$result = json_decode( $output, true );

		$this->assertArrayHasKey( 'env', $result );
		$this->assertArrayHasKey( 'MY_VAR', $result['env'] );
		$this->assertEquals( 'test_value', $result['env']['MY_VAR'] );
		$this->assertArrayHasKey( 'ANOTHER_VAR', $result['env'] );
		$this->assertEquals( 'another_value', $result['env']['ANOTHER_VAR'] );
	}

	/**
	 * Test with environment file
	 */
	public function testWithEnvironmentFile() {
		// Create an env file
		$envFile = sys_get_temp_dir() . '/test.env';
		file_put_contents( $envFile, "TEST_VAR=from_file\nANOTHER_TEST=also_from_file" );

		$output = qit( [
			'env:up',
			'--json',
			'--env_file',
			$envFile
		] );
		$result = json_decode( $output, true );

		$this->assertArrayHasKey( 'env', $result );
		$this->assertArrayHasKey( 'TEST_VAR', $result['env'] );
		$this->assertEquals( 'from_file', $result['env']['TEST_VAR'] );
		$this->assertArrayHasKey( 'ANOTHER_TEST', $result['env'] );
		$this->assertEquals( 'also_from_file', $result['env']['ANOTHER_TEST'] );

		// Cleanup
		unlink( $envFile );
	}

	/**
	 * Test WordPress version variations
	 */
	public function testWordPressVersions() {
		$config = [
			'sut'          => [
				'slug'   => 'test-plugin',
				'type'   => 'plugin',
				'source' => [ 'type' => 'local', 'path' => $this->testPluginDir ]
			],
			'environments' => [
				'default' => [
					'php' => '8.2'
				]
			]
		];

		// Test stable
		$output = qit( [ 'env:up', '--json', '--wp', 'stable' ], $config );
		$result = json_decode( $output, true );
		$this->assertEquals( 'stable', $result['wp_version'] );

		qit( [ 'env:down' ] );

		// Test specific version
		$output = qit( [ 'env:up', '--json', '--wp', '6.4.2' ], $config );
		$result = json_decode( $output, true );
		$this->assertEquals( '6.4.2', $result['wp_version'] );

		qit( [ 'env:down' ] );

		// Test RC version
		$output = qit( [ 'env:up', '--json', '--wp', 'rc' ], $config );
		$result = json_decode( $output, true );
		// RC should resolve to either a specific RC version or fall back to stable
		$this->assertNotEmpty( $result['wp_version'] );
	}

	/**
	 * Test WooCommerce version variations
	 */
	public function testWooCommerceVersions() {
		$config = [
			'sut'          => [
				'slug'   => 'test-plugin',
				'type'   => 'plugin',
				'source' => [ 'type' => 'local', 'path' => $this->testPluginDir ]
			],
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => 'stable'
				]
			]
		];

		// Test stable
		$output = qit( [ 'env:up', '--json', '--woo', 'stable' ], $config );
		$result = json_decode( $output, true );
		$this->assertEquals( 'stable', $result['woo_version'] );

		// Verify WooCommerce is in plugins list
		$pluginSlugs = array_column( $result['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );

		qit( [ 'env:down' ] );

		// Test specific version
		$output = qit( [ 'env:up', '--json', '--woo', '8.5.1' ], $config );
		$result = json_decode( $output, true );
		$this->assertEquals( '8.5.1', $result['woo_version'] );
		
		// Verify WooCommerce is in plugins list with correct version
		$wooPlugin = null;
		foreach ( $result['plugins'] as $plugin ) {
			if ( $plugin['slug'] === 'woocommerce' ) {
				$wooPlugin = $plugin;
				break;
			}
		}
		$this->assertNotNull( $wooPlugin );
		$this->assertEquals( '8.5.1', $wooPlugin['version'] );
		$this->assertEquals( 'wporg', $wooPlugin['from'] );

		qit( [ 'env:down' ] );

		// Test RC version (special version resolved to URL)
		$output = qit( [ 'env:up', '--json', '--woo', 'rc' ], $config );
		$result = json_decode( $output, true );
		$this->assertEquals( 'rc', $result['woo_version'] );
		
		// Verify WooCommerce is in plugins list with URL source
		$wooPlugin = null;
		foreach ( $result['plugins'] as $plugin ) {
			if ( $plugin['slug'] === 'woocommerce' ) {
				$wooPlugin = $plugin;
				break;
			}
		}
		$this->assertNotNull( $wooPlugin );
		$this->assertEquals( 'rc', $wooPlugin['version'] );
		$this->assertEquals( 'url', $wooPlugin['from'] );
		$this->assertStringContains( 'github.com', $wooPlugin['source'] );

		qit( [ 'env:down' ] );

		// Test nightly version (special version resolved to URL)
		$output = qit( [ 'env:up', '--json', '--woo', 'nightly' ], $config );
		$result = json_decode( $output, true );
		$this->assertEquals( 'nightly', $result['woo_version'] );
		
		// Verify WooCommerce is in plugins list with URL source
		$wooPlugin = null;
		foreach ( $result['plugins'] as $plugin ) {
			if ( $plugin['slug'] === 'woocommerce' ) {
				$wooPlugin = $plugin;
				break;
			}
		}
		$this->assertNotNull( $wooPlugin );
		$this->assertEquals( 'nightly', $wooPlugin['version'] );
		$this->assertEquals( 'url', $wooPlugin['from'] );
		$this->assertStringContains( 'nightly', $wooPlugin['source'] );
	}

	/**
	 * Test with invalid environment name
	 */
	public function testInvalidEnvironment() {
		$output = qit( [ 'env:up', '--environment', 'nonexistent' ], [
			'environments' => [
				'default' => [
					'wp' => '6.3'
				]
			]
		], 1 );

		$this->assertStringContainsString( 'Environment \'nonexistent\' not found', $output );
	}

	/**
	 * Test skip activation flags
	 */
	public function testSkipActivation() {
		$output = qit( [
			'env:up',
			'--json',
			'--plugin',
			'woocommerce',
			'--theme',
			'storefront',
			'--skip_activating_plugins',
			'--skip_activating_themes'
		], [
			'sut'          => [
				'slug'   => 'test-plugin',
				'type'   => 'plugin',
				'source' => [ 'type' => 'local', 'path' => $this->testPluginDir ]
			],
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp'  => 'stable'
				]
			]
		] );
		$result = json_decode( $output, true );

		$this->assertTrue( $result['skip_activating_plugins'] ?? false );
		$this->assertTrue( $result['skip_activating_themes'] ?? false );
	}
}