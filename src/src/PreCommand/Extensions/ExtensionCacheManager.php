<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Validation\ArtifactValidator;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\debug_log;
use function QIT_CLI\normalize_path;

/**
 * Manages extension caching and downloads with intelligent cache validation.
 *
 * ## Caching Strategy
 *
 * This class handles the physical caching of extension files (plugins/themes):
 *
 * ### 1. Cache Location
 * - WPORG/WCCOM/URL extensions: Cached as ZIP files in cache directory
 * - Local extensions: Used directly from their source location (no caching)
 * - Cache path format: `[cache_dir]/[type]_[slug]_[version].zip`
 *
 * ### 2. Cache Validation (is_cached method)
 * - Checks if extension file exists and is valid WITHOUT downloading
 * - For ZIP files: Validates using ZipArchive::CHECKCONS
 * - For local directories: Verifies directory exists
 * - Returns true only if extension is ready to use
 *
 * ### 3. Cache Usage Flow
 * ```
 * ExtensionResolver calls is_cached() first
 * ├─ If cached: Skip metadata fetch & download
 * └─ If not cached: Fetch metadata, then ensure_cached()
 * ```
 *
 * ### 4. Download Optimization
 * - Only downloads if not already cached
 * - Validates downloads before caching
 * - Detects entry points after download
 *
 * ### 5. Benefits
 * - Prevents redundant downloads
 * - Validates cache integrity
 * - Supports multiple source types (wporg, wccom, url, local, build)
 * - No API calls for cached extensions
 *
 * @see is_cached() for cache validation without downloading
 * @see ensure_cached() for download and cache storage
 */
class ExtensionCacheManager {
	protected Cache $cache;

	protected Zipper $zipper;

	protected OutputInterface $output;

	protected EntrypointDetector $entrypoint_detector;

	protected ArtifactValidator $artifact_validator;

	/** @var string[] */
	protected $download_handlers = [
		'wporg' => 'download_from_url',
		'wccom' => 'download_from_url',
		'url'   => 'download_from_url',
		'local' => 'handle_local_source',
		'build' => 'copy_local_file',
	];

	public function __construct( Cache $cache, Zipper $zipper, OutputInterface $output, EntrypointDetector $entrypoint_detector, ArtifactValidator $artifact_validator ) {
		$this->cache               = $cache;
		$this->zipper              = $zipper;
		$this->output              = $output;
		$this->entrypoint_detector = $entrypoint_detector;
		$this->artifact_validator  = $artifact_validator;
	}


	/**
	 * Check if an extension is already cached without downloading or making API calls.
	 *
	 * This method is critical for preventing unnecessary downloads and API calls:
	 * - Called by ExtensionResolver BEFORE fetching metadata
	 * - Returns true only if extension is fully ready to use
	 * - Does NOT make any network requests
	 *
	 * Cache validation by source type:
	 * - **Local**: Checks if directory/file exists at source path
	 * - **WPORG/WCCOM/URL**: Checks for valid ZIP file in cache directory
	 * - **Already downloaded**: Checks downloaded_source property
	 *
	 * ZIP validation ensures cache integrity:
	 * - Uses ZipArchive::CHECKCONS to verify ZIP structure
	 * - Removes corrupt cache files automatically
	 * - Sets downloaded_source for ensure_cached() to skip download
	 *
	 * @param Extension $extension The extension to check.
	 * @param string    $cache_dir Cache directory path.
	 *
	 * @return bool True if cached and valid (no download needed), false otherwise.
	 */
	public function is_cached( Extension $extension, string $cache_dir ): bool {
		debug_log( "ExtensionCacheManager: Checking cache for '{$extension->slug}' from '{$extension->from}'" );

		// Already downloaded
		if ( ! empty( $extension->downloaded_source ) && file_exists( $extension->downloaded_source ) ) {
			debug_log( "  Already has downloaded_source: {$extension->downloaded_source}" );
			return true;
		}

		// Check for local sources
		if ( $extension->from === 'local' ) {
			$source_path = $extension->directory ?? $extension->source;
			if ( ! empty( $source_path ) && ( is_dir( $source_path ) || is_file( $source_path ) ) ) {
				debug_log( "  Local source exists: $source_path" );
				return true;
			}
		}

		// Check for cached remote extensions
		if ( in_array( $extension->from, [ 'wporg', 'wccom', 'url' ], true ) ) {
			$cache_file = $this->make_cache_path( $extension, $cache_dir );

			if ( file_exists( $cache_file ) ) {
				// Check if it's a valid zip
				$zip = new \ZipArchive();
				if ( $zip->open( $cache_file, \ZipArchive::CHECKCONS ) === true ) {
					$zip->close();

					// Check cache age (max 1 day)
					$age = time() - filemtime( $cache_file );
					if ( $age > DAY_IN_SECONDS ) {
						debug_log( '  Cache expired: older than 1 day' );
						return false;
					}

					debug_log( "  Found valid cached file: $cache_file" );
					// Set the downloaded_source so ensure_cached can use it
					$extension->downloaded_source = $cache_file;
					return true;
				} else {
					// Invalid ZIP, remove it
					unlink( $cache_file );
				}
			}
		}

		debug_log( '  Not cached' );
		return false;
	}

	/**
	 * Ensure extension is cached and set downloaded_source.
	 *
	 * @param Extension $extension
	 * @param string    $cache_dir
	 *
	 * @throws \RuntimeException If caching fails or file operations fail.
	 */
	public function ensure_cached( Extension $extension, string $cache_dir ): void {
		debug_log( "ExtensionCacheManager: Ensuring cached for '{$extension->slug}' from '{$extension->from}'" );

		// Skip if already downloaded
		if ( ! empty( $extension->downloaded_source ) && file_exists( $extension->downloaded_source ) ) {
			debug_log( "  Already downloaded at: {$extension->downloaded_source}" );

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
		debug_log( "  Using handler: $handler" );
		$this->$handler( $extension, $cache_dir );

		// Verify download
		if ( empty( $extension->downloaded_source ) ) {
			throw new \RuntimeException( "Failed to download extension '{$extension->slug}'" );
		}

		debug_log( "  Cached '{$extension->slug}' at {$extension->downloaded_source}" );
	}

	/**
	 * Handle local source (can be either directory or zip file).
	 */
	protected function handle_local_source( Extension $extension, string $cache_dir ): void {
		debug_log( "ExtensionCacheManager: Handling local source for '{$extension->slug}'" );

		// Determine the source path
		$source_path = $extension->directory ?? $extension->source;
		if ( empty( $source_path ) ) {
			throw new \RuntimeException( "Extension '{$extension->slug}' has no source path" );
		}

		debug_log( "  Source path: $source_path" );

		// Check if it's a directory
		if ( is_dir( $source_path ) ) {
			debug_log( '  Source is a directory' );
			$extension->downloaded_source = $source_path;

			// Validate artifact integrity
			try {
				$this->artifact_validator->validate_extension( $extension );
			} catch ( \Exception $e ) {
				throw new \RuntimeException( "Local {$extension->type} artifact failed validation: " . $e->getMessage() );
			}

			// Detect entrypoint
			$this->entrypoint_detector->detect( $extension );

			return;
		}

		// Check if it's a zip file
		if ( is_file( $source_path ) && pathinfo( $source_path, PATHINFO_EXTENSION ) === 'zip' ) {
			debug_log( '  Source is a zip file' );
			$this->copy_local_file( $extension, $cache_dir );

			return;
		}

		// Neither directory nor zip file
		throw new \RuntimeException( "Local source for '{$extension->slug}' is neither a directory nor a zip file: $source_path" );
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

				// Validate artifact integrity
				try {
					$this->artifact_validator->validate_extension( $extension );
				} catch ( \Exception $e ) {
					// Cached artifact is invalid, remove it
					unlink( $cache_file );
					throw new \RuntimeException( "Cached {$extension->type} artifact failed validation: " . $e->getMessage() );
				}

				// Detect entrypoint
				$this->entrypoint_detector->detect( $extension );

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

		// Validate artifact integrity
		try {
			$this->artifact_validator->validate_extension( $extension );
		} catch ( \Exception $e ) {
			// Downloaded artifact is invalid, remove it
			unlink( $cache_file );
			throw new \RuntimeException( "Downloaded {$extension->type} artifact failed validation: " . $e->getMessage() );
		}

		// Detect entrypoint
		$this->entrypoint_detector->detect( $extension );
	}

	/**
	 * Copy local file (BUILD sources and local zip files).
	 */
	protected function copy_local_file( Extension $extension, string $cache_dir ): void {
		$source_path = $extension->source ?? $extension->directory;
		if ( empty( $source_path ) ) {
			debug_log( "ExtensionCacheManager: No source path for '{$extension->slug}'" );
			throw new \RuntimeException( "Extension '{$extension->slug}' has no source path" );
		}

		debug_log( "ExtensionCacheManager: Copying local file from: $source_path" );

		if ( ! file_exists( $source_path ) ) {
			debug_log( "  Source file not found: $source_path", 'error' );
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

		// Validate artifact integrity
		try {
			$this->artifact_validator->validate_extension( $extension );
		} catch ( \Exception $e ) {
			// Local artifact is invalid, don't cache it
			unlink( $cache_file );
			throw new \RuntimeException( "Local {$extension->type} artifact failed validation: " . $e->getMessage() );
		}

		// Detect entrypoint
		$this->entrypoint_detector->detect( $extension );
	}

	/**
	 * Validate cached file.
	 */
	protected function validate_cache( string $cache_file, Extension $extension ): bool {
		// For versioned extensions, check if cache is recent
		if ( ! empty( $extension->version ) && $extension->version !== 'local' && $extension->version !== 'url' ) {
			$cache_age = time() - filemtime( $cache_file );

			// Check if this is a special version that changes frequently
			$is_special_version = in_array( $extension->version, [ 'rc', 'nightly' ], true ) ||
									( $extension->slug === 'woocommerce' && in_array( $extension->version, [ 'rc', 'nightly' ], true ) );

			if ( $is_special_version ) {
				// RC and nightly versions change frequently, so short cache
				if ( $cache_age > 5 * MINUTE_IN_SECONDS ) {
					return false;
				}
			} elseif ( $cache_age > DAY_IN_SECONDS ) {
				// Regular versioned extensions can be cached longer
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
			throw new \InvalidArgumentException( 'Cache dir must be inside QIT directory' );
		}

		// Create cache key components
		$type        = $extension->type;
		$slug        = $extension->slug;
		$version     = $extension->version;
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
