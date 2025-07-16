<?php

namespace QIT_AI_Webserver\Lib;

/**
 * File Path Contract
 *
 * All file paths in the system follow these rules:
 * 1. ALWAYS relative to the project root directory
 * 2. NEVER include absolute path prefixes
 * 3. Use forward slashes (/) even on Windows
 * 4. No leading slash
 *
 * Example:
 * - Actual file: /project/wp-content/plugins/my-plugin/classes/FortisApi.php
 * - Project root: /project
 * - Relative path: wp-content/plugins/my-plugin/classes/FortisApi.php
 */

/**
 * File path resolver to ensure consistent path handling
 * Standalone copy for the node to avoid manager dependencies
 */
class FilePathResolver {
	private string $extract_path;
	private string $root_dir;
	private ToolPathGuard $g;

	public function __construct( string $extract_path, string $sut_dir = '' ) {
		$this->extract_path = rtrim( $extract_path, '/\\' );
		$this->root_dir     = $this->extract_path;
		$this->g            = new ToolPathGuard( $this->extract_path, $sut_dir );
	}

	/**  Convert *user* path → absolute canon path or throw  */
	public function to_absolute( string $user_path ): string {
		// For file operations, try to resolve using both WP-relative and SUT-relative paths
		try {
			return $this->g->resolve( $user_path );
		} catch ( \RuntimeException $e ) {
			// Fallback to the old method for non-file paths or when resolve fails
			$rel = $this->canon_relative( $user_path );     // throws if illegal

			// Always use the project root (extract_path) as the base
			if ( $rel === '.' || $rel === '' ) {
				return $this->extract_path;
			}

			return $this->extract_path . '/' . $rel;
		}
	}

	/**  Return *relative*, canonical path inside workspace  */
	public function canon_relative( string $user_path ): string {
		return $this->g->normalise( $user_path );      // may throw
	}

	/**
	 * Convert an absolute path to relative
	 */
	public function to_relative( string $absolute_path ): string {
		$absolute_path = $this->normalize( $absolute_path );
		$extract_path  = $this->normalize( $this->extract_path );

		// Remove extract path prefix
		if ( strpos( $absolute_path, $extract_path ) === 0 ) {
			$relative = substr( $absolute_path, strlen( $extract_path ) );

			return ltrim( $relative, '/' );
		}

		// Path is already relative
		return ltrim( $absolute_path, '/' );
	}

	/**
	 * Normalize path separators and remove redundant parts
	 */
	public function normalize( string $path ): string {
		// Convert backslashes to forward slashes
		$path = str_replace( '\\', '/', $path );

		// Remove duplicate slashes
		$path = preg_replace( '#/+#', '/', $path );

		// Remove trailing slash
		return rtrim( $path, '/' );
	}

	/**
	 * Check if a file exists (using relative path)
	 */
	public function file_exists( string $relative_path ): bool {
		return file_exists( $this->to_absolute( $relative_path ) );
	}

	/**
	 * Read file contents (using relative path)
	 */
	public function read_file( string $relative_path ): string {
		$absolute_path = $this->to_absolute( $relative_path );
		if ( ! file_exists( $absolute_path ) ) {
			DebugLogger::log( 'read_file_error', [
				'reason'        => 'file_not_found',
				'relative_path' => $relative_path,
				'absolute_path' => $absolute_path,
				'work_dir'      => $this->extract_path,
				'work_dir_tree' => DebugLogger::dir_tree( $this->extract_path ),
			] );
			throw new \RuntimeException( "File not found: $relative_path" );
		}

		$content = file_get_contents( $absolute_path );
		if ( $content === '' || filesize( $absolute_path ) === 0 ) {
			DebugLogger::log( 'read_file_error', [
				'reason'        => 'empty_file',
				'relative_path' => $relative_path,
				'absolute_path' => $absolute_path,
				'work_dir'      => $this->extract_path,
				'dir_tree'      => DebugLogger::dir_tree( dirname( $absolute_path ) ),
			] );
		}

		return $content;
	}

	/**
	 * Get file info (using relative path)
	 * @return array<string, mixed>
	 */
	public function get_file_info( string $relative_path ): array {
		$absolute_path = $this->to_absolute( $relative_path );
		if ( ! file_exists( $absolute_path ) ) {
			throw new \RuntimeException( "File not found: $relative_path" );
		}

		$content = file_get_contents( $absolute_path );

		return [
			'path'          => $relative_path,
			'absolute_path' => $absolute_path,
			'size'          => filesize( $absolute_path ),
			'lines'         => substr_count( $content, "\n" ) + 1,
			'extension'     => pathinfo( $relative_path, PATHINFO_EXTENSION ),
		];
	}

	/**
	 * Convert absolute path to relative path
	 */
	public function toRelative( string $absolute_path ): string {
		// Remove the root directory from the absolute path
		$relative = str_replace( $this->root_dir, '', $absolute_path );
		// Remove leading slash
		return ltrim( $relative, '/' );
	}
}
