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
	private string $extractPath;
	private ToolPathGuard $g;

	public function __construct( string $extractPath ) {
		$this->extractPath = rtrim( $extractPath, '/\\' );
		$this->g           = new ToolPathGuard( $this->extractPath );
	}

	/**  Convert *user* path → absolute canon path or throw  */
	public function toAbsolute( string $userPath ): string {
		$rel = $this->canonRelative( $userPath );     // throws if illegal

		// Always use the project root (extractPath) as the base
		if ( $rel === '.' || $rel === '' ) {
			return $this->extractPath;
		}

		return $this->extractPath . '/' . $rel;
	}

	/**  Return *relative*, canonical path inside workspace  */
	public function canonRelative( string $userPath ): string {
		return $this->g->normalise( $userPath );      // may throw
	}

	/**
	 * Convert an absolute path to relative
	 */
	public function toRelative( string $absolutePath ): string {
		$absolutePath = $this->normalize( $absolutePath );
		$extractPath  = $this->normalize( $this->extractPath );

		// Remove extract path prefix
		if ( strpos( $absolutePath, $extractPath ) === 0 ) {
			$relative = substr( $absolutePath, strlen( $extractPath ) );

			return ltrim( $relative, '/' );
		}

		// Path is already relative
		return ltrim( $absolutePath, '/' );
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
	public function fileExists( string $relativePath ): bool {
		return file_exists( $this->toAbsolute( $relativePath ) );
	}

	/**
	 * Read file contents (using relative path)
	 */
	public function readFile( string $relativePath ): string {
		$absolutePath = $this->toAbsolute( $relativePath );
		if ( ! file_exists( $absolutePath ) ) {
			DebugLogger::log( 'read_file_error', [
				'reason'        => 'file_not_found',
				'relative_path' => $relativePath,
				'absolute_path' => $absolutePath,
				'work_dir'      => $this->extractPath,
				'work_dir_tree' => DebugLogger::dirTree( $this->extractPath ),
			] );
			throw new \RuntimeException( "File not found: $relativePath" );
		}

		$content = file_get_contents( $absolutePath );
		if ( $content === '' || filesize( $absolutePath ) === 0 ) {
			DebugLogger::log( 'read_file_error', [
				'reason'        => 'empty_file',
				'relative_path' => $relativePath,
				'absolute_path' => $absolutePath,
				'work_dir'      => $this->extractPath,
				'dir_tree'      => DebugLogger::dirTree( dirname( $absolutePath ) ),
			] );
		}

		return $content;
	}

	/**
	 * Get file info (using relative path)
	 */
	public function getFileInfo( string $relativePath ): array {
		$absolutePath = $this->toAbsolute( $relativePath );
		if ( ! file_exists( $absolutePath ) ) {
			throw new \RuntimeException( "File not found: $relativePath" );
		}

		$content = file_get_contents( $absolutePath );

		return [
			'path'          => $relativePath,
			'absolute_path' => $absolutePath,
			'size'          => filesize( $absolutePath ),
			'lines'         => substr_count( $content, "\n" ) + 1,
			'extension'     => pathinfo( $relativePath, PATHINFO_EXTENSION )
		];
	}

}
