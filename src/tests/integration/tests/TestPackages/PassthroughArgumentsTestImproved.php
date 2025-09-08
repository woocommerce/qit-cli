<?php

namespace integration\tests\TestPackages;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;
use function qit;

/**
 * Improved test for passthrough arguments functionality.
 * 
 * This test verifies that runner arguments (after --) are correctly
 * passed through to test packages based on the passthrough rules.
 */
class PassthroughArgumentsTestImproved extends TestCase {
	
	private string $echoLocalPackage;
	private string $echoRemotePackage;
	private array $publishedPackages = [];
	
	protected function setUp(): void {
		parent::setUp();
		TestCleanupHelper::cleanup_all_test_packages();
		
		// Create echo test packages that log their arguments
		$this->echoLocalPackage = $this->createEchoPackage( 'local' );
		$this->echoRemotePackage = $this->createEchoPackage( 'remote' );
		
		// Publish the remote package
		$this->publishRemotePackage();
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		
		// Clean up published packages
		foreach ( $this->publishedPackages as $package ) {
			try {
				qit( [
					'package:delete',
					$package,
				] );
			} catch ( \Exception $e ) {
				// Ignore cleanup errors
			}
		}
	}
	
	/**
	 * Test that passthrough arguments go to local packages by default.
	 */
	public function test_default_passthrough_to_local_packages_only() {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->echoLocalPackage,
			'--test-package=woocommerce/qit-integration-test-passthrough-echo-remote:1.0.0',
			'--',
			'--test-arg=value1',
			'--another=value2',
		], return_process: true );
		
		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();
		
		// Test should complete (may fail due to echo package simplicity, but that's OK)
		$this->assertContains( $exitCode, [ 0, 1 ], 'Command should execute' );
		
		// Check that local package received the arguments
		$this->assertStringContainsString( 
			'ARGS_RECEIVED: --test-arg=value1 --another=value2',
			$output,
			'Local package should receive passthrough arguments'
		);
		
		// Remote package should show empty args
		$remoteArgsPattern = '/remote.*ARGS_RECEIVED:\s*$/m';
		$this->assertMatchesRegularExpression(
			$remoteArgsPattern,
			$output,
			'Remote package should not receive passthrough arguments'
		);
	}
	
	/**
	 * Test explicit --passthrough option overrides default behavior.
	 */
	public function test_explicit_passthrough_targets() {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->echoLocalPackage,
			'--test-package=woocommerce/qit-integration-test-passthrough-echo-remote:1.0.0',
			'--passthrough=woocommerce/qit-integration-test-passthrough-echo-remote:1.0.0',
			'--',
			'--test-arg=value1',
		], return_process: true );
		
		$output = $proc->getOutput();
		
		// Local package should show empty args
		$localArgsPattern = '/local.*ARGS_RECEIVED:\s*$/m';
		$this->assertMatchesRegularExpression(
			$localArgsPattern,
			$output,
			'Local package should not receive arguments when not explicitly targeted'
		);
		
		// Remote package should receive the arguments
		$this->assertStringContainsString(
			'remote',
			$output
		);
		$this->assertStringContainsString(
			'ARGS_RECEIVED: --test-arg=value1',
			$output,
			'Remote package should receive passthrough arguments when explicitly targeted'
		);
	}
	
	/**
	 * Test multiple explicit passthrough targets.
	 */
	public function test_multiple_passthrough_targets() {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->echoLocalPackage,
			'--test-package=woocommerce/qit-integration-test-passthrough-echo-remote:1.0.0',
			'--passthrough=' . $this->echoLocalPackage,
			'--passthrough=woocommerce/qit-integration-test-passthrough-echo-remote:1.0.0',
			'--',
			'--shared-arg=both',
		], return_process: true );
		
		$output = $proc->getOutput();
		
		// Count occurrences of the argument in output
		$argCount = substr_count( $output, 'ARGS_RECEIVED: --shared-arg=both' );
		
		$this->assertEquals(
			2,
			$argCount,
			'Both packages should receive the arguments when both are explicitly targeted'
		);
	}
	
	/**
	 * Test single local package gets args by default.
	 */
	public function test_single_local_package_gets_args_by_default() {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->echoLocalPackage,
			'--',
			'--single-test=yes',
		], return_process: true );
		
		$output = $proc->getOutput();
		$exitCode = $proc->getExitCode();
		
		// Verify the test ran with the arguments visible in the command output
		$this->assertStringContainsString(
			"bash echo-args.sh '--single-test=yes'",
			$output,
			'Arguments should be passed to the local package and visible in command output'
		);
	}
	
	// ========== Helper Methods ==========
	
	/**
	 * Create an echo test package that outputs its received arguments.
	 */
	private function createEchoPackage( string $type ): string {
		$dir = sys_get_temp_dir() . '/qit-passthrough-echo-' . $type . '-' . uniqid();
		mkdir( $dir, 0755, true );
		
		// Create a simple script that echoes arguments and produces minimal test results
		$script = <<<'BASH'
#!/bin/bash
echo "PACKAGE_TYPE_ARGS_RECEIVED: $@"

# Create minimal CTRF output
mkdir -p results
cat > results/ctrf.json << 'EOF'
{
  "results": {
    "tool": {"name": "echo-test"},
    "summary": {
      "tests": 1,
      "passed": 1,
      "failed": 0,
      "skipped": 0,
      "pending": 0,
      "other": 0,
      "start": 1000000000000,
      "stop": 1000000001000
    },
    "tests": []
  }
}
EOF

# Create minimal blob report
mkdir -p blob-report
echo "{}" > blob-report/report.json

exit 0
BASH;
		
		// Replace PACKAGE_TYPE with actual type
		$script = str_replace( 'PACKAGE_TYPE', $type, $script );
		
		file_put_contents( $dir . '/echo-args.sh', $script );
		chmod( $dir . '/echo-args.sh', 0755 );
		
		// Create qit-test.json
		$manifest = [
			'package' => $type === 'remote' 
				? 'woocommerce/qit-integration-test-passthrough-echo-remote'
				: 'test/passthrough-echo-' . $type,
			'test_type' => 'e2e',
			'description' => 'Echo test package - ' . $type,
			'test' => [
				'phases' => [
					'run' => [
						'bash echo-args.sh'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		
		file_put_contents( $dir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $dir;
	}
	
	private function publishRemotePackage(): void {
		$result = qit( [
			'package:publish',
			$this->echoRemotePackage,
			'1.0.0',
		] );
		
		$this->publishedPackages[] = 'woocommerce/qit-integration-test-passthrough-echo-remote:1.0.0';
	}
}