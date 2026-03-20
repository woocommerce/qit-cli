<?php

// Load composer autoloader for integration test dependencies
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Process\Process;

// Define the root directory for integration tests to avoid relative path issues
define( 'QIT_INTEGRATION_TESTS_ROOT', __DIR__ );

// Generate a unique process identifier that works for both serial and parallel execution
// For paratest: use TEST_TOKEN (e.g., "1", "2", "3")
// For serial: use a unique ID
$process_id = getenv( 'TEST_TOKEN' ) ?: uniqid( 'serial-' );
define( 'QIT_TEST_PROCESS_ID', $process_id );

// Set up required global variables for integration tests
$GLOBALS['qit-php']  = __DIR__ . '/../../../src/qit-cli.php';
$GLOBALS['QIT_HOME'] = sys_get_temp_dir() . '/qit-test-' . uniqid();

// Clean up stale lock file from crashed previous runs.
$lock_file = sys_get_temp_dir() . '/test-initialization-lock-file';
if ( file_exists( $lock_file ) && filemtime( $lock_file ) < time() - 600 ) {
	unlink( $lock_file );
}

// Clean up stale qit-test-* directories from PREVIOUS runs (older than 10 minutes).
// Must be long enough that running tests don't get cleaned up mid-flight.
// Triple-guarded: age check + realpath prefix check + basename regex.
$cleanup_cutoff = time() - 600;
$cleanup_tmp    = realpath( sys_get_temp_dir() );
foreach ( glob( sys_get_temp_dir() . '/qit-test-*' ) as $cleanup_dir ) {
	if ( is_dir( $cleanup_dir )
		&& filemtime( $cleanup_dir ) < $cleanup_cutoff
		&& strpos( realpath( $cleanup_dir ), $cleanup_tmp ) === 0
		&& preg_match( '/^qit-test-[a-f0-9]+$/', basename( $cleanup_dir ) )
	) {
		exec( 'rm -rf ' . escapeshellarg( $cleanup_dir ) );
	}
}

if ( ! is_dir( '/tmp/qit' ) ) {
	mkdir( '/tmp/qit', 0755, true );
}

function qit( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false, bool $capture_stderr_separately = false ): string|Process {
	// Handle capture_stderr_separately option if it's in the config array
	if ( is_array( $qit_env_json ) && isset( $qit_env_json['capture_stderr_separately'] ) ) {
		$capture_stderr_separately = $qit_env_json['capture_stderr_separately'];
		unset( $qit_env_json['capture_stderr_separately'] );
	}
	
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
	// Don't add --trace on for run:e2e anymore since it's not a valid 
	// option for the test packages being run in the integration tests

	if ( isset( $extra_env['QIT_SELF_TEST'] ) && $extra_env['QIT_SELF_TEST'] === 'precommand' ) {
		$args[] = '--json';
	}


	$env = [
		'QIT_HOME'            => $GLOBALS['QIT_HOME'],
		'MANAGER_URL'         => $_ENV['QIT_CUSTOM_TESTS_URL'] ?? '', // Never hit production from tests
		'QIT_DISABLE_CLEANUP' => '1', // Disable cleanup during tests to prevent output pollution
		'QIT_SELF_TESTS'      => '1',
		'QIT_NO_PULL'         => '1',
		'CI'                  => '1',
		'COLUMNS'             => '300',  // Set a fixed width so that we can snapshot the output.
	];

	/*
	 * Add our helper mu-plugin, if applicable.
	 * To do this, we check if the command we are running have a "--volume" option.
	 * Only add it if we're not running run:e2e since it would be passed as runner_args
	 */
	if ( $command[0] !== 'run:e2e' ) {
		$volume_check = new Process( [ 'php', $GLOBALS['qit-php'], $command[0], '--help' ] );
		$volume_check->setEnv( $env );
		$volume_check->run();

		if ( strpos( $volume_check->getOutput(), '--volume' ) !== false ) {
			$args[] = '--volume';
			$args[] = sprintf( '%s:%s', __DIR__ . '/helpers/custom-test-mu-plugin.php', '/var/www/html/wp-content/mu-plugins/custom-test-mu-plugin.php' );
		}
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

		// Handle separate stderr capture for group:clear
		if ( $capture_stderr_separately ) {
			return [ $qit->getOutput(), $qit->getErrorOutput() ];
		}

		return $qit->getOutput();
	}
	
	// If we're returning the process, don't check exit code
	if ( $return_process ) {
		return $qit;
	}

	if ( $qit->getExitCode() !== $expected_exit_code ) {
		throw new \RuntimeException( sprintf( "Command \"%s\" failed with exit code %d. \n\nError Output:\n %s \n\nOutput:\n %s", implode( ' ', $command ), $qit->getExitCode(), $qit->getErrorOutput(), $qit->getOutput() ) );
	}

	// Handle separate stderr capture
	if ( $capture_stderr_separately ) {
		return [ $qit->getOutput(), $qit->getErrorOutput() ];
	}

	return $qit->getOutput();
}

/**
 * Runs a QIT env:up command but only executes the configuration resolution phase, returning the raw output.
 * This is much faster than running the full command, as it doesn't touch Docker or download anything.
 *
 * This function is useful for testing the configuration resolution phase without running the full command.
 * It allows tests to be split into different layers:
 * 1. Configuration tests (pure unit tests) - Only test the configuration resolution
 * 2. Command runtime tests (lightweight functional tests) - Test the command execution
 * 3. Full integration tests (slow, end-to-end scenarios) - Test the entire system
 *
 * This function has the same parameters as the `qit` function, but adds the 'QIT_SELF_TEST' => 'env_up'
 * environment variable to trigger the early-return mechanism in UpEnvironmentCommand.
 *
 * Example usage:
 * ```php
 * // Basic usage:
 * $output = qit_run_env_up(
 *     ['env:up', '--wp', '6.1', '--json']
 * );
 * $env = json_decode($output, true);
 * echo $env['wp']; // "6.1"
 *
 * // With configuration:
 * $output = qit_run_env_up(
 *     ['env:up', '--json'],
 *     <<<'JSON'
 * { "environments": { "default": { "wp": "6.0" } } }
 * JSON
 * );
 * $env = json_decode($output, true);
 *
 * // With custom exit code and environment variables:
 * $output = qit_run_env_up(
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
 * @return string The raw output from the configuration resolution phase.
 */
function qit_run_env_up( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false ): string|Process|array {
	// Add the QIT_SELF_TEST environment variable to trigger the early-return mechanism
	$extra_env = array_merge( $extra_env, [ 'QIT_SELF_TEST' => 'env_up' ] );

	// Handle capture_stderr_separately option if it's in the config array
	$capture_stderr_separately = false;
	if ( is_array( $qit_env_json ) && isset( $qit_env_json['capture_stderr_separately'] ) ) {
		$capture_stderr_separately = $qit_env_json['capture_stderr_separately'];
	}

	// Pass all parameters to the qit function and return its output directly
	return qit( $command, $qit_env_json, $expected_exit_code, $extra_env, $return_process, $capture_stderr_separately );
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
function qit_run_e2e( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false ): string|Process|array {
	// Add the QIT_SELF_TEST environment variable to trigger the env_info early-return mechanism
	$extra_env = array_merge( $extra_env, [ 'QIT_SELF_TEST' => 'run_e2e' ] );

	// Handle capture_stderr_separately option if it's in the config array
	$capture_stderr_separately = false;
	if ( is_array( $qit_env_json ) && isset( $qit_env_json['capture_stderr_separately'] ) ) {
		$capture_stderr_separately = $qit_env_json['capture_stderr_separately'];
	}

	// Pass all parameters to the qit function and return its output directly
	return qit( $command, $qit_env_json, $expected_exit_code, $extra_env, $return_process, $capture_stderr_separately );
}

/**
 * Runs a QIT remote test command but only executes the configuration resolution phase.
 * This is useful for testing remote test commands (run:security, run:phpstan, etc.)
 * without actually sending requests to the QIT Manager API.
 *
 * This function uses the 'QIT_SELF_TEST' => 'remote_test' environment variable
 * to trigger the early-return mechanism in CreateRunCommands.
 *
 * @param array $command Command line arguments to pass to QIT (should start with 'run:').
 * @param string|array|null $qit_env_json Optional JSON configuration or array to use.
 * @param int $expected_exit_code Expected exit code from the command.
 * @param array $extra_env Additional environment variables to pass to the command.
 *
 * @return string The raw JSON output from the options resolution phase.
 */
function qit_run_remote_test( array $command, $qit_env_json = [], int $expected_exit_code = 0, array $extra_env = [], bool $return_process = false ): string|Process|array {
	// Add --json flag to get clean JSON output without progress messages
	if ( ! in_array( '--json', $command, true ) ) {
		$command[] = '--json';
	}
	
	// Add the QIT_SELF_TEST environment variable to trigger the remote_test early-return mechanism
	$extra_env = array_merge( $extra_env, [ 'QIT_SELF_TEST' => 'remote_test' ] );

	// Handle capture_stderr_separately option if it's in the config array
	$capture_stderr_separately = false;
	if ( is_array( $qit_env_json ) && isset( $qit_env_json['capture_stderr_separately'] ) ) {
		$capture_stderr_separately = $qit_env_json['capture_stderr_separately'];
	}

	// Pass all parameters to the qit function and return its output directly
	return qit( $command, $qit_env_json, $expected_exit_code, $extra_env, $return_process, $capture_stderr_separately );
}
