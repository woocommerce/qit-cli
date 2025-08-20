<?php

namespace QIT\IntegrationTests\Security;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for secret redaction in output.
 * 
 * These tests ensure that sensitive information is never exposed in:
 * - Command output
 * - Error messages
 * - Log files
 * - Debug information
 * 
 * This is critical for security when running in CI/CD environments
 * where logs might be publicly visible.
 */
class OutputRedactionTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-redaction-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
		
		// Clear any test environment variables
		putenv( 'TEST_API_KEY' );
		putenv( 'TEST_SECRET' );
	}

	protected function tearDown(): void {
		// Clear environment variables
		putenv( 'TEST_API_KEY' );
		putenv( 'TEST_SECRET' );
		parent::tearDown();
	}

	/**
	 * Test that secrets are redacted from command output.
	 * 
	 * When a package outputs secret values (accidentally or intentionally),
	 * they should be replaced with [REDACTED] in all output.
	 */
	public function test_secrets_are_redacted_from_output(): void {
		// Set secrets with known values
		$apiKey = 'super-secret-api-key-' . uniqid();
		$secret = 'my-password-' . uniqid();
		
		putenv( 'TEST_API_KEY=' . $apiKey );
		putenv( 'TEST_SECRET=' . $secret );

		$packageDir = $this->createPackageThatEchoesSecrets();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();
		
		// Secrets should NOT appear in output
		$this->assertStringNotContainsString( $apiKey, $output,
			'API key should be redacted from output' );
		$this->assertStringNotContainsString( $secret, $output,
			'Secret should be redacted from output' );
		
		// Instead, we should see redaction markers
		$this->assertStringContainsString( '[REDACTED]', $output,
			'Output should contain redaction markers' );
	}

	/**
	 * Test that partial secrets are also redacted.
	 * 
	 * If only part of a secret appears in output (e.g., in a URL),
	 * it should still be redacted.
	 */
	public function test_partial_secrets_are_redacted(): void {
		$apiKey = 'key_' . uniqid();
		putenv( 'TEST_API_KEY=' . $apiKey );

		$packageDir = $this->createPackageThatUsesSecretInUrl();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();
		
		// Even when part of a URL, secret should be redacted
		$this->assertStringNotContainsString( $apiKey, $output,
			'Secret should be redacted even when part of other strings' );
	}

	/**
	 * Test that redaction works in error output.
	 */
	public function test_secrets_redacted_from_error_output(): void {
		$secret = 'error-secret-' . uniqid();
		putenv( 'TEST_SECRET=' . $secret );

		$packageDir = $this->createPackageThatFailsWithSecret();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$errorOutput = $proc->getErrorOutput();
		
		// Secret should not appear in error output
		$this->assertStringNotContainsString( $secret, $errorOutput,
			'Secret should be redacted from error output' );
	}

	// ========== Helper Methods ==========

	private function createPackageThatEchoesSecrets(): string {
		$packageDir = $this->fixturesDir . '/echo-secrets';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/echo-secrets',
			'test_type' => 'e2e',
			'description' => 'Package that outputs secrets',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY' => 'API key',
					'TEST_SECRET' => 'Secret'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "API Key is: $TEST_API_KEY"',
						'host: echo "Secret is: $TEST_SECRET"',
						'host: echo "Combined: $TEST_API_KEY:$TEST_SECRET"'
					]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageThatUsesSecretInUrl(): string {
		$packageDir = $this->fixturesDir . '/secret-in-url';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/secret-url',
			'test_type' => 'e2e',
			'description' => 'Package using secret in URL',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY' => 'API key'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Calling API at https://api.example.com/endpoint?key=$TEST_API_KEY&action=test"'
					]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageThatFailsWithSecret(): string {
		$packageDir = $this->fixturesDir . '/fail-with-secret';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/fail-secret',
			'test_type' => 'e2e',
			'description' => 'Package that fails with secret in message',
			'requires' => [
				'secrets' => [
					'TEST_SECRET' => 'Secret'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Error: Authentication failed for $TEST_SECRET" && exit 1'
					]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createConfig( array $packages ): string {
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => $packages
					]
				]
			]
		];
		
		$tempDir = sys_get_temp_dir() . '/qit-config-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}