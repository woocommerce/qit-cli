<?php

namespace QIT_CLI\Environment;

class EnvironmentVersionResolver {
	/**
	 * @param string $woo
	 *
	 * @return string|array{slug: string, source: string} A plugin syntax, can be a string or an array.
	 */
	public static function resolve_woo( string $woo ) {
		if ( $woo === 'nightly' ) {
			$woo = [
				'slug'   => 'woocommerce',
				'source' => 'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip',
			];
		} elseif ( $woo === 'rc' ) {
			throw new \InvalidArgumentException( 'Using "nightly" instead. If you want a specific RC, please use the GitHub tag, eg: "1.2.3-rc.1"' );
		} elseif ( $woo === 'stable' ) {
			$woo = 'woocommerce';
		} else {
			$woo = "https://github.com/woocommerce/woocommerce/releases/download/$woo/woocommerce.zip";
		}

		return $woo;
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
			throw new \InvalidArgumentException( 'Using "nightly" instead. If you want a specific RC, please use the GitHub tag, eg: "1.2.3-RC1"' );
		}

		return $wp;
	}
}
