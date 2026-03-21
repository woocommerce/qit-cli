<?php

namespace QIT_CLI\BreakingChanges;

use QIT_CLI\Cache;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

class WooDevelopedFetcher {
	private Cache $cache;

	private const CACHE_KEY = 'woo_developed_extensions';
	private const CACHE_TTL = DAY_IN_SECONDS;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Fetch the list of Woo-developed plugin slugs.
	 *
	 * @return string[] Array of plugin slugs.
	 */
	public function fetch(): array {
		$cached = $this->cache->get( self::CACHE_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$url      = get_manager_url() . '/wp-json/cd/v1/cli/woo-developed-extensions';
		$response = ( new RequestBuilder( $url ) )
			->with_method( 'GET' )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Failed to fetch Woo-developed extensions list.' );
		}

		$slugs = array_map( function ( array $ext ) {
			return $ext['slug'];
		}, $data );

		$this->cache->set( self::CACHE_KEY, $slugs, self::CACHE_TTL );

		return $slugs;
	}
}
