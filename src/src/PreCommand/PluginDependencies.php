<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use function QIT_CLI\get_manager_url;

class PluginDependencies {
	/** @var Cache $cache */
	protected $cache;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	/** @var WPORGExtensionsList $wporg_extensions_list */
	protected $wporg_extensions_list;

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

	public function __construct( Cache $cache, WooExtensionsList $woo_extensions_list, WPORGExtensionsList $wporg_extensions_list ) {
		$this->cache                 = $cache;
		$this->woo_extensions_list   = $woo_extensions_list;
		$this->wporg_extensions_list = $wporg_extensions_list;
	}

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

	private function get_wporg_plugin_dependencies( string $slug ): array {
		$cache_key = "wporg_plugin_deps_$slug";
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			return $cached;
		}

		try {
			$this->wporg_extensions_list->get_plugin_download_info( $slug );
			$response_body = ( new RequestBuilder( sprintf( $this->wporg_extensions_list->plugin_api_url, $slug ) ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();
			$raw_info      = @unserialize( $response_body );

			if ( ! is_object( $raw_info ) || ! isset( $raw_info->requires_plugins ) ) {
				$requires_plugins = [];
			} else {
				$requires_plugins = (array) $raw_info->requires_plugins;
			}

			$dependencies = [
				'plugin'        => $requires_plugins,
				'theme'         => [],
				'php_extension' => [],
			];

			$this->cache->set( $cache_key, $dependencies, HOUR_IN_SECONDS );

			return $dependencies;
		} catch ( \Exception $e ) {
			return [
				'plugin'        => [],
				'theme'         => [],
				'php_extension' => [],
			];
		}
	}

	public function get_dependencies( array $plugins, array $themes, string $dependencies_mode ): array {
		if ( $dependencies_mode === self::DEPENDENCY_MODES['env_only']['none'] ) {
			return [
				'plugin'        => [],
				'theme'         => [],
				'php_extension' => [],
			];
		}

		$all_deps = [
			'plugin'        => [],
			'theme'         => [],
			'php_extension' => [],
		];

		$woo_extension_ids = [];

		foreach ( array_merge( $plugins, $themes ) as $ext ) {
			if ( ! empty( $ext->wccom_id ) ) {
				$woo_extension_ids[] = $ext->wccom_id;
			}
		}

		$woo_extension_ids = array_unique( $woo_extension_ids );

		if ( ! empty( $woo_extension_ids ) ) {
			$first_id                  = array_shift( $woo_extension_ids );
			$wccom_deps                = $this->get_plugin_and_php_ext_dependencies( $first_id, $woo_extension_ids );
			$all_deps['plugin']        = array_merge( $all_deps['plugin'], $wccom_deps['plugin'] );
			$all_deps['theme']         = array_merge( $all_deps['theme'], $wccom_deps['theme'] );
			$all_deps['php_extension'] = array_merge( $all_deps['php_extension'], $wccom_deps['php_extension'] );
		}

		foreach ( $plugins as $ext ) {
			if ( $this->wporg_extensions_list->is_wporg_plugin( $ext->slug ) ) {
				$wporg_deps                = $this->get_wporg_plugin_dependencies( $ext->slug );
				$all_deps['plugin']        = array_merge( $all_deps['plugin'], $wporg_deps['plugin'] );
				$all_deps['theme']         = array_merge( $all_deps['theme'], $wporg_deps['theme'] );
				$all_deps['php_extension'] = array_merge( $all_deps['php_extension'], $wporg_deps['php_extension'] );
			}
		}

		$all_deps['plugin']        = array_values( array_unique( $all_deps['plugin'] ) );
		$all_deps['theme']         = array_values( array_unique( $all_deps['theme'] ) );
		$all_deps['php_extension'] = array_values( array_unique( $all_deps['php_extension'] ) );

		$plugins_result = [];
		foreach ( $all_deps['plugin'] as $plugin_slug ) {
			$exists = array_filter( $plugins_result, function ( $ext ) use ( $plugin_slug ) {
				return $ext->slug === $plugin_slug;
			} );

			if ( empty( $exists ) ) {
				$plugin           = new Extension( $plugin_slug, 'plugin' );
				$plugin->priority = Extension::PRIORITY_LOW;

				if ( $this->wporg_extensions_list->is_wporg_plugin( $plugin_slug ) ) {
					try {
						$info            = $this->wporg_extensions_list->get_plugin_download_info( $plugin_slug );
						$plugin->source  = $info['url'];
						$plugin->version = $info['version'];
					} catch ( \Exception $e ) {
						// Skip if unable to fetch info.
					}
				}

				$plugins_result[] = $plugin;
			}
		}

		$themes_result = [];
		foreach ( $all_deps['theme'] as $theme_slug ) {
			$exists = array_filter( $themes_result, function ( $ext ) use ( $theme_slug ) {
				return $ext->slug === $theme_slug;
			} );

			if ( empty( $exists ) ) {
				$theme           = new Extension( $theme_slug, 'theme' );
				$theme->priority = Extension::PRIORITY_LOW;

				if ( $this->wporg_extensions_list->is_wporg_theme( $theme_slug ) ) {
					try {
						$info           = $this->wporg_extensions_list->get_theme_download_info( $theme_slug );
						$theme->source  = $info['url'];
						$theme->version = $info['version'];
					} catch ( \Exception $e ) {
						// Skip if unable to fetch info.
					}
				}

				$themes_result[] = $theme;
			}
		}

		return [
			'plugin'        => $plugins_result,
			'theme'         => array_values( $themes_result ),
			'php_extension' => $all_deps['php_extension'],
		];
	}

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

	public function maybe_add_php_extensions( array $new_extensions, array &$existing ): void {
		foreach ( $new_extensions as $ext_name ) {
			if ( ! in_array( $ext_name, $existing, true ) ) {
				$existing[] = $ext_name;
			}
		}
	}
}
