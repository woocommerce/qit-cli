<?php

namespace QIT_CLI_Tests;

use Symfony\Component\Process\Process;

class EnvironmentDanglingCleanupTest extends QITTestCase {
	public function setUp(): void {
		#if ( getenv( 'CI' ) !== 'true' ) {
		#	$this->markTestSkipped( 'This test is only run in CI environments.' );
		#}
		parent::setUp();
	}

	public function tearDown(): void {
		$envUpProcess = new Process( [ PHP_BINARY, __DIR__ . '/../qit-cli.php', 'env:down' ] );
		$envUpProcess->run();
		parent::tearDown();
	}

	public function testCleanupDangling() {
		// Step 1: Invoke env:up command
		$envUpProcess = new Process( [ PHP_BINARY, __DIR__ . '/../qit-cli.php', 'env:up' ] );
		$envUpProcess->run();

		// Check if the command executed successfully
		$this->assertTrue( $envUpProcess->isSuccessful(), $envUpProcess->getOutput() . $envUpProcess->getErrorOutput() );

		// Step 2: Create test conditions for dangling resources

		// Your logic to create dangling resources goes here
		// ...

		// Step 3: Invoke cleanup_dangling method
		$cleanup = \QIT_CLI\App::make( \QIT_CLI\Environment\EnvironmentDanglingCleanup::class );
		$cleanup->cleanup_dangling();

		// Step 4: Assert the outcome
		// Use Process to run docker commands and assert their outputs
		// Example: Assert that a certain container is no longer running
		$process = new Process( [ 'docker', 'ps', '-a' ] );
		$process->run();
		$this->assertStringNotContainsString( 'danglingContainerName', $process->getOutput() );

		// Similar assertions for networks, directories, etc.
	}
}
