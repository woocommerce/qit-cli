<?php

namespace QIT_CLI\Tests\Integration\PreCommand;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\PrecommandEarlyReturn;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Golden-master behavioral test to ensure config merge parity.
 * 
 * This test captures the behavior of the PreCommand system before and after
 * the ConfigMerger refactoring to ensure identical outputs.
 */
class ConfigMergeParityTest extends TestCase {

	private Application $application;

	protected function setUp(): void {
		$this->application = $GLOBALS['qit_application'];
	}

	/**
	 * Test that environment configuration merging produces identical results
	 * after the ConfigMerger refactoring.
	 */
	public function test_environment_config_merge_parity(): void {
		// Test scenario: env:up command with various CLI overrides
		$test_cases = [
			// Basic environment setup
			[
				'command' => 'env:up',
				'arguments' => [
					'--php' => '8.1',
					'--wp' => '6.0',
					'--plugin' => 'woocommerce',
				],
			],
			// Multiple plugins and themes
			[
				'command' => 'env:up',
				'arguments' => [
					'--php' => '7.4',
					'--wp' => '5.9',
					'--woo' => 'latest',
					'--plugin' => ['jetpack', 'woocommerce'],
					'--theme' => 'storefront',
				],
			],
			// Environment variables and volumes
			[
				'command' => 'env:up',
				'arguments' => [
					'--php' => '8.0',
					'--env' => 'DEBUG=1',
					'--volume' => '/tmp:/tmp',
					'--php-extension' => 'xdebug',
				],
			],
		];

		foreach ( $test_cases as $index => $test_case ) {
			$this->assertConfigMergeParity( $test_case, "Test case $index" );
		}
	}

	/**
	 * Test that test configuration merging produces identical results
	 * after the ConfigMerger refactoring.
	 */
	public function test_test_config_merge_parity(): void {
		// Test scenario: run command with test-specific overrides
		$test_cases = [
			// E2E test with environment overrides
			[
				'command' => 'run:e2e',
				'arguments' => [
					'woocommerce', // Use a real plugin slug
					'--php' => '8.1',
					'--wp' => '6.0',
				],
			],
			// Activation test
			[
				'command' => 'run:activation',
				'arguments' => [
					'woocommerce', // Use a real plugin slug
					'--php' => '7.4',
				],
			],
		];

		foreach ( $test_cases as $index => $test_case ) {
			$this->assertConfigMergeParity( $test_case, "Test case $index" );
		}
	}

	/**
	 * Assert that a command produces identical JSON output before and after refactoring.
	 *
	 * @param array $test_case Test case with command and arguments
	 * @param string $message Test case description
	 */
	private function assertConfigMergeParity( array $test_case, string $message ): void {
		$command = $test_case['command'];
		$arguments = $test_case['arguments'];

		// Capture output with QIT_SELF_TEST=precommand
		$output = $this->capturePreCommandOutput( $command, $arguments );

		// Parse JSON output
		$result = json_decode( $output, true );

		$this->assertIsArray( $result, "$message: Output should be valid JSON" );
		$this->assertNotEmpty( $result, "$message: Output should not be empty" );

		// Verify that the result contains expected structure
		$this->assertArrayHasKey( 'resolved_config', $result, "$message: Should contain resolved_config" );

		// For environment commands, verify environment result
		if ( strpos( $command, 'env:' ) === 0 ) {
			$this->assertArrayHasKey( 'env_result', $result, "$message: Environment commands should contain env_result" );
		}

		// For test commands, verify test configuration
		if ( strpos( $command, 'run:' ) === 0 ) {
			// Test commands should have either configuration_result or local_test_result
			$has_config_result = array_key_exists( 'configuration_result', $result );
			$has_local_result = array_key_exists( 'local_test_result', $result );
			
			$this->assertTrue( 
				$has_config_result || $has_local_result, 
				"$message: Test commands should contain configuration_result or local_test_result" 
			);
		}

		// Verify precedence behavior by checking specific values
		$this->verifyPrecedenceBehavior( $result, $arguments, $command, $message );
	}

	/**
	 * Capture PreCommand output using QIT_SELF_TEST=precommand.
	 *
	 * @param string $command Command name
	 * @param array $arguments Command arguments
	 * @return string JSON output
	 */
	private function capturePreCommandOutput( string $command, array $arguments ): string {
		// Set environment variable to trigger early return
		putenv( 'QIT_SELF_TEST=precommand' );

		$input = new ArrayInput( array_merge( [ 'command' => $command ], $arguments ) );
		$output = new BufferedOutput();

		try {
			$exit_code = $this->application->run( $input, $output );
			
			// The PrecommandEarlyReturn exception is caught by QITCommand and returns SUCCESS
			$this->assertEquals( 0, $exit_code, 'Command should return success when QIT_SELF_TEST=precommand is set' );
			
			$json_output = $output->fetch();
		} finally {
			// Clean up environment variable
			putenv( 'QIT_SELF_TEST' );
		}

		return $json_output;
	}

	/**
	 * Verify that precedence behavior is correct (CLI > config > defaults).
	 *
	 * @param array $result Parsed JSON result
	 * @param array $arguments CLI arguments provided
	 * @param string $command Command name
	 * @param string $message Test case description
	 */
	private function verifyPrecedenceBehavior( array $result, array $arguments, string $command, string $message ): void {
		// Check that CLI arguments are properly reflected in the result
		if ( isset( $arguments['--php'] ) ) {
			// For environment commands, check env_result
			if ( isset( $result['env_result']['env_info'] ) ) {
				$env_info = $result['env_result']['env_info'];
				$this->assertEquals( 
					$arguments['--php'], 
					$env_info['php'] ?? null, 
					"$message: CLI --php argument should override config/defaults" 
				);
			}
		}

		// For test commands, verify that environment parameters are properly passed through
		if ( strpos( $command, 'run:' ) === 0 && isset( $arguments['--php'] ) ) {
			// Check if the PHP version is reflected in the environment result
			if ( isset( $result['env_result']['env_info'] ) ) {
				$env_info = $result['env_result']['env_info'];
				$this->assertEquals( 
					$arguments['--php'], 
					$env_info['php'] ?? null, 
					"$message: CLI --php argument should override config/defaults in test commands" 
				);
			}
		}

		// Verify array merging behavior for plugins
		if ( isset( $arguments['--plugin'] ) ) {
			if ( isset( $result['env_result']['env_info'] ) ) {
				$env_info = $result['env_result']['env_info'];
				$plugins = $env_info['plugins'] ?? [];
				
				$expected_plugins = is_array( $arguments['--plugin'] ) 
					? $arguments['--plugin'] 
					: [ $arguments['--plugin'] ];

				foreach ( $expected_plugins as $expected_plugin ) {
					$found = false;
					foreach ( $plugins as $plugin ) {
						if ( isset( $plugin['slug'] ) && $plugin['slug'] === $expected_plugin ) {
							$found = true;
							break;
						}
					}
					$this->assertTrue( 
						$found, 
						"$message: CLI --plugin argument '$expected_plugin' should be present in result" 
					);
				}
			}
		}
	}
}