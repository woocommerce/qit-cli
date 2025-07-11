<?php
/**
 * Worker router – bound to 127.0.0.1, processes one queued task.
 */
require_once __DIR__ . '/bootstrap-node.php';
require_once __DIR__ . '/Handlers/helpers.php';   // worker needs helpers

$result  = qit_http_request( false );
$method  = $result['method'];
$uri     = $result['uri'];
$headers = $result['headers'];
$input   = $result['input'];
qit_llm_boot( [
	'temperature' => $input['temperature'] ?? null,
	'max_tokens'  => $input['max_tokens'] ?? null,
] );

use QIT_AI_Webserver\Persistence\SleekTaskRepository;
use QIT_AI_Webserver\Lib\CallbackSender;
use QIT_AI_Webserver\Endpoints\{
	BasicPromptEndpoint,
	PromptWithToolsEndpoint,
	ZipExtractionEndpoint,
	FileReadingEndpoint,
	VulnerabilityScanEndpoint
};

$repo      = new SleekTaskRepository( getenv( 'QIT_NODE_DIR' ) );
$endpoints = [
	'basic-prompt'       => new BasicPromptEndpoint(),
	'prompt-with-tools'  => new PromptWithToolsEndpoint(),
	'extract-zip'        => new ZipExtractionEndpoint(),
	'read-file'          => new FileReadingEndpoint(),
	'vulnerability-scan' => new VulnerabilityScanEndpoint(),
];

// Initialize callback sender - throw if environment variable is not available
if ( empty( getenv( 'QIT_NODE_TOKEN' ) ) ) {
	throw new \RuntimeException( "Environment variable QIT_NODE_TOKEN is not set" );
}

$callback_sender = new CallbackSender();

if ( $method === 'POST' && $uri === '/run-one' ) {
	$task = $repo->reserveNextPending();
	if ( ! $task ) {
		echo json_encode( [ 'did_run' => false ] );

		return;
	}
	$taskId = $task['task_id'];

	try {
		$payload = $task['data'] ?? [];
		$callback_url = $payload['callback_url'] ?? null;

		if (!$callback_url) {
			throw new RuntimeException("Missing callback_url in task data");
		}

		$type = $task['type'];
		if ( ! isset( $endpoints[ $type ] ) ) {
			throw new RuntimeException( "Unsupported task type: $type" );
		}

		// Track processing time
		$start_time = microtime(true);

		// Get JSON response directly from endpoint handler
		$result = $endpoints[ $type ]->handle( $payload );  // returns JSON string

		$processing_time = round((microtime(true) - $start_time) * 1000);

		// Log the raw result from the endpoint
		log_info( 'Endpoint handler result', [
			'task_id'       => $taskId,
			'type'          => $type,
			'result_length' => strlen( $result ),
			'result_starts' => substr( $result, 0, 50 ) . '...',
			'processing_time_ms' => $processing_time
		] );

		$repo->markFinished( $taskId, $result );

		// Parse the result for the callback
		$decoded_result = json_decode( $result, true );
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new RuntimeException("Invalid JSON response from endpoint: " . json_last_error_msg());
		}

		// Extract additional data for callback
		$tool_calls = $decoded_result['_tool_calls'] ?? [];
		$metadata = $decoded_result['_metadata'] ?? [];

		// Remove internal fields from response
		unset($decoded_result['_processing_time'], $decoded_result['_tool_calls'], $decoded_result['_metadata']);

		// Send result to callback URL
		$success = $callback_sender->sendCallback(
			$callback_url,
			$payload['action_id'] ?? $taskId,
			$decoded_result,
			$processing_time,
			$tool_calls,
			$metadata
		);

		if (!$success) {
			throw new RuntimeException("Failed to send callback to Manager");
		}

		log_info( 'Callback sent successfully', [
			'task_id' => $taskId,
			'callback_url' => $callback_url,
			'processing_time_ms' => $processing_time
		] );

	} catch ( \Throwable $e ) {
		$error_message = $e->getMessage();
		$repo->markFinished( $taskId, [ 'error' => $error_message ] );

		// Send error to callback URL if available
		if (isset($callback_sender) && isset($callback_url)) {
			$callback_sender->sendErrorCallback(
				$callback_url,
				$payload['action_id'] ?? $taskId,
				$error_message
			);
		}

		log_info( 'Task failed', [
			'task_id' => $taskId,
			'error' => $error_message
		] );
	}

	echo json_encode( [ 'did_run' => true, 'task_id' => $taskId ] );

	return;
}

http_response_code( 404 );
echo json_encode( [ 'error' => 'Route not found on Worker' ] );
