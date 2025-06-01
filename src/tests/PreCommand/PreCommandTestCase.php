<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Extension;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;
use function QIT_CLI\get_manager_url;

abstract class PreCommandTestCase extends TestCase {
	protected $temp_dir;
	protected $to_delete = [];
	protected $to_reset = [];
	protected $non_normalized_env_info = null;
	protected $mock_plugin_dir;

	protected function setUp(): void {
		parent::setUp();
		try {
			$this->temp_dir = '/tmp/qit/qit_test_' . uniqid();
			if ( ! is_dir( $this->temp_dir ) ) {
				mkdir( $this->temp_dir, 0777, true );
				chmod( $this->temp_dir, 0777 );
			}
			$this->non_normalized_env_info = null;
			$this->to_reset                = [];

			// Create a mock plugin directory
			$this->createMockPluginDirectory();

			file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: setUp temp_dir set to: {$this->temp_dir}\n", FILE_APPEND );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: setUp exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	protected function tearDown(): void {
		try {
			foreach ( $this->to_delete as $file ) {
				if ( is_dir( $file ) ) {
					$this->rmdir_recursive( $file );
				} else {
					@unlink( $file );
				}
			}
			$this->to_delete = [];
			foreach ( $this->to_reset as $key ) {
				App::offsetUnset( $key );
			}
			$this->to_reset                = [];
			$this->non_normalized_env_info = null;
			if ( ! getenv( 'QIT_TESTING_ENV_INFO' ) ) {
				// Add environment cleanup logic if needed
			}
			file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: tearDown completed for temp_dir: {$this->temp_dir}\n", FILE_APPEND );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: tearDown exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
		parent::tearDown();
	}

	protected function mock_file( string $path, string $content ): void {
		$full_path = $this->temp_dir . DIRECTORY_SEPARATOR . $path;
		$dir       = dirname( $full_path );
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0777, true );
		}
		file_put_contents( $full_path, $content );
		$this->to_delete[] = $full_path;
		file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: Mocked file at $full_path\n", FILE_APPEND );
	}

	protected function create_temp_config_file( array $config ): string {
		$config_path = $this->temp_dir . '/qit_' . uniqid() . '.json';
		file_put_contents( $config_path, json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		$this->to_delete[] = $config_path;
		file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: Created temp config file at $config_path\n", FILE_APPEND );

		return $config_path;
	}

	protected function rmdir_recursive( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = "$dir/$file";
			if ( is_dir( $path ) ) {
				$this->rmdir_recursive( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}

	protected function createMockPluginDirectory(): void {
		$plugin_dir = $this->temp_dir . '/my-awesome-plugin';
		if ( ! is_dir( $plugin_dir ) ) {
			mkdir( $plugin_dir, 0777, true );
		}

		$plugin_file    = $plugin_dir . '/my-awesome-plugin.php';
		$plugin_content = <<<PHP
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.0.0
 * Description: A mock plugin for testing
 * Author: QIT Tests
 */

// This is a mock plugin file for testing
PHP;
		file_put_contents( $plugin_file, $plugin_content );

		$this->mock_plugin_dir = $plugin_dir;
		$this->to_delete[]     = $plugin_dir;

		file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: Created mock plugin directory at {$plugin_dir}\n", FILE_APPEND );
	}

	protected function getMockPluginDir(): string {
		return $this->mock_plugin_dir;
	}

	protected function run_unit_test( array $config, array $cli_args = [], bool $expect_failure = false ) {
		$this->non_normalized_env_info = null;
		if ( empty( $this->temp_dir ) || ! is_dir( $this->temp_dir ) ) {
			throw new \RuntimeException( 'temp_dir is not initialized. Ensure parent::setUp() is called.' );
		}

		try {
			$config_path = $this->create_temp_config_file( $config );
			file_put_contents( '/tmp/qit/qit_debug.log', 'Test Config: ' . json_encode( $config, JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );

			putenv( 'QIT_TESTING_ENV_INFO=1' );
			$command = App::make( UpEnvironmentCommand::class );
			$input   = new ArrayInput( array_merge( [ '--config' => $config_path ], $cli_args ) );
			$input->bind( $command->getDefinition() );
			$output = new BufferedOutput();

			$return_code   = $command->execute( $input, $output );
			$output_string = $output->fetch();
			file_put_contents( '/tmp/qit/qit_debug.log', "run_unit_test: Command executed with return code $return_code\nOutput: $output_string\n", FILE_APPEND );

			if ( $expect_failure ) {
				if ( $return_code === 0 ) {
					$this->fail( 'Expected command failure but it succeeded' );
				}
				if ( preg_match( '/<error>(.*?)<\/error>/', $output_string, $matches ) ) {
					return [ 'exit_code' => $return_code, 'output' => $matches[1] ];
				}

				// Fallback if no <error> tags
				return [ 'exit_code' => $return_code, 'output' => trim( $output_string ) ];
			}

			$this->assertEquals( 0, $return_code, 'Command failed: ' . $output_string );

			$lines    = explode( "\n", trim( $output_string ) );
			$env_info = null;
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( str_starts_with( $line, '{' ) ) {
					$decoded = json_decode( $line, true );
					if ( is_array( $decoded ) ) {
						$env_info = $decoded;
						break;
					}
				}
			}

			$this->assertIsArray( $env_info, "Invalid JSON output: $output_string" );

			$this->non_normalized_env_info = $env_info;
			$normalized_env_info           = $this->normalize_env_info( $env_info );

			return $normalized_env_info;
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: run_unit_test exception: " . $e->getMessage() . "\n", FILE_APPEND );
			if ( $expect_failure ) {
				// Return the exception message as output for assertion
				return [ 'exit_code' => 1, 'output' => $e->getMessage() ];
			}
			throw $e;
		}
	}

	protected function get_non_normalized_output(): ?array {
		return $this->non_normalized_env_info;
	}

	protected function mockWpOrgPlugin( string $slug, string $version, string $download_link, array $requires_plugins = [] ): void {
		$key = sprintf( 'mock_%s', "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$slug}" );
		App::setVar( $key, json_encode( [
			'slug'             => $slug,
			'version'          => $version,
			'download_link'    => $download_link,
			'requires_plugins' => $requires_plugins,
		] ) );
		$this->to_reset[] = $key;
	}

	protected function mockWpOrgTheme( string $slug, string $version, string $download_link ): void {
		$key = sprintf( 'mock_%s', "https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]={$slug}" );
		App::setVar( $key, json_encode( [
			'slug'          => $slug,
			'version'       => $version,
			'download_link' => $download_link,
		] ) );
		$this->to_reset[] = $key;
	}

	protected function mockWooComDependencies( array $plugins = [], array $themes = [], array $php_extensions = [] ): void {
		$key = sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' );
		App::setVar( $key, json_encode( [
			'plugins'        => $plugins,
			'themes'         => [], // No plugin-theme dependency allowed.
			'php_extensions' => $php_extensions,
		] ) );
		$this->to_reset[] = $key;
	}

	protected function mockWooComDownloadUrls( array $urls = [] ): void {
		$key = sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/download-urls' );
		App::setVar( $key, json_encode( $urls ) );
		$this->to_reset[] = $key;
	}

	protected function mockDownloadUrl( string $url, string $response ): void {
		$key = sprintf( 'mock_%s', $url );
		App::setVar( $key, $response );
		$this->to_reset[] = $key;
	}

	protected function mockExtension( string $slug, string $type, string $version, string $from = 'wporg', ?string $source_path = null ): void {
		$cache_dir        = '/tmp/qit/cache';
		$cache_file       = $from === 'local' && $source_path ? $source_path : "$cache_dir/$type/$slug-$version.zip";
		$entrypoint       = $type === 'plugin' ? "$slug/$slug.php" : "$slug/style.css";
		$source           = $from === 'wporg' ? "https://downloads.wordpress.org/$type/$slug.zip" : ( $from === 'local' ? null : "https://qit.woo.com/downloads/$slug.zip" );
		$versioned_source = $from === 'wporg' ? "https://downloads.wordpress.org/$type/$slug.$version.zip" : ( $from === 'local' ? null : "https://qit.woo.com/downloads/$slug.zip" );

		if ( $from === 'wporg' ) {
			if ( $type === 'plugin' ) {
				$this->mockWpOrgPlugin( $slug, $version, $versioned_source );

				$zip_content = $this->createMinimalPluginZip( $slug, $version );
				$this->mockDownloadUrl( $source, $zip_content );
				$this->mockDownloadUrl( $versioned_source, $zip_content );

				$alt_versioned_source = "https://downloads.wordpress.org/$type/$slug.$version.zip";
				if ( $alt_versioned_source !== $versioned_source ) {
					$this->mockDownloadUrl( $alt_versioned_source, $zip_content );
				}
			} else {
				$this->mockWpOrgTheme( $slug, $version, $versioned_source );

				$zip_content = $this->createMinimalThemeZip( $slug, $version );
				$this->mockDownloadUrl( $source, $zip_content );
				$this->mockDownloadUrl( $versioned_source, $zip_content );

				$alt_versioned_source = "https://downloads.wordpress.org/$type/$slug.$version.zip";
				if ( $alt_versioned_source !== $versioned_source ) {
					$this->mockDownloadUrl( $alt_versioned_source, $zip_content );
				}
			}
		} elseif ( $from === 'wccom' ) {
			$this->mockWooComDownloadUrls( [ $slug => $source ] );

			$zip_content = $this->createMinimalPluginZip( $slug, $version );
			$this->mockDownloadUrl( $source, $zip_content );
		} elseif ( $from === 'url' ) {
			$this->mockDownloadUrl( $source_path, 'mocked-zip-content' );
		}

		$key = "mock_extension_$slug";
		App::setVar( $key, [
			'slug'              => $slug,
			'type'              => $type,
			'version'           => $version,
			'from'              => $from,
			'downloaded_source' => $cache_file,
			'entrypoint'        => $entrypoint,
			'source'            => $source,
		] );
		$this->to_reset[] = $key;
	}

	protected function mockStandardExtensions(): void {
		$this->mockWooComDownloadUrls( [
			'urls' => [
				'wccom-plugin-1' => [
					'slug'    => 'wccom-plugin-1',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-1.zip',
				],
				'wccom-plugin-2' => [
					'slug'    => 'wccom-plugin-2',
					'version' => '1.0.0',
					'url'     => 'https://qit.woo.com/downloads/wccom-plugin-2.zip',
				],
				'woocommerce'    => [
					'slug'    => 'woocommerce',
					'version' => '8.0.0',
					'url'     => 'https://qit.woo.com/downloads/woocommerce.zip',
				],
			],
		] );

		$this->mockExtension( 'wporg-plugin-1', 'plugin', '1.0.0', 'wporg' );
		$this->mockExtension( 'wporg-plugin-2', 'plugin', '1.0.0', 'wporg' );

		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-1.zip', $this->createMinimalPluginZip( 'wccom-plugin-1', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-2.zip', $this->createMinimalPluginZip( 'wccom-plugin-2', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/woocommerce.zip', $this->createMinimalPluginZip( 'woocommerce', '8.0.0' ) );
	}

	protected function createMinimalPluginZip( string $slug, string $version ): string {
		$filename = "{$slug}.php";
		$content  = "<?php\n/**\n * Plugin Name: " . ucwords( str_replace( '-', ' ', $slug ) ) . "\n * Version: {$version}\n */";

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


	protected function normalize_env_info( array $env_info ): array {
		if ( isset( $env_info['env_id'] ) ) {
			$env_info['env_id'] = 'ENV_ID_NORMALIZED';
		}

		$env_info['created_at'] = 1700000000;
		$env_info['domain']     = 'normalized.localhost';

		if ( isset( $env_info['sut']['source']['path'] ) && str_starts_with( $env_info['sut']['source']['path'], '/tmp/qit/qit_test_' ) ) {
			$env_info['sut']['source']['path'] = str_replace(
				$env_info['sut']['source']['path'],
				'/tmp/qit/qit_test_' . substr( $env_info['sut']['source']['path'], strpos( $env_info['sut']['source']['path'], '/tmp/qit/qit_test_' ) + 19, 13 ),
				'/tmp/qit/qit_test_normalized/' . basename( $env_info['sut']['source']['path'] )
			);
			file_put_contents( '/tmp/qit/qit_debug.log', "Normalized SUT path: {$env_info['sut']['source']['path']}\n", FILE_APPEND );
		}

		if ( isset( $env_info['sut']['source']['output'] ) && str_starts_with( $env_info['sut']['source']['output'], '/tmp/qit/' ) ) {
			$env_info['sut']['source']['output'] = '/tmp/qit/qit_test_normalized/plugin.zip';
		}

		if ( isset( $env_info['extra']['sut']['source']['path'] ) && str_starts_with( $env_info['extra']['sut']['source']['path'], '/tmp/qit/qit_test_' ) ) {
			$env_info['extra']['sut']['source']['path'] = str_replace(
				$env_info['extra']['sut']['source']['path'],
				'/tmp/qit/qit_test_' . substr( $env_info['extra']['sut']['source']['path'], strpos( $env_info['extra']['sut']['source']['path'], '/tmp/qit/qit_test_' ) + 19, 13 ),
				'/tmp/qit/qit_test_normalized/' . basename( $env_info['extra']['sut']['source']['path'] )
			);
		}

		if ( ! empty( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as &$plugin ) {
				if ( $plugin instanceof \QIT_CLI\Environment\Extension ) {
					$plugin = $plugin->jsonSerialize();
				}
				if ( is_array( $plugin ) ) {
					if ( isset( $plugin['type'] ) && $plugin['type'] === 'plugin' ) {
						if ( isset( $plugin['directory'] ) && str_starts_with( $plugin['directory'], '/' ) ) {
							if ( str_contains( $plugin['directory'], '/plugin-folder' ) ) {
								$plugin['directory'] = '/tmp-normalized/plugin-folder';
							} elseif ( str_starts_with( $plugin['directory'], '/tmp/qit/qit_test_' ) ) {
								$plugin['directory'] = '/tmp/qit/qit_test_normalized/' . basename( $plugin['directory'] );
							} else {
								$plugin['directory'] = '/tmp-normalized/plugin-folder';
							}
						}

						if ( isset( $plugin['source'] ) && str_starts_with( $plugin['source'], '/tmp/qit/qit_test_' ) ) {
							$plugin['source'] = '/tmp/qit/qit_test_normalized/' . basename( $plugin['source'] );
						}

						if ( isset( $plugin['downloaded_source'] ) && str_starts_with( $plugin['downloaded_source'], '/' ) ) {
							if ( str_contains( $plugin['downloaded_source'], '/plugin-folder' ) ) {
								$plugin['downloaded_source'] = '/tmp-normalized/plugin-folder';
							} elseif ( str_contains( $plugin['downloaded_source'], 'plugin.zip' ) ) {
								$plugin['downloaded_source'] = '/tmp-normalized/plugin.zip';
							} elseif ( str_contains( $plugin['downloaded_source'], 'woocommerce' ) ) {
								$plugin['downloaded_source'] = '/tmp-normalized/cache/plugin/woocommerce-8.0.0.zip';
							} elseif ( isset( $plugin['from'] ) && $plugin['from'] === 'url' ) {
								$plugin['downloaded_source'] = '/tmp-normalized/cache/plugin/' . $plugin['slug'] . '.zip';
							} else {
								$plugin['downloaded_source'] = sprintf(
									'/tmp-normalized/cache/plugin/%s.zip',
									$plugin['slug'] ?? 'unknown-plugin'
								);
							}
						}
					}
				} elseif ( is_string( $plugin ) && str_starts_with( $plugin, '/' ) && str_contains( $plugin, 'test-plugin' ) ) {
					$plugin = '/tmp-normalized/normalized-plugin.zip';
				}
			}
			unset( $plugin );
		}

		if ( isset( $env_info['temporary_env'] ) && str_starts_with( $env_info['temporary_env'], '/' ) ) {
			$env_info['temporary_env'] = '/tmp-normalized/default-ENV_ID_NORMALIZED';
		}

		if ( ! empty( $env_info['themes'] ) && is_array( $env_info['themes'] ) ) {
			foreach ( $env_info['themes'] as &$theme ) {
				if ( $theme instanceof \QIT_CLI\Environment\Extension ) {
					$theme = $theme->jsonSerialize();
				}
				if ( is_array( $theme ) ) {
					if ( isset( $theme['directory'] ) && str_starts_with( $theme['directory'], '/' ) ) {
						if ( str_contains( $theme['directory'], '/theme-folder' ) ) {
							$theme['directory'] = '/tmp-normalized/theme-folder';
						} elseif ( str_starts_with( $theme['directory'], '/tmp/qit/qit_test_' ) ) {
							$parts              = explode( '/', $theme['directory'] );
							$theme_name         = end( $parts );
							$theme['directory'] = '/tmp/qit/qit_test_normalized/' . $theme_name;
						} else {
							$theme['directory'] = '/tmp-normalized/theme-folder';
						}
					}

					if ( isset( $theme['source'] ) && str_starts_with( $theme['source'], '/tmp/qit/qit_test_' ) ) {
						$parts           = explode( '/', $theme['source'] );
						$theme_name      = end( $parts );
						$theme['source'] = '/tmp/qit/qit_test_normalized/' . $theme_name;
					}

					if ( isset( $theme['downloaded_source'] ) && str_starts_with( $theme['downloaded_source'], '/' ) ) {
						if ( str_contains( $theme['downloaded_source'], '/theme-folder' ) ) {
							$theme['downloaded_source'] = '/tmp-normalized/theme-folder';
						} elseif ( str_contains( $theme['downloaded_source'], 'theme.zip' ) ) {
							$theme['downloaded_source'] = '/tmp-normalized/theme.zip';
						} else {
							$theme['downloaded_source'] = sprintf(
								'/tmp-normalized/cache/theme/%s.zip',
								$theme['slug'] ?? 'unknown-theme'
							);
						}
					}
				} elseif ( is_string( $theme ) && str_starts_with( $theme, '/' ) && str_contains( $theme, 'test-theme' ) ) {
					$theme = '/tmp-normalized/normalized-theme.zip';
				}
			}
			unset( $theme );
		}

		if ( ! empty( $env_info['volumes'] ) && is_array( $env_info['volumes'] ) ) {
			$normalized_volumes = [];
			foreach ( $env_info['volumes'] as $container_path => $host_path ) {
				if ( str_starts_with( $host_path, '/' ) ) {
					$normalized_host_path = '/tmp-normalized/volume';
				} else {
					$normalized_host_path = $host_path;
				}
				$normalized_volumes[ $container_path ] = $normalized_host_path;
			}
			$env_info['volumes'] = $normalized_volumes;
		}

		if ( isset( $env_info['extra']['sut'] ) && is_array( $env_info['extra']['sut'] ) ) {
			if ( isset( $env_info['extra']['sut']['source']['path'] ) && str_starts_with( $env_info['extra']['sut']['source']['path'], '/' ) ) {
				if ( $env_info['extra']['sut']['type'] === 'plugin' ) {
					if ( str_contains( $env_info['extra']['sut']['source']['path'], 'plugin-folder' ) ) {
						$env_info['extra']['sut']['source']['path'] = '/normalized/path/plugin-folder';
					} elseif ( str_contains( $env_info['extra']['sut']['source']['path'], 'plugin.zip' ) ) {
						$env_info['extra']['sut']['source']['path'] = '/normalized/path/plugin.zip';
					}
				} elseif ( $env_info['extra']['sut']['type'] === 'theme' ) {
					if ( str_contains( $env_info['extra']['sut']['source']['path'], 'theme-folder' ) ) {
						$env_info['extra']['sut']['source']['path'] = '/normalized/path/theme-folder';
					} elseif ( str_contains( $env_info['extra']['sut']['source']['path'], 'theme.zip' ) ) {
						$env_info['extra']['sut']['source']['path'] = '/normalized/path/theme.zip';
					}
				}
			}
			if ( isset( $env_info['extra']['sut']['source']['output'] ) ) {
				if ( str_starts_with( $env_info['extra']['sut']['source']['output'], '/tmp/qit/' ) ) {
					$env_info['extra']['sut']['source']['output'] = '/tmp/qit/qit_test_normalized/plugin.zip';
				} elseif ( str_contains( $env_info['extra']['sut']['source']['output'], 'plugin.zip' ) ) {
					$env_info['extra']['sut']['source']['output'] = '/normalized/path/plugin.zip';
				}
			}
		}

		if ( isset( $env_info['test_packages'] ) && is_array( $env_info['test_packages'] ) ) {
			foreach ( $env_info['test_packages'] as &$package ) {
				if ( isset( $package['test_dir'] ) && str_starts_with( $package['test_dir'], '/' ) ) {
					$package['test_dir'] = '/normalized/path/test-dir';
				}
				if ( isset( $package['lifecycle'] ) ) {
					foreach ( $package['lifecycle'] as &$phase ) {
						foreach ( $phase as &$script ) {
							if ( isset( $script['command'] ) && str_starts_with( $script['command'], '/' ) ) {
								$script['command'] = '/normalized/path/command';
							}
						}
					}
				}
			}
		}

		return $env_info;
	}
}