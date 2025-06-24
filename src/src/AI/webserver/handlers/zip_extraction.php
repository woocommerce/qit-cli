<?php
/**
 * ZIP Extraction Handler
 */

function handle_zip_extraction( $input ) {
	log_info( 'Starting ZIP extraction handler', [
		'input_keys'     => array_keys( $input ),
		'has_zip_url'    => isset( $input['zip_url'] ),
		'has_session_id' => isset( $input['session_id'] )
	] );

	// Parse the input
	$params = json_decode( $input['prompt'], true );
	if ( ! isset( $params['zip_url'] ) || empty( $params['zip_url'] ) ) {
		log_error( 'No ZIP URL provided for extraction' );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Missing zip_url parameter' ] );

		return;
	}

	if ( ! isset( $params['extract_subdir'] ) || empty( $params['extract_subdir'] ) ) {
		log_error( 'No extraction subdirectory provided' );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Missing extract_subdir parameter' ] );

		return;
	}

	$zip_url = $params['zip_url'];

	// SECURITY: Prevent directory traversal attacks
	$extract_subdir = $params['extract_subdir'];

	// SECURITY: Reject any path containing ".." sequences (don't sanitize, reject entirely)
	if ( strpos( $extract_subdir, '..' ) !== false ) {
		log_error( 'Directory traversal attempt detected in extract_subdir', [
			'extract_subdir' => $extract_subdir
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Directory traversal sequences (..) are not allowed in extract_subdir.' ] );

		return;
	}

	// SECURITY: Reject any path containing null bytes
	if ( strpos( $extract_subdir, "\0" ) !== false ) {
		log_error( 'Null byte injection attempt detected in extract_subdir', [
			'extract_subdir' => $extract_subdir
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Null bytes are not allowed in extract_subdir.' ] );

		return;
	}

	// Normalize path separators and remove leading/trailing slashes
	$extract_subdir = trim( str_replace( '\\', '/', $extract_subdir ), '/' );

	// SECURITY: Validate that extract_subdir only contains safe characters
	if ( ! preg_match( '/^[a-zA-Z0-9\/_\-\.]+$/', $extract_subdir ) ) {
		log_error( 'extract_subdir contains invalid characters', [
			'extract_subdir' => $extract_subdir
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'extract_subdir contains invalid characters. Only alphanumeric, dash, underscore, slash, and dot are allowed.' ] );

		return;
	}

	// SECURITY: Additional check - reject if path is empty after sanitization
	if ( empty( $extract_subdir ) ) {
		log_error( 'extract_subdir is empty after sanitization' );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'extract_subdir cannot be empty.' ] );

		return;
	}

	// CLI defines the basepath, manager dictates WHERE in temp
	$temp_base  = sys_get_temp_dir();
	$extract_to = $temp_base . '/' . $extract_subdir;

	// SECURITY: Use realpath to resolve any remaining path traversal attempts and validate sandbox
	$real_temp_base = realpath( $temp_base );
	if ( $real_temp_base === false ) {
		log_error( 'Failed to resolve temp directory realpath', [ 'temp_base' => $temp_base ] );
		http_response_code( 500 );
		echo json_encode( [ 'error' => 'Failed to resolve temp directory path' ] );

		return;
	}

	// Create the directory first so we can get its realpath
	$extract_parent = dirname( $extract_to );
	if ( ! is_dir( $extract_parent ) ) {
		if ( ! mkdir( $extract_parent, 0777, true ) ) {
			log_error( 'Failed to create parent directory for security validation', [ 'parent' => $extract_parent ] );
			http_response_code( 500 );
			echo json_encode( [ 'error' => 'Failed to create parent directory' ] );

			return;
		}
	}

	$real_extract_parent = realpath( $extract_parent );
	if ( $real_extract_parent === false ) {
		log_error( 'Failed to resolve extraction parent directory realpath', [ 'extract_parent' => $extract_parent ] );
		http_response_code( 500 );
		echo json_encode( [ 'error' => 'Failed to resolve extraction directory path' ] );

		return;
	}

	// SECURITY: Ensure the resolved path is within the temp directory sandbox
	if ( strpos( $real_extract_parent, $real_temp_base ) !== 0 ) {
		log_error( 'Directory traversal attempt detected', [
			'original_subdir'     => $params['extract_subdir'],
			'sanitized_subdir'    => $extract_subdir,
			'extract_to'          => $extract_to,
			'real_temp_base'      => $real_temp_base,
			'real_extract_parent' => $real_extract_parent
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Directory traversal attempt detected. Path must be within temp directory.' ] );

		return;
	}

	log_info( 'Path security validation passed', [
		'original_subdir'     => $params['extract_subdir'],
		'sanitized_subdir'    => $extract_subdir,
		'extract_to'          => $extract_to,
		'real_temp_base'      => $real_temp_base,
		'real_extract_parent' => $real_extract_parent
	] );

	// Validate that extract_to is a directory path, not a file path
	if ( pathinfo( $extract_to, PATHINFO_EXTENSION ) ) {
		log_error( 'extract_subdir appears to be a file path, not a directory path', [
			'extract_subdir' => $extract_subdir,
			'extract_to'     => $extract_to,
			'extension'      => pathinfo( $extract_to, PATHINFO_EXTENSION )
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'extract_subdir must be a directory path, not a file path' ] );

		return;
	}

	try {
		log_info( 'Downloading and extracting ZIP', [
			'zip_url'        => substr( $zip_url, 0, 100 ) . '...',
			'extract_to'     => $extract_to,
			'extract_subdir' => $params['extract_subdir']
		] );

		// Handle existing extraction directory
		if ( is_dir( $extract_to ) ) {
			log_info( 'Extraction directory already exists, clearing it to avoid permission issues', [ 'path' => $extract_to ] );

			// Remove all files and subdirectories to avoid "Operation not permitted" errors
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $extract_to, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ( $iterator as $file ) {
				if ( $file->isDir() ) {
					if ( ! rmdir( $file->getRealPath() ) ) {
						log_warning( 'Failed to remove directory', [ 'path' => $file->getRealPath() ] );
					}
				} else {
					if ( ! unlink( $file->getRealPath() ) ) {
						log_warning( 'Failed to remove file', [ 'path' => $file->getRealPath() ] );
					}
				}
			}

			log_info( 'Existing directory cleared successfully', [ 'path' => $extract_to ] );
		}

		// Create extraction directory (ensure ALL parent directories are created properly)
		if ( ! is_dir( $extract_to ) ) {
			log_info( 'Creating extraction directory and all parent directories', [ 'path' => $extract_to ] );

			// Create parent directories step by step to ensure proper permissions
			$path_parts   = explode( '/', $extract_to );
			$current_path = '';

			foreach ( $path_parts as $part ) {
				if ( empty( $part ) ) {
					$current_path .= '/';
					continue;
				}

				// Add path separator if current_path doesn't end with one and isn't empty
				if ( ! empty( $current_path ) && ! str_ends_with( $current_path, '/' ) ) {
					$current_path .= '/';
				}
				$current_path .= $part;

				if ( ! is_dir( $current_path ) ) {
					log_info( 'Creating parent directory', [ 'path' => $current_path ] );
					if ( ! mkdir( $current_path, 0777, false ) ) {
						throw new Exception( 'Failed to create parent directory: ' . $current_path );
					}
					log_info( 'Parent directory created successfully', [ 'path' => $current_path ] );
				} else {
					log_debug( 'Parent directory already exists', [ 'path' => $current_path ] );
				}

				// Verify the directory is writable
				if ( ! is_writable( $current_path ) ) {
					log_warning( 'Parent directory is not writable, attempting to fix permissions', [ 'path' => $current_path ] );
					if ( ! chmod( $current_path, 0777 ) ) {
						throw new Exception( 'Parent directory is not writable and cannot fix permissions: ' . $current_path );
					}
					log_info( 'Fixed permissions for parent directory', [ 'path' => $current_path ] );
				}
			}

			log_info( 'All parent directories created and verified', [ 'final_path' => $extract_to ] );
		} else {
			log_info( 'Extraction directory ready for use', [ 'path' => $extract_to ] );
		}

		// Download the ZIP file
		$zip_path = $extract_to . '/plugin.zip';
		$ch       = curl_init( $zip_url );
		$fp       = fopen( $zip_path, 'wb' );

		curl_setopt( $ch, CURLOPT_FILE, $fp );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );

		$download_start = microtime( true );
		curl_exec( $ch );
		$download_time = microtime( true ) - $download_start;

		$http_code     = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$download_size = curl_getinfo( $ch, CURLINFO_SIZE_DOWNLOAD );

		curl_close( $ch );
		fclose( $fp );

		if ( $http_code !== 200 ) {
			throw new Exception( "Failed to download ZIP: HTTP $http_code" );
		}

		log_info( 'ZIP downloaded successfully', [
			'size_mb'      => round( $download_size / ( 1024 * 1024 ), 2 ),
			'time_seconds' => round( $download_time, 2 )
		] );

		// Extract using ZipArchive (safer than Docker for this use case)
		$zip = new ZipArchive();
		if ( $zip->open( $zip_path ) === true ) {
			// Verify extraction directory exists and is writable before extraction
			if ( ! is_dir( $extract_to ) ) {
				throw new Exception( 'Extraction directory does not exist: ' . $extract_to );
			}
			if ( ! is_writable( $extract_to ) ) {
				throw new Exception( 'Extraction directory is not writable: ' . $extract_to );
			}

			log_info( 'Starting ZIP extraction', [
				'extract_to'        => $extract_to,
				'extract_to_exists' => file_exists( $extract_to ) ? 'yes' : 'no',
				'zip_files'         => $zip->numFiles,
				'is_dir'            => is_dir( $extract_to ),
				'is_writable'       => is_writable( $extract_to )
			] );

			$extract_start     = microtime( true );
			$extraction_result = $zip->extractTo( $extract_to );

			if ( ! $extraction_result ) {
				$zip->close();
				throw new Exception( 'ZipArchive::extractTo() failed for path: ' . $extract_to );
			}

			$file_count = $zip->numFiles;
			$zip->close();
			$extract_time = microtime( true ) - $extract_start;

			log_info( 'ZIP extracted successfully', [
				'files'        => $file_count,
				'time_seconds' => round( $extract_time, 2 )
			] );

			// Remove the ZIP file
			unlink( $zip_path );

			// Create marker file
			touch( $extract_to . '/.analyzed' );

			// Get some info about what was extracted
			$php_files   = glob( $extract_to . '/**/*.php' );
			$all_files   = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $extract_to, RecursiveDirectoryIterator::SKIP_DOTS )
			);
			$total_files = 0;
			foreach ( $all_files as $file ) {
				if ( $file->isFile() ) {
					$total_files ++;
				}
			}

			// Return extraction results
			echo json_encode( [
				'response'           => json_encode( [
					'success'         => true,
					'extract_path'    => $extract_to,
					'files_extracted' => $file_count,
					'php_files_found' => count( $php_files ),
					'total_files'     => $total_files,
					'extraction_time' => round( $extract_time, 2 ),
					'download_time'   => round( $download_time, 2 )
				] ),
				'model'              => 'none',
				'timestamp'          => time(),
				'processing_time_ms' => round( ( $download_time + $extract_time ) * 1000 )
			] );

		} else {
			throw new Exception( 'Failed to open ZIP file' );
		}

	} catch ( Exception $e ) {
		log_error( 'ZIP extraction failed: ' . $e->getMessage() );

		http_response_code( 500 );
		echo json_encode( [
			'error'   => 'Extraction failed',
			'message' => $e->getMessage()
		] );
	}
}
