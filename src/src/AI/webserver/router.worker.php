<?php
/**
 * Worker router – bound to 127.0.0.1, processes jobs directly.
 */
require_once __DIR__ . '/bootstrap-node.php';
require_once __DIR__ . '/Handlers/helpers.php';   // worker needs helpers

$result  = qit_http_request( false );
$method  = $result['method'];
$uri     = $result['uri'];
$headers = $result['headers'];
$input   = $result['input'];

// Log inbound request to worker
log_info('Inbound request', [
    'method' => $method,
    'uri'    => $uri,
    'remote' => $_SERVER['REMOTE_ADDR'] ?? 'cli'
]);

qit_llm_boot( [
	'temperature' => $input['temperature'] ?? null,
	'max_tokens'  => $input['max_tokens'] ?? null,
] );

use QIT_AI_Webserver\Lib\CallbackSender;
use QIT_AI_Webserver\Endpoints\{
	BasicPromptEndpoint,
	ZipExtractionEndpoint,
	FileReadingEndpoint,
	VulnerabilityScanEndpoint
};

$endpoints = [
	'basic-prompt'       => new BasicPromptEndpoint(),
	'extract-zip'        => new ZipExtractionEndpoint(),
	'read-file'          => new FileReadingEndpoint(),
	'vulnerability-scan' => new VulnerabilityScanEndpoint(),
];

// Initialize callback sender - throw if environment variable is not available
if ( empty( getenv( 'QIT_NODE_TOKEN' ) ) ) {
	throw new \RuntimeException( "Environment variable QIT_NODE_TOKEN is not set" );
}

$callback_sender = new CallbackSender();

if ( $method === 'POST' && $uri === '/run-job' ) {
	$task = $input;                    // already validated by listener
	if (!isset($task['task_id'])) {
        throw new RuntimeException('Missing task_id in job payload');
    }
    $taskId = $task['task_id'];
	$callback_url = $task['callback_url'];
	$type = $task['type'];

	log_info('Starting job', ['job_id' => $taskId, 'type' => $type]);  // Add logging

	$start = microtime( true );
	try {
		$result = $endpoints[ $type ]->handle( $task );      // ← same endpoint map you already have

		$processing_time = round((microtime(true) - $start) * 1000);

		// Restore original parse/log
		log_info( 'Endpoint handler result', [
			'task_id'       => $taskId,
			'type'          => $type,
			'result_length' => strlen( $result ),
			'result_starts' => substr( $result, 0, 50 ) . '...',
			'processing_time_ms' => $processing_time
		] );

		$decoded_result = json_decode( $result, true );
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new RuntimeException("Invalid JSON response from endpoint: " . json_last_error_msg());
		}

		$tool_calls = $decoded_result['_tool_calls'] ?? [];
		$metadata = $decoded_result['_metadata'] ?? [];
		unset($decoded_result['_processing_time'], $decoded_result['_tool_calls'], $decoded_result['_metadata']);

		$ok = $callback_sender->sendCallback(
			$callback_url,
			$task['action_id'] ?? $taskId,
			$decoded_result,
			$processing_time,
			$tool_calls,
			$metadata,
			$taskId
		);
		if ( ! $ok ) throw new RuntimeException( 'callback failed' );
		http_response_code( 200 );
		echo '{"status":"done"}';
	} catch ( \Throwable $e ) {
		$callback_sender->sendErrorCallback(
			$callback_url,
			$task['action_id'] ?? $taskId,
			$e->getMessage(),
			$taskId
		);
		http_response_code( 500 );
		echo '{"error":"' . $e->getMessage() . '"}';
	} finally {
		// clear busy flag so the next /process can succeed
		@unlink( getenv( 'QIT_NODE_DIR' ) . '/busy.lock' );
	}

	log_info('Finished job', ['job_id' => $taskId]);  // Add logging
	return;
}

http_response_code( 404 );
echo json_encode( [ 'error' => 'Route not found on Worker' ] );
