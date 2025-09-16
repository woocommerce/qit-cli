<?php

namespace QIT_CLI\Utils;

/**
 * Utility class for handling test package references.
 *
 * Provides centralized logic for parsing and extracting information
 * from package references like "woocommerce/e2e:latest" or local paths.
 */
class PackageReferenceUtils {

	/**
	 * Extract package ID from a package reference.
	 *
	 * Examples:
	 * - "woocommerce/e2e:latest" -> "woocommerce/e2e"
	 * - "woocommerce/e2e:1.0.0" -> "woocommerce/e2e"
	 * - "woocommerce/e2e" -> "woocommerce/e2e"
	 * - "./local/path" -> null (local paths return null)
	 *
	 * @param string $reference The package reference.
	 * @return string|null The package ID, or null if it's a local path.
	 */
	public static function extract_package_id( string $reference ): ?string {
		// Check if it's a local directory path
		if ( is_dir( $reference ) ) {
			return null;
		}

		// For remote packages, remove version suffix if present
		$package_id = $reference;
		if ( strpos( $reference, ':' ) !== false ) {
			$package_id = substr( $reference, 0, strpos( $reference, ':' ) );
		}

		return $package_id;
	}

	/**
	 * Check if a reference is a local path.
	 *
	 * Distinguishes local paths from remote package references based on patterns:
	 * - Local: absolute paths (/path), relative paths (./path, ../path),
	 *   simple directories, or paths with 3+ slashes
	 * - Remote: namespace/package (1 slash) or namespace/package/subpackage (2 slashes)
	 *
	 * @param string $reference The package reference.
	 * @return bool True if it's a local path, false otherwise.
	 */
	public static function is_local_reference( string $reference ): bool {
		// Absolute paths
		if ( strpos( $reference, '/' ) === 0 ) {
			return true;
		}

		// Relative paths with ./ or ../
		if ( strpos( $reference, './' ) === 0 || strpos( $reference, '../' ) === 0 ) {
			return true;
		}

		$slash_count = substr_count( $reference, '/' );

		// Simple directory name (no slashes)
		if ( $slash_count === 0 ) {
			return true;
		}

		// 3 or more slashes - definitely a local path
		if ( $slash_count >= 3 ) {
			return true;
		}

		// 1 or 2 slashes - could be remote package/subpackage or existing local directory
		// Check if it's an existing directory as a fallback
		return is_dir( $reference );
	}

	/**
	 * Check if a reference is a remote package.
	 *
	 * @param string $reference The package reference.
	 * @return bool True if it's a remote package reference, false otherwise.
	 */
	public static function is_remote_reference( string $reference ): bool {
		return ! self::is_local_reference( $reference );
	}

	/**
	 * Extract version from a package reference.
	 *
	 * Examples:
	 * - "woocommerce/e2e:latest" -> "latest"
	 * - "woocommerce/e2e:1.0.0" -> "1.0.0"
	 * - "woocommerce/e2e" -> null (no version specified)
	 * - "./local/path" -> null
	 *
	 * @param string $reference The package reference.
	 * @return string|null The version, or null if it's a local path or no version specified.
	 */
	public static function extract_version( string $reference ): ?string {
		// Local paths don't have versions
		if ( self::is_local_reference( $reference ) ) {
			return null;
		}

		// Extract version after colon, return null if no version specified
		if ( strpos( $reference, ':' ) !== false ) {
			return substr( $reference, strpos( $reference, ':' ) + 1 );
		}

		return null;
	}

	/**
	 * Check if a reference has a version specified.
	 *
	 * @param string $reference The package reference.
	 * @return bool True if the reference has a version, false otherwise.
	 */
	public static function has_version( string $reference ): bool {
		// Local paths don't need versions
		if ( self::is_local_reference( $reference ) ) {
			return true; // Consider local paths as "having a version" since they don't need one
		}

		return strpos( $reference, ':' ) !== false;
	}

	/**
	 * Validate a package reference.
	 *
	 * Ensures remote packages have explicit versions (no fallback to 'latest').
	 * Local paths are always valid.
	 *
	 * @param string $reference The package reference to validate.
	 * @throws \RuntimeException If the reference is invalid.
	 */
	public static function validate_reference( string $reference ): void {
		// Local paths are always valid
		if ( self::is_local_reference( $reference ) ) {
			return;
		}

		// Remote packages must have an explicit version
		if ( ! self::has_version( $reference ) ) {
			$package_id = self::extract_package_id( $reference );

			// Determine if this looks like a subpackage reference
			$parts                = explode( '/', $package_id );
			$is_likely_subpackage = count( $parts ) > 2 ||
				( count( $parts ) === 2 && strpos( $parts[1], '/' ) !== false );

			if ( $is_likely_subpackage ) {
				throw new \RuntimeException(
					"Package reference '$reference' is missing a version number.\n" .
					"Subpackages must include a version, e.g., '$reference:1.0.0'\n" .
					'To see available versions, run: qit package:list'
				);
			} else {
				throw new \RuntimeException(
					"Package reference '$reference' is missing a version number.\n" .
					"Remote packages must include an explicit version (e.g., '$reference:latest' or '$reference:1.0.0')\n" .
					'To see available versions, run: qit package:list'
				);
			}
		}
	}

	/**
	 * Validate multiple package references.
	 *
	 * @param array<string> $references Array of package references to validate.
	 * @throws \RuntimeException If any reference is invalid.
	 */
	public static function validate_references( array $references ): void {
		foreach ( $references as $reference ) {
			self::validate_reference( $reference );
		}
	}

	/**
	 * Read package ID from a local package's qit-test.json manifest.
	 *
	 * @param string $path Path to the package directory.
	 * @return string|null The package ID from manifest, or null if not found/invalid.
	 */
	public static function read_local_package_id( string $path ): ?string {
		$manifest_path = rtrim( $path, '/' ) . '/qit-test.json';

		if ( ! file_exists( $manifest_path ) ) {
			return null;
		}

		$manifest_content = file_get_contents( $manifest_path );
		$manifest_data    = json_decode( $manifest_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return null;
		}

		return $manifest_data['package'] ?? null;
	}
}
