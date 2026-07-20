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

	$stdout  = '';
	$stderr  = '';
	$streams = [
		'stdout' => $pipes[1],
		'stderr' => $pipes[2],
	];
	foreach ( $streams as $stream ) {
		stream_set_blocking( $stream, false );
	}

	while ( ! empty( $streams ) ) {
		$read     = array_values( $streams );
		$write    = null;
		$except   = null;
		$selected = stream_select( $read, $write, $except, 1 );
		if ( $selected === false ) {
			foreach ( $streams as $stream ) {
				fclose( $stream );
			}
			proc_terminate( $process );
			proc_close( $process );
			return [ 'exit_code' => 1, 'stdout' => $stdout, 'stderr' => 'Unable to read reset command output.' ];
		}

		foreach ( $read as $stream ) {
			$name  = array_search( $stream, $streams, true );
			$chunk = stream_get_contents( $stream );
			if ( $name === 'stdout' && is_string( $chunk ) ) {
				$stdout .= $chunk;
			} elseif ( $name === 'stderr' && is_string( $chunk ) ) {
				$stderr .= $chunk;
			}

			if ( $name !== false && feof( $stream ) ) {
				fclose( $stream );
				unset( $streams[ $name ] );
			}
		}
	}

	return [
		'exit_code' => proc_close( $process ),
		'stdout'    => $stdout,
		'stderr'    => $stderr,
	];
}

/**
 * @param array<string,array{status:string,seconds:float}> $phase_values
 */
function qit_reset_finish( string $status, ?string $failed_phase, string $message, array $phase_values, ?string $failure_code = null ): void {
	global $started;
	echo json_encode( [
		'status'       => $status,
		'failed_phase' => $failed_phase,
		'failure_code'  => $failure_code,
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
	! is_readable( $snapshot )
) {
	$phases['database_import'] = [ 'status' => 'failed', 'seconds' => qit_reset_elapsed( $phase_started ) ];
	qit_reset_finish( 'failed', 'database_import', 'The staged database snapshot is missing or unreadable.', $phases, 'snapshot_unavailable' );
}

$actual_checksum = hash_file( 'sha256', $snapshot );
if ( ! is_string( $actual_checksum ) || ! hash_equals( $checksum, $actual_checksum ) ) {
	$phases['database_import'] = [ 'status' => 'failed', 'seconds' => qit_reset_elapsed( $phase_started ) ];
	qit_reset_finish( 'failed', 'database_import', 'The staged database snapshot failed checksum verification.', $phases, 'snapshot_checksum_mismatch' );
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
