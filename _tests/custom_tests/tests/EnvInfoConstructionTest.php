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
	private function normalize_env_info( array $env_info ): array {
		$original_env_id = $env_info['env_id'] ?? null;
		if ( $original_env_id ) {
			$env_info['env_id']        = 'ENV_ID_NORMALIZED';
			$env_info['temporary_env'] = str_replace( $original_env_id, 'ENV_ID_NORMALIZED', $env_info['temporary_env'] );
		}

		$env_info['created_at'] = 1700000000;
		$env_info['domain']     = 'normalized.localhost';

		if ( ! empty( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as &$plugin ) {
				if ( is_object( $plugin ) ) {
					$plugin = (array) $plugin;
				}
				if ( isset( $plugin['version'] ) ) {
					$plugin['version'] = 'NORMALIZED_VERSION';
				}
				if ( isset( $plugin['downloaded_source'] ) ) {
					$plugin['downloaded_source'] = '/normalized/path.zip';
				}
				if ( isset( $plugin['source'] ) && strpos( $plugin['source'], 'http' ) === 0 ) {
					$filename         = basename( parse_url( $plugin['source'], PHP_URL_PATH ) );
					$plugin['source'] = "https://normalized-remote-source/$filename";
				}
			}
		}

		if ( ! empty( $env_info['themes'] ) && is_array( $env_info['themes'] ) ) {
			foreach ( $env_info['themes'] as &$theme ) {
				if ( is_object( $theme ) ) {
					$theme = (array) $theme;
				}
				if ( isset( $theme['version'] ) ) {
					$theme['version'] = 'NORMALIZED_VERSION';
				}
				if ( isset( $theme['downloaded_source'] ) ) {
					$theme['downloaded_source'] = '/normalized/path.zip';
				}
				if ( isset( $theme['source'] ) && strpos( $theme['source'], 'http' ) === 0 ) {
					$filename        = basename( parse_url( $plugin['source'], PHP_URL_PATH ) );
					$theme['source'] = "https://normalized-remote-source/$filename";
				}
			}
		}

		// Normalize paths
		$real_temp_dir = realpath( sys_get_temp_dir() );
		if ( $real_temp_dir ) {
			$env_info = json_decode( str_replace(
				[ $real_temp_dir, rtrim( $real_temp_dir, '/' ) . '/' ],
				'/tmp-normalized/',
				json_encode( $env_info )
			), true );
		}

		return $env_info;
	}

	/**
	 * Creates a temporary qit.json file and runs env:up, returning normalized EnvInfo.
	 *
	 * @param array $config qit.json configuration to write to file.
	 * @param array $cli_args Additional CLI arguments.
	 *
	 * @return array Normalized EnvInfo array.
	 */
	private function runQitConfigTest( array $config, array $cli_args = [] ): array {
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
		try {
			$output = qit( $command, [], 0, [ 'QIT_TESTING_ENV_INFO' => '1' ] );
		} catch ( \RuntimeException $e ) {
			$this->fail( "Command failed: {$e->getMessage()}\nCommand: " . implode( ' ', $command ) );
		}

		// Clean up config file
		unlink( $configFile );

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
			'--plugin=woocommerce:activate',
			'--plugin=wordpress-importer:bootstrap'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
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
			'--plugin=woocommerce:activate',
			'--plugin=contact-form-7'
		];

		$envInfo = $this->runQitConfigTest( $config, $cliArgs );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}
}