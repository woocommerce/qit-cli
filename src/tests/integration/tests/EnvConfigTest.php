<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use QIT_CLI\App;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\QITConfig;
use QIT_CLI\PluginDependencies;
use QIT_CLI\Environment\ExtensionSetResolver;
use QIT_CLI\Environment\EnvironmentVersionResolver;

class EnvConfigTest extends TestCase {
	use SnapshotHelpers;

	protected $application;
	protected $commandTester;
	protected $tempDir;

	protected function setUp(): void {
		parent::setUp();

		// Initialize Symfony Console Application
		$this->application = new Application();
		$this->application->add( App::make( UpEnvironmentCommand::class ) );

		$this->commandTester = new CommandTester( $this->application->find( 'env:up' ) );

		// Set up temporary directory
		$this->tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
		if ( ! mkdir( $this->tempDir ) ) {
			throw new \RuntimeException( "Failed to create temporary directory: $this->tempDir" );
		}

		// Mock dependencies
		App::bind( QITConfig::class, function () {
			return new class extends QITConfig {
				private $config = [];

				public function setConfig( array $config ) {
					$this->config = $config;
				}

				public function get_environment( string $environment ): array {
					return $this->config['environments'][ $environment ] ?? [];
				}

				public function get_all(): array {
					return $this->config;
				}
			};
		} );

		// Mock PluginDependencies to simulate dependency resolution
		App::bind( PluginDependencies::class, function () {
			return new class extends PluginDependencies {
				public function get_dependencies( array $plugins, array $themes, string $dependencies_mode ): array {
					$deps = [ 'plugins' => [], 'themes' => [], 'php_extensions' => [] ];
					if ( $dependencies_mode === 'activate' && in_array( 'woocommerce', $plugins ) ) {
						$deps['plugins'][] = 'woocommerce-gateway-stripe';
					}

					return $deps;
				}

				public function maybe_add_plugin_dependencies( array $dependencies, array &$plugins ) {
					$plugins = array_merge( $plugins, $dependencies );
				}

				public function maybe_add_theme_dependencies( array $dependencies, array &$themes ) {
					$themes = array_merge( $themes, $dependencies );
				}

				public function maybe_add_php_extensions( array $dependencies, array &$php_extensions ) {
					$php_extensions = array_merge( $php_extensions, $dependencies );
				}
			};
		} );

		// Mock ExtensionSetResolver to simulate extension set resolution
		App::bind( ExtensionSetResolver::class, function () {
			return new class extends ExtensionSetResolver {
				public function resolve( $env_info, array $overrides ): object {
					if ( isset( $overrides['overrides']['extension_set'] ) && $overrides['overrides']['extension_set'] === 'standard' ) {
						$env_info->php_extensions = array_merge( $env_info->php_extensions, [ 'gd', 'curl' ] );
					}

					return $env_info;
				}
			};
		} );

		// Mock EnvironmentVersionResolver to simulate version resolution
		App::bind( EnvironmentVersionResolver::class, function () {
			return new class extends EnvironmentVersionResolver {
				public static function resolve_wp( $version ): string {
					return $version === 'latest' ? '6.6' : $version;
				}

				public static function resolve_woo( $version, array $plugins ) {
					return $version === 'stable' ? '9.0' : $version;
				}
			};
		} );
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

		// Reset App bindings
		App::offsetUnset( QITConfig::class );
		App::offsetUnset( PluginDependencies::class );
		App::offsetUnset( ExtensionSetResolver::class );
		App::offsetUnset( EnvironmentVersionResolver::class );

		parent::tearDown();
	}

	/**
	 * Runs the env:up command with the given config and CLI options, returning normalized EnvInfo.
	 *
	 * @param array $config In-memory qit.json configuration.
	 * @param array $cli_options CLI options to pass to env:up.
	 *
	 * @return array Normalized EnvInfo array.
	 */
	private function runQitConfigTest( array $config, array $cli_options = [] ): array {
		// Set in-memory config
		$qitConfig = App::make( QITConfig::class );
		$qitConfig->setConfig( $config );

		// Prepare CLI command
		$commandOptions = array_merge( [
			'command' => 'env:up',
			'--json'  => true,
		], $cli_options );

		// Run command with QIT_TESTING_ENV_INFO
		putenv( 'QIT_TESTING_ENV_INFO=1' );
		$this->commandTester->execute( $commandOptions );
		putenv( 'QIT_TESTING_ENV_INFO' );

		// Decode and normalize output
		$output  = $this->commandTester->getDisplay();
		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, "Invalid JSON output: $output" );

		return $this->normalize_env_info( $envInfo );
	}

	/**
	 * Normalizes EnvInfo to remove dynamic values for snapshot consistency.
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
				if ( isset( $theme['version'] ) ) {
					$theme['version'] = 'NORMALIZED_VERSION';
				}
				if ( isset( $theme['downloaded_source'] ) ) {
					$theme['downloaded_source'] = '/normalized/path.zip';
				}
				if ( isset( $theme['source'] ) && strpos( $theme['source'], 'http' ) === 0 ) {
					$filename        = basename( parse_url( $theme['source'], PHP_URL_PATH ) );
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

	public function testBasicConfigArrayOfStrings() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'wordpress-importer' ],
					'themes'  => [ 'storefront', 'twentytwentyone' ]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testAssociativePluginsConfig() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce'        => [ 'action' => 'activate' ],
						'wordpress-importer' => [ 'action' => 'bootstrap' ]
					],
					'themes'  => [ 'twentytwentyone' ]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testWpAndWooVersions() {
		$config = [
			'environments' => [
				'default' => [
					'wp'          => 'latest',
					'woo_version' => 'stable',
					'plugins'     => [ 'woocommerce' ]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertEquals( '6.6', $envInfo['wp'] );
		$this->assertEquals( '9.0', $envInfo['woo_version'] );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testCliOverrides() {
		$config = [
			'environments' => [
				'default' => [
					'wp'          => '6.0',
					'php_version' => '7.4',
					'plugins'     => [ 'woocommerce' ]
				]
			]
		];

		$cliOptions = [
			'--wp'          => '6.1',
			'--php_version' => '8.0',
			'--plugin'      => [ 'wordpress-importer' ]
		];

		$envInfo = $this->runQitConfigTest( $config, $cliOptions );
		$this->assertEquals( '6.1', $envInfo['wp'] );
		$this->assertEquals( '8.0', $envInfo['php_version'] );
		$this->assertContains( 'wordpress-importer', array_column( $envInfo['plugins'], 'slug' ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testPluginDependencies() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'           => [ 'woocommerce' ],
					'dependencies_mode' => 'activate'
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertContains( 'woocommerce', array_column( $envInfo['plugins'], 'slug' ) );
		$this->assertContains( 'woocommerce-gateway-stripe', array_column( $envInfo['plugins'], 'slug' ) );
		$this->assertEquals( 'activate', $envInfo['dependencies_mode'] );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testExtensionSetResolution() {
		$config = [
			'environments' => [
				'default' => [
					'extension_set'  => 'standard',
					'php_extensions' => [ 'mysql' ]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertContains( 'mysql', $envInfo['php_extensions'] );
		$this->assertContains( 'gd', $envInfo['php_extensions'] );
		$this->assertContains( 'curl', $envInfo['php_extensions'] );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testEnvironmentVariables() {
		$envFile = $this->tempDir . '/test.env';
		file_put_contents( $envFile, "HELLO=world\nFOO=bar" );

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$cliOptions = [
			'--env'      => [ 'DB_NAME=wp_test' ],
			'--env_file' => [ $envFile ]
		];

		$envInfo       = $this->runQitConfigTest( $config, $cliOptions );
		$dockerEnvVars = App::getVar( 'QIT_DOCKER_ENV_VARS', [] );
		$this->assertArrayHasKey( 'HELLO', $dockerEnvVars );
		$this->assertEquals( 'world', $dockerEnvVars['HELLO'] );
		$this->assertArrayHasKey( 'FOO', $dockerEnvVars );
		$this->assertEquals( 'bar', $dockerEnvVars['FOO'] );
		$this->assertArrayHasKey( 'DB_NAME', $dockerEnvVars );
		$this->assertEquals( 'wp_test', $dockerEnvVars['DB_NAME'] );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testLocalPathsAndUrls() {
		$dummyPluginZip = $this->tempDir . '/fake-plugin.zip';
		file_put_contents( $dummyPluginZip, 'fake plugin contents' );

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						$dummyPluginZip,
						'https://example.com/plugin.zip' => [ 'source' => 'https://example.com/plugin.zip' ]
					],
					'themes'  => [
						'/absolute/path/to/theme.zip'
					]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testSkipActivationFlags() {
		$config = [
			'environments' => [
				'default' => [
					'skip_activating_plugins' => true,
					'skip_activating_themes'  => true,
					'plugins'                 => [ 'woocommerce' ],
					'themes'                  => [ 'storefront' ]
				]
			]
		];

		$envInfo = $this->runQitConfigTest( $config );
		$this->assertTrue( $envInfo['skip_activating_plugins'] );
		$this->assertTrue( $envInfo['skip_activating_themes'] );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}

	public function testMixedConfigAndCliPluginOverride() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce' => [ 'action' => 'bootstrap' ],
						'my-plugin'   => [ 'source' => './my-plugin.zip' ]
					],
					'themes'  => [ 'storefront' ]
				]
			]
		];

		$cliOptions = [
			'--plugin' => [
				'woocommerce:activate:newTestTag',
				'contact-form-7'
			]
		];

		$envInfo = $this->runQitConfigTest( $config, $cliOptions );
		$this->assertContains( 'contact-form-7', array_column( $envInfo['plugins'], 'slug' ) );
		$this->assertMatchesSnapshot( json_encode( $envInfo, JSON_PRETTY_PRINT ) );
	}
}