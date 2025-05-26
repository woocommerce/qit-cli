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

		// Convert plugin slugs to Extension objects
		$plugin_extensions = array_map( function ( $slug ) {
			$ext = new Extension( $slug, 'plugin' );
			$ext->populate_from();

			return $ext;
		}, $response['plugins'] );

		// Convert theme slugs to Extension objects
		$theme_extensions = array_map( function ( $slug ) {
			$ext = new Extension( $slug, 'theme' );
			$ext->populate_from();

			return $ext;
		}, $response['themes'] ?? [] );

		return [
			'plugin'        => $plugin_extensions,
			'theme'         => $theme_extensions,
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

			// Convert plugin slugs to Extension objects
			$plugin_extensions = array_map( function ( $slug ) {
				$ext = new Extension( $slug, 'plugin' );
				$ext->populate_from();
				try {
					$info         = $this->wporg_extensions_list->get_plugin_download_info( $slug );
					$ext->source  = $info['url'];
					$ext->version = $info['version'];
				} catch ( \Exception $e ) {
					// Skip if unable to fetch info
				}

				return $ext;
			}, $requires_plugins );

			$dependencies = [
				'plugin'        => $plugin_extensions,
				'theme'         => [], // Themes remain empty as WP.org plugins don't specify theme dependencies
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

	/**
	 * Get dependencies for plugins and themes.
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

		// Deduplicate plugins by slug, keeping the first occurrence
		$unique_plugins = [];
		foreach ( $all_deps['plugin'] as $plugin ) {
			if ( ! isset( $unique_plugins[ $plugin->slug ] ) ) {
				$unique_plugins[ $plugin->slug ] = $plugin;
			}
		}
		$all_deps['plugin'] = array_values( $unique_plugins );

		// Deduplicate themes by slug, keeping the first occurrence
		$unique_themes = [];
		foreach ( $all_deps['theme'] as $theme ) {
			if ( ! isset( $unique_themes[ $theme->slug ] ) ) {
				$unique_themes[ $theme->slug ] = $theme;
			}
		}
		$all_deps['theme'] = array_values( $unique_themes );

		// Deduplicate PHP extensions (still strings)
		$all_deps['php_extension'] = array_values( array_unique( $all_deps['php_extension'] ) );

		// Initialize plugins result with input plugins
		$plugins_result = [];
		foreach ( $plugins as $plugin ) {
			$exists = array_filter( $plugins_result, function ( $ext ) use ( $plugin ) {
				return $ext->slug === $plugin->slug;
			} );
			if ( empty( $exists ) ) {
				$plugins_result[] = $plugin;
			}
		}

		// Add dependency plugins not already present
		foreach ( $all_deps['plugin'] as $plugin ) {
			$exists = array_filter( $plugins_result, function ( $ext ) use ( $plugin ) {
				return $ext->slug === $plugin->slug;
			} );

			if ( empty( $exists ) ) {
				$plugin->priority            = Extension::PRIORITY_LOW;
				$plugin->added_automatically = 'Added as a dependency';
				$plugins_result[]            = $plugin;
			}
		}

		// Initialize themes result with input themes
		$themes_result = [];
		foreach ( $themes as $theme ) {
			$exists = array_filter( $themes_result, function ( $ext ) use ( $theme ) {
				return $ext->slug === $theme->slug;
			} );
			if ( empty( $exists ) ) {
				$themes_result[] = $theme;
			}
		}

		// Add dependency themes not already present
		foreach ( $all_deps['theme'] as $theme ) {
			$exists = array_filter( $themes_result, function ( $ext ) use ( $theme ) {
				return $ext->slug === $theme->slug;
			} );

			if ( empty( $exists ) ) {
				$theme->added_automatically = 'Added as a dependency';
				$theme->populate_from();
				$themes_result[] = $theme;
			}
		}

		return [
			'plugin'        => $plugins_result,
			'theme'         => $themes_result,
			'php_extension' => $all_deps['php_extension'],
		];
	}

	/**
	 * @param array<Extension> $new_deps
	 * @param array<Extension> $existing_plugins
	 *
	 * @return void
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
	 *
	 * @return void
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
				$dep_ext->added_automatically = 'Added as a dependency';
				$dep_ext->populate_from();
				$existing_themes[] = $dep_ext;
			}
		}
	}

	/**
	 * @param array<string> $new_extensions
	 * @param array<string> $existing
	 *
	 * @return void
	 */
	public function maybe_add_php_extensions( array $new_extensions, array &$existing ): void {
		foreach ( $new_extensions as $ext ) {
			if ( ! is_string( $ext ) ) {
				throw new \InvalidArgumentException( "PHP extension must be a string, got " . gettype( $ext ) );
			}
			if ( ! in_array( $ext, $existing, true ) ) {
				$existing[] = $ext;
			}
		}
	}
}