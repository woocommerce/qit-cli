<?php

namespace QIT_CLI\Validation;

use QIT_CLI\PreCommand\Objects\Extension;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\debug_log;

/**
 * Centralized artifact validation for all package types.
 *
 * Performs minimal sanity checks on downloaded or cached artifacts to catch
 * obviously broken files (empty downloads, wrong file types, etc).
 *
 * Philosophy: Keep validation as simple as possible. We only check for:
 * - Plugins: Any PHP file containing "plugin name" (case-insensitive)
 * - Themes: style.css containing "theme name" (case-insensitive)
 * - Test packages: qit-test.json exists (actual validation happens at runtime)
 *
 * We intentionally don't validate schemas or enforce strict requirements.
 * The actual systems (WordPress, test runner) will provide proper validation
 * and error messages when they try to use these artifacts.
 */
class ArtifactValidator {
	protected OutputInterface $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Validate an extension artifact (plugin or theme).
	 *
	 * @param Extension $extension The extension to validate.
	 * @throws \RuntimeException If validation fails.
	 */
	public function validate_extension( Extension $extension ): void {
		if ( empty( $extension->downloaded_source ) ) {
			throw new \RuntimeException( "No downloaded source for {$extension->type} '{$extension->slug}'" );
		}

		$source = $extension->downloaded_source;

		if ( $extension->type === 'plugin' ) {
			$this->validate_plugin( $extension->slug, $source );
		} elseif ( $extension->type === 'theme' ) {
			$this->validate_theme( $extension->slug, $source );
		} else {
			throw new \RuntimeException( "Unknown extension type: {$extension->type}" );
		}

		debug_log( "✓ Validated {$extension->type} artifact: {$extension->slug}" );
	}

	/**
	 * Validate a plugin artifact.
	 *
	 * @param string $slug Plugin slug.
	 * @param string $path Path to plugin (directory or ZIP).
	 * @throws \RuntimeException If validation fails.
	 */
	protected function validate_plugin( string $slug, string $path ): void {
		$has_valid_header = false;
		$checked_files    = [];

		if ( is_dir( $path ) ) {
			// Check directory for PHP files with plugin header
			$has_valid_header = $this->check_plugin_directory( $path, $checked_files );
		} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			// Check ZIP for PHP files with plugin header
			$has_valid_header = $this->check_plugin_zip( $path, $slug, $checked_files );
		} else {
			throw new \RuntimeException( "Plugin source is neither a directory nor a ZIP file: $path" );
		}

		if ( ! $has_valid_header ) {
			$files_checked = empty( $checked_files ) ? 'no PHP files found' : implode( ', ', $checked_files );
			throw new \RuntimeException(
				"Invalid plugin artifact for '$slug': No PHP file containing 'Plugin Name' found. " .
				"Checked files: $files_checked"
			);
		}
	}

	/**
	 * Check plugin directory for valid header.
	 *
	 * @param string            $directory      Plugin directory path.
	 * @param array<int,string> &$checked_files List of checked filenames (output parameter).
	 */
	protected function check_plugin_directory( string $directory, array &$checked_files ): bool {
		$iterator = new \DirectoryIterator( $directory );

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
				continue;
			}

			$checked_files[] = $file->getFilename();

			// Read first 8KB to find header
			$contents = file_get_contents( $file->getPathname(), false, null, 0, 8192 );

			// Simple check: just look for "plugin name" (case-insensitive)
			if ( stripos( $contents, 'plugin name' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check plugin ZIP for valid header.
	 *
	 * @param string            $zip_path       Path to plugin ZIP.
	 * @param string            $slug           Expected plugin slug (directory name inside ZIP).
	 * @param array<int,string> &$checked_files List of checked filenames (output parameter).
	 */
	protected function check_plugin_zip( string $zip_path, string $slug, array &$checked_files ): bool {
		$zip = new \ZipArchive();

		if ( $zip->open( $zip_path ) !== true ) {
			throw new \RuntimeException( "Failed to open ZIP file: $zip_path" );
		}

		try {
			// First try with the expected slug structure
			$slug_prefix     = $slug . '/';
			$slug_prefix_len = strlen( $slug_prefix );
			$found_with_slug = false;

			for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- External ZipArchive API uses camelCase
				$filename = $zip->getNameIndex( $i );

				// Skip if not in plugin root directory
				if ( strpos( $filename, $slug_prefix ) !== 0 ) {
					continue;
				}

				$found_with_slug = true;

				// Skip if in subdirectory
				$relative_path = substr( $filename, $slug_prefix_len );
				if ( strpos( $relative_path, '/' ) !== false ) {
					continue;
				}

				// Skip if not PHP file
				if ( substr( $filename, -4 ) !== '.php' ) {
					continue;
				}

				$checked_files[] = basename( $filename );

				// Read first 8KB to check for plugin header
				$contents = $zip->getFromIndex( $i, 0, \ZipArchive::FL_UNCHANGED );
				if ( $contents === false ) {
					continue;
				}

				$contents = substr( $contents, 0, 8192 );

				// Simple check: just look for "plugin name" (case-insensitive)
				if ( stripos( $contents, 'plugin name' ) !== false ) {
					return true;
				}
			}

			// If no files found with expected slug, try to find any top-level directory
			if ( ! $found_with_slug ) {
				// Detect the actual top-level directory in the ZIP
				$top_dirs = [];
				for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- External ZipArchive API uses camelCase
					$filename = $zip->getNameIndex( $i );
					$parts    = explode( '/', $filename );
					if ( count( $parts ) > 1 && ! empty( $parts[0] ) ) {
						$top_dirs[ $parts[0] ] = true;
					}
				}

				// Check each top-level directory for plugin files
				foreach ( array_keys( $top_dirs ) as $dir ) {
					$dir_prefix     = $dir . '/';
					$dir_prefix_len = strlen( $dir_prefix );

					for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- External ZipArchive API uses camelCase
						$filename = $zip->getNameIndex( $i );

						// Skip if not in this directory
						if ( strpos( $filename, $dir_prefix ) !== 0 ) {
							continue;
						}

						// Skip if in subdirectory
						$relative_path = substr( $filename, $dir_prefix_len );
						if ( strpos( $relative_path, '/' ) !== false ) {
							continue;
						}

						// Skip if not PHP file
						if ( substr( $filename, -4 ) !== '.php' ) {
							continue;
						}

						$checked_files[] = basename( $filename );

						// Read first 8KB to check for plugin header
						$contents = $zip->getFromIndex( $i, 0, \ZipArchive::FL_UNCHANGED );
						if ( $contents === false ) {
							continue;
						}

						$contents = substr( $contents, 0, 8192 );

						// Simple check: just look for "plugin name" (case-insensitive)
						if ( stripos( $contents, 'plugin name' ) !== false ) {
							debug_log( "Found plugin header in $filename (actual dir: $dir, expected slug: $slug)" );
							return true;
						}
					}
				}
			}
		} finally {
			$zip->close();
		}

		return false;
	}

	/**
	 * Validate a theme artifact.
	 *
	 * @param string $slug Theme slug.
	 * @param string $path Path to theme (directory or ZIP).
	 * @throws \RuntimeException If validation fails.
	 */
	protected function validate_theme( string $slug, string $path ): void {
		$has_style_css  = false;
		$has_theme_name = false;

		if ( is_dir( $path ) ) {
			// Check for style.css in directory
			$style_path = $path . '/style.css';
			if ( file_exists( $style_path ) ) {
				$has_style_css = true;
				$contents      = file_get_contents( $style_path, false, null, 0, 8192 );
				// Simple check: just look for "theme name" (case-insensitive)
				if ( stripos( $contents, 'theme name' ) !== false ) {
					$has_theme_name = true;
				}
			}
		} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			// Check for style.css in ZIP
			$zip = new \ZipArchive();
			if ( $zip->open( $path ) !== true ) {
				throw new \RuntimeException( "Failed to open ZIP file: $path" );
			}

			try {
				// First try expected path
				$expected_path = $slug . '/style.css';
				$index         = $zip->locateName( $expected_path );

				if ( $index !== false ) {
					$has_style_css = true;
					$contents      = $zip->getFromIndex( $index, 0, \ZipArchive::FL_UNCHANGED );
					if ( $contents !== false ) {
						$contents = substr( $contents, 0, 8192 );
						// Simple check: just look for "theme name" (case-insensitive)
						if ( stripos( $contents, 'theme name' ) !== false ) {
							$has_theme_name = true;
						}
					}
				} else {
					// If not found with expected slug, look for any style.css in top-level directories
					for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- External ZipArchive API uses camelCase
						$filename = $zip->getNameIndex( $i );
						if ( preg_match( '#^[^/]+/style\.css$#', $filename ) ) {
							$has_style_css = true;
							$contents      = $zip->getFromIndex( $i, 0, \ZipArchive::FL_UNCHANGED );
							if ( $contents !== false ) {
								$contents = substr( $contents, 0, 8192 );
								// Simple check: just look for "theme name" (case-insensitive)
								if ( stripos( $contents, 'theme name' ) !== false ) {
									$has_theme_name = true;
								}
							}
							break;
						}
					}
				}
			} finally {
				$zip->close();
			}
		} else {
			throw new \RuntimeException( "Theme source is neither a directory nor a ZIP file: $path" );
		}

		if ( ! $has_style_css ) {
			throw new \RuntimeException( "Invalid theme artifact for '$slug': No style.css file found" );
		}

		if ( ! $has_theme_name ) {
			throw new \RuntimeException( "Invalid theme artifact for '$slug': style.css missing 'Theme Name' text" );
		}
	}

	/**
	 * Validate a test package artifact.
	 *
	 * @param string $path Path to test package directory.
	 * @param string $package_id Package identifier.
	 * @throws \RuntimeException If validation fails.
	 */
	public function validate_test_package( string $path, string $package_id ): void {
		if ( ! is_dir( $path ) ) {
			throw new \RuntimeException( "Test package path is not a directory: $path" );
		}

		// Just check that qit-test.json exists somewhere in the package
		// The actual schema validation happens when we run the test
		$manifest_path = $path . '/qit-test.json';

		if ( ! file_exists( $manifest_path ) ) {
			throw new \RuntimeException(
				"Invalid test package artifact for '$package_id': No qit-test.json found"
			);
		}

		debug_log( "✓ Validated test package artifact: $package_id" );
	}

	/**
	 * Validate any artifact based on its type.
	 *
	 * @param string $type Artifact type: 'plugin', 'theme', or 'test-package'.
	 * @param string $path Path to the artifact.
	 * @param string $identifier Slug or package ID.
	 * @throws \RuntimeException If validation fails.
	 */
	public function validate_artifact( string $type, string $path, string $identifier ): void {
		switch ( $type ) {
			case 'plugin':
				$this->validate_plugin( $identifier, $path );
				break;
			case 'theme':
				$this->validate_theme( $identifier, $path );
				break;
			case 'test-package':
				$this->validate_test_package( $path, $identifier );
				break;
			default:
				throw new \RuntimeException( "Unknown artifact type: $type" );
		}
	}
}
