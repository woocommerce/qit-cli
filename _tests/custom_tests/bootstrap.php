<?php

use Symfony\Component\Process\Process;

function qit( array $command, array $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [] ): string {
	if ( ! empty( $qit_env_json ) ) {
		$qit_config_filename = sprintf( '%s/qit-env-%s.json', sys_get_temp_dir(), md5( $GLOBALS['QIT_HOME'] ) );
		if ( ! file_put_contents( $qit_config_filename, json_encode( $qit_env_json ) ) ) {
			throw new \RuntimeException( 'Failed to write to file.' );
		}
	}

	/*
	 * Everything is parallelized, except the QIT Manager back-end itself, which all instances connects to.
	 *
	 * If we use the same test tag on our self-tests, they will affect each other, with one test tag creating
	 * a tag and another deleting it because it finished theirs.
	 *
	 * The easiest solution is to use unique test tags for each test, but we can also create a simple lock system, like this one.
	 *
	 * If it's a tag upload, create a lock so that other processes wait until we delete it.
	 *
	 * Example:
	 * 	qit( [
	 *		'tag:upload',
	 *		'automatewoo:self-test-scaffolded',
	 *		$this->scaffold_test(),
	 *	] );
	 * Lock "automatewoo:self-test-scaffolded"
	 * When we receive qit( [ 'tag:delete', 'automatewoo:self-test-scaffolded' ] ) we unlock it.
	 *
	 * If we receive another command like the previous one while it's locked, wait.
	 */
	if ( $command[0] === 'tag:upload' ) {
		$lock_name = $command[1];
		$lock_file = sprintf( '%s/qit-test-tag-lock-%s', sys_get_temp_dir(), md5( $lock_name ) );
		$max_wait  = 60;
		$waited    = 0;
		while ( file_exists( $lock_file ) ) {
			sleep( 1 );
			$waited ++;
			if ( $waited > $max_wait ) {
				throw new \RuntimeException( sprintf( 'Timeout while waiting for the lock for %s', $lock_name ) );
			}
		}
		touch( $lock_file );
	} elseif ( $command[0] === 'tag:delete' ) {
		$lock_name = $command[1];
		$lock_file = sprintf( '%s/qit-test-tag-lock-%s', sys_get_temp_dir(), md5( $lock_name ) );
		if ( file_exists( $lock_file ) ) {
			unlink( $lock_file );
		}
	}

	$args = [ 'php', $GLOBALS['qit-php'] ];
	$args = array_merge( $args, $command );
	if ( ! empty( $qit_env_json ) ) {
		$args[] = '--config';
		$args[] = $qit_config_filename;
	}
	if ( $command[0] === 'run:e2e' ) {
		$args[] = '--pw_options';
		$args[] = '"--trace on"';
	}

	$env = [
		'QIT_HOME'            => $GLOBALS['QIT_HOME'],
		'QIT_DISABLE_CLEANUP' => '1', // We need to disable it because of parallelization with individualized QIT_HOMEs.
		'QIT_SELF_TESTS'      => '1',
		'QIT_NO_PULL'         => '1',
		'CI'                  => '1',
		'COLUMNS'             => '300',  // Set a fixed width so that we can snapshot the output.
	];

	/*
	 * Add our helper mu-plugin, if applicable.
	 * To do this, we check if the command we are running have a "--volume" option.
	 */
	$volume_check = new Process( [ 'php', $GLOBALS['qit-php'], $command[0], '--help' ] );
	$volume_check->setEnv( $env );
	$volume_check->run();

	if ( strpos( $volume_check->getOutput(), '--volume' ) !== false ) {
		$args[] = '--volume';
		$args[] = sprintf( '%s:%s', __DIR__ . '/helpers/custom-test-mu-plugin.php', '/var/www/html/wp-content/mu-plugins/custom-test-mu-plugin.php' );
	}

	$env = array_merge( $env, $extra_env );

	$qit = new Process( $args );
	$qit->setTimeout( 300 );
	$qit->setIdleTimeout( 300 );
	$qit->setTty( false );
	$qit->setPty( false );
	$qit->setEnv( $env );
	$qit->run();

	if ( $qit->getExitCode() !== $expected_exit_code ) {
		throw new \RuntimeException( sprintf( "Command \"%s\" failed with exit code %d. \n\nError Output:\n %s \n\nOutput:\n %s", implode( ' ', $command ), $qit->getExitCode(), $qit->getErrorOutput(), $qit->getOutput() ) );
	}

	return $qit->getOutput();
}