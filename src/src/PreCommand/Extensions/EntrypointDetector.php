<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\PreCommand\Objects\Extension;
use function QIT_CLI\debug_log;

/**
 * Detects entrypoints for WordPress plugins and themes
 */
class EntrypointDetector {
	/**
	 * Detect the entrypoint for an extension
	 *
	 * @param Extension $extension The extension to detect entrypoint for.
	 *
	 * @return void
	 */
	public function detect( Extension $extension ): void {
		if ( ! empty( $extension->entrypoint ) ) {
			debug_log( "Entrypoint already detected for {$extension->slug}: {$extension->entrypoint}" );

			return;
		}

		if ( empty( $extension->downloaded_source ) ) {
			debug_log( "No downloaded source for {$extension->slug}, skipping entrypoint detection" );

			return;
		}

		debug_log( "Detecting entrypoint for {$extension->slug} ({$extension->type})" );

		if ( is_dir( $extension->downloaded_source ) ) {
			$this->detect_in_directory( $extension, $extension->downloaded_source );
		} elseif ( is_file( $extension->downloaded_source ) && pathinfo( $extension->downloaded_source, PATHINFO_EXTENSION ) === 'zip' ) {
			$this->detect_in_zip( $extension );
		} else {
			debug_log( "Unknown source type for entrypoint detection: {$extension->downloaded_source}" );
		}

		if ( empty( $extension->entrypoint ) ) {
			debug_log( "WARNING: No entrypoint found for {$extension->slug}", 'warning' );
		}
	}

	/**
	 * Detect entrypoint in a directory
	 */
	protected function detect_in_directory( Extension $extension, string $directory ): void {
		if ( $extension->type === 'theme' ) {
			$this->detect_theme_entrypoint_in_directory( $extension, $directory );
		} elseif ( $extension->type === 'plugin' ) {
			$this->detect_plugin_entrypoint_in_directory( $extension, $directory );
		}
	}

	/**
	 * Detect theme entrypoint in directory
	 */
	protected function detect_theme_entrypoint_in_directory( Extension $extension, string $directory ): void {
		if ( file_exists( "$directory/style.css" ) ) {
			$extension->entrypoint = "{$extension->slug}/style.css";
			debug_log( "  Found theme entrypoint: {$extension->entrypoint}" );
		} else {
			debug_log( '  No style.css found in theme directory', 'error' );
		}
	}

	/**
	 * Detect plugin entrypoint in directory
	 */
	protected function detect_plugin_entrypoint_in_directory( Extension $extension, string $directory ): void {
		// First, try the standard naming convention
		$standard_file = "$directory/{$extension->slug}.php";
		if ( file_exists( $standard_file ) ) {
			$extension->entrypoint = "{$extension->slug}/{$extension->slug}.php";
			debug_log( "  Found plugin entrypoint (standard): {$extension->entrypoint}" );

			return;
		}

		// Scan for PHP files with Plugin Name header
		$this->scan_for_plugin_header( $extension, $directory );
	}

	/**
	 * Scan directory for PHP files with Plugin Name header
	 */
	protected function scan_for_plugin_header( Extension $extension, string $directory ): void {
		$iterator = new \DirectoryIterator( $directory );

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
				continue;
			}

			// Read first 8KB of file to find header
			$contents = file_get_contents( $file->getPathname(), false, null, 0, 8192 );

			if ( preg_match( '/^\s*\*\s*Plugin Name:/m', $contents ) ) {
				$extension->entrypoint = "{$extension->slug}/{$file->getFilename()}";
				debug_log( "  Found plugin entrypoint (scan): {$extension->entrypoint}" );

				return;
			}
		}

		debug_log( '  No plugin header found in any PHP file', 'error' );
	}

	/**
	 * Detect entrypoint in a ZIP file without extracting
	 */
	protected function detect_in_zip( Extension $extension ): void {
		$zip = new \ZipArchive();

		if ( $zip->open( $extension->downloaded_source ) !== true ) {
			debug_log( "  Failed to open ZIP for entrypoint detection: {$extension->downloaded_source}", 'error' );

			return;
		}

		try {
			if ( $extension->type === 'theme' ) {
				$this->detect_theme_entrypoint_in_zip( $extension, $zip );
			} elseif ( $extension->type === 'plugin' ) {
				$this->detect_plugin_entrypoint_in_zip( $extension, $zip );
			}
		} finally {
			$zip->close();
		}
	}

	/**
	 * Detect theme entrypoint in ZIP
	 */
	protected function detect_theme_entrypoint_in_zip( Extension $extension, \ZipArchive $zip ): void {
		$expected_path = "{$extension->slug}/style.css";

		if ( $zip->locateName( $expected_path ) !== false ) {
			$extension->entrypoint = $expected_path;
			debug_log( "  Found theme entrypoint in ZIP: {$extension->entrypoint}" );
		} else {
			debug_log( "  No style.css found in theme ZIP at expected path: $expected_path", 'error' );
		}
	}

	/**
	 * Detect plugin entrypoint in ZIP
	 */
	protected function detect_plugin_entrypoint_in_zip( Extension $extension, \ZipArchive $zip ): void {
		// First try standard naming
		$expected_path = "{$extension->slug}/{$extension->slug}.php";

		if ( $zip->locateName( $expected_path ) !== false ) {
			$extension->entrypoint = $expected_path;
			debug_log( "  Found plugin entrypoint in ZIP (standard): {$extension->entrypoint}" );

			return;
		}

		// Scan for PHP files with Plugin Name header
		$this->scan_zip_for_plugin_header( $extension, $zip );
	}

	/**
	 * Scan ZIP for PHP files with Plugin Name header
	 */
	protected function scan_zip_for_plugin_header( Extension $extension, \ZipArchive $zip ): void {
		$slug_prefix     = $extension->slug . '/';
		$slug_prefix_len = strlen( $slug_prefix );

		for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$filename = $zip->getNameIndex( $i );

			// Skip if not in plugin root directory
			if ( strpos( $filename, $slug_prefix ) !== 0 ) {
				continue;
			}

			// Skip if in subdirectory
			$relative_path = substr( $filename, $slug_prefix_len );
			if ( strpos( $relative_path, '/' ) !== false ) {
				continue;
			}

			// Skip if not PHP file
			if ( substr( $filename, - 4 ) !== '.php' ) {
				continue;
			}

			// Read first 8KB to check for plugin header
			$contents = $zip->getFromIndex( $i, 0, \ZipArchive::FL_UNCHANGED );
			if ( $contents === false ) {
				continue;
			}

			// Only read first 8KB
			$contents = substr( $contents, 0, 8192 );

			if ( preg_match( '/^\s*\*\s*Plugin Name:/m', $contents ) ) {
				$extension->entrypoint = $filename;
				debug_log( "  Found plugin entrypoint in ZIP (scan): {$extension->entrypoint}" );

				return;
			}
		}

		debug_log( '  No plugin header found in ZIP', 'error' );
	}

	/**
	 * Batch detect entrypoints for multiple extensions
	 *
	 * @param Extension[] $extensions
	 */
	public function detect_batch( array $extensions ): void {
		foreach ( $extensions as $extension ) {
			$this->detect( $extension );
		}
	}
}
