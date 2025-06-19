<?php

namespace QIT_CLI_Tests\PreCommand2;

use Spatie\Snapshots\MatchesSnapshots;

class VersionConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

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

	public function test_version_overrides_from_cli() {
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
			'--php_version' => '8.0'
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertEquals( '6.1', $env_info['wp_version'] );
		$this->assertEquals( '8.0', $env_info['php_version'] );
		$this->assertTrue( in_array( 'woocommerce', array_map( fn( $p ) => $p['slug'] ?? $p, $env_info['plugins'] ) ) );
		$this->assertTrue( in_array( 'storefront', $env_info['themes'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}