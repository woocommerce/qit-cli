<?php

namespace QIT_CLI;

class WPORGExtensionsList {
	/**
	 * @var string
	 */
	public $plugin_api_url = 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=%s';

	/**
	 * @var string
	 */
	public $theme_api_url = 'https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]=%s';

	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Return true if the slug is a known plugin on WP.org
	 */
	public function is_wporg_plugin( string $slug ): bool {
		try {
			$info = $this->get_plugin_download_info( $slug );

			// If no exception thrown => we found it.
			$result = ! empty( $info['slug'] );
			return $result;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Return true if the slug is a known theme on WP.org
	 */
	public function is_wporg_theme( string $slug ): bool {
		try {
			$info = $this->get_theme_download_info( $slug );

			return ! empty( $info['slug'] );
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Retrieve plugin info from WP.org.
	 * If it doesn't exist or there's an HTTP error, throw an exception.
	 *
	 * @return array{slug: string, version: string, url: string, last_updated?: string}
	 */
	public function get_plugin_download_info( string $slug ): array {
		$cache_key = "wporg_plugin_download_info_$slug";
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			return $cached;
		}

		// Example: https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$slug}
		$url = sprintf( $this->plugin_api_url, rawurlencode( $slug ) );

		try {
			$response_body = ( new RequestBuilder( $url ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "HTTP error fetching plugin info for '$slug': " . $e->getMessage() );
		}

		$json = json_decode( $response_body, true );
		if ( ! is_array( $json ) || empty( $json['download_link'] ) ) {
			throw new \RuntimeException( "Could not parse plugin info for slug '$slug' from WP.org." );
		}

		$info = [
			'slug'    => $slug,
			'version' => $json['version'] ?? '',
			'url'     => $json['download_link'],
		];

		// Add last_updated if available for cache validation
		if ( ! empty( $json['last_updated'] ) ) {
			$info['last_updated'] = $json['last_updated'];
		}

		// Cache for 30 seconds to prevent API burst but still get fresh data
		$this->cache->set( $cache_key, $info, 30 );

		return $info;
	}

	/**
	 * Retrieve theme info from WP.org.
	 * If it doesn't exist, throw an exception.
	 *
	 * @return array{slug: string, version: string, url: string, last_updated?: string}
	 */
	public function get_theme_download_info( string $slug ): array {
		$cache_key = "wporg_theme_download_info_$slug";
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			return $cached;
		}

		// Example: https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]={$slug}
		$url = sprintf( $this->theme_api_url, rawurlencode( $slug ) );

		try {
			$response_body = ( new RequestBuilder( $url ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "HTTP error fetching theme info for '$slug': " . $e->getMessage() );
		}

		$json = json_decode( $response_body, true );
		if ( ! is_array( $json ) || empty( $json['download_link'] ) ) {
			throw new \RuntimeException( "Could not parse theme info for slug '$slug' from WP.org." );
		}

		$info = [
			'slug'    => $slug,
			'version' => $json['version'] ?? '',
			'url'     => $json['download_link'],
		];

		// Add last_updated if available for cache validation
		if ( ! empty( $json['last_updated'] ) ) {
			$info['last_updated'] = $json['last_updated'];
		}

		// Cache for 30 seconds to prevent API burst but still get fresh data
		$this->cache->set( $cache_key, $info, 30 );

		return $info;
	}
}
