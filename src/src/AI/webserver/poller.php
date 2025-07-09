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
 * 2. Simple poll loop
 * ------------------------------------------------------------ */
$client = curl_init();
curl_setopt_array( $client, [
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json' ],
] );

$pollInterval = 1.0;            // seconds between /run-one calls
$idleStreak = 0;                // track consecutive empty queue responses

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

	if ( $httpCode !== 200 ) {
		fwrite( STDERR, date( '[Y-m-d H:i:s] ' ) .
		                "Poller error: HTTP $httpCode – " . trim( $response ) . "\n" );
		sleep( 5 );
		continue;
	}

	$data = json_decode( $response, true );
	if ( ! empty( $data['did_run'] ) ) {
		// Optional: log which task was processed
		fwrite( STDOUT, date( '[H:i:s] ' ) .
		                "Processed task " . ( $data['task_id'] ?? '(unknown)' ) . "\n" );
		$idleStreak = 0;  // reset when we actually worked
	} else {
		// nothing processed – exponential back-off, capped at 5 s
		$idleStreak = min($idleStreak + 1, 5);          // 1,2,3,4,5
		sleep($idleStreak);
	}

	usleep( (int) ( $pollInterval * 1_000_000 ) );
}
