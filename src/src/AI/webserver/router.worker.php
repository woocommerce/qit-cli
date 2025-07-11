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
qit_llm_boot( [
	'temperature' => $input['temperature'] ?? null,
	'max_tokens'  => $input['max_tokens'] ?? null,
] );

use QIT_AI_Webserver\Lib\CallbackSender;
use QIT_AI_Webserver\Endpoints\{
	BasicPromptEndpoint,
	PromptWithToolsEndpoint,
	ZipExtractionEndpoint,
	FileReadingEndpoint,
	VulnerabilityScanEndpoint
};

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

if ( $method === 'POST' && $uri === '/run-job' ) {
	$task = $input;                    // already validated by listener
	$type = $task['type'];

	$start = microtime( true );
	try {
		$result = $endpoints[ $type ]->handle( $task );      // ← same endpoint map you already have
		$ok     = $callback_sender->sendCallback(
			$task['callback_url'],
			$task['action_id'] ?? $task['job_id'],
			json_decode( $result, true ),
			(int) ( ( microtime( true ) - $start ) * 1000 )
		);
		if ( ! $ok ) throw new RuntimeException( 'callback failed' );
		http_response_code( 200 );
		echo '{"status":"done"}';
	} catch ( \Throwable $e ) {
		$callback_sender->sendErrorCallback(
			$task['callback_url'],
			$task['action_id'] ?? $task['job_id'],
			$e->getMessage()
		);
		http_response_code( 500 );
		echo '{"error":"' . $e->getMessage() . '"}';
	} finally {
		// clear busy flag so the next /process can succeed
		@unlink( getenv( 'QIT_NODE_DIR' ) . '/busy.lock' );
	}
	return;
}

http_response_code( 404 );
echo json_encode( [ 'error' => 'Route not found on Worker' ] );
