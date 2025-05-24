<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class EnvInfoConstructionTest extends TestCase {
	use MatchesSnapshots;

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

	public function test_basic_config_array_of_strings() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'     => [ 'woocommerce', 'wordpress-importer' ],
					'themes'      => [ 'storefront', 'twentytwentyone' ],
					'wp_version'  => 'stable',
					'php_version' => '8.2'
				]
			]
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'wordpress-importer', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'storefront', $env_info['themes'] ) );
		$this->assertTrue( in_array( 'twentytwentyone', $env_info['themes'] ) );
		$this->assertEquals( 'latest', $env_info['wp_version'] );
		$this->assertEquals( '8.2', $env_info['php_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_associative_plugins_config() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'wordpress-importer' ],
					'themes'  => [ 'twentytwentyone' ]
				]
			]
		];

		$cli_args = [
			'--plugin' => [ 'woocommerce', 'wordpress-importer' ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'wordpress-importer', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_wp_and_woo_versions() {
		$config = [
			'environments' => [
				'default' => [
					'wp_version'  => 'latest',
					'woo_version' => 'stable',
					'plugins'     => [ 'woocommerce' ]
				]
			]
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertEquals( 'latest', $env_info['wp_version'] );
		$this->assertEquals( 'stable', $env_info['woo_version'] );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_cli_overrides() {
		$config = [
			'environments' => [
				'default' => [
					'plugins'     => [ 'woocommerce' ],
					'themes'      => [ 'storefront' ],
					'wp_version'  => '6.0',
					'php_version' => '7.4'
				]
			]
		];

		$cli_args = [
			'--wp_version'  => '6.1',
			'--php_version' => '8.0',
			'--plugin'      => [ 'wordpress-importer' ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertEquals( '6.1', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'wordpress-importer', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'storefront', $env_info['themes'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_plugin_dependencies() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_extension_set_resolution() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_environment_variables() {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "HELLO=world\nFOO=bar" );
		$this->to_delete[] = $env_file;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$cli_args = [
			'--env'      => [ 'DB_NAME=wp_test' ],
			'--env_file' => [ $env_file ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'wp_test', $env_info['env']['DB_NAME'] );
		$this->assertEquals( 'world', $env_info['env']['HELLO'] );
		$this->assertEquals( 'bar', $env_info['env']['FOO'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_local_paths_and_urls() {
		$plugin_zip = $this->temp_dir . '/test-plugin-' . uniqid() . '.zip';
		$theme_zip  = $this->temp_dir . '/test-theme-' . uniqid() . '.zip';
		file_put_contents( $plugin_zip, 'fake plugin contents' );
		file_put_contents( $theme_zip, 'fake theme contents' );
		$this->to_delete[] = $plugin_zip;
		$this->to_delete[] = $theme_zip;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'themes'  => [ 'storefront' ]
				]
			]
		];

		$cli_args = [
			'--plugin' => [ $plugin_zip ],
			'--theme'  => [ $theme_zip ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( '/tmp-normalized/normalized-plugin.zip', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( '/tmp-normalized/normalized-theme.zip', $env_info['themes'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_skip_activation_flags() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'themes'  => [ 'storefront' ]
				]
			]
		];

		$cli_args = [
			'--skip_activating_plugins' => true,
			'--skip_activating_themes'  => true
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( $env_info['skip_activating_plugins'] );
		$this->assertTrue( $env_info['skip_activating_themes'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_mixed_config_and_cli_plugin_override() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'my-plugin' ],
					'themes'  => [ 'storefront' ]
				]
			]
		];

		$cli_args = [
			'--plugin' => [ 'woocommerce', 'contact-form-7' ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'my-plugin', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'contact-form-7', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_theme_configuration() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'themes'  => [ 'storefront' ]
				]
			]
		];

		$cli_args = [
			'--theme' => [ 'twentytwentyone' ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( in_array( 'storefront', $env_info['themes'] ) );
		$this->assertTrue( in_array( 'twentytwentyone', $env_info['themes'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_env_var_from_cli() {
		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$cli_args = [
			'--env' => [ 'DB_NAME=wp_test' ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( 'wp_test', $env_info['env']['DB_NAME'] );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_env_var_from_file() {
		$env_file = $this->temp_dir . '/test_' . uniqid() . '.env';
		file_put_contents( $env_file, "HELLO=world\nFOO=bar" );
		$this->to_delete[] = $env_file;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$cli_args = [
			'--env_file' => [ $env_file ]
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
					'plugins' => [ 'woocommerce' ]
				]
			]
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'env', $env_info );
		$this->assertEquals( '/qit/wp-cli.yml', $env_info['env']['WP_CLI_CONFIG_PATH'] );
		$this->assertCount( 1, $env_info['env'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_local_plugin_path() {
		$plugin_zip = $this->temp_dir . '/test-plugin-' . uniqid() . '.zip';
		file_put_contents( $plugin_zip, 'fake plugin contents' );
		$this->to_delete[] = $plugin_zip;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', $plugin_zip ]
				]
			]
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( '/tmp-normalized/normalized-plugin.zip', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertEquals( count( $env_info['plugins'] ), count( array_unique( array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}