<?php

namespace QIT_CLI;

class RequiresPluginsResolver {
	/** @var Cache $cache */
	protected $cache;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	protected $resolved_tree = [];

	public function __construct(
		Cache $cache,
		WooExtensionsList $woo_extensions_list
	) {
		$this->cache = $cache;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	/**
	 * Unit tests can override this to mock the curl response.
	 *
	 * @param $curl
	 *
	 * @return bool|string
	 */
	protected function maybe_mock_curl_response( $curl ) {
		return curl_exec( $curl );
	}

	public function resolve_dependencies( int $woo_extension_id ): array {
		$dependencies = $this->resolve_wporg_dependencies( $woo_extension_id );
		$dependencies = array_merge( $dependencies, $this->resolve_woocom_dependencies( $woo_extension_id ) );

		return $dependencies;
	}

	protected function resolve_woocom_dependencies( int $woo_extension_id ): array {
		$dependencies = $this->woo_extensions_list->get_woo_extension_dependencies( $woo_extension_id );

		return $dependencies;
	}

	/**
	 * Get the WPORG dependencies of a plugin.
	 *
	 * @param string $slug The slug of the plugin to get the dependencies of.
	 *
	 * @return array The dependencies of the plugin.
	 */
	protected function resolve_wporg_dependencies( string $slug ): array {
		/*
		 * GET "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug=$slug"
		 * Parse the JSON
		 * Search for "Requires Plugins" array, which is an array of strings or empty.
		 * If set, iterate over the array, getting the WPORG dependencies of these.
		 * Return the array, dependencies first.
		 */
		$cached = $this->cache->get( "wporg_dependencies_$slug" );

		if ( $cached ) {
			return $cached;
		}

		$dependencies = [];

		if ( ! empty( App::getVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE' ) ) ) {
			$data = json_decode( App::getVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE' ), true );
		} else {
			// Account for the possibility of the request failing. If it's a 429, wait and retry up to 2 times.
			$retries = 0;
			$curl    = curl_init();
			curl_setopt_array( $curl, [
				CURLOPT_URL            => "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug=$slug",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_HTTPHEADER     => [
					'Accept: application/json',
				],
			] );
			retry:
			$response  = curl_exec( $curl );
			$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			curl_close( $curl );
			if ( $http_code === 429 && $retries < 2 ) {
				sleep( 5 );
				$retries ++;
				goto retry;
			}

			if ( $http_code !== 200 ) {
				return [];
			}

			$data = json_decode( $response, true );
		}

		if ( ! isset( $data['requires_plugins'] ) ) {
			return [];
		}

		foreach ( $data['requires_plugins'] as $required_plugin ) {
			$dependencies   = array_merge( $dependencies, $this->resolve_wporg_dependencies( $required_plugin ) );
			$dependencies[] = $required_plugin;
		}

		$this->cache->set( "wporg_dependencies_$slug", $dependencies, HOUR_IN_SECONDS );

		return [ 'wporg' => $dependencies ];
	}
}