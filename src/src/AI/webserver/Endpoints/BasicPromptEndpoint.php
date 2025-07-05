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

		try {
			// Get model and provider info from bootstrapped LLPhant integration
			$model    = \QIT_AI_Webserver\Lib\LLPhantBootstrap::getModel();
			$provider = \QIT_AI_Webserver\Lib\LLPhantBootstrap::getCurrentProvider();

			$messages = [];
			foreach ( $input['messages'] as $m ) {
				$messages[] = \LLPhant\Chat\Message::{$m['role']}( $m['content'] );
			}

			if ( ! empty( $input['response_format'] ) ) {
				$this->chat->setModelOption( 'response_format', $input['response_format'] );
			}

			$this->log_info( "Starting AI processing", [
				'job_id'        => $input['job_id'] ?? 'unknown',
				'message_count' => count( $messages ),
				'has_schema'    => isset( $input['response_format'] ) ? 'yes' : 'no',
				'has_options'   => isset( $input['options'] ) ? 'yes' : 'no'
			] );

			// Make the API call using chat with additional error handling
			NodeResponse::mark( 'llm_call' );
			$start = microtime( true );

			try {
				$result = $this->chat->generateChat( $messages );
			} catch ( \TypeError $e ) {
				// Check if the model exists.
				throw $e;
			}

			$elapsed = microtime( true ) - $start;

			$response = [
				'response' => trim( (string) $result ),
				'duration' => $elapsed,
				'model'    => $model,
				'provider' => $provider
			];

			// Log performance metrics
			$this->log_info( "AI processing completed successfully", [
				'job_id'          => $input['job_id'] ?? 'unknown',
				'model'           => $response['model'],
				'provider'        => $response['provider'],
				'duration'        => $response['duration'] ?? 0,
				'response_length' => strlen( $response['response'] ),
			] );

			// Use NodeResponse::prompt for standardized response
			NodeResponse::prompt(
				trim( $response['response'] ),
				$response['model'],
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
