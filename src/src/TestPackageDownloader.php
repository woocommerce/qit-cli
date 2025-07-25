<?php

namespace QIT_CLI;

use RuntimeException;
use function QIT_CLI\get_manager_url;

/**
 * Shared service for package download, verification, and extraction operations.
 * Used by both PackageDownloadCommand and PreCommand components.
 */
class TestPackageDownloader {
	private Zipper $zipper;

	public function __construct( Zipper $zipper ) {
		$this->zipper = $zipper;
	}

	/**
	 * Fetch download URLs from Manager API.
	 *
	 * @param array<string> $package_ids
	 * @return array<string, array<string,mixed>>
	 */
	public function fetch_download_urls( array $package_ids ): array {
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [ 'package_ids' => $package_ids ] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || ! isset( $data['urls'] ) ) {
			throw new RuntimeException( 'Invalid response from package download API' );
		}

		return $data['urls'];
	}

	/**
	 * Download a file from URL to destination (no retries).
	 *
	 * @param string $url
	 * @param string $destination
	 * @throws RuntimeException When download fails.
	 */
	public function download_file( string $url, string $destination ): void {
		RequestBuilder::download_file( $url, $destination );
	}

	/**
	 * Verify file checksum.
	 *
	 * @param string $file_path
	 * @param string $expected_checksum
	 * @return bool
	 */
	public function verify_checksum( string $file_path, string $expected_checksum ): bool {
		$actual_checksum = hash_file( 'sha256', $file_path );
		return strcasecmp( $actual_checksum, $expected_checksum ) === 0;
	}

	/**
	 * Extract ZIP file to directory.
	 *
	 * @param string $zip_path
	 * @param string $extract_dir
	 * @param bool   $force_overwrite
	 * @throws RuntimeException When extraction fails.
	 */
	public function extract_package( string $zip_path, string $extract_dir, bool $force_overwrite = false ): void {
		$this->zipper->validate_zip( $zip_path );

		if ( is_dir( $extract_dir ) ) {
			if ( $force_overwrite ) {
				$this->recursive_rmdir( $extract_dir );
			} else {
				throw new RuntimeException( 'Extract directory already exists' );
			}
		}

		$this->zipper->extract_zip( $zip_path, $extract_dir );
	}

	/**
	 * Generate safe filename from package identifier.
	 *
	 * @param string $package_id
	 * @return string
	 */
	public function generate_filename( string $package_id ): string {
		return str_replace( [ '/', ':' ], '-', $package_id ) . '.zip';
	}

	/**
	 * Validate package identifier format.
	 *
	 * @param string $package_id
	 * @return bool
	 */
	public function is_valid_package_identifier( string $package_id ): bool {
		return preg_match( '/^[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/', $package_id ) === 1;
	}

	/**
	 * Process a single package: download, verify, optionally extract, and install dependencies.
	 *
	 * @param string                                                 $package_id Package identifier.
	 * @param array{url:string,checksum:string|null,version?:string} $url_info Package URL info.
	 * @param string                                                 $output_dir Output directory.
	 * @param bool                                                   $verify Whether to verify checksums.
	 * @param bool                                                   $extract Whether to extract the package.
	 * @param bool                                                   $force Whether to overwrite existing files.
	 * @param bool                                                   $install Whether to install dependencies (npm/composer).
	 * @param bool                                                   $cleanup_zip Whether to delete ZIP file after extraction (only when extract=true).
	 * @return array<string,mixed> Processing result.
	 * @throws RuntimeException On any failure.
	 */
	public function process_package( string $package_id, array $url_info, string $output_dir, bool $verify = true, bool $extract = true, bool $force = false, bool $install = false, bool $cleanup_zip = true ): array {
		$filename  = $this->generate_filename( $package_id );
		$file_path = rtrim( $output_dir, '/' ) . '/' . $filename;

		// Check if file exists and handle force flag
		if ( file_exists( $file_path ) && ! $force ) {
			throw new RuntimeException( 'File already exists (use --force to overwrite)' );
		}

		// Download the package (no retries - single attempt)
		$this->download_file( $url_info['url'], $file_path );

		// Verify checksum if enabled and available
		if ( $verify && ! empty( $url_info['checksum'] ) ) {
			if ( ! $this->verify_checksum( $file_path, $url_info['checksum'] ) ) {
				unlink( $file_path ); // Clean up failed download
				throw new RuntimeException( 'Checksum verification failed' );
			}
		}

		$result = [
			'package'       => $package_id,
			'downloaded_to' => $file_path,
			'size'          => filesize( $file_path ),
			'checksum'      => $url_info['checksum'] ?? null,
			'version'       => $url_info['version'] ?? 'unknown',
		];

		// Extract if requested
		if ( $extract ) {
			$extract_dir = rtrim( $output_dir, '/' ) . '/' . pathinfo( $filename, PATHINFO_FILENAME );

			try {
				$this->extract_package( $file_path, $extract_dir, $force );
				$result['extracted_to'] = $extract_dir;

				// Clean up ZIP file after successful extraction if requested
				// This preserves the security measure of using unlink() with proper error handling
				if ( $cleanup_zip && file_exists( $file_path ) ) {
					unlink( $file_path );
					$result['zip_cleaned_up'] = true;
				}
			} catch ( \Exception $e ) {
				// Clean up the downloaded file if extraction fails
				unlink( $file_path );
				throw new RuntimeException( 'Extraction failed: ' . $e->getMessage() );
			}
		}

		// Install dependencies if requested and package was extracted
		if ( $install && isset( $result['extracted_to'] ) ) {
			try {
				$this->install_dependencies( $result['extracted_to'] );
				$result['dependencies_installed'] = true;
			} catch ( \Exception $e ) {
				// If installation fails, don't fail the entire download
				// Just log the error and continue (same behavior as command)
				$result['dependencies_installed'] = false;
				$result['install_error']          = $e->getMessage();
			}
		}

		// Add verification status to result
		if ( $verify && ! empty( $url_info['checksum'] ) ) {
			$result['checksum_verified'] = true;
		}

		return $result;
	}

	/**
	 * Install dependencies (npm/composer) in the extracted directory.
	 *
	 * @param string $extract_dir The directory where the package was extracted.
	 * @throws RuntimeException If dependency installation fails.
	 */
	private function install_dependencies( string $extract_dir ): void {
		$installed_something = false;

		// Check for package.json and run appropriate npm command
		if ( file_exists( $extract_dir . '/package.json' ) ) {
			// Use npm ci if package-lock.json exists, otherwise use npm install
			$use_ci          = file_exists( $extract_dir . '/package-lock.json' );
			$npm_command     = 'cd ' . escapeshellarg( $extract_dir ) . ' && npm ' . ( $use_ci ? 'ci' : 'install' );
			$npm_output      = [];
			$npm_return_code = 0;

			exec( $npm_command . ' 2>&1', $npm_output, $npm_return_code );

			if ( $npm_return_code !== 0 ) {
				$command_used = $use_ci ? 'npm ci' : 'npm install';
				throw new RuntimeException( $command_used . ' failed: ' . implode( "\n", $npm_output ) );
			}

			$installed_something = true;
		}

		// Check for composer.json and run composer install
		if ( file_exists( $extract_dir . '/composer.json' ) ) {
			$composer             = escapeshellcmd( 'composer' );
			$composer_command     = 'cd ' . escapeshellarg( $extract_dir ) . ' && ' . $composer . ' install --no-dev --optimize-autoloader';
			$composer_output      = [];
			$composer_return_code = 0;

			exec( $composer_command . ' 2>&1', $composer_output, $composer_return_code );

			if ( $composer_return_code !== 0 ) {
				throw new RuntimeException( 'composer install failed: ' . implode( "\n", $composer_output ) );
			}

			$installed_something = true;
		}

		if ( ! $installed_something ) {
			// No package.json or composer.json found, nothing to install
			return;
		}
	}

	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursive_rmdir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}
