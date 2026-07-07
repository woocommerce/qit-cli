<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Extensions\ExtensionResolver;
use QIT_CLI\PreCommand\Objects\Extension;

/**
 * Regression coverage for QIT-997: a WooCommerce dev/nightly version pinned as a
 * wporg plugin must NOT resolve to a non-existent WordPress.org download URL.
 * WordPress.org only hosts released tags, so
 * `downloads.wordpress.org/plugin/woocommerce.11.0.0-dev.zip` returns HTTP 404,
 * whose body was previously written to disk and surfaced as "invalid zip".
 */
class ExtensionResolverWooVersionTest extends QITTestCase {
	private function resolver(): ExtensionResolver {
		return App::make( ExtensionResolver::class );
	}

	private function woo( string $version, ?string $from = 'wporg' ): Extension {
		$ext          = new Extension( 'woocommerce', 'plugin' );
		$ext->from    = $from;
		$ext->version = $version;

		return $ext;
	}

	/** @return mixed */
	private function invoke( ExtensionResolver $resolver, string $method, Extension $ext ) {
		$ref = new \ReflectionMethod( ExtensionResolver::class, $method );
		$ref->setAccessible( true );

		return $ref->invoke( $resolver, $ext );
	}

	public function test_woo_dev_wporg_source_is_not_considered_resolved(): void {
		$this->assertFalse(
			$this->invoke( $this->resolver(), 'is_source_resolved', $this->woo( '11.0.0-dev' ) ),
			'WooCommerce dev version on wporg must be flagged unresolved so it routes through the version resolver.'
		);
	}

	public function test_woo_stable_wporg_source_remains_resolved(): void {
		$this->assertTrue(
			$this->invoke( $this->resolver(), 'is_source_resolved', $this->woo( '9.5.0' ) ),
			'A stable WooCommerce version is legitimately available on WordPress.org.'
		);
	}

	/**
	 * @dataProvider wporg_prerelease_provider
	 */
	public function test_woo_beta_and_rc_wporg_versions_remain_resolved( string $version ): void {
		$this->assertTrue(
			$this->invoke( $this->resolver(), 'is_source_resolved', $this->woo( $version ) ),
			'Explicit WooCommerce beta/RC versions are legitimately available on WordPress.org.'
		);
	}

	/** @return array<string,array{0:string}> */
	public function wporg_prerelease_provider(): array {
		return [
			'explicit beta' => [ '10.9.0-beta.1' ],
			'explicit rc'   => [ '10.9.0-rc.1' ],
		];
	}

	public function test_resolve_source_maps_woo_dev_to_github_url(): void {
		$ext = $this->woo( '11.0.0-dev' );
		$this->invoke( $this->resolver(), 'resolve_extension_source', $ext );

		$this->assertSame( 'url', $ext->from );
		$this->assertSame(
			'https://github.com/woocommerce/woocommerce/releases/download/11.0.0-dev/woocommerce.zip',
			$ext->source
		);
	}

	public function test_resolve_source_maps_woo_nightly_to_github_url(): void {
		$ext = $this->woo( 'nightly' );
		$this->invoke( $this->resolver(), 'resolve_extension_source', $ext );

		$this->assertSame( 'url', $ext->from );
		$this->assertSame(
			'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip',
			$ext->source
		);
	}

	public function test_resolve_source_leaves_woo_dev_url_untouched_when_already_a_url(): void {
		$ext          = $this->woo( '11.0.0-dev', 'url' );
		$ext->source  = 'https://example.com/custom-woocommerce.zip';
		$this->invoke( $this->resolver(), 'resolve_extension_source', $ext );

		// An explicit url source is already resolved and must not be overridden.
		$this->assertSame( 'url', $ext->from );
		$this->assertSame( 'https://example.com/custom-woocommerce.zip', $ext->source );
	}
}
