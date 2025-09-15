<?php

namespace QIT\IntegrationTests\TestPackages\Security;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for secret format validation in manifests.
 * 
 * These tests ensure that:
 * - Key-value pairs are rejected (security issue - might contain actual secrets)
 * - Clear error messages explain the security concern
 * - Correct format (array of environment variable names) works correctly
 */
class SecretFormatValidationTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-secret-format-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * Test that key-value format triggers security error.
	 */
	public function test_key_value_format_shows_security_error(): void {
		$packageDir = $this->createPackageWithKeyValueSecrets();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		// Should fail with our security error message
		$this->assertNotEquals( 0, $proc->getExitCode(), 
			'Should fail when using key-value format (potential security issue)' );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// The error may be wrapped in JSON, so decode if needed
		$errorMessage = $output;
		if ( preg_match( '/\{"error":"[^"]+","output":"(.+)"\}/', $output, $matches ) ) {
			// Properly decode the JSON-escaped string
			$decoded = json_decode( '{"msg":"' . $matches[1] . '"}', true );
			if ( $decoded && isset( $decoded['msg'] ) ) {
				$errorMessage = $decoded['msg'];
			}
		}
		
		// Should contain our concise error message
		$this->assertStringContainsString( 'Invalid secrets format', $errorMessage );
		$this->assertStringContainsString( 'must be an array of environment variable names, not key-value pairs', $errorMessage );
		$this->assertStringContainsString( 'Wrong:   "secrets": {"API_KEY": "value"}', $errorMessage );
		$this->assertStringContainsString( 'Correct: "secrets": ["API_KEY"]', $errorMessage );
		$this->assertStringContainsString( 'provided as environment variables when running', $errorMessage );
		
		// Should NOT show the generic "Schema validation failed" message
		$this->assertStringNotContainsString( 'Schema validation failed', $output );
	}

	/**
	 * Test that the exact error message is shown without generic schema validation prefix.
	 * This ensures users see the security warning immediately.
	 */
	public function test_exact_error_message_without_schema_prefix(): void {
		$packageDir = $this->createPackageWithKeyValueSecrets();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// The error may be wrapped in JSON, so decode if needed
		$errorMessage = $output;
		if ( preg_match( '/\{"error":"[^"]+","output":"(.+)"\}/', $output, $matches ) ) {
			// Properly decode the JSON-escaped string
			$decoded = json_decode( '{"msg":"' . $matches[1] . '"}', true );
			if ( $decoded && isset( $decoded['msg'] ) ) {
				$errorMessage = $decoded['msg'];
			}
		}
		
		// The error should start with our custom message, not "Schema validation failed"
		$lines = explode("\n", $errorMessage);
		$found_invalid_format = false;
		$found_schema_validation = false;
		
		foreach ($lines as $line) {
			if (strpos($line, 'Invalid secrets format') !== false) {
				$found_invalid_format = true;
			}
			if (strpos($line, 'Schema validation failed') !== false) {
				$found_schema_validation = true;
			}
		}
		
		$this->assertTrue($found_invalid_format, 'Custom error message should be present');
		$this->assertFalse($found_schema_validation, 'Generic "Schema validation failed" should NOT be present');
		
		// Verify the complete message is shown
		$this->assertStringContainsString('Invalid secrets format', $errorMessage);
		$this->assertStringContainsString('Secrets must be an array of environment variable names, not key-value pairs', $errorMessage);
		$this->assertStringContainsString('Wrong:   "secrets": {"API_KEY": "value"}', $errorMessage);
		$this->assertStringContainsString('Correct: "secrets": ["API_KEY"]', $errorMessage);
		$this->assertStringContainsString('provided as environment variables when running the test', $errorMessage);
	}

	/**
	 * Test that new format works correctly.
	 */
	public function test_new_format_is_accepted(): void {
		// Set required secrets
		putenv( 'TEST_API_KEY=test-key' );
		
		$packageDir = $this->createPackageWithNewSecretFormat();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		// Should not have format error
		$output = $proc->getOutput() . $proc->getErrorOutput();
		$this->assertStringNotContainsString( 'Invalid secrets format', $output );
		
		// Clean up
		putenv( 'TEST_API_KEY' );
	}

	// ========== Helper Methods ==========

	private function createPackageWithKeyValueSecrets(): string {
		$packageDir = $this->fixturesDir . '/key-value-format';
		mkdir( $packageDir, 0755, true );
		
		// Intentionally use key-value format to trigger security error
		// This simulates someone mistakenly putting actual secrets in the manifest
		$manifest = [
			'package' => 'test/insecure-secrets',
			'test_type' => 'e2e',
			'description' => 'Package with potential security issue',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY' => 'sk_test_123456789',  // NEVER DO THIS!
					'TEST_SECRET' => 'super-secret-password'  // This is a security risk!
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Test"'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createPackageWithNewSecretFormat(): string {
		$packageDir = $this->fixturesDir . '/new-format';
		mkdir( $packageDir, 0755, true );
		
		$ctrf = \QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf();
		
		$manifest = [
			'package' => 'test/new-secret-format',
			'test_type' => 'e2e',
			'description' => 'Package with new secret format',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Test" && mkdir -p ./results && echo \'' . json_encode($ctrf) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
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