<?php

namespace QIT_CLI\BreakingChanges;

use QIT_CLI\CachedDownloader;
use QIT_CLI\Zipper;

class PluginSourceResolver {
	private CachedDownloader $downloader;
	private Zipper $zipper;

	public function __construct( CachedDownloader $downloader, Zipper $zipper ) {
		$this->downloader = $downloader;
		$this->zipper     = $zipper;
	}

	/**
	 * Resolve a plugin slug or path to a local directory for analysis.
	 *
	 * @param string      $slug_or_path Plugin slug, local directory path, or local zip path.
	 * @param string|null $version      Optional version for WPORG downloads.
	 * @return string Path to the extracted plugin directory.
	 */
	public function resolve( string $slug_or_path, ?string $version = null ): string {
		// Local directory — use directly.
		if ( is_dir( $slug_or_path ) ) {
			return rtrim( $slug_or_path, DIRECTORY_SEPARATOR );
		}

		// Local zip file — extract to temp directory.
		if ( is_file( $slug_or_path ) && $this->is_zip_file( $slug_or_path ) ) {
			return $this->extract_zip( $slug_or_path );
		}

		// WPORG slug — download and extract.
		return $this->download_wporg( $slug_or_path, $version );
	}

	private function is_zip_file( string $path ): bool {
		return strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) === 'zip';
	}

	private function extract_zip( string $zip_path ): string {
		$extract_dir = sys_get_temp_dir() . '/qit-breaking-changes/' . basename( $zip_path, '.zip' ) . '-' . substr( md5( $zip_path ), 0, 8 );

		if ( is_dir( $extract_dir ) ) {
			return $this->find_plugin_root( $extract_dir );
		}

		mkdir( $extract_dir, 0755, true );

		$zip = new \ZipArchive();
		if ( $zip->open( $zip_path ) !== true ) {
			throw new \RuntimeException( "Failed to open zip file: {$zip_path}" );
		}

		$zip->extractTo( $extract_dir );
		$zip->close();

		return $this->find_plugin_root( $extract_dir );
	}

	private function download_wporg( string $slug, ?string $version ): string {
		$cache_dir = sys_get_temp_dir() . '/qit-breaking-changes/cache';
		$options   = [];

		if ( $version !== null ) {
			$options['version'] = $version;
		}

		$result    = $this->downloader->download( 'wporg_plugin', $slug, $cache_dir, $options );
		$zip_path  = $result['path'];

		return $this->extract_zip( $zip_path );
	}

	/**
	 * Find the actual plugin root directory inside an extracted zip.
	 * Most WordPress plugin zips contain a single top-level directory.
	 */
	private function find_plugin_root( string $extract_dir ): string {
		$entries = array_diff( scandir( $extract_dir ), [ '.', '..' ] );

		// If there's exactly one directory, that's the plugin root.
		if ( count( $entries ) === 1 ) {
			$single = $extract_dir . '/' . reset( $entries );
			if ( is_dir( $single ) ) {
				return $single;
			}
		}

		return $extract_dir;
	}
}
