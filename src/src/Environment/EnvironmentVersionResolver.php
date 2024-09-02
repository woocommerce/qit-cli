<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;

class EnvironmentVersionResolver {
	/**
	 * @param string       $woo
	 * @param array<mixed> $plugins
	 *
	 * @return string|array{slug: string, source: string} A plugin syntax, can be a string or an array.
	 */
	public static function resolve_woo( string $woo, array $plugins ) {
		$plugins = App::make( PluginsAndThemesParser::class )->parse_extensions( $plugins, Extension::TYPES['plugin'] );

		$action    = 'activate';
		$test_tags = 'default';

		foreach ( $plugins as $plugin ) {
			if ( $plugin->slug === 'woocommerce' ) {
				$action    = $plugin->action;
				$test_tags = $plugin->test_tags;
			}
		}

		if ( $woo === 'nightly' ) {
			$woo = [
				'slug'      => 'woocommerce',
				'source'    => 'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip',
				'action'    => $action,
				'test_tags' => [ $test_tags ],
			];
		} elseif ( $woo === 'rc' ) {
			throw new \InvalidArgumentException( 'Please specify a RC version, such as "1.2.3-rc.1", or use "nightly".' );
		} elseif ( $woo === 'stable' ) {
			$woo = [
				'slug'      => 'woocommerce',
				'source'    => 'https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip',
				'action'    => $action,
				'test_tags' => [ $test_tags ],
			];
		} else {
			$woo = [
				'slug'      => 'woocommerce',
				'source'    => "https://github.com/woocommerce/woocommerce/releases/download/$woo/woocommerce.zip",
				'action'    => $action,
				'test_tags' => [ $test_tags ],
			];
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
			throw new \InvalidArgumentException( 'Please specify a RC version, such as "1.2.3-rc.1", or use "nightly".' );
		}

		return $wp;
	}
}
