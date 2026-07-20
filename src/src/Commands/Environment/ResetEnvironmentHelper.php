<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

/**
 * Shared constants and the small in-container helper used by env:reset.
 *
 * The helper keeps the database import and object-cache flush in one Docker execution while
 * retaining phase-level timings and explicit failure reporting.
 */
class ResetEnvironmentHelper {
	public const CONTAINER_SNAPSHOT_PATH = '/tmp/qit-env-reset/setup-complete.sql';
	public const CONTAINER_HELPER_PATH   = '/qit/bin/qit-env-reset.php';

	/**
	 * Return the PHP helper staged through the environment's existing /qit/bin bind mount.
	 */
	public static function script(): string {
		return <<<'PHP'
<?php

$started = microtime( true );
$phases  = [
	'database_import'    => [ 'status' => 'not_started', 'seconds' => 0.0 ],
	'object_cache_flush' => [ 'status' => 'not_started', 'seconds' => 0.0 ],
];

/**
 * @return float
 */
function qit_reset_elapsed( float $phase_started ): float {
	return round( microtime( true ) - $phase_started, 6 );
}

/**
 * @return array{exit_code:int,stdout:string,stderr:string}
 */
function qit_reset_run( string $command ): array {
	$descriptors = [
		1 => [ 'pipe', 'w' ],
		2 => [ 'pipe', 'w' ],
	];
	$pipes       = [];
	$process     = proc_open( $command, $descriptors, $pipes, '/var/www/html' );
	if ( ! is_resource( $process ) ) {
		return [ 'exit_code' => 1, 'stdout' => '', 'stderr' => 'Unable to start reset command.' ];
	}

	$stdout = stream_get_contents( $pipes[1] );
	$stderr = stream_get_contents( $pipes[2] );
	fclose( $pipes[1] );
	fclose( $pipes[2] );

	return [
		'exit_code' => proc_close( $process ),
		'stdout'    => is_string( $stdout ) ? $stdout : '',
		'stderr'    => is_string( $stderr ) ? $stderr : '',
	];
}

/**
 * @param array<string,array{status:string,seconds:float}> $phase_values
 */
function qit_reset_finish( string $status, ?string $failed_phase, string $message, array $phase_values ): void {
	global $started;
	echo json_encode( [
		'status'       => $status,
		'failed_phase' => $failed_phase,
		'message'      => $message,
		'total_seconds' => qit_reset_elapsed( $started ),
		'phases'       => $phase_values,
	], JSON_UNESCAPED_SLASHES );
	exit( 0 );
}

$snapshot = $argv[1] ?? '';
$checksum = $argv[2] ?? '';

$phase_started = microtime( true );
if (
	$snapshot === '' ||
	$checksum === '' ||
	! is_readable( $snapshot ) ||
	! hash_equals( $checksum, (string) hash_file( 'sha256', $snapshot ) )
) {
	$phases['database_import'] = [ 'status' => 'failed', 'seconds' => qit_reset_elapsed( $phase_started ) ];
	qit_reset_finish( 'failed', 'database_import', 'The staged database snapshot is missing or failed checksum verification.', $phases );
}

$import = qit_reset_run(
	'wp db import ' . escapeshellarg( $snapshot ) . ' --defaults --quiet'
);
$phases['database_import'] = [
	'status'  => $import['exit_code'] === 0 ? 'completed' : 'failed',
	'seconds' => qit_reset_elapsed( $phase_started ),
];
if ( $import['exit_code'] !== 0 ) {
	$message = trim( $import['stderr'] !== '' ? $import['stderr'] : $import['stdout'] );
	qit_reset_finish( 'failed', 'database_import', substr( $message, -2000 ), $phases );
}

$phase_started = microtime( true );
$flush         = qit_reset_run( 'wp cache flush --quiet' );
$phases['object_cache_flush'] = [
	'status'  => $flush['exit_code'] === 0 ? 'completed' : 'failed',
	'seconds' => qit_reset_elapsed( $phase_started ),
];
if ( $flush['exit_code'] !== 0 ) {
	$message = trim( $flush['stderr'] !== '' ? $flush['stderr'] : $flush['stdout'] );
	qit_reset_finish( 'failed', 'object_cache_flush', substr( $message, -2000 ), $phases );
}

qit_reset_finish( 'success', null, '', $phases );
PHP;
	}
}
