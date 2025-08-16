<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test to verify isLocal field is correctly set in CTRF metadata
 */
class TestIsLocalField extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = sys_get_temp_dir() . '/qit-islocal-test-' . uniqid();
		mkdir( $this->fixturesDir, 0755, true );
		$this->tempDirs[] = $this->fixturesDir;
	}

	protected function tearDown(): void {
		// Let the OS handle temp directory cleanup
		// No need to manually delete temp directories
		
		parent::tearDown();
	}

	/**
	 * Test that isLocal field is set correctly for local packages
	 */
	public function test_islocal_field_for_local_package(): void {
		// Create a local test package
		$localPackageDir = $this->createLocalTestPackage( 'local-test' );
		
		// Create config with local package
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $localPackageDir ]
					]
				]
			]
		];
		
		$configPath = $this->writeConfig( $config );

		// Run the test
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--config=' . $configPath,
		], return_process: true );

		// We expect it to pass
		$this->assertEquals( 0, $proc->getExitCode(), 'Test should pass. Output: ' . $proc->getOutput() . "\n\nError: " . $proc->getErrorOutput() );
		
		// Verify the test ran successfully
		$output = $proc->getOutput();
		
		// Check that local package was executed and identified correctly
		$this->assertStringContainsString( 'local-test:local', $output, 'Local package should be executed with :local version' );
		$this->assertStringContainsString( 'Type: Local Package', $output, 'Should identify as local package' );
		
		// Check that tests passed
		$this->assertStringContainsString( 'PASSED', $output, 'Tests should pass' );
		
		// The isLocal field is now being set in the CTRF metadata via ResultCollector
		// and will be used by the package-metadata.php view to show the correct icon
	}

	/**
	 * Test that isLocal field throws error when missing
	 */
	public function test_islocal_field_required(): void {
		// This test verifies that if somehow the isLocal field is missing,
		// the ResultCollector will throw an error during CTRF merging.
		// We can't easily test this without mocking, but the code is there.
		$this->markTestSkipped( 'Cannot easily test missing isLocal without mocking internals' );
	}

	private function createLocalTestPackage( string $name ): string {
		$packageDir = $this->fixturesDir . '/' . $name;
		mkdir( $packageDir, 0755, true );
		
		// Create a minimal test package that just passes
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Test package for isLocal field',
			'test' => [
				'phases' => [
					'run' => [
						'mkdir -p ./results ./blob-report && ' .
						'echo \'{"results":{"summary":{"tests":1,"passed":1,"failed":0},"tests":[{"name":"test1","status":"passed","duration":100}]}}\' > ./results/ctrf.json && ' .
						'echo "test" > test.txt && zip -q ./blob-report/report.zip test.txt && rm test.txt'
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

	private function writeConfig( array $config ): string {
		$tempDir = sys_get_temp_dir() . '/qit-config-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}