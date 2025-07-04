<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use finfo;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\Lib\FilePathResolver;

/**
 * ZIP Extraction Endpoint (hardened)
 *
 *  • Rejects Zip‑Slip, symlinks, device files
 *  • Caps file count, ratio & total uncompressed size
 *  • Validates MIME & size of downloaded archive
 */
class ZipExtractionEndpoint extends AbstractEndpoint {

	/* --------------------------------------------------------------------
	 *  Configuration ‑ adjust to your policy
	 * ------------------------------------------------------------------*/
	private const MAX_ARCHIVE_SIZE_BYTES          = 250 * 1024 * 1024;   // 250 MB
	private const MAX_UNCOMPRESSED_TOTAL_BYTES    = 2_000_000_000;       // 2 GB
	private const MAX_COMPRESSION_RATIO           = 50;                  // 50×
	private const MAX_ENTRIES                     = 20_000;

	/* ------------------------------------------------------------------ */
	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/extract-zip';
	}

	public function handle( array $input ): void {

		$this->log_info( 'Starting ZIP extraction endpoint', [
			'input_keys' => array_keys( $input )
		] );

		NodeResponse::mark( 'parameter_validation' );
		if ( ! $this->validateParameters( $input ) ) {
			return;
		}

		$zipUrl        = $input['zip_url'];
		$extractSubdir = $input['extract_subdir'];

		NodeResponse::mark( 'path_sanitization' );
		$sanitizedSubdir = $this->sanitizeExtractionPath( $extractSubdir );
		if ( $sanitizedSubdir === null ) {
			return;
		}

		$tempBase  = sys_get_temp_dir();
		$extractTo = $tempBase . '/' . $sanitizedSubdir;

		NodeResponse::mark( 'security_validation' );
		if ( ! $this->validateExtractionSecurity( $tempBase, $extractTo, $extractSubdir, $sanitizedSubdir ) ) {
			return;
		}

		try {
			NodeResponse::mark( 'extraction_start' );
			$this->performExtraction( $zipUrl, $extractTo, $input );
		} catch ( Exception $e ) {
			$this->handleExtractionError( $e );
		}
	}

	/* =====================================================================
	 *  Parameter & path validation helpers (unchanged)
	 * ===================================================================*/
	private function validateParameters( array $input ): bool {
		foreach ( [ 'zip_url', 'extract_subdir' ] as $k ) {
			if ( empty( $input[ $k ] ) ) {
				$this->log_error( "Missing $k parameter" );
				http_response_code( 400 );
				NodeResponse::error( "Missing $k parameter" );

				return false;
			}
		}

		return true;
	}

	private function sanitizeExtractionPath( string $extractSubdir ): ?string {
		if ( str_contains( $extractSubdir, '..' ) || str_contains( $extractSubdir, "\0" ) ) {
			$this->log_error( 'Illegal characters in extract_subdir', [ 'extract_subdir' => $extractSubdir ] );
			http_response_code( 400 );
			NodeResponse::error( 'Illegal characters in extract_subdir' );

			return null;
		}

		$sanitized = trim( str_replace( '\\', '/', $extractSubdir ), '/' );

		if ( ! preg_match( '/^[a-zA-Z0-9\/_\-\.]+$/', $sanitized ) ) {
			$this->log_error( 'extract_subdir contains invalid characters', [ 'extract_subdir' => $sanitized ] );
			http_response_code( 400 );
			NodeResponse::error( 'extract_subdir contains invalid characters' );

			return null;
		}
		if ( empty( $sanitized ) || pathinfo( $sanitized, PATHINFO_EXTENSION ) ) {
			http_response_code( 400 );
			NodeResponse::error( 'extract_subdir must be a directory path' );

			return null;
		}

		return $sanitized;
	}

	private function validateExtractionSecurity(
		string $tempBase,
		string $extractTo,
		string $originalSubdir,
		string $sanitizedSubdir
	): bool {

		$realTempBase = realpath( $tempBase );
		if ( $realTempBase === false ) {
			http_response_code( 500 );
			NodeResponse::error( 'Failed to resolve temp directory path' );

			return false;
		}

		$extractParent = dirname( $extractTo );
		if ( ! is_dir( $extractParent ) && ! mkdir( $extractParent, 0777, true ) ) {
			http_response_code( 500 );
			NodeResponse::error( 'Failed to create parent directory' );

			return false;
		}

		$realExtractParent = realpath( $extractParent );
		if ( $realExtractParent === false || str_starts_with( $realExtractParent, $realTempBase ) === false ) {
			http_response_code( 400 );
			NodeResponse::error( 'Directory traversal attempt detected' );

			return false;
		}

		return true;
	}

	/* =====================================================================
	 *  Main flow
	 * ===================================================================*/
	private function performExtraction( string $zipUrl, string $extractTo, array $params ): void {

		$this->prepareExtractionDirectory( $extractTo );

		$zipPath = $this->downloadZipFile( $zipUrl, $extractTo );

		$extractionResult = $this->extractZipFile( $zipPath, $extractTo );

		unlink( $zipPath );

		if ( $extractionResult['file_count'] === 0 ) {
			http_response_code( 422 );
			NodeResponse::error( 'ZIP extraction failed – no files extracted', [ 'files_extracted' => 0 ] );

			return;
		}

		touch( $extractTo . '/.analyzed' );

		$actualExtractPath = $this->findWordPressExtensionDirectory( $extractTo );

		// NEW: collect the roots and persist context for later prompts
		$roots = $this->getWorkspaceRoots($extractTo);
		$this->writeContextFile($extractTo, $roots);

		$this->log_info('Workspace roots detected', ['roots' => $roots]);

		$this->sendUnifiedExtractionResponse( $actualExtractPath, $extractionResult['file_count'], $params );
	}

	/* --------------------------------------------------------------------
	 *  Directory preparation
	 * ------------------------------------------------------------------*/
	private function prepareExtractionDirectory( string $extractTo ): void {

		if ( is_dir( $extractTo ) ) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $extractTo, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $iterator as $file ) {
				$file->isDir() ? rmdir( $file->getRealPath() ) : unlink( $file->getRealPath() );
			}
		}
		if ( ! is_dir( $extractTo ) && ! mkdir( $extractTo, 0777, true ) ) {
			throw new Exception( 'Failed to create extraction directory' );
		}
	}

	/* --------------------------------------------------------------------
	 *  Download + upfront validation
	 * ------------------------------------------------------------------*/
	private function downloadZipFile( string $zipUrl, string $extractTo ): string {

		$zipPath = $extractTo . '/archive.zip';

		$ch = curl_init( $zipUrl );
		$fp = fopen( $zipPath, 'wb' );
		curl_setopt_array( $ch, [
			CURLOPT_FILE           => $fp,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT        => 300,
		] );
		curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
		fclose( $fp );

		if ( $httpCode !== 200 ) {
			throw new Exception( "Failed to download ZIP (HTTP $httpCode)" );
		}

		$stat = stat( $zipPath );
		if ( $stat['size'] > self::MAX_ARCHIVE_SIZE_BYTES ) {
			throw new Exception( 'Archive exceeds maximum allowed size' );
		}

		$finfo = new finfo( FILEINFO_MIME_TYPE );
		$mime  = $finfo->file( $zipPath );
		if ( $mime !== 'application/zip' && $mime !== 'application/x-zip' && $mime !== 'application/octet-stream' ) {
			throw new Exception( "Invalid MIME type for ZIP: $mime" );
		}

		return $zipPath;
	}

	/* --------------------------------------------------------------------
	 *  Extraction (SECURE)
	 * ------------------------------------------------------------------*/
	private function extractZipFile( string $zipPath, string $extractTo ): array {

		$stats = $this->secureExtractZip( $zipPath, $extractTo );

		$this->log_info( 'ZIP extracted securely', $stats );

		return $stats;
	}

	/**
	 * Secure extraction with per‑entry validation.
	 *
	 * @return array{file_count:int,extract_time:float}
	 * @throws Exception
	 */
	private function secureExtractZip( string $zipPath, string $extractRoot ): array {

		$zip = new ZipArchive();
		if ( $zip->open( $zipPath ) !== true ) {
			throw new Exception( 'Failed to open ZIP file' );
		}

		if ( $zip->numFiles > self::MAX_ENTRIES ) {
			$zip->close();
			throw new Exception( 'Archive contains too many entries' );
		}

		$totalUncompressed = 0;
		$start             = microtime( true );

		for ( $i = 0; $i < $zip->numFiles; $i ++ ) {

			$stat = $zip->statIndex( $i );
			$name = $stat['name'];

			// Normalise & validate path
			$targetPath = $this->canonicalisePath( $extractRoot, $name );

			// Reject directory traversal
			if ( str_starts_with( $targetPath, $extractRoot ) === false ) {
				$zip->close();
				throw new Exception( "Zip entry attempts path traversal: {$name}" );
			}

			// Reject symlinks / special files
			if ( ( $stat['external_attributes'] >> 16 ) & 0xA000 ) { // 0xA000 = symlink
				$zip->close();
				throw new Exception( "Zip entry is a symlink: {$name}" );
			}

			// Compression‑ratio guard
			if ( $stat['size'] > 0 && ( $stat['comp_size'] > 0 ) ) {
				$ratio = $stat['size'] / $stat['comp_size'];
				if ( $ratio > self::MAX_COMPRESSION_RATIO ) {
					$zip->close();
					throw new Exception( "Excessive compression ratio on {$name}" );
				}
			}

			$totalUncompressed += $stat['size'];
			if ( $totalUncompressed > self::MAX_UNCOMPRESSED_TOTAL_BYTES ) {
				$zip->close();
				throw new Exception( 'Total uncompressed size limit exceeded' );
			}

			// Ensure directory exists
			$dir = dirname( $targetPath );
			if ( ! is_dir( $dir ) && ! mkdir( $dir, 0777, true ) ) {
				$zip->close();
				throw new Exception( "Failed to create directory: $dir" );
			}

			if ( substr( $name, -1 ) === '/' ) {
				// Directory entry
				if ( ! is_dir( $targetPath ) && ! mkdir( $targetPath, 0777, true ) ) {
					$zip->close();
					throw new Exception( "Failed to create directory: $targetPath" );
				}
				continue;
			}

			$stream = $zip->getStream( $name );
			if ( ! $stream ) {
				$zip->close();
				throw new Exception( "Failed to read entry: {$name}" );
			}

			$out = fopen( $targetPath, 'wb' );
			if ( ! $out ) {
				$zip->close();
				throw new Exception( "Cannot write file: {$targetPath}" );
			}

			stream_copy_to_stream( $stream, $out );
			fclose( $stream );
			fclose( $out );
			chmod( $targetPath, 0644 );
		}

		$zip->close();

		return [
			'file_count'   => $zip->numFiles,
			'extract_time' => microtime( true ) - $start
		];
	}

	/**
	 * Resolve $entryPath against $root securely (no "..", no back‑slashes).
	 */
	private function canonicalisePath( string $root, string $entryPath ): string {
		$entryPath = str_replace( ['\\', "\0"], '/', $entryPath );
		$entryPath = preg_replace( '#/+#', '/', $entryPath );
		$parts     = [];
		foreach ( explode( '/', $entryPath ) as $part ) {
			if ( $part === '' || $part === '.' ) {
				continue;
			}
			if ( $part === '..' ) {
				array_pop( $parts );
				continue;
			}
			$parts[] = $part;
		}

		return rtrim( $root, '/' ) . '/' . implode( '/', $parts );
	}

	/**
	 * Send unified extraction response with both stats and file list
	 *
	 * @param string $actualExtractPath Actual extraction path
	 * @param int $fileCount Number of files extracted
	 * @param array $params Request parameters
	 */
	private function sendUnifiedExtractionResponse( string $actualExtractPath, int $fileCount, array $params ): void {
		$filePattern     = $params['config']['file_pattern'] ?? '*.php';
		$resolver        = new FilePathResolver( $actualExtractPath );
		$discoveredFiles = [];
		$phpFiles        = [];
		$totalFiles      = 0;

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $actualExtractPath, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$totalFiles ++;

				// Check if it's a PHP file for basic stats
				if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
					$phpFiles[] = str_replace( $actualExtractPath . '/', '', $file->getPathname() );
				}

				// Check if it matches the file pattern for discovery
				if ( fnmatch( $filePattern, $file->getFilename() ) ) {
					$relativePath = $resolver->toRelative( $file->getPathname() );
					$priority     = $this->calculateSecurityPriority( $relativePath );

					$discoveredFiles[] = [
						'path'     => $relativePath,
						'size'     => $file->getSize(),
						'lines'    => substr_count( file_get_contents( $file ), "\n" ) + 1,
						'priority' => $priority
					];
				}
			}
		}

		// Sort discovered files by priority (security-sensitive files first)
		usort( $discoveredFiles, fn( $a, $b ) => $b['priority'] - $a['priority'] );

		if ( empty( $phpFiles ) ) {
			$this->log_warning( 'ZIP extracted successfully but no PHP files found', [
				'files_extracted' => $fileCount,
				'actual_path'     => $actualExtractPath
			] );
		}

		$this->log_info( 'PHP files detected', [
			'php_files' => array_slice( $phpFiles, 0, 10 ),
			'count'     => count( $phpFiles )
		] );

		// Send unified response with both stats and file discovery data
		NodeResponse::success( [
			'extract_path'     => $actualExtractPath,
			'session_id'       => $params['session_id'] ?? md5( $actualExtractPath ),
			'files_discovered' => $discoveredFiles,
			'stats'            => [
				'files_extracted'        => $fileCount,
				'php_files_found'        => count( $phpFiles ),
				'total_files'            => $totalFiles,
				'files_matching_pattern' => count( $discoveredFiles )
			]
		] );
	}


	/**
	 * Handle extraction error
	 *
	 * @param Exception $e Exception that occurred
	 */
	private function handleExtractionError( Exception $e ): void {
		$this->log_error( 'ZIP extraction failed: ' . $e->getMessage() );

		http_response_code( 500 );
		NodeResponse::error( 'Extraction failed', 500, [ 'message' => $e->getMessage() ] );
	}

	/**
	 * Find the actual WordPress plugin/theme directory after extraction
	 *
	 * @param string $extractBase The base extraction directory
	 *
	 * @return string The actual plugin/theme directory path
	 */
	private function findWordPressExtensionDirectory( string $extractBase ): string {
		// First check if the base directory itself is a plugin/theme
		$structure = $this->detectWordPressStructure( $extractBase );
		if ( $structure['is_plugin'] || $structure['is_theme'] ) {
			return $extractBase;
		}

		// Otherwise, look for a subdirectory that contains the plugin/theme
		$items = scandir( $extractBase );
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' || $item === '.analyzed' ) {
				continue;
			}

			$fullPath = $extractBase . '/' . $item;
			if ( is_dir( $fullPath ) ) {
				$subStructure = $this->detectWordPressStructure( $fullPath );
				if ( $subStructure['is_plugin'] || $subStructure['is_theme'] ) {
					$this->log_info( 'Found WordPress extension in subdirectory', [
						'subdirectory' => $item,
						'type'         => $subStructure['type'],
						'main_file'    => $subStructure['main_file']
					] );

					return $fullPath;
				}
			}
		}

		// If no plugin/theme structure found, return the base directory
		$this->log_warning( 'No WordPress plugin or theme structure detected, using base directory' );

		return $extractBase;
	}

	/**
	 * Detect WordPress plugin or theme structure
	 *
	 * @param string $directory Directory to check
	 *
	 * @return array Structure information
	 */
	private function detectWordPressStructure( string $directory ): array {
		$result = [
			'is_plugin' => false,
			'is_theme'  => false,
			'type'      => 'unknown',
			'main_file' => null
		];

		// Check for WordPress plugin
		$phpFiles = glob( $directory . '/*.php' );
		foreach ( $phpFiles as $file ) {
			$content = file_get_contents( $file );
			if ( strpos( $content, 'Plugin Name:' ) !== false ) {
				$result['is_plugin'] = true;
				$result['type']      = 'plugin';
				$result['main_file'] = basename( $file );

				return $result;
			}
		}

		// Check for WordPress theme
		if ( file_exists( $directory . '/style.css' ) ) {
			$styleContent = file_get_contents( $directory . '/style.css' );
			if ( strpos( $styleContent, 'Theme Name:' ) !== false ) {
				$result['is_theme']  = true;
				$result['type']      = 'theme';
				$result['main_file'] = 'style.css';

				return $result;
			}
		}

		// Check for theme with functions.php
		if ( file_exists( $directory . '/functions.php' ) && file_exists( $directory . '/index.php' ) ) {
			$result['is_theme']  = true;
			$result['type']      = 'theme';
			$result['main_file'] = 'functions.php';

			return $result;
		}

		return $result;
	}

	/**
	 * Calculate security priority for a file path
	 *
	 * @param string $filePath File path
	 *
	 * @return int Priority score
	 */
	private function calculateSecurityPriority( string $filePath ): int {
		$priorityPatterns = [
			'/ajax|admin-ajax/i'     => 100,
			'/admin\//i'             => 90,
			'/api|rest/i'            => 85,
			'/callback|webhook/i'    => 80,
			'/upload|download/i'     => 75,
			'/auth|login|register/i' => 70,
			'/payment|checkout/i'    => 65,
			'/includes\//i'          => 50,
			'/template/i'            => 30,
			'/assets|css|js/i'       => 10
		];

		foreach ( $priorityPatterns as $pattern => $score ) {
			if ( preg_match( $pattern, $filePath ) ) {
				return $score;
			}
		}

		return 40; // Default priority
	}

	/* ------------------------------------------------------ New helpers --- */

	/** Return every top‑level directory in the workspace (one level deep). */
	private function getWorkspaceRoots(string $base): array {
		$roots = [];
		foreach (scandir($base) as $item) {
			if ($item === '.' || $item === '..') continue;
			if (is_dir($base.'/'.$item)) {
				$roots[] = rtrim($item, '/').'/';              // keep trailing "/"
			}
		}
		sort($roots);
		return $roots;
	}

	/** Write a small JSON file the prompt‑builder can read later. */
	private function writeContextFile(string $base, array $roots): void {
		$ctx = [
			'contract_version' => 3,
			'roots'            => $roots,
			'generated_at'     => date(DATE_ATOM),
		];
		file_put_contents($base.'/.ctx.json', json_encode($ctx, JSON_PRETTY_PRINT));
	}
}
