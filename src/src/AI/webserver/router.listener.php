<?php
/**
 * Listener router – exposed (and tunnelled) HTTP interface.
 * Reads its configuration exclusively from environment variables.
 */
require_once __DIR__ . '/bootstrap-node.php';

$result  = qit_http_request( true );
$method  = $result['method'];
$uri     = $result['uri'];
$headers = $result['headers'];
$input   = $result['input'];
qit_llm_boot();                                     // listener sometimes proxies LLM

switch ( "$method $uri" ) {

	case 'POST /process':
		$task = $input ?? [];

		// Check if node is busy
		$busyFile = getenv( 'QIT_NODE_DIR' ) . '/busy.lock';
		if ( file_exists( $busyFile ) ) {
			http_response_code( 409 );                  // Node-Busy
			echo json_encode( [ 'error' => 'busy' ] );
			break;
		}

		// ── strict validation ─────────────────────────────────────────
		$allowed  = [
			'basic-prompt',
			'prompt-with-tools',
			'read-file',
			'extract-zip',
			'vulnerability-scan'
		];
		$required = [
			'basic-prompt'       => [ 'job_id', 'type', 'messages', 'model' ],
			'prompt-with-tools'  => [ 'job_id', 'type', 'messages', 'model' ],
			'read-file'          => [ 'job_id', 'type', 'file', 'extract_path', 'session_id' ],
			'extract-zip'        => [ 'job_id', 'type', 'zip_url', 'session_id' ],
			'vulnerability-scan' => [ 'job_id', 'type', 'vulnerability', 'model' ],
		];

		// Validate required fields including callback_url
		foreach ( [ 'job_id', 'type', 'callback_url' ] as $key ) {
			if ( ! isset( $task[ $key ] ) ) {
				http_response_code( 400 );
				echo json_encode( [ 'error' => "Missing required field: $key" ] );
				break 2;
			}
		}

		// Validate callback_url format
		if ( ! filter_var( $task['callback_url'], FILTER_VALIDATE_URL ) ) {
			http_response_code( 400 );
			echo json_encode( [ 'error' => 'Invalid callback_url format' ] );
			break;
		}

		if ( ! in_array( $task['type'], $allowed, true ) ) {
			http_response_code( 400 );
			echo json_encode( [ 'error' => "Unknown type: {$task['type']}" ] );
			break;
		}
		foreach ( $required[ $task['type'] ] as $key ) {
			if ( ! array_key_exists( $key, $task ) ) {
				http_response_code( 400 );
				echo json_encode( [ 'error' => "Missing required field for {$task['type']}: $key" ] );
				break 2;
			}
		}
		// ──────────────────────────────────────────────────────────────

		// Mark busy *before* forking
		file_put_contents( $busyFile, '1' );

		// Fire-and-forget call to worker
		$workerUrl = getenv( 'QIT_WORKER_URL' );
		$ch = curl_init( "$workerUrl/run-job" );
		curl_setopt_array( $ch, [
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => json_encode( $task ),
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json',
				'X-Node-Token: ' . getenv( 'QIT_NODE_TOKEN' )
			],
			// detach – we don't care about the reply
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_TIMEOUT_MS     => 1,
		] );
		curl_exec( $ch );
		curl_close( $ch );

		http_response_code( 202 );
		echo json_encode( [ 'status' => 'accepted' ] );
		break;

	default:
		http_response_code( 404 );
		echo json_encode( [ 'error' => 'Route not found on Listener. Method: ' . $method . ', URI: ' . $uri ] );
}
