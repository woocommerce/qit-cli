<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\NetworkErrorException;

class PluginDependencies {
	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @param int        $woo_id
	 * @param array<int> $additional_woo_extension_ids
	 *
	 * @return array{
	 *     plugins: array<string>,
	 *     php_extensions: array<string>,
	 * } The dependencies of the plugins.
	 */
	public function get_plugin_and_php_ext_dependencies( int $woo_id, array $additional_woo_extension_ids ): array {
		$cache_key = sprintf( 'plugin_dependencies_%s_%s', $woo_id, md5( implode( ',', $additional_woo_extension_ids ) ) );

		$cached = $this->cache->get( $cache_key );

		if ( $cached ) {
			$response = json_decode( $cached, true );
		} else {
			// Example response: "{\"plugins\":[\"woocommerce-payments\",\"automatewoo-birthdays\"],\"themes\":[],\"php_extensions\":[]}".
			try {
				$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ) )
					->with_method( 'POST' )
					->with_post_body( [
						'sut_id'                       => $woo_id,
						'additional_woo_extension_ids' => implode( ',', $additional_woo_extension_ids ),
					] )
					->request();
			} catch ( NetworkErrorException $e ) {
				// Could not get download URLs for any of the dependencies and/or the SUT.
				throw $e;
			}

			$response = json_decode( $json, true );

			if ( ! is_array( $response ) ) {
				throw new \UnexpectedValueException( 'Invalid response from the server when attempting to get Plugin Dependencies. Not a JSON array.' );
			}

			if ( ! isset( $response['plugins'] ) ) {
				throw new \UnexpectedValueException( 'Invalid response from the server when attempting to get Plugin Dependencies. JSON array does not contain "plugins" key.' );
			}

			if ( ! isset( $response['php_extensions'] ) ) {
				throw new \UnexpectedValueException( 'Invalid response from the server when attempting to get Plugin Dependencies. JSON array does not contain "php_extensions" key.' );
			}

			$this->cache->set( $cache_key, $json, HOUR_IN_SECONDS );
		}

		return [
			'plugins'        => $response['plugins'], // @phan-suppress-current-line PhanTypeArraySuspiciousNullable
			'php_extensions' => $response['php_extensions'], // @phan-suppress-current-line PhanTypeArraySuspiciousNullable
		];
	}
}
