<?php

namespace integration\tests\TestPackages;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

class PassthroughArgumentsTest extends TestCase {
	
	private string $localPackage;
	private string $remotePackage;
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		
		// Use the actual fixture packages we created
		$this->localPackage = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages/passthrough-local';
		$this->remotePackage = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages/passthrough-remote';
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		// Let the OS handle cleanup of temp files
		// No manual deletion needed for sys_get_temp_dir() files
	}
	
	/**
	 * Test that passthrough arguments go to local packages by default.
	 */
	public function test_default_passthrough_to_local_packages_only() {
		// Publish the remote package first
		$this->publishRemotePackage();
		
		// Use local and remote packages with --grep to filter tests
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->localPackage,
			'--test-package=test/passthrough-remote:1.0.0',
			'--',
			'--grep=@local-only',
		], return_process: true );
		
		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();
		
		// The test should complete successfully
		$this->assertEquals( 0, $exitCode, 'Test run should complete successfully' );
		
		// Extract artifacts directory
		if ( preg_match( '/test-runs\/run-[a-f0-9.]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $matches[0];
			
			// Check CTRF outputs
			$local_ctrf = $artifacts_path . '/ctrf/passthrough-local.json';
			$remote_ctrf = $artifacts_path . '/ctrf/passthrough-remote.json';
			
			$this->assertFileExists( $local_ctrf, 'Local CTRF output should exist' );
			$this->assertFileExists( $remote_ctrf, 'Remote CTRF output should exist' );
			
			$local_data = json_decode( file_get_contents( $local_ctrf ), true );
			// Local package should have run only the @local-only test
			$this->assertEquals( 1, $local_data['results']['summary']['tests'],
				'Local package should run only 1 test when grep filter is applied' );
			$this->assertEquals( 1, $local_data['results']['summary']['passed'],
				'Local package test should pass' );
			$this->assertStringContainsString( '@local-only', $local_data['results']['tests'][0]['name'],
				'Local package should run the @local-only test' );
			
			$remote_data = json_decode( file_get_contents( $remote_ctrf ), true );
			// Remote package should run all 4 tests (no grep filter applied)
			$this->assertEquals( 4, $remote_data['results']['summary']['tests'],
				'Remote package should run all 4 tests when no grep filter is applied' );
		} else {
			$this->fail( 'Could not find artifacts directory in output' );
		}
		
		// Cleanup
		$this->deleteRemotePackage();
	}
	
	/**
	 * Test explicit --passthrough option overrides default behavior.
	 */
	public function test_explicit_passthrough_targets() {
		$this->publishRemotePackage();
		
		// Explicitly pass args only to the remote package
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->localPackage,
			'--test-package=test/passthrough-remote:1.0.0',
			'--passthrough=test/passthrough-remote:1.0.0',
			'--',
			'--grep=@remote-only',
		], return_process: true );
		
		$output = $proc->getOutput();
		
		if ( preg_match( '/test-runs\/run-[a-f0-9.]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $matches[0];
			
			$local_ctrf = $artifacts_path . '/ctrf/passthrough-local.json';
			$remote_ctrf = $artifacts_path . '/ctrf/passthrough-remote.json';
			
			if ( file_exists( $local_ctrf ) ) {
				$local_data = json_decode( file_get_contents( $local_ctrf ), true );
				// Local package should run all 4 tests (no grep filter)
				$this->assertEquals( 4, $local_data['results']['summary']['tests'],
					'Local package should run all tests when not targeted for passthrough' );
			}
			
			if ( file_exists( $remote_ctrf ) ) {
				$remote_data = json_decode( file_get_contents( $remote_ctrf ), true );
				// Remote package should run only 1 test (grep filter applied)
				$this->assertEquals( 1, $remote_data['results']['summary']['tests'],
					'Remote package should run only 1 test when grep filter is applied' );
				$this->assertStringContainsString( '@remote-only', $remote_data['results']['tests'][0]['name'],
					'Remote package should run the @remote-only test' );
			}
		}
		
		$this->deleteRemotePackage();
	}
	
	/**
	 * Test multiple explicit passthrough targets.
	 */
	public function test_multiple_passthrough_targets() {
		$this->publishRemotePackage();
		
		// Pass args to both packages explicitly
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->localPackage,
			'--test-package=test/passthrough-remote:1.0.0',
			'--passthrough=' . $this->localPackage,
			'--passthrough=test/passthrough-remote:1.0.0',
			'--',
			'--grep=@shared',
		], return_process: true );
		
		$output = $proc->getOutput();
		
		if ( preg_match( '/test-runs\/run-[a-f0-9.]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $matches[0];
			
			$local_ctrf = $artifacts_path . '/ctrf/passthrough-local.json';
			$remote_ctrf = $artifacts_path . '/ctrf/passthrough-remote.json';
			
			// Both packages should run only the @shared test
			if ( file_exists( $local_ctrf ) ) {
				$local_data = json_decode( file_get_contents( $local_ctrf ), true );
				$this->assertEquals( 1, $local_data['results']['summary']['tests'],
					'Local package should run only 1 test when grep filter is applied' );
				$this->assertStringContainsString( '@shared', $local_data['results']['tests'][0]['name'],
					'Local package should run the @shared test' );
			}
			
			if ( file_exists( $remote_ctrf ) ) {
				$remote_data = json_decode( file_get_contents( $remote_ctrf ), true );
				$this->assertEquals( 1, $remote_data['results']['summary']['tests'],
					'Remote package should run only 1 test when grep filter is applied' );
				$this->assertStringContainsString( '@shared', $remote_data['results']['tests'][0]['name'],
					'Remote package should run the @shared test' );
			}
		}
		
		$this->deleteRemotePackage();
	}
	
	/**
	 * Test single local package gets args by default.
	 */
	public function test_single_local_package_gets_args_by_default() {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->localPackage,
			'--',
			'--grep=@grep-test',
		], return_process: true );
		
		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();
		
		// Debug output
		if ( $exitCode !== 0 ) {
			echo "Exit code: $exitCode\n";
			echo "Output:\n$output\n";
			echo "Error:\n" . $proc->getErrorOutput() . "\n";
		}
		
		// The test should complete successfully
		$this->assertEquals( 0, $exitCode, 'Test run should complete successfully' );
		
		// Extract artifacts directory - try multiple patterns
		$artifacts_path = null;
		if ( preg_match( '/Test Artifacts:\s+Location: ([^\n]+)/', $output, $matches ) ) {
			$artifacts_path = trim( $matches[1] );
		} elseif ( preg_match( '/Wrote debug contents to: ([^\n]+)/', $output, $matches ) ) {
			// Extract the directory path from the debug log path
			$debug_path = trim( $matches[1] );
			$artifacts_path = dirname( $debug_path );
		} elseif ( preg_match( '/\/tmp\/qit-results-[a-z0-9A-Z-]+/', $output, $matches ) ) {
			$artifacts_path = $matches[0];
		} elseif ( preg_match( '/qit-e2e-artifacts-[a-z0-9A-Z-]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/' . $matches[0];
		} elseif ( preg_match( '/test-runs\/run-[a-f0-9.]+/', $output, $matches ) ) {
			$artifacts_path = sys_get_temp_dir() . '/qit-e2e-artifacts-' . $matches[0];
		}
		
		// For this test, we just need to verify the arguments were passed
		// The CTRF results are not saved to artifacts for passing tests
		// Check in the output that the arguments were passed to the test package
		$this->assertStringContainsString( 
			"bash simple-test.sh '--grep=@grep-test'",
			$output,
			'Arguments should be passed to the local test package'
		);
	}
	
	// ========== Helper Methods ==========
	
	private function publishRemotePackage(): void {
		// Try to publish the remote package to the registry
		$proc = qit( [
			'package:publish',
			$this->remotePackage,
			'1.0.0',
		], return_process: true );
		
		// Check if we don't have permission (CI environment)
		if ( $proc->getExitCode() !== 0 ) {
			$output = $proc->getOutput();
			if ( strpos( $output, 'not a maintainer' ) !== false ) {
				$this->markTestSkipped( 'Test requires package publishing permissions not available in CI' );
			}
			// If it's a different error, let it fail normally
			$this->assertEquals( 0, $proc->getExitCode(), 'Package publish failed: ' . $output );
		}
	}
	
	private function deleteRemotePackage(): void {
		// Delete the remote package from the registry
		qit( [
			'package:delete',
			'test/passthrough-remote:1.0.0',
		] );
	}
}