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
				return null; // Not a special version we handle
		}
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
