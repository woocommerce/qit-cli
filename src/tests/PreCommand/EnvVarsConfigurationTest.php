<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class EnvVarsConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function test_config_envs_only() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'envs'    => [
						'QIT_DEBUG' => 'true',
						'APP_ENV'   => 'test',
					],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'true', $env_info['env']['QIT_DEBUG'] );
		$this->assertEquals( 'test', $env_info['env']['APP_ENV'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_cli_env_only() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$cli_args = [
			'--env' => [ 'DB_NAME=wp_test' ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'wp_test', $env_info['env']['DB_NAME'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_env_file_only() {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "HELLO=world\nFOO=bar" );
		$this->to_delete[] = $env_file;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$cli_args = [
			'--env_file' => [ $env_file ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'world', $env_info['env']['HELLO'] );
		$this->assertEquals( 'bar', $env_info['env']['FOO'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_default_env_vars() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertCount( 1, $env_info['env'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_merge_config_and_cli() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'envs'    => [
						'QIT_DEBUG'  => 'true',
						'SHARED_VAR' => 'config',
					],
				],
			],
		];

		$cli_args = [
			'--env' => [ 'SHARED_VAR=cli', 'CLI_VAR=cli' ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'true', $env_info['env']['QIT_DEBUG'] );
		$this->assertEquals( 'cli', $env_info['env']['SHARED_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['CLI_VAR'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_merge_config_and_env_file() {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "FILE_VAR=file\nSHARED_VAR=file" );
		$this->to_delete[] = $env_file;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'envs'    => [
						'QIT_DEBUG'  => 'true',
						'SHARED_VAR' => 'config',
					],
				],
			],
		];

		$cli_args = [
			'--env_file' => [ $env_file ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'true', $env_info['env']['QIT_DEBUG'] );
		$this->assertEquals( 'file', $env_info['env']['SHARED_VAR'] );
		$this->assertEquals( 'file', $env_info['env']['FILE_VAR'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_merge_all_sources() {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "FILE_VAR=file\nSHARED_VAR=file" );
		$this->to_delete[] = $env_file;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'envs'    => [
						'CONFIG_VAR' => 'config',
						'SHARED_VAR' => 'config',
					],
				],
			],
		];

		$cli_args = [
			'--env'      => [ 'CLI_VAR=cli', 'SHARED_VAR=cli' ],
			'--env_file' => [ $env_file ],
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'config', $env_info['env']['CONFIG_VAR'] );
		$this->assertEquals( 'file', $env_info['env']['FILE_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['CLI_VAR'] );
		$this->assertEquals( 'cli', $env_info['env']['SHARED_VAR'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}