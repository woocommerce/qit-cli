<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait PreCommandTestTrait {
	protected $temp_dir;
	protected $to_delete = [];

	public function setUp(): void {
		parent::setUp();
		$this->temp_dir = sys_get_temp_dir() . '/qit_test_' . uniqid();
		mkdir( $this->temp_dir );
	}

	protected function tearDown(): void {
		parent::tearDown();
		foreach ( $this->to_delete as $file ) {
			@unlink( $file );
		}
		$this->to_delete = [];
	}

	protected function run_unit_test( array $config, array $cli_args = [], bool $expect_failure = false ) {
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
			$this->assertEquals( 0, $return_code );
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

		$real_temp_dir = realpath( sys_get_temp_dir() );
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
				if ( is_array( $plugin ) && isset( $plugin['slug'] ) ) {
					$plugin = $plugin['slug'];
				}
				if ( ! is_string( $plugin ) ) {
					throw new \RuntimeException( 'Plugin must be a string, got ' . gettype( $plugin ) );
				}
				if ( preg_match( '/^\/tmp-normalized\/+qit_test_[a-f0-9]+\/test-plugin-[a-f0-9]+\.zip$/i', $plugin ) ) {
					$plugin = '/tmp-normalized/normalized-plugin.zip';
				}
			}
			unset( $plugin );
		}

		if ( ! empty( $env_info['themes'] ) && is_array( $env_info['themes'] ) ) {
			foreach ( $env_info['themes'] as &$theme ) {
				if ( ! is_string( $theme ) ) {
					throw new \RuntimeException( 'Theme must be a string, got ' . gettype( $theme ) );
				}
				if ( preg_match( '/^\/tmp-normalized\/+qit_test_[a-f0-9]+\/test-theme-[a-f0-9]+\.zip$/i', $theme ) ) {
					$theme = '/tmp-normalized/normalized-theme.zip';
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