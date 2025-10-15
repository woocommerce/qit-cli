<?php

namespace QIT_CLI;

use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

/**
 * Unified cached downloader that handles all download caching in one place.
 *
 * This single class handles:
 * - Querying remote APIs for metadata (version, checksum, download URL)
 * - Tracking what versions are cached locally
 * - Validating cache against remote metadata
 * - Downloading only when needed
 * - Managing the cache lifecycle
 *
 * Supports multiple resource types:
 * - Test packages (with SHA256 checksums)
 * - WPORG plugins/themes (with version + last_updated)
 * - WCCOM extensions (with version)
 * - Generic URLs (with optional checksums)
 */
class CachedDownloader {
	private Cache $cache;
	private Zipper $zipper;

	// Cache map key prefix
	private const CACHE_MAP_KEY = 'cached_downloads_map_v2';

	// Default cache durations
	private const METADATA_CACHE_DURATION = 30; // 30 seconds for metadata
	private const FILE_CACHE_DURATION     = DAY_IN_SECONDS; // 1 day for files

	public function __construct( Cache $cache, Zipper $zipper ) {
		$this->cache  = $cache;
		$this->zipper = $zipper;
	}

	/**
	 * Download a resource with intelligent caching.
	 * Automatically queries the appropriate API based on type.
	 *
	 * @param string              $type Resource type: 'test_package', 'wporg_plugin', 'wporg_theme', 'wccom_extension', 'url'.
	 * @param string              $identifier Unique identifier (e.g., 'woocommerce', 'namespace/package:1.0.0').
	 * @param string              $cache_dir Directory to cache files in.
	 * @param array<string,mixed> $options Optional: Additional options like explicit URL, version, etc.
	 * @return array{path: string, metadata: array<string,mixed>, cached: bool}
	 */
	public function download(
		string $type,
		string $identifier,
		string $cache_dir,
		array $options = []
	): array {
		// First, get remote metadata (with 30s cache to prevent bursts)
		$remote_metadata = $this->fetch_remote_metadata( $type, $identifier, $options );

		if ( empty( $remote_metadata['url'] ) ) {
			throw new \RuntimeException( "No download URL available for $type:$identifier" );
		}

		// Get our local cache map
		$cache_map = $this->get_cache_map();
		$cache_key = $this->make_cache_key( $type, $identifier );

		// Check if we have this cached
		if ( isset( $cache_map[ $cache_key ] ) ) {
			$local_cache = $cache_map[ $cache_key ];

			// Validate cache is still good
			if ( $this->is_cache_valid( $local_cache, $remote_metadata ) ) {
				// Cache is valid, return the path
				return [
					'path'     => $local_cache['path'],
					'metadata' => $remote_metadata,
					'cached'   => true,
				];
			}

			// Cache is invalid, clean it up
			$this->cleanup_invalid_cache( $local_cache );
		}

		// Need to download
		$file_path = $this->perform_download(
			$type,
			$identifier,
			$remote_metadata,
			$cache_dir
		);

		// Update cache map
		$this->update_cache_map( $cache_key, $file_path, $remote_metadata );

		return [
			'path'     => $file_path,
			'metadata' => $remote_metadata,
			'cached'   => false,
		];
	}

	/**
	 * Fetch metadata from the appropriate remote API.
	 *
	 * @param string                    $type
	 * @param string                    $identifier
	 * @param array<string,string|null> $options
	 * @return array<string,string|int|null>
	 */
	private function fetch_remote_metadata( string $type, string $identifier, array $options ): array {
		// Check short-lived metadata cache first (30 seconds)
		$metadata_cache_key = "remote_metadata_{$type}_{$identifier}";
		$cached_metadata    = $this->cache->get( $metadata_cache_key );

		if ( is_array( $cached_metadata ) ) {
			return $cached_metadata;
		}

		// Fetch based on type
		switch ( $type ) {
			case 'test_package':
				$metadata = $this->fetch_test_package_metadata( $identifier, $options );
				break;
			case 'wporg_plugin':
				$metadata = $this->fetch_wporg_plugin_metadata( $identifier );
				break;
			case 'wporg_theme':
				$metadata = $this->fetch_wporg_theme_metadata( $identifier );
				break;
			case 'wccom_extension':
				$metadata = $this->fetch_wccom_metadata( $identifier, $options );
				break;
			case 'url':
				$metadata = $this->fetch_url_metadata( $identifier, $options );
				break;
			default:
				throw new \InvalidArgumentException( "Unknown type: $type" );
		}

		// Cache for 30 seconds to prevent burst requests
		$this->cache->set( $metadata_cache_key, $metadata, self::METADATA_CACHE_DURATION );

		return $metadata;
	}

	/**
	 * Fetch test package metadata from QIT Manager.
	 *
	 * @param string                    $identifier
	 * @param array<string,string|null> $options
	 * @return array<string,string|int|null>
	 */
	private function fetch_test_package_metadata( string $identifier, array $options ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v2/cli/test-package-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'package_ids' => [ $identifier ],
			] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || ! isset( $data['urls'][ $identifier ] ) ) {
			throw new \RuntimeException( "Failed to fetch metadata for test package: $identifier" );
		}

		$info = $data['urls'][ $identifier ];

		return [
			'url'      => $info['url'] ?? null,
			'version'  => $info['version'] ?? null,
			'checksum' => $info['checksum'] ?? null, // SHA256
			'size'     => $info['size'] ?? null,
		];
	}

	/**
	 * Fetch WordPress.org plugin metadata.
	 *
	 * @param string $slug
	 * @return array<string,string|null>
	 */
	private function fetch_wporg_plugin_metadata( string $slug ): array {
		$url = "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$slug}";

		$response = ( new RequestBuilder( $url ) )
			->with_method( 'GET' )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || empty( $data['download_link'] ) ) {
			throw new \RuntimeException( "Failed to fetch metadata for WPORG plugin: $slug" );
		}

		return [
			'url'          => $data['download_link'],
			'version'      => $data['version'] ?? 'unknown',
			'last_updated' => $data['last_updated'] ?? null,
			// WPORG doesn't provide checksums in API
		];
	}

	/**
	 * Fetch WordPress.org theme metadata.
	 *
	 * @param string $slug
	 * @return array<string,string|null>
	 */
	private function fetch_wporg_theme_metadata( string $slug ): array {
		$url = "https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]={$slug}";

		$response = ( new RequestBuilder( $url ) )
			->with_method( 'GET' )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || empty( $data['download_link'] ) ) {
			throw new \RuntimeException( "Failed to fetch metadata for WPORG theme: $slug" );
		}

		return [
			'url'          => $data['download_link'],
			'version'      => $data['version'] ?? 'unknown',
			'last_updated' => $data['last_updated'] ?? null,
		];
	}

	/**
	 * Fetch WooCommerce.com extension metadata.
	 *
	 * @param string                    $identifier
	 * @param array<string,string|null> $options
	 * @return array<string,string|null>
	 */
	private function fetch_wccom_metadata( string $identifier, array $options ): array {
		// This would need the authenticated WCCOM API
		// For now, return what's passed in options
		if ( ! empty( $options['url'] ) ) {
			return [
				'url'     => $options['url'],
				'version' => $options['version'] ?? 'unknown',
			];
		}

		throw new \RuntimeException( 'WCCOM metadata fetching requires authenticated API setup' );
	}

	/**
	 * Handle generic URL metadata.
	 *
	 * @param string                    $url
	 * @param array<string,string|null> $options
	 * @return array<string,string|null>
	 */
	private function fetch_url_metadata( string $url, array $options ): array {
		return [
			'url'      => $url,
			'version'  => $options['version'] ?? 'unknown',
			'checksum' => $options['checksum'] ?? null,
		];
	}

	/**
	 * Check if cached file is still valid.
	 *
	 * @param array<string,string|int|null> $local_cache
	 * @param array<string,string|int|null> $remote_metadata
	 * @return bool
	 */
	private function is_cache_valid( array $local_cache, array $remote_metadata ): bool {
		// Check if file still exists
		if ( ! file_exists( $local_cache['path'] ) ) {
			return false;
		}

		// Check age (max 1 day)
		if ( time() - $local_cache['cached_at'] > self::FILE_CACHE_DURATION ) {
			return false;
		}

		// If we have checksums, compare them (most reliable)
		if ( ! empty( $remote_metadata['checksum'] ) && ! empty( $local_cache['checksum'] ) ) {
			return $remote_metadata['checksum'] === $local_cache['checksum'];
		}

		// If we have versions, compare them
		if ( ! empty( $remote_metadata['version'] ) && ! empty( $local_cache['version'] ) ) {
			// Special handling for rolling versions
			if ( $this->is_rolling_version( $remote_metadata['version'] ) ) {
				// For rolling versions, also check last_updated if available
				if ( ! empty( $remote_metadata['last_updated'] ) && ! empty( $local_cache['last_updated'] ) ) {
					return $remote_metadata['last_updated'] === $local_cache['last_updated'];
				}
				// If no last_updated, be conservative and re-download
				return false;
			}

			return $remote_metadata['version'] === $local_cache['version'];
		}

		// If we can't determine validity, be safe and re-download
		return false;
	}

	/**
	 * Check if this is a rolling version that changes frequently.
	 */
	private function is_rolling_version( string $version ): bool {
		$rolling       = [ 'latest', 'stable', 'trunk', 'dev', 'nightly', 'rc', 'beta', 'alpha' ];
		$version_lower = strtolower( $version );

		foreach ( $rolling as $keyword ) {
			if ( $version_lower === $keyword || str_contains( $version_lower, $keyword ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Perform the actual download.
	 *
	 * @param string                        $type
	 * @param string                        $identifier
	 * @param array<string,string|int|null> $remote_metadata
	 * @param string                        $cache_dir
	 * @return string
	 */
	private function perform_download(
		string $type,
		string $identifier,
		array $remote_metadata,
		string $cache_dir
	): string {
		// Generate file path
		$file_name = $this->generate_filename( $type, $identifier, $remote_metadata );
		$file_path = $cache_dir . '/' . $file_name;

		// Ensure directory exists
		$dir = dirname( $file_path );
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}

		// Download the file
		RequestBuilder::download_file( $remote_metadata['url'], $file_path );

		// Validate if it's a ZIP
		if ( str_ends_with( $file_path, '.zip' ) ) {
			$this->zipper->validate_zip( $file_path );
		}

		return $file_path;
	}

	/**
	 * Generate a cache filename.
	 *
	 * @param string              $type Resource type.
	 * @param string              $identifier Resource identifier.
	 * @param array<string,mixed> $metadata Metadata.
	 * @return string
	 */
	private function generate_filename( string $type, string $identifier, array $metadata ): string {
		// Clean up identifier for filename
		$safe_id = preg_replace( '/[^a-zA-Z0-9_-]/', '_', $identifier );

		// Include version or checksum in filename for clarity
		$suffix = '';
		if ( ! empty( $metadata['version'] ) ) {
			$suffix = '-' . preg_replace( '/[^a-zA-Z0-9_.-]/', '_', $metadata['version'] );
		} elseif ( ! empty( $metadata['checksum'] ) ) {
			$suffix = '-' . substr( $metadata['checksum'], 0, 8 );
		}

		// Add timestamp for uniqueness
		$timestamp = gmdate( 'Ymd-His' );

		// Determine extension
		$extension = $metadata['extension'] ?? 'zip';

		return "{$type}/{$safe_id}{$suffix}-{$timestamp}.{$extension}";
	}

	/**
	 * Get the cache map from storage.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_cache_map(): array {
		$map = $this->cache->get( self::CACHE_MAP_KEY );
		return is_array( $map ) ? $map : [];
	}

	/**
	 * Update the cache map with new entry.
	 *
	 * @param string                        $cache_key
	 * @param string                        $file_path
	 * @param array<string,string|int|null> $metadata
	 * @return void
	 */
	private function update_cache_map( string $cache_key, string $file_path, array $metadata ): void {
		$map = $this->get_cache_map();

		$map[ $cache_key ] = [
			'path'         => $file_path,
			'cached_at'    => time(),
			'version'      => $metadata['version'] ?? null,
			'checksum'     => $metadata['checksum'] ?? null,
			'last_updated' => $metadata['last_updated'] ?? null,
		];

		// Store for 30 days (longer than file cache to track history)
		$this->cache->set( self::CACHE_MAP_KEY, $map, 30 * DAY_IN_SECONDS );
	}

	/**
	 * Clean up invalid cache entry.
	 *
	 * @param array<string,string|int|null> $local_cache
	 * @return void
	 */
	private function cleanup_invalid_cache( array $local_cache ): void {
		if ( ! empty( $local_cache['path'] ) && file_exists( $local_cache['path'] ) ) {
			// Only delete if it's in our cache directory
			if ( str_contains( $local_cache['path'], Config::get_qit_dir() ) ) {
				unlink( $local_cache['path'] );
			}
		}
	}

	/**
	 * Make a cache key for the map.
	 */
	private function make_cache_key( string $type, string $identifier ): string {
		return $type . ':' . $identifier;
	}

	/**
	 * Clear cache for a specific item.
	 */
	public function clear_cache( string $type, string $identifier ): void {
		$cache_map = $this->get_cache_map();
		$cache_key = $this->make_cache_key( $type, $identifier );

		if ( isset( $cache_map[ $cache_key ] ) ) {
			$this->cleanup_invalid_cache( $cache_map[ $cache_key ] );
			unset( $cache_map[ $cache_key ] );
			$this->cache->set( self::CACHE_MAP_KEY, $cache_map, 30 * DAY_IN_SECONDS );
		}

		// Also clear metadata cache
		$metadata_cache_key = "remote_metadata_{$type}_{$identifier}";
		$this->cache->delete( $metadata_cache_key );
	}

	/**
	 * Get info about what's cached.
	 *
	 * @param string $type
	 * @param string $identifier
	 * @return array{path: string, version: string, checksum?: string}|null
	 */
	public function get_cached_info( string $type, string $identifier ): ?array {
		$cache_map = $this->get_cache_map();
		$cache_key = $this->make_cache_key( $type, $identifier );

		return $cache_map[ $cache_key ] ?? null;
	}
}
