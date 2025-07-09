<?php
/**
 * Worker router – bound to 127.0.0.1, processes one queued task.
 */
require_once __DIR__ . '/bootstrap-node.php';
require_once __DIR__ . '/Handlers/helpers.php';   // worker needs helpers

use QIT_AI_Webserver\Persistence\SleekTaskRepository;
use QIT_AI_Webserver\Endpoints\{
	BasicPromptEndpoint,
	PromptWithToolsEndpoint,
	ZipExtractionEndpoint,
	FileReadingEndpoint,
	VulnerabilityScanEndpoint
};

global $method, $uri;
$repo      = new SleekTaskRepository( QIT_NODE_DIR );
$endpoints = [
	'basic-prompt'       => new BasicPromptEndpoint(),
	'prompt-with-tools'  => new PromptWithToolsEndpoint(),
	'extract-zip'        => new ZipExtractionEndpoint(),
	'read-file'          => new FileReadingEndpoint(),
	'vulnerability-scan' => new VulnerabilityScanEndpoint(),
];

if ( $method === 'POST' && $uri === '/run-one' ) {
	$task = $repo->reserveNextPending();
	if (!$task) {
		echo json_encode(['did_run' => false]);
		return;
	}
	$taskId = $task['task_id'];

	try {
		$payload = $task['data'] ?? [];
		$type    = $task['type'];
		if ( ! isset( $endpoints[ $type ] ) ) {
			throw new RuntimeException( "Unsupported task type: $type" );
		}

		ob_start();
		$endpoints[ $type ]->handle( $payload );            // produces JSON
		$repo->markFinished( $taskId, ob_get_clean() );
	} catch ( \Throwable $e ) {
		$repo->markFinished( $taskId, [ 'error' => $e->getMessage() ] );
	}

	echo json_encode( [ 'did_run' => true, 'task_id' => $taskId ] );

	return;
}

http_response_code( 404 );
echo json_encode( [ 'error' => 'Route not found on Worker' ] );
