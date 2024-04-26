<?php

use Dotenv\Dotenv;
use Symfony\Component\Process\Process;

$dotenv = Dotenv::createImmutable( __DIR__ );
$dotenv->load();
$dotenv->required( [
	'QIT_CUSTOM_TESTS_USER',
	'QIT_CUSTOM_TESTS_USER_QIT_TOKEN',
	'QIT_CUSTOM_TESTS_SECRET',
	'QIT_CUSTOM_TESTS_URL',
	'QIT_CUSTOM_TESTS_ENV',
] );

if ( ! file_exists( __DIR__ . '/../../qit' ) ) {
	throw new \RuntimeException( sprintf( 'The qit binary was not found at %s.', realpath( __DIR__ . '/../../qit' ) ) );
}

$GLOBALS['qit'] = __DIR__ . '/../../qit';

// Generate an ID for this run.
$run_id      = uniqid( 'qit_custom_tests_' );
$qit_tmp_dir = __DIR__ . "/tmp_qit_config-$run_id";

$GLOBALS['QIT_HOME'] = $qit_tmp_dir;
$GLOBALS['RUN_ID']   = $qit_tmp_dir;

function qit( array $command, array $qit_env_json = [], int $expected_exit_code = 0 ): string {
	if ( ! empty( $qit_env_json ) ) {
		if ( ! file_put_contents( __DIR__ . '/qit-env.json', json_encode( $qit_env_json ) ) ) {
			throw new \RuntimeException( 'Failed to write to file.' );
		}
	}

	$args = [ $GLOBALS['qit'] ];
	$args = array_merge( $args, $command );
	$qit  = new Process( $args );
	$qit->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
	$qit->run();

	if ( $qit->getExitCode() !== $expected_exit_code ) {
		throw new \RuntimeException( sprintf( "Command \"%s\" failed with exit code %d. \n\nError Output:\n %s \n\nOutput:\n %s", implode( ' ', $command ), $qit->getExitCode(), $qit->getErrorOutput(), $qit->getOutput() ) );
	}

	return $qit->getOutput();
}