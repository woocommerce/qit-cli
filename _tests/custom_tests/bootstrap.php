<?php

use Symfony\Component\Process\Process;

function qit( array $command, array $qit_env_json = [], int $expected_exit_code = 0 ): string {
	if ( ! empty( $qit_env_json ) ) {
		if ( ! file_put_contents( __DIR__ . '/qit-env.json', json_encode( $qit_env_json ) ) ) {
			throw new \RuntimeException( 'Failed to write to file.' );
		}
	}

	$args = [ $GLOBALS['qit'] ];
	$args = array_merge( $args, $command );
	$qit  = new Process( $args );
	$qit->setEnv( [
		'QIT_HOME'            => $GLOBALS['QIT_HOME'],
		'QIT_DISABLE_CLEANUP' => '1', // We need to disable it because of parallelization with individualized QIT_HOMEs.
	] );
	$qit->run();

	if ( $qit->getExitCode() !== $expected_exit_code ) {
		throw new \RuntimeException( sprintf( "Command \"%s\" failed with exit code %d. \n\nError Output:\n %s \n\nOutput:\n %s", implode( ' ', $command ), $qit->getExitCode(), $qit->getErrorOutput(), $qit->getOutput() ) );
	}

	return $qit->getOutput();
}