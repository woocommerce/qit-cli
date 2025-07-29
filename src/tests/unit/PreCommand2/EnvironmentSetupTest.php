<?php

namespace QIT_CLI_Tests\PreCommand2;

use Spatie\Snapshots\MatchesSnapshots;

class EnvironmentSetupTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function test_basic_config_with_plugins_themes_versions() {
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
}