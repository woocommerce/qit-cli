<?php

namespace QIT\IntegrationTests\Security;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Tests for secret requirement validation.
 * 
 * These tests ensure that packages requiring secrets:
 * - Fail early with clear error messages when secrets are missing
 * - Properly validate secret presence before execution
 * - Guide users on how to provide required secrets
 */
class SecretValidationTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-secret-test-' . uniqid();
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
	 * Test that missing required secrets cause validation failure.
	 * 
	 * When a package declares required secrets but they're not provided,
	 * the test run should fail early with a helpful error message.
	 */
	public function test_missing_required_secrets_fail_validation(): void {
		$packageDir = $this->createPackageRequiringSecrets();
		$config = $this->createConfig( [ $packageDir ] );

		// Run without setting the required secrets
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		// Should fail due to missing secrets
		$this->assertNotEquals( 0, $proc->getExitCode(), 
			'Test should fail when required secrets are missing' );

		$output = $proc->getOutput();
		
		// Should have clear error messages about missing secrets
		$this->assertStringContainsString( 'Missing required secrets', $output );
		$this->assertStringContainsString( 'TEST_API_KEY', $output );
		$this->assertStringContainsString( 'TEST_SECRET', $output );
	}

	/**
	 * Test that providing required secrets allows execution.
	 */
	public function test_provided_secrets_pass_validation(): void {
		// Set required secrets
		putenv( 'TEST_API_KEY=test-key-123' );
		putenv( 'TEST_SECRET=test-secret-456' );

		$packageDir = $this->createPackageRequiringSecrets();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		// Should succeed when secrets are provided
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Test should succeed when required secrets are provided' );
	}

	/**
	 * Test that packages without secret requirements work without secrets.
	 */
	public function test_packages_without_secrets_work_normally(): void {
		$packageDir = $this->createSimplePackage();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		// Should succeed without any secrets
		$this->assertEquals( 0, $proc->getExitCode(), 
			'Package without secret requirements should work normally' );
	}

	// ========== Helper Methods ==========

	private function createPackageRequiringSecrets(): string {
		$packageDir = $this->fixturesDir . '/package-with-secrets';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/secret-package',
			'test_type' => 'e2e',
			'description' => 'Package requiring secrets',
			'requires' => [
				'secrets' => [
					'TEST_API_KEY' => 'API key for testing',
					'TEST_SECRET' => 'Secret value for testing'
				]
			],
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Running with secrets" && echo "Done"'
					]
				]
			]
		];
		
		file_put_contents( $packageDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		return $packageDir;
	}

	private function createSimplePackage(): string {
		$packageDir = $this->fixturesDir . '/simple-package';
		mkdir( $packageDir, 0755, true );
		
		$manifest = [
			'package' => 'test/simple',
			'test_type' => 'e2e',
			'description' => 'Simple package without secrets',
			'test' => [
				'phases' => [
					'run' => [
						'host: echo "Simple test"'
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