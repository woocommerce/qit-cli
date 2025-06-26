<?php

/**
 * Basic Prompt Handler
 * 
 * Simplified handler for basic prompting: receive a string and model, process it, return response.
 * Tool-based requests are handled by dedicated endpoints (/security-analysis and /logical-security-analysis).
 */

function handle_ai_process( $input, $ollama_api_url ) {
	log_info( "Processing basic AI request" );

	// Original simple prompt flow
	if ( ! isset( $input['prompt'] ) || ! isset( $input['model'] ) ) {
		log_error( "Missing required parameters", [
			'missing' => ! isset( $input['prompt'] ) ? 'prompt' : 'model',
			'uri'     => $_SERVER['REQUEST_URI']
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => 'Missing prompt or model' ] );
		exit;
	}

	try {
		// Ensure the model is available before processing
		$model = $input['model'] ?? 'llama3.2';
		if ( ! ensure_model_available( $model, $ollama_api_url ) ) {
			throw new Exception( 'Failed to ensure model availability: ' . $model );
		}

		// Track processing time
		$start_time = microtime( true );
		log_info( "Starting AI processing", [
			'model'         => $model,
			'job_id'        => $input['job_id'] ?? 'unknown',
			'prompt_length' => strlen( $input['prompt'] ),
			'has_schema'    => isset( $input['schema'] ) ? 'yes' : 'no'
		] );

		$ollama_request = [
			'model'  => $model,
			'prompt' => $input['prompt'],
			'stream' => false,
			'system' => '/no_think', // Disable thinking for models that support it
		];

		// Add format if schema is provided
		if ( isset( $input['schema'] ) && is_array( $input['schema'] ) ) {
			$ollama_request['format'] = $input['schema'];
			log_debug( "Using schema format", [ 'schema_keys' => array_keys( $input['schema'] ) ] );
		}

		log_debug( "Sending request to Ollama API", [
			'url'    => $ollama_api_url . '/api/generate',
			'model'  => $ollama_request['model'],
			'system' => $ollama_request['system'],
			'prompt' => $ollama_request['prompt'],
		] );

		$ch = curl_init( $ollama_api_url . '/api/generate' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $ollama_request ) );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ] );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 300 ); // 5 minutes timeout

		$response  = curl_exec( $ch );
		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error     = curl_error( $ch );
		$info      = curl_getinfo( $ch );
		curl_close( $ch );

		log_debug( "Ollama API response received", [
			'http_code'     => $http_code,
			'total_time'    => $info['total_time'],
			'size_download' => $info['size_download'],
			'has_error'     => ! empty( $error ) ? 'yes' : 'no'
		] );

		if ( $response === false ) {
			log_error( "Ollama API curl error", [ 'error' => $error, 'info' => $info ] );
			throw new Exception( 'Ollama API error: ' . $error );
		}

		if ( $http_code !== 200 ) {
			log_error( "Ollama API non-200 response", [
				'http_code' => $http_code,
				'response'  => substr( $response, 0, 500 )
			] );
			throw new Exception( 'Ollama API returned status ' . $http_code );
		}

		// Log full ollama response for debugging.
		log_debug( "Ollama response", [
			'response_size' => strlen( $response ),
			'response'      => $response
		] );

		$ollama_response = json_decode( $response, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			log_error( "JSON decode error", [ 'error' => json_last_error_msg() ] );
			throw new Exception( 'Invalid JSON response from Ollama: ' . json_last_error_msg() );
		}

		if ( ! isset( $ollama_response['response'] ) ) {
			log_error( "Invalid Ollama response structure", [
				'keys'             => array_keys( $ollama_response ),
				'response_excerpt' => substr( $response, 0, 500 )
			] );
			throw new Exception( 'Invalid response from Ollama' );
		}

		// Check for schema response issues as requested
		if ( isset( $input['schema'] ) ) {
			// Only decode if the response is a string, otherwise it's already decoded
			if ( is_string( $ollama_response['response'] ) ) {
				$decoded = json_decode( $ollama_response['response'], true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					log_error( "Schema response is not valid JSON", [
						'job_id'           => $input['job_id'] ?? 'unknown',
						'json_error'       => json_last_error_msg(),
						'response_excerpt' => substr( $ollama_response['response'], 0, 200 )
					] );
				}
			} else {
				// Response is already decoded (array)
				$decoded = $ollama_response['response'];
			}
		}

		// Calculate processing time
		$end_time           = microtime( true );
		$processing_time_ms = round( ( $end_time - $start_time ) * 1000 );

		// Calculate tokens per second
		$tokens_per_second = 0;
		if ( isset( $ollama_response['eval_count'] ) && isset( $ollama_response['eval_duration'] ) && $ollama_response['eval_duration'] > 0 ) {
			$eval_seconds      = $ollama_response['eval_duration'] / 1000000000;
			$tokens_per_second = round( $ollama_response['eval_count'] / $eval_seconds, 2 );
		}

		// Also include prompt evaluation metrics if available
		$prompt_eval_tokens   = $ollama_response['prompt_eval_count'] ?? null;
		$prompt_eval_duration = null;
		if ( isset( $ollama_response['prompt_eval_duration'] ) && $ollama_response['prompt_eval_duration'] > 0 ) {
			$prompt_eval_duration = round( $ollama_response['prompt_eval_duration'] / 1000000 ); // Convert to milliseconds
		}

		// Log performance metrics
		log_info( "AI processing completed successfully", [
			'job_id'             => $input['job_id'] ?? 'unknown',
			'model'              => $ollama_response['model'] ?? ( $input['model'] ?? 'llama3.2' ),
			'processing_time_ms' => $processing_time_ms,
			'tokens_generated'   => $ollama_response['eval_count'] ?? 0,
			'tokens_per_second'  => $tokens_per_second,
			'response_length'    => strlen( $ollama_response['response'] ),
		] );

		// Prepare response
		$response_data = [
			'response'                => trim( $ollama_response['response'] ),
			'model'                   => $ollama_response['model'] ?? ( $input['model'] ?? 'llama3.2' ),
			'timestamp'               => time(),
			'processing_time_ms'      => $processing_time_ms,
			'tokens_generated'        => $ollama_response['eval_count'] ?? null,
			'tokens_per_second'       => $tokens_per_second,
			'prompt_eval_tokens'      => $prompt_eval_tokens,
			'prompt_eval_duration_ms' => $prompt_eval_duration,
			'total_duration_ms'       => isset( $ollama_response['total_duration'] ) ? round( $ollama_response['total_duration'] / 1000000 ) : $processing_time_ms
		];

		log_debug( "Sending response", [
			'response_size' => strlen( json_encode( $response_data ) ),
			'job_id'        => $input['job_id'] ?? 'unknown'
		] );

		echo json_encode( $response_data );

	} catch ( Exception $e ) {
		// Get stack trace for detailed logging
		$trace = $e->getTraceAsString();

		log_error( 'Processing error: ' . $e->getMessage(), [
			'exception' => get_class( $e ),
			'job_id'    => $input['job_id'] ?? 'unknown',
			'model'     => $input['model'] ?? 'unknown',
			'trace'     => $trace
		] );

		// Report error back to manager
		$error_report = [
			'job_id'        => $input['job_id'] ?? null,
			'error_type'    => get_class( $e ),
			'error_message' => $e->getMessage(),
			'error_time'    => date( 'Y-m-d H:i:s' ),
			'job_type'      => $input['type'] ?? 'unknown'
		];

		log_info( "Storing error for next heartbeat", [
			'job_id'     => $input['job_id'] ?? 'unknown',
			'error_type' => get_class( $e )
		] );

		// Store error for next heartbeat
		file_put_contents(
			sys_get_temp_dir() . '/qit-node-last-error.json',
			json_encode( $error_report )
		);

		// Prepare error response
		$error_response = [
			'error'         => 'Failed to process request',
			'message'       => $e->getMessage(),
			'error_details' => $error_report
		];

		log_debug( "Sending error response", [
			'status_code'   => 500,
			'response_size' => strlen( json_encode( $error_response ) )
		] );

		http_response_code( 500 );
		echo json_encode( $error_response );
	}
}