<?php

namespace QIT_CLI_Tests\PreCommand;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PluginConfigurationTest extends TestCase {
	use MatchesSnapshots;
	use PreCommandTestTrait;

	public function test_cli_plugin_override_with_config() {
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

	public function test_local_plugin_path() {
		$plugin_zip = $this->temp_dir . '/test-plugin-' . uniqid() . '.zip';
		file_put_contents( $plugin_zip, 'fake plugin contents' );
		$this->to_delete[] = $plugin_zip;

		$config = [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
					'themes'  => [ 'storefront' ]
				]
			]
		];

		$cli_args = [
			'--plugin' => [ $plugin_zip ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( '/tmp-normalized/normalized-plugin.zip', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertEquals( count( $env_info['plugins'] ), count( array_unique( array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}