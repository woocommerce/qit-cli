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

			// Convert messages to Ollama format
			$messages = $input['messages'];
			$systemMessage = '';
			$userPrompt = '';

			// Extract system and user messages
			foreach ( $messages as $message ) {
				if ( $message['role'] === 'system' ) {
					$systemMessage .= $message['content'] . "\n";
				} elseif ( $message['role'] === 'user' ) {
					$userPrompt .= $message['content'] . "\n";
				}
			}

			$this->log_info( "Starting AI processing", [
				'model'           => $model,
				'job_id'          => $input['job_id'] ?? 'unknown',
				'message_count'   => count( $messages ),
				'prompt_length'   => strlen( trim( $userPrompt ) ),
				'has_system'      => ! empty( trim( $systemMessage ) ) ? 'yes' : 'no',
				'has_schema'      => isset( $input['format'] ) ? 'yes' : 'no',
				'has_options'     => isset( $input['options'] ) ? 'yes' : 'no'
			] );

			$ollamaRequest = [
				'model'  => $model,
				'prompt' => trim( $userPrompt ),
				'stream' => false,
			];

			// Add system message if present
			if ( ! empty( trim( $systemMessage ) ) ) {
				$ollamaRequest['system'] = trim( $systemMessage );
			} else {
				$ollamaRequest['system'] = '/no_think'; // Disable thinking for models that support it
			}

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

			// Stop the model after the entire request is complete (per-request stopping)
			$this->stopOllamaModel( $model );

			// Use NodeResponse::prompt for standardized response
			NodeResponse::prompt(
				trim( $response['response'] ),
				$response['model'] ?? $model,
				$response, // Pass full response for token stats
				[ 'job_id' => $input['job_id'] ?? null ]
			);

		} catch ( Exception $e ) {
			// Stop the model even on error (per-request stopping)
			if ( isset( $model ) ) {
				$this->stopOllamaModel( $model );
			}

			$this->handleError( $e, [
				'job_id'   => $input['job_id'] ?? null,
				'model'    => $input['model'] ?? 'unknown',
				'job_type' => $input['type'] ?? 'unknown'
			] );
		}
	}
}
