<?php

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;

class EnvConfigLoaderTest extends TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	private $configDir;

	public function setUp(): void {
		$this->configDir = __DIR__ . '/../data/env-config/';
		App::setVar( 'QIT_CONFIG_LOADER_DIR', $this->configDir );
		parent::setUp();
	}

	public function tearDown(): void {
		if ( ! file_exists( '/.dockerenv' ) ) {
			throw new \RuntimeException( 'This test is not running in a docker container. It should not be run outside of a docker container for safety reasons.' );
		}

		foreach ( glob( $this->configDir . 'qit-env*' ) as $file ) {
			unlink( $file );
		}
		parent::tearDown();
	}

	private function create_config_file( $filename, $content ) {
		if ( ! file_put_contents( $this->configDir . $filename, $content ) ) {
			throw new RuntimeException( 'Could not create file.' );
		}

	}

	public function test_env_config_loader_no_file() {
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertNull( $env_config_loader->load_config() );

	}

	public function test_env_config_loader_from_json() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_loader_from_yml() {
		$this->create_config_file( 'qit-env.yml', "foo: bar" );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_loader_from_override_json() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( [ 'foo' => 'baz' ] ) );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_loader_from_override_yml() {
		$this->create_config_file( 'qit-env.yml', "foo: bar" );
		$this->create_config_file( 'qit-env.override.yml', "foo: baz" );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_exception_both_json_and_yml_exist() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.yml', "foo: baz" );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Both "qit-env.json" and "qit-env.yml" exists. Please remove one.' );
		$env_config_loader->load_config();
	}

	public function test_env_config_exception_both_json_and_yml_overrides_exist() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.yml', "foo: baz" );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Both "qit-env.override.json" and "qit-env.override.yml" exists. Please remove one.' );
		$env_config_loader->load_config();
	}

	public function test_env_config_allows_json_config_and_yml_override() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.yml', "foo: baz" );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_allows_yml_config_and_json_override() {
		$this->create_config_file( 'qit-env.yml', "foo: bar" );
		$this->create_config_file( 'qit-env.override.json', json_encode( [ 'foo' => 'baz' ] ) );
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_loader_complex_json_structure() {
		$complexStructure = [
			'database' => [
				'host'        => 'localhost',
				'port'        => 3306,
				'credentials' => [
					'username' => 'user',
					'password' => 'pass',
				],
			],
			'features' => [ 'feature1', 'feature2', 'feature3' ],
		];
		$this->create_config_file( 'qit-env.json', json_encode( $complexStructure ) );
		$envConfigLoader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $envConfigLoader->load_config() );
	}

	public function test_env_config_loader_override_complex_json_structure() {
		$baseStructure     = [
			'database' => [
				'host'        => 'localhost',
				'port'        => 3306,
				'credentials' => [
					'username' => 'user',
					'password' => 'pass',
				],
			],
			'features' => [ 'feature1', 'feature2' ],
		];
		$overrideStructure = [
			'database' => [
				'credentials' => [
					'password' => 'newpass',
				],
			],
			'features' => [ 'feature5', 'feature4' ],
		];
		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( $overrideStructure ) );
		$envConfigLoader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $envConfigLoader->load_config() );
	}

	public function test_env_config_loader_override_with_different_data_types_should_throw() {
		$baseStructure = [
			'server' => 'localhost',
		];

		$overrideStructure = [
			'server' => [ 'host' => 'remotehost', 'backup' => 'backuphost' ], // scalar to array
		];

		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( $overrideStructure ) );
		$envConfigLoader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->expectException( \InvalidArgumentException::class );
		$envConfigLoader->load_config();
	}

	public function test_env_config_loader_override_with_different_data_types_should_throw__2() {
		$baseStructure = [
			'server' => [ 'host' => 'remotehost', 'backup' => 'backuphost' ],
		];

		$overrideStructure = [
			'server' => 'localhost', // array to scalar
		];

		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( $overrideStructure ) );
		$envConfigLoader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->expectException( \InvalidArgumentException::class );
		$envConfigLoader->load_config();
	}

	public function test_env_config_loader_invalid_json_should_throw() {
		$baseStructure = 'this is not a valid JSON';

		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );
		$envConfigLoader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->expectException( \RuntimeException::class );
		$envConfigLoader->load_config();
	}

	public function test_env_config_loader_override_with_invalid_json_should_throw() {
		$baseStructure = [
			'server' => 'localhost',
		];

		$invalidOverride = "this is not valid JSON";

		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );
		$this->create_config_file( 'qit-env.override.json', $invalidOverride );
		$envConfigLoader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->expectException( \RuntimeException::class );
		$envConfigLoader->load_config();
	}
}
