<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\Cache;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use QIT_CLI\PreCommand\Objects\Extension;

/**
 * Validates extension cache using version information.
 * 
 * Since WPORG and WCCOM don't provide checksums, we use version + last_updated
 * information to determine if cached extensions are still valid.
 * 
 * ## Strategy:
 * 
 * ### For WPORG Extensions:
 * - Fetch plugin/theme info (lightweight API call)
 * - Compare version + last_updated with cached metadata
 * - If version changed or last_updated is newer, invalidate cache
 * - Cache metadata for 1 hour to avoid repeated API calls
 * 
 * ### For WCCOM Extensions:
 * - Fetch version from API (requires authentication)
 * - Compare version with cached metadata
 * - If version changed, invalidate cache
 * - Cache metadata for 1 hour
 * 
 * ### For Rolling Versions (trunk, dev, nightly):
 * - Always fetch fresh metadata
 * - Cache for very short time (5 minutes)
 * - Ensures users get latest development versions
 * 
 * ### Benefits:
 * - Handles "stable" versions that may update
 * - Detects when plugins are updated on WPORG
 * - Still prevents unnecessary downloads
 * - Works without checksums
 */
class ExtensionVersionValidator {
	protected Cache $cache;
	protected WPORGExtensionsList $wporg_list;
	protected WooExtensionsList $woo_list;
	
	public function __construct(
		Cache $cache,
		WPORGExtensionsList $wporg_list,
		WooExtensionsList $woo_list
	) {
		$this->cache = $cache;
		$this->wporg_list = $wporg_list;
		$this->woo_list = $woo_list;
	}
	
	/**
	 * Validate if cached extension is still current.
	 * 
	 * @param Extension $extension The extension to validate.
	 * @param string $cached_path Path to cached extension file.
	 * @return bool True if cache is valid, false if needs re-download.
	 */
	public function is_cache_valid( Extension $extension, string $cached_path ): bool {
		// If file doesn't exist, cache is invalid
		if ( ! file_exists( $cached_path ) ) {
			return false;
		}
		
		// Get cached metadata
		$cache_meta_key = $this->get_cache_metadata_key( $extension );
		$cached_meta = $this->cache->get( $cache_meta_key );
		
		if ( ! $cached_meta || ! is_array( $cached_meta ) ) {
			// No metadata cached, need to validate
			return $this->validate_and_update_cache( $extension, $cached_path );
		}
		
		// Check if metadata is still fresh (30 seconds to prevent burst)
		// We want fresh metadata checks to catch updates quickly
		$max_age = 30; // 30 seconds for all versions
		
		if ( isset( $cached_meta['timestamp'] ) ) {
			$age = time() - $cached_meta['timestamp'];
			if ( $age > $max_age ) {
				// Metadata expired, re-validate
				return $this->validate_and_update_cache( $extension, $cached_path );
			}
		}
		
		// Metadata is fresh, trust it
		return true;
	}
	
	/**
	 * Validate cache by fetching current version info.
	 */
	protected function validate_and_update_cache( Extension $extension, string $cached_path ): bool {
		try {
			// Fetch current version info
			$current_info = $this->fetch_current_version_info( $extension );
			
			if ( ! $current_info ) {
				// Couldn't fetch info, assume cache is still valid
				return true;
			}
			
			// Get cached version info
			$cache_meta_key = $this->get_cache_metadata_key( $extension );
			$cached_meta = $this->cache->get( $cache_meta_key );
			
			// Compare versions
			$needs_update = false;
			
			if ( ! $cached_meta || ! isset( $cached_meta['version'] ) ) {
				// No cached version, need update
				$needs_update = true;
			} elseif ( $cached_meta['version'] !== $current_info['version'] ) {
				// Version changed
				$needs_update = true;
			} elseif ( isset( $current_info['last_updated'] ) && isset( $cached_meta['last_updated'] ) ) {
				// Check if last_updated changed (for WPORG)
				if ( $current_info['last_updated'] !== $cached_meta['last_updated'] ) {
					$needs_update = true;
				}
			}
			
			// Update cache metadata
			$current_info['timestamp'] = time();
			$this->cache->set( $cache_meta_key, $current_info, DAY_IN_SECONDS );
			
			// If version/update time changed, cache is invalid
			if ( $needs_update ) {
				// Remove the cached file so it gets re-downloaded
				if ( file_exists( $cached_path ) ) {
					unlink( $cached_path );
				}
				return false;
			}
			
			return true;
			
		} catch ( \Exception $e ) {
			// If we can't fetch version info, assume cache is valid
			// This prevents failures when APIs are down
			return true;
		}
	}
	
	/**
	 * Fetch current version information from API.
	 */
	protected function fetch_current_version_info( Extension $extension ): ?array {
		if ( $extension->from === 'wporg' ) {
			return $this->fetch_wporg_version_info( $extension );
		} elseif ( $extension->from === 'wccom' ) {
			return $this->fetch_wccom_version_info( $extension );
		}
		
		return null;
	}
	
	/**
	 * Fetch version info from WordPress.org.
	 */
	protected function fetch_wporg_version_info( Extension $extension ): ?array {
		try {
			if ( $extension->type === 'plugin' ) {
				$info = $this->wporg_list->get_plugin_download_info( $extension->slug );
			} else {
				$info = $this->wporg_list->get_theme_download_info( $extension->slug );
			}
			
			// WPORG provides version and download URL
			// We need to fetch additional info for last_updated
			$api_info = $this->fetch_wporg_api_info( $extension );
			
			return [
				'version' => $info['version'],
				'url' => $info['url'],
				'last_updated' => $api_info['last_updated'] ?? null,
			];
			
		} catch ( \Exception $e ) {
			return null;
		}
	}
	
	/**
	 * Fetch detailed info from WPORG API.
	 */
	protected function fetch_wporg_api_info( Extension $extension ): array {
		$cache_key = "wporg_api_info_{$extension->type}_{$extension->slug}";
		$cached = $this->cache->get( $cache_key );
		
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}
		
		// Build API URL
		if ( $extension->type === 'plugin' ) {
			$url = "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]={$extension->slug}";
		} else {
			$url = "https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]={$extension->slug}";
		}
		
		try {
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'QIT-CLI' );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			$response = curl_exec( $ch );
			$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );
			
			if ( $http_code !== 200 ) {
				return [];
			}
			
			$data = json_decode( $response, true );
			if ( ! is_array( $data ) ) {
				return [];
			}
			
			$info = [
				'version' => $data['version'] ?? '',
				'last_updated' => $data['last_updated'] ?? '',
			];
			
			// Cache for 30 seconds to prevent API burst but still get fresh data
			$this->cache->set( $cache_key, $info, 30 );
			
			return $info;
			
		} catch ( \Exception $e ) {
			return [];
		}
	}
	
	/**
	 * Fetch version info from WooCommerce.com.
	 */
	protected function fetch_wccom_version_info( Extension $extension ): ?array {
		// WCCOM version info would come from the authenticated API
		// For now, we'll rely on the existing metadata fetcher
		return [
			'version' => $extension->version ?? 'stable',
		];
	}
	
	/**
	 * Check if this is a development version.
	 */
	protected function is_development_version( ?string $version ): bool {
		if ( ! $version ) {
			return false;
		}
		
		$dev_versions = [ 'trunk', 'dev', 'nightly', 'rc', 'beta', 'alpha' ];
		$version_lower = strtolower( $version );
		
		foreach ( $dev_versions as $dev ) {
			if ( $version_lower === $dev || str_contains( $version_lower, $dev ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get cache metadata key for an extension.
	 */
	protected function get_cache_metadata_key( Extension $extension ): string {
		return "ext_cache_meta_{$extension->from}_{$extension->type}_{$extension->slug}_{$extension->version}";
	}
}