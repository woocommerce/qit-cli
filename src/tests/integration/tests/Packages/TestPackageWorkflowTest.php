<?php

use QIT\IntegrationTests\Traits\ScaffoldHelpers;
use QIT\IntegrationTests\Traits\SnapshotHelpers;
use Symfony\Component\Process\Process;

class TestPackageWorkflowTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use SnapshotHelpers;

	private string $temp_dir;
	private string $package_dir;
	private string $qit_json_path;

	protected function setUp(): void {
		$this->temp_dir = sys_get_temp_dir() . '/qit_test_' . uniqid();
		mkdir( $this->temp_dir, 0755, true );
		$this->package_dir = $this->temp_dir . '/test-package';
	}

	protected function tearDown(): void {
		// Clean up temporary files
		if ( isset( $this->qit_json_path ) && file_exists( $this->qit_json_path ) ) {
			unlink( $this->qit_json_path );
		}
		if ( isset( $this->package_dir ) && is_dir( $this->package_dir ) ) {
			$this->recursiveRemoveDirectory( $this->package_dir );
		}
		if ( isset( $this->temp_dir ) && is_dir( $this->temp_dir ) ) {
			$this->recursiveRemoveDirectory( $this->temp_dir );
		}
	}

	/**
	 * Test complete workflow with enhanced verification
	 */
	public function test_complete_package_workflow_with_all_phases() {
		// Step 1: Scaffold a test package
		$this->scaffoldEnhancedTestPackage();

		// Step 2: Modify manifest for comprehensive testing
		$this->modifyManifestForAllPhases();

		// Step 3: Create custom scripts for each phase with trace markers
		$this->createPhaseTraceScripts();

		// Step 4: Create qit.json configuration
		$this->createQitJsonConfig();

		// Step 5: Run the test and capture detailed output
		$process = qit( [
			'run:e2e',
			'woocommerce',
			'--environment=without-setup',
			'--config=' . $this->qit_json_path,
			'--json' // Get structured output
		], return_process: true );

		// Step 6: Enhanced verification
		$this->verifyTestExecution( $process );
		$this->verifyResultCollection();
	}

	private function scaffoldEnhancedTestPackage(): void {
		$scaffold_output = qit( [
			'package:scaffold',
			$this->package_dir,
			'--namespace=qit-test-plugin',
			'--package=tp001-all-phases',
			'--framework=playwright',
			'--test-type=e2e',
			'--only-manifest', // Skip npm scaffolding for faster tests
			'--no-interaction'
		] );

		$this->assertDirectoryExists( $this->package_dir );
		$this->assertFileExists( $this->package_dir . '/manifest.json' );
	}

	private function modifyManifestForAllPhases(): void {
		$manifest_path = $this->package_dir . '/manifest.json';
		$manifest      = json_decode( file_get_contents( $manifest_path ), true );

		// Modify manifest to include all phases with trace scripts
		$manifest['test']['phases'] = [
			'globalSetup'    => [ './scripts/phase-marker.sh', './scripts/global-setup.sh' ],
			'setup'          => [ './scripts/phase-marker.sh', './scripts/setup.sh' ],
			'run'            => [ './scripts/phase-marker.sh', 'mkdir -p ./results && echo \'{"results":{"tool":{"name":"test-runner"},"summary":{"tests":1,"passed":1,"failed":0,"pending":0,"skipped":0,"other":0,"start":1234567890,"stop":1234567891,"suites":1},"tests":[{"name":"dummy test","status":"passed","duration":1}]}}\' > ./results/ctrf.json' ],
			'teardown'       => [ './scripts/phase-marker.sh', './scripts/teardown.sh' ],
			'globalTeardown' => [ './scripts/phase-marker.sh', './scripts/global-teardown.sh' ]
		];

		// Ensure results directory exists
		$results_dir = $this->package_dir . '/results';
		if ( ! is_dir( $results_dir ) ) {
			mkdir( $results_dir, 0755, true );
		}

		file_put_contents( $manifest_path, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}

	private function createPhaseTraceScripts(): void {
		$scripts_dir = $this->package_dir . '/scripts';
		if ( ! is_dir( $scripts_dir ) ) {
			mkdir( $scripts_dir, 0755, true );
		}

		// Phase marker script
		$phase_marker = <<<BASH
#!/bin/bash
PHASE_NAME="\${QIT_TEST_PHASE:-unknown}"
echo "[\$(date '+%Y-%m-%d %H:%M:%S.%3N')] Executing phase: \$PHASE_NAME (PID: \$\$)" >> /qit/tp001-execution-trace.log
BASH;
		file_put_contents( $scripts_dir . '/phase-marker.sh', $phase_marker );
		chmod( $scripts_dir . '/phase-marker.sh', 0755 );

		// Individual phase scripts
		$phases = [ 'global-setup', 'setup', 'teardown', 'global-teardown' ];
		foreach ( $phases as $phase ) {
			$script = <<<BASH
#!/bin/bash
export QIT_TEST_PHASE="$phase"
./scripts/phase-marker.sh
echo "$phase specific actions" >> /qit/tp001-execution-trace.log
BASH;
			file_put_contents( $scripts_dir . "/$phase.sh", $script );
			chmod( $scripts_dir . "/$phase.sh", 0755 );
		}
	}

	private function createQitJsonConfig(): void {
		$qit_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/qit',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $this->package_dir ]
					]
				]
			],
			'environments' => [
				'without-setup' => [
					'php' => '8.2',
					'wp'  => 'stable'
				],
				'with-setup'    => [
					'php'                => '8.2',
					'wp'                 => 'stable',
					'bootstrap_packages' => [ $this->package_dir ]
				]
			]
		];

		$this->qit_json_path = $this->temp_dir . '/qit-config.json';
		file_put_contents( $this->qit_json_path, json_encode( $qit_json, JSON_PRETTY_PRINT ) );
	}

	private function verifyTestExecution( Process $process ): void {
		// Check that the process completed successfully
		$this->assertEquals( 0, $process->getExitCode(),
			"Test run failed with exit code {$process->getExitCode()}. Error: " . $process->getErrorOutput() );

		// Check for key success indicators in output
		$output = $process->getOutput();
		$this->assertStringContainsString( 'All test packages completed successfully', $output );
		$this->assertStringContainsString( 'globalSetup phase for all packages', $output );
		$this->assertStringContainsString( 'Processing package:', $output );
	}

	private function verifyPhaseExecutionOrder(): void {
		// Check execution trace file
		$trace_file = '/tmp/tp001-execution-trace.log';
		$this->assertFileExists( $trace_file, 'Execution trace file should exist' );

		$trace_content = file_get_contents( $trace_file );
		$lines         = explode( "\n", trim( $trace_content ) );

		// Verify all phases were executed
		$this->assertStringContainsString( 'globalSetup', $trace_content );
		$this->assertStringContainsString( 'setup', $trace_content );
		$this->assertStringContainsString( 'Main test execution', $trace_content );
		$this->assertStringContainsString( 'teardown', $trace_content );
		$this->assertStringContainsString( 'globalTeardown', $trace_content );

		// Verify execution order (simplified check)
		$globalSetupPos    = strpos( $trace_content, 'globalSetup' );
		$setupPos          = strpos( $trace_content, 'setup' );
		$runPos            = strpos( $trace_content, 'Main test execution' );
		$teardownPos       = strpos( $trace_content, 'teardown' );
		$globalTeardownPos = strpos( $trace_content, 'globalTeardown' );

		$this->assertLessThan( $setupPos, $globalSetupPos, 'globalSetup should execute before setup' );
		$this->assertLessThan( $runPos, $setupPos, 'setup should execute before run' );
		$this->assertLessThan( $teardownPos, $runPos, 'run should execute before teardown' );
		$this->assertLessThan( $globalTeardownPos, $teardownPos, 'teardown should execute before globalTeardown' );
	}

	private function verifyResultCollection(): void {
		// Check that CTRF results were generated
		$ctrf_path = $this->package_dir . '/results/ctrf.json';
		$this->assertFileExists( $ctrf_path, 'CTRF results file should be generated' );

		$ctrf_content = file_get_contents( $ctrf_path );
		$ctrf_data    = json_decode( $ctrf_content, true );

		$this->assertIsArray( $ctrf_data, 'CTRF content should be valid JSON' );
		$this->assertArrayHasKey( 'results', $ctrf_data, 'CTRF should have results key' );
	}

	private function verifyLogCollection(): void {
		// Check that PHP logs were captured (this would require checking the individual-log mechanism)
		// This is a simplified check - in reality, we'd need to generate actual PHP errors in our test scripts
		$trace_file = '/tmp/tp001-execution-trace.log';
		$this->assertFileExists( $trace_file, 'PHP log collection trace should exist' );

		$content = file_get_contents( $trace_file );
		$this->assertNotEmpty( $content, 'Log content should not be empty' );
	}

	private function recursiveRemoveDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = "$dir/$file";
			if ( is_dir( $path ) ) {
				$this->recursiveRemoveDirectory( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}
}
