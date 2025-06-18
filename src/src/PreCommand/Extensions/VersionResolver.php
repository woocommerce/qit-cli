<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\App;
use QIT_CLI\Cache;

class VersionResolver {
	/**
	 * Registry of version resolvers for specific extensions.
	 *
	 * @var array<string, array<string, callable>>
	 */
	private array $resolvers;

	public function __construct() {
		$this->resolvers = [
			'woocommerce' => [
				'rc'      => static function () {
					$versions = App::make( Cache::class )->get_manager_sync_data( 'versions' );

					if ( empty( $versions['woocommerce']['rc_unsynced'] ) ) {
						throw new \RuntimeException( 'No WooCommerce RC version available. Please specify a RC version, such as "1.2.3-rc.1".' );
					}

					return "https://github.com/woocommerce/woocommerce/releases/download/{$versions['woocommerce']['rc_unsynced']}/woocommerce.zip";
				},
				'nightly' => static function () {
					return 'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip';
				},
			],
			'wordpress'   => [
				'rc' => static function () {
					$versions = App::make( Cache::class )->get_manager_sync_data( 'versions' );

					if ( empty( $versions['wordpress']['rc'] ) ) {
						// No RC available, fall back to stable
						return 'stable';
					}

					return $versions['wordpress']['rc'];
				},
			],
			// PRs can add more plugins here.
		];
	}

	/**
	 * Check if a version needs special resolution.
	 */
	public function can_resolve( string $plugin, string $version ): bool {
		return isset( $this->resolvers[ $plugin ][ $version ] );
	}

	/**
	 * Resolve a version to a download URL or version string.
	 */
	public function resolve( string $plugin, string $version ): string {
		if ( ! $this->can_resolve( $plugin, $version ) ) {
			throw new \RuntimeException( "No resolver for $plugin:$version" );
		}

		return $this->resolvers[ $plugin ][$version]();
	}

	/**
	 * Resolve WordPress core version (only handles RC)
	 */
	public function resolveWordPressVersion( string $version ): string {
		// Use the resolver if available
		if ( $this->can_resolve( 'wordpress', $version ) ) {
			return $this->resolve( 'wordpress', $version );
		}

		// Pass through other versions unchanged
		return $version;
	}
}