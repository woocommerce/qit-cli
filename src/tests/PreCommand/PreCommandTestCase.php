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

	protected function setUp(): void {
		parent::setUp();
		try {
			$this->temp_dir = '/tmp/qit/qit_test_' . uniqid();
			if ( ! is_dir( $this->temp_dir ) ) {
				mkdir( $this->temp_dir, 0777, true );
				chmod( $this->temp_dir, 0777 );
			}
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

	protected function run_unit_test( array $config, array $cli_args = [], bool $expect_failure = false ) {
		if ( empty( $this->temp_dir ) || ! is_dir( $this->temp_dir ) ) {
			throw new \RuntimeException( 'temp_dir is not initialized. Ensure parent::setUp() is called.' );
		}

		try {
			$config_path = $this->temp_dir . '/qit_' . uniqid() . '.json';
			file_put_contents( $config_path, json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			file_put_contents( '/tmp/qit/qit_debug.log', 'Test Config: ' . json_encode( $config, JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );
			$this->to_delete[] = $config_path;

			putenv( 'QIT_TESTING_ENV_INFO=1' );
			$command = App::make( UpEnvironmentCommand::class );
			$input   = new ArrayInput( array_merge( [ '--config' => $config_path ], $cli_args ) );
			$input->bind( $command->getDefinition() );
			$output = new BufferedOutput();

			$return_code   = $command->execute( $input, $output );
			$output_string = $output->fetch();
			file_put_contents( '/tmp/qit/qit_debug.log', "run_unit_test: Command executed with return code $return_code, output: $output_string\n", FILE_APPEND );

			if ( $expect_failure ) {
				if ( $return_code === 0 ) {
					$this->fail( 'Expected command failure but it succeeded' );
				}
				if ( preg_match( '/<error>(.*?)<\/error>/', $output_string, $matches ) ) {
					return [ 'exit_code' => $return_code, 'output' => $matches[1] ];
				}

				return [ 'exit_code' => $return_code, 'output' => $output_string ];
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

			return $this->normalize_env_info( $env_info );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: run_unit_test exception: " . $e->getMessage() . "\n", FILE_APPEND );
			throw $e;
		}
	}

	protected function normalize_env_info( array $env_info ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: Normalizing env_info: " . print_r( $env_info, true ) . "\n", FILE_APPEND );

		// Normalize env_id
		if ( isset( $env_info['env_id'] ) ) {
			$env_info['env_id'] = 'ENV_ID_NORMALIZED';
		}

		// Normalize created_at and domain
		$env_info['created_at'] = 1700000000;
		$env_info['domain']     = 'normalized.localhost';

		// Normalize plugin paths
		if ( ! empty( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as &$plugin ) {
				if ( $plugin instanceof \QIT_CLI\Environment\Extension ) {
					$plugin = $plugin->jsonSerialize();
				}
				if ( is_array( $plugin ) ) {
					// Debug logging before normalization
					file_put_contents(
						'/tmp/qit/qit_debug.log',
						sprintf(
							"Before normalization: slug=%s, type=%s, from=%s, directory=%s, downloaded_source=%s, source=%s\n",
							$plugin['slug'] ?? 'null',
							$plugin['type'] ?? 'null',
							$plugin['from'] ?? 'null',
							$plugin['directory'] ?? 'null',
							$plugin['downloaded_source'] ?? 'null',
							$plugin['source'] ?? 'null'
						),
						FILE_APPEND
					);

					// Only normalize for plugins
					if ( isset( $plugin['type'] ) && $plugin['type'] === 'plugin' ) {
						// Normalize directory
						if ( isset( $plugin['directory'] ) && str_starts_with( $plugin['directory'], '/' ) && str_contains( $plugin['directory'], '/plugin-folder' ) ) {
							$plugin['directory'] = '/tmp-normalized/plugin-folder';
						}

						// Normalize source
						if ( isset( $plugin['source'] ) && str_starts_with( $plugin['source'], '/' ) && str_contains( $plugin['source'], 'plugin.zip' ) ) {
							$plugin['source'] = '/tmp-normalized/plugin.zip';
						}

						// Normalize downloaded_source for all plugins
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

					// Debug logging after normalization
					file_put_contents(
						'/tmp/qit/qit_debug.log',
						sprintf(
							"After normalization: slug=%s, type=%s, from=%s, directory=%s, downloaded_source=%s, source=%s\n",
							$plugin['slug'] ?? 'null',
							$plugin['type'] ?? 'null',
							$plugin['from'] ?? 'null',
							$plugin['directory'] ?? 'null',
							$plugin['downloaded_source'] ?? 'null',
							$plugin['source'] ?? 'null'
						),
						FILE_APPEND
					);
				} elseif ( is_string( $plugin ) && str_starts_with( $plugin, '/' ) && str_contains( $plugin, 'test-plugin' ) ) {
					$plugin = '/tmp-normalized/normalized-plugin.zip';
				}
			}
			unset( $plugin );
		}

		// Normalize temporary environment path
		if ( isset( $env_info['temporary_env'] ) && str_starts_with( $env_info['temporary_env'], '/' ) ) {
			$env_info['temporary_env'] = '/tmp-normalized/default-ENV_ID_NORMALIZED';
		}

		// Normalize theme paths
		if ( ! empty( $env_info['themes'] ) && is_array( $env_info['themes'] ) ) {
			foreach ( $env_info['themes'] as &$theme ) {
				if ( $theme instanceof \QIT_CLI\Environment\Extension ) {
					$theme = $theme->jsonSerialize();
				}
				if ( is_string( $theme ) && str_starts_with( $theme, '/' ) && str_contains( $theme, 'test-theme' ) ) {
					$theme = '/tmp-normalized/normalized-theme.zip';
				}
			}
			unset( $theme );
		}

		// Normalize volumes
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

		// Normalize SUT paths
		if ( isset( $env_info['extra']['sut'] ) && is_array( $env_info['extra']['sut'] ) ) {
			if ( isset( $env_info['extra']['sut']['path'] ) ) {
				if ( str_contains( $env_info['extra']['sut']['path'], 'plugin-folder' ) ) {
					$env_info['extra']['sut']['path'] = '/normalized/path/plugin-folder';
				} elseif ( str_contains( $env_info['extra']['sut']['path'], 'plugin.zip' ) ) {
					$env_info['extra']['sut']['path'] = '/normalized/path/plugin.zip';
				}
			}
			// Fix: Normalize sut.source.output
			if ( isset( $env_info['extra']['sut']['source']['output'] ) && str_contains( $env_info['extra']['sut']['source']['output'], 'plugin.zip' ) ) {
				$env_info['extra']['sut']['source']['output'] = '/normalized/path/plugin.zip';
			}
		}

		// Normalize test packages
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

		file_put_contents( '/tmp/qit/qit_debug.log', "PreCommandTest: Normalized env_info: " . print_r( $env_info, true ) . "\n", FILE_APPEND );

		return $env_info;
	}

	protected function mockWpOrgPlugin( string $slug, string $version, string $download_link, array $requires_plugins = [] ): void {
		App::setVar(
			sprintf( 'mock_%s', "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$slug}" ),
			json_encode( [
				'slug'             => $slug,
				'version'          => $version,
				'download_link'    => $download_link,
				'requires_plugins' => $requires_plugins,
			] )
		);
	}

	protected function mockWpOrgTheme( string $slug, string $version, string $download_link ): void {
		App::setVar(
			sprintf( 'mock_%s', "https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]={$slug}" ),
			json_encode( [
				'slug'          => $slug,
				'version'       => $version,
				'download_link' => $download_link,
			] )
		);
	}

	protected function mockWooComDependencies( array $plugins = [], array $themes = [], array $php_extensions = [] ): void {
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => $plugins,
				'themes'         => $themes,
				'php_extensions' => $php_extensions,
			] )
		);
	}

	protected function mockWooComDownloadUrls( array $urls = [] ): void {
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ),
			json_encode( $urls )
		);
	}

	protected function mockDownloadUrl( string $url, string $response ): void {
		App::setVar(
			sprintf( 'mock_%s', $url ),
			$response
		);
	}

	protected function mockExtension( string $slug, string $type, string $version, string $from = 'wporg', ?string $source_path = null ): void {
		$cache_dir  = '/tmp/qit/cache';
		$cache_file = $from === 'local' && $source_path ? $source_path : "$cache_dir/$type/$slug-$version.zip";
		$entrypoint = $type === 'plugin' ? "$slug/$slug.php" : "$slug/style.css";
		$source     = $from === 'wporg' ? "https://downloads.wordpress.org/$type/$slug.$version.zip" : ( $from === 'local' ? null : "https://qit.woo.com/downloads/$slug.zip" );

		if ( $from === 'wporg' ) {
			if ( $type === 'plugin' ) {
				$this->mockWpOrgPlugin( $slug, $version, $source );
			} else {
				$this->mockWpOrgTheme( $slug, $version, $source );
			}
		} elseif ( $from === 'wccom' ) {
			$this->mockWooComDownloadUrls( [ $slug => $source ] );
		} elseif ( $from === 'url' ) {
			$this->mockDownloadUrl( $source_path, 'mocked-zip-content' );
		}

		App::setVar( "mock_extension_$slug", [
			'slug'              => $slug,
			'type'              => $type,
			'version'           => $version,
			'from'              => $from,
			'downloaded_source' => $cache_file,
			'entrypoint'        => $entrypoint,
			'source'            => $source,
		] );
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

		$this->mockWpOrgPlugin(
			'wporg-plugin-1',
			'1.0.0',
			'https://downloads.wordpress.org/plugin/wporg-plugin-1.zip'
		);
		$this->mockWpOrgPlugin(
			'wporg-plugin-2',
			'1.0.0',
			'https://downloads.wordpress.org/plugin/wporg-plugin-2.zip'
		);

		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-1.zip', $this->createMinimalPluginZip( 'wccom-plugin-1', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/wccom-plugin-2.zip', $this->createMinimalPluginZip( 'wccom-plugin-2', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://qit.woo.com/downloads/woocommerce.zip', $this->createMinimalPluginZip( 'woocommerce', '8.0.0' ) );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/wporg-plugin-1.zip', $this->createMinimalPluginZip( 'wporg-plugin-1', '1.0.0' ) );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/wporg-plugin-2.zip', $this->createMinimalPluginZip( 'wporg-plugin-2', '1.0.0' ) );
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
}