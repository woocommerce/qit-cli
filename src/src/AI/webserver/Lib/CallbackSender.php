<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Sends results to Manager callback URLs
 */
class CallbackSender {
	/**
	 * Send successful result to callback URL
	 */
	public function send_callback(
		string $callback_url,
		string $action_id,
		array $response,
		?int $processing_time = null,
		array $tool_calls = [],
		array $metadata = [],
		?string $task_id = null
	): bool {
		$data = [
			'action_id'       => $action_id,
			'response'        => json_encode( $response ),
			'processing_time' => $processing_time,
			'tool_calls'      => $tool_calls,
			'metadata'        => $metadata,
		];

		if ( $task_id !== null ) {
			$data['task_id'] = $task_id;
		}

		$request = OutboundRequest::callback( $callback_url, $data, 'task-callback-request-success' );
		$result  = $request->send();

		return $result['success'];
	}

	/**
	 * Send error to callback URL
	 */
	public function send_error_callback(
		string $callback_url,
		string $action_id,
		string $error_message,
		?string $task_id = null
	): bool {
		$data = [
			'action_id'       => $action_id,
			'response'        => json_encode( [ 'error' => $error_message ] ),
			'processing_time' => 0,
			'tool_calls'      => [],
			'metadata'        => [ 'error' => true ],
		];

		if ( $task_id !== null ) {
			$data['task_id'] = $task_id;
		}

		$request = OutboundRequest::callback( $callback_url, $data, 'task-callback-request-error' );
		$result  = $request->send();

		return $result['success'];
	}
}
