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
 * Downloads and caches remote test packages from the QIT repository
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
	 * Download multiple test packages
	 *
	 * @param array<string, array<string,mixed>> $packages Map of reference => package info.
	 * @param string                             $cache_dir Cache directory.
	 *
	 * @return array<string,TestPackageManifest> Map of reference => manifest objects
	 */
	public function download( array $packages, string $cache_dir ): array {
		if ( empty( $packages ) ) {
			return [];
		}

		$start     = microtime( true );
		$manifests = [];

		// Get download URLs from QIT Manager
		$this->output->writeln( 'Fetching download URLs...' );
		$download_urls = $this->fetch_download_urls( array_keys( $packages ) );

		// Download each package
		foreach ( $packages as $reference => $package_info ) {
			if ( ! isset( $download_urls[ $reference ] ) ) {
				throw new \RuntimeException( "No download URL found for package '$reference'" );
			}

			$this->output->writeln( "Downloading package: $reference" );
			$manifests[ $reference ] = $this->download_package( $reference, $download_urls[ $reference ], $cache_dir );
		}

		$elapsed = round( microtime( true ) - $start, 2 );
		$this->output->writeln( 'Downloaded ' . count( $manifests ) . " packages in {$elapsed}s" );

		return $manifests;
	}

	/**
	 * Download a single test package
	 *
	 * @return TestPackageManifest
	 */
	public function download_single( string $reference, string $cache_dir ): TestPackageManifest {
		$download_urls = $this->fetch_download_urls( [ $reference ] );

		if ( ! isset( $download_urls[ $reference ] ) ) {
			throw new \RuntimeException( "No download URL found for package '$reference'" );
		}

		return $this->download_package( $reference, $download_urls[ $reference ], $cache_dir );
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
				'references' => array_values( $references ),
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
	 * Download and extract a test package
	 *
	 * @param string              $reference
	 * @param array<string,mixed> $url_info
	 * @param string              $cache_dir
	 * @return TestPackageManifest
	 */
	protected function download_package( string $reference, array $url_info, string $cache_dir ): TestPackageManifest {
		// Use checksum in cache key for cache busting
		$cache_suffix = $url_info['version'] ?? 'latest';
		if ( isset( $url_info['checksum'] ) ) {
			$cache_suffix .= '_' . substr( $url_info['checksum'], 0, 8 ); // Use first 8 chars of checksum
		}
		$cache_key = 'test_package_' . md5( $reference . '_' . $cache_suffix );
		$cached    = $this->cache->get( $cache_key );

		if ( $cached && is_array( $cached ) && isset( $cached['manifest'] ) ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( "Using cached test package '$reference'" );
			}

			// Restore metadata for caller access
			if ( isset( $cached['metadata'] ) ) {
				$this->package_metadata[ $reference ] = $cached['metadata'];
			}

			return new TestPackageManifest( $cached['manifest'] );
		}

		// Download the package
		$package_dir = $cache_dir . '/packages/' . md5( $reference );
		$zip_file    = $package_dir . '.zip';

		if ( ! file_exists( dirname( $zip_file ) ) ) {
			mkdir( dirname( $zip_file ), 0755, true );
		}

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( "Downloading test package '$reference' from {$url_info['url']}" );
		}

		RequestBuilder::download_file( $url_info['url'], $zip_file );

		// Validate and extract
		$this->zipper->validate_zip( $zip_file );

		if ( is_dir( $package_dir ) ) {
			// Clean existing directory
			$this->recursive_rmdir( $package_dir );
		}

		$this->zipper->extract_zip( $zip_file, $package_dir );

		// Find and parse manifest
		$manifest_file = $this->find_manifest( $package_dir );
		if ( ! $manifest_file ) {
			throw new \RuntimeException( "No manifest.json found in package '$reference'" );
		}

		$manifest_object = $this->manifest_parser->parse( $manifest_file );

		// Prepare metadata separately
		$metadata                             = [
			'reference'       => $reference,
			'remote'          => true,
			'downloaded_path' => $package_dir,
			'version'         => $url_info['version'] ?? 'unknown',
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
	 * Find manifest.json in extracted package
	 */
	protected function find_manifest( string $dir ): ?string {
		// Check root directory
		if ( file_exists( $dir . '/manifest.json' ) ) {
			return $dir . '/manifest.json';
		}

		// Check one level deep (common with GitHub archives)
		$entries = scandir( $dir );
		foreach ( $entries as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$path = $dir . '/' . $entry;
			if ( is_dir( $path ) && file_exists( $path . '/manifest.json' ) ) {
				return $path . '/manifest.json';
			}
		}

		return null;
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

		// Cache for 1 hour
		$this->cache->set( $cache_key, $info, HOUR_IN_SECONDS );

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
