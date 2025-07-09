<?php
/**
 * Listener router – exposed (and tunnelled) HTTP interface.
 * Reads its configuration exclusively from environment variables.
 */
require_once __DIR__ . '/bootstrap-node.php';   // ← single include

use QIT_AI_Webserver\Persistence\SleekTaskRepository;

global $method, $uri;
$repo = new SleekTaskRepository( QIT_NODE_DIR );

switch ( "$method $uri" ) {

	case 'POST /process':
		$task = $input ?? [];

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

		foreach ( [ 'job_id', 'type' ] as $key ) {
			if ( ! isset( $task[ $key ] ) ) {
				http_response_code( 400 );
				echo json_encode( [ 'error' => "Missing required field: $key" ] );
				break 2;
			}
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

		$repo->create( $task['job_id'], $task['type'], $task );
		http_response_code( 202 );
		echo json_encode( [ 'accepted' => true, 'task_id' => $task['job_id'] ] );
		break;

	default:
		if ( preg_match( '#^/status/([a-f0-9]+)$#', $uri, $m ) && $method === 'GET' ) {
			$status = $repo->get( $m[1] );
			if ( ! $status ) {
				http_response_code( 404 );
				echo json_encode( [ 'error' => 'Unknown task id' ] );
			} else {
				echo json_encode( $status );
			}
			break;
		}
		http_response_code( 404 );
		echo json_encode( [ 'error' => 'Route not found on Listener. Method: ' . $method . ', URI: ' . $uri ] );
}
