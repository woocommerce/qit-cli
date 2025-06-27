<?php

namespace QIT_AI_Webserver\Handlers;

use Exception;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\Lib\FilePathResolver;

/**
 * ZIP Extraction Handler
 *
 * Handles ZIP file extraction for WordPress plugins/themes analysis.
 * Provides secure extraction with path validation and WordPress structure detection.
 */
class ZipExtractionHandler extends AbstractHandler {

	/**
	 * Handle ZIP extraction request
	 *
	 * @param array $input Request input data
	 *
	 * @return void Outputs JSON response
	 */
	public function handle( array $input ): void {
		$this->log_info( 'Starting ZIP extraction handler', [
			'input_keys'     => array_keys( $input ),
			'has_zip_url'    => isset( $input['zip_url'] ),
			'has_session_id' => isset( $input['session_id'] )
		] );

		// Parse the input
		$params = json_decode( $input['prompt'], true );

		// Validate required parameters
		if ( ! $this->validateParameters( $params ) ) {
			return;
		}

		$zipUrl        = $params['zip_url'];
		$extractSubdir = $params['extract_subdir'];

		// Validate and sanitize the extraction subdirectory
		$sanitizedSubdir = $this->sanitizeExtractionPath( $extractSubdir );
		if ( $sanitizedSubdir === null ) {
			return;
		}

		// Prepare extraction paths
		$tempBase  = sys_get_temp_dir();
		$extractTo = $tempBase . '/' . $sanitizedSubdir;

		// Validate extraction path security
		if ( ! $this->validateExtractionSecurity( $tempBase, $extractTo, $params['extract_subdir'], $sanitizedSubdir ) ) {
			return;
		}

		// Perform extraction
		try {
			$this->performExtraction( $zipUrl, $extractTo, $params );
		} catch ( Exception $e ) {
			$this->handleExtractionError( $e );
		}
	}

	/**
	 * Validate required parameters
	 *
	 * @param array|null $params Parsed parameters
	 *
	 * @return bool True if valid
	 */
	private function validateParameters( ?array $params ): bool {
		if ( ! isset( $params['zip_url'] ) || empty( $params['zip_url'] ) ) {
			$this->log_error( 'No ZIP URL provided for extraction' );
			http_response_code( 400 );
			NodeResponse::error( 'Missing zip_url parameter' );

			return false;
		}

		if ( ! isset( $params['extract_subdir'] ) || empty( $params['extract_subdir'] ) ) {
			$this->log_error( 'No extraction subdirectory provided' );
			http_response_code( 400 );
			NodeResponse::error( 'Missing extract_subdir parameter' );

			return false;
		}

		return true;
	}

	/**
	 * Sanitize extraction path
	 *
	 * @param string $extractSubdir Raw extraction subdirectory
	 *
	 * @return string|null Sanitized path or null if invalid
	 */
	private function sanitizeExtractionPath( string $extractSubdir ): ?string {
		// SECURITY: Reject any path containing ".." sequences
		if ( strpos( $extractSubdir, '..' ) !== false ) {
			$this->log_error( 'Directory traversal attempt detected in extract_subdir', [
				'extract_subdir' => $extractSubdir
			] );
			http_response_code( 400 );
			NodeResponse::error( 'Directory traversal sequences (..) are not allowed in extract_subdir.' );

			return null;
		}

		// SECURITY: Reject any path containing null bytes
		if ( strpos( $extractSubdir, "\0" ) !== false ) {
			$this->log_error( 'Null byte injection attempt detected in extract_subdir', [
				'extract_subdir' => $extractSubdir
			] );
			http_response_code( 400 );
			NodeResponse::error( 'Null bytes are not allowed in extract_subdir.' );

			return null;
		}

		// Normalize path separators and remove leading/trailing slashes
		$sanitized = trim( str_replace( '\\', '/', $extractSubdir ), '/' );

		// SECURITY: Validate that extract_subdir only contains safe characters
		if ( ! preg_match( '/^[a-zA-Z0-9\/_\-\.]+$/', $sanitized ) ) {
			$this->log_error( 'extract_subdir contains invalid characters', [
				'extract_subdir' => $sanitized
			] );
			http_response_code( 400 );
			NodeResponse::error( 'extract_subdir contains invalid characters. Only alphanumeric, dash, underscore, slash, and dot are allowed.' );

			return null;
		}

		// SECURITY: Additional check - reject if path is empty after sanitization
		if ( empty( $sanitized ) ) {
			$this->log_error( 'extract_subdir is empty after sanitization' );
			http_response_code( 400 );
			NodeResponse::error( 'extract_subdir cannot be empty.' );

			return null;
		}

		// Validate that extract_to is a directory path, not a file path
		if ( pathinfo( $sanitized, PATHINFO_EXTENSION ) ) {
			$this->log_error( 'extract_subdir appears to be a file path, not a directory path', [
				'extract_subdir' => $sanitized,
				'extension'      => pathinfo( $sanitized, PATHINFO_EXTENSION )
			] );
			http_response_code( 400 );
			NodeResponse::error( 'extract_subdir must be a directory path, not a file path' );

			return null;
		}

		return $sanitized;
	}

	/**
	 * Validate extraction path security
	 *
	 * @param string $tempBase Temp directory base
	 * @param string $extractTo Full extraction path
	 * @param string $originalSubdir Original subdirectory
	 * @param string $sanitizedSubdir Sanitized subdirectory
	 *
	 * @return bool True if secure
	 */
	private function validateExtractionSecurity( string $tempBase, string $extractTo, string $originalSubdir, string $sanitizedSubdir ): bool {
		// SECURITY: Use realpath to resolve any remaining path traversal attempts
		$realTempBase = realpath( $tempBase );
		if ( $realTempBase === false ) {
			$this->log_error( 'Failed to resolve temp directory realpath', [ 'temp_base' => $tempBase ] );
			http_response_code( 500 );
			NodeResponse::error( 'Failed to resolve temp directory path' );

			return false;
		}

		// Create the directory first so we can get its realpath
		$extractParent = dirname( $extractTo );
		if ( ! is_dir( $extractParent ) ) {
			if ( ! mkdir( $extractParent, 0777, true ) ) {
				$this->log_error( 'Failed to create parent directory for security validation', [ 'parent' => $extractParent ] );
				http_response_code( 500 );
				NodeResponse::error( 'Failed to create parent directory' );

				return false;
			}
		}

		$realExtractParent = realpath( $extractParent );
		if ( $realExtractParent === false ) {
			$this->log_error( 'Failed to resolve extraction parent directory realpath', [ 'extract_parent' => $extractParent ] );
			http_response_code( 500 );
			NodeResponse::error( 'Failed to resolve extraction directory path' );

			return false;
		}

		// SECURITY: Ensure the resolved path is within the temp directory sandbox
		if ( strpos( $realExtractParent, $realTempBase ) !== 0 ) {
			$this->log_error( 'Directory traversal attempt detected', [
				'original_subdir'     => $originalSubdir,
				'sanitized_subdir'    => $sanitizedSubdir,
				'extract_to'          => $extractTo,
				'real_temp_base'      => $realTempBase,
				'real_extract_parent' => $realExtractParent
			] );
			http_response_code( 400 );
			NodeResponse::error( 'Directory traversal attempt detected. Path must be within temp directory.' );

			return false;
		}

		$this->log_info( 'Path security validation passed', [
			'original_subdir'     => $originalSubdir,
			'sanitized_subdir'    => $sanitizedSubdir,
			'extract_to'          => $extractTo,
			'real_temp_base'      => $realTempBase,
			'real_extract_parent' => $realExtractParent
		] );

		return true;
	}

	/**
	 * Perform the ZIP extraction
	 *
	 * @param string $zipUrl ZIP file URL
	 * @param string $extractTo Extraction directory
	 * @param array $params Request parameters
	 *
	 * @throws Exception On extraction failure
	 */
	private function performExtraction( string $zipUrl, string $extractTo, array $params ): void {
		$this->log_info( 'Downloading and extracting ZIP', [
			'zip_url'        => substr( $zipUrl, 0, 100 ) . '...',
			'extract_to'     => $extractTo,
			'extract_subdir' => $params['extract_subdir']
		] );

		// Prepare extraction directory
		$this->prepareExtractionDirectory( $extractTo );

		// Download the ZIP file
		$zipPath = $this->downloadZipFile( $zipUrl, $extractTo );

		// Extract using ZipArchive
		$extractionResult = $this->extractZipFile( $zipPath, $extractTo );

		// Remove the ZIP file
		unlink( $zipPath );

		// Validate extraction results
		if ( $extractionResult['file_count'] === 0 ) {
			$this->log_error( 'ZIP extraction completed but no files were extracted', [
				'zip_url'    => substr( $zipUrl, 0, 100 ) . '...',
				'extract_to' => $extractTo
			] );

			http_response_code( 422 );
			NodeResponse::error( 'ZIP extraction failed - no files extracted', [
				'details'         => 'The ZIP file was downloaded and opened successfully but contained no extractable files',
				'files_extracted' => 0
			] );

			return;
		}

		// Create marker file
		touch( $extractTo . '/.analyzed' );

		// Find the actual plugin/theme directory
		$actualExtractPath = $this->findWordPressExtensionDirectory( $extractTo );

		$this->log_info( 'Determined actual extension path', [
			'base_extract_path'   => $extractTo,
			'actual_extract_path' => $actualExtractPath,
			'is_subdirectory'     => ( $actualExtractPath !== $extractTo )
		] );

		// Handle file discovery mode
		if ( $this->isFileDiscoveryMode( $params ) ) {
			$this->handleFileDiscovery( $actualExtractPath, $params );

			return;
		}

		// Regular extraction response
		$this->sendExtractionResponse( $actualExtractPath, $extractionResult['file_count'], $params );
	}

	/**
	 * Prepare extraction directory
	 *
	 * @param string $extractTo Extraction directory path
	 *
	 * @throws Exception On directory preparation failure
	 */
	private function prepareExtractionDirectory( string $extractTo ): void {
		// Handle existing extraction directory
		if ( is_dir( $extractTo ) ) {
			$this->log_info( 'Extraction directory already exists, clearing it to avoid permission issues', [ 'path' => $extractTo ] );

			// Remove all files and subdirectories
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $extractTo, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( $file->isDir() ) {
					if ( ! rmdir( $file->getRealPath() ) ) {
						$this->log_warning( 'Failed to remove directory', [ 'path' => $file->getRealPath() ] );
					}
				} else {
					if ( ! unlink( $file->getRealPath() ) ) {
						$this->log_warning( 'Failed to remove file', [ 'path' => $file->getRealPath() ] );
					}
				}
			}

			$this->log_info( 'Existing directory cleared successfully', [ 'path' => $extractTo ] );
		}

		// Create extraction directory
		if ( ! is_dir( $extractTo ) ) {
			$this->log_info( 'Creating extraction directory and all parent directories', [ 'path' => $extractTo ] );

			if ( ! mkdir( $extractTo, 0777, true ) ) {
				throw new Exception( 'Failed to create extraction directory: ' . $extractTo );
			}

			$this->log_info( 'Extraction directory created successfully', [ 'path' => $extractTo ] );
		}
	}

	/**
	 * Download ZIP file
	 *
	 * @param string $zipUrl ZIP file URL
	 * @param string $extractTo Extraction directory
	 *
	 * @return string Downloaded ZIP file path
	 * @throws Exception On download failure
	 */
	private function downloadZipFile( string $zipUrl, string $extractTo ): string {
		$zipPath = $extractTo . '/plugin.zip';
		$ch      = curl_init( $zipUrl );
		$fp      = fopen( $zipPath, 'wb' );

		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );

		$downloadStart = microtime( true );
		curl_exec( $ch );
		$downloadTime = microtime( true ) - $downloadStart;

		$httpCode     = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$downloadSize = curl_getinfo( $ch, CURLINFO_SIZE_DOWNLOAD );

		curl_close( $ch );
		fclose( $fp );

		if ( $httpCode !== 200 ) {
			throw new Exception( "Failed to download ZIP: HTTP $httpCode" );
		}

		$this->log_info( 'ZIP downloaded successfully', [
			'size_mb'      => round( $downloadSize / ( 1024 * 1024 ), 2 ),
			'time_seconds' => round( $downloadTime, 2 )
		] );

		return $zipPath;
	}

	/**
	 * Extract ZIP file
	 *
	 * @param string $zipPath ZIP file path
	 * @param string $extractTo Extraction directory
	 *
	 * @return array Extraction results
	 * @throws Exception On extraction failure
	 */
	private function extractZipFile( string $zipPath, string $extractTo ): array {
		$zip = new ZipArchive();
		if ( $zip->open( $zipPath ) !== true ) {
			throw new Exception( 'Failed to open ZIP file' );
		}

		$this->log_info( 'Starting ZIP extraction', [
			'extract_to'  => $extractTo,
			'zip_files'   => $zip->numFiles,
			'is_dir'      => is_dir( $extractTo ),
			'is_writable' => is_writable( $extractTo )
		] );

		$extractStart     = microtime( true );
		$extractionResult = $zip->extractTo( $extractTo );

		if ( ! $extractionResult ) {
			$zip->close();
			throw new Exception( 'ZipArchive::extractTo() failed for path: ' . $extractTo );
		}

		$fileCount = $zip->numFiles;
		$zip->close();
		$extractTime = microtime( true ) - $extractStart;

		$this->log_info( 'ZIP extracted successfully', [
			'files'        => $fileCount,
			'time_seconds' => round( $extractTime, 2 )
		] );

		return [
			'file_count'   => $fileCount,
			'extract_time' => $extractTime
		];
	}

	/**
	 * Check if this is file discovery mode
	 *
	 * @param array $params Request parameters
	 *
	 * @return bool True if file discovery mode
	 */
	private function isFileDiscoveryMode( array $params ): bool {
		return ( $params['config']['return_file_list'] ?? false ) === true;
	}

	/**
	 * Handle file discovery mode
	 *
	 * @param string $actualExtractPath Actual extraction path
	 * @param array $params Request parameters
	 */
	private function handleFileDiscovery( string $actualExtractPath, array $params ): void {
		$filePattern     = $params['config']['file_pattern'] ?? '*.php';
		$resolver        = new FilePathResolver( $actualExtractPath );
		$discoveredFiles = [];

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $actualExtractPath, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && fnmatch( $filePattern, $file->getFilename() ) ) {
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

		// Sort by priority (security-sensitive files first)
		usort( $discoveredFiles, fn( $a, $b ) => $b['priority'] - $a['priority'] );

		NodeResponse::success( [
			'extract_path'     => $actualExtractPath,
			'files_discovered' => $discoveredFiles,
			'total_files'      => count( $discoveredFiles ),
			'session_id'       => $params['session_id'] ?? md5( $actualExtractPath )
		] );
	}

	/**
	 * Send regular extraction response
	 *
	 * @param string $actualExtractPath Actual extraction path
	 * @param int $fileCount Number of files extracted
	 * @param array $params Request parameters
	 */
	private function sendExtractionResponse( string $actualExtractPath, int $fileCount, array $params ): void {
		// Get PHP files info
		$phpFiles   = $this->getPhpFiles( $actualExtractPath );
		$totalFiles = $this->getTotalFileCount( $actualExtractPath );

		if ( empty( $phpFiles ) ) {
			$this->log_warning( 'ZIP extracted successfully but no PHP files found', [
				'files_extracted' => $fileCount,
				'actual_path'     => $actualExtractPath
			] );
		}

		NodeResponse::success( [
			'extract_path'    => $actualExtractPath,
			'files_extracted' => $fileCount,
			'php_files_found' => count( $phpFiles ),
			'total_files'     => $totalFiles,
			'session_id'      => $params['session_id'] ?? md5( $actualExtractPath )
		] );
	}

	/**
	 * Get PHP files from directory
	 *
	 * @param string $directory Directory path
	 *
	 * @return array PHP file paths
	 */
	private function getPhpFiles( string $directory ): array {
		$phpFiles = [];
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
				$phpFiles[] = str_replace( $directory . '/', '', $file->getPathname() );
			}
		}

		$this->log_info( 'PHP files detected', [
			'php_files' => array_slice( $phpFiles, 0, 10 ),
			'count'     => count( $phpFiles )
		] );

		return $phpFiles;
	}

	/**
	 * Get total file count
	 *
	 * @param string $directory Directory path
	 *
	 * @return int Total file count
	 */
	private function getTotalFileCount( string $directory ): int {
		$totalFiles = 0;
		$allFiles   = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $allFiles as $file ) {
			if ( $file->isFile() ) {
				$totalFiles ++;
			}
		}

		return $totalFiles;
	}

	/**
	 * Handle extraction error
	 *
	 * @param Exception $e Exception that occurred
	 */
	private function handleExtractionError( Exception $e ): void {
		$this->log_error( 'ZIP extraction failed: ' . $e->getMessage() );

		http_response_code( 500 );
		NodeResponse::error( 'Extraction failed', [ 'message' => $e->getMessage() ] );
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
}
