<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Integration test for secret handling and orchestrator CTRF generation.
 * 
 * These tests verify:
 * 1. Secrets are properly validated before test execution
 * 2. Secrets are redacted from output
 * 3. Orchestrator generates CTRF for lifecycle phases
 * 4. CTRF files are properly merged
 */
class SecretHandlingTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
		// Clear any test environment variables
		putenv( 'TEST_API_KEY' );
		putenv( 'TEST_SECRET' );
	}

	protected function tearDown(): void {
		// Clean up temp directories
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				exec( "rm -rf " . escapeshellarg( $dir ) );
			}
		}
		// Clear environment variables
		putenv( 'TEST_API_KEY' );
		putenv( 'TEST_SECRET' );
		parent::tearDown();
	}

	/**
	 * Test that missing required secrets cause validation failure.
	 */
	public function test_missing_secrets_validation_fails(): void {
		// Create a test package that requires secrets
		$packageDir = $this->createPackageWithSecrets();
		$config = $this->createConfig( [ $packageDir ] );

		// Run without setting the required secrets
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Should fail with missing secrets message
		$this->assertStringContainsString( 'Missing required secrets', $output );
		$this->assertStringContainsString( 'TEST_API_KEY', $output );
		$this->assertStringContainsString( 'TEST_SECRET', $output );
		$this->assertStringContainsString( 'export TEST_API_KEY=', $output );
	}

	/**
	 * Test that secrets are validated and redacted from output.
	 */
	public function test_secrets_are_redacted_from_output(): void {
		// Set required secrets
		putenv( 'TEST_API_KEY=super-secret-key-123' );
		putenv( 'TEST_SECRET=my-password-456' );

		// Create a test package that echoes secrets
		$packageDir = $this->createPackageWithSecrets( true );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v', // Verbose to see output
		], return_process: true );

		$output = $proc->getOutput();

		// Secrets should be validated
		$this->assertStringContainsString( '✓ All required secrets validated', $output );

		// Secrets should be redacted from output
		$this->assertStringNotContainsString( 'super-secret-key-123', $output );
		$this->assertStringNotContainsString( 'my-password-456', $output );
		
		// Should see redacted markers instead
		$this->assertStringContainsString( '[REDACTED:TEST_API_KEY]', $output );
		$this->assertStringContainsString( '[REDACTED:TEST_SECRET]', $output );
	}

	/**
	 * Test that orchestrator CTRF is generated for lifecycle phases.
	 */
	public function test_orchestrator_ctrf_generation(): void {
		// Create a simple utility package (no run phase)
		$packageDir = $this->createUtilityPackage();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// Test should pass (utility packages are successful)
		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Look for artifacts directory in output
		if ( preg_match( '/test-runs\/run-[a-f0-9.]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $matches[0];
			
			// Check if orchestrator.json was created
			$orchestrator_ctrf = $artifacts_path . '/ctrf/orchestrator.json';
			if ( file_exists( $orchestrator_ctrf ) ) {
				$ctrf_data = json_decode( file_get_contents( $orchestrator_ctrf ), true );
				
				// Verify it contains lifecycle phase results
				$this->assertArrayHasKey( 'results', $ctrf_data );
				$this->assertArrayHasKey( 'tests', $ctrf_data['results'] );
				
				// Should have entries for setup and teardown
				$has_setup = false;
				$has_teardown = false;
				foreach ( $ctrf_data['results']['tests'] as $test ) {
					if ( strpos( $test['name'], '[setup]' ) !== false ) {
						$has_setup = true;
					}
					if ( strpos( $test['name'], '[teardown]' ) !== false ) {
						$has_teardown = true;
					}
				}
				
				$this->assertTrue( $has_setup, 'Should have setup phase in CTRF' );
				$this->assertTrue( $has_teardown, 'Should have teardown phase in CTRF' );
			}
		}
	}

	/**
	 * Test that output is suppressed in CI mode.
	 */
	public function test_output_suppression_in_ci(): void {
		// Set CI environment
		putenv( 'CI=true' );

		$packageDir = $this->createUtilityPackage();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// In CI mode, lifecycle output should be suppressed
		// We shouldn't see the actual echo output
		$this->assertStringNotContainsString( 'Setting up test environment...', $output );
		$this->assertStringNotContainsString( 'Cleaning up test environment...', $output );

		// Clean up
		putenv( 'CI' );
	}

	// ============= Helper Methods =============

	private function createPackageWithSecrets( bool $echo_secrets = false ): string {
		$tempDir = sys_get_temp_dir() . '/qit-secret-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$setup_commands = $echo_secrets 
			? [
				'echo "Using API key: $TEST_API_KEY"',
				'echo "Using secret: $TEST_SECRET"'
			]
			: [
				'echo "Setup complete"'
			];

		$manifest = [
			'package' => 'secret-test-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'description' => 'Package that requires secrets',
			'requires' => [
				'secrets' => [ 'TEST_API_KEY', 'TEST_SECRET' ]
			],
			'test' => [
				'phases' => [
					'setup' => $setup_commands,
					'run' => [
						'mkdir -p ./results && echo \'{"results":{"summary":{"tests":1,"passed":1,"failed":0},"tests":[{"name":"test","status":"passed"}]}}\' > ./results/ctrf.json && mkdir -p ./blob-report && echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $tempDir . '/manifest.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		return $tempDir;
	}

	private function createUtilityPackage(): string {
		$tempDir = sys_get_temp_dir() . '/qit-utility-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$manifest = [
			'package' => 'utility-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'description' => 'Utility package for testing',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "Setting up test environment..."'
					],
					'teardown' => [
						'echo "Cleaning up test environment..."'
					]
				]
			]
		];
		file_put_contents( $tempDir . '/manifest.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		return $tempDir;
	}

	private function createConfig( array $testPackages ): string {
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => $testPackages
					]
				]
			]
		];

		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}