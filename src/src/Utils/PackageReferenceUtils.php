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
	 * @param string $reference The package reference.
	 * @return bool True if it's a local path, false otherwise.
	 */
	public static function is_local_reference( string $reference ): bool {
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
	 * - "woocommerce/e2e" -> "latest" (default)
	 * - "./local/path" -> null
	 *
	 * @param string $reference The package reference.
	 * @return string|null The version, or null if it's a local path.
	 */
	public static function extract_version( string $reference ): ?string {
		// Local paths don't have versions
		if ( self::is_local_reference( $reference ) ) {
			return null;
		}

		// Extract version after colon, default to 'latest'
		if ( strpos( $reference, ':' ) !== false ) {
			return substr( $reference, strpos( $reference, ':' ) + 1 );
		}

		return 'latest';
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
		$manifest_data = json_decode( $manifest_content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return null;
		}

		return $manifest_data['package'] ?? null;
	}
}