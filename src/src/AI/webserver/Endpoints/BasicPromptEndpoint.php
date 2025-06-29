<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\NodeResponse;

/**
 * Basic Prompt Endpoint
 *
 * Simplified endpoint for basic prompting: receive a string and model, process it, return response.
 * Tool-based requests are handled by dedicated endpoints.
 */
class BasicPromptEndpoint extends AbstractEndpoint {
	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/basic-prompt';
	}

	/**
	 * Handle AI process request
	 *
	 * @param array $input Request input data
	 *
	 * @return void Outputs JSON response
	 */
	public function handle( array $input ): void {
		$this->log_info( "Processing basic AI request" );

		// Validate input - only model is required
		if ( ! isset( $input['model'] ) ) {
			$this->log_error( "Missing required model parameter", [
				'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );

			NodeResponse::error( 'Missing required model parameter', 400, [
				'job_id' => $input['job_id'] ?? null
			] );

			return;
		}

		try {
			// Ensure the model is available before processing
			$model = $input['model'];
			NodeResponse::mark( 'model_check' );
			if ( ! $this->ensureModelAvailable( $model ) ) {
				throw new Exception( 'Failed to ensure model availability: ' . $model );
			}

			$this->log_info( "Starting AI processing", [
				'model'         => $model,
				'job_id'        => $input['job_id'] ?? 'unknown',
				'prompt_length' => strlen( $input['prompt'] ?? '' ),
				'has_schema'    => isset( $input['format'] ) ? 'yes' : 'no',
				'has_options'   => isset( $input['options'] ) ? 'yes' : 'no'
			] );

			$ollamaRequest = [
				'model'  => $model,
				'prompt' => $input['prompt'] ?? '',
				'stream' => false,
				'system' => '/no_think', // Disable thinking for models that support it
			];

			// Add format if schema is provided
			if ( isset( $input['format'] ) && is_array( $input['format'] ) ) {
				$ollamaRequest['format'] = $input['format'];
			}

			// Make the API call - options are automatically applied by parent class
			NodeResponse::mark( 'ollama_call' );
			$response = $this->callOllamaGenerate( $ollamaRequest, $input );

			// Log performance metrics
			$this->log_info( "AI processing completed successfully", [
				'job_id'           => $input['job_id'] ?? 'unknown',
				'model'            => $response['model'] ?? $model,
				'tokens_generated' => $response['eval_count'] ?? 0,
				'response_length'  => strlen( $response['response'] ),
			] );

			// Use NodeResponse::prompt for standardized response
			NodeResponse::prompt(
				trim( $response['response'] ),
				$response['model'] ?? $model,
				$response, // Pass full response for token stats
				[ 'job_id' => $input['job_id'] ?? null ]
			);

		} catch ( Exception $e ) {
			$this->handleError( $e, [
				'job_id'   => $input['job_id'] ?? null,
				'model'    => $input['model'] ?? 'unknown',
				'job_type' => $input['type'] ?? 'unknown'
			] );
		}
	}
}