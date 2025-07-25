<?php

namespace QIT_CLI\PreCommand\Pipeline\Stages;

use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\PipelineStage;
use QIT_CLI\TestPackageDownloader;
use RuntimeException;

/**
 * Pipeline stage for resolving static test packages.
 *
 * This stage handles raw package identifiers (namespace/package:version)
 * from CLI arguments or options, downloads and extracts them using the
 * shared TestPackageDownloader service, and stores the results in the
 * PipelineContext for use by later stages.
 */
class ResolveTestPackageStage implements PipelineStage {
	private TestPackageDownloader $downloader;

	public function __construct( TestPackageDownloader $downloader ) {
		$this->downloader = $downloader;
	}

	/**
	 * Process the pipeline context to resolve static test packages.
	 *
	 * @param PipelineContext $context The pipeline context.
	 * @return PipelineContext The modified context with resolved packages.
	 */
	public function process( PipelineContext $context ): PipelineContext {
		$input = $context->input;

		// Get package identifier from CLI argument or option
		$package_identifier = $this->getPackageIdentifier( $input );

		if ( ! $package_identifier ) {
			// No static package to resolve, continue with existing context
			return $context;
		}

		// Validate package identifier format
		if ( ! $this->downloader->is_valid_package_identifier( $package_identifier ) ) {
			throw new RuntimeException( "Invalid package identifier format: {$package_identifier}. Expected: namespace/package:version", 1 );
		}

		// Check if checksum verification should be disabled
		$verify_checksums = $input->getOption( 'verify' ) !== false;

		// Resolve the package
		$extracted_path = $this->resolvePackage( $package_identifier, $verify_checksums );

		// Store results in context for later stages
		$context->set( 'static_test_package_path', $extracted_path );
		$context->set( 'static_test_package_id', $package_identifier );

		// Also add to the test_packages array if it exists
		$existing_packages                        = $context->get_test_packages();
		$existing_packages[ $package_identifier ] = [
			'path'     => $extracted_path,
			'type'     => 'static',
			'resolved' => true,
		];
		$context->set_test_packages( $existing_packages );

		return $context;
	}

	/**
	 * Get package identifier from CLI input.
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @return string|null
	 */
	private function getPackageIdentifier( $input ): ?string {
		// Try to get from 'package' argument first
		if ( $input->hasArgument( 'package' ) ) {
			$package = $input->getArgument( 'package' );
			if ( $package ) {
				return $package;
			}
		}

		// Try to get from 'test-package' option
		if ( $input->hasOption( 'test-package' ) ) {
			$test_packages = $input->getOption( 'test-package' );
			if ( is_array( $test_packages ) && ! empty( $test_packages ) ) {
				// For now, just take the first one
				// In the future, this could be enhanced to handle multiple packages
				return $test_packages[0];
			} elseif ( is_string( $test_packages ) && ! empty( $test_packages ) ) {
				return $test_packages;
			}
		}

		return null;
	}

	/**
	 * Resolve a package identifier to an extracted directory path.
	 *
	 * @param string $identifier Package identifier (namespace/package:version).
	 * @param bool   $verify_checksums Whether to verify checksums.
	 * @return string Absolute path to extracted package directory.
	 * @throws RuntimeException On any failure.
	 */
	private function resolvePackage( string $identifier, bool $verify_checksums ): string {
		// Fetch download URL and checksum
		$urls = $this->downloader->fetch_download_urls( [ $identifier ] );
		if ( ! isset( $urls[ $identifier ] ) ) {
			throw new RuntimeException( "Package not found: {$identifier}", 1 );
		}

		$url_info = $urls[ $identifier ];

		// Setup paths
		$temp_dir     = sys_get_temp_dir() . '/qit-packages';
		$zip_path     = $temp_dir . '/' . $this->downloader->generate_filename( $identifier );
		$extract_path = $temp_dir . '/' . str_replace( [ '/', ':' ], '-', $identifier );

		// Ensure temp directory exists
		if ( ! is_dir( $temp_dir ) && ! mkdir( $temp_dir, 0755, true ) ) {
			throw new RuntimeException( "Failed to create temporary directory: {$temp_dir}", 1 );
		}

		// Download (no retries - single attempt)
		try {
			$this->downloader->download_file( $url_info['url'], $zip_path );
		} catch ( \Exception $e ) {
			throw new RuntimeException( 'Download failed: ' . $e->getMessage(), 1 );
		}

		// Verify checksum if enabled
		if ( $verify_checksums && ! empty( $url_info['checksum'] ) ) {
			if ( ! $this->downloader->verify_checksum( $zip_path, $url_info['checksum'] ) ) {
				unlink( $zip_path );
				throw new RuntimeException( "Checksum verification failed for: {$identifier}", 1 );
			}
		}

		// Extract package
		try {
			$this->downloader->extract_package( $zip_path, $extract_path, true );
		} catch ( \Exception $e ) {
			unlink( $zip_path );
			throw new RuntimeException( 'Extraction failed: ' . $e->getMessage(), 1 );
		}

		// Cleanup zip
		unlink( $zip_path );

		return $extract_path;
	}
}
