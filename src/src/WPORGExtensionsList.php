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
			file_put_contents( '/tmp/qit/qit_debug.log', "is_wporg_plugin: Checking if $slug is a WordPress.org plugin\n", FILE_APPEND );
			$info = $this->get_plugin_download_info( $slug );

			// If no exception thrown => we found it.
			$result = ! empty( $info['slug'] );
			file_put_contents( '/tmp/qit/qit_debug.log', "is_wporg_plugin: $slug is " . ( $result ? 'a WordPress.org plugin' : 'not a WordPress.org plugin' ) . "\n", FILE_APPEND );
			return $result;
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "is_wporg_plugin: $slug is not a WordPress.org plugin: " . $e->getMessage() . "\n", FILE_APPEND );
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
	 * @return array{slug: string, version: string, url: string}
	 */
	public function get_plugin_download_info( string $slug ): array {
		$cache_key = "wporg_plugin_download_info_$slug";
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "get_plugin_download_info: Using cached info for $slug: " . print_r( $cached, true ) . "\n", FILE_APPEND );
			return $cached;
		}

		// Example: https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$slug}
		$url = sprintf( $this->plugin_api_url, urlencode( $slug ) );
		file_put_contents( '/tmp/qit/qit_debug.log', "get_plugin_download_info: Fetching info for $slug from $url\n", FILE_APPEND );

		try {
			$response_body = ( new RequestBuilder( $url ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();
			// file_put_contents( '/tmp/qit/qit_debug.log', "get_plugin_download_info: Response for $slug: " . substr( $response_body, 0, 1000 ) . "...\n", FILE_APPEND );
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "get_plugin_download_info: HTTP error fetching plugin info for '$slug': " . $e->getMessage() . "\n", FILE_APPEND );
			throw new \RuntimeException( "HTTP error fetching plugin info for '$slug': " . $e->getMessage() );
		}

		$json = json_decode( $response_body, true );
		if ( ! is_array( $json ) || empty( $json['download_link'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "get_plugin_download_info: Could not parse plugin info for slug '$slug' from WP.org.\n", FILE_APPEND );
			throw new \RuntimeException( "Could not parse plugin info for slug '$slug' from WP.org." );
		}

		$info = [
			'slug'    => $slug,
			'version' => $json['version'] ?? '',
			'url'     => $json['download_link'],
		];

		file_put_contents( '/tmp/qit/qit_debug.log', "get_plugin_download_info: Info for $slug: " . print_r( $info, true ) . "\n", FILE_APPEND );
		$this->cache->set( $cache_key, $info, 300 );

		return $info;
	}

	/**
	 * Retrieve theme info from WP.org.
	 * If it doesn't exist, throw an exception.
	 *
	 * @return array{slug: string, version: string, url: string}
	 */
	public function get_theme_download_info( string $slug ): array {
		$cache_key = "wporg_theme_download_info_$slug";
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			return $cached;
		}

		// Example: https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]={$slug}
		$url = sprintf( $this->theme_api_url, urlencode( $slug ) );

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

		$this->cache->set( $cache_key, $info, 300 );

		return $info;
	}
}
