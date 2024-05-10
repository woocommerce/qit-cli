<?php

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Environment\EnvConfigLoader;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class EnvConfigLoaderTest extends TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	private $configDir;

	/** @var EnvConfigLoader */
	private $sut;

	public function setUp(): void {
		parent::setUp();
		$this->configDir = __DIR__ . '/../data/env-config/';
		App::setVar( 'QIT_CONFIG_LOADER_DIR', $this->configDir );

		App::container()->when( EnvConfigLoader::class )
		   ->needs( OutputInterface::class )
		   ->give( NullOutput::class );

		$this->sut = App::make( EnvConfigLoader::class );
	}

	public function tearDown(): void {
		if ( ! file_exists( '/.dockerenv' ) ) {
			throw new \RuntimeException( 'This test is not running in a docker container. It should not be run outside of a docker container for safety reasons.' );
		}

		foreach ( glob( $this->configDir . '{.qit-env*,qit-env*}', GLOB_BRACE ) as $file ) {
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
		$this->assertEquals( [], $this->sut->load_config() );
	}

	public function test_env_config_loader_from_json() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
	}

	public function test_env_config_loader_from_yml() {
		$this->create_config_file( 'qit-env.yml', "foo: bar" );

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
	}

	public function test_env_config_loader_from_override_json() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( [ 'foo' => 'baz' ] ) );

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
	}

	public function test_env_config_loader_from_override_yml() {
		$this->create_config_file( 'qit-env.yml', "foo: bar" );
		$this->create_config_file( 'qit-env.override.yml', "foo: baz" );

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
	}

	public function test_env_config_exception_both_json_and_yml_exist() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.yml', "foo: baz" );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'More than one "qit-env" file exists. Please remove one.' );
		$this->sut->load_config();
	}

	public function test_env_config_exception_both_json_and_dot_json_exists() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( '.qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'More than one "qit-env" file exists. Please remove one.' );
		$this->sut->load_config();
	}

	public function test_env_config_exception_both_json_and_yml_overrides_exist() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.yml', "foo: baz" );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'More than one "qit-env.override" file exists. Please remove one.' );
		$this->sut->load_config();
	}

	public function test_env_config_allows_json_config_and_yml_override() {
		$this->create_config_file( 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->create_config_file( 'qit-env.override.yml', "foo: baz" );

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
	}

	public function test_env_config_allows_yml_config_and_json_override() {
		$this->create_config_file( 'qit-env.yml', "foo: bar" );
		$this->create_config_file( 'qit-env.override.json', json_encode( [ 'foo' => 'baz' ] ) );

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
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

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
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

		$this->assertMatchesJsonSnapshot( $this->sut->load_config() );
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

		$this->expectException( \InvalidArgumentException::class );
		$this->sut->load_config();
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

		$this->expectException( \InvalidArgumentException::class );
		$this->sut->load_config();
	}

	public function test_env_config_loader_invalid_json_should_throw() {
		$baseStructure = 'this is not a valid JSON';

		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );

		$this->expectException( \RuntimeException::class );
		$this->sut->load_config();
	}

	public function test_env_config_loader_override_with_invalid_json_should_throw() {
		$baseStructure = [
			'server' => 'localhost',
		];

		$invalidOverride = "this is not valid JSON";

		$this->create_config_file( 'qit-env.json', json_encode( $baseStructure ) );
		$this->create_config_file( 'qit-env.override.json', $invalidOverride );

		$this->expectException( \RuntimeException::class );
		$this->sut->load_config();
	}

	public function test_env_config_loader_plugins_array() {
		$complexStructure = [
			'plugins' => [
				'qit-beaver' => [
					'test_tags' => [ 'bar' ],
				],
			],
		];
		$this->create_config_file( 'qit-env.json', json_encode( $complexStructure ) );

		$this->assertMatchesJsonSnapshot( json_encode( $this->normalized_env_info( $this->sut->load_config() ) ) );
	}

	public function test_env_config_loader_plugins_string() {
		$complexStructure = [
			'plugins' => [
				'qit-beaver',
			],
		];
		$this->create_config_file( 'qit-env.json', json_encode( $complexStructure ) );

		$this->assertMatchesJsonSnapshot( json_encode( $this->normalized_env_info( $this->sut->load_config() ) ) );
	}

	public function test_env_config_loader_plugins_mixed() {
		$complexStructure = [
			'plugins' => [
				'qit-cat' => [
					'test_tags' => [ 'bar' ],
				],
				'qit-beaver',
			],
			'themes'  => [
				'qit-dog',
				'foo-extension' => [
					'test_tags' => [ 'foo' ],
				],
			],
		];
		$this->create_config_file( 'qit-env.json', json_encode( $complexStructure ) );

		$this->assertMatchesJsonSnapshot( json_encode( $this->normalized_env_info( $this->sut->load_config() ) ) );
	}

	public function test_env_config_loader_plugins_override() {
		$override_structure = [
			'plugins' => [
				'qit-beaver'    => [
					'test_tags' => [ 'array_override' ],
				],
				'qit-dog'       => [
					'source' => 'source_from_value',
				],
				'https://woo.com/qit-beaver',
				'foo-extension' => [
					'source'    => 'https://woo.com/foo-extension',
					'test_tags' => [ 'test_tag_array' ],
				],
				// 'https://woo.com/qit-dog:bar', <!-- Forbidden because we can't infer the "slug" to get the "bar" test tag. -->
				'{"source": "https://woo.com/qit-dog", "slug": "qit-dog", "test_tags": ["bar"]}',
				'C:\\Users\\user\\Desktop\\qit-beaver',
				'C:\\Users\\user\\Desktop\\qit-beaver:activate',
			],
			'themes'  => [
				'qit-beaver:test',
			],
		];
		$this->create_config_file( 'qit-env.json', json_encode( [] ) );

		$this->assertMatchesJsonSnapshot( json_encode( $this->normalized_env_info( $this->sut->load_config(), $override_structure ) ) );
	}

	public function test_env_config_loader_plugins_url_with_source() {
		$this->create_config_file( 'qit-env.json', json_encode( [] ) );
		$this->assertMatchesJsonSnapshot(
			json_encode(
				$this->normalized_env_info( $this->sut->load_config(), [
					'plugins' => [
						'qit-beaver' => [
							'source' => 'https://woo.com/qit-beaver',
						],
					],
				] )
			)
		);
	}

	public function test_env_config_loader_plugins_url_with_source_and_tags() {
		$this->create_config_file( 'qit-env.json', json_encode( [] ) );
		$this->assertMatchesJsonSnapshot(
			json_encode(
				$this->normalized_env_info( $this->sut->load_config(), [
					'plugins' => [
						'qit-beaver' => [
							'source'    => 'https://woo.com/qit-beaver',
							'test_tags' => [ 'rc' ],
						],
					],
				] )
			)
		);
	}

	public function test_env_config_loader_plugins_url_with_source_json() {
		$this->create_config_file( 'qit-env.json', json_encode( [] ) );
		$this->assertMatchesJsonSnapshot(
			json_encode(
				$this->normalized_env_info( $this->sut->load_config(), [
					'plugins' => [
						'{"source":"https://woo.com/qit-beaver", "slug":"qit-beaver"}',
					],
				] )
			)
		);
	}

	public function test_env_config_loader_plugins_url_with_source_and_tags_json() {
		$this->create_config_file( 'qit-env.json', json_encode( [] ) );
		$this->assertMatchesJsonSnapshot(
			json_encode(
				$this->normalized_env_info( $this->sut->load_config(), [
					'plugins' => [
						'{"source":"https://woo.com/qit-beaver", "slug":"qit-beaver", "test_tags": ["rc"]}',
					],
				] )
			)
		);
	}

	protected function normalized_env_info( array $defaults, array $overrides = [] ): EnvInfo {
		$env_info = App::make( EnvConfigLoader::class )->init_env_info( [ 'defaults' => $defaults, 'overrides' => $overrides ] );

		$original_env_id         = $env_info->env_id;
		$normalized_env_id       = '123456';
		$env_info->env_id        = $normalized_env_id;
		$env_info->temporary_env = str_replace( $original_env_id, $normalized_env_id, $env_info->temporary_env );
		$env_info->created_at    = 1711651749;

		return $env_info;
	}
}
