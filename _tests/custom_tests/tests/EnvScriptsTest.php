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
		// Pass the --scripts env_started=... param so it writes a simple marker in /tmp/env_started.log
		$output = qit( [
			'env:up',
			'--scripts',
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
			'--scripts',
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
			'--scripts',
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
}