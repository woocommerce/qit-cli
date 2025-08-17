<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test package registry operations: publish, list, and delete.
 * 
 * These tests verify that packages can be:
 * 1. Published to the registry with versions
 * 2. Listed from the registry
 * 3. Deleted from the registry
 */
class PackageRegistryOperationsTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];
	private array $publishedPackages = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../fixtures/test-packages';
	}

	protected function tearDown(): void {
		// Clean up any published packages
		foreach ( $this->publishedPackages as $packageId ) {
			qit( [
				'package:delete',
				$packageId,
				'--yes'
			], return_process: true );
		}
		parent::tearDown();
	}

	/**
	 * Test publishing a simple package to the registry.
	 */
	public function test_package_publish_simple(): void {
		$tempDir = sys_get_temp_dir() . '/qit_publish_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		// Create a simple test package
		$packageDir = $tempDir . '/simple-package';
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/regular-test-package-one' ) . " " . escapeshellarg( $packageDir ) );
		
		// Give it a unique name
		$packageName = 'woocommerce/qit-integration-test-simple-' . substr( uniqid(), 0, 8 );
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		$manifest['description'] = 'Simple test package for registry operations';
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish the package
		$proc = qit( [
			'package:publish',
			$packageDir,
		], return_process: true );
		
		if ( strpos( $proc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$output = $proc->getOutput();
		
		$this->assertEquals( 0, $proc->getExitCode(),
			'Package should publish successfully. Output: ' . $output );
		
		$this->assertStringContainsString( 'published successfully', $output,
			'Should confirm successful publication' );
		
		$this->assertStringContainsString( $packageName, $output,
			'Should show package name in output' );
		
		// Track for cleanup
		$this->publishedPackages[] = $packageName . ':latest';
	}

	/**
	 * Test publishing a package with explicit version.
	 */
	public function test_package_publish_with_version(): void {
		$tempDir = sys_get_temp_dir() . '/qit_version_publish_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		// Create test package
		$packageDir = $tempDir . '/versioned-package';
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/regular-test-package-one' ) . " " . escapeshellarg( $packageDir ) );
		
		$packageName = 'woocommerce/qit-integration-test-versioned-' . substr( uniqid(), 0, 8 );
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish with specific version
		$proc = qit( [
			'package:publish',
			$packageDir,
			'1.2.3'
		], return_process: true );
		
		if ( strpos( $proc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$output = $proc->getOutput();
		
		$this->assertEquals( 0, $proc->getExitCode(),
			'Should publish with version. Output: ' . $output );
		
		$this->assertMatchesRegularExpression( '/1\.2\.3|published successfully/i', $output,
			'Should indicate version in output' );
		
		$this->publishedPackages[] = $packageName . ':1.2.3';
	}

	/**
	 * Test publishing a package with subpackages.
	 */
	public function test_package_publish_with_subpackages(): void {
		$tempDir = sys_get_temp_dir() . '/qit_subpkg_publish_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		// Use the subpackages fixture
		$packageDir = $tempDir . '/subpackages';
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/subpackages-parent' ) . " " . escapeshellarg( $packageDir ) );
		
		$packageName = 'woocommerce/qit-integration-test-subpkgs-' . substr( uniqid(), 0, 8 );
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish the package with subpackages
		$proc = qit( [
			'package:publish',
			$packageDir,
		], return_process: true );
		
		if ( strpos( $proc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$output = $proc->getOutput();
		
		$this->assertEquals( 0, $proc->getExitCode(),
			'Should publish package with subpackages. Output: ' . $output );
		
		// Should indicate subpackages were published
		$this->assertMatchesRegularExpression( '/subpackage|3 packages|checkout.*cart.*account/i', $output,
			'Should mention subpackages in output' );
		
		$this->publishedPackages[] = $packageName . ':latest';
	}

	/**
	 * Test listing packages from the registry.
	 */
	public function test_package_list(): void {
		// First publish a package so we have something to list
		$tempDir = sys_get_temp_dir() . '/qit_list_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$packageDir = $tempDir . '/list-test-package';
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/regular-test-package-one' ) . " " . escapeshellarg( $packageDir ) );
		
		$packageName = 'woocommerce/qit-integration-test-list-' . substr( uniqid(), 0, 8 );
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish it
		$publishProc = qit( [
			'package:publish',
			$packageDir,
		], return_process: true );
		
		if ( strpos( $publishProc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publishProc->getExitCode(),
			'Should publish test package' );
		
		$this->publishedPackages[] = $packageName . ':latest';
		
		// Now list packages
		$listProc = qit( [
			'package:list',
			'--namespace=woocommerce'
		], return_process: true );
		
		$listOutput = $listProc->getOutput();
		
		$this->assertEquals( 0, $listProc->getExitCode(),
			'Should list packages successfully' );
		
		$this->assertStringContainsString( $packageName, $listOutput,
			'Should find our published package in the list' );
		
		// Test JSON format
		$listJsonProc = qit( [
			'package:list',
			'--namespace=woocommerce',
			'--json'
		], return_process: true );
		
		$this->assertEquals( 0, $listJsonProc->getExitCode(),
			'package:list --json should succeed' );
		
		$jsonOutput = $listJsonProc->getOutput();
		$data = json_decode( $jsonOutput, true );
		
		$this->assertIsArray( $data,
			'Should return valid JSON. Output was: ' . $jsonOutput );
		
		$this->assertArrayHasKey( 'packages', $data,
			'JSON should have packages key' );
		
		// Find our package in the list
		$found = false;
		foreach ( $data['packages'] as $pkg ) {
			if ( strpos( $pkg['package_id'], $packageName ) === 0 ) {
				$found = true;
				break;
			}
		}
		
		$this->assertTrue( $found,
			'Should find our package in JSON list' );
	}

	/**
	 * Test deleting a package from the registry.
	 */
	public function test_package_delete(): void {
		// First publish a package to delete
		$tempDir = sys_get_temp_dir() . '/qit_delete_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$packageDir = $tempDir . '/delete-test-package';
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/regular-test-package-one' ) . " " . escapeshellarg( $packageDir ) );
		
		$packageName = 'woocommerce/qit-integration-test-delete-' . substr( uniqid(), 0, 8 );
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		// Publish it
		$publishProc = qit( [
			'package:publish',
			$packageDir,
		], return_process: true );
		
		if ( strpos( $publishProc->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $publishProc->getExitCode(),
			'Should publish package to delete' );
		
		// Now delete it (need to specify version)
		$deleteProc = qit( [
			'package:delete',
			$packageName . ':latest',
			'--yes'
		], return_process: true );
		
		$deleteOutput = $deleteProc->getOutput();
		
		$this->assertEquals( 0, $deleteProc->getExitCode(),
			'Should delete package successfully. Output: ' . $deleteOutput );
		
		$this->assertStringContainsString( 'deleted', strtolower( $deleteOutput ),
			'Should confirm deletion' );
		
		// Verify it's gone from the list
		$listProc = qit( [
			'package:list',
			'--namespace=woocommerce'
		], return_process: true );
		
		$listOutput = $listProc->getOutput();
		
		$this->assertStringNotContainsString( $packageName, $listOutput,
			'Deleted package should not appear in list' );
	}

	/**
	 * Test publish, list, and delete workflow with versions.
	 */
	public function test_package_lifecycle_with_versions(): void {
		$tempDir = sys_get_temp_dir() . '/qit_lifecycle_test_' . uniqid();
		$this->tempDirs[] = $tempDir;
		mkdir( $tempDir, 0755, true );
		
		$packageDir = $tempDir . '/lifecycle-package';
		exec( "cp -r " . escapeshellarg( $this->fixturesDir . '/regular-test-package-one' ) . " " . escapeshellarg( $packageDir ) );
		
		$packageName = 'woocommerce/qit-integration-test-lifecycle-' . substr( uniqid(), 0, 8 );
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$manifest['package'] = $packageName;
		
		// Publish v1.0.0
		$manifest['description'] = 'Version 1.0.0 of lifecycle test';
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		$proc1 = qit( [
			'package:publish',
			$packageDir,
			'1.0.0'
		], return_process: true );
		
		if ( strpos( $proc1->getOutput(), 'not connected' ) !== false ) {
			$this->markTestSkipped( 'Test requires connection to QIT Manager' );
		}
		
		$this->assertEquals( 0, $proc1->getExitCode(),
			'Should publish v1.0.0' );
		
		// Publish v2.0.0
		$manifest['description'] = 'Version 2.0.0 with improvements';
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		$proc2 = qit( [
			'package:publish',
			$packageDir,
			'2.0.0'
		], return_process: true );
		
		$this->assertEquals( 0, $proc2->getExitCode(),
			'Should publish v2.0.0' );
		
		// List and verify both versions exist
		$listProc = qit( [
			'package:list',
			'--namespace=woocommerce',
			'--json'
		], return_process: true );
		
		$data = json_decode( $listProc->getOutput(), true );
		$versions = [];
		
		foreach ( $data['packages'] as $pkg ) {
			if ( strpos( $pkg['package_id'], $packageName ) === 0 ) {
				$versions[] = $pkg['package_id'];
			}
		}
		
		$this->assertCount( 2, $versions,
			'Should have 2 versions published' );
		
		// Delete specific version
		$deleteProc = qit( [
			'package:delete',
			$packageName . ':1.0.0',
			'--yes'
		], return_process: true );
		
		$this->assertEquals( 0, $deleteProc->getExitCode(),
			'Should delete v1.0.0' );
		
		// Clean up v2.0.0
		$this->publishedPackages[] = $packageName . ':2.0.0';
	}
}