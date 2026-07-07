<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Extensions\VersionResolver;

class VersionResolverTest extends QITTestCase {
	private function resolver(): VersionResolver {
		return App::make( VersionResolver::class );
	}

	public function test_resolve_woo_dev_version_maps_to_github_release(): void {
		$this->assertSame(
			'https://github.com/woocommerce/woocommerce/releases/download/11.0.0-dev/woocommerce.zip',
			$this->resolver()->resolve_woo( '11.0.0-dev' )
		);
	}

	public function test_resolve_woo_nightly_maps_to_trunk_nightly(): void {
		$this->assertSame(
			'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip',
			$this->resolver()->resolve_woo( 'nightly' )
		);
	}

	public function test_resolve_woo_returns_null_for_regular_and_stable_versions(): void {
		$this->assertNull( $this->resolver()->resolve_woo( '9.5.0' ) );
		$this->assertNull( $this->resolver()->resolve_woo( 'stable' ) );
	}

	/**
	 * @dataProvider special_version_provider
	 */
	public function test_is_woo_special_version( string $version, bool $expected ): void {
		$this->assertSame( $expected, $this->resolver()->is_woo_special_version( $version ) );
	}

	/** @return array<string,array{0:string,1:bool}> */
	public function special_version_provider(): array {
		return [
			'nightly channel'   => [ 'nightly', true ],
			'rc channel'        => [ 'rc', true ],
			'explicit dev build' => [ '11.0.0-dev', true ],
			'other dev build'   => [ '9.9.0-dev', true ],
			'stable keyword'    => [ 'stable', false ],
			'regular version'   => [ '9.5.0', false ],
			'undefined'         => [ 'undefined', false ],
			// Explicit rc/beta tags are not handled by resolve_woo() (separate follow-up).
			'explicit rc tag'   => [ '11.0.0-rc.1', false ],
		];
	}
}
