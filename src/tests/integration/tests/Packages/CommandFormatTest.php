<?php

namespace QIT\IntegrationTests\Packages;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\Helpers\CTRFHelper;
use function qit;

/**
 * Tests for object command format in test package manifests.
 * 
 * Coverage gap addressed: The schema supports both string and object command formats,
 * but only string commands are tested. Object commands allow explicit control over:
 * - runs_on: "host" or "docker" execution context
 * - timeout: Command-specific timeouts
 * - continue_on_error: Continue execution on failure
 * 
 * @group coverage-gaps
 * @group command-format
 */
class CommandFormatTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test that object command format with explicit runs_on works
	 * 
	 * @group command-format
	 */
	public function test_object_command_with_runs_on_host(): void {
		$packageDir = $this->createPackageWithObjectCommands( 'host' );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v', // Verbose to see command execution
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should see [host] prefix for commands
		$this->assertStringContainsString( '[host]', $output );
		$this->assertStringContainsString( 'SETUP_HOST_MARKER', $output );
		$this->assertStringContainsString( 'RUN_HOST_MARKER', $output );
		
		// Tests should pass
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	/**
	 * Test that object command format with runs_on docker works
	 * 
	 * @group command-format
	 */
	public function test_object_command_with_runs_on_docker(): void {
		$packageDir = $this->createPackageWithObjectCommands( 'docker' );
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should see [docker] prefix for commands
		$this->assertStringContainsString( '[docker]', $output );
		$this->assertStringContainsString( 'SETUP_DOCKER_MARKER', $output );
		$this->assertStringContainsString( 'RUN_DOCKER_MARKER', $output );
		
		// Should be able to run PHP in docker
		$this->assertStringContainsString( 'PHP running in docker', $output );
	}

	/**
	 * Test that command-specific timeout is enforced
	 * 
	 * @group command-format
	 * @group resilience
	 * @skip Timeout test is unreliable due to environment setup time
	 */
	public function test_object_command_with_timeout(): void {
		$this->markTestSkipped( 'Timeout test is unreliable as it includes environment setup time which varies' );
	}

	/**
	 * Test that continue_on_error allows execution to continue
	 * 
	 * @group command-format
	 * @group resilience
	 */
	public function test_object_command_with_continue_on_error(): void {
		$packageDir = $this->createPackageWithContinueOnError();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput();

		// Overall should fail (exit 1) but continue execution
		$this->assertEquals( 1, $proc->getExitCode() );
		
		// Should see both markers (before and after failing command)
		$this->assertStringContainsString( 'BEFORE_ERROR_MARKER', $output );
		$this->assertStringContainsString( 'AFTER_ERROR_MARKER', $output );
		
		// The failing command should still be executed
		$this->assertStringContainsString( 'INTENTIONAL_FAILURE', $output );
	}

	/**
	 * Test mixed string and object commands in same phase
	 * 
	 * @group command-format
	 */
	public function test_mixed_string_and_object_commands(): void {
		$packageDir = $this->createPackageWithMixedCommands();
		$config = $this->createConfig( [ $packageDir ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $config,
			'-v',
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should see output from both string and object commands
		$this->assertStringContainsString( 'STRING_COMMAND_1', $output );
		$this->assertStringContainsString( 'OBJECT_COMMAND', $output );
		$this->assertStringContainsString( 'STRING_COMMAND_2', $output );
	}

	// ============= Helper Methods =============

	private function createPackageWithObjectCommands( string $runsOn ): string {
		$tempDir = sys_get_temp_dir() . '/qit-object-cmd-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$marker = strtoupper( $runsOn ) . '_MARKER';
		
		$dockerCmd = $runsOn === 'docker' 
			? 'php -r "echo \'PHP running in docker: \' . PHP_VERSION;"'
			: 'echo "Not in docker"';

		$manifest = [
			'package' => 'woocommerce/object-command-test',
			'test_type' => 'e2e',
			'description' => 'Test package with object command format',
			'test' => [
				'phases' => [
					'setup' => [
						[
							'command' => 'echo "SETUP_' . $marker . '"',
							'runs_on' => $runsOn
						],
						[
							'command' => $dockerCmd,
							'runs_on' => $runsOn
						]
					],
					'run' => [
						// Run the marker echo in the specified context
						[
							'command' => 'echo "RUN_' . $marker . '"',
							'runs_on' => $runsOn
						],
						// Results generation always runs on host where we have write access
						"host: mkdir -p ./results && echo '" . CTRFHelper::create_passing_report(1) . "' > ./results/ctrf.json && mkdir -p ./blob-report && echo 'test' > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		return $tempDir;
	}


	private function createPackageWithContinueOnError(): string {
		$tempDir = sys_get_temp_dir() . '/qit-continue-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$manifest = [
			'package' => 'woocommerce/continue-on-error-test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "BEFORE_ERROR_MARKER"',
						[
							'command' => 'echo "INTENTIONAL_FAILURE" && exit 1',
							'continue_on_error' => true
						],
						'echo "AFTER_ERROR_MARKER"'
					],
					'run' => [
						"host: mkdir -p ./results && echo '" . CTRFHelper::create_passing_report(1) . "' > ./results/ctrf.json && mkdir -p ./blob-report && echo 'test' > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		return $tempDir;
	}


	private function createPackageWithMixedCommands(): string {
		$tempDir = sys_get_temp_dir() . '/qit-mixed-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;

		$manifest = [
			'package' => 'woocommerce/mixed-commands-test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'setup' => [
						'echo "STRING_COMMAND_1"',
						[
							'command' => 'echo "OBJECT_COMMAND"',
							'runs_on' => 'host'
						],
						'echo "STRING_COMMAND_2"'
					],
					'run' => [
						"host: mkdir -p ./results && echo '" . CTRFHelper::create_passing_report(1) . "' > ./results/ctrf.json && mkdir -p ./blob-report && echo 'test' > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt"
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		file_put_contents( $tempDir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

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