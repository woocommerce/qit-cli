<?php
/**
 * poller.php
 * ----------
 * Stand‑alone worker‑poller for a QIT node.
 *
 * Usage:
 *     php poller.php <WORKER_URL>
 *
 * The node token must be provided through the environment variable
 *     QIT_NODE_TOKEN
 */

declare( strict_types=1 );

// Surface PHP fatals
error_reporting( E_ALL );
ini_set( 'display_errors', 'stderr' );

require_once __DIR__ . '/bootstrap-node.php';
qit_runtime_init();   // no HTTP, no LLM – just env‑validation & autoloader

require_once __DIR__ . '/Lib/HeartbeatSender.php';

use QIT_AI_Webserver\Lib\HeartbeatSender;

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "poller.php must be executed from the command line.\n" );
	exit( 1 );
}

/* ------------------------------------------------------------
 * 1. Command‑line & environment validation
 * ------------------------------------------------------------ */
$workerUrl = $argv[1] ?? null;
$nodeToken = getenv( 'QIT_NODE_TOKEN' ) ?: null;

if ( ! $workerUrl || ! $nodeToken ) {
	fwrite(
		STDERR,
		"Usage: php poller.php <WORKER_URL>\n" .
		"       (environment variable QIT_NODE_TOKEN must be set)\n"
	);
	exit( 1 );
}

/* ------------------------------------------------------------
 * 2. Initialize heartbeat sender if environment variables are available
 * ------------------------------------------------------------ */
$heartbeat           = null;
$nodeId              = getenv( 'QIT_NODE_ID' );
$managerHeartbeatUrl = getenv( 'QIT_MANAGER_HEARTBEAT_URL' );

if ( $nodeId && $nodeToken && $managerHeartbeatUrl ) {
	$heartbeat = new HeartbeatSender(
		$nodeId,
		$nodeToken,
		$managerHeartbeatUrl,
		60
	);
}

/* ------------------------------------------------------------
 * 3. Simple poll loop
 * ------------------------------------------------------------ */
$client = curl_init();
curl_setopt_array( $client, [
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json' ],
	CURLOPT_FAILONERROR    => false,  // Changed to false to capture body on HTTP errors
] );

$pollInterval = 1.0;            // seconds between /run-one calls
$idleStreak   = 0;              // track consecutive empty queue responses

while ( true ) {
	$payload = '{}';            // nothing is required in body
	curl_setopt_array( $client, [
		CURLOPT_URL        => $workerUrl . '/run-one',
		CURLOPT_POST       => true,
		CURLOPT_POSTFIELDS => $payload,
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/json',
			'X-Node-Token: ' . $nodeToken,
		],
	] );

	$response = curl_exec( $client );
	$httpCode = curl_getinfo( $client, CURLINFO_HTTP_CODE );

	if ( $response === false ) {
		$curlError = curl_error( $client );
		// Log pure cURL failures (e.g., connection issues)
		fwrite( STDERR, date( '[Y-m-d H:i:s] ' ) .
		                "Poller cURL error: $curlError\n" );
		fwrite( STDERR, "Check PHP error log at: " . getenv( 'QIT_NODE_DIR' ) . "/php-errors.log\n" );
		sleep( 5 );
		continue;
	}

	if ( $httpCode !== 200 ) {
		// Log HTTP errors with response body for debugging
		$errorMsg = "HTTP $httpCode";
		if ( ! empty( trim( $response ) ) ) {
			$errorMsg .= " - Response body: " . trim( $response );
		}
		fwrite( STDERR, date( '[Y-m-d H:i:s] ' ) .
		                "Poller error: $errorMsg\n" );
		fwrite( STDERR, "Check PHP error log at: " . getenv( 'QIT_NODE_DIR' ) . "/php-errors.log\n" );
		sleep( 5 );
		continue;
	}

	$data = json_decode( $response, true );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		fwrite( STDERR, date( '[Y-m-d H:i:s] ' ) .
		                "Poller error: Invalid JSON response - " . json_last_error_msg() . "\n" );
		sleep( 5 );
		continue;
	}

	if ( ! empty( $data['did_run'] ) ) {
		// Optional: log which task was processed
		fwrite( STDOUT, date( '[H:i:s] ' ) .
		                "Processed task " . ( $data['task_id'] ?? '(unknown)' ) . "\n" );
		$idleStreak = 0;  // reset when we actually worked
	} else {
		// nothing processed – exponential back-off, capped at 5 s
		$idleStreak = min( $idleStreak + 1, 5 );          // 1,2,3,4,5
		sleep( $idleStreak );
	}

	// Send heartbeat if initialized
	if ( $heartbeat ) {
		$heartbeat->maybeSend();
	}

	usleep( (int) ( $pollInterval * 1_000_000 ) );
}