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

		// Validate input - model and messages are required
		$missing = [];
		if ( ! isset( $input['model'] ) || empty( $input['model'] ) ) {
			$missing[] = 'model';
		}
		if ( ! isset( $input['messages'] ) || $input['messages'] === null || ! is_array( $input['messages'] ) || empty( $input['messages'] ) ) {
			$missing[] = 'messages';
		}

		// Validate message structure if messages are provided
		if ( ! in_array( 'messages', $missing ) ) {
			foreach ( $input['messages'] as $index => $message ) {
				if ( ! is_array( $message ) || ! isset( $message['role'] ) || ! isset( $message['content'] ) ) {
					$missing[] = "messages[$index] must have 'role' and 'content' fields";
				} elseif ( ! in_array( $message['role'], [ 'system', 'user', 'assistant', 'tool' ] ) ) {
					$missing[] = "messages[$index] has invalid role: " . $message['role'];
				}
			}
		}

		if ( ! empty( $missing ) ) {
			$this->log_error( "Missing or invalid required parameters", [
				'missing' => $missing,
				'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
				'messages_type' => gettype( $input['messages'] ?? 'undefined' ),
				'messages_value' => $input['messages'] ?? 'undefined'
			] );

			NodeResponse::error( 'Missing or invalid required parameters: ' . implode(', ', $missing), 400, [
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

			// Use messages directly with LLM
			$messages = $input['messages'];

			$this->log_info( "Starting AI processing", [
				'model'           => $model,
				'job_id'          => $input['job_id'] ?? 'unknown',
				'message_count'   => count( $messages ),
				'has_schema'      => isset( $input['format'] ) ? 'yes' : 'no',
				'has_options'     => isset( $input['options'] ) ? 'yes' : 'no'
			] );

			// Make the API call using LLM integration
			NodeResponse::mark( 'llm_call' );
			$response = $this->callLLM( $messages, $input );

			// Log performance metrics
			$this->log_info( "AI processing completed successfully", [
				'job_id'           => $input['job_id'] ?? 'unknown',
				'model'            => $response['model'] ?? $model,
				'provider'         => $response['provider'] ?? 'unknown',
				'duration'         => $response['duration'] ?? 0,
				'response_length'  => strlen( $response['response'] ),
			] );

			// Use NodeResponse::prompt for standardized response
			NodeResponse::prompt(
				trim( $response['response'] ),
				$response['model'] ?? $model,
				$response, // Pass full response for stats
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
