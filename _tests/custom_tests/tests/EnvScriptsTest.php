<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

/**
 * Tests for environment lifecycle scripts (env_started, env_stopped).
 */
class EnvScriptsTest extends TestCase {
	use SnapshotHelpers;

	/**
	 * Clean up environment after each test.
	 */
	protected function tearDown(): void {
		qit( [ 'env:down' ] );
		parent::tearDown();
	}

	/**
	 * #1: Test a CLI override of env_started script that creates a file in the container.
	 */
	public function test_env_up_with_env_started_cli_script() {
		// Pass the --script env_started=... param so it writes a simple marker in /tmp/env_started.log
		$output = qit( [
			'env:up',
			'--script',
			'env_started=echo "Script ran" > /tmp/env_started.log',
			'--json'
		] );

		// Parse the JSON output to get the env_id
		$info = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info, 'No env_id in the QIT JSON output.' );

		// env:exec inside the container to verify /tmp/env_started.log exists or has content
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/env_started.log'
		] );

		$this->assertStringContainsString(
			'Script ran',
			$exec_output,
			'env_started script did not run or did not create expected output.'
		);
	}

	/**
	 * #2: Test a CLI override of env_stopped script that creates a file in the container
	 * right before we tear down the environment.
	 *
	 * We'll run `env:down` afterwards, so we have to check logs/console output.
	 */
	public function test_env_up_with_env_stopped_cli_script() {
		$output = qit( [
			'env:up',
			'--script',
			'env_stopped=echo "Stopped script" > /tmp/env_stopped.log',
			'--json'
		] );
		$info   = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// Now manually down the environment
		$down_output = qit( [ 'env:down', '-v' ] );

		// We expect to see a line like "Running env_stopped script..." in the down output
		$this->assertStringContainsString(
			'Running env_stopped script...',
			$down_output,
			'Expected the env_stopped script message but did not see it.'
		);
	}

	/**
	 * #3: Test config-based env_started script. We'll create a temporary qit.yml that sets:
	 *
	 * scripts:
	 *   env_started: 'echo "ENV_STARTED_FROM_CONFIG" > /tmp/env_started_config.log'
	 *
	 * Then run `env:up`, ensure it picks up that config, and verify the file was created.
	 */
	public function test_env_up_with_env_started_in_config() {
		// Create a temporary qit.yml in a temp dir
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file_path = $config_dir . '/qit.yml';
		$config_contents  = <<<YML
scripts:
  env_started: 'echo "ENV_STARTED_FROM_CONFIG" > /tmp/env_started_config.log'
YML;

		file_put_contents( $config_file_path, $config_contents );

		// 2. Force QIT to use that file
		$output = qit( [
			'env:up',
			'--json',
			'--config',
			$config_file_path,
		] );
		$info   = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// 3. Check inside the container
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/env_started_config.log'
		] );

		$this->assertStringContainsString( 'ENV_STARTED_FROM_CONFIG', $exec_output );

		// Cleanup
		@unlink( $config_file_path );
		@rmdir( $config_dir );
	}

	/**
	 * #4: Test config-based env_stopped script. We do the same approach, but check
	 * the QIT console output for "env_stopped" logs. If you want to check a file
	 * inside Docker, you'd do it in the environment's `down()` method before containers go away.
	 */
	public function test_env_up_with_env_stopped_in_config() {
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file_path = $config_dir . '/qit.yml';
		$config_contents  = <<<YML
scripts:
  env_stopped: 'echo "ENV_STOPPED_FROM_CONFIG" > /tmp/env_stopped_config.log && cat /tmp/env_stopped_config.log'
YML;
		file_put_contents( $config_file_path, $config_contents );

		// Spin up
		$output = qit( [
			'env:up',
			'--json',
			'--config',
			$config_file_path,
		] );

		$info = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// Down the environment. The env_stopped script runs, so we check console output:
		$down_output = qit( [ 'env:down', '-v' ] );
		$this->assertStringContainsString( 'Running env_stopped script...', $down_output );
		$this->assertStringContainsString( 'ENV_STOPPED_FROM_CONFIG', $down_output );

		@unlink( $config_file_path );
		@rmdir( $config_dir );
	}

	public function test_env_stopped_script_overridden_by_cli() {
		// 1. Create a config that sets env_stopped to something we can detect
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file_path = $config_dir . '/qit.yml';
		$config_contents  = <<<YML
scripts:
  env_stopped: 'echo "CONFIG STOPPED"'
YML;
		file_put_contents( $config_file_path, $config_contents );

		// 2. Spin up but override with CLI
		// The CLI should override "CONFIG STOPPED" with "CLI STOPPED"
		$up_output = qit( [
			'env:up',
			'--json',
			'--config',
			$config_file_path,
			'--script',
			'env_stopped=echo "CLI STOPPED"'
		] );
		$info      = json_decode( $up_output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// 3. Down the environment
		$down_output = qit( [ 'env:down', '-v' ] );

		// We expect "CLI STOPPED" to appear, not "CONFIG STOPPED"
		$this->assertStringContainsString( 'Running env_stopped script...', $down_output );
		$this->assertStringContainsString( 'CLI STOPPED', $down_output );
		$this->assertStringNotContainsString( 'CONFIG STOPPED', $down_output );

		@unlink( $config_file_path );
		@rmdir( $config_dir );
	}

	public function test_env_started_local_sh_script_runs() {
		// 1. Create a local shell script in a temp directory
		$local_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $local_dir, 0755 );

		$script_file = $local_dir . '/my-started-script.sh';
		file_put_contents( $script_file, <<<'BASH'
#!/bin/bash
echo "HELLO_FROM_SCRIPT" > /tmp/local_script_ran.txt
BASH
		);
		chmod( $script_file, 0755 );

		// 2. Spin up environment, mounting this file as env_started
		//    The typical way is something like:
		//    qit env:up --volume local_script.sh:/var/www/html/.qit-lifecycle-my-script.sh --script env_started=./.qit-lifecycle-my-script.sh
		//    But you can replicate that with direct arguments or config.

		$output = qit( [
			'env:up',
			'--script',
			sprintf( 'env_started=%s', $script_file ), // If your code automatically sees a local file & sets volume
			'--json',
		] );

		$info = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// 3. Confirm the script created /tmp/local_script_ran.txt in the container
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/local_script_ran.txt'
		] );

		$this->assertStringContainsString( 'HELLO_FROM_SCRIPT', $exec_output );

		// 4. Cleanup
		@unlink( $script_file );
		@rmdir( $local_dir );
	}

	public function test_env_started_wp_cli_command() {
		// 1. We'll define a script that runs "wp core version" and logs it to /tmp/wp_version.txt
		//    This script can be inline in the config or as a local file.
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file_path = $config_dir . '/qit.yml';
		$config_contents  = <<<YML
scripts:
  env_started: 'wp core version > /tmp/wp_version.txt'
YML;
		file_put_contents( $config_file_path, $config_contents );

		// 2. Spin up environment with that config
		$up_output = qit( [
			'env:up',
			'--json',
			'--config',
			$config_file_path,
		] );
		$info      = json_decode( $up_output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// 3. Check inside container if /tmp/wp_version.txt was created
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/wp_version.txt'
		] );
		// The version might look like "6.2.2"
		$this->assertMatchesRegularExpression( '/\d+\.\d+(\.\d+)?/', $exec_output, 'Expected WP version string in /tmp/wp_version.txt' );

		// Cleanup
		@unlink( $config_file_path );
		@rmdir( $config_dir );
	}

	public function test_env_stopped_config_overridden_by_local_file() {
		// 1. Config sets "env_stopped: echo 'CONFIG STOPPED'"
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file = $config_dir . '/qit.yml';
		file_put_contents( $config_file, <<<YML
scripts:
  env_stopped: 'echo "CONFIG STOPPED"'
YML
		);

		// 2. Create a local .sh file that echoes "LOCAL STOPPED"
		$local_sh = $config_dir . '/my-stopped.sh';
		file_put_contents( $local_sh, <<<'SH'
#!/bin/bash
echo "LOCAL STOPPED"
SH
		);
		chmod( $local_sh, 0755 );

		// 3. Spin up with --config + --script env_stopped=the local file path
		$up_output = qit( [
			'env:up',
			'--json',
			'--config',
			$config_file,
			'--script',
			sprintf( 'env_stopped=%s', $local_sh ),
		] );
		$info      = json_decode( $up_output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// 4. Down the environment. We expect "LOCAL STOPPED", not "CONFIG STOPPED"
		$down_output = qit( [ 'env:down', '-v' ] );
		$this->assertStringContainsString( 'Running env_stopped script...', $down_output );
		$this->assertStringContainsString( 'LOCAL STOPPED', $down_output );
		$this->assertStringNotContainsString( 'CONFIG STOPPED', $down_output );

		// Cleanup
		@unlink( $local_sh );
		@unlink( $config_file );
		@rmdir( $config_dir );
	}

	public function test_env_started_script_fails_stops_environment_build() {
		// We'll define a small script that exits 1
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file = $config_dir . '/qit.yml';
		file_put_contents( $config_file, <<<YML
scripts:
  env_started: 'exit 1'
YML
		);

		// Try env:up. We expect it to fail with a non-zero exit or throw a RuntimeException
		try {
			qit( [
				'env:up',
				'--config',
				$config_file,
			] );
			// If it doesn't throw, that's unexpected:
			$this->fail( 'Expected the env_started script to fail and stop environment build, but it succeeded.' );
		} catch ( \RuntimeException $e ) {
			// Confirm the error message includes something about failing command
			$this->assertStringContainsString( 'Command not successul', $e->getMessage() );
		}

		// Cleanup
		@unlink( $config_file );
		@rmdir( $config_dir );
	}

	public function test_env_started_script_has_environment_variables() {
		// We define a small script that echoes $FOO to a file
		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		$config_file = $config_dir . '/qit.yml';
		file_put_contents( $config_file, <<<YML
scripts:
  env_started: 'echo "ENV_VAR_IS:\$FOO" > /tmp/env_foo_value.txt'
YML
		);

		// We pass --env FOO=HelloWorld to see if the script sees it
		$output = qit( [
			'env:up',
			'--env',
			'FOO=HelloWorld',
			'--config',
			$config_file,
			'--json'
		] );
		$info   = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// Now check inside the container for /tmp/env_foo_value.txt
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/env_foo_value.txt'
		] );
		$this->assertStringContainsString( 'ENV_VAR_IS:HelloWorld', $exec_output );

		// Cleanup
		@unlink( $config_file );
		@rmdir( $config_dir );
	}

	public function test_env_started_multi_line_script() {
		// We'll define an env_started script with multiple lines:
		// - Creates a new directory
		// - Writes a file inside it
		// - Then prints the file content

		$config_dir = sys_get_temp_dir() . '/env-script-test-' . uniqid();
		mkdir( $config_dir, 0755 );

		// In YAML, use the block scalar (|) to do multi-line
		$config_file     = $config_dir . '/qit.yml';
		$config_contents = <<<YML
scripts:
  env_started: |
    mkdir -p /tmp/multi-script-test
    echo "MULTI_LINE_SUCCESS" > /tmp/multi-script-test/result.txt
    cat /tmp/multi-script-test/result.txt
YML;
		file_put_contents( $config_file, $config_contents );

		// Spin up environment with this config
		$output = qit( [
			'env:up',
			'--json',
			'--config',
			$config_file,
		] );
		$info   = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info );

		// Now check inside container if the final file was created
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/multi-script-test/result.txt'
		] );
		$this->assertStringContainsString( 'MULTI_LINE_SUCCESS', $exec_output );

		// Cleanup
		@unlink( $config_file );
		@rmdir( $config_dir );
	}

	public function test_env_up_with_default_cli_script_action() {
		// Run "env:up" with --script but no "env_started=" prefix.
		$output = qit( [
			'env:up',
			'--script',
			'echo "Default action script ran" > /tmp/default_script_action.log',
			'--json',
		] );

		$info = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $info, 'No env_id in the QIT JSON output.' );

		// Now verify that the script ran as the env_started action by checking for
		// the file in the container.
		$exec_output = qit( [
			'env:exec',
			'--env_id',
			$info['env_id'],
			'cat /tmp/default_script_action.log',
		] );

		$this->assertStringContainsString(
			'Default action script ran',
			$exec_output,
			'The default (env_started) script did not run or did not create expected output.'
		);
	}
}