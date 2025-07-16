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
	/*
	--------------------------------------------------------------------
	 *  Configuration ‑ adjust to your policy
	 * ------------------------------------------------------------------
	 */
	private const MAX_ARCHIVE_SIZE_BYTES       = 250 * 1024 * 1024;   // 250 MB
	private const MAX_UNCOMPRESSED_TOTAL_BYTES = 2_000_000_000;       // 2 GB
	private const MAX_COMPRESSION_RATIO        = 50;                  // 50×
	private const MAX_ENTRIES                  = 20_000;

	/* ------------------------------------------------------------------ */
	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/extract-zip';
	}

	public function handle( array $input ) {

		$this->log_info( 'Starting ZIP extraction endpoint', [
			'input_keys' => array_keys( $input ),
		] );

		NodeResponse::mark( 'parameter_validation' );
		$validation_result = $this->validateParameters( $input );
		if ( $validation_result !== true ) {
			// validateParameters returns error response string if validation fails
			return $validation_result;
		}

		$zip_url    = $input['zip_url'];
		$session_id = $input['session_id'] ?? md5( uniqid() );

		NodeResponse::mark( 'path_preparation' );
		$temp_base  = sys_get_temp_dir();
		$extract_to = $temp_base . '/qit-code-analysis-' . $session_id;

		NodeResponse::mark( 'security_validation' );
		$security_result = $this->validateExtractionSecurity( $temp_base, $extract_to );
		if ( $security_result !== true ) {
			// validateExtractionSecurity returns error response string if validation fails
			return $security_result;
		}

		try {
			NodeResponse::mark( 'extraction_start' );
			return $this->performExtraction( $zip_url, $extract_to, $input );
		} catch ( Exception $e ) {
			return $this->handleExtractionError( $e );
		}
	}

	/*
	=====================================================================
	 *  Parameter & path validation helpers (unchanged)
	 * ===================================================================
	 */
	/**
	 * Validate required parameters
	 *
	 * @param array<string, mixed> $input Input parameters.
	 * @return mixed Error response string or true if valid
	 */
	private function validateParameters( array $input ) {
		foreach ( [ 'zip_url', 'session_id' ] as $k ) {
			if ( empty( $input[ $k ] ) ) {
				$this->log_error( "Missing $k parameter" );
				// Note: NodeResponse::error will set the HTTP status code in the JSON response
				// The router.worker.php will handle setting the actual HTTP status code
				return json_encode( NodeResponse::error( "Missing $k parameter", 400 ) );
			}
		}

		return true;
	}


	/**
	 * Validate extraction security
	 *
	 * @param string $temp_base Temporary base directory.
	 * @param string $extract_to Extraction target directory.
	 * @return mixed Error response string or true if valid.
	 */
	private function validateExtractionSecurity(
		string $temp_base,
		string $extract_to
	) {

		$real_temp_base = realpath( $temp_base );
		if ( $real_temp_base === false ) {
			// Note: NodeResponse::error will set the HTTP status code in the JSON response
			// The router.worker.php will handle setting the actual HTTP status code
			return json_encode( NodeResponse::error( 'Failed to resolve temp directory path', 500 ) );
		}

		$extract_parent = dirname( $extract_to );
		if ( ! is_dir( $extract_parent ) && ! mkdir( $extract_parent, 0777, true ) ) {
			return json_encode( NodeResponse::error( 'Failed to create parent directory', 500 ) );
		}

		$real_extract_parent = realpath( $extract_parent );
		if ( $real_extract_parent === false || str_starts_with( $real_extract_parent, $real_temp_base ) === false ) {
			return json_encode( NodeResponse::error( 'Directory traversal attempt detected', 400 ) );
		}

		return true;
	}

	/*
	=====================================================================
	 *  Precondition validation
	 * ===================================================================
	 */
	/**
	 * Validate precondition
	 *
	 * @param string $extract_to Extraction directory.
	 * @param array<string, mixed> $config Configuration array.
	 * @return void
	 */
	private function validatePrecondition( string $extract_to, array $config ): void {
		$requires = $config['requires'] ?? null;

		// 1. new_extraction_dir  ─────────────────────────────────────────────
		if ( $requires === 'new_extraction_dir' ) {
			if ( is_dir( $extract_to ) ) {
				throw new Exception(
					'Pre‑condition failed (new_extraction_dir): '
					. "$extract_to already exists"
				);
			}
			return;                         // nothing else to check
		}

		// 2. wordpress_on_extraction_dir  ────────────────────────────────────
		if ( $requires === 'wordpress_on_extraction_dir' ) {
			if ( ! is_file( $extract_to . '/wp-includes/version.php' ) ||
				! is_file( $extract_to . '/wp-admin/admin.php' ) ) {
				throw new Exception(
					'Pre‑condition failed (wordpress_on_extraction_dir): '
					. "WordPress core not found in $extract_to"
				);
			}
		}
	}

	/*
	=====================================================================
	 *  Main flow
	 * ===================================================================
	 */
	/**
	 * Perform extraction
	 *
	 * @param string $zip_url URL of ZIP file.
	 * @param string $extract_to Extraction directory.
	 * @param array<string, mixed>  $params Parameters array.
	 * @return string|array<string, mixed> Response string or error array.
	 */
	private function performExtraction( string $zip_url, string $extract_to, array $params ) {

		$session_id    = $params['session_id'] ?? md5( $extract_to );
		$target_subdir = $params['config']['target_subdir'] ?? null;

		/* Workspace root is decided here, not by the Manager */
		$workspace_root = $extract_to;

		$extract_root = $target_subdir
			? rtrim( $workspace_root, '/' ) . '/' . trim( $target_subdir, '/' )
			: $workspace_root;

		$this->validatePrecondition( $workspace_root, $params['config'] ?? [] );

		/* ensure the directory exists */
		if ( ! is_dir( $extract_root ) && ! mkdir( $extract_root, 0777, true ) ) {
			throw new Exception( "Failed to create extraction directory: $extract_root" );
		}

		$zip_path = $this->downloadZipFile( $zip_url, $workspace_root );

		$extraction_result = $this->extractZipFile( $zip_path, $extract_root, $params );

		unlink( $zip_path );

		if ( $extraction_result['file_count'] === 0 ) {
			// Note: NodeResponse::error will set the HTTP status code in the JSON response
			// The router.worker.php will handle setting the actual HTTP status code
			return json_encode( NodeResponse::error( 'ZIP extraction failed – no files extracted', 422, [ 'files_extracted' => 0 ] ) );
		}

		touch( $workspace_root . '/.analyzed' );

		// Create component manifest
		$component_type = $params['config']['type'] ?? 'unknown';
		$structure      = $this->detectWordPressStructure( $extract_root );

		// Determine component details
		$dep_type = 'unknown';
		$dep_slug = basename( $extract_root );

		if ( $structure['is_plugin'] ) {
			$dep_type = 'plugin';
		} elseif ( $structure['is_theme'] ) {
			$dep_type = 'theme';
		} elseif ( $component_type === 'wordpress_core' ) {
			$dep_type = 'wordpress_core';
		} elseif ( strpos( $dep_slug, 'dependency' ) !== false ) {
			$dep_type = strpos( $dep_slug, 'premium' ) !== false ? 'dependency_premium' : 'dependency_free';
		}

		$manifest = [
			'type'   => $dep_type,
			'slug'   => $dep_slug,
			'is_sut' => $component_type === 'sut',
		];

		// Return the workspace root as extract_path, with proper prefix filtering
		return $this->sendUnifiedExtractionResponse( $workspace_root, $extraction_result['file_count'], $params, $manifest );
	}

	/*
	--------------------------------------------------------------------
	 *  Directory preparation
	 * ------------------------------------------------------------------
	 */
	/**
	 * Prepare extraction directory
	 *
	 * @param string $extract_to Extraction directory path.
	 * @return void
	 */
	private function prepareExtractionDirectory( string $extract_to ): void {

		if ( is_dir( $extract_to ) ) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $extract_to, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $iterator as $file ) {
				$file->isDir() ? rmdir( $file->getRealPath() ) : unlink( $file->getRealPath() );
			}
		}
		if ( ! is_dir( $extract_to ) && ! mkdir( $extract_to, 0777, true ) ) {
			throw new Exception( 'Failed to create extraction directory' );
		}
	}

	/**
	 * Download ZIP file
	 *
	 * @param string $zip_url URL of ZIP file.
	 * @param string $extract_to Extraction directory.
	 * @return string Path to downloaded ZIP file.
	 */
	private function downloadZipFile( string $zip_url, string $extract_to ): string {

		$zip_path = $extract_to . '/archive.zip';

		$ch = curl_init( $zip_url );
		$fp = fopen( $zip_path, 'wb' );
		curl_setopt_array( $ch, [
			CURLOPT_FILE           => $fp,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT        => 300,
		] );
		curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );
		fclose( $fp );

		if ( $http_code !== 200 ) {
			throw new Exception( "Failed to download ZIP (HTTP $http_code)" );
		}

		$stat = stat( $zip_path );
		if ( $stat['size'] > self::MAX_ARCHIVE_SIZE_BYTES ) {
			throw new Exception( 'Archive exceeds maximum allowed size' );
		}

		$finfo = new finfo( FILEINFO_MIME_TYPE );
		$mime  = $finfo->file( $zip_path );
		if ( $mime !== 'application/zip' && $mime !== 'application/x-zip' && $mime !== 'application/octet-stream' ) {
			throw new Exception( "Invalid MIME type for ZIP: $mime" );
		}

		return $zip_path;
	}

	/*
	--------------------------------------------------------------------
	 *  Extraction (SECURE)
	 * ------------------------------------------------------------------
	 */
	/**
	 * Extract ZIP file
	 *
	 * @param string $zip_path Path to ZIP file.
	 * @param string $extract_to Extraction directory.
	 * @param array<string, mixed> $params Parameters array.
	 * @return array<string, mixed> Extraction statistics.
	 */
	private function extractZipFile( string $zip_path, string $extract_to, array $params = [] ): array {

		$stats = $this->secureExtractZip( $zip_path, $extract_to, $params );

		$this->log_info( 'ZIP extracted securely', $stats );

		return $stats;
	}

	/**
	 * Secure extraction with per‑entry validation.
	 *
	 * @param string $zip_path Path to ZIP file.
	 * @param string $extract_root Root extraction directory.
	 * @param array<string, mixed> $params Parameters array.
	 * @return array{file_count:int,extract_time:float}
	 * @throws Exception If ZIP file cannot be opened or extraction fails.
	 */
	private function secureExtractZip( string $zip_path, string $extract_root, array $params = [] ): array {

		$zip = new ZipArchive();
		if ( $zip->open( $zip_path ) !== true ) {
			throw new Exception( 'Failed to open ZIP file' );
		}

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- External library property
		if ( $zip->numFiles > self::MAX_ENTRIES ) {
			$zip->close();
			throw new Exception( 'Archive contains too many entries' );
		}

		$total_uncompressed   = 0;
		$extracted_file_count = 0; // Track actual extracted files, not directories
		$start                = microtime( true );

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- External library property
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {

			$stat          = $zip->statIndex( $i );
			$original_name = $stat['name'];
			$name          = $original_name;

			// Strip "wordpress/" prefix for WordPress core extraction
			$is_word_press_core = ( $params['config']['type'] ?? '' ) === 'wordpress_core';
			if ( $is_word_press_core && str_starts_with( $name, 'wordpress/' ) ) {
				$name = substr( $name, 10 ); // Remove "wordpress/" (10 characters)
				// Skip if the entry becomes empty after stripping the prefix
				if ( empty( $name ) ) {
					continue;
				}
			}

			// Strip extra nested directory for plugins/themes with redundant parent directories
			$is_plugin = in_array( $params['config']['type'] ?? '', [ 'sut', 'plugin', 'theme', 'premium_dependency', 'free_dependency' ], true );
			if ( $is_plugin && ! $is_word_press_core ) {
				// Get the expected plugin/theme name from target_subdir
				$target_subdir = $params['config']['target_subdir'] ?? '';
				if ( ! empty( $target_subdir ) ) {
					// Extract the plugin/theme name from target_subdir (e.g., "wp-content/plugins/fortis-for-woocommerce" -> "fortis-for-woocommerce")
					$expected_name = basename( $target_subdir );

					// Check if the entry starts with the expected plugin name followed by a slash
					if ( str_starts_with( $name, $expected_name . '/' ) ) {
						$name = substr( $name, strlen( $expected_name ) + 1 ); // Remove "plugin-name/" prefix
						// Skip if the entry becomes empty after stripping the prefix
						if ( empty( $name ) ) {
							continue;
						}
					}
				}
			}

			// Normalise & validate path
			$target_path = $this->canonicalisePath( $extract_root, $name );

			// Reject directory traversal
			if ( str_starts_with( $target_path, $extract_root ) === false ) {
				$zip->close();
				throw new Exception( "Zip entry attempts path traversal: {$name}" );
			}

			// Reject symlinks / special files
			if ( isset( $stat['external_attributes'] ) && ( $stat['external_attributes'] >> 16 ) & 0xA000 ) { // 0xA000 = symlink
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

			$total_uncompressed += $stat['size'];
			if ( $total_uncompressed > self::MAX_UNCOMPRESSED_TOTAL_BYTES ) {
				$zip->close();
				throw new Exception( 'Total uncompressed size limit exceeded' );
			}

			// Ensure directory exists
			$dir = dirname( $target_path );
			if ( ! is_dir( $dir ) && ! mkdir( $dir, 0777, true ) ) {
				$zip->close();
				throw new Exception( "Failed to create directory: $dir" );
			}

			if ( substr( $name, -1 ) === '/' ) {
				// Directory entry
				if ( ! is_dir( $target_path ) && ! mkdir( $target_path, 0777, true ) ) {
					$zip->close();
					throw new Exception( "Failed to create directory: $target_path" );
				}
				continue; // Don't count directories as extracted files
			}

			$stream = $zip->getStream( $original_name );
			if ( ! $stream ) {
				$zip->close();
				throw new Exception( "Failed to read entry: {$original_name}" );
			}

			$out = fopen( $target_path, 'wb' );
			if ( ! $out ) {
				$zip->close();
				throw new Exception( "Cannot write file: {$target_path}" );
			}

			stream_copy_to_stream( $stream, $out );
			fclose( $stream );
			fclose( $out );
			chmod( $target_path, 0644 );

			++$extracted_file_count; // Only count actual files that were extracted
		}

		$zip->close();

		return [
			'file_count'   => $extracted_file_count, // Return actual extracted file count
			'extract_time' => microtime( true ) - $start,
		];
	}

	/**
	 * Resolve $entry_path against $root securely (no "..", no back‑slashes).
	 *
	 * @param string $root Root directory.
	 * @param string $entry_path Entry path to canonicalize.
	 * @return string Canonicalized path.
	 */
	private function canonicalisePath( string $root, string $entry_path ): string {
		$entry_path = str_replace( [ '\\', "\0" ], '/', $entry_path );
		$entry_path = preg_replace( '#/+#', '/', $entry_path );
		$parts      = [];
		foreach ( explode( '/', $entry_path ) as $part ) {
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
	 * @param string $actual_extract_path Actual extraction path.
	 * @param int    $file_count Number of files extracted.
	 * @param array<string, mixed>  $params Request parameters.
	 * @param array<string, mixed>  $manifest Optional component manifest.
	 * @return array<string, mixed> Response array.
	 */
	private function sendUnifiedExtractionResponse( string $actual_extract_path, int $file_count, array $params, array $manifest = [] ) {
		$list_files       = $params['config']['list_files'] ?? true;   // ★ new flag
		$file_pattern     = $params['config']['file_pattern'] ?? '*.php';
		$resolver         = new FilePathResolver( $actual_extract_path );
		$discovered_files = [];
		$php_files        = [];
		$total_files      = 0;

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $actual_extract_path, RecursiveDirectoryIterator::SKIP_DOTS )
		);

		// Pre-calculate SUT directory prefix for filtering if this is a SUT component
		$sut_prefix = null;
		if ( ( $params['config']['type'] ?? '' ) === 'sut' ) {
			$target_subdir = $params['config']['target_subdir'] ?? null;
			$sut_prefix    = $target_subdir ? trim( $target_subdir, '/' ) . '/' : null;
		}

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$relative_path = $resolver->toRelative( $file->getPathname() );

				// Filter files for SUT components - only include files under the SUT directory
				if ( $sut_prefix !== null && ! str_starts_with( $relative_path, $sut_prefix ) ) {
					continue; // Skip files not under the SUT directory
				}

				++$total_files;

				// Check if it's a PHP file for basic stats
				if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
					$php_files[] = str_replace( $actual_extract_path . '/', '', $file->getPathname() );
				}

				// Always collect stats, but only build the heavy list if requested
				if ( $list_files && fnmatch( $file_pattern, $file->getFilename() ) ) {
					$priority = $this->calculateSecurityPriority( $relative_path );

					// Calculate SHA-1 for the file
					$sha1 = sha1_file( $file->getPathname() );

					$discovered_files[] = [
						'path'     => $relative_path,
						'size'     => $file->getSize(),
						'lines'    => substr_count( file_get_contents( $file ), "\n" ) + 1,
						'priority' => $priority,
						'sha1'     => $sha1,
					];
				}
			}
		}

		// Sort discovered files by priority (security-sensitive files first)
		usort( $discovered_files, fn( $a, $b ) => $b['priority'] - $a['priority'] );

		if ( empty( $php_files ) ) {
			$this->log_warning( 'ZIP extracted successfully but no PHP files found', [
				'files_extracted' => $file_count,
				'actual_path'     => $actual_extract_path,
			] );
		}

		$this->log_info( 'PHP files detected', [
			'php_files' => array_slice( $php_files, 0, 10 ),
			'count'     => count( $php_files ),
		] );

		// Send unified response with both stats and file discovery data
		$response = [
			'extract_path'     => $actual_extract_path,
			'session_id'       => $params['session_id'] ?? md5( $actual_extract_path ),
			'files_discovered' => $list_files ? $discovered_files : [],
			'stats'            => [
				'files_extracted'        => $file_count,
				'php_files_found'        => count( $php_files ),
				'total_files'            => $total_files,
				'files_matching_pattern' => count( $discovered_files ),
			],
		];

		// Add component manifest if provided
		if ( ! empty( $manifest ) ) {
			$response['component'] = $manifest;
		}

		return json_encode( NodeResponse::success( $response ) );
	}


	/**
	 * Handle extraction error
	 *
	 * @param Exception $e Exception that occurred.
	 * @return string JSON error response
	 */
	private function handleExtractionError( Exception $e ): string {
		$this->log_error( 'ZIP extraction failed: ' . $e->getMessage() );

		// Note: NodeResponse::error will set the HTTP status code in the JSON response
		// The router.worker.php will handle setting the actual HTTP status code
		return json_encode( NodeResponse::error( 'Extraction failed', 500, [ 'message' => $e->getMessage() ] ) );
	}


	/**
	 * Detect WordPress plugin or theme structure
	 *
	 * @param string $directory Directory to check.
	 *
	 * @return array<string, mixed> Structure information.
	 */
	private function detectWordPressStructure( string $directory ): array {
		$result = [
			'is_plugin' => false,
			'is_theme'  => false,
			'type'      => 'unknown',
			'main_file' => null,
		];

		// Check for WordPress plugin
		$php_files = glob( $directory . '/*.php' );
		foreach ( $php_files as $file ) {
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
			$style_content = file_get_contents( $directory . '/style.css' );
			if ( strpos( $style_content, 'Theme Name:' ) !== false ) {
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
	 * @param string $file_path File path.
	 *
	 * @return int Priority score.
	 */
	private function calculateSecurityPriority( string $file_path ): int {
		$priority_patterns = [
			'/ajax|admin-ajax/i'     => 100,
			'/admin\//i'             => 90,
			'/api|rest/i'            => 85,
			'/callback|webhook/i'    => 80,
			'/upload|download/i'     => 75,
			'/auth|login|register/i' => 70,
			'/payment|checkout/i'    => 65,
			'/includes\//i'          => 50,
			'/template/i'            => 30,
			'/assets|css|js/i'       => 10,
		];

		foreach ( $priority_patterns as $pattern => $score ) {
			if ( preg_match( $pattern, $file_path ) ) {
				return $score;
			}
		}

		return 40; // Default priority
	}

	/* ------------------------------------------------------ New helpers --- */

	/**
	 * Return every top‑level directory in the workspace (one level deep).
	 * 
	 * @param string $base Base directory to scan.
	 * @return array<string, mixed> Array of workspace roots.
	 */
	private function getWorkspaceRoots( string $base ): array {
		$roots = [];
		foreach ( scandir( $base ) as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			if ( is_dir( $base . '/' . $item ) ) {
				$roots[] = rtrim( $item, '/' ) . '/';              // keep trailing "/"
			}
		}
		sort( $roots );
		return $roots;
	}

	/**
	 * Write a small JSON file the prompt‑builder can read later.
	 *
	 * @param string $base Base directory.
	 * @param array<string, mixed> $roots Directory roots.
	 * @return void
	 */
	private function writeContextFile( string $base, array $roots ): void {
		$ctx       = [
			'contract_version' => 3,
			'roots'            => $roots,
			'generated_at'     => gmdate( DATE_ATOM ),
		];
		$file_path = $base . '/.ctx.json';
		$this->log_info('Attempting to write .ctx.json file', [
			'file_path'          => $file_path,
			'base_directory'     => $base,
			'roots'              => $roots,
			'directory_exists'   => is_dir( $base ),
			'directory_writable' => is_writable( $base ),
		]);

		$result = file_put_contents( $file_path, json_encode( $ctx, JSON_PRETTY_PRINT ) );

		if ( $result === false ) {
			$this->log_error('Failed to write .ctx.json file', [
				'file_path' => $file_path,
				'error'     => error_get_last(),
			]);
		} else {
			$this->log_info('.ctx.json file written successfully', [
				'file_path'     => $file_path,
				'bytes_written' => $result,
				'file_exists'   => file_exists( $file_path ),
			]);
		}
	}
}
