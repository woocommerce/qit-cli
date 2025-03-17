<?php

namespace QIT_CLI;

use QIT_CLI\Environment\Extension;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\IO\Output;

class PluginDependencies {
	/** @var Cache $cache */
	protected $cache;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	const DEPENDENCY_MODES = [
		'env_only' => [
			'none'     => 'none',
			'activate' => 'activate',
		],
		'env_test' => [
			'none'      => 'none',
			'activate'  => 'activate',
			'bootstrap' => 'bootstrap',
			'test'      => 'test'
		],
	];

	public function __construct( Cache $cache, WooExtensionsList $woo_extensions_list ) {
		$this->cache               = $cache;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	/**
	 * @param int $woo_id
	 * @param array<int> $additional_woo_extension_ids
	 *
	 * @return array{
	 *     plugins: array<string>,
	 *     php_extensions: array<string>,
	 * } The dependencies of the plugins.
	 */
	public function get_plugin_and_php_ext_dependencies( int $woo_id, array $additional_woo_extension_ids ): array {
		$cache_key = sprintf( 'plugin_dependencies_%s_%s_v2', $woo_id, md5( implode( ',', $additional_woo_extension_ids ) ) );

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


	/**
	 * Enrich plugins and themes with their plugin dependencies.
	 *
	 * @param array<Extension> $plugins
	 * @param array<Extension> $themes
	 * @param string $dependencies_mode
	 *
	 * @return array{
	 *     plugins_with_deps: array<Extension>,
	 *     php_extensions: array<string>
	 * }
	 */
	public function add_plugin_dependencies( array $plugins, array $themes, string $dependencies_mode ): array {
		if ( $dependencies_mode === self::DEPENDENCY_MODES['env_only']['none'] ) {
			return [ 'plugins_with_deps' => [], 'php_extensions' => [] ];
		}

		$woo_extension_ids = [];

		foreach ( array_merge( $plugins, $themes ) as $ext ) {
			if ( is_numeric( $ext->source ) ) {
				$woo_extension_ids[] = (int) $ext->source;
				continue;
			}

			try {
				$woo_id              = $this->woo_extensions_list->get_woo_extension_id_by_slug( $ext->slug );
				$woo_extension_ids[] = $woo_id;
			} catch ( \UnexpectedValueException $e ) {
				App::make( Output::class )->writeln(
					sprintf( '<comment>Warning: Skip dependency checks of "%s", because it\'s WooCommerce.com ID could not be found or is unauthorized.</comment>', $ext->slug )
				);
			}
		}

		$woo_extension_ids = array_unique( $woo_extension_ids );

		if ( empty( $woo_extension_ids ) ) {
			return [ 'plugins_with_deps' => [], 'php_extensions' => [] ];
		}

		$first_id = array_shift( $woo_extension_ids );

		$dependencies_data = $this->get_plugin_and_php_ext_dependencies( $first_id, $woo_extension_ids );

		$plugins_with_deps = [];

		foreach ( $dependencies_data['plugins'] as $plugin_slug ) {
			$exists = array_filter( $plugins, function ( $ext ) use ( $plugin_slug ) {
				return $ext->slug === $plugin_slug;
			} );

			if ( empty( $exists ) ) {
				$extension            = new Extension();
				$extension->slug      = $plugin_slug;
				$extension->source    = $plugin_slug;
				$extension->action    = $dependencies_mode;
				$extension->type      = Extension::TYPES['plugin'];
				$extension->test_tags = [ 'dependency' ];
				$plugins_with_deps[]  = $extension;
			}
		}

		return [
			'plugins_with_deps' => $plugins_with_deps,
			'php_extensions'    => $dependencies_data['php_extensions'],
		];
	}
}
