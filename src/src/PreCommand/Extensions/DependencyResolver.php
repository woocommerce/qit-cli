<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\Cache;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use function QIT_CLI\get_manager_url;

/**
 * Resolves dependencies for extensions.
 */
class DependencyResolver {
	protected Cache $cache;

	protected WooExtensionsList $woo_extensions_list;

	protected WPORGExtensionsList $wporg_extensions_list;

	protected PluginMetadataParser $plugin_metadata_parser;

	public function __construct(
		Cache $cache,
		WooExtensionsList $woo_extensions_list,
		WPORGExtensionsList $wporg_extensions_list,
		PluginMetadataParser $plugin_metadata_parser
	) {
		$this->cache                  = $cache;
		$this->woo_extensions_list    = $woo_extensions_list;
		$this->wporg_extensions_list  = $wporg_extensions_list;
		$this->plugin_metadata_parser = $plugin_metadata_parser;
	}

	/**
	 * Resolve dependencies for a single extension.
	 *
	 * @param Extension $extension
	 *
	 * @return Extension[] Array of dependency extensions
	 */
	public function resolve_dependencies( Extension $extension ): array {
		$dependencies = [];

		// Parse dependencies from the extension files
		if ( ! empty( $extension->downloaded_source ) ) {
			$parsed_deps = $this->plugin_metadata_parser->parse( $extension->downloaded_source, $extension->type );

			foreach ( $parsed_deps as $dep_slug ) {
				$dep                      = new Extension( $dep_slug, 'plugin' );
				$dep->added_automatically = 'Added as a dependency';
				$dependencies[]           = $dep;
			}
		}

		// Get additional dependencies from APIs
		if ( $extension->from === 'wccom' && ! empty( $extension->wccom_id ) ) {
			$wccom_deps   = $this->get_wccom_dependencies( $extension->wccom_id );
			$dependencies = array_merge( $dependencies, $wccom_deps );
		} elseif ( $extension->from === 'wporg' && $extension->type === 'plugin' ) {
			$wporg_deps   = $this->get_wporg_dependencies( $extension->slug );
			$dependencies = array_merge( $dependencies, $wporg_deps );
		}

		// Deduplicate by slug
		$unique = [];
		foreach ( $dependencies as $dep ) {
			if ( ! isset( $unique[ $dep->slug ] ) ) {
				$unique[ $dep->slug ] = $dep;
			}
		}

		return array_values( $unique );
	}

	/**
	 * Get dependencies for multiple extensions.
	 *
	 * @param Extension[] $extensions
	 *
	 * @return array{plugin: Extension[], theme: Extension[], php_extension: string[]}
	 */
	public function get_all_dependencies( array $extensions ): array {
		$all_deps = [
			'plugin'        => [],
			'theme'         => [],
			'php_extension' => [],
		];

		// Collect WCCOM extension IDs for bulk query
		$wccom_ids = [];
		foreach ( $extensions as $ext ) {
			if ( ! empty( $ext->wccom_id ) ) {
				$wccom_ids[] = $ext->wccom_id;
			} elseif ( $ext->from === 'wccom' || $this->is_wccom_extension( $ext ) ) {
				try {
					$id          = $this->woo_extensions_list->get_woo_extension_id_by_slug( $ext->slug );
					$wccom_ids[] = $id;
				} catch ( \UnexpectedValueException $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// Not a WCCOM extension
				}
			}
		}

		// Get WCCOM dependencies in bulk
		if ( ! empty( $wccom_ids ) ) {
			$wccom_data                = $this->get_wccom_dependencies_bulk( $wccom_ids );
			$all_deps['plugin']        = array_merge( $all_deps['plugin'], $wccom_data['plugins'] );
			$all_deps['theme']         = array_merge( $all_deps['theme'], $wccom_data['themes'] );
			$all_deps['php_extension'] = array_merge( $all_deps['php_extension'], $wccom_data['php_extensions'] );
		}

		// Get WPORG dependencies
		foreach ( $extensions as $ext ) {
			if ( $ext->from === 'wporg' && $ext->type === 'plugin' ) {
				$wporg_deps         = $this->get_wporg_dependencies( $ext->slug );
				$all_deps['plugin'] = array_merge( $all_deps['plugin'], $wporg_deps );
			}
		}

		// Parse local dependencies
		foreach ( $extensions as $ext ) {
			if ( ! empty( $ext->downloaded_source ) ) {
				$parsed = $this->plugin_metadata_parser->parse( $ext->downloaded_source, $ext->type );
				foreach ( $parsed as $dep_slug ) {
					$dep                      = new Extension( $dep_slug, 'plugin' );
					$dep->added_automatically = 'Added as a dependency';
					$all_deps['plugin'][]     = $dep;
				}
			}
		}

		// Deduplicate
		$all_deps['plugin']        = $this->deduplicate_extensions( $all_deps['plugin'] );
		$all_deps['theme']         = $this->deduplicate_extensions( $all_deps['theme'] );
		$all_deps['php_extension'] = array_unique( $all_deps['php_extension'] );

		return $all_deps;
	}

	/**
	 * Check if extension is a WCCOM extension.
	 */
	protected function is_wccom_extension( Extension $extension ): bool {
		try {
			$this->woo_extensions_list->get_woo_extension_id_by_slug( $extension->slug );

			return true;
		} catch ( \UnexpectedValueException $e ) {
			return false;
		}
	}

	/**
	 * Get dependencies from WCCOM API.
	 *
	 * @return array<int, \QIT_CLI\PreCommand\Objects\Extension>
	 */
	protected function get_wccom_dependencies( int $wccom_id ): array {
		$data = $this->get_wccom_dependencies_bulk( [ $wccom_id ] );

		$dependencies = [];
		foreach ( $data['plugins'] as $plugin ) {
			$dependencies[] = $plugin;
		}
		foreach ( $data['themes'] as $theme ) {
			$dependencies[] = $theme;
		}

		return $dependencies;
	}

	/**
	 * Get dependencies from WCCOM API in bulk.
	 *
	 * @param array<int> $wccom_ids
	 * @return array{plugins: array<int, \QIT_CLI\PreCommand\Objects\Extension>, themes: array<int, \QIT_CLI\PreCommand\Objects\Extension>, php_extensions: array<string>}
	 */
	protected function get_wccom_dependencies_bulk( array $wccom_ids ): array {
		if ( empty( $wccom_ids ) ) {
			return [
				'plugins'        => [],
				'themes'         => [],
				'php_extensions' => [],
			];
		}

		$cache_key = sprintf( 'wccom_deps_%s', md5( implode( ',', $wccom_ids ) ) );
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			$data = json_decode( $cached, true );
		} else {
			$first_id = array_shift( $wccom_ids );

			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'sut_id'                       => $first_id,
					'additional_woo_extension_ids' => implode( ',', $wccom_ids ),
				] )
				->request();

			$data = json_decode( $response, true );
			if ( ! is_array( $data ) || ! isset( $data['plugins'], $data['php_extensions'] ) ) {
				throw new \RuntimeException( 'Invalid response from WCCOM dependencies API' );
			}

			// Cache dependencies for 30 seconds to prevent API burst but still get fresh data
			$this->cache->set( $cache_key, $response, 30 );
		}

		// Convert to Extensions objects
		$result = [
			'plugins'        => [],
			'themes'         => [],
			'php_extensions' => $data['php_extensions'] ?? [],
		];

		foreach ( $data['plugins'] ?? [] as $slug ) {
			$ext                      = new Extension( $slug, 'plugin' );
			$ext->added_automatically = 'Added as a dependency';
			$result['plugins'][]      = $ext;
		}

		foreach ( $data['themes'] ?? [] as $slug ) {
			$ext                      = new Extension( $slug, 'theme' );
			$ext->added_automatically = 'Added as a dependency';
			$result['themes'][]       = $ext;
		}

		return $result;
	}

	/**
	 * Get dependencies from WPORG API.
	 *
	 * @return array<int, \QIT_CLI\PreCommand\Objects\Extension>
	 */
	protected function get_wporg_dependencies( string $slug ): array {
		$cache_key = "wporg_deps_$slug";
		$cached    = $this->cache->get( $cache_key );

		if ( $cached ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Debug logging in CLI tool
			file_put_contents( '/tmp/qit/qit_debug.log', "DependencyResolver: Cached WPORG deps for $slug: " . print_r( $cached, true ) . "\n", FILE_APPEND );

			// Need to recreate Extension objects from cached arrays
			$dependencies = [];
			foreach ( $cached as $dep_data ) {
				if ( is_array( $dep_data ) ) {
					$ext                      = new Extension( $dep_data['slug'], $dep_data['type'] );
					$ext->added_automatically = $dep_data['added_automatically'];
					$ext->from                = $dep_data['from'];
					$dependencies[]           = $ext;
				} else {
					// Already an Extension object
					$dependencies[] = $dep_data;
				}
			}

			return $dependencies;
		}

		try {
			$response = ( new RequestBuilder( sprintf( $this->wporg_extensions_list->plugin_api_url, $slug ) ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();

			$raw_info = json_decode( $response, true );
			// file_put_contents( '/tmp/qit/qit_debug.log', "DependencyResolver: Raw info for $slug: " . print_r( $raw_info, true ) . "\n", FILE_APPEND );

			if ( ! is_array( $raw_info ) || ! isset( $raw_info['requires_plugins'] ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "DependencyResolver: No requires_plugins for $slug\n", FILE_APPEND );
				// Cache empty dependencies for 30 seconds
				$this->cache->set( $cache_key, [], 30 );

				return [];
			}

			$dependencies = [];
			foreach ( (array) $raw_info['requires_plugins'] as $dep_slug ) {
				$ext                      = new Extension( $dep_slug, 'plugin' );
				$ext->added_automatically = 'Added as a dependency';
				$ext->from                = 'wporg';
				$dependencies[]           = $ext;
			}

			// Cache the Extension objects for 30 seconds
			$this->cache->set( $cache_key, $dependencies, 30 );

			return $dependencies;
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "DependencyResolver: Failed WPORG deps for $slug: " . $e->getMessage() . "\n", FILE_APPEND );

			return [];
		}
	}

	/**
	 * Deduplicate extensions by slug.
	 *
	 * @param array<\QIT_CLI\PreCommand\Objects\Extension> $extensions
	 * @return array<\QIT_CLI\PreCommand\Objects\Extension>
	 */
	protected function deduplicate_extensions( array $extensions ): array {
		$unique = [];
		foreach ( $extensions as $ext ) {
			if ( ! isset( $unique[ $ext->slug ] ) ) {
				$unique[ $ext->slug ] = $ext;
			}
		}

		return array_values( $unique );
	}
}
