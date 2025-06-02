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
			file_put_contents( '/tmp/qit/qit_debug.log', "get_wporg_plugin_dependencies: Using cached dependencies for $slug: " . print_r( $cached, true ) . "\n", FILE_APPEND );
			return $cached;
		}

		try {
			$this->wporg_extensions_list->get_plugin_download_info( $slug );
			$response_body = ( new RequestBuilder( sprintf( $this->wporg_extensions_list->plugin_api_url, $slug ) ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();
			$raw_info      = @unserialize( $response_body );

			file_put_contents( '/tmp/qit/qit_debug.log', "get_wporg_plugin_dependencies: Raw info for $slug: " . print_r( $raw_info, true ) . "\n", FILE_APPEND );

			if ( ! is_object( $raw_info ) || ! isset( $raw_info->requires_plugins ) ) {
				$requires_plugins = [];
				file_put_contents( '/tmp/qit/qit_debug.log', "get_wporg_plugin_dependencies: No requires_plugins for $slug\n", FILE_APPEND );
			} else {
				$requires_plugins = (array) $raw_info->requires_plugins;
				file_put_contents( '/tmp/qit/qit_debug.log', "get_wporg_plugin_dependencies: requires_plugins for $slug: " . print_r( $requires_plugins, true ) . "\n", FILE_APPEND );
			}

			// Convert plugin slugs to Extension objects
			$plugin_extensions = array_map( function ( $dep_slug ) {
				$ext = new Extension( $dep_slug, 'plugin' );
				$ext->populate_from();
				try {
					$info                     = $this->wporg_extensions_list->get_plugin_download_info( $dep_slug );
					$ext->source              = $info['url'];
					$ext->version             = $info['version'];
					$ext->added_automatically = 'Added as a dependency';
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
	 * @param string           $dependencies_mode
	 *
	 * @return array{
	 *     plugin: array<Extension>,
	 *     theme: array<Extension>,
	 *     php_extension: array<string>
	 * }
	 */
	public function get_dependencies( array $plugins, array $themes, string $dependencies_mode ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "PluginDependencies: Delegating to DependencyResolver with mode: $dependencies_mode\n", FILE_APPEND );

		if ( $dependencies_mode === self::DEPENDENCY_MODES['env_only']['none'] ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "PluginDependencies: Mode is 'none', returning empty arrays\n", FILE_APPEND );
			return [
				'plugin'        => [],
				'theme'         => [],
				'php_extension' => [],
			];
		}

		$resolver = \QIT_CLI\App::make( \QIT_CLI\respondent\PreCommand\Extension\DependencyResolver::class );
		file_put_contents( '/tmp/qit/qit_debug.log', "PluginDependencies: Calling DependencyResolver::get_all_dependencies\n", FILE_APPEND );
		return $resolver->get_all_dependencies( array_merge( $plugins, $themes ) );
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
				$dep_ext->added_automatically = 'Added as a dependency';
				$from                         = $dep_ext->from; // Save the 'from' property
				$dep_ext->populate_from();
				if ( $from !== null ) {
					$dep_ext->from = $from; // Restore the 'from' property if it was set
				}
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
				$from                         = $dep_ext->from; // Save the 'from' property
				$dep_ext->populate_from();
				if ( $from !== null ) {
					$dep_ext->from = $from; // Restore the 'from' property if it was set
				}
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
				throw new \InvalidArgumentException( 'PHP extension must be a string, got ' . gettype( $ext ) );
			}
			if ( ! in_array( $ext, $existing, true ) ) {
				$existing[] = $ext;
			}
		}
	}
}
