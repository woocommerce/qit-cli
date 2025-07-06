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

		$zipUrl = $input['zip_url'];
		$sessionId = $input['session_id'] ?? md5(uniqid());

		NodeResponse::mark( 'path_preparation' );
		$tempBase = sys_get_temp_dir();
		$extractTo = $tempBase . '/qit-code-analysis-' . $sessionId;

		NodeResponse::mark( 'security_validation' );
		if ( ! $this->validateExtractionSecurity( $tempBase, $extractTo ) ) {
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
		foreach ( [ 'zip_url', 'session_id' ] as $k ) {
			if ( empty( $input[ $k ] ) ) {
				$this->log_error( "Missing $k parameter" );
				http_response_code( 400 );
				NodeResponse::error( "Missing $k parameter" );

				return false;
			}
		}

		return true;
	}


	private function validateExtractionSecurity(
		string $tempBase,
		string $extractTo
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
	 *  Precondition validation
	 * ===================================================================*/
	private function validatePrecondition(string $extractTo, array $config): void
	{
		$requires = $config['requires'] ?? null;

		// 1. new_extraction_dir  ─────────────────────────────────────────────
		if ($requires === 'new_extraction_dir') {
			if (is_dir($extractTo)) {
				throw new Exception(
					"Pre‑condition failed (new_extraction_dir): "
				  . "$extractTo already exists"
				);
			}
			return;                         // nothing else to check
		}

		// 2. wordpress_on_extraction_dir  ────────────────────────────────────
		if ($requires === 'wordpress_on_extraction_dir') {
			if (!is_file($extractTo.'/wp-includes/version.php') ||
				!is_file($extractTo.'/wp-admin/admin.php')) {
				throw new Exception(
					"Pre‑condition failed (wordpress_on_extraction_dir): "
				  . "WordPress core not found in $extractTo"
				);
			}
		}
	}

	/* =====================================================================
	 *  Main flow
	 * ===================================================================*/
	private function performExtraction( string $zipUrl, string $extractTo, array $params ): void {

		$sessionId = $params['session_id'] ?? md5($extractTo);
		$targetSubdir = $params['config']['target_subdir'] ?? null;

		/* Workspace root is decided here, not by the Manager */
		$workspaceRoot = $extractTo;

		$extractRoot = $targetSubdir
			? rtrim($workspaceRoot, '/') . '/' . trim($targetSubdir, '/')
			: $workspaceRoot;

		$this->validatePrecondition($workspaceRoot, $params['config'] ?? []);

		/* ensure the directory exists */
		if (!is_dir($extractRoot) && !mkdir($extractRoot, 0777, true)) {
			throw new Exception("Failed to create extraction directory: $extractRoot");
		}

		$zipPath = $this->downloadZipFile( $zipUrl, $workspaceRoot );

		$extractionResult = $this->extractZipFile( $zipPath, $extractRoot, $params );

		unlink( $zipPath );

		if ( $extractionResult['file_count'] === 0 ) {
			http_response_code( 422 );
			NodeResponse::error( 'ZIP extraction failed – no files extracted', [ 'files_extracted' => 0 ] );

			return;
		}

		touch( $workspaceRoot . '/.analyzed' );

		// Create component manifest
		$componentType = $params['config']['type'] ?? 'unknown';
		$structure = $this->detectWordPressStructure( $extractRoot );

		// Determine component details
		$depType = 'unknown';
		$depSlug = basename( $extractRoot );

		if ( $structure['is_plugin'] ) {
			$depType = 'plugin';
		} elseif ( $structure['is_theme'] ) {
			$depType = 'theme';
		} elseif ( $componentType === 'wordpress_core' ) {
			$depType = 'wordpress_core';
		} elseif ( strpos( $depSlug, 'dependency' ) !== false ) {
			$depType = strpos( $depSlug, 'premium' ) !== false ? 'dependency_premium' : 'dependency_free';
		}

		$manifest = [
			'type'   => $depType,
			'slug'   => $depSlug,
			'is_sut' => $componentType === 'sut'
		];

		// Return the workspace root as extract_path, with proper prefix filtering
		$this->sendUnifiedExtractionResponse( $workspaceRoot, $extractionResult['file_count'], $params, $manifest );
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
	private function extractZipFile( string $zipPath, string $extractTo, array $params = [] ): array {

		$stats = $this->secureExtractZip( $zipPath, $extractTo, $params );

		$this->log_info( 'ZIP extracted securely', $stats );

		return $stats;
	}

	/**
	 * Secure extraction with per‑entry validation.
	 *
	 * @return array{file_count:int,extract_time:float}
	 * @throws Exception
	 */
	private function secureExtractZip( string $zipPath, string $extractRoot, array $params = [] ): array {

		$zip = new ZipArchive();
		if ( $zip->open( $zipPath ) !== true ) {
			throw new Exception( 'Failed to open ZIP file' );
		}

		if ( $zip->numFiles > self::MAX_ENTRIES ) {
			$zip->close();
			throw new Exception( 'Archive contains too many entries' );
		}

		$totalUncompressed = 0;
		$extractedFileCount = 0; // Track actual extracted files, not directories
		$start             = microtime( true );

		for ( $i = 0; $i < $zip->numFiles; $i ++ ) {

			$stat = $zip->statIndex( $i );
			$originalName = $stat['name'];
			$name = $originalName;

 		// Strip "wordpress/" prefix for WordPress core extraction
 		$isWordPressCore = ($params['config']['type'] ?? '') === 'wordpress_core';
		if ($isWordPressCore && str_starts_with($name, 'wordpress/')) {
			$name = substr($name, 10); // Remove "wordpress/" (10 characters)
			// Skip if the entry becomes empty after stripping the prefix
			if (empty($name)) {
				continue;
			}
		}

		// Strip extra nested directory for plugins/themes with redundant parent directories
		$isPlugin = in_array($params['config']['type'] ?? '', ['sut', 'plugin', 'theme', 'premium_dependency', 'free_dependency']);
		if ($isPlugin && !$isWordPressCore) {
			// Get the expected plugin/theme name from target_subdir
			$targetSubdir = $params['config']['target_subdir'] ?? '';
			if (!empty($targetSubdir)) {
				// Extract the plugin/theme name from target_subdir (e.g., "wp-content/plugins/fortis-for-woocommerce" -> "fortis-for-woocommerce")
				$expectedName = basename($targetSubdir);

				// Check if the entry starts with the expected plugin name followed by a slash
				if (str_starts_with($name, $expectedName . '/')) {
					$name = substr($name, strlen($expectedName) + 1); // Remove "plugin-name/" prefix
					// Skip if the entry becomes empty after stripping the prefix
					if (empty($name)) {
						continue;
					}
				}
			}
		}

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
			continue; // Don't count directories as extracted files
		}

		$stream = $zip->getStream( $originalName );
		if ( ! $stream ) {
			$zip->close();
			throw new Exception( "Failed to read entry: {$originalName}" );
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

		$extractedFileCount++; // Only count actual files that were extracted
		}

		$zip->close();

		return [
			'file_count'   => $extractedFileCount, // Return actual extracted file count
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
	 * @param array $manifest Optional component manifest
	 */
	private function sendUnifiedExtractionResponse( string $actualExtractPath, int $fileCount, array $params, array $manifest = [] ): void {
		$listFiles = $params['config']['list_files'] ?? true;   // ★ new flag
		$filePattern     = $params['config']['file_pattern'] ?? '*.php';
		$resolver        = new FilePathResolver( $actualExtractPath );
		$discoveredFiles = [];
		$phpFiles        = [];
		$totalFiles      = 0;

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $actualExtractPath, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		// Pre-calculate SUT directory prefix for filtering if this is a SUT component
		$sutPrefix = null;
		if (($params['config']['type'] ?? '') === 'sut') {
			$targetSubdir = $params['config']['target_subdir'] ?? null;
			$sutPrefix = $targetSubdir ? trim($targetSubdir, '/') . '/' : null;
		}

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$relativePath = $resolver->toRelative( $file->getPathname() );

				// Filter files for SUT components - only include files under the SUT directory
				if ($sutPrefix !== null && !str_starts_with($relativePath, $sutPrefix)) {
					continue; // Skip files not under the SUT directory
				}

				$totalFiles ++;

				// Check if it's a PHP file for basic stats
				if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
					$phpFiles[] = str_replace( $actualExtractPath . '/', '', $file->getPathname() );
				}

				// Always collect stats, but only build the heavy list if requested
				if ( $listFiles && fnmatch( $filePattern, $file->getFilename() ) ) {
					$priority     = $this->calculateSecurityPriority( $relativePath );

					// Calculate SHA-1 for the file
					$sha1 = sha1_file( $file->getPathname() );

					$discoveredFiles[] = [
						'path'     => $relativePath,
						'size'     => $file->getSize(),
						'lines'    => substr_count( file_get_contents( $file ), "\n" ) + 1,
						'priority' => $priority,
						'sha1'     => $sha1
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
		$response = [
			'extract_path'     => $actualExtractPath,
			'session_id'       => $params['session_id'] ?? md5( $actualExtractPath ),
			'files_discovered' => $listFiles ? $discoveredFiles : [],
			'stats'            => [
				'files_extracted'        => $fileCount,
				'php_files_found'        => count( $phpFiles ),
				'total_files'            => $totalFiles,
				'files_matching_pattern' => count( $discoveredFiles )
			]
		];

		// Add component manifest if provided
		if ( ! empty( $manifest ) ) {
			$response['component'] = $manifest;
		}

		NodeResponse::success( $response );
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
		$filePath = $base.'/.ctx.json';
		$this->log_info('Attempting to write .ctx.json file', [
			'file_path' => $filePath,
			'base_directory' => $base,
			'roots' => $roots,
			'directory_exists' => is_dir($base),
			'directory_writable' => is_writable($base)
		]);

		$result = file_put_contents($filePath, json_encode($ctx, JSON_PRETTY_PRINT));

		if ($result === false) {
			$this->log_error('Failed to write .ctx.json file', [
				'file_path' => $filePath,
				'error' => error_get_last()
			]);
		} else {
			$this->log_info('.ctx.json file written successfully', [
				'file_path' => $filePath,
				'bytes_written' => $result,
				'file_exists' => file_exists($filePath)
			]);
		}
	}



}
