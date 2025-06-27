<?php

namespace QIT_AI_Webserver\Handlers;

use Exception;

/**
 * Basic Prompt Handler
 *
 * Simplified handler for basic prompting: receive a string and model, process it, return response.
 * Tool-based requests are handled by dedicated endpoints.
 */
class BasicPromptHandler extends AbstractHandler {
	/**
	 * Handle AI process request
	 *
	 * @param array $input Request input data
	 *
	 * @return void Outputs JSON response
	 */
	public function handle( array $input ): void {
		$this->log_info( "Processing basic AI request" );

		// Validate input
		if ( ! isset( $input['prompt'] ) || ! isset( $input['model'] ) ) {
			$this->log_error( "Missing required parameters", [
				'missing' => ! isset( $input['prompt'] ) ? 'prompt' : 'model',
				'uri'     => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );

			http_response_code( 400 );
			echo json_encode( [ 'error' => 'Missing prompt or model' ] );

			return;
		}

		try {
			// Ensure the model is available before processing
			$model = $input['model'] ?? 'llama3.2';
			if ( ! $this->ensureModelAvailable( $model ) ) {
				throw new Exception( 'Failed to ensure model availability: ' . $model );
			}

			// Track processing time
			$startTime = microtime( true );
			$this->log_info( "Starting AI processing", [
				'model'         => $model,
				'job_id'        => $input['job_id'] ?? 'unknown',
				'prompt_length' => strlen( $input['prompt'] ),
				'has_schema'    => isset( $input['schema'] ) ? 'yes' : 'no'
			] );

			$ollamaRequest = [
				'model'  => $model,
				'prompt' => $input['prompt'],
				'stream' => false,
				'system' => '/no_think', // Disable thinking for models that support it
			];

			// Add format if schema is provided
			if ( isset( $input['schema'] ) && is_array( $input['schema'] ) ) {
				$ollamaRequest['format'] = $input['schema'];
				$this->log_debug( "Using schema format", [ 'schema_keys' => array_keys( $input['schema'] ) ] );
			}

			$this->log_debug( "Sending request to Ollama API", [
				'model'  => $ollamaRequest['model'],
				'system' => $ollamaRequest['system'],
				'prompt' => $ollamaRequest['prompt'],
			] );

			// Make the API call using the parent class method
			$response = $this->callOllamaGenerate( $ollamaRequest );

			// Calculate processing metrics
			$endTime          = microtime( true );
			$processingTimeMs = round( ( $endTime - $startTime ) * 1000 );

			// Calculate tokens per second
			$tokensPerSecond = 0;
			if ( isset( $response['eval_count'] ) && isset( $response['eval_duration'] ) && $response['eval_duration'] > 0 ) {
				$evalSeconds     = $response['eval_duration'] / 1000000000;
				$tokensPerSecond = round( $response['eval_count'] / $evalSeconds, 2 );
			}

			// Log performance metrics
			$this->log_info( "AI processing completed successfully", [
				'job_id'             => $input['job_id'] ?? 'unknown',
				'model'              => $response['model'] ?? $model,
				'processing_time_ms' => $processingTimeMs,
				'tokens_generated'   => $response['eval_count'] ?? 0,
				'tokens_per_second'  => $tokensPerSecond,
				'response_length'    => strlen( $response['response'] ),
			] );

			// Prepare response
			$responseData = [
				'response'                => trim( $response['response'] ),
				'model'                   => $response['model'] ?? $model,
				'timestamp'               => time(),
				'processing_time_ms'      => $processingTimeMs,
				'tokens_generated'        => $response['eval_count'] ?? null,
				'tokens_per_second'       => $tokensPerSecond,
				'prompt_eval_tokens'      => $response['prompt_eval_count'] ?? null,
				'prompt_eval_duration_ms' => isset( $response['prompt_eval_duration'] )
					? round( $response['prompt_eval_duration'] / 1000000 )
					: null,
				'total_duration_ms'       => isset( $response['total_duration'] )
					? round( $response['total_duration'] / 1000000 )
					: $processingTimeMs
			];

			$this->log_debug( "Sending response", [
				'response_size' => strlen( json_encode( $responseData ) ),
				'job_id'        => $input['job_id'] ?? 'unknown'
			] );

			echo json_encode( $responseData );

		} catch ( Exception $e ) {
			$this->handleError( $e, [
				'job_id'   => $input['job_id'] ?? 'unknown',
				'model'    => $input['model'] ?? 'unknown',
				'job_type' => $input['type'] ?? 'unknown'
			] );
		}
	}
}