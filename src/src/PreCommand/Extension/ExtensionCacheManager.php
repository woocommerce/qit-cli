<?php

namespace QIT_CLI\PreCommand\Extension;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

/**
 * Manages extension caching and downloads.
 */
class ExtensionCacheManager {
	/** @var Cache */
	protected $cache;

	/** @var Zipper */
	protected $zipper;

	/** @var OutputInterface */
	protected $output;

	/** @var string[] */
	protected $download_handlers = [
		'wporg'     => 'download_from_url',
		'wccom'     => 'download_from_url',
		'url'       => 'download_from_url',
		'zip'       => 'copy_local_file',
		'build'     => 'copy_local_file',
		'directory' => 'use_directory',
	];

	public function __construct( Cache $cache, Zipper $zipper, OutputInterface $output ) {
		$this->cache  = $cache;
		$this->zipper = $zipper;
		$this->output = $output;
	}

	/**
	 * Ensure extension is cached and set downloaded_source.
	 *
	 * @param Extension $extension
	 * @param string $cache_dir
	 *
	 * @throws \RuntimeException
	 */
	public function ensure_cached( Extension $extension, string $cache_dir ): void {
		// Skip if already downloaded
		if ( ! empty( $extension->downloaded_source ) && file_exists( $extension->downloaded_source ) ) {
			return;
		}

		// Validate extension has required properties
		if ( empty( $extension->from ) ) {
			throw new \RuntimeException( "Extension '{$extension->slug}' has no source type" );
		}

		// Get handler method
		if ( ! isset( $this->download_handlers[ $extension->from ] ) ) {
			throw new \RuntimeException( "No download handler for source type '{$extension->from}'" );
		}

		$handler = $this->download_handlers[ $extension->from ];
		$this->$handler( $extension, $cache_dir );

		// Verify download
		if ( empty( $extension->downloaded_source ) ) {
			throw new \RuntimeException( "Failed to download extension '{$extension->slug}'" );
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionCacheManager: Cached '{$extension->slug}' at {$extension->downloaded_source}\n", FILE_APPEND );
	}

	/**
	 * Download from URL (WPORG, WCCOM, URL sources).
	 */
	protected function download_from_url( Extension $extension, string $cache_dir ): void {
		if ( empty( $extension->source ) ) {
			throw new \RuntimeException( "Extension '{$extension->slug}' has no download URL" );
		}

		$cache_file = $this->make_cache_path( $extension, $cache_dir );

		// Check if already cached
		if ( file_exists( $cache_file ) ) {
			// Validate cached file
			if ( $this->validate_cache( $cache_file, $extension ) ) {
				if ( $this->output->isVerbose() ) {
					$this->output->writeln( "Using cached {$extension->type} {$extension->slug}." );
				}
				$extension->downloaded_source = $cache_file;

				return;
			} else {
				// Invalid cache, remove it
				unlink( $cache_file );
			}
		}

		// Download file
		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( "Downloading {$extension->type} {$extension->slug} from {$extension->source}" );
		}

		RequestBuilder::download_file( $extension->source, $cache_file );

		// Validate download
		try {
			$this->zipper->validate_zip( $cache_file );
		} catch ( \Exception $e ) {
			unlink( $cache_file );
			throw new \RuntimeException( "Invalid ZIP file downloaded for '{$extension->slug}': " . $e->getMessage() );
		}

		$extension->downloaded_source = $cache_file;
	}

	/**
	 * Copy local file (ZIP, BUILD sources).
	 */
	protected function copy_local_file( Extension $extension, string $cache_dir ): void {
		$source_path = $extension->source;
		if ( empty( $source_path ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionCacheManager: No source path for '{$extension->slug}'\n", FILE_APPEND );
			throw new \RuntimeException( "Extension '{$extension->slug}' has no source path" );
		}
		file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionCacheManager: Checking source path: $source_path\n", FILE_APPEND );
		if ( ! file_exists( $source_path ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionCacheManager: Source file not found: $source_path\n", FILE_APPEND );
			throw new \RuntimeException( "Source file not found for '{$extension->slug}': $source_path" );
		}

		// Validate ZIP
		try {
			$this->zipper->validate_zip( $source_path );
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "Invalid ZIP file for '{$extension->slug}': " . $e->getMessage() );
		}

		$cache_file = $this->make_cache_path( $extension, $cache_dir );

		// Copy to cache if not already there
		if ( ! file_exists( $cache_file ) ) {
			if ( ! copy( $source_path, $cache_file ) ) {
				throw new \RuntimeException( "Failed to copy file for '{$extension->slug}'" );
			}
		}

		$extension->downloaded_source = $cache_file;
	}

	/**
	 * Use directory as-is (DIRECTORY sources).
	 */
	protected function use_directory( Extension $extension, string $cache_dir ): void {
		$directory = $extension->directory ?? $extension->source;
		if ( empty( $directory ) ) {
			throw new \RuntimeException( "Extension '{$extension->slug}' has no directory path" );
		}

		if ( ! is_dir( $directory ) ) {
			throw new \RuntimeException( "Directory not found for '{$extension->slug}': $directory" );
		}

		$extension->downloaded_source = $directory;
	}

	/**
	 * Validate cached file.
	 */
	protected function validate_cache( string $cache_file, Extension $extension ): bool {
		// For versioned extensions, check if cache is recent
		if ( ! empty( $extension->version ) && $extension->version !== 'local' && $extension->version !== 'url' ) {
			$cache_age = time() - filemtime( $cache_file );
			// Cache is valid for 24 hours for versioned extensions
			if ( $cache_age > DAY_IN_SECONDS ) {
				return false;
			}
		}

		// Validate ZIP integrity
		try {
			$this->zipper->validate_zip( $cache_file );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Create cache path for extension.
	 */
	protected function make_cache_path( Extension $extension, string $cache_dir ): string {
		// Validate inputs
		if ( ! in_array( $extension->type, [ 'plugin', 'theme' ], true ) ) {
			throw new \InvalidArgumentException( "Invalid extension type: {$extension->type}" );
		}

		if ( strpos( normalize_path( $cache_dir ), Config::get_qit_dir() ) !== 0 ) {
			throw new \InvalidArgumentException( "Cache dir must be inside QIT directory" );
		}

		// Create cache key components
		$type        = $extension->type;
		$slug        = $extension->slug;
		$version     = $extension->version ?? 'unknown';
		$source_hash = md5( $extension->source ?? $extension->from );
		$cache_burst = $this->get_cache_burst( $extension );

		// Build cache path
		$cache_path = "$cache_dir/$type/$slug-$source_hash-$version-$cache_burst.zip";

		// Ensure directory exists
		$dir = dirname( $cache_path );
		if ( ! file_exists( $dir ) ) {
			if ( ! mkdir( $dir, 0755, true ) ) {
				throw new \RuntimeException( "Could not create cache directory: $dir" );
			}
		}

		// Track cache access for cleanup
		$this->track_cache_access( $cache_path, $extension );

		return $cache_path;
	}

	/**
	 * Get cache burst string.
	 */
	protected function get_cache_burst( Extension $extension ): string {
		// For versioned extensions, use day of year
		if ( ! empty( $extension->version ) && ! in_array( $extension->version, [ 'local', 'url', 'undefined' ], true ) ) {
			return gmdate( 'z' );
		}

		// For unversioned extensions, use more granular cache burst
		return gmdate( 'YmdH' );
	}

	/**
	 * Track cache access for cleanup.
	 */
	protected function track_cache_access( string $cache_path, Extension $extension ): void {
		$last_accesses = $this->cache->get( 'last_extension_cache_access' ) ?? [];

		// Clean up old entries
		foreach ( $last_accesses as $key => $data ) {
			if ( $data['access'] < time() - WEEK_IN_SECONDS ) {
				if ( file_exists( $data['path'] ) && strpos( normalize_path( $data['path'] ), Config::get_qit_dir() ) === 0 ) {
					unlink( $data['path'] );
				}
				unset( $last_accesses[ $key ] );
			}
		}

		// Add current access
		$last_accesses[ $extension->slug ] = [
			'path'   => $cache_path,
			'access' => time(),
		];

		$this->cache->set( 'last_extension_cache_access', $last_accesses, MONTH_IN_SECONDS );
	}
}