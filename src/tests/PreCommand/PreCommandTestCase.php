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
		$this->temp_dir = '/tmp/qit/qit_test_' . uniqid();
		if ( ! is_dir( $this->temp_dir ) ) {
			mkdir( $this->temp_dir, 0777, true );
			chmod( $this->temp_dir, 0777 );
		}
		file_put_contents( '/tmp/qit/debug.log', "PreCommandTest temp_dir set to: {$this->temp_dir}\n", FILE_APPEND );
	}

	protected function tearDown(): void {
		parent::tearDown();
		foreach ( $this->to_delete as $file ) {
			if ( is_dir( $file ) ) {
				$this->rmdir_recursive( $file );
			} else {
				@unlink( $file );
			}
		}
		$this->to_delete = [];
		if ( ! getenv( 'QIT_TESTING_ENV_INFO' ) ) {
			// Add environment cleanup if needed
		}
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

		$config_path = $this->temp_dir . '/qit_' . uniqid() . '.json';
		file_put_contents( $config_path, json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		$this->to_delete[] = $config_path;

		putenv( 'QIT_TESTING_ENV_INFO=1' );
		$command = App::make( UpEnvironmentCommand::class );
		$input   = new ArrayInput( array_merge( [ '--config' => $config_path ], $cli_args ) );
		$input->bind( $command->getDefinition() );
		$output = new BufferedOutput();

		try {
			$return_code   = $command->execute( $input, $output );
			$output_string = $output->fetch();
			if ( $expect_failure ) {
				$this->fail( 'Expected an exception but none was thrown' );
			}
			$this->assertEquals( 0, $return_code, 'Command failed: ' . $output_string );
		} catch ( \RuntimeException $e ) {
			if ( $expect_failure ) {
				return $e->getMessage();
			}
			throw $e;
		} finally {
			putenv( 'QIT_TESTING_ENV_INFO' );
		}

		// Split output into lines and find the first valid JSON
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
	}

	protected function normalize_env_info( array $env_info ): array {
		if ( isset( $env_info['env_id'] ) ) {
			$original_env_id    = $env_info['env_id'];
			$env_info['env_id'] = 'ENV_ID_NORMALIZED';
			if ( isset( $env_info['temporary_env'] ) ) {
				$env_info['temporary_env'] = str_replace( $original_env_id, 'ENV_ID_NORMALIZED', $env_info['temporary_env'] );
			}
		}

		$env_info['created_at'] = 1700000000;
		$env_info['domain']     = 'normalized.localhost';

		$real_temp_dir = realpath( '/tmp/qit' );
		if ( $real_temp_dir ) {
			$real_temp_dir = rtrim( $real_temp_dir, '/' );
			$env_info      = json_decode( str_replace(
				[ $real_temp_dir . '/', $real_temp_dir ],
				'/tmp-normalized/',
				json_encode( $env_info )
			), true );
		}

		if ( isset( $env_info['temporary_env'] ) ) {
			$env_info['temporary_env'] = preg_replace(
				'/_qit_config-qit_custom_tests_[a-f0-9]+/',
				'_qit_config-normalized',
				$env_info['temporary_env']
			);
		}

		if ( ! empty( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as &$plugin ) {
				if ( $plugin instanceof Extension ) {
					$plugin = $plugin->jsonSerialize();
				}
				if ( is_array( $plugin ) ) {
					// Preserve array structure
				} elseif ( is_string( $plugin ) ) {
					if ( preg_match( '/^\/tmp-normalized\/+q[a-f0-9]+\/test-plugin_[a-f0-9]+\.zip$/i', $plugin ) ) {
						$plugin = '/tmp-normalized/normalized-plugin.zip';
					}
				} else {
					throw new \RuntimeException( 'Plugin must be a string or array, got ' . gettype( $plugin ) );
				}
			}
			unset( $plugin );
		}

		if ( ! empty( $env_info['themes'] ) && is_array( $env_info['themes'] ) ) {
			foreach ( $env_info['themes'] as &$theme ) {
				if ( $theme instanceof Extension ) {
					$theme = $theme->jsonSerialize();
				}
				if ( is_array( $theme ) ) {
					// Preserve array structure
				} elseif ( is_string( $theme ) ) {
					if ( preg_match( '/^\/tmp-normalized\/+qit_test_[a-f0-9]+\/test-theme-[a-f0-9]+\.zip$/i', $theme ) ) {
						$theme = '/tmp-normalized/normalized-theme.zip';
					}
				} else {
					throw new \RuntimeException( 'Theme must be a string or array, got ' . gettype( $theme ) );
				}
			}
			unset( $theme );
		}

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

		if ( isset( $env_info['sut'] ) && is_array( $env_info['sut'] ) ) {
			if ( isset( $env_info['sut']['source']['path'] ) ) {
				$env_info['sut']['source']['path'] = str_replace(
					realpath( dirname( $env_info['sut']['source']['path'] ) ) . '/',
					'/normalized/path/',
					$env_info['sut']['source']['path']
				);
			}
			if ( isset( $env_info['sut']['source']['output'] ) ) {
				$env_info['sut']['source']['output'] = str_replace(
					realpath( dirname( $env_info['sut']['source']['output'] ) ) . '/',
					'/normalized/path/',
					$env_info['sut']['source']['output']
				);
			}
		}

		if ( isset( $env_info['test_packages'] ) && is_array( $env_info['test_packages'] ) ) {
			foreach ( $env_info['test_packages'] as &$package ) {
				if ( isset( $package['test_dir'] ) ) {
					$package['test_dir'] = str_replace(
						realpath( dirname( $package['test_dir'] ) ) . '/',
						'/normalized/path/',
						$package['test_dir']
					);
				}
				if ( isset( $package['lifecycle'] ) ) {
					foreach ( $package['lifecycle'] as &$phase ) {
						foreach ( $phase as &$script ) {
							if ( isset( $script['command'] ) ) {
								$script['command'] = str_replace(
									realpath( dirname( $script['command'] ) ) . '/',
									'/normalized/path/',
									$script['command']
								);
							}
						}
					}
				}
			}
		}

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
}