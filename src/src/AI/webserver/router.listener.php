<?php
/**
 * Listener router – exposed (and tunnelled) HTTP interface.
 * Reads its configuration exclusively from environment variables.
 */
require_once __DIR__ . '/bootstrap-node.php';
require_once __DIR__ . '/Lib/JsonSchemaValidator.php';

use QIT_AI_Webserver\Lib\JsonSchemaValidator;

$result = qit_http_request( true );
$method = $result['method'];
$uri    = $result['uri'];
// Normalise URI – PHP built-in server may append a trailing semicolon
// (e.g. "/process;"). Strip it so switch cases match consistently.
$uri     = rtrim( $uri, ';' );
$headers = $result['headers'];
$input   = $result['input'];

// Log every inbound HTTP request (excluding potentially large bodies)
log_info('Inbound request', [
	'method' => $method,
	'uri'    => $uri,
	'remote' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
]);

qit_llm_boot();                                     // listener sometimes proxies LLM

switch ( "$method $uri" ) {

	case 'POST /process':
		$task = $input ?? [];

		// Check if node is busy using atomic flock to prevent race conditions
		$busy_file = getenv( 'QIT_NODE_DIR' ) . '/busy.lock';
		$fp        = fopen( $busy_file, 'w' );
		if ( ! flock( $fp, LOCK_EX | LOCK_NB ) ) {  // Atomic check+lock
			fclose( $fp );
			http_response_code( 503 );  // Better than 409; Service Unavailable
			echo json_encode( [ 'error' => 'busy' ] );
			break;
		}
		// Hold lock briefly (release after forward attempt)

		// ── strict validation ─────────────────────────────────────────
		$allowed  = [
			'basic-prompt',
			'read-file',
			'extract-zip',
			'vulnerability-scan',
		];
		$required = [
			'basic-prompt'       => [ 'job_id', 'type', 'messages', 'model' ],
			'read-file'          => [ 'job_id', 'type', 'file', 'extract_path', 'session_id' ],
			'extract-zip'        => [ 'job_id', 'type', 'zip_url', 'session_id' ],
			'vulnerability-scan' => [ 'job_id', 'type', 'vulnerability', 'model' ],
		];

		// Validate required fields including callback_url
		foreach ( [ 'job_id', 'type', 'callback_url' ] as $key ) {
			if ( ! isset( $task[ $key ] ) ) {
				log_warning( 'Rejecting /process – missing field', [
					'field'  => $key,
					'job_id' => $task['job_id'] ?? 'unknown',
					'type'   => $task['type'] ?? 'unknown',
					'remote' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
				] );
				http_response_code( 400 );
				echo json_encode( [ 'error' => "Missing required field: $key" ] );
				break 2;
			}
		}

		// Validate callback_url format
		if ( ! filter_var( $task['callback_url'], FILTER_VALIDATE_URL ) ) {
			log_warning( 'Rejecting /process – invalid callback_url', [
				'callback_url' => $task['callback_url'],
				'job_id'       => $task['job_id'] ?? 'unknown',
			] );
			http_response_code( 400 );
			echo json_encode( [ 'error' => 'Invalid callback_url format' ] );
			break;
		}

		if ( ! in_array( $task['type'], $allowed, true ) ) {
			log_warning( 'Rejecting /process – unknown type', [
				'type'   => $task['type'],
				'job_id' => $task['job_id'] ?? 'unknown',
			] );
			http_response_code( 400 );
			echo json_encode( [ 'error' => "Unknown type: {$task['type']}" ] );
			break;
		}
		foreach ( $required[ $task['type'] ] as $key ) {
			if ( ! array_key_exists( $key, $task ) ) {
				log_warning( 'Rejecting /process – missing field for type', [
					'type'   => $task['type'],
					'field'  => $key,
					'job_id' => $task['job_id'] ?? 'unknown',
				] );
				http_response_code( 400 );
				echo json_encode( [ 'error' => "Missing required field for {$task['type']}: $key" ] );
				break 2;
			}
		}

		// JSON Schema validation
		$validator  = JsonSchemaValidator::getInstance();
		$validation = $validator->validateInbound( $task, $task['type'] );

		if ( ! $validation['valid'] ) {
			log_warning( 'JSON Schema validation failed', [
				'errors' => $validation['errors'],
				'job_id' => $task['job_id'] ?? 'unknown',
			] );
			http_response_code( 400 );
			$error_details = implode( '; ', $validation['errors'] );
			echo json_encode( [
				'error'   => 'JSON Schema validation failed: ' . $error_details,
				'details' => $validation['errors'],
			] );
			break;
		} else {
			log_debug('Inbound validation passed', [
				'job_id' => $task['job_id'] ?? 'unknown',
				'type'   => $task['type'],
			]);
		}
		// ──────────────────────────────────────────────────────────────

		// Fire-and-forget call to worker
		$worker_url = getenv( 'QIT_WORKER_URL' );
		$ch         = curl_init( "$worker_url/run-job" );
		curl_setopt_array( $ch, [
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => json_encode( $task ),
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json',
				'X-Node-Token: ' . getenv( 'QIT_NODE_TOKEN' ),
			],
			// detach – we don't care about the reply
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_TIMEOUT_MS     => 100,  // Increased from 1ms to 100ms for reliability
		] );
		curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			log_error( 'Forward to worker failed', [
				'curl_error' => curl_error( $ch ),
				'job_id'     => $task['job_id'] ?? 'unknown',
			] );
		}
		curl_close( $ch );

		// Release the lock after forward attempt
		flock( $fp, LOCK_UN );
		fclose( $fp );

		http_response_code( 202 );
		log_info( 'Accepted task for async processing', [
			'job_id' => $task['job_id'],
			'type'   => $task['type'],
		] );
		echo json_encode( [ 'status' => 'accepted' ] );
		break;

	/*
	──────────────────────────────────────────
	 * Internal: payload validation
	 * ──────────────────────────────────────────
	 */
	case 'POST /internal/register':
		// 0. Validate secret first
		$expected = getenv( 'QIT_INTERNAL_TOKEN' );
		$provided = $headers['x-internal-token'] ?? ( $headers['X-Internal-Token'] ?? null );

		if ( ! is_string( $expected ) || ! hash_equals( $expected, (string) $provided ) ) {
			http_response_code( 403 );
			echo '{"error":"forbidden"}';
			break;
		}

		$registration = $input;                    // raw JSON from CLI

		$validator  = JsonSchemaValidator::getInstance();
		$validation = $validator->validateOutbound( $registration, 'node-registration' );

		// Return validation result only
		http_response_code( 200 );
		echo json_encode( [
			'valid'  => $validation['valid'],
			'errors' => $validation['errors'] ?? [],
		] );
		break;

	/*
	──────────────────────────────────────────
	 *  NEW: Log bundle for remote debugging
	 *  Route:  POST /collect-logs
	 *  Auth:   X-Node-Token (already enforced by qit_http_request)
	 *  Body:   { "since": "2025-07-12T00:00:00Z", "glob": "*.log" }
	 *  Resp:   { "archive": "<base64(gzip(tar))>" }
	 *  Note:   Zero DB writes – temp files live inside QIT_NODE_DIR.
	 * ──────────────────────────────────────────
	 */
	case 'POST /collect-logs':
		try {
			$validator = JsonSchemaValidator::getInstance();
			$inbound   = $validator->validateInbound( $input ?? [], 'collect-logs' );
			if ( ! $inbound['valid'] ) {
				log_warning('collect-logs validation failed', [
					'errors' => $inbound['errors'],
				]);
				http_response_code( 400 );
				$error_details = implode( '; ', $inbound['errors'] );
				echo json_encode( [
					'error'   => 'schema_error: ' . $error_details,
					'details' => $inbound['errors'],
				] );
				break;
			} else {
				log_debug( 'collect-logs validation passed' );
			}

			$params = $input ?? [];
			$since  = isset( $params['since'] ) ? strtotime( $params['since'] ) : null;
			$glob   = $params['glob'] ?? '*.log';

			$log_dir = dirname( getenv( 'QIT_LOG_FILE' ) );
			$iter    = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $log_dir, RecursiveDirectoryIterator::SKIP_DOTS )
			);

			$files = [];
			foreach ( $iter as $f ) {
				if ( ! $f->isFile() ) {
					continue;
				}
				if ( ! fnmatch( $glob, $f->getFilename() ) ) {
					continue;
				}
				if ( $since && $f->getMTime() < $since ) {
					continue;
				}
				$files[] = $f->getPathname();
			}

			if ( $files === [] ) {
				$payload = [
					'status'  => 'no_logs',
					'archive' => null,
				];
				$validator->validateOutbound( $payload, 'collect-logs-response' ); // assert
				echo json_encode( $payload );
				break;
			}

			$tmp_base = rtrim( getenv( 'QIT_NODE_DIR' ), '/' );
			$tar_path = tempnam( $tmp_base, 'qit-logs-' ) . '.tar';
			$tar      = new PharData( $tar_path );
			foreach ( $files as $p ) {
				$tar->addFile( $p, basename( $p ) );
			}
			$tar->compress( Phar::GZ );
			unset( $tar );
			unlink( $tar_path );

			$gz_path = $tar_path . '.gz';
			$b64     = base64_encode( file_get_contents( $gz_path ) );
			unlink( $gz_path );

			$payload = [
				'status'  => 'ok',
				'archive' => $b64,
			];
			$validator->validateOutbound( $payload, 'collect-logs-response' );

			echo json_encode( $payload );
		} catch ( Throwable $e ) {
			http_response_code( 500 );
			echo json_encode( [
				'error'  => 'collect_logs_failed',
				'detail' => $e->getMessage(),
			] );
		}
		break;

	default:
		http_response_code( 404 );
		echo json_encode( [ 'error' => 'Route not found on Listener. Method: ' . $method . ', URI: ' . $uri ] );
}
