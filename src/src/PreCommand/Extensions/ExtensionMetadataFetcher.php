<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

/**
 * Fetches and caches metadata (version, download URLs) for extensions.
 *
 * ## Caching Strategy
 *
 * This class implements metadata caching to prevent repeated API calls:
 *
 * ### 1. Metadata Cache
 * - Caches API responses for version info and download URLs
 * - Cache duration: 30 seconds (prevents burst requests while keeping data fresh)
 * - Separate cache for WPORG and WCCOM metadata
 *
 * ### 2. Cache Keys
 * - WPORG: `wporg_metadata_[md5(slug_type_version)]`
 * - WCCOM: `wccom_metadata_[md5(slug_version)]`
 * - Includes version to handle different version requests
 *
 * ### 3. Cache-First Approach
 * - Checks cache before making ANY API calls
 * - Only fetches metadata for extensions not in cache
 * - Batch processes uncached items for efficiency
 *
 * ### 4. API Call Optimization
 * - **WPORG**: Individual API calls, but cached per extension
 * - **WCCOM**: Bulk API call for multiple extensions at once
 * - Both use cache to prevent repeated calls
 *
 * ### 5. Benefits
 * - Prevents rate limiting from WordPress.org API
 * - Reduces load on WooCommerce.com API
 * - Faster metadata resolution on repeated runs
 * - Shared cache across all test runs
 *
 * ### 6. Cache Flow
 * ```
 * fetch_metadata() called
 * ├─ Check cache for each extension
 * ├─ Use cached data if available
 * └─ Fetch only uncached items from API
 *     └─ Store in cache for future use
 * ```
 *
 * @see fetch_wporg_metadata() for WordPress.org caching
 * @see fetch_wccom_metadata() for WooCommerce.com caching
 */
class ExtensionMetadataFetcher {
	protected WooExtensionsList $woo_extensions_list;

	protected WPORGExtensionsList $wporg_extensions_list;

	protected Cache $cache;

	protected OutputInterface $output;

	public function __construct(
		WooExtensionsList $woo_extensions_list,
		WPORGExtensionsList $wporg_extensions_list,
		Cache $cache,
		OutputInterface $output
	) {
		$this->woo_extensions_list   = $woo_extensions_list;
		$this->wporg_extensions_list = $wporg_extensions_list;
		$this->cache                 = $cache;
		$this->output                = $output;
	}

	/**
	 * Fetch metadata for multiple extensions.
	 * Groups by source type for efficiency.
	 *
	 * @param Extension[] $extensions
	 *
	 * @throws \RuntimeException If metadata fetching fails.
	 */
	public function fetch_metadata( array $extensions ): void {
		// Group extensions by source type
		$grouped = [];
		foreach ( $extensions as $extension ) {
			if ( empty( $extension->from ) ) {
				throw new \RuntimeException( "Extensions '{$extension->slug}' has no source type set" );
			}
			$grouped[ $extension->from ][] = $extension;
		}

		// Process each group
		foreach ( $grouped as $source_type => $group ) {
			switch ( $source_type ) {
				case 'wporg':
					$this->fetch_wporg_metadata( $group );
					break;
				case 'wccom':
					$this->fetch_wccom_metadata( $group );
					break;
				case 'local':
				case 'build':
					// Local sources don't need metadata fetching
					$this->process_local_metadata( $group );
					break;
				case 'url':
					// URL sources use the URL as-is
					$this->process_url_metadata( $group );
					break;
				default:
					throw new \RuntimeException( "Unknown source type: $source_type" );
			}
		}
	}

	/**
	 * Fetch metadata for WPORG extensions with intelligent caching.
	 *
	 * This method implements per-extension caching to minimize API calls:
	 * 1. Checks cache for each extension's metadata
	 * 2. Uses cached data if available (no API call)
	 * 3. Only fetches from WordPress.org API if not cached
	 * 4. Caches fetched metadata for 30 seconds
	 *
	 * This prevents rate limiting from burst requests while ensuring
	 * we always check for the latest versions.
	 *
	 * @param Extension[] $extensions Array of WPORG extensions to process.
	 */
	/**
	 * @param array<\QIT_CLI\PreCommand\Objects\Extension> $extensions
	 */
	protected function fetch_wporg_metadata( array $extensions ): void {
		$start         = microtime( true );
		$fetched_count = 0;

		foreach ( $extensions as $extension ) {
			// Check cache first
			$cache_key       = 'wporg_metadata_' . md5( $extension->slug . '_' . $extension->type . '_' . ( $extension->version ?: 'stable' ) );
			$cached_metadata = $this->cache->get( $cache_key );

			if ( $cached_metadata && is_array( $cached_metadata ) ) {
				// Use cached metadata
				$extension->source  = $cached_metadata['source'];
				$extension->version = $cached_metadata['version'];
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( "Using cached metadata for WPORG extension: {$extension->slug}" );
				}
				continue;
			}

			// Fetch from API
			try {
				if ( $extension->type === 'plugin' ) {
					$info = $this->wporg_extensions_list->get_plugin_download_info( $extension->slug );
				} else {
					$info = $this->wporg_extensions_list->get_theme_download_info( $extension->slug );
				}

				$extension->source = $info['url'];
				// Only override version if it's not explicitly set or is 'stable'
				if ( $extension->version === 'stable' || $extension->version === 'undefined' ) {
					$extension->version = $info['version'];
				}

				// Cache the metadata
				// Cache metadata for 30 seconds to prevent API burst but still get fresh data
				$this->cache->set( $cache_key, [
					'source'  => $extension->source,
					'version' => $extension->version,
				], 30 );

				++$fetched_count;
			} catch ( \Exception $e ) {
				throw new \RuntimeException( "Failed to fetch WPORG metadata for '{$extension->slug}': " . $e->getMessage() );
			}
		}

		if ( $this->output->isVerbose() && $fetched_count > 0 ) {
			$this->output->writeln( sprintf(
				'Fetched metadata for %d WordPress.org extensions in %f seconds (%d cached).',
				$fetched_count,
				microtime( true ) - $start,
				count( $extensions ) - $fetched_count
			) );
		}
	}

	/**
	 * Fetch metadata for WCCOM extensions using bulk API.
	 */
	/**
	 * @param array<\QIT_CLI\PreCommand\Objects\Extension> $extensions
	 */
	protected function fetch_wccom_metadata( array $extensions ): void {
		if ( empty( $extensions ) ) {
			return;
		}

		// Check cache for each extension's metadata
		$extensions_needing_fetch = [];
		foreach ( $extensions as $extension ) {
			$cache_key       = 'wccom_metadata_' . md5( $extension->slug . '_' . ( $extension->version === 'undefined' ? 'stable' : $extension->version ) );
			$cached_metadata = $this->cache->get( $cache_key );

			if ( $cached_metadata && is_array( $cached_metadata ) ) {
				// Use cached metadata
				$extension->version = $cached_metadata['version'];
				$extension->source  = $cached_metadata['source'];
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( "Using cached metadata for WCCOM extension: {$extension->slug}" );
				}
			} else {
				// Need to fetch from API
				$extensions_needing_fetch[] = $extension;
			}
		}

		if ( empty( $extensions_needing_fetch ) ) {
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( 'All WCCOM extensions metadata cached, skipping API call' );
			}
			return;
		}

		$start     = microtime( true );
		$slugs     = array_map( fn( $ext ) => $ext->slug, $extensions_needing_fetch );
		$types_map = [];
		foreach ( $extensions_needing_fetch as $ext ) {
			$types_map[ $ext->slug ] = $ext->type;
		}

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'sut_slug'   => App::getVar( 'QIT_SUT_SLUG', '' ),
					'extensions' => implode( ',', $slugs ),
					'types'      => $types_map,
					'from'       => 'wccom',
				] )
				->request();

			$data = json_decode( $response, true );

			if ( ! is_array( $data ) || ! isset( $data['urls'] ) ) {
				throw new \RuntimeException( 'Invalid response from WCCOM API' );
			}

			foreach ( $extensions_needing_fetch as $extension ) {
				if ( isset( $data['urls'][ $extension->slug ] ) ) {
					$info               = $data['urls'][ $extension->slug ];
					$extension->slug    = $info['slug']; // May be different from requested
					$extension->version = $info['version'];
					$extension->source  = $info['url'];

					// Cache the metadata
					$cache_key = 'wccom_metadata_' . md5( $extension->slug . '_' . ( $extension->version === 'undefined' ? 'stable' : $extension->version ) );
					// Cache metadata for 30 seconds to prevent API burst but still get fresh data
					$this->cache->set( $cache_key, [
						'version' => $extension->version,
						'source'  => $extension->source,
					], 30 );
				} else {
					// Fallback for extensions not found
					$extension->version = 'stable';
					$extension->source  = '';
				}
			}
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'Failed to fetch WCCOM metadata: ' . $e->getMessage() );
		}

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf(
				'Fetched metadata for %d WooCommerce.com extensions in %f seconds.',
				count( $extensions ),
				microtime( true ) - $start
			) );
		}
	}

	/**
	 * Process local extensions metadata.
	 *
	 * @param array<\QIT_CLI\PreCommand\Objects\Extension> $extensions
	 */
	protected function process_local_metadata( array $extensions ): void {
		foreach ( $extensions as $extension ) {
			if ( empty( $extension->source ) && ! empty( $extension->directory ) ) {
				$extension->source = $extension->directory;
			}
			$extension->version = $this->extract_local_version( $extension ) ?: 'local';
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionMetadataFetcher: Local metadata for '{$extension->slug}': type={$extension->from}\n", FILE_APPEND );
		}
	}

	/**
	 * Extract version from a local plugin or theme's header.
	 *
	 * @param Extension $extension The extension to extract version from.
	 *
	 * @return string|null The version string, or null if not found.
	 */
	protected function extract_local_version( Extension $extension ): ?string {
		$source = $extension->source ?? $extension->directory ?? $extension->downloaded_source;

		if ( empty( $source ) || ! is_dir( $source ) ) {
			return null;
		}

		if ( $extension->type === 'theme' ) {
			// Themes have version in style.css
			$header_file = "$source/style.css";
		} else {
			// Plugins - try standard naming first
			$header_file = "$source/{$extension->slug}.php";
			if ( ! file_exists( $header_file ) ) {
				// Scan for PHP files with Plugin Name header
				$header_file = $this->find_plugin_header_file( $source );
			}
		}

		if ( empty( $header_file ) || ! file_exists( $header_file ) ) {
			return null;
		}

		// Read first 8KB of file to find header
		$contents = file_get_contents( $header_file, false, null, 0, 8192 );

		if ( preg_match( '/^\s*\*?\s*Version:\s*(.+)$/mi', $contents, $matches ) ) {
			return trim( $matches[1] );
		}

		return null;
	}

	/**
	 * Find the main plugin file with Plugin Name header.
	 *
	 * @param string $directory The plugin directory.
	 *
	 * @return string|null The path to the header file, or null if not found.
	 */
	protected function find_plugin_header_file( string $directory ): ?string {
		$iterator = new \DirectoryIterator( $directory );

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
				continue;
			}

			// Read first 8KB of file to find header
			$contents = file_get_contents( $file->getPathname(), false, null, 0, 8192 );

			if ( preg_match( '/^\s*\*\s*Plugin Name:/m', $contents ) ) {
				return $file->getPathname();
			}
		}

		return null;
	}

	/**
	 * Process URL extensions metadata.
	 *
	 * @param array<\QIT_CLI\PreCommand\Objects\Extension> $extensions
	 */
	protected function process_url_metadata( array $extensions ): void {
		foreach ( $extensions as $extension ) {
			// URL extensions use the URL as source
			if ( empty( $extension->source ) ) {
				throw new \RuntimeException( "URL extension '{$extension->slug}' has no source URL" );
			}

			// Version is not determined for URL sources
			$extension->version = 'url';

			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionMetadataFetcher: URL metadata for '{$extension->slug}': url={$extension->source}\n", FILE_APPEND );
		}
	}
}
