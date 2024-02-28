<?php

namespace QIT_CLI;

use Symfony\Component\Filesystem\Filesystem;

class SafeRemove {
	/**
	 * Safely delete a directory after validating its depth and that it's inside a specific directory.
	 *
	 * @param string $path The directory to delete.
	 * @param string $assert_inside_dir A directory that the path must be inside of.
	 * @param int    $min_depth The minimum depth of the path.
	 *
	 * @return void
	 * @throws \Exception If the safety checks fail.
	 */
	public static function delete_dir( string $path, string $assert_inside_dir = '', int $min_depth = 2 ) {
		$filesystem = new Filesystem();

		// Ensure the path is not empty and is a directory.
		if ( empty( $path ) || ! is_dir( $path ) ) {
			throw new \Exception( 'Invalid or empty directory path.' );
		}

		// Normalize the paths to remove trailing slashes.
		$normalized_path       = rtrim( $path, DIRECTORY_SEPARATOR );
		$normalized_assert_dir = rtrim( $assert_inside_dir, DIRECTORY_SEPARATOR );

		// Check if the path is inside the assert directory.
		if ( $normalized_assert_dir && strpos( $normalized_path, $normalized_assert_dir ) !== 0 ) {
			throw new \Exception( 'The path must be inside the specified assert directory.' );
		}

		// Check the depth of the path.
		$depth = count( array_filter( explode( DIRECTORY_SEPARATOR, $normalized_path ), 'strlen' ) );

		if ( $depth < $min_depth ) {
			throw new \Exception( 'The depth of the path is not sufficient.' );
		}

		// Delete the directory.
		$filesystem->remove( $normalized_path );
	}
}
