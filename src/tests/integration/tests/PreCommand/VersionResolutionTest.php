<?php

namespace integration\tests\PreCommand;

use PHPUnit\Framework\TestCase;

/**
 * Tests that version-specific extension resolution produces correct download URLs.
 *
 * These tests assert on the raw source URL — NOT snapshots — because the normalizer
 * masks versions and URLs, hiding mismatches between requested and resolved versions.
 */
final class VersionResolutionTest extends TestCase {

	/**
	 * Find an extension by slug in the payload.
	 *
	 * @param array<string,mixed> $payload The decoded env_info JSON.
	 * @param string              $slug    The extension slug to find.
	 * @param string              $type    'plugins' or 'themes'.
	 *
	 * @return array<string,mixed>|null The extension array, or null if not found.
	 */
	private function find_extension( array $payload, string $slug, string $type = 'plugins' ): ?array {
		foreach ( $payload[ $type ] ?? [] as $ext ) {
			if ( ( $ext['slug'] ?? '' ) === $slug ) {
				return $ext;
			}
		}

		return null;
	}

	/* ================================================================
	 * 1. --woo=9.0.0 → source URL contains woocommerce.9.0.0.zip
	 * ============================================================== */
	public function test_woo_specific_version_resolves_correct_source(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--woo=9.0.0' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$woo = $this->find_extension( $payload, 'woocommerce' );
		$this->assertNotNull( $woo, 'WooCommerce plugin should be present' );
		$this->assertStringContainsString( 'woocommerce.9.0.0.zip', $woo['source'] );
	}

	/* ================================================================
	 * 2. --woo=stable (implicit) → source version matches resolved version
	 * ============================================================== */
	public function test_woo_stable_resolves_latest(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--plugin=woocommerce' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$woo = $this->find_extension( $payload, 'woocommerce' );
		$this->assertNotNull( $woo, 'WooCommerce plugin should be present' );
		// Source URL should contain the resolved version.
		$this->assertStringContainsString( 'woocommerce.' . $woo['version'] . '.zip', $woo['source'] );
	}

	/* ================================================================
	 * 3. --woo=rc → from is 'url', not 'wporg'
	 * ============================================================== */
	public function test_woo_rc_uses_url_source(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--woo=rc' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$woo = $this->find_extension( $payload, 'woocommerce' );
		$this->assertNotNull( $woo, 'WooCommerce plugin should be present' );
		$this->assertSame( 'url', $woo['from'], '--woo=rc should resolve via URL, not wporg' );
	}

	/* ================================================================
	 * 4. --woo=nightly → from is 'url', not 'wporg'
	 * ============================================================== */
	public function test_woo_nightly_uses_url_source(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--woo=nightly' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$woo = $this->find_extension( $payload, 'woocommerce' );
		$this->assertNotNull( $woo, 'WooCommerce plugin should be present' );
		$this->assertSame( 'url', $woo['from'], '--woo=nightly should resolve via URL, not wporg' );
	}

	/* ================================================================
	 * 5. --plugin=akismet:5.6 → source URL contains akismet.5.6.zip
	 * ============================================================== */
	public function test_plugin_slug_version_resolves_correct_source(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--plugin=akismet:5.6' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$akismet = $this->find_extension( $payload, 'akismet' );
		$this->assertNotNull( $akismet, 'Akismet plugin should be present' );
		$this->assertStringContainsString( 'akismet.5.6.zip', $akismet['source'] );
	}

	/* ================================================================
	 * 6. --theme=twentytwentythree:1.0 → source URL contains twentytwentythree.1.0.zip
	 * ============================================================== */
	public function test_theme_slug_version_resolves_correct_source(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--theme=twentytwentythree:1.0' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$theme = $this->find_extension( $payload, 'twentytwentythree', 'themes' );
		$this->assertNotNull( $theme, 'Twenty Twenty-Three theme should be present' );
		$this->assertStringContainsString( 'twentytwentythree.1.0.zip', $theme['source'] );
	}

	/* ================================================================
	 * 7. --plugin=akismet (bare slug) → source version matches resolved version
	 * ============================================================== */
	public function test_bare_plugin_slug_resolves_latest(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--plugin=akismet' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$akismet = $this->find_extension( $payload, 'akismet' );
		$this->assertNotNull( $akismet, 'Akismet plugin should be present' );
		// Source URL should contain the resolved version.
		$this->assertStringContainsString( 'akismet.' . $akismet['version'] . '.zip', $akismet['source'] );
	}
}
