<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\App;
use QIT_CLI\Cache;

class VersionResolver {
	/**
	 * Resolve WooCommerce special versions (rc, nightly).
	 * Only handles these specific cases - no fallbacks.
	 */
	public function resolve_woo( string $version ): ?string {
		switch ( $version ) {
			case 'rc':
				$versions = App::make( Cache::class )->get_manager_sync_data( 'versions' );
				if ( empty( $versions['woocommerce']['rc_unsynced'] ) ) {
					throw new \RuntimeException( 'No WooCommerce RC version available.' );
				}
				return "https://github.com/woocommerce/woocommerce/releases/download/{$versions['woocommerce']['rc_unsynced']}/woocommerce.zip";

			case 'nightly':
				return 'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip';

			default:
				if ( preg_match( '/^\d+\.\d+\.\d+-dev$/', $version ) ) {
					return "https://github.com/woocommerce/woocommerce/releases/download/{$version}/woocommerce.zip";
				}

				return null; // Not a special version we handle
		}
	}

	/**
	 * Whether a WooCommerce version selector is a "special" version that must be
	 * resolved to a GitHub release URL rather than a WordPress.org download.
	 *
	 * Mirrors the cases handled by {@see resolve_woo()}: the `rc` and `nightly`
	 * channels and explicit development builds (e.g. `11.0.0-dev`). WordPress.org
	 * only hosts released tags, so treating one of these as an ordinary wporg
	 * version produces a dead `downloads.wordpress.org/.../{slug}.{version}.zip`
	 * URL (HTTP 404), which later surfaces as a misleading "invalid zip" error.
	 *
	 * This is a side-effect-free predicate (unlike resolve_woo(), which may hit
	 * the manager sync cache for `rc`), so it is safe to call from cache checks.
	 */
	public function is_woo_special_version( string $version ): bool {
		return in_array( $version, [ 'rc', 'nightly' ], true )
			|| preg_match( '/^\d+\.\d+\.\d+-dev$/', $version ) === 1;
	}

	/**
	 * Resolve WordPress special versions (rc only).
	 * Only handles RC - no fallbacks.
	 */
	public function resolve_wp( string $version ): ?string {
		if ( $version === 'rc' ) {
			$versions = App::make( Cache::class )->get_manager_sync_data( 'versions' );
			return $versions['wordpress']['rc'] ?? 'stable';
		}

		return null; // Not a special version we handle
	}
}
