<?php

namespace QIT_CLI_Tests\PreCommand2;

use QIT_CLI\App;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class PreCommandTestCase extends TestCase {
	protected $temp_dir;
	protected $to_delete = [];

	protected function setUp(): void {
		parent::setUp();
		// Use /tmp/qit explicitly to ensure writability
		$this->temp_dir = '/tmp/qit/qit_test_' . uniqid();
		if ( ! is_dir( $this->temp_dir ) ) {
			mkdir( $this->temp_dir, 0777, true );
			chmod( $this->temp_dir, 0777 );
		}
		// Debug temp_dir
		file_put_contents( '/tmp/qit/debug.log', "PreCommandTest temp_dir set to: {$this->temp_dir}\n", FILE_APPEND );
	}

	protected function tearDown(): void {
		parent::tearDown();
		foreach ( $this->to_delete as $file ) {
			@unlink( $file );
		}
		$this->to_delete = [];
		// Skip environment cleanup to avoid 'tests' environment error
		if ( ! getenv( 'QIT_TESTING_ENV_INFO' ) ) {
			// Add environment cleanup logic here if needed
		}
	}

	protected function run_unit_test( array $config, array $cli_args = [], bool $expect_failure = false ) {
		// Debug temp_dir before file operation
		file_put_contents( '/tmp/qit/debug.log', "run_unit_test temp_dir: {$this->temp_dir}\n", FILE_APPEND );
		if ( empty( $this->temp_dir ) || ! is_dir( $this->temp_dir ) ) {
			throw new \RuntimeException( "temp_dir is not initialized. Ensure parent::setUp() is called in setUp." );
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
			$return_code = $command->execute( $input, $output );
			if ( $expect_failure ) {
				$this->fail( 'Expected an exception but none was thrown' );
			}
			try {
				$this->assertEquals( 0, $return_code );
			} catch ( \Exception $e ) {
				$this->fail( 'Command failed with return code: ' . $return_code . ' Output: ' . $output->fetch() );
			}
		} catch ( \RuntimeException $e ) {
			if ( $expect_failure ) {
				return $e->getMessage();
			}
			throw $e;
		} finally {
			putenv( 'QIT_TESTING_ENV_INFO' );
		}

		$output_string = $output->fetch();
		$env_info      = json_decode( $output_string, true );
		$this->assertIsArray( $env_info, "Invalid JSON output: $output_string" );

		return $this->normalize_env_info( $env_info );
	}

	protected function normalize_env_info( array $env_info ): array {
		$original_env_id = isset( $env_info['env_id'] ) ? $env_info['env_id'] : null;
		if ( $original_env_id ) {
			$env_info['env_id']        = 'ENV_ID_NORMALIZED';
			$env_info['temporary_env'] = str_replace( $original_env_id, 'ENV_ID_NORMALIZED', $env_info['temporary_env'] );
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
				if ( is_object( $plugin ) ) {
					// Handle Extension objects by serializing
					$plugin = $plugin->jsonSerialize();
				}
				if ( is_array( $plugin ) ) {
					// Preserve all plugin arrays, regardless of structure
					// No modifications to array contents
				} elseif ( is_string( $plugin ) ) {
					// Normalize zip file paths for string plugins
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
				if ( is_object( $theme ) ) {
					// Handle Extension objects by serializing
					$theme = $theme->jsonSerialize();
				}
				if ( is_array( $theme ) ) {
					// Preserve all theme arrays, regardless of structure
					// No modifications to array contents
				} elseif ( is_string( $theme ) ) {
					// Normalize zip file paths for string themes
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

		return $env_info;
	}
}