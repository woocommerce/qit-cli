<?php

namespace QIT_CLI\PreCommand\Download;

use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use QIT_CLI\Cache;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use QIT_CLI\Validation\ArtifactValidator;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

/**
 * Downloads and caches remote test packages from the QIT repository.
 *
 * ## Caching Strategy
 *
 * This class implements a checksum-based caching system to handle both immutable and rolling versions:
 *
 * ### 1. Checksum-Based Validation
 * - ALWAYS fetches package metadata (including SHA256 checksum) from the API first
 * - Uses checksum to validate if cached package is still current
 * - This ensures rolling versions (latest, rc, nightly) stay up-to-date
 * - Immutable versions (1.0.0) benefit from checksum validation too
 *
 * ### 2. Cache Key Generation
 * - Format: `test_package_checksum_[checksum]`
 * - Uses SHA256 checksum as the cache key component
 * - Different checksums = different cache entries
 * - Rolling versions get new cache entries when updated
 *
 * ### 3. Cache Validation Flow
 * - Fetch metadata (lightweight API call with checksum)
 * - Check if we have this exact checksum cached
 * - If cached and valid, use it (no download needed)
 * - If not cached or checksum changed, download new version
 *
 * ### 4. Benefits
 * - Rolling versions (latest, rc) always get fresh content when updated
 * - Immutable versions are cached indefinitely (checksum never changes)
 * - Prevents using stale packages for version channels
 * - Still prevents unnecessary downloads when content hasn't changed
 *
 * ### 5. Subpackage Handling
 * - Subpackages share the same artifact as their parent
 * - When a subpackage is requested, downloads parent and extracts subpackage manifest
 * - Cache key is based on parent package checksum
 *
 * @see download() for the main checksum-based caching logic
 * @see validate_and_get_cached_package() for cache validation
 */
class TestPackageDownloader {
	protected Cache $cache;

	protected Zipper $zipper;

	protected OutputInterface $output;

	protected TestPackageManifestParser $manifest_parser;

	protected ArtifactValidator $artifact_validator;

	/** @var array<string,array<string,mixed>> */
	protected array $package_metadata = [];

	/** @var array<string,TestPackageManifest> Parent manifests keyed by checksum */
	protected array $parent_manifests = [];

	public function __construct(
		Cache $cache,
		Zipper $zipper,
		OutputInterface $output,
		TestPackageManifestParser $manifest_parser,
		ArtifactValidator $artifact_validator
	) {
		$this->cache              = $cache;
		$this->zipper             = $zipper;
		$this->output             = $output;
		$this->manifest_parser    = $manifest_parser;
		$this->artifact_validator = $artifact_validator;
	}

	/**
	 * Download multiple test packages with checksum-based caching.
	 *
	 * This method implements checksum validation to handle rolling versions:
	 * 1. Separates local packages (no caching needed) from remote packages
	 * 2. For ALL remote packages, fetches metadata including checksums (lightweight API call)
	 * 3. Uses checksums to validate cache - only downloads if checksum changed
	 * 4. Ensures rolling versions like 'latest' always get fresh content when updated
	 *
	 * This approach handles both immutable versions and rolling version channels correctly.
	 *
	 * @param array<string, array<string,mixed>> $packages Map of reference => package info.
	 * @param string                             $cache_dir Cache directory (e.g., /tmp/qit-cache).
	 *
	 * @return array<string,TestPackageManifest> Map of reference => manifest objects
	 */
	public function download( array $packages, string $cache_dir ): array {
		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( '[DEBUG] Starting download with packages: ' . json_encode( $packages ) );
		}

		if ( empty( $packages ) ) {
			return [];
		}

		$start           = microtime( true );
		$manifests       = [];
		$remote_packages = [];

		// First, separate local paths from remote references
		foreach ( $packages as $reference => $package_info ) {
			// Check if reference is a local path
			if ( is_dir( $reference ) && file_exists( $reference . '/qit-test.json' ) ) {
				// Handle local package
				$this->output->writeln( "Using local package: $reference" );
				$manifest                = $this->manifest_parser->parse( $reference . '/qit-test.json' );
				$manifests[ $reference ] = $manifest;

				// Store metadata for local package
				$this->package_metadata[ $reference ] = [
					'reference'       => $reference,
					'remote'          => false,
					'downloaded_path' => $reference,
					'version'         => 'local',
				];

				// For local packages, ensure npm dependencies and Playwright browsers are installed
				if ( file_exists( $reference . '/package.json' ) ) {
					// Check if node_modules exists, if not run npm install
					if ( ! is_dir( $reference . '/node_modules' ) ) {
						$this->install_npm_dependencies( $reference );
					}

					// Ensure Playwright browsers are installed if Playwright is present
					$this->install_playwright_browsers_if_needed( $reference );
				}
			} else {
				// Remote package - will fetch metadata to validate cache
				$remote_packages[ $reference ] = $package_info;
			}
		}

		// Process remote packages with checksum validation
		if ( ! empty( $remote_packages ) ) {
			// Always fetch metadata for ALL remote packages (lightweight API call)
			// This gives us checksums to validate cache
			$this->output->writeln( 'Fetching package metadata...' );
			$response = $this->fetch_download_urls( array_keys( $remote_packages ) );

			// Extract URLs and artifact groups from response
			$package_metadata = $response['urls'];
			$artifact_groups  = $response['artifact_groups'] ?? [];

			// Debug: Log what we received
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( 'Received ' . count( $package_metadata ) . ' package metadata entries' );
				if ( ! empty( $artifact_groups ) ) {
					$this->output->writeln( 'Server provided ' . count( $artifact_groups ) . ' artifact groups' );
				}
			}

			// If artifact groups are provided, use them to optimize downloads
			// Otherwise, create our own groups based on checksums
			if ( empty( $artifact_groups ) ) {
				// Group packages by checksum ourselves
				$artifact_groups = [];
				foreach ( $package_metadata as $reference => $metadata ) {
					if ( isset( $metadata['checksum'] ) ) {
						$artifact_groups[ $metadata['checksum'] ][] = $reference;
					} else {
						// No checksum means it's its own group
						$artifact_groups[ 'no_checksum_' . $reference ][] = $reference;
					}
				}
			}

			// Process by artifact group (packages that share the same ZIP)
			foreach ( $artifact_groups as $artifact_key => $package_ids ) {
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( '[DEBUG] Processing artifact group: ' . json_encode( $package_ids ) );
				}

				// Find packages in this group that we need to process
				$group_packages = [];
				foreach ( $package_ids as $package_id ) {
					if ( isset( $remote_packages[ $package_id ] ) && isset( $package_metadata[ $package_id ] ) ) {
						$group_packages[ $package_id ] = $package_metadata[ $package_id ];
					}
				}

				if ( empty( $group_packages ) ) {
					continue;
				}

				// Pick the first package as the primary one to download
				$first_reference = array_key_first( $group_packages );
				$first_metadata  = $group_packages[ $first_reference ];

				// Check if the primary package is already cached
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( "[DEBUG] Checking cache for primary package: $first_reference" );
				}
				$cached_manifest  = $this->validate_and_get_cached_package( $first_reference, $first_metadata, $cache_dir );
				$primary_manifest = null;

				if ( $cached_manifest !== null ) {
					$manifests[ $first_reference ] = $cached_manifest;
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( "Using cached package: $first_reference (checksum validated)" );
					}
					$artifact_downloaded = true;

					// For subpackage extraction, we need the parent manifest
					// If the cached manifest is a subpackage, we need to get its parent
					if ( $cached_manifest->is_subpackage() && count( $group_packages ) > 1 ) {
						// We have a cached subpackage but need the parent for extraction
						// The artifact is already downloaded (cached), so we need to load the parent manifest
						$package_path = $this->package_metadata[ $first_reference ]['downloaded_path'] ?? null;
						if ( $package_path && file_exists( $package_path . '/qit-test.json' ) ) {
							// Load the parent manifest directly from disk
							$parent_data = json_decode( file_get_contents( $package_path . '/qit-test.json' ), true );
							if ( $parent_data ) {
								try {
									$primary_manifest = new TestPackageManifest( $parent_data );
								} catch ( \Exception $e ) {
									// Fall back to using the subpackage manifest
									$primary_manifest = $cached_manifest;
								}
							}
						}
					}

					// If we don't have a primary manifest yet, use the cached one
					if ( $primary_manifest === null ) {
						$primary_manifest = $cached_manifest;
					}
				} else {
					// Download the artifact using the primary package
					if ( count( $group_packages ) > 1 ) {
						$this->output->writeln( 'Downloading shared artifact for ' . count( $group_packages ) . ' packages: ' . implode( ', ', array_keys( $group_packages ) ) );
					} else {
						$this->output->writeln( "Downloading package: $first_reference" );
					}

					$downloaded_manifest           = $this->download_package( $first_reference, $first_metadata, $cache_dir );
					$manifests[ $first_reference ] = $downloaded_manifest;
					$artifact_downloaded           = true;

					// Check if we have the parent manifest stored (for subpackage extraction)
					$checksum = $first_metadata['checksum'] ?? null;
					if ( $checksum && isset( $this->parent_manifests[ $checksum ] ) ) {
						$primary_manifest = $this->parent_manifests[ $checksum ];
						if ( $this->output->isVeryVerbose() ) {
							$this->output->writeln( '[DEBUG] Using stored parent manifest for extraction' );
						}
					} else {
						$primary_manifest = $downloaded_manifest;
					}

					if ( $this->output->isVeryVerbose() ) {
						$this->output->writeln( '[DEBUG] Primary manifest package ID: ' . $primary_manifest->get_package_id() );
						$this->output->writeln( '[DEBUG] Primary manifest has subpackages: ' . ( $primary_manifest->has_subpackages() ? 'YES' : 'NO' ) );
						if ( $primary_manifest->has_subpackages() ) {
							$this->output->writeln( '[DEBUG] Subpackages: ' . implode( ', ', array_keys( $primary_manifest->get_subpackages() ) ) );
						}
					}
				}

				// Process other packages in the group
				foreach ( $group_packages as $reference => $metadata ) {
					if ( $reference === $first_reference ) {
						continue; // Already processed
					}

					// Try to extract subpackage manifest from the primary manifest
					$requested_package_id = $this->extract_package_id( $reference );
					$subpackage_manifest  = $this->extract_subpackage_manifest( $primary_manifest, $requested_package_id );

					if ( $subpackage_manifest !== null ) {
						// Set metadata for the subpackage to use the parent's path
						// The subpackage shares the same physical artifact as the parent
						$this->package_metadata[ $reference ] = $this->package_metadata[ $first_reference ] ?? [];
						if ( $this->output->isVeryVerbose() ) {
							$this->output->writeln( "Extracted subpackage $reference from $first_reference" );
							$this->output->writeln( '  Metadata path: ' . ( $this->package_metadata[ $reference ]['downloaded_path'] ?? 'NOT SET' ) );
						}
						$manifests[ $reference ] = $subpackage_manifest;
					} else {
						// This package doesn't exist as a subpackage in the primary manifest
						// It must be downloaded separately
						$this->output->writeln( "Downloading package: $reference (not found as subpackage)" );
						$manifests[ $reference ] = $this->download_package( $reference, $metadata, $cache_dir );
					}
				}
			}
		}

		$elapsed = round( microtime( true ) - $start, 2 );
		$this->output->writeln( 'Downloaded ' . count( $manifests ) . " packages in {$elapsed}s" );

		return $manifests;
	}

	/**
	 * Validate cache using checksum and return cached package if valid.
	 *
	 * This method validates cached packages using SHA256 checksums:
	 * - Uses checksum from API metadata to generate cache key
	 * - Checks if we have this exact checksum cached
	 * - Validates that the cached package directory still exists
	 * - Handles subpackage extraction from parent manifests
	 *
	 * This ensures:
	 * - Rolling versions (latest, rc) get fresh content when updated
	 * - Immutable versions use cache indefinitely (checksum never changes)
	 * - Cache invalidation is automatic when content changes
	 *
	 * @param string              $reference The package reference (e.g., "woocommerce/e2e:latest").
	 * @param array<string,mixed> $metadata Package metadata from API including checksum.
	 * @param string              $cache_dir The cache directory path.
	 * @return TestPackageManifest|null The cached manifest or null if not cached/checksum changed.
	 */
	protected function validate_and_get_cached_package( string $reference, array $metadata, string $cache_dir ): ?TestPackageManifest { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( "[DEBUG] validate_and_get_cached_package called for: $reference" );
		}

		// Use checksum as the cache key component for reliable cache invalidation
		if ( ! isset( $metadata['checksum'] ) || empty( $metadata['checksum'] ) ) {
			// No checksum available, can't use cache
			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( "[DEBUG] No checksum available for $reference, skipping cache" );
			}
			return null;
		}

		// Generate cache key based on checksum (not version)
		// This ensures cache is invalidated when content changes
		// Using only checksum allows subpackages to share the same cache entry
		$cache_key = 'test_package_checksum_' . $metadata['checksum'];
		$cached    = $this->cache->get( $cache_key );

		if ( $cached && is_array( $cached ) && isset( $cached['manifest'] ) ) {
			// Verify the cached package still exists on disk
			if ( isset( $cached['metadata']['downloaded_path'] ) && is_dir( $cached['metadata']['downloaded_path'] ) ) {
				// Validate cached test package artifact integrity
				try {
					$this->artifact_validator->validate_test_package( $cached['metadata']['downloaded_path'], $reference );
				} catch ( \Exception $e ) {
					// Cached artifact is invalid, remove from cache
					$this->cache->delete( $cache_key );
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( 'Cached test package artifact failed validation, cache deleted: ' . $e->getMessage() );
					}
					return null; // Will trigger re-download
				}

				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( "[DEBUG] Found cached package data for $reference" );
					$this->output->writeln( '[DEBUG] Creating TestPackageManifest from cached data' );
					$this->output->writeln( "[DEBUG] Cached manifest has '_normalized' flag: " . ( isset( $cached['manifest']['_normalized'] ) ? 'YES' : 'NO' ) );
					$this->output->writeln( '[DEBUG] Cached manifest keys: ' . implode( ', ', array_keys( $cached['manifest'] ) ) );
				}

				// Restore metadata for caller access
				$this->package_metadata[ $reference ] = $cached['metadata'];

				// Check if we need to handle subpackage extraction
				try {
					$manifest_object = new TestPackageManifest( $cached['manifest'] );
				} catch ( \Exception $e ) {
					// Invalid cached manifest - delete it and throw error
					$this->cache->delete( $cache_key );
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( "Invalid cached manifest for $reference, cache deleted: " . $e->getMessage() );
					}
					// This shouldn't happen with checksum-based caching unless the manifest format changed
					// Re-throw the exception so the issue is visible
					throw new \RuntimeException( "Cached manifest for $reference is invalid: " . $e->getMessage(), 0, $e );
				}
				$requested_package_id = $this->extract_package_id( $reference );
				$manifest_package_id  = $manifest_object->get_package_id();

				// If the cached manifest is for a different package, check if it's a parent with the requested subpackage
				if ( $requested_package_id !== $manifest_package_id ) {
					if ( $this->output->isVeryVerbose() ) {
						$this->output->writeln( "Cache hit for different package. Requested: $requested_package_id, Cached: $manifest_package_id" );
					}
					// Try to extract the subpackage configuration
					$subpackage_manifest = $this->extract_subpackage_manifest( $manifest_object, $requested_package_id );
					if ( $subpackage_manifest ) {
						// Update metadata for the subpackage to point to the parent's path
						// The subpackage uses the same physical files as the parent
						$this->package_metadata[ $reference ] = $cached['metadata'];
						return $subpackage_manifest;
					}
					// Cache miss - need to download the correct package
					return null;
				}

				return $manifest_object;
			}
		}

		return null;
	}

	/**
	 * Get package metadata for a reference.
	 *
	 * @param string $reference The package reference.
	 * @return array<string,mixed> The package metadata.
	 */
	public function get_package_metadata( string $reference ): array {
		return $this->package_metadata[ $reference ] ?? [];
	}

	/**
	 * Download a single test package with checksum validation
	 *
	 * @return TestPackageManifest
	 */
	public function download_single( string $reference, string $cache_dir ): TestPackageManifest {
		// Always fetch metadata first to get checksum
		$metadata_array = $this->fetch_download_urls( [ $reference ] );

		if ( ! isset( $metadata_array[ $reference ] ) ) {
			throw new \RuntimeException( "No metadata found for package '$reference'" );
		}

		$metadata = $metadata_array[ $reference ];

		// Check if we have this exact checksum cached
		$cached_manifest = $this->validate_and_get_cached_package( $reference, $metadata, $cache_dir );
		if ( $cached_manifest !== null ) {
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( "Using cached package: $reference (checksum validated)" );
			}
			return $cached_manifest;
		}

		// Not cached or checksum changed - download
		return $this->download_package( $reference, $metadata, $cache_dir );
	}

	/**
	 * Fetch download URLs from QIT Manager
	 *
	 * @param string[] $references
	 * @return array{urls: array<string,array<string,mixed>>, artifact_groups?: array<string,array<string>>}
	 */
	protected function fetch_download_urls( array $references ): array {
		// Check cache first to prevent API rate limiting
		// Cache key includes all references to handle bulk requests
		$cache_key = 'test_package_urls_' . md5( implode( '|', $references ) );
		$cached    = $this->cache->get( $cache_key );

		if ( $cached && is_array( $cached ) ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( 'Using cached package metadata (expires in ' . ( 30 - ( time() - ( $cached['cached_at'] ?? time() ) ) ) . 's)' );
			}
			return $cached['data'] ?? $cached; // Support both old and new cache format
		}

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'package_ids' => array_values( $references ),
			] )
			->request();

		$data = json_decode( $response, true );

		// Check for rate limiting or other errors
		if ( isset( $data['error'] ) ) {
			// This is an error response from the API
			throw new \RuntimeException( $data['error'] );
		}

		if ( ! is_array( $data ) || ! isset( $data['urls'] ) ) {
			// Add more context to the error for debugging
			$debug_info = is_array( $data ) ? json_encode( $data ) : $response;
			throw new \RuntimeException( 'Invalid response from package download API. Response: ' . substr( $debug_info, 0, 500 ) );
		}

		// Build the response structure
		$result = [
			'urls' => $data['urls'],
		];

		// Include artifact groups if provided by the server
		if ( isset( $data['artifact_groups'] ) ) {
			$result['artifact_groups'] = $data['artifact_groups'];
		}

		// Cache the response for 30 seconds to prevent API burst
		// This is especially important for repeated test runs
		$this->cache->set( $cache_key, [
			'data'      => $result,
			'cached_at' => time(),
		], 30 );

		return $result;
	}

	/**
	 * Download and extract a test package (already validated that cache doesn't have it)
	 *
	 * @param string              $reference Package reference.
	 * @param array<string,mixed> $metadata Package metadata including URL and checksum.
	 * @param string              $cache_dir Cache directory.
	 * @return TestPackageManifest
	 */
	protected function download_package( string $reference, array $metadata, string $cache_dir ): TestPackageManifest {
		// Generate cache key using checksum for reliable invalidation
		if ( ! isset( $metadata['checksum'] ) ) {
			throw new \RuntimeException( "No checksum available for package '$reference'" );
		}

		// Using only checksum allows subpackages to share the same cache entry
		$cache_key = 'test_package_checksum_' . $metadata['checksum'];

		// Double-check cache (shouldn't hit this since we already validated)
		$cached = $this->cache->get( $cache_key );
		if ( $cached && is_array( $cached ) && isset( $cached['manifest'] ) ) {
			// Verify the cached package still exists on disk
			if ( isset( $cached['metadata']['downloaded_path'] ) && is_dir( $cached['metadata']['downloaded_path'] ) ) {
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( "Using cached test package '$reference' (found on second check)" );
				}

				// Restore metadata for caller access
				$this->package_metadata[ $reference ] = $cached['metadata'];

				try {
					return new TestPackageManifest( $cached['manifest'] );
				} catch ( \Exception $e ) {
					// Invalid cached manifest - delete it
					$this->cache->delete( $cache_key );
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( "Invalid cached manifest for $reference (second check), cache deleted: " . $e->getMessage() );
						$this->output->writeln( 'Continuing with fresh download...' );
					}
					// Continue with download rather than throwing, since we're already in the download method
				}
			}
		}

		// Download the package
		$package_dir = $cache_dir . '/packages/' . md5( $reference );
		$zip_file    = $package_dir . '.zip';

		if ( ! file_exists( dirname( $zip_file ) ) ) {
			mkdir( dirname( $zip_file ), 0755, true );
		}

		if ( ! isset( $metadata['url'] ) ) {
			throw new \RuntimeException( "No download URL for package '$reference'" );
		}

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( "Downloading test package '$reference' from {$metadata['url']}" );
		}

		RequestBuilder::download_file( $metadata['url'], $zip_file );

		// Validate and extract
		$this->zipper->validate_zip( $zip_file );

		if ( is_dir( $package_dir ) ) {
			// Clean existing directory
			$this->recursive_rmdir( $package_dir );
		}

		$this->zipper->extract_zip( $zip_file, $package_dir );

		// Validate test package artifact integrity
		try {
			$this->artifact_validator->validate_test_package( $package_dir, $reference );
		} catch ( \Exception $e ) {
			// Invalid artifact, clean up
			$this->recursive_rmdir( $package_dir );
			unlink( $zip_file );
			throw new \RuntimeException( 'Downloaded test package artifact failed validation: ' . $e->getMessage() );
		}

		// Install dependencies if package.json exists
		if ( file_exists( $package_dir . '/package.json' ) ) {
			$this->install_npm_dependencies( $package_dir );
		}

		// Find and parse manifest
		$manifest_file = $this->find_manifest( $package_dir );
		if ( ! $manifest_file ) {
			throw new \RuntimeException( "No qit-test.json found in package '$reference'" );
		}

		$manifest_object = $this->manifest_parser->parse( $manifest_file );

		// Extract version from metadata
		$version = null;
		if ( isset( $metadata['version'] ) ) {
			$version = $metadata['version'];
		} elseif ( preg_match( '/:([^:]+)$/', $reference, $matches ) ) {
			// Extract version from reference format namespace/package:version
			$version = $matches[1];
		}

		if ( ! $version ) {
			throw new \RuntimeException( "Cannot determine version for remote package '{$reference}'" );
		}

		// Check if we requested a subpackage but got the parent manifest
		$requested_package_id  = $this->extract_package_id( $reference );
		$manifest_package_id   = $manifest_object->get_package_id();
		$is_subpackage_request = false;

		// If the downloaded manifest is for a different package, check if it's a parent with the requested subpackage
		if ( $requested_package_id !== $manifest_package_id ) {
			$is_subpackage_request = true;
			// We downloaded a parent package for a subpackage request
			// Cache the parent under its own reference for future subpackage requests
			$parent_reference = $manifest_package_id . ':' . $version;
			// Using only checksum allows subpackages to share the same cache entry
			$parent_cache_key = 'test_package_checksum_' . $metadata['checksum'];

			// Cache the parent manifest
			$parent_metadata = [
				'reference'       => $parent_reference,
				'remote'          => true,
				'downloaded_path' => $package_dir,
				'version'         => $version,
			];

			$this->cache->set( $parent_cache_key, [
				'manifest' => $manifest_object->to_array(),
				'metadata' => $parent_metadata,
			], DAY_IN_SECONDS );

			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( "[DEBUG] Cached parent package '$parent_reference' with key: $parent_cache_key" );
			}

			// Try to extract the subpackage configuration
			$subpackage_manifest = $this->extract_subpackage_manifest( $manifest_object, $requested_package_id );
			if ( $subpackage_manifest ) {
				// Store the parent manifest for later use
				$this->parent_manifests[ $metadata['checksum'] ] = $manifest_object;
				// Use the subpackage manifest instead
				$manifest_object = $subpackage_manifest;
			} else {
				// This shouldn't happen if the Manager API is working correctly
				throw new \RuntimeException( "Downloaded package '{$manifest_package_id}' does not match requested '{$requested_package_id}' and is not a parent with that subpackage" );
			}
		}

		// Prepare metadata separately
		$metadata                             = [
			'reference'       => $reference,
			'remote'          => true,
			'downloaded_path' => $package_dir,
			'version'         => $version,
		];
		$this->package_metadata[ $reference ] = $metadata;

		$manifest_array = $manifest_object->to_array();

		// Only cache if we haven't already cached the parent manifest
		// (subpackages share the parent's cache entry)
		if ( ! $is_subpackage_request ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( "[DEBUG] Caching package with key: $cache_key" );
				$this->output->writeln( '[DEBUG] Manifest array has _normalized: ' . ( isset( $manifest_array['_normalized'] ) ? 'YES' : 'NO' ) );
			}

			// Cache both manifest and metadata together
			$this->cache->set( $cache_key, [
				'manifest' => $manifest_array,
				'metadata' => $metadata,
			], DAY_IN_SECONDS );
		}

		// Clean up zip file
		unlink( $zip_file );

		// Return the TestPackageManifest object
		return $manifest_object;
	}

	/**
	 * Find qit-test.json in extracted package
	 */
	protected function find_manifest( string $dir ): ?string {
		// Check root directory
		if ( file_exists( $dir . '/qit-test.json' ) ) {
			return $dir . '/qit-test.json';
		}

		// Check one level deep (common with GitHub archives)
		$entries = scandir( $dir );
		foreach ( $entries as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$path = $dir . '/' . $entry;
			if ( is_dir( $path ) && file_exists( $path . '/qit-test.json' ) ) {
				return $path . '/qit-test.json';
			}
		}

		return null;
	}

	/**
	 * Install npm dependencies in the package directory.
	 *
	 * @param string $package_dir The directory containing package.json.
	 * @throws \RuntimeException If npm install fails.
	 */
	protected function install_npm_dependencies( string $package_dir ): void {
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( "Installing npm dependencies in $package_dir" );
		}

		// Use npm ci if package-lock.json exists, otherwise use npm install
		$use_ci      = file_exists( $package_dir . '/package-lock.json' );
		$npm_command = 'cd ' . escapeshellarg( $package_dir ) . ' && npm ' . ( $use_ci ? 'ci' : 'install' );

		$npm_output      = [];
		$npm_return_code = 0;

		exec( $npm_command . ' 2>&1', $npm_output, $npm_return_code );

		if ( $npm_return_code !== 0 ) {
			$command_used = $use_ci ? 'npm ci' : 'npm install';
			throw new \RuntimeException( $command_used . ' failed: ' . implode( "\n", $npm_output ) );
		}

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( 'npm dependencies installed successfully' );
		}

		// Ensure Playwright browsers are installed if Playwright is present
		$this->install_playwright_browsers_if_needed( $package_dir );
	}

	/**
	 * Install Playwright browsers if Playwright is detected in the package.
	 *
	 * @param string $package_dir The directory containing the package.
	 */
	protected function install_playwright_browsers_if_needed( string $package_dir ): void {
		// Check if Playwright is installed
		if ( ! file_exists( $package_dir . '/node_modules/@playwright/test' ) &&
			! file_exists( $package_dir . '/node_modules/playwright' ) &&
			! file_exists( $package_dir . '/node_modules/playwright-core' ) ) {
			return;
		}

		// First check with --dry-run to see if browsers are already installed
		$dry_run_command = 'cd ' . escapeshellarg( $package_dir ) . ' && npx playwright install --dry-run 2>&1';
		$dry_run_output  = [];
		exec( $dry_run_command, $dry_run_output );

		// Check if all browser paths exist
		$browsers_needed = false;
		foreach ( $dry_run_output as $line ) {
			if ( strpos( $line, 'Install location:' ) !== false ) {
				// Extract path from line like "  Install location:    /home/user/.cache/ms-playwright/chromium-1187"
				// First remove "Install location:" then trim whitespace
				$parts = explode( 'Install location:', $line );
				if ( count( $parts ) > 1 ) {
					$path = trim( $parts[1] );
					if ( ! empty( $path ) && ! is_dir( $path ) ) {
						$browsers_needed = true;
						if ( $this->output->isVeryVerbose() ) {
							$this->output->writeln( "Browser directory missing: $path" );
						}
						break;
					}
				}
			}
		}

		if ( ! $browsers_needed ) {
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( 'Playwright browsers already installed, skipping...' );
			}
			return;
		}

		// Browsers need to be installed
		$this->output->writeln( 'Installing Playwright browsers...' );

		$playwright_command     = 'cd ' . escapeshellarg( $package_dir ) . ' && npx playwright install';
		$playwright_output      = [];
		$playwright_return_code = 0;

		exec( $playwright_command . ' 2>&1', $playwright_output, $playwright_return_code );

		if ( $playwright_return_code !== 0 ) {
			// Don't throw an exception, just warn - some packages may not need browsers
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( '<warning>Warning: playwright install failed (this may be expected for some packages):</warning>' );
				$this->output->writeln( implode( "\n", $playwright_output ) );
			}
		} else {
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( 'Playwright browsers installed successfully' );
			}
		}
	}

	/**
	 * Legacy method for backward compatibility.
	 *
	 * @deprecated Use validate_and_get_cached_package() instead
	 *
	 * @param string $reference The package reference.
	 * @param string $cache_dir The cache directory path.
	 * @return TestPackageManifest|null The cached manifest or null if not cached.
	 */
	protected function get_cached_package( string $reference, string $cache_dir ): ?TestPackageManifest {
		// For backward compatibility, we need to fetch metadata first
		// This is not ideal but maintains compatibility
		try {
			$metadata_array = $this->fetch_download_urls( [ $reference ] );
			if ( isset( $metadata_array[ $reference ] ) ) {
				return $this->validate_and_get_cached_package( $reference, $metadata_array[ $reference ], $cache_dir );
			}
		} catch ( \Exception $e ) {
			// If metadata fetch fails, can't use cache
			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( 'Could not fetch metadata for cache validation: ' . $e->getMessage() );
			}
		}
		return null;
	}

	/**
	 * Extract package ID from a reference (removes version suffix).
	 *
	 * @param string $reference Package reference like "namespace/package:version".
	 * @return string Package ID like "namespace/package".
	 */
	protected function extract_package_id( string $reference ): string {
		if ( strpos( $reference, ':' ) !== false ) {
			[ $package_id, ] = explode( ':', $reference, 2 );
			return $package_id;
		}
		return $reference;
	}

	/**
	 * Extract subpackage manifest from a parent manifest.
	 * Respects inheritance rules as documented:
	 * - Inherits globalSetup/globalTeardown from parent (cannot override)
	 * - Inherits test.results from parent
	 * - Inherits requires, mu_plugins, envs, timeout, retry from parent
	 * - Can override: description, tags, test.phases.setup/run/teardown
	 *
	 * @param TestPackageManifest $parent_manifest The parent package manifest.
	 * @param string              $subpackage_id The subpackage ID to extract.
	 * @return TestPackageManifest|null The subpackage manifest or null if not found.
	 */
	protected function extract_subpackage_manifest( TestPackageManifest $parent_manifest, string $subpackage_id ): ?TestPackageManifest {
		// Check if parent has this subpackage
		$subpackage_config = $parent_manifest->get_subpackage( $subpackage_id );
		if ( ! $subpackage_config ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( "Subpackage $subpackage_id not found in parent manifest" );
			}
			return null;
		}

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( "Found subpackage $subpackage_id in parent manifest" );
		}

		// Start with parent's configuration as base (full inheritance)
		$parent_phases   = $parent_manifest->get_phases();
		$subpackage_data = [
			'package'        => $subpackage_id,
			'parent_package' => $parent_manifest->get_package_id(),
			'test_type'      => $parent_manifest->get_test_type(),
			'test_dir'       => $parent_manifest->get_test_dir(),
			'test'           => [
				'phases'  => [
					// Global phases can be overridden or inherited
					'globalSetup'    => $parent_phases['globalSetup'] ?? [],
					'globalTeardown' => $parent_phases['globalTeardown'] ?? [],
				],
				// Results paths inherited from parent
				'results' => $parent_manifest->get_test_results(),
			],
			// Inherit other configurations from parent
			'requires'       => $parent_manifest->get_requires(),
			'mu_plugins'     => $parent_manifest->get_mu_plugins(),
			'envs'           => $parent_manifest->get_env(),
			'timeout'        => $parent_manifest->get_timeout(),
			'retry'          => $parent_manifest->get_retry(),
		];

		// Apply subpackage-specific overrides (only for allowed fields)
		if ( isset( $subpackage_config['description'] ) ) {
			$subpackage_data['description'] = $subpackage_config['description'];
		}
		if ( isset( $subpackage_config['tags'] ) ) {
			$subpackage_data['tags'] = $subpackage_config['tags'];
		}

		// Override phases - all phases can now be overridden
		if ( isset( $subpackage_config['test']['phases'] ) ) {
			$subpackage_phases = $subpackage_config['test']['phases'];

			// Allow overriding all phases including global ones
			if ( isset( $subpackage_phases['globalSetup'] ) ) {
				$subpackage_data['test']['phases']['globalSetup'] = $subpackage_phases['globalSetup'];
			}
			if ( isset( $subpackage_phases['globalTeardown'] ) ) {
				$subpackage_data['test']['phases']['globalTeardown'] = $subpackage_phases['globalTeardown'];
			}
			if ( isset( $subpackage_phases['setup'] ) ) {
				$subpackage_data['test']['phases']['setup'] = $subpackage_phases['setup'];
			}
			if ( isset( $subpackage_phases['run'] ) ) {
				$subpackage_data['test']['phases']['run'] = $subpackage_phases['run'];
			}
			if ( isset( $subpackage_phases['teardown'] ) ) {
				$subpackage_data['test']['phases']['teardown'] = $subpackage_phases['teardown'];
			}
		}

		// Debug the package field before creating manifest
		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Creating subpackage manifest with package ID: ' . $subpackage_data['package'] );
		}

		// Create and return the subpackage manifest
		return new TestPackageManifest( $subpackage_data );
	}

	/**
	 * Recursively remove directory
	 */
	protected function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$objects = scandir( $dir );
		foreach ( $objects as $object ) {
			if ( $object === '.' || $object === '..' ) {
				continue;
			}

			$path = $dir . '/' . $object;
			if ( is_dir( $path ) ) {
				$this->recursive_rmdir( $path );
			} else {
				unlink( $path );
			}
		}

		rmdir( $dir );
	}

	/**
	 * Get package information from API
	 *
	 * @return array<string,mixed>
	 */
	public function get_package_info( string $reference ): array {
		$cache_key = 'package_info_' . md5( $reference );
		$cached    = $this->cache->get( $cache_key );

		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/package-info' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'package' => $reference,
			] )
			->request();

		$info = json_decode( $response, true );

		if ( ! is_array( $info ) ) {
			throw new \RuntimeException( "Invalid package info response for '$reference'" );
		}

		// Cache for 30 seconds to prevent API burst but still get fresh data
		$this->cache->set( $cache_key, $info, 30 );

		return $info;
	}

	/**
	 * Search for packages
	 *
	 * @param string              $query
	 * @param array<string,mixed> $filters
	 * @return array<string,mixed>
	 */
	public function search( string $query, array $filters = [] ): array {
		$params = [
			'q'      => $query,
			'limit'  => $filters['limit'] ?? 20,
			'offset' => $filters['offset'] ?? 0,
		];

		if ( isset( $filters['tags'] ) ) {
			$params['tags'] = implode( ',', $filters['tags'] );
		}

		if ( isset( $filters['test_type'] ) ) {
			$params['test_type'] = $filters['test_type'];
		}

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/package-search' ) )
			->with_method( 'POST' )
			->with_post_body( $params )
			->request();

		$results = json_decode( $response, true );

		if ( ! is_array( $results ) ) {
			throw new \RuntimeException( 'Invalid search response' );
		}

		return $results;
	}

	/**
	 * Expose package metadata collected during downloads.
	 *
	 * @return array<string,mixed>
	 */
	public function get_metadata( string $reference ): array {
		return $this->package_metadata[ $reference ] ?? [];
	}
}
