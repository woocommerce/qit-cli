<?php

namespace QIT_CLI_Tests\Fixtures;

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Environment\PackagePhaseRunner;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\PackageOrchestrator;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Test the -- pass-through behavior for runner_args
 */
class RunE2EPassThroughTest extends TestCase {

	/**
	 * Test that runner_args are passed to run phase commands
	 */
	public function test_runner_args_passed_to_run_phase(): void {
		// Create a mock manifest with a run phase
		$manifest = $this->createMock( TestPackageManifest::class );
		$manifest->method( 'hasPhase' )->with( 'run' )->willReturn( true );
		$manifest->method( 'getPhaseCommands' )->with( 'run' )->willReturn( [ 'npx playwright test' ] );
		
		// Create temp directory for package
		$tempDir = sys_get_temp_dir() . '/test-package-' . uniqid();
		mkdir( $tempDir );
		file_put_contents( $tempDir . '/qit-test.json', json_encode( [
			'package' => 'test-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 'npx playwright test' ]
				]
			]
		] ) );
		
		// Create mocks
		$envInfo = $this->createMock( E2EEnvInfo::class );
		$orchestrator = $this->createMock( PackageOrchestrator::class );
		$output = new NullOutput();
		
		// Create Docker mock that will capture the command
		$docker = $this->createMock( \QIT_CLI\Environment\Docker::class );
		
		// Expect the command to include runner_args
		$capturedCommand = null;
		$orchestrator->expects( $this->once() )
			->method( 'show_command' )
			->willReturnCallback( function( $cmd, $context ) use ( &$capturedCommand ) {
				$capturedCommand = $cmd;
			} );
		
		// Create PackagePhaseRunner
		$envVars = $this->createMock( \QIT_CLI\Environment\EnvironmentVars::class );
		$runner = new PackagePhaseRunner( $docker, $output, $envVars );
		
		// Test with runner_args
		$runner_args = [ '--fail-fast', '--workers=2', '--project=chromium' ];
		
		try {
			$runner->run_phase(
				$envInfo,
				'run',
				'test-package',
				$tempDir,
				null,
				$orchestrator,
				$runner_args
			);
		} catch ( \Exception $e ) {
			// Expected to fail since we're mocking, but we can check the command
		}
		
		// Verify the command includes the runner_args
		$this->assertNotNull( $capturedCommand );
		$this->assertStringContainsString( '--fail-fast', $capturedCommand );
		$this->assertStringContainsString( '--workers=2', $capturedCommand );
		$this->assertStringContainsString( '--project=chromium', $capturedCommand );
		
		// Clean up
		rmdir( $tempDir );
	}
	
	/**
	 * Test that runner_args are NOT passed to non-run phases
	 */
	public function test_runner_args_not_passed_to_setup_phase(): void {
		// Create a mock manifest with a setup phase
		$manifest = $this->createMock( TestPackageManifest::class );
		$manifest->method( 'hasPhase' )->with( 'setup' )->willReturn( true );
		$manifest->method( 'getPhaseCommands' )->with( 'setup' )->willReturn( [ 'npm install' ] );
		
		// Create temp directory for package
		$tempDir = sys_get_temp_dir() . '/test-package-' . uniqid();
		mkdir( $tempDir );
		file_put_contents( $tempDir . '/qit-test.json', json_encode( [
			'package' => 'test-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'setup' => [ 'npm install' ]
				]
			]
		] ) );
		
		// Create mocks
		$envInfo = $this->createMock( E2EEnvInfo::class );
		$orchestrator = $this->createMock( PackageOrchestrator::class );
		$output = new NullOutput();
		
		// Create Docker mock
		$docker = $this->createMock( \QIT_CLI\Environment\Docker::class );
		
		// Expect the command to NOT include runner_args
		$capturedCommand = null;
		$orchestrator->expects( $this->once() )
			->method( 'show_command' )
			->willReturnCallback( function( $cmd, $context ) use ( &$capturedCommand ) {
				$capturedCommand = $cmd;
			} );
		
		// Create PackagePhaseRunner
		$envVars = $this->createMock( \QIT_CLI\Environment\EnvironmentVars::class );
		$runner = new PackagePhaseRunner( $docker, $output, $envVars );
		
		// Test with runner_args (should be ignored for setup phase)
		$runner_args = [ '--fail-fast', '--workers=2' ];
		
		try {
			$runner->run_phase(
				$envInfo,
				'setup',
				'test-package',
				$tempDir,
				null,
				$orchestrator,
				$runner_args
			);
		} catch ( \Exception $e ) {
			// Expected to fail since we're mocking, but we can check the command
		}
		
		// Verify the command does NOT include the runner_args
		$this->assertNotNull( $capturedCommand );
		$this->assertEquals( 'npm install', $capturedCommand );
		$this->assertStringNotContainsString( '--fail-fast', $capturedCommand );
		$this->assertStringNotContainsString( '--workers=2', $capturedCommand );
		
		// Clean up
		rmdir( $tempDir );
	}
	
	/**
	 * Test that multiple run commands all get the runner_args
	 */
	public function test_multiple_run_commands_get_args(): void {
		// Create temp directory for package
		$tempDir = sys_get_temp_dir() . '/test-package-' . uniqid();
		mkdir( $tempDir );
		file_put_contents( $tempDir . '/qit-test.json', json_encode( [
			'package' => 'test-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [
						'npx playwright test tests/smoke.spec.js',
						'npx playwright test tests/regression.spec.js'
					]
				]
			]
		] ) );
		
		// Create mocks
		$envInfo = $this->createMock( E2EEnvInfo::class );
		$orchestrator = $this->createMock( PackageOrchestrator::class );
		$output = new NullOutput();
		
		// Create Docker mock
		$docker = $this->createMock( \QIT_CLI\Environment\Docker::class );
		
		// Capture all commands
		$capturedCommands = [];
		$orchestrator->expects( $this->exactly( 2 ) )
			->method( 'show_command' )
			->willReturnCallback( function( $cmd, $context ) use ( &$capturedCommands ) {
				$capturedCommands[] = $cmd;
			} );
		
		// Create PackagePhaseRunner
		$envVars = $this->createMock( \QIT_CLI\Environment\EnvironmentVars::class );
		$runner = new PackagePhaseRunner( $docker, $output, $envVars );
		
		// Test with runner_args
		$runner_args = [ '--headed', '--debug' ];
		
		try {
			$runner->run_phase(
				$envInfo,
				'run',
				'test-package',
				$tempDir,
				null,
				$orchestrator,
				$runner_args
			);
		} catch ( \Exception $e ) {
			// Expected to fail since we're mocking, but we can check the commands
		}
		
		// Verify both commands include the runner_args
		$this->assertCount( 2, $capturedCommands );
		
		foreach ( $capturedCommands as $cmd ) {
			$this->assertStringContainsString( '--headed', $cmd );
			$this->assertStringContainsString( '--debug', $cmd );
		}
		
		// Clean up
		rmdir( $tempDir );
	}
	
	/**
	 * Test that --shard arguments are filtered out with a warning
	 */
	public function test_shard_args_filtered(): void {
		// Create temp directory for package
		$tempDir = sys_get_temp_dir() . '/test-package-' . uniqid();
		mkdir( $tempDir );
		file_put_contents( $tempDir . '/qit-test.json', json_encode( [
			'package' => 'test-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 'npx playwright test' ]
				]
			]
		] ) );
		
		// Create mocks
		$envInfo = $this->createMock( E2EEnvInfo::class );
		$orchestrator = $this->createMock( PackageOrchestrator::class );
		
		// Create output to capture warnings
		$output = $this->createMock( \Symfony\Component\Console\Output\OutputInterface::class );
		
		// Expect warning about sharding
		$output->expects( $this->exactly( 2 ) )
			->method( 'writeln' )
			->withConsecutive(
				[ $this->stringContains( 'Warning: --shard is not supported' ) ],
				[ $this->stringContains( 'Tests will run without sharding' ) ]
			);
		
		// Create Docker mock
		$docker = $this->createMock( \QIT_CLI\Environment\Docker::class );
		
		// Capture the command
		$capturedCommand = null;
		$orchestrator->expects( $this->once() )
			->method( 'show_command' )
			->willReturnCallback( function( $cmd, $context ) use ( &$capturedCommand ) {
				$capturedCommand = $cmd;
			} );
		
		// Create PackagePhaseRunner
		$envVars = $this->createMock( \QIT_CLI\Environment\EnvironmentVars::class );
		$runner = new PackagePhaseRunner( $docker, $output, $envVars );
		
		// Test with shard argument that should be filtered
		$runner_args = [ '--shard=1/3', '--grep=checkout', '--headed' ];
		
		try {
			$runner->run_phase(
				$envInfo,
				'run',
				'test-package',
				$tempDir,
				null,
				$orchestrator,
				$runner_args
			);
		} catch ( \Exception $e ) {
			// Expected to fail since we're mocking
		}
		
		// Verify the command does NOT include --shard but includes other args
		$this->assertNotNull( $capturedCommand );
		$this->assertStringNotContainsString( '--shard', $capturedCommand );
		$this->assertStringContainsString( '--grep=checkout', $capturedCommand );
		$this->assertStringContainsString( '--headed', $capturedCommand );
		
		// Clean up
		rmdir( $tempDir );
	}
	
	/**
	 * Test that runner_args with special characters are properly escaped
	 */
	public function test_runner_args_escaping(): void {
		// Create temp directory for package
		$tempDir = sys_get_temp_dir() . '/test-package-' . uniqid();
		mkdir( $tempDir );
		file_put_contents( $tempDir . '/qit-test.json', json_encode( [
			'package' => 'test-package',
			'namespace' => 'test',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 'npx playwright test' ]
				]
			]
		] ) );
		
		// Create mocks
		$envInfo = $this->createMock( E2EEnvInfo::class );
		$orchestrator = $this->createMock( PackageOrchestrator::class );
		$output = new NullOutput();
		
		// Create Docker mock
		$docker = $this->createMock( \QIT_CLI\Environment\Docker::class );
		
		// Capture the command
		$capturedCommand = null;
		$orchestrator->expects( $this->once() )
			->method( 'show_command' )
			->willReturnCallback( function( $cmd, $context ) use ( &$capturedCommand ) {
				$capturedCommand = $cmd;
			} );
		
		// Create PackagePhaseRunner
		$envVars = $this->createMock( \QIT_CLI\Environment\EnvironmentVars::class );
		$runner = new PackagePhaseRunner( $docker, $output, $envVars );
		
		// Test with runner_args containing special characters
		$runner_args = [ 
			'--grep=Test with spaces',
			'--reporter=json',
			'--output=results/test$.json'
		];
		
		try {
			$runner->run_phase(
				$envInfo,
				'run',
				'test-package',
				$tempDir,
				null,
				$orchestrator,
				$runner_args
			);
		} catch ( \Exception $e ) {
			// Expected to fail since we're mocking, but we can check the command
		}
		
		// Verify the command has properly escaped arguments
		$this->assertNotNull( $capturedCommand );
		// Arguments should be escaped (quotes around values with spaces)
		$this->assertStringContainsString( "'--grep=Test with spaces'", $capturedCommand );
		$this->assertStringContainsString( "'--output=results/test$.json'", $capturedCommand );
		
		// Clean up
		rmdir( $tempDir );
	}
}