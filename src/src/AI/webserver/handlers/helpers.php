<?php
/**
 * Helper Functions
 */

/**
 * Check if Ollama CLI is available
 */
function is_ollama_available() {
	$process = proc_open( 'which ollama', [
		0 => [ 'pipe', 'r' ],
		1 => [ 'pipe', 'w' ],
		2 => [ 'pipe', 'w' ]
	], $pipes );

	if ( is_resource( $process ) ) {
		fclose( $pipes[0] );
		$output = stream_get_contents( $pipes[1] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		$return_code = proc_close( $process );

		return $return_code === 0 && ! empty( trim( $output ) );
	}

	return false;
}

/**
 * Get Ollama API base URL
 */
function get_ollama_api_url() {
	return getenv( 'OLLAMA_API_URL' ) ?: 'http://localhost:11434';
}

/**
 * Helper function to call Ollama API
 */
function call_ollama( $url, $data ) {
	// Determine if this is a tool call or regular generation
	$has_tools = isset( $data['tools'] ) && ! empty( $data['tools'] );

	log_debug( "Calling Ollama API", [
		'url'           => $url,
		'model'         => $data['model'] ?? 'unknown',
		'message_count' => isset( $data['messages'] ) ? count( $data['messages'] ) : 0,
		'has_tools'     => $has_tools,
		'tools_count'   => isset( $data['tools'] ) ? count( $data['tools'] ) : 0,
		'request_size'  => strlen( json_encode( $data ) )
	] );

	// IMPORTANT: Use the correct endpoint for tool calls
	if ( $has_tools && strpos( $url, '/api/generate' ) !== false ) {
		// For tool calls, we MUST use /api/chat endpoint
		$url = str_replace( '/api/generate', '/api/chat', $url );
		log_info( "Using chat endpoint for tool-enabled request", [ 'url' => $url ] );
	}

	$start_time = microtime( true );

	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ] );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );

	$response  = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$error     = curl_error( $ch );
	$info      = curl_getinfo( $ch );
	curl_close( $ch );

	$duration = microtime( true ) - $start_time;

	log_debug( "Ollama API response", [
		'http_code'        => $http_code,
		'duration_seconds' => round( $duration, 2 ),
		'response_size'    => $response ? strlen( $response ) : 0,
		'curl_error'       => $error ?: null,
		'total_time'       => $info['total_time'] ?? null
	] );

	if ( $response === false ) {
		throw new Exception( "Ollama API curl error: $error" );
	}

	if ( $http_code !== 200 ) {
		log_error( "Ollama API error response", [
			'http_code' => $http_code,
			'response'  => substr( $response, 0, 500 )
		] );
		throw new Exception( "Ollama API error: HTTP $http_code" );
	}

	$decoded = json_decode( $response, true );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		throw new Exception( "Invalid JSON response from Ollama: " . json_last_error_msg() );
	}

	// Log tool calls if present
	if ( isset( $decoded['message']['tool_calls'] ) ) {
		log_info( "Ollama returned tool calls", [
			'tool_calls_count' => count( $decoded['message']['tool_calls'] ),
			'tool_names'       => array_map( function ( $tc ) {
				return $tc['function']['name'] ?? 'unknown';
			}, $decoded['message']['tool_calls'] )
		] );
	}

	return $decoded;
}

/**
 * Safely remove a directory and its contents
 */
function remove_directory_safely( $dir ) {
	if ( ! is_dir( $dir ) ) {
		log_debug( "Not a directory, skipping removal", [ 'path' => $dir ] );

		return;
	}

	log_debug( "Removing directory safely", [ 'dir' => $dir ] );

	$files      = array_diff( scandir( $dir ), [ '.', '..' ] );
	$file_count = 0;
	$dir_count  = 0;

	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;
		if ( is_dir( $path ) ) {
			$dir_count ++;
			remove_directory_safely( $path );
		} else {
			$file_count ++;
			unlink( $path );
		}
	}

	rmdir( $dir );

	log_debug( "Directory removed", [
		'dir'             => $dir,
		'files_removed'   => $file_count,
		'subdirs_removed' => $dir_count
	] );
}

/**
 * Generate a secure random token
 */
function generate_token( $length = 32 ) {
	return bin2hex( random_bytes( $length ) );
}

/**
 * Validate file path to prevent directory traversal
 */
function validate_file_path( $path, $base_dir ) {
	$real_base = realpath( $base_dir );
	$real_path = realpath( $base_dir . '/' . ltrim( $path, '/' ) );

	if ( $real_path === false || strpos( $real_path, $real_base ) !== 0 ) {
		return false;
	}

	return $real_path;
}

/**
 * Format bytes to human readable format
 */
function format_bytes( $bytes, $precision = 2 ) {
	$units = [ 'B', 'KB', 'MB', 'GB', 'TB' ];

	$bytes = max( $bytes, 0 );
	$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow   = min( $pow, count( $units ) - 1 );

	$bytes /= pow( 1024, $pow );

	return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}

/**
 * Cleanup old sessions periodically
 */
function cleanup_old_sessions() {
	$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';

	if ( ! is_dir( $cache_dir ) ) {
		log_debug( "Cache directory does not exist, skipping cleanup", [ 'path' => $cache_dir ] );

		return;
	}

	log_debug( "Starting cleanup of old sessions", [ 'cache_dir' => $cache_dir ] );

	$now          = time();
	$dirs_scanned = 0;
	$dirs_removed = 0;
	$dirs_skipped = 0;
	$dirs_invalid = 0;

	foreach ( scandir( $cache_dir ) as $dir ) {
		if ( $dir === '.' || $dir === '..' ) {
			continue;
		}

		$dirs_scanned ++;
		$session_dir = $cache_dir . '/' . $dir;
		$real_path   = realpath( $session_dir );

		// Verify it's really inside cache_dir
		if ( $real_path === false || strpos( $real_path, realpath( $cache_dir ) ) !== 0 ) {
			log_warning( "Skipping directory outside cache_dir", [ 'dir' => $session_dir ] );
			$dirs_invalid ++;
			continue;
		}

		if ( is_dir( $real_path ) ) {
			$mtime     = filemtime( $real_path );
			$age_hours = round( ( $now - $mtime ) / 3600, 1 );

			if ( $now - $mtime > 3600 ) { // 1 hour old
				log_info( "Removing old session directory", [
					'dir'       => $dir,
					'age_hours' => $age_hours
				] );

				// Use PHP's recursive directory removal instead of exec
				remove_directory_safely( $real_path );
				$dirs_removed ++;
			} else {
				log_debug( "Skipping recent session directory", [
					'dir'       => $dir,
					'age_hours' => $age_hours
				] );
				$dirs_skipped ++;
			}
		}
	}

	log_info( "Session cleanup completed", [
		'dirs_scanned' => $dirs_scanned,
		'dirs_removed' => $dirs_removed,
		'dirs_skipped' => $dirs_skipped,
		'dirs_invalid' => $dirs_invalid
	] );
}

/**
 * Get memory usage information
 */
function get_memory_info() {
	return [
		'current'           => memory_get_usage( true ),
		'current_formatted' => format_bytes( memory_get_usage( true ) ),
		'peak'              => memory_get_peak_usage( true ),
		'peak_formatted'    => format_bytes( memory_get_peak_usage( true ) ),
		'limit'             => ini_get( 'memory_limit' )
	];
}

/**
 * Simple rate limiter
 */
function check_rate_limit( $key, $max_requests = 10, $window_seconds = 60 ) {
	$rate_limit_file = sys_get_temp_dir() . '/qit_rate_' . md5( $key ) . '.json';

	$now  = time();
	$data = [];

	if ( file_exists( $rate_limit_file ) ) {
		$data = json_decode( file_get_contents( $rate_limit_file ), true ) ?: [];
	}

	// Clean old entries
	$data = array_filter( $data, function ( $timestamp ) use ( $now, $window_seconds ) {
		return ( $now - $timestamp ) < $window_seconds;
	} );

	if ( count( $data ) >= $max_requests ) {
		return false; // Rate limit exceeded
	}

	// Add current request
	$data[] = $now;

	// Save updated data
	file_put_contents( $rate_limit_file, json_encode( array_values( $data ) ) );

	return true;
}

/**
 * Sanitize user input for logging
 */
function sanitize_for_log( $data, $max_length = 1000 ) {
	if ( is_string( $data ) ) {
		return substr( $data, 0, $max_length ) . ( strlen( $data ) > $max_length ? '...' : '' );
	}

	if ( is_array( $data ) ) {
		$sanitized = [];
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, [ 'password', 'token', 'secret', 'api_key' ] ) ) {
				$sanitized[ $key ] = '***REDACTED***';
			} else {
				$sanitized[ $key ] = sanitize_for_log( $value, $max_length );
			}
		}

		return $sanitized;
	}

	return $data;
}