<?php

namespace QIT\IntegrationTests\Validation;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

/**
 * Test artifact validation for plugins, themes, and test packages.
 */
class ArtifactValidationTest extends TestCase {
	
	private array $tempDirs = [];
	private string $fixturesDir;
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/artifacts';
	}
	
	protected function tearDown(): void {
		// Clean up temp directories
		foreach ( $this->tempDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $dir ) );
			}
		}
		parent::tearDown();
	}
	
	/**
	 * Test that invalid plugin artifacts are rejected.
	 */
	public function test_invalid_plugin_artifact_rejected(): void {
		// Use fixture: plugin without Plugin Name header
		$zipPath = $this->fixturesDir . '/plugins/invalid/test-invalid-plugin.zip';
		
		$this->assertFileExists( $zipPath, 'Invalid plugin fixture should exist' );
		
		// Try to use it as a plugin
		$proc = qit( [
			'env:up',
			'--plugin=' . $zipPath,
		], return_process: true );
		
		$output = $proc->getOutput();
		$error = $proc->getErrorOutput();
		$fullOutput = $output . "\n" . $error;
		
		// Should fail validation
		$this->assertNotEquals( 0, $proc->getExitCode(),
			'Should fail with invalid plugin artifact' );
		
		$this->assertStringContainsString( 'validation', strtolower( $fullOutput ),
			'Should mention validation failure' );
	}
	
	/**
	 * Test that empty plugin artifacts are rejected.
	 */
	public function test_empty_plugin_artifact_rejected(): void {
		// Use fixture: empty plugin file
		$zipPath = $this->fixturesDir . '/plugins/invalid/test-empty-plugin.zip';
		
		$this->assertFileExists( $zipPath, 'Empty plugin fixture should exist' );
		
		// Try to use it as a plugin
		$proc = qit( [
			'env:up',
			'--plugin=' . $zipPath,
		], return_process: true );
		
		$output = $proc->getOutput();
		$error = $proc->getErrorOutput();
		$fullOutput = $output . "\n" . $error;
		
		// Should fail validation
		$this->assertNotEquals( 0, $proc->getExitCode(),
			'Should fail with empty plugin artifact' );
		
		$this->assertStringContainsString( 'validation', strtolower( $fullOutput ),
			'Should mention validation failure' );
	}
	
	/**
	 * Test that invalid theme artifacts are rejected.
	 */
	public function test_invalid_theme_artifact_rejected(): void {
		// Use fixture: theme without Theme Name header
		$zipPath = $this->fixturesDir . '/themes/invalid/test-invalid-theme.zip';
		
		$this->assertFileExists( $zipPath, 'Invalid theme fixture should exist' );
		
		// Try to use it as a theme
		$proc = qit( [
			'env:up',
			'--theme=' . $zipPath,
		], return_process: true );
		
		$output = $proc->getOutput();
		$error = $proc->getErrorOutput();
		$fullOutput = $output . "\n" . $error;
		
		// Should fail validation
		$this->assertNotEquals( 0, $proc->getExitCode(),
			'Should fail with invalid theme artifact' );
		
		$this->assertStringContainsString( 'validation', strtolower( $fullOutput ),
			'Should mention validation failure' );
	}
	
	/**
	 * Test that theme without style.css is rejected.
	 */
	public function test_theme_without_style_css_rejected(): void {
		// Use fixture: theme missing style.css
		$zipPath = $this->fixturesDir . '/themes/invalid/test-no-style-theme.zip';
		
		$this->assertFileExists( $zipPath, 'No-style theme fixture should exist' );
		
		// Try to use it as a theme
		$proc = qit( [
			'env:up',
			'--theme=' . $zipPath,
		], return_process: true );
		
		$output = $proc->getOutput();
		$error = $proc->getErrorOutput();
		$fullOutput = $output . "\n" . $error;
		
		// Should fail validation
		$this->assertNotEquals( 0, $proc->getExitCode(),
			'Should fail with theme missing style.css' );
		
		$this->assertTrue(
			stripos( $fullOutput, 'style.css' ) !== false ||
			stripos( $fullOutput, 'validation' ) !== false,
			'Should mention missing style.css or validation failure'
		);
	}
	
	/**
	 * Test that invalid test package artifacts are rejected.
	 */
	public function test_invalid_test_package_artifact_rejected(): void {
		// Create a fake test package without qit-test.json
		$tempDir = sys_get_temp_dir() . '/qit_invalid_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		$packageDir = $tempDir . '/fake-test-package';
		mkdir( $packageDir . '/tests', 0755, true );
		
		// Create test file but no manifest
		file_put_contents( $packageDir . '/tests/test.spec.js', 'test("fake", () => {});' );
		
		// Try to publish it
		$proc = qit( [
			'package:publish',
			$packageDir,
			'1.0.0',
		], return_process: true );
		
		$output = $proc->getOutput();
		$error = $proc->getErrorOutput();
		$fullOutput = $output . "\n" . $error;
		
		// Should fail validation
		$this->assertNotEquals( 0, $proc->getExitCode(),
			'Should fail with invalid test package' );
		
		// Should mention missing manifest
		$this->assertTrue(
			stripos( $fullOutput, 'qit-test.json' ) !== false ||
			stripos( $fullOutput, 'manifest' ) !== false,
			'Should mention missing manifest file'
		);
	}
	
	/**
	 * Test that valid plugin artifacts are accepted.
	 */
	public function test_valid_plugin_artifact_accepted(): void {
		// Use fixture: valid plugin with all required headers
		$zipPath = $this->fixturesDir . '/plugins/valid/test-valid-plugin.zip';
		
		$this->assertFileExists( $zipPath, 'Valid plugin fixture should exist' );
		
		// Try to use it as a plugin
		$proc = qit( [
			'env:up',
			'--plugin=' . $zipPath,
		], return_process: true );
		
		// Should succeed
		$this->assertEquals( 0, $proc->getExitCode(),
			'Should succeed with valid plugin artifact. Output: ' . $proc->getOutput() . ' Error: ' . $proc->getErrorOutput() );
		
		// Clean up environment
		qit( [ 'env:down' ] );
	}
	
	/**
	 * Test that valid theme artifacts are accepted.
	 */
	public function test_valid_theme_artifact_accepted(): void {
		// Use fixture: valid theme with all required files
		$zipPath = $this->fixturesDir . '/themes/valid/test-valid-theme.zip';
		
		$this->assertFileExists( $zipPath, 'Valid theme fixture should exist' );
		
		// Try to use it as a theme
		$proc = qit( [
			'env:up',
			'--theme=' . $zipPath,
		], return_process: true );
		
		// Should succeed
		$this->assertEquals( 0, $proc->getExitCode(),
			'Should succeed with valid theme artifact. Output: ' . $proc->getOutput() . ' Error: ' . $proc->getErrorOutput() );
		
		// Clean up environment
		qit( [ 'env:down' ] );
	}
	
	/**
	 * Test that cached invalid artifacts are re-validated and rejected.
	 */
	public function test_cached_invalid_artifact_revalidated(): void {
		// This test ensures that validation happens even for cached artifacts
		
		// Use valid plugin fixture
		$validZipPath = $this->fixturesDir . '/plugins/valid/test-valid-plugin.zip';
		
		// First run - should download and cache
		$proc = qit( [
			'env:up',
			'--plugin=' . $validZipPath,
		], return_process: true );
		
		$this->assertEquals( 0, $proc->getExitCode(), 
			'First run should succeed. Output: ' . $proc->getOutput() . ' Error: ' . $proc->getErrorOutput() );
		qit( [ 'env:down' ] );
		
		// Second run - should use cache and still validate
		$proc2 = qit( [
			'env:up',
			'--plugin=' . $validZipPath,
			'-v', // Verbose to see cache messages
		], return_process: true );
		
		$output2 = $proc2->getOutput();
		
		$this->assertEquals( 0, $proc2->getExitCode(),
			'Second run should succeed with cached artifact' );
		
		// Check that cache was used
		$this->assertTrue(
			stripos( $output2, 'cache' ) !== false || 
			stripos( $output2, 'using' ) !== false,
			'Second run should mention using cache'
		);
		
		qit( [ 'env:down' ] );
	}
	
	/**
	 * Helper: Create config file for testing.
	 */
	private function createConfig( string $artifactPath, string $type ): string {
		$tempDir = sys_get_temp_dir() . '/qit_config_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$config = [
			'$schema' => 'https://qit.woo.com/json-schema/qit',
			'sut' => [
				'type' => 'plugin',
				'slug' => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'environments' => [
				'default' => [
					'php' => '8.2',
					'wp' => 'stable'
				]
			]
		];
		
		// Add the artifact based on type - use simple string format
		if ( $type === 'plugin' ) {
			$config['environments']['default']['plugins'] = [
				$artifactPath  // Just the path to the ZIP file
			];
		} elseif ( $type === 'theme' ) {
			$config['environments']['default']['themes'] = [
				$artifactPath  // Just the path to the ZIP file
			];
		}
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}