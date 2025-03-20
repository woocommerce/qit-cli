<?php

namespace QIT_CLI;

use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\PluginsAndThemesParser;
use QIT_CLI\Exceptions\NetworkErrorException;

class PluginDependencies {
	/** @var Cache $cache */
	protected $cache;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	/** @var PluginsAndThemesParser $parser */
	protected $parser;

	const DEPENDENCY_MODES = [
		'env_only' => [
			'none'     => 'none',
			'activate' => 'activate',
		],
		'env_test' => [
			'none'      => 'none',
			'activate'  => 'activate',
			'bootstrap' => 'bootstrap',
			'test'      => 'test',
		],
	];

	public function __construct( Cache $cache, WooExtensionsList $woo_extensions_list, PluginsAndThemesParser $parser ) {
		$this->cache               = $cache;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->parser              = $parser;
	}

	/**
	 * @param int        $woo_id
	 * @param array<int> $additional_woo_extension_ids
	 *
	 * @return array{
	 *     plugin: array<string>,
	 *     php_extension: array<string>,
	 * } The dependencies of the plugins.
	 */
	private function get_plugin_and_php_ext_dependencies( int $woo_id, array $additional_woo_extension_ids ): array {
		$cache_key = sprintf( 'plugins_%s_%s_v2', $woo_id, md5( implode( ',', $additional_woo_extension_ids ) ) );

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
			'plugin'        => $response['plugins'], // @phan-suppress-current-line PhanTypeArraySuspiciousNullable
			'php_extension' => $response['php_extensions'], // @phan-suppress-current-line PhanTypeArraySuspiciousNullable
		];
	}


	/**
	 * Enrich plugins and themes with their plugin dependencies.
	 *
	 * @param array<Extension> $plugins
	 * @param array<Extension> $themes
	 * @param string           $dependencies_mode
	 *
	 * @return array{
	 *     plugin: array<Extension>,
	 *     php_extension: array<string>
	 * }
	 */
	public function get_dependencies( array $plugins, array $themes, string $dependencies_mode ): array {
		if ( $dependencies_mode === self::DEPENDENCY_MODES['env_only']['none'] ) {
			return [
				'plugin'        => [],
				'php_extension' => [],
			];
		}

		$woo_extension_ids = [];

		foreach ( array_merge( $plugins, $themes ) as $ext ) {
			if ( ! isset( $ext->wccom_id ) ) {
				continue;
			}

			if ( ! $ext->wccom_id ) {
				continue;
			}

			$woo_extension_ids[] = $ext->wccom_id;
		}

		$woo_extension_ids = array_unique( $woo_extension_ids );

		if ( empty( $woo_extension_ids ) ) {
			return [
				'plugin'        => [],
				'php_extension' => [],
			];
		}

		$first_id = array_shift( $woo_extension_ids );

		$dependencies_data = $this->get_plugin_and_php_ext_dependencies( $first_id, $woo_extension_ids );

		$plugins = [];

		foreach ( $dependencies_data['plugin'] as $plugin_slug ) {
			$exists = array_filter( $plugins, function ( $ext ) use ( $plugin_slug ) {
				return $ext->slug === $plugin_slug;
			} );

			if ( empty( $exists ) ) {
				$p         = $this->parser->parse_extensions( [ $plugin_slug ], 'plugin', $dependencies_mode );
				$plugins[] = array_shift( $p );
			}
		}

		foreach ( $plugins as $plugin ) {
			$plugin->priority = Extension::PRIORITY_LOW;
		}

		return [
			'plugin'        => $plugins,
			'php_extension' => $dependencies_data['php_extension'],
		];
	}

	/**
	 * @param array<Extension> $new_deps
	 * @param array<Extension> $existing_plugins
	 * @param int              $default_priority
	 */
	public function maybe_add_plugin_dependencies( array $new_deps, array &$existing_plugins, int $default_priority = Extension::PRIORITY_LOW ): void {
		foreach ( $new_deps as $dep_ext ) {
			$dep_ext->priority = $default_priority;

			$found_index = null;
			foreach ( $existing_plugins as $i => $existing_ext ) {
				if ( $existing_ext->slug === $dep_ext->slug ) {
					if ( $dep_ext->priority > $existing_ext->priority ) {
						$existing_plugins[ $i ] = $dep_ext;
					}
					$found_index = $i;
					break;
				}
			}

			// If not found, add.
			if ( $found_index === null ) {
				$existing_plugins[] = $dep_ext;
			}
		}
	}

	/**
	 * @param array<string> $new_extensions
	 * @param array<string> $existing
	 */
	public function maybe_add_php_extensions( array $new_extensions, array &$existing ): void {
		foreach ( $new_extensions as $ext_name ) {
			if ( ! in_array( $ext_name, $existing, true ) ) {
				$existing[] = $ext_name;
			}
		}
	}
}
