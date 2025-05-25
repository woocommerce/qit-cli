<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Extension;

class EnvironmentVersionResolver {
	/**
	 * @param string       $woo
	 * @param array<mixed> $plugins
	 *
	 * @return Extension A WooCommerce Extension object.
	 */
	public static function resolve_woo( string $woo, array $plugins ): Extension {
		$versions = App::make( Cache::class )->get_manager_sync_data( 'versions' );

		// Find existing WooCommerce Extension in plugins
		$woo_extension = null;
		foreach ( $plugins as $plugin ) {
			if ( is_object( $plugin ) && $plugin->slug === 'woocommerce' ) {
				$woo_extension = $plugin;
				break;
			}
		}

		// If WooCommerce is not in plugins, create a new Extension
		if ( ! $woo_extension ) {
			$woo_extension = new Extension( 'woocommerce', 'plugin' );
		}

		// Set source based on $woo
		if ( $woo === 'nightly' ) {
			$woo_extension->source = 'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip';
		} elseif ( $woo === 'rc' ) {
			if ( empty( $versions['woocommerce']['rc_unsynced'] ) ) {
				throw new \InvalidArgumentException( 'No unsynced RC version available. Please specify a RC version, such as "1.2.3-rc.1".' );
			}
			$woo_extension->source = "https://github.com/woocommerce/woocommerce/releases/download/{$versions['woocommerce']['rc_unsynced']}/woocommerce.zip";
		} elseif ( $woo === 'stable' ) {
			$woo_extension->source = 'https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip';
		} elseif ( filter_var( $woo, FILTER_VALIDATE_URL ) ) {
			$woo_extension->source = $woo;
		} else {
			$woo_extension->source = "https://github.com/woocommerce/woocommerce/releases/download/$woo/woocommerce.zip";
		}

		return $woo_extension;
	}

	/**
	 * WP CLI "wp core download" --version parameter accepts a version number, 'latest' or 'nightly'.
	 *
	 * Since we already use "stable" throughout the codebase, we allow to use "stable" instead of "latest".
	 *
	 * Other than that, we just make clear that we don't support "rc" here, and we just pass the value to WP CLI to resolve.
	 *
	 * @param string $wp The original value of WP.
	 *
	 * @return string The parsed value of WP, to be feed to WP CLI.
	 */
	public static function resolve_wp( string $wp ): string {
		if ( $wp === 'stable' ) {
			$wp = 'latest';
		} elseif ( $wp === 'rc' ) {
			$versions = App::make( Cache::class )->get_manager_sync_data( 'versions' );
			if ( ! empty( $versions['wordpress']['rc'] ) ) {
				$wp = $versions['wordpress']['rc'];
			} else {
				throw new \InvalidArgumentException( 'No RC version available. Please specify a RC version, such as "1.2.3-rc.1".' );
			}
		}
		return $wp;
	}
}
