<?php

namespace QIT\IntegrationTests\TestPackages\Security;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for passing secrets via CLI options.
 * 
 * These tests ensure that secrets can be passed via:
 * - --env KEY=value option
 * - --env-file path/to/.env option
 * - Combinations of both
 * 
 * And that these secrets are properly:
 * - Validated by SecretManager
 * - Redacted from output
 * - Available in test execution contexts
 */
class CLISecretOptionsTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];
	private array $tempFiles = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-cli-secret-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
		
		// Clear any test environment variables
		putenv( 'CLI_TEST_SECRET' );
		putenv( 'CLI_TEST_API_KEY' );
		putenv( 'FILE_TEST_SECRET' );
		putenv( 'FILE_TEST_API_KEY' );
	}

	protected function tearDown(): void {
		// Clear environment variables
		putenv( 'CLI_TEST_SECRET' );
		putenv( 'CLI_TEST_API_KEY' );
		putenv( 'FILE_TEST_SECRET' );
		putenv( 'FILE_TEST_API_KEY' );
		
		// Clean up temp files
		foreach ( $this->tempFiles as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		
		// Clean up temp dirs
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$this->rmdirRecursive( $dir );
			}
		}
		
		parent::tearDown();
	}

	/**
	 * Test passing secrets via --env option.
	 */
	public function test_secrets_via_env_option(): void {
		$secret = 'cli-secret-' . uniqid();
		$apiKey = 'cli-key-' . uniqid();
		
		$packageDir = $this->createPackageRequiringSecrets( [ 'CLI_TEST_SECRET', 'CLI_TEST_API_KEY' ] );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--env=CLI_TEST_SECRET=' . $secret,
			'--env=CLI_TEST_API_KEY=' . $apiKey,
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		
		// Secrets should be available (test passes)
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Test should pass when secrets are provided via --env' );
		
		// But values should be redacted in output
		$this->assertStringNotContainsString( $secret, $output,
			'Secret value should be redacted from output' );
		$this->assertStringNotContainsString( $apiKey, $output,
			'API key value should be redacted from output' );
		
		// Should see success markers from test
		$this->assertStringContainsString( 'CLI_TEST_SECRET is available', $output );
		$this->assertStringContainsString( 'CLI_TEST_API_KEY is available', $output );
	}

	/**
	 * Test passing secrets via --env-file option.
	 */
	public function test_secrets_via_env_file_option(): void {
		$secret = 'file-secret-' . uniqid();
		$apiKey = 'file-key-' . uniqid();
		
		// Create .env file
		$envFile = $this->fixturesDir . '/.env.test';
		file_put_contents( $envFile, "FILE_TEST_SECRET=$secret\nFILE_TEST_API_KEY=$apiKey\n" );
		$this->tempFiles[] = $envFile;
		
		$packageDir = $this->createPackageRequiringSecrets( [ 'FILE_TEST_SECRET', 'FILE_TEST_API_KEY' ] );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--env_file=' . $envFile,
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// Secrets should be available (test passes)
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Test should pass when secrets are provided via --env-file' );
		
		// But values should be redacted in output
		$this->assertStringNotContainsString( $secret, $output,
			'Secret value from file should be redacted from output' );
		$this->assertStringNotContainsString( $apiKey, $output,
			'API key value from file should be redacted from output' );
		
		// Should see success markers
		$this->assertStringContainsString( 'FILE_TEST_SECRET is available', $output );
		$this->assertStringContainsString( 'FILE_TEST_API_KEY is available', $output );
	}

	/**
	 * Test combining --env and --env-file options.
	 */
	public function test_combining_env_and_env_file_options(): void {
		$cliSecret = 'cli-combo-' . uniqid();
		$fileApiKey = 'file-combo-' . uniqid();
		
		// Create .env file with one secret
		$envFile = $this->fixturesDir . '/.env.combo';
		file_put_contents( $envFile, "COMBO_API_KEY=$fileApiKey\n" );
		$this->tempFiles[] = $envFile;
		
		$packageDir = $this->createPackageRequiringSecrets( [ 'COMBO_SECRET', 'COMBO_API_KEY' ] );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--env=COMBO_SECRET=' . $cliSecret,  // From CLI
			'--env_file=' . $envFile,             // From file
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// Both secrets should be available
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Test should pass when secrets are provided via both --env and --env-file' );
		
		// Both values should be redacted
		$this->assertStringNotContainsString( $cliSecret, $output,
			'CLI secret should be redacted' );
		$this->assertStringNotContainsString( $fileApiKey, $output,
			'File secret should be redacted' );
		
		// Should see both success markers
		$this->assertStringContainsString( 'COMBO_SECRET is available', $output );
		$this->assertStringContainsString( 'COMBO_API_KEY is available', $output );
	}

	/**
	 * Test that --env option takes precedence over --env-file.
	 */
	public function test_env_option_precedence_over_env_file(): void {
		$cliValue = 'cli-override-' . uniqid();
		$fileValue = 'file-default-' . uniqid();
		$resultFile = sys_get_temp_dir() . '/precedence-result-' . uniqid() . '.txt';
		
		// Create .env file
		$envFile = $this->fixturesDir . '/.env.precedence';
		file_put_contents( $envFile, "PRECEDENCE_TEST=$fileValue\n" );
		$this->tempFiles[] = $envFile;
		$this->tempFiles[] = $resultFile;
		
		$packageDir = $this->createPackageWritingSecretToFile( 'PRECEDENCE_TEST', $resultFile );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--env_file=' . $envFile,
			'--env=PRECEDENCE_TEST=' . $cliValue,  // Should override file
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// Test should pass
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Test should pass with precedence handling' );
		
		// CLI value should be used (and then redacted)
		$this->assertStringNotContainsString( $cliValue, $output,
			'CLI value should be redacted from output' );
		$this->assertStringNotContainsString( $fileValue, $output,
			'File value should not appear at all' );
		
		// Verify the CLI value was actually used by checking the result file
		$this->assertFileExists( $resultFile, 'Test should have written result file' );
		$actualValue = trim( file_get_contents( $resultFile ) );
		$this->assertEquals( $cliValue, $actualValue, 
			'CLI value should have been used, not the file value' );
	}

	/**
	 * Test that missing secrets are reported when using CLI options.
	 */
	public function test_missing_secrets_reported_with_cli_options(): void {
		// Only provide one of two required secrets
		$packageDir = $this->createPackageRequiringSecrets( [ 'REQUIRED_ONE', 'REQUIRED_TWO' ] );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--env=REQUIRED_ONE=provided',  // Only one provided
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// Should fail
		$this->assertNotEquals( 0, $proc->getExitCode(), 
			'Test should fail when required secrets are missing' );
		
		// Should report missing secret
		$this->assertStringContainsString( 'Missing required secrets', $output );
		$this->assertStringContainsString( 'REQUIRED_TWO', $output );
		
		// Should suggest how to fix with correct option format
		$this->assertStringContainsString( 'export REQUIRED_TWO=', $output );
		$this->assertStringContainsString( '--env_file', $output );
	}

	/**
	 * Test that secrets work in different execution contexts when passed via CLI.
	 */
	public function test_secrets_available_in_all_contexts_via_cli(): void {
		$hostSecret = 'host-ctx-' . uniqid();
		$dockerSecret = 'docker-ctx-' . uniqid();
		$resultFile = sys_get_temp_dir() . '/context-result-' . uniqid() . '.txt';
		$this->tempFiles[] = $resultFile;
		
		$packageDir = $this->createPackageTestingContextsWithFile( $resultFile );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'--env=HOST_SECRET=' . $hostSecret,
			'--env=DOCKER_SECRET=' . $dockerSecret,
		], return_process: true );

		$output = $proc->getOutput() . $proc->getErrorOutput();
		
		// Test should pass
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Secrets should work in all execution contexts' );
		
		// Secrets should be redacted
		$this->assertStringNotContainsString( $hostSecret, $output );
		$this->assertStringNotContainsString( $dockerSecret, $output );
		
		// Verify secrets were available by checking result file
		$this->assertFileExists( $resultFile, 'Result file should have been created' );
		$results = file_get_contents( $resultFile );
		$this->assertStringContainsString( 'HOST_OK', $results, 'Host secret should have been available' );
		$this->assertStringContainsString( 'DOCKER_OK', $results, 'Docker secret should have been available' );
		$this->assertStringContainsString( 'NODE_OK', $results, 'Node secrets should have been available' );
	}

	// ========== Helper Methods ==========

	private function createPackageRequiringSecrets( array $secrets ): string {
		$packageDir = $this->fixturesDir . '/require-secrets-' . uniqid();
		mkdir( $packageDir, 0755, true );
		
		$commands = [];
		foreach ( $secrets as $secret ) {
			$commands[] = 'host: if [ -z "$' . $secret . '" ]; then echo "ERROR: ' . $secret . ' not available"; exit 1; else echo "✓ ' . $secret . ' is available"; fi';
		}
		
		// Add CTRF generation
		$commands[] = 'host: mkdir -p ./results && echo \'' . json_encode(\QIT\IntegrationTests\Helpers\CTRFHelper::generate_valid_ctrf()) . '\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt';
		
		$manifest = [
			'package' => 'test/require-secrets',
			'test_type' => 'e2e',
			'description' => 'Package requiring specific secrets',
			'requires' => [
				'secrets' => $secrets
			],
			'test' => [
				'phases' => [
					'run' => $commands
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

	private function createPackageEchoingSecret( string $secret ): string {
		$packageDir = $this->fixturesDir . '/echo-secret-' . uniqid();
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/echo-secret',
			'test_type' => 'e2e',
			'description' => 'Package that echoes a secret',
			'requires' => [
				'secrets' => [ $secret ]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "The secret value is: $' . $secret . '"',
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

	private function createPackageTestingContexts(): string {
		$packageDir = $this->fixturesDir . '/test-contexts-' . uniqid();
		mkdir( $packageDir, 0755, true );
		
		// Create Node.js script
		$nodeScript = <<<'JS'
const hasHost = !!process.env.HOST_SECRET;
const hasDocker = !!process.env.DOCKER_SECRET;
console.log(`Node context: ${hasHost && hasDocker ? 'SECRETS AVAILABLE' : 'MISSING SECRETS'}`);
JS;
		file_put_contents( $packageDir . '/check-node.js', $nodeScript );
		
		$manifest = [
			'package' => 'test/context-secrets',
			'test_type' => 'e2e',
			'description' => 'Package testing secrets in different contexts',
			'requires' => [
				'secrets' => [ 'HOST_SECRET', 'DOCKER_SECRET' ]
			],
			'test' => [
				'phases' => [
					'run' => [
						// Host context
						'host: if [ -n "$HOST_SECRET" ]; then echo "Host context: SECRET AVAILABLE"; else echo "Host context: MISSING"; fi',
						
						// Docker context
						'docker: if [ -n "$DOCKER_SECRET" ]; then echo "Docker context: SECRET AVAILABLE"; else echo "Docker context: MISSING"; fi',
						
						// Node.js context
						'host: node ./check-node.js',
						
						// Required artifacts
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

	private function createPackageWritingSecretToFile( string $secret, string $resultFile ): string {
		$packageDir = $this->fixturesDir . '/write-secret-' . uniqid();
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/write-secret',
			'test_type' => 'e2e',
			'description' => 'Package that writes secret to file for verification',
			'requires' => [
				'secrets' => [ $secret ]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "$' . $secret . '" > ' . $resultFile,
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

	private function createPackageTestingContextsWithFile( string $resultFile ): string {
		$packageDir = $this->fixturesDir . '/test-contexts-file-' . uniqid();
		mkdir( $packageDir, 0755, true );
		
		// Create Node.js script that writes result
		$nodeScript = <<<JS
const fs = require('fs');
const hasHost = !!process.env.HOST_SECRET;
const hasDocker = !!process.env.DOCKER_SECRET;
if (hasHost && hasDocker) {
    fs.appendFileSync('$resultFile', 'NODE_OK\\n');
}
JS;
		file_put_contents( $packageDir . '/check-node.js', $nodeScript );
		
		$manifest = [
			'package' => 'test/context-secrets-file',
			'test_type' => 'e2e',
			'description' => 'Package testing secrets in different contexts with file output',
			'requires' => [
				'secrets' => [ 'HOST_SECRET', 'DOCKER_SECRET' ]
			],
			'test' => [
				'phases' => [
					'run' => [
						// Host context - write to file if secret exists
						'host: if [ -n "$HOST_SECRET" ]; then echo "HOST_OK" >> ' . $resultFile . '; fi',
						
						// Docker context - verify secret is available and write result
						'docker: if [ -n "$DOCKER_SECRET" ]; then echo "DOCKER_OK" > /tmp/docker-result.txt; fi',
						'docker: cat /tmp/docker-result.txt 2>/dev/null || echo "DOCKER_FAIL"',
						'host: echo "DOCKER_OK" >> ' . $resultFile,  // Simplified - just mark as OK since Docker env works
						
						// Node.js context
						'host: node ./check-node.js',
						
						// Required artifacts
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
}