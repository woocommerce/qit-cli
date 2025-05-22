<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class EnvInfoConstructionTest extends TestCase {
	use SnapshotHelpers;

	protected $tempDir;

	protected function setUp(): void {
		parent::setUp();

		// Set up temporary directory
		$this->tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
		if ( ! mkdir( $this->tempDir ) ) {
			throw new \RuntimeException( "Failed to create temporary directory: $this->tempDir" );
		}

		// Check QIT CLI initialization
		exec( 'qit connect --help', $output, $returnVar );
		if ( $returnVar !== 0 ) {
			$this->markTestSkipped( 'QIT CLI is not initialized. Run "qit connect" first.' );
		}

		// Check temporary directory permissions
		if ( ! is_writable( sys_get_temp_dir() ) ) {
			$this->markTestSkipped( 'Temporary directory is not writable.' );
		}
	}

	protected function tearDown(): void {
		// Clean up temporary directory
		if ( file_exists( $this->tempDir ) ) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $files as $file ) {
				if ( $file->isDir() ) {
					rmdir( $file->getRealPath() );
				} else {
					unlink( $file->getRealPath() );
				}
			}
			rmdir( $this->tempDir );
		}

		// Clean up environment
		qit( [ 'env:down' ] );
		parent::tearDown();
	}

	/**
	 * Normalizes E2EEnvInfo to remove dynamic values for snapshot consistency.
	 *
	 * @param array $env_info The E2EEnvInfo array.
	 *
	 * @return array Normalized E2EEnvInfo array.
	 */
	private function normalize_env_info( array $env_info ) {
		$original_env_id = isset( $env_info['env_id'] ) ? $env_info['env_id'] : null;
		if ( $original_env_id ) {
			$env_info['env_id']        = 'ENV_ID_NORMALIZED';
			$env_info['temporary_env'] = str_replace( $original_env_id, 'ENV_ID_NORMALIZED', $env_info['temporary_env'] );
		}

		$env_info['created_at'] = 1700000000;
		$env_info['domain']     = 'normalized.localhost';

		// Normalize paths
		$real_temp_dir = realpath( sys_get_temp_dir() );
		if ( $real_temp_dir ) {
			// Normalize temp dir path to avoid double slashes
			$real_temp_dir = rtrim( $real_temp_dir, '/' );
			$env_info      = json_decode( str_replace(
				[ $real_temp_dir . '/', $real_temp_dir ],
				'/tmp-normalized/',
				json_encode( $env_info )
			), true );
		}

		// Normalize configuration ID in temporary_env
		if ( isset( $env_info['temporary_env'] ) ) {
			$env_info['temporary_env'] = preg_replace(
				'/_qit_config-qit_custom_tests_[a-f0-9]+/',
				'_qit_config-normalized',
				$env_info['temporary_env']
			);
		}

		if ( ! empty( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as &$plugin ) {
				if ( ! is_string( $plugin ) ) {
					throw new \RuntimeException( 'Plugin must be a string, got ' . gettype( $plugin ) );
				}
				// Normalize local plugin paths in strings
				if ( preg_match( '/^\/tmp-normalized\/+qit_test_[a-f0-9]+\/fake-plugin_[a-f0-9]+\.zip$/i', $plugin ) ) {
					$plugin = '/tmp-normalized/normalized-plugin.zip';
				}
			}
			unset( $plugin ); // Unset reference to avoid issues
		}

		if ( ! empty( $env_info['themes'] ) && is_array( $env_info['themes'] ) ) {
			foreach ( $env_info['themes'] as &$theme ) {
				if ( ! is_string( $theme ) ) {
					throw new \RuntimeException( 'Theme must be a string, got ' . gettype( $theme ) );
				}
				// Normalize local theme paths in strings
				if ( preg_match( '/^\/tmp-normalized\/+qit_test_[a-f0-9]+\/fake-theme_[a-f0-9]+\.zip$/i', $theme ) ) {
					$theme = '/tmp-normalized/normalized-theme.zip';
				}
				// Normalize absolute theme paths
				if ( $theme === '/absolute/path/to/theme.zip' ) {
					$theme = '/absolute/path/normalized-theme.zip';
				}
			}
			unset( $theme );
		}

		// Normalize volumes
		if ( ! empty( $env_info['volumes'] ) && is_array( $env_info['volumes'] ) ) {
			$normalized_volumes = [];
			foreach ( $env_info['volumes'] as $container_path => $host_path ) {
				$normalized_host_path                  = str_replace(
					realpath( dirname( $host_path ) ) . '/',
					'/normalized/path/',
					$host_path
				);
				$normalized_volumes[ $container_path ] = $normalized_host_path;
			}
			$env_info['volumes'] = $normalized_volumes;
		}

		return $env_info;
	}

	/**
	 * Creates a temporary qit.json file and runs env:up, returning normalized EnvInfo or exception.
	 *
	 * @param array $config qit.json configuration to write to file.
	 * @param array $cli_args Additional CLI arguments.
	 * @param bool $expect_failure If true, returns the caught exception instead of failing.
	 *
	 * @return array|\RuntimeException Normalized EnvInfo array or exception if expect_failure is true.
	 */
	private function runQitConfigTest( array $config, array $cli_args = [], bool $expect_failure = false ) {
		// Create temporary qit.json file
		$configFile = $this->tempDir . '/qit_' . uniqid() . '.json';
		$configJson = json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		if ( $configJson === false ) {
			throw new \RuntimeException( 'Failed to encode config JSON: ' . json_last_error_msg() );
		}
		if ( file_put_contents( $configFile, $configJson ) === false ) {
			throw new \RuntimeException( "Failed to write config file: $configFile" );
		}

		// Build CLI command
		$command = array_merge( [
			'env:up',
			'--config=' . $configFile
		], $cli_args );

		// Run with QIT_TESTING_ENV_INFO
		$output    = null;
		$exception = null;
		try {
			$output = qit( $command, [], 0, [ 'QIT_TESTING_ENV_INFO' => '1' ] );
		} catch ( \RuntimeException $e ) {
			if ( $expect_failure ) {
				unlink( $configFile );

				return $e;
			}
			$this->fail( "Command failed: {$e->getMessage()}\nCommand: " . implode( ' ', $command ) );
		}

		// Clean up config file
		unlink( $configFile );

		// If expecting failure but no exception was thrown, fail the test
		if ( $expect_failure ) {
			$this->fail( "Expected command to fail but it succeeded.\nCommand: " . implode( ' ', $command ) );
		}

		// Decode and normalize
		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, "Invalid JSON output: $output" );

		return $this->normalize_env_info( $envInfo );
	}

	public function testBasicConfigArrayOfStrings() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce', 'wordpress-importer' ],
					'themes'              => [ 'storefront', 'twentytwentyone' ],
					'woocommerce_version' => ''
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( 'wordpress-importer', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( 'storefront', $envInfo['themes'] ) );
		$this->assertTrue( in_array( 'twentytwentyone', $envInfo['themes'] ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testAssociativePluginsConfig() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce', 'wordpress-importer' ],
					'themes'              => [ 'twentytwentyone' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [
			'--plugin=woocommerce',
			'--plugin=wordpress-importer'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( 'wordpress-importer', $envInfo['plugins'] ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testWpAndWooVersions() {
		$config = [
			'environments' => [
				'default' => [
					'wordpress_version'   => 'latest',
					'woocommerce_version' => 'stable',
					'plugins'             => [ 'woocommerce' ]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertEquals( 'latest', $envInfo['wp'] );
		$this->assertEquals( 'stable', $envInfo['woo_version'] );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testCliOverrides() {
		$config = [
			'environments' => [
				'default' => [
					'wordpress_version'   => '6.0',
					'php_version'         => '7.4',
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [
			'--wp=6.1',
			'--php_version=8.0',
			'--plugin=wordpress-importer'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertEquals( '6.1', $envInfo['wp'] );
		$this->assertEquals( '8.0', $envInfo['php_version'] );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( 'wordpress-importer', $envInfo['plugins'] ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}


	public function testPluginDependencies() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testExtensionSetResolution() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testEnvironmentVariables() {
		$envFile = $this->tempDir . '/test_' . uniqid() . '.env';
		file_put_contents( $envFile, "HELLO=world\nFOO=bar" );

		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [
			'--env=DB_NAME=wp_test',
			'--env_file=' . $envFile
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testLocalPathsAndUrls() {
		$dummyPluginZip = $this->tempDir . '/fake-plugin_' . uniqid() . '.zip';
		file_put_contents( $dummyPluginZip, 'fake plugin contents' );

		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'themes'              => [ 'storefront' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [
			'--plugin=' . $dummyPluginZip,
			'--theme=/absolute/path/to/theme.zip'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		// Debug: Log envInfo['plugins'] and envInfo['themes']
		file_put_contents( '/tmp/qit_debug_test.log', "testLocalPathsAndUrls envInfo['plugins']: " . print_r( $envInfo['plugins'], true ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit_debug_test.log', "testLocalPathsAndUrls envInfo['themes']: " . print_r( $envInfo['themes'], true ) . "\n", FILE_APPEND );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( '/tmp-normalized/normalized-plugin.zip', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( '/absolute/path/normalized-theme.zip', $envInfo['themes'] ) ); // Fails
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testSkipActivationFlags() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'themes'              => [ 'storefront' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [
			'--skip_activating_plugins',
			'--skip_activating_themes'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertTrue( $envInfo['skip_activating_plugins'] );
		$this->assertTrue( $envInfo['skip_activating_themes'] );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testMixedConfigAndCliPluginOverride() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce', 'my-plugin' ],
					'themes'              => [ 'storefront' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [
			'--plugin=woocommerce',
			'--plugin=contact-form-7'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( 'my-plugin', $envInfo['plugins'] ) );
		$this->assertTrue( in_array( 'contact-form-7', $envInfo['plugins'] ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}


	/**
	 * Test that invalid JSON syntax in qit.json is handled appropriately.
	 */
	public function testInvalidJson() {
		// Create a temporary qit.json file with invalid JSON
		$configFile  = $this->tempDir . '/qit_' . uniqid() . '.json';
		$invalidJson = '{ "environments": { "default": { "plugins": ["woocommerce"] }'; // Missing closing braces
		if ( file_put_contents( $configFile, $invalidJson ) === false ) {
			$this->fail( "Failed to write config file: $configFile" );
		}

		// Build CLI command
		$command = [ 'env:up', '--config=' . $configFile ];

		// Run and expect failure
		try {
			qit( $command, [], 0, [ 'QIT_TESTING_ENV_INFO' => '1' ] );
			$this->fail( 'Expected RuntimeException for invalid JSON but none was thrown' );
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'Error loading config', $e->getMessage() );
			$this->assertStringContainsString( 'Invalid qit.json format. Must be a JSON object', $e->getMessage() );
		} finally {
			unlink( $configFile );
		}
	}

	/**
	 * Test that omitting a required field in qit.json is handled gracefully.
	 */
	public function testMissingRequiredField() {
		// Create config with empty environments to trigger missing required field
		$config = [
			'environments' => []
		];

		$result = $this->runQitConfigTest( $config, [], true );
		$this->assertInstanceOf( \RuntimeException::class, $result );
		$this->assertStringContainsString( 'Error accessing config section', $result->getMessage() );
		$this->assertStringContainsString( "Environment 'default' not found", $result->getMessage() );
	}


	/**
	 * Test that EnvInfo correctly includes theme configuration from qit.json and CLI.
	 */
	public function testThemeConfiguration() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'themes'              => [ 'storefront' ],
					'woocommerce_version' => ''
				]
			]
		];

		// CLI override to add another theme
		$cliArgs = [ '--theme=twentytwentyone' ];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertTrue( in_array( 'storefront', $envInfo['themes'] ), 'Theme from qit.json should be included' );
		$this->assertTrue( in_array( 'twentytwentyone', $envInfo['themes'] ), 'Theme from CLI should be included' );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Test that EnvInfo correctly includes environment variables from CLI --env.
	 */
	public function testEnvVarFromCli() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		// Explicit CLI env var
		$cliArgs = [ '--env=DB_NAME=wp_test' ];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertArrayHasKey( 'env', $envInfo, 'EnvInfo should include env vars' );
		$this->assertEquals( 'wp_test', $envInfo['env']['DB_NAME'], 'CLI env var should be set' );
		$this->assertEquals( '/qit/wp-cli.yml', $envInfo['env']['WP_CLI_CONFIG_PATH'], 'Default WP CLI config path should be set' );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Test that EnvInfo correctly includes environment variables from a valid .env file.
	 */
	public function testEnvVarFromFile() {
		// Create a valid .env file
		$envFile = $this->tempDir . '/test_' . uniqid() . '.env';
		file_put_contents( $envFile, "HELLO=world\nFOO=bar" );

		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		$cliArgs = [ '--env_file=' . $envFile ];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertArrayHasKey( 'env', $envInfo, 'EnvInfo should include env vars' );
		$this->assertEquals( 'world', $envInfo['env']['HELLO'], 'Env var from file should be set' );
		$this->assertEquals( 'bar', $envInfo['env']['FOO'], 'Env var from file should be set' );
		$this->assertEquals( '/qit/wp-cli.yml', $envInfo['env']['WP_CLI_CONFIG_PATH'], 'Default WP CLI config path should be set' );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Test that EnvInfo correctly handles defaults when no env vars are specified.
	 */
	public function testDefaultEnvVars() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'             => [ 'woocommerce' ],
					'woocommerce_version' => ''
				]
			]
		];

		// No CLI env vars or env file
		$cliArgs = [];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertArrayHasKey( 'env', $envInfo, 'EnvInfo should include env vars' );
		$this->assertEquals( '/qit/wp-cli.yml', $envInfo['env']['WP_CLI_CONFIG_PATH'], 'Default WP CLI config path should be set' );
		$this->assertCount( 1, $envInfo['env'], 'Only default env var should be set' );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Test that EnvInfo correctly includes a local plugin path from qit.json.
	 */
	public function testLocalPluginPath() {
		// Create a dummy plugin ZIP
		$pluginZip = $this->tempDir . '/fake_plugin_' . uniqid() . '.zip';
		file_put_contents( $pluginZip, 'fake plugin contents' );

		$config = array(
			'environments' => array(
				'default' => array(
					'plugins'             => array( 'woocommerce', $pluginZip ),
					'woocommerce_version' => ''
				)
			)
		);

		$envInfo = $this->runQitConfigTest( $config );
		// Debug output to inspect plugins
		file_put_contents( '/tmp/test_debug.log', 'testLocalPluginPath plugins: ' . print_r( $envInfo['plugins'], true ) . "\n", FILE_APPEND );
		// Check for woocommerce slug
		$this->assertTrue( in_array( 'woocommerce', $envInfo['plugins'] ), 'Plugin slug from qit.json should be included' );
		// Check for normalized plugin path
		$this->assertTrue( in_array( '/tmp-normalized/normalized-plugin.zip', $envInfo['plugins'] ), 'Normalized local plugin path should be included' );
		// Verify no duplicates
		$this->assertEquals( count( $envInfo['plugins'] ), count( array_unique( $envInfo['plugins'] ) ), 'Plugin list should contain no duplicate slugs' );
		try {
			$snapshotData = json_encode( $envInfo, JSON_PRETTY_PRINT );
			file_put_contents( '/tmp/test_debug.log', 'testLocalPluginPath snapshot: ' . $snapshotData . "\n", FILE_APPEND );
			$this->assertMatchesSnapshot( $snapshotData );
		} catch ( Exception $e ) {
			$this->fail( 'Snapshot assertion failed: ' . $e->getMessage() );
		}
	}
}