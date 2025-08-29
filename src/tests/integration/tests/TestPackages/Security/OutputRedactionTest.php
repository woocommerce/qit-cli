<?php

namespace QIT\IntegrationTests\TestPackages\Security;

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
		putenv( 'HOST_SECRET' );
		putenv( 'DOCKER_SECRET' );
		putenv( 'NODE_SECRET' );
		
		// Clean up temp directories
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$this->rmdirRecursive( $dir );
			}
		}
		
		parent::tearDown();
	}
	
	private function rmdirRecursive( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->rmdirRecursive( $path ) : unlink( $path );
		}
		rmdir( $dir );
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
		$resultFile = sys_get_temp_dir() . '/redaction-test-' . uniqid() . '.txt';
		
		putenv( 'TEST_API_KEY=' . $apiKey );
		putenv( 'TEST_SECRET=' . $secret );

		$packageDir = $this->createPackageVerifyingRedaction( $apiKey, $secret, $resultFile );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], extra_env: [
			'TEST_API_KEY' => $apiKey,
			'TEST_SECRET' => $secret,
		], return_process: true );

		$output = $proc->getOutput();
		
		// Secrets should NOT appear in output
		$this->assertStringNotContainsString( $apiKey, $output,
			'API key should be redacted from output' );
		$this->assertStringNotContainsString( $secret, $output,
			'Secret should be redacted from output' );
		
		// Verify secrets were available to the test by checking the result file
		if ( file_exists( $resultFile ) ) {
			$result = trim( file_get_contents( $resultFile ) );
			$this->assertEquals( 'SECRETS_AVAILABLE', $result, 'Secrets should have been available to test' );
			unlink( $resultFile );
		}
	}

	/**
	 * Test that secrets are properly available but redacted in output.
	 * 
	 * This test verifies:
	 * 1. Secrets are available to test packages (functionality works)
	 * 2. Secrets don't appear in raw form in any captured output
	 * 3. The test execution completes successfully with secrets
	 */
	public function test_secrets_functionality_and_redaction(): void {
		// Use unique secret values
		$hostSecret = 'host-secret-' . uniqid();
		$dockerSecret = 'docker-secret-' . uniqid(); 
		$nodeSecret = 'node-secret-' . uniqid();
		$verifyFile = sys_get_temp_dir() . '/secret-verify-' . uniqid() . '.txt';
		
		putenv( 'HOST_SECRET=' . $hostSecret );
		putenv( 'DOCKER_SECRET=' . $dockerSecret );
		putenv( 'NODE_SECRET=' . $nodeSecret );
		putenv( 'VERIFY_FILE=' . $verifyFile );

		$packageDir = $this->createPackageVerifyingSecretsWork( $verifyFile );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], extra_env: [
			'HOST_SECRET' => $hostSecret,
			'DOCKER_SECRET' => $dockerSecret,
			'NODE_SECRET' => $nodeSecret,
			'VERIFY_FILE' => $verifyFile,
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// Test should pass
		$this->assertEquals( 0, $proc->getExitCode(), 'Test should execute successfully with secrets' );
		
		// Raw secret values should NOT appear in any output
		$this->assertStringNotContainsString( $hostSecret, $output,
			'Host secret should be redacted from output' );
		$this->assertStringNotContainsString( $dockerSecret, $output,
			'Docker secret should be redacted from output' );
		$this->assertStringNotContainsString( $nodeSecret, $output,
			'Node secret should be redacted from output' );
		
		// Verify secrets were actually available by checking the verification file
		$this->assertFileExists( $verifyFile, 'Verification file should have been created' );
		$verifyContents = file_get_contents( $verifyFile );
		
		// The file should contain the actual secret values (proving they were available)
		$this->assertStringContainsString( $hostSecret, $verifyContents,
			'Host secret should have been available to the test' );
		$this->assertStringContainsString( $dockerSecret, $verifyContents,
			'Docker secret should have been available to the test' );
		$this->assertStringContainsString( $nodeSecret, $verifyContents,
			'Node secret should have been available to the test' );
		
		// Clean up
		if ( file_exists( $verifyFile ) ) {
			unlink( $verifyFile );
		}
	}

	/**
	 * Test that secrets are actually redacted from stdout output.
	 * 
	 * This test runs QIT directly and captures the actual stdout to verify
	 * that secrets printed by test packages are replaced with [REDACTED] markers.
	 */
	public function test_stdout_redaction_works(): void {
		$secret = 'secret-value-' . uniqid();
		$packageDir = $this->createSimplePackageThatEchoesSecret();
		$config = $this->createConfig( [ $packageDir ] );
		
		// Run QIT directly and capture actual output
		$qit_cli_path = '/storage/qit/qit-cli/src/qit-cli.php';
		$cmd = sprintf(
			'php %s run:e2e woocommerce --config=%s --env=MY_SECRET=%s 2>&1',
			$qit_cli_path,
			$config,
			$secret
		);
		
		$output = shell_exec( $cmd );
		
		// Critical assertions
		$this->assertStringNotContainsString( $secret, $output,
			'Raw secret value must not appear in stdout' );
		
		$this->assertStringContainsString( '[REDACTED:MY_SECRET]', $output,
			'Redacted marker must appear in stdout' );
		
		$this->assertStringContainsString( 'Secret is:', $output,
			'Command output must be captured' );
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
		], extra_env: [
			'TEST_API_KEY' => $apiKey,
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
		], extra_env: [
			'TEST_SECRET' => $secret,
		], return_process: true );

		$errorOutput = $proc->getErrorOutput();
		
		// Secret should not appear in error output
		$this->assertStringNotContainsString( $secret, $errorOutput,
			'Secret should be redacted from error output' );
	}

	// ========== Helper Methods ==========

	private function createSimplePackageThatEchoesSecret(): string {
		$packageDir = $this->fixturesDir . '/simple-echo';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/simple-echo',
			'test_type' => 'e2e',
			'description' => 'Simple package that echoes a secret',
			'requires' => [
				'secrets' => ['MY_SECRET']
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Secret is: $MY_SECRET"',
						'docker: echo "Docker: $MY_SECRET"',
						'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && touch ./blob-report/test.zip'
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

	private function createPackageVerifyingSecretsWork( string $verifyFile ): string {
		$packageDir = $this->fixturesDir . '/verify-secrets-work';
		mkdir( $packageDir, 0755, true );
		
		// Create a bash script that writes secrets to verification file
		$bashScript = <<<'BASH'
#!/bin/bash
# Write actual values to file to verify they're available
echo "HOST:$HOST_SECRET" >> $VERIFY_FILE
echo "DOCKER:$DOCKER_SECRET" >> $VERIFY_FILE
# Also output to stdout (should be redacted)
echo "Bash: The secrets are $HOST_SECRET and $DOCKER_SECRET"
BASH;
		file_put_contents( $packageDir . '/test.sh', $bashScript );
		chmod( $packageDir . '/test.sh', 0755 );
		
		// Create a Node.js script
		$nodeScript = <<<'JS'
const fs = require('fs');
// Write to verification file
fs.appendFileSync(process.env.VERIFY_FILE, 'NODE:' + process.env.NODE_SECRET + '\n');
// Also output to stdout (should be redacted)
console.log('Node: The secret is ' + process.env.NODE_SECRET);
JS;
		file_put_contents( $packageDir . '/test.js', $nodeScript );
		
		$manifest = [
			'package' => 'test/verify-secrets',
			'test_type' => 'e2e',
			'description' => 'Package that verifies secrets work while being redacted',
			'requires' => [
				'secrets' => [
					'HOST_SECRET',
					'DOCKER_SECRET',
					'NODE_SECRET',
					'VERIFY_FILE'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						// Direct host command - write and echo
						'host: echo "Direct HOST: $HOST_SECRET" && echo "DIRECT:$HOST_SECRET" >> $VERIFY_FILE',
						
						// Bash script
						'host: bash ./test.sh',
						
						// Docker command - echo secret (should be redacted in output)
						'docker: echo "Docker: $DOCKER_SECRET"',
						
						// Node.js script
						'host: node ./test.js',
						
						// Create required test artifacts
						'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function createPackageVerifyingRedaction( string $apiKey, string $secret, string $resultFile ): string {
		$packageDir = $this->fixturesDir . '/verify-redaction';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/verify-redaction',
			'test_type' => 'e2e',
			'description' => 'Package that verifies secrets are available but redacted',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY',
					'TEST_SECRET'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						// Verify secrets are available and write result
						'host: if [ -n "$TEST_API_KEY" ] && [ -n "$TEST_SECRET" ]; then echo "SECRETS_AVAILABLE" > ' . $resultFile . '; else echo "SECRETS_MISSING" > ' . $resultFile . '; fi',
						
						// Echo the secrets (these should be redacted in output)
						'host: echo "API Key value: $TEST_API_KEY"',
						'host: echo "Secret value: $TEST_SECRET"',
						
						// Create required test artifacts
						'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function createPackageThatEchoesSecrets(): string {
		$packageDir = $this->fixturesDir . '/echo-secrets';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/echo-secrets',
			'test_type' => 'e2e',
			'description' => 'Package that outputs secrets',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY',
					'TEST_SECRET'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						// Echo secrets that should be redacted
						'host: echo "The API key is: $TEST_API_KEY"',
						'host: echo "The secret is: $TEST_SECRET"',
						
						// Create required test artifacts
						'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function createPackageThatUsesSecretInUrl(): string {
		$packageDir = $this->fixturesDir . '/secret-in-url';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/secret-url',
			'test_type' => 'e2e',
			'description' => 'Package using secret in URL',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Calling API at https://api.example.com/endpoint?key=$TEST_API_KEY&action=test"',
						'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function createPackageThatFailsWithSecret(): string {
		$packageDir = $this->fixturesDir . '/fail-with-secret';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/fail-secret',
			'test_type' => 'e2e',
			'description' => 'Package that fails with secret in message',
			'requires' => [
				'secrets' => [
					'TEST_SECRET'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf(['tests' => [['name' => 'test', 'status' => 'failed', 'duration' => 100]]])) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt && echo "Error: Authentication failed for $TEST_SECRET" && exit 1'
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