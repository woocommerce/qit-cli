<?php

namespace QIT_CLI\PreCommand\Download;

use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use QIT_CLI\Cache;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
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
 * - Format: `test_package_[md5(reference_checksum)]`
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

	/** @var array<string,array<string,mixed>> */
	protected array $package_metadata = [];

	public function __construct(
		Cache $cache,
		Zipper $zipper,
		OutputInterface $output,
		TestPackageManifestParser $manifest_parser
	) {
		$this->cache           = $cache;
		$this->zipper          = $zipper;
		$this->output          = $output;
		$this->manifest_parser = $manifest_parser;
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
			$package_metadata = $this->fetch_download_urls( array_keys( $remote_packages ) );

			// Process each package with checksum validation
			foreach ( $remote_packages as $reference => $package_info ) {
				if ( ! isset( $package_metadata[ $reference ] ) ) {
					throw new \RuntimeException( "No metadata found for package '$reference'" );
				}

				$metadata = $package_metadata[ $reference ];
				
				// Check if we have this exact checksum cached
				$cached_manifest = $this->validate_and_get_cached_package( $reference, $metadata, $cache_dir );
				
				if ( $cached_manifest !== null ) {
					// Cache is valid for this checksum
					$manifests[ $reference ] = $cached_manifest;
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( "Using cached package: $reference (checksum validated)" );
					}
				} else {
					// Need to download - either not cached or checksum changed
					$this->output->writeln( "Downloading package: $reference" );
					$manifests[ $reference ] = $this->download_package( $reference, $metadata, $cache_dir );
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
	 * @param string $reference The package reference (e.g., "woocommerce/e2e:latest").
	 * @param array<string,mixed> $metadata Package metadata from API including checksum.
	 * @param string $cache_dir The cache directory path.
	 * @return TestPackageManifest|null The cached manifest or null if not cached/checksum changed.
	 */
	protected function validate_and_get_cached_package( string $reference, array $metadata, string $cache_dir ): ?TestPackageManifest {
		// Use checksum as the cache key component for reliable cache invalidation
		if ( ! isset( $metadata['checksum'] ) || empty( $metadata['checksum'] ) ) {
			// No checksum available, can't use cache
			return null;
		}
		
		// Generate cache key based on checksum (not version)
		// This ensures cache is invalidated when content changes
		$cache_key = 'test_package_' . md5( $reference . '_' . $metadata['checksum'] );
		$cached    = $this->cache->get( $cache_key );

		if ( $cached && is_array( $cached ) && isset( $cached['manifest'] ) ) {
			// Verify the cached package still exists on disk
			if ( isset( $cached['metadata']['downloaded_path'] ) && is_dir( $cached['metadata']['downloaded_path'] ) ) {
				// Restore metadata for caller access
				$this->package_metadata[ $reference ] = $cached['metadata'];
				
				// Check if we need to handle subpackage extraction
				$manifest_object = new TestPackageManifest( $cached['manifest'] );
				$requested_package_id = $this->extract_package_id( $reference );
				$manifest_package_id = $manifest_object->getPackageId();
				
				// If the cached manifest is for a different package, check if it's a parent with the requested subpackage
				if ( $requested_package_id !== $manifest_package_id ) {
					// Try to extract the subpackage configuration
					$subpackage_manifest = $this->extract_subpackage_manifest( $manifest_object, $requested_package_id );
					if ( $subpackage_manifest ) {
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
	 * @return array<string,array<string,mixed>>
	 */
	protected function fetch_download_urls( array $references ): array {
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'package_ids' => array_values( $references ),
			] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || ! isset( $data['urls'] ) ) {
			throw new \RuntimeException( 'Invalid response from package download API' );
		}

		// Response is now keyed by full reference, so return as-is
		$urls = [];
		foreach ( $data['urls'] as $ref => $info ) {
			$urls[ $ref ] = $info;
		}

		return $urls;
	}

	/**
	 * Download and extract a test package (already validated that cache doesn't have it)
	 *
	 * @param string              $reference
	 * @param array<string,mixed> $metadata Package metadata including URL and checksum
	 * @param string              $cache_dir
	 * @return TestPackageManifest
	 */
	protected function download_package( string $reference, array $metadata, string $cache_dir ): TestPackageManifest {
		// Generate cache key using checksum for reliable invalidation
		if ( ! isset( $metadata['checksum'] ) ) {
			throw new \RuntimeException( "No checksum available for package '$reference'" );
		}
		
		$cache_key = 'test_package_' . md5( $reference . '_' . $metadata['checksum'] );
		
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

				return new TestPackageManifest( $cached['manifest'] );
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
		$requested_package_id = $this->extract_package_id( $reference );
		$manifest_package_id = $manifest_object->getPackageId();
		
		// If the downloaded manifest is for a different package, check if it's a parent with the requested subpackage
		if ( $requested_package_id !== $manifest_package_id ) {
			// Try to extract the subpackage configuration
			$subpackage_manifest = $this->extract_subpackage_manifest( $manifest_object, $requested_package_id );
			if ( $subpackage_manifest ) {
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

		$manifest_array = $manifest_object->jsonSerialize();

		// Cache both manifest and metadata together
		$this->cache->set( $cache_key, [
			'manifest' => $manifest_array,
			'metadata' => $metadata,
		], DAY_IN_SECONDS );

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
	}

	/**
	 * Legacy method for backward compatibility.
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
				$this->output->writeln( "Could not fetch metadata for cache validation: " . $e->getMessage() );
			}
		}
		return null;
	}
	
	/**
	 * Extract package ID from a reference (removes version suffix).
	 *
	 * @param string $reference Package reference like "namespace/package:version"
	 * @return string Package ID like "namespace/package"
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
	 * @param TestPackageManifest $parent_manifest The parent package manifest
	 * @param string $subpackage_id The subpackage ID to extract
	 * @return TestPackageManifest|null The subpackage manifest or null if not found
	 */
	protected function extract_subpackage_manifest( TestPackageManifest $parent_manifest, string $subpackage_id ): ?TestPackageManifest {
		// Check if parent has this subpackage
		$subpackage_config = $parent_manifest->get_subpackage( $subpackage_id );
		if ( ! $subpackage_config ) {
			return null;
		}
		
		// Start with parent's configuration as base (full inheritance)
		$parent_phases = $parent_manifest->getPhases();
		$subpackage_data = [
			'package' => $subpackage_id,
			'parent_package' => $parent_manifest->getPackageId(),
			'test_type' => $parent_manifest->getTestType(),
			'test_dir' => $parent_manifest->getTestDir(),
			'test' => [
				'phases' => [
					// Global phases MUST be inherited from parent (cannot override)
					'globalSetup' => $parent_phases['globalSetup'] ?? [],
					'globalTeardown' => $parent_phases['globalTeardown'] ?? [],
				],
				// Results paths inherited from parent
				'results' => $parent_manifest->getTestResults(),
			],
			// Inherit other configurations from parent
			'requires' => $parent_manifest->getRequires(),
			'mu_plugins' => $parent_manifest->getMuPlugins(),
			'envs' => $parent_manifest->getEnv(),
			'timeout' => $parent_manifest->getTimeout(),
			'retry' => $parent_manifest->getRetry(),
		];
		
		// Apply subpackage-specific overrides (only for allowed fields)
		if ( isset( $subpackage_config['description'] ) ) {
			$subpackage_data['description'] = $subpackage_config['description'];
		}
		if ( isset( $subpackage_config['tags'] ) ) {
			$subpackage_data['tags'] = $subpackage_config['tags'];
		}
		
		// Override package-specific phases (setup, run, teardown)
		// But NOT globalSetup or globalTeardown
		if ( isset( $subpackage_config['test']['phases'] ) ) {
			$subpackage_phases = $subpackage_config['test']['phases'];
			
			// Only allow overriding non-global phases
			if ( isset( $subpackage_phases['setup'] ) ) {
				$subpackage_data['test']['phases']['setup'] = $subpackage_phases['setup'];
			}
			if ( isset( $subpackage_phases['run'] ) ) {
				$subpackage_data['test']['phases']['run'] = $subpackage_phases['run'];
			}
			if ( isset( $subpackage_phases['teardown'] ) ) {
				$subpackage_data['test']['phases']['teardown'] = $subpackage_phases['teardown'];
			}
			
			// Explicitly ignore any attempts to override global phases
			// (they should not be in subpackage config, but enforce the rule)
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
