<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use function QIT_CLI\get_manager_url;

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
			'test'      => 'test',
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
	 *     plugin: array<string>,
	 *     theme: array<string>,
	 *     php_extension: array<string>,
	 * } The dependencies of the extension.
	 */
	private function get_plugin_and_php_ext_dependencies( int $woo_id, array $additional_woo_extension_ids ): array {
		$cache_key = sprintf( 'plugins_%s_%s_v2', $woo_id, md5( implode( ',', $additional_woo_extension_ids ) ) );

		$cached = $this->cache->get( $cache_key );

		if ( $cached ) {
			$response = json_decode( $cached, true );
			if ( ! is_array( $response ) ) {
				throw new \UnexpectedValueException( 'Cached response is not a valid JSON array.' );
			}
		} else {
			try {
				$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ) )
					->with_method( 'POST' )
					->with_post_body( [
						'sut_id'                       => $woo_id,
						'additional_woo_extension_ids' => implode( ',', $additional_woo_extension_ids ),
					] )
					->request();
				if ( $json === 'NULL_RESPONSE' || $json === '' ) {
					throw new NetworkErrorException( 'Received null or empty response from dependency API.' );
				}
			} catch ( NetworkErrorException $e ) {
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
			'plugin'        => $response['plugins'],
			'theme'         => $response['themes'] ?? [],
			'php_extension' => $response['php_extensions'],
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
	 *     plugin: array<Extension>,
	 *     theme: array<Extension>,
	 *     php_extension: array<string>
	 * }
	 */
	public function get_dependencies( array $plugins, array $themes, string $dependencies_mode ): array {
		if ( $dependencies_mode === self::DEPENDENCY_MODES['env_only']['none'] ) {
			return [
				'plugin'        => [],
				'theme'         => [],
				'php_extension' => [],
			];
		}

		$woo_extension_ids = [];

		foreach ( array_merge( $plugins, $themes ) as $ext ) {
			if ( ! isset( $ext->wccom_id ) || ! $ext->wccom_id ) {
				continue;
			}
			$woo_extension_ids[] = $ext->wccom_id;
		}

		$woo_extension_ids = array_unique( $woo_extension_ids );

		if ( empty( $woo_extension_ids ) ) {
			return [
				'plugin'        => [],
				'theme'         => [],
				'php_extension' => [],
			];
		}

		$first_id = array_shift( $woo_extension_ids );

		$dependencies_data = $this->get_plugin_and_php_ext_dependencies( $first_id, $woo_extension_ids );

		$plugins_result = [];
		foreach ( $dependencies_data['plugin'] as $plugin_slug ) {
			$exists = array_filter( $plugins_result, function ( $ext ) use ( $plugin_slug ) {
				return $ext->slug === $plugin_slug;
			} );

			if ( empty( $exists ) ) {
				$plugin           = new Extension( $plugin_slug, 'plugin' );
				$plugin->priority = Extension::PRIORITY_LOW;
				$plugins_result[] = $plugin;
			}
		}

		$themes_result = [];
		foreach ( $dependencies_data['theme'] as $theme_slug ) {
			$exists = array_filter( $themes_result, function ( $ext ) use ( $theme_slug ) {
				return $ext->slug === $theme_slug;
			} );

			if ( empty( $exists ) ) {
				$theme           = new Extension( $theme_slug, 'theme' );
				$theme->priority = Extension::PRIORITY_LOW;
				$themes_result[] = $theme;
			}
		}

		return [
			'plugin'        => $plugins_result,
			'theme'         => $themes_result,
			'php_extension' => $dependencies_data['php_extension'],
		];
	}

	/**
	 * @param array<Extension> $new_deps
	 * @param array<Extension> $existing_plugins
	 * @param int $default_priority
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

			if ( $found_index === null ) {
				$existing_plugins[] = $dep_ext;
			}
		}
	}

	/**
	 * @param array<Extension> $new_deps
	 * @param array<Extension> $existing_themes
	 */
	public function maybe_add_theme_dependencies( array $new_deps, array &$existing_themes ): void {
		foreach ( $new_deps as $dep_ext ) {
			$found_index = null;
			foreach ( $existing_themes as $i => $existing_ext ) {
				if ( $existing_ext->slug === $dep_ext->slug ) {
					$found_index = $i;
					break;
				}
			}

			if ( $found_index === null ) {
				$existing_themes[] = $dep_ext;
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