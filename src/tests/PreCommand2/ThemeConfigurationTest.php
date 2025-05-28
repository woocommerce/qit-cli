<?php

namespace QIT_CLI_Tests\PreCommand2;

use Spatie\Snapshots\MatchesSnapshots;

class ThemeConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

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

	public function test_local_theme_path() {
		$theme_zip = $this->temp_dir . '/test-theme-' . uniqid() . '.zip';
		file_put_contents( $theme_zip, 'fake theme contents' );
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
			'--theme' => [ $theme_zip ]
		];

		$env_info = $this->run_unit_test( $config, $cli_args );
		$this->assertTrue( in_array( 'storefront', $env_info['themes'] ) );
		$this->assertTrue( in_array( '/tmp-normalized/normalized-theme.zip', $env_info['themes'] ) );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}