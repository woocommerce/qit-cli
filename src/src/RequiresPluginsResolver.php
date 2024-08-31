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
		$this->cache               = $cache;
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
		$resolved_dependencies = [
			'woo'   => [],
			'wporg' => [],
		];

		$dependencies = $this->woo_extensions_list->get_woo_extension_dependencies( $woo_extension_id );

		foreach ( $dependencies['woo'] ?? [] as $woo_dependency ) {
			foreach ( $this->resolve_woocom_dependencies( $woo_dependency ) as $resolved_woo_dependency ) {
				$resolved_dependencies['woo'][] = $resolved_woo_dependency;
			}
			$resolved_dependencies['woo'][] = $woo_dependency;
		}

		foreach ( $dependencies['wporg'] ?? [] as $wporg_dependency ) {
			$dependencies_of_this_dependency = $this->resolve_wporg_dependencies( $wporg_dependency );
			foreach ( $dependencies_of_this_dependency as $resolved_wporg_dependency ) {
				$resolved_dependencies['wporg'][] = $resolved_wporg_dependency;
			}
			$resolved_dependencies['wporg'][] = $wporg_dependency;
		}

		$resolved_dependencies['woo']   = array_unique( $resolved_dependencies['woo'] );
		$resolved_dependencies['wporg'] = array_unique( $resolved_dependencies['wporg'] );

		return $resolved_dependencies;
	}

	protected function resolve_woocom_dependencies( int $woo_extension_id ): array {
		try {
			return $this->woo_extensions_list->get_woo_extension_dependencies( $woo_extension_id )['woo'] ?? [];
		} catch ( \Exception $e ) {
			// This can happen if the plugin has a Woo dependency that the user does not have access to.
			return [];
		}
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

		// Initialize an empty array for dependencies if not already set.
		$dependencies = [];

		// Check if the plugin has already been processed to avoid infinite recursion.
		if ( in_array( $slug, $this->resolved_tree ) ) {
			return [];
		}

		// Add the current plugin to the resolved tree to mark it as processed.
		$this->resolved_tree[] = $slug;

		if ( ! empty( App::getVar( "MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_$slug" ) ) ) {
			$data = json_decode( App::getVar( "MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_$slug" ), true );
		} else {
			if ( defined( 'UNIT_TESTS' ) ) {
				throw new \LogicException( 'This method should not be called in unit tests without a mocked response. Expected: ' . "MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_$slug" );
			}

			$curl = curl_init();
			curl_setopt_array( $curl, [
				CURLOPT_URL            => "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug=$slug",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_HTTPHEADER     => [
					'Accept: application/json',
				],
			] );

			$response  = $this->maybe_mock_curl_response( $curl );
			$http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			curl_close( $curl );

			if ( $http_code !== 200 ) {
				return [];
			}

			$data = json_decode( $response, true );
		}

		if ( empty( $data['requires_plugins'] ) ) {
			return [];
		}

		foreach ( $data['requires_plugins'] as $required_plugin ) {
			if ( empty( $required_plugin ) ) {
				continue;
			}
			$dependencies   = array_merge( $dependencies, $this->resolve_wporg_dependencies( $required_plugin ) );
			$dependencies[] = $required_plugin;
		}

		$this->cache->set( "wporg_dependencies_$slug", $dependencies, HOUR_IN_SECONDS );

		return $dependencies;
	}
}