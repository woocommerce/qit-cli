<?php

use Symfony\Component\Process\Process;

// Set up required global variables for integration tests
$GLOBALS['qit-php']  = __DIR__ . '/../../../src/qit-cli.php';
$GLOBALS['QIT_HOME'] = sys_get_temp_dir() . '/qit-test-' . uniqid();

if ( ! is_dir( '/tmp/qit' ) ) {
	mkdir( '/tmp/qit', 0755, true );
}

function qit( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false ): string|Process {
	if ( ! empty( $qit_env_json ) ) {
		if ( is_array( $qit_env_json ) ) {
			$qit_env_json = json_encode( $qit_env_json );
		}
		$qit_config_filename = sprintf( '%s/qit-env-%s.json', sys_get_temp_dir(), md5( $GLOBALS['QIT_HOME'] ) );
		if ( ! file_put_contents( $qit_config_filename, $qit_env_json ) ) {
			throw new \RuntimeException( 'Failed to write to file.' );
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

	if ( isset( $extra_env['QIT_SELF_TEST'] ) && $extra_env['QIT_SELF_TEST'] === 'precommand' ) {
		$args[] = '--json';
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
	$qit->setTimeout( 600 );
	$qit->setIdleTimeout( 600 );
	$qit->setTty( false );
	$qit->setPty( false );
	$qit->setEnv( $env );
	$qit->run();

	// Special case for group:clear command - don't throw an exception if it fails with "No group found"
	if ( $command[0] === 'group:clear' && $qit->getExitCode() === 1 && strpos( $qit->getOutput(), 'No group found' ) !== false ) {
		// This is fine - the group was already cleared or didn't exist
		if ( $return_process ) {
			return $qit;
		}

		return $qit->getOutput();
	}

	if ( $qit->getExitCode() !== $expected_exit_code ) {
		throw new \RuntimeException( sprintf( "Command \"%s\" failed with exit code %d. \n\nError Output:\n %s \n\nOutput:\n %s", implode( ' ', $command ), $qit->getExitCode(), $qit->getErrorOutput(), $qit->getOutput() ) );
	}

	if ( $return_process ) {
		return $qit;
	}

	return $qit->getOutput();
}

/**
 * Runs a QIT command but only executes the PreCommand phase, returning the raw output.
 * This is much faster than running the full command, as it doesn't touch Docker or download anything.
 *
 * This function is useful for testing the configuration resolution phase without running the full command.
 * It allows tests to be split into different layers:
 * 1. Pre-Command tests (pure unit tests) - Only test the configuration resolution
 * 2. Command runtime tests (lightweight functional tests) - Test the command execution
 * 3. Full integration tests (slow, end-to-end scenarios) - Test the entire system
 *
 * This function has the same parameters as the `qit` function, but adds the 'QIT_SELF_TEST' => 'precommand'
 * environment variable to trigger the early-return mechanism in PreCommandHandler.
 *
 * Example usage:
 * ```php
 * // Basic usage:
 * $output = qit_precommand(
 *     ['env:up', '--wp_version', '6.1', '--json']
 * );
 * $env = json_decode($output, true);
 * echo $env['wp_version']; // "6.1"
 *
 * // With configuration:
 * $output = qit_precommand(
 *     ['env:up', '--json'],
 *     <<<'JSON'
 * { "environments": { "default": { "wp_version": "6.0" } } }
 * JSON
 * );
 * $env = json_decode($output, true);
 *
 * // With custom exit code and environment variables:
 * $output = qit_precommand(
 *     ['env:up', '--json'],
 *     null,
 *     0,
 *     ['MY_CUSTOM_ENV' => 'value']
 * );
 * $env = json_decode($output, true);
 * ```
 *
 * @param array $command Command line arguments to pass to QIT.
 * @param string|array|null $qit_env_json Optional JSON configuration or array to use.
 * @param int $expected_exit_code Expected exit code from the command.
 * @param array $extra_env Additional environment variables to pass to the command.
 *
 * @return string The raw output from the PreCommand phase.
 */
function qit_run_env_up( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false ): string|Process {
	// Add the QIT_SELF_TEST environment variable to trigger the early-return mechanism
	$extra_env = array_merge( $extra_env, [ 'QIT_SELF_TEST' => 'env_up' ] );

	// Pass all parameters to the qit function and return its output directly
	return qit( $command, $qit_env_json, $expected_exit_code, $extra_env, $return_process );
}

/**
 * Runs a QIT run:e2e command but only executes up to the env_info creation phase.
 * This is useful for testing the configuration resolution and environment setup
 * without actually running Docker or tests.
 *
 * This function uses the 'QIT_SELF_TEST' => 'env_info' environment variable
 * to trigger the early-return mechanism in RunE2ECommand.
 *
 * @param array $command Command line arguments to pass to QIT (should start with 'run:e2e').
 * @param string|array|null $qit_env_json Optional JSON configuration or array to use.
 * @param int $expected_exit_code Expected exit code from the command.
 * @param array $extra_env Additional environment variables to pass to the command.
 *
 * @return string The raw JSON output from the env_info phase.
 */
function qit_run_e2e( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false ): string|Process {
	// Add the QIT_SELF_TEST environment variable to trigger the env_info early-return mechanism
	$extra_env = array_merge( $extra_env, [ 'QIT_SELF_TEST' => 'run_e2e' ] );

	// Pass all parameters to the qit function and return its output directly
	return qit( $command, $qit_env_json, $expected_exit_code, $extra_env, $return_process );
}
