<?php
/**
 * Code Analysis Handler - WP Call Graph Analysis
 */

function handle_code_analysis( $input ) {
	log_info( 'Starting code analysis handler', [
		'analysis_type' => 'code_analysis',
		'input_keys'    => array_keys( $input ),
		'timestamp'     => date( 'Y-m-d H:i:s' )
	] );

	// For wp_call_graph analysis, we only need zip_url, file, and line
	// For other analysis types, we might need additional parameters
	$required_params = [ 'zip_url', 'file', 'line' ];
	$missing_params  = [];

	foreach ( $required_params as $param ) {
		if ( ! isset( $input[ $param ] ) ) {
			$missing_params[] = $param;
		}
	}

	if ( ! empty( $missing_params ) ) {
		log_error( 'Missing required parameters', [
			'missing'  => $missing_params,
			'provided' => array_keys( $input )
		] );

		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Missing required parameters: ' . implode( ', ', $missing_params ) ] );
		exit;
	}

	// Validate and sanitize inputs
	$zip_url = $input['zip_url'];
	$file    = $input['file'];
	$line    = $input['line'];

	// Validate file path - only allow PHP files with safe characters
	if ( ! preg_match( '/^[a-zA-Z0-9\/_\-\.]+\.php$/', $file ) ) {
		log_error( 'Invalid file path format', [ 'file' => $file ] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Invalid file path format' ] );
		exit;
	}

	// Remove any directory traversal attempts
	$file = str_replace( '..', '', $file );
	$file = ltrim( $file, '/' );

	// Validate line number
	$line = filter_var( $line, FILTER_VALIDATE_INT, [
		'options' => [
			'min_range' => 1,
			'max_range' => 999999
		]
	] );

	if ( $line === false ) {
		log_error( 'Invalid line number', [ 'line' => $input['line'] ] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Invalid line number' ] );
		exit;
	}

	// Validate ZIP URL
	if ( ! filter_var( $zip_url, FILTER_VALIDATE_URL ) ) {
		log_error( 'Invalid ZIP URL format', [ 'url' => substr( $zip_url, 0, 50 ) . '...' ] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Invalid ZIP URL format' ] );
		exit;
	}

	// Validate URL scheme (only allow HTTPS)
	$url_parts = parse_url( $zip_url );
	if ( $url_parts['scheme'] !== 'https' ) {
		log_error( 'Only HTTPS URLs are allowed', [ 'scheme' => $url_parts['scheme'] ] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Only HTTPS URLs are allowed' ] );
		exit;
	}

	try {
		// Generate secure session ID
		$session_id = $input['session_id'] ?? null;
		if ( ! $session_id ) {
			$session_id = 'qit_' . bin2hex( random_bytes( 16 ) );
		}

		$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';
		$work_dir  = $cache_dir . '/' . $session_id;

		log_info( "Code analysis session details", [
			'session_id' => $session_id,
			'work_dir'   => $work_dir,
			'file'       => $file,
			'line'       => $line
		] );

		// Download and extract if not already cached
		if ( ! is_dir( $work_dir ) || ! file_exists( $work_dir . '/.analyzed' ) ) {
			log_info( "Preparing codebase from URL" );
			prepare_codebase( $zip_url, $work_dir );
		} else {
			log_info( "Using cached codebase" );
		}

		// For dedicated wp call graph analysis, only run wp-call-graph
		log_info( "Starting dedicated wp-call-graph analysis", [
			'work_dir'    => $work_dir,
			'target_file' => $file,
			'target_line' => $line
		] );

		$wp_call_graph_results = run_wp_call_graph_analysis( $work_dir, $file, $line );

		// Build response for wp call graph only
		$response = [
			'success'       => true,
			'analysis_type' => 'wp_call_graph',
			'context'       => [
				'file'       => $file,
				'line'       => $line,
				'session_id' => $session_id,
				'work_dir'   => $work_dir
			],
			'wp_call_graph' => $wp_call_graph_results
		];

		log_info( "Sending dedicated wp-call-graph analysis response", [
			'session_id'            => $session_id,
			'response_size'         => strlen( json_encode( $response ) ),
			'wp_call_graph_success' => $wp_call_graph_results['success'] ?? false
		] );

		echo json_encode( $response );

	} catch ( Exception $e ) {
		log_error( 'Code analysis error: ' . $e->getMessage(), [
			'trace' => $e->getTraceAsString()
		] );

		http_response_code( 500 );
		echo json_encode( [
			'error'   => 'Analysis failed',
			'message' => htmlspecialchars( substr( $e->getMessage(), 0, 200 ), ENT_QUOTES, 'UTF-8' )
		] );
	}
}


/**
 * Prepare codebase by downloading and extracting ZIP
 */
function prepare_codebase( $zip_url, $work_dir ) {
	log_info( "Preparing codebase", [
		'zip_url'  => substr( $zip_url, 0, 50 ) . '...',
		'work_dir' => $work_dir
	] );

	// Create work directory if it doesn't exist
	if ( ! is_dir( $work_dir ) ) {
		mkdir( $work_dir, 0777, true );
	}

	// Download zip file
	$zip_path = $work_dir . '/plugin.zip';
	log_info( "Downloading zip file" );

	$ch = curl_init( $zip_url );
	$fp = fopen( $zip_path, 'wb' );

	curl_setopt( $ch, CURLOPT_FILE, $fp );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 300 );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );

	$download_start = microtime( true );
	$success        = curl_exec( $ch );
	$download_time  = microtime( true ) - $download_start;

	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$error     = curl_error( $ch );

	curl_close( $ch );
	fclose( $fp );

	if ( ! $success || $http_code !== 200 ) {
		log_error( "Download failed", [
			'http_code' => $http_code,
			'error'     => $error
		] );
		throw new Exception( "Failed to download file: HTTP $http_code - $error" );
	}

	log_info( "Download complete", [
		'time_seconds' => round( $download_time, 2 )
	] );

	// Extract using unzip in Docker
	log_info( "Extracting zip file" );

	$descriptorspec = [
		0 => [ "pipe", "r" ],
		1 => [ "pipe", "w" ],
		2 => [ "pipe", "w" ]
	];

	$cmd = [
		'docker',
		'run',
		'--rm',
		'-v',
		$work_dir . ':/work',
		'-w',
		'/work',
		'alpine:latest',
		'sh',
		'-c',
		'apk add --no-cache unzip && unzip -o plugin.zip && rm plugin.zip'
	];

	$extraction_start = microtime( true );
	$process          = proc_open( $cmd, $descriptorspec, $pipes );

	if ( is_resource( $process ) ) {
		fclose( $pipes[0] );
		$stdout = stream_get_contents( $pipes[1] );
		$stderr = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		$return_code     = proc_close( $process );
		$extraction_time = microtime( true ) - $extraction_start;

		if ( $return_code !== 0 ) {
			log_error( "Extraction failed", [
				'return_code' => $return_code,
				'stderr'      => $stderr
			] );
			throw new Exception( 'Failed to extract zip: ' . $stderr );
		}

		log_info( "Extraction complete", [
			'time_seconds' => round( $extraction_time, 2 )
		] );

	} else {
		throw new Exception( 'Failed to start extraction process' );
	}

	// Mark as analyzed
	touch( $work_dir . '/.analyzed' );

	log_info( "Codebase preparation complete" );
}

// Stub logging functions if not defined
if ( ! function_exists( 'log_info' ) ) {
	function log_info( $message, $context = [] ) {
		error_log( '[INFO] ' . $message . ' ' . json_encode( $context ) );
	}
}

if ( ! function_exists( 'log_debug' ) ) {
	function log_debug( $message, $context = [] ) {
		error_log( '[DEBUG] ' . $message . ' ' . json_encode( $context ) );
	}
}

if ( ! function_exists( 'log_error' ) ) {
	function log_error( $message, $context = [] ) {
		error_log( '[ERROR] ' . $message . ' ' . json_encode( $context ) );
	}
}

if ( ! function_exists( 'log_warning' ) ) {
	function log_warning( $message, $context = [] ) {
		error_log( '[WARNING] ' . $message . ' ' . json_encode( $context ) );
	}
}

/**
 * Run wp-call-graph analysis for a specific file and line
 */
function run_wp_call_graph_analysis( $work_dir, $file, $line ) {
	log_info( "Starting wp-call-graph analysis", [
		'work_dir'     => $work_dir,
		'target_file'  => $file,
		'target_line'  => $line,
		'process_id'   => getmypid(),
		'memory_usage' => memory_get_usage( true )
	] );

	$descriptorspec = [
		0 => [ "pipe", "r" ],
		1 => [ "pipe", "w" ],
		2 => [ "pipe", "w" ]
	];

	// Get the wp-call-graph binary path
	$wp_call_graph_bin = __DIR__ . '/../wp-call-graph/bin/wpcallgraph';

	log_debug( "wp-call-graph binary path resolution", [
		'binary_path'   => $wp_call_graph_bin,
		'__DIR__'       => __DIR__,
		'file_exists'   => file_exists( $wp_call_graph_bin ),
		'is_executable' => is_executable( $wp_call_graph_bin )
	] );

	if ( ! file_exists( $wp_call_graph_bin ) ) {
		log_error( "wp-call-graph binary not found", [
			'expected_path'      => $wp_call_graph_bin,
			'directory_contents' => is_dir( dirname( $wp_call_graph_bin ) ) ? scandir( dirname( $wp_call_graph_bin ) ) : 'directory not found'
		] );

		return [
			'success' => false,
			'error'   => 'wp-call-graph binary not found'
		];
	}

	// Build the target file path relative to work directory
	$target_file_path = $work_dir . '/' . $file;

	log_debug( "wp-call-graph target file validation", [
		'work_dir'         => $work_dir,
		'relative_file'    => $file,
		'target_file_path' => $target_file_path,
		'file_exists'      => file_exists( $target_file_path ),
		'file_size'        => file_exists( $target_file_path ) ? filesize( $target_file_path ) : 0,
		'is_readable'      => is_readable( $target_file_path )
	] );

	if ( ! file_exists( $target_file_path ) ) {
		log_error( "wp-call-graph target file not found", [
			'target_file'       => $target_file_path,
			'work_dir_contents' => is_dir( $work_dir ) ? array_slice( scandir( $work_dir ), 0, 10 ) : 'work_dir not found'
		] );

		return [
			'success' => false,
			'error'   => 'Target file not found: ' . $file
		];
	}

	$cmd = [
		'php',
		$wp_call_graph_bin,
		'-f',
		$target_file_path,
		'-l',
		$line,
		'-b',
		$work_dir,
		'-q'  // Quiet mode for clean JSON output
	];

	log_info( "wp-call-graph command preparation complete", [
		'command'     => implode( ' ', $cmd ),
		'target_file' => $target_file_path,
		'line'        => $line,
		'work_dir'    => $work_dir,
		'cmd_length'  => strlen( implode( ' ', $cmd ) )
	] );

	$start_time = microtime( true );
	log_debug( "wp-call-graph process starting", [
		'start_time'     => $start_time,
		'memory_before'  => memory_get_usage( true ),
		'descriptorspec' => $descriptorspec
	] );

	$process = proc_open( $cmd, $descriptorspec, $pipes );

	if ( is_resource( $process ) ) {
		log_debug( "wp-call-graph process opened successfully", [
			'process_resource' => get_resource_type( $process ),
			'pipes_count'      => count( $pipes )
		] );

		fclose( $pipes[0] );

		$stdout = stream_get_contents( $pipes[1] );
		$stderr = stream_get_contents( $pipes[2] );

		fclose( $pipes[1] );
		fclose( $pipes[2] );

		$return_code    = proc_close( $process );
		$execution_time = microtime( true ) - $start_time;

		log_info( "wp-call-graph process completed", [
			'return_code'    => $return_code,
			'execution_time' => round( $execution_time, 2 ) . 's',
			'stdout_length'  => strlen( $stdout ),
			'stderr_length'  => strlen( $stderr ),
			'memory_after'   => memory_get_usage( true ),
			'success'        => $return_code === 0
		] );

		if ( ! empty( $stderr ) ) {
			log_debug( "wp-call-graph stderr output detected", [
				'stderr_length'    => strlen( $stderr ),
				'stderr_preview'   => substr( $stderr, 0, 1000 ),
				'contains_error'   => stripos( $stderr, 'error' ) !== false,
				'contains_warning' => stripos( $stderr, 'warning' ) !== false
			] );
		}

		// Parse JSON output
		if ( ! empty( $stdout ) ) {
			log_debug( "wp-call-graph stdout processing", [
				'stdout_length'     => strlen( $stdout ),
				'stdout_preview'    => substr( trim( $stdout ), 0, 200 ),
				'starts_with_brace' => substr( trim( $stdout ), 0, 1 ) === '{',
				'ends_with_brace'   => substr( trim( $stdout ), - 1 ) === '}'
			] );

			$json_result = json_decode( trim( $stdout ), true );

			if ( json_last_error() === JSON_ERROR_NONE && is_array( $json_result ) ) {
				log_info( "wp-call-graph JSON parsing successful", [
					'symbol'          => $json_result['symbol'] ?? 'unknown',
					'traces_count'    => count( $json_result['trace'] ?? [] ),
					'result_keys'     => array_keys( $json_result ),
					'has_symbol_info' => isset( $json_result['symbol'] ),
					'has_trace_data'  => isset( $json_result['trace'] ) && ! empty( $json_result['trace'] )
				] );

				// Log detailed trace information if available
				if ( isset( $json_result['trace'] ) && is_array( $json_result['trace'] ) ) {
					foreach ( $json_result['trace'] as $index => $trace ) {
						log_debug( "wp-call-graph trace entry", [
							'trace_index' => $index,
							'trace_keys'  => array_keys( $trace ),
							'type'        => $trace['type'] ?? 'unknown',
							'hook_name'   => $trace['hook_name'] ?? 'unknown',
							'file'        => basename( $trace['file'] ?? 'unknown' ),
							'line'        => $trace['line'] ?? 'unknown'
						] );
					}
				}

				return [
					'success'        => true,
					'result'         => $json_result,
					'execution_time' => round( $execution_time, 2 )
				];
			} else {
				log_error( "wp-call-graph JSON parsing failed", [
					'json_error'      => json_last_error_msg(),
					'json_error_code' => json_last_error(),
					'output_preview'  => substr( $stdout, 0, 500 ),
					'output_length'   => strlen( $stdout ),
					'trimmed_output'  => substr( trim( $stdout ), 0, 500 )
				] );

				return [
					'success'    => false,
					'error'      => 'Failed to parse wp-call-graph output',
					'raw_output' => $stdout
				];
			}
		} else {
			log_warning( "wp-call-graph produced no stdout output", [
				'return_code'    => $return_code,
				'stderr_preview' => substr( $stderr, 0, 500 ),
				'stderr_length'  => strlen( $stderr ),
				'execution_time' => round( $execution_time, 2 ) . 's'
			] );

			return [
				'success'     => false,
				'error'       => 'wp-call-graph produced no output',
				'return_code' => $return_code,
				'stderr'      => $stderr
			];
		}

	} else {
		log_error( "Failed to start wp-call-graph process" );

		return [
			'success' => false,
			'error'   => 'Failed to start wp-call-graph process'
		];
	}
}
