<?php

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;

class EnvConfigLoaderTest extends TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	protected $to_delete = [];

	public function tearDown(): void {
		foreach ( $this->to_delete as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}

		parent::tearDown();
	}

	public function test_env_config_loader_from_json() {
		App::setVar( 'QIT_CONFIG_LOADER_DIR', __DIR__ . '/../data/env-config/' );
		file_put_contents( App::getVar( 'QIT_CONFIG_LOADER_DIR' ) . 'qit-env.json', json_encode( [ 'foo' => 'bar' ] ) );
		$this->to_delete[] = App::getVar( 'QIT_CONFIG_LOADER_DIR' ) . 'qit-env.json';
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}

	public function test_env_config_loader_from_yml() {
		App::setVar( 'QIT_CONFIG_LOADER_DIR', __DIR__ . '/../data/env-config/' );
		file_put_contents( App::getVar( 'QIT_CONFIG_LOADER_DIR' ) . 'qit-env.yml', "foo: bar" );
		$this->to_delete[] = App::getVar( 'QIT_CONFIG_LOADER_DIR' ) . 'qit-env.yml';
		$env_config_loader = App::make( \QIT_CLI\Environment\EnvConfigLoader::class );

		$this->assertMatchesJsonSnapshot( $env_config_loader->load_config() );
	}
}
