<?php

namespace QIT_CLI_Tests\Environment;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\PackagePhaseRunner;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Configuration\TestPackageManifest;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class PackagePhaseRunnerTest extends TestCase {
	private PackagePhaseRunner $runner;
	private Docker $docker;
	private OutputInterface $output;
	private E2EEnvInfo $env_info;
	private string $temp_dir;

	protected function setUp(): void {
		parent::setUp();
		
		$this->docker = $this->createMock( Docker::class );
		$this->output = new BufferedOutput();
		$this->runner = new PackagePhaseRunner( $this->docker, $this->output );
		
		$this->env_info = new E2EEnvInfo();
		$this->env_info->env_id = 'test-env';
		
		// Create temporary directory for test packages
		$this->temp_dir = sys_get_temp_dir() . '/qit_test_' . uniqid();
		mkdir( $this->temp_dir, 0777, true );
	}

	protected function tearDown(): void {
		// Clean up temporary directory
		if ( is_dir( $this->temp_dir ) ) {
			$this->deleteDirectory( $this->temp_dir );
		}
		parent::tearDown();
	}

	/**
	 * Test venue determination logic
	 */
	public function test_determine_execution_venue(): void {
		// Use reflection to access private method
		$reflection = new \ReflectionClass( PackagePhaseRunner::class );
		$method = $reflection->getMethod( 'determine_execution_venue' );
		$method->setAccessible( true );

		// Test shell scripts go to container
		$this->assertEquals( 'container', $method->invoke( $this->runner, './setup.sh' ) );
		$this->assertEquals( 'container', $method->invoke( $this->runner, 'scripts/init.sh' ) );
		$this->assertEquals( 'container', $method->invoke( $this->runner, '/path/to/script.sh' ) );
		$this->assertEquals( 'container', $method->invoke( $this->runner, '  ./test.sh  ' ) ); // with whitespace

		// Test non-shell commands go to host
		$this->assertEquals( 'host', $method->invoke( $this->runner, 'npm install' ) );
		$this->assertEquals( 'host', $method->invoke( $this->runner, 'composer install' ) );
		$this->assertEquals( 'host', $method->invoke( $this->runner, 'yarn build' ) );
		$this->assertEquals( 'host', $method->invoke( $this->runner, './setup.php' ) );
		$this->assertEquals( 'host', $method->invoke( $this->runner, 'python setup.py' ) );
	}

	/**
	 * Test that run_phase returns correct executed count
	 */
	public function test_run_phase_returns_executed_count(): void {
		// Create test package directory and manifest
		$package_dir = $this->temp_dir . '/test-package';
		mkdir( $package_dir, 0777, true );
		
		$manifest = [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'e2e',
			'lifecycle' => [
				'setup' => [
					'npm install',
					'./build.sh'
				],
				'run' => [
					'npm test'
				],
				'teardown' => [
					'./cleanup.sh'
				]
			]
		];
		
		file_put_contents( $package_dir . '/manifest.json', json_encode( $manifest ) );

		// Mock the parser to return our test manifest
		$mock_parser = $this->createMock( TestPackageManifestParser::class );
		$mock_manifest = $this->createMock( TestPackageManifest::class );
		
		$mock_manifest->method( 'getPhaseCommands' )
			->willReturnMap( [
				[ 'setup', [ 'npm install', './build.sh' ] ],
				[ 'run', [ 'npm test' ] ],
				[ 'teardown', [ './cleanup.sh' ] ]
			] );
		
		$mock_parser->method( 'parse' )->willReturn( $mock_manifest );

		// Use reflection to inject the mock parser
		$reflection = new \ReflectionClass( $this->runner );
		$parser_property = $reflection->getProperty( 'parser' );
		$parser_property->setAccessible( true );
		$parser_property->setValue( $this->runner, $mock_parser );

		// Test setup phase (2 commands)
		$count = $this->runner->run_phase( $this->env_info, 'setup', 'test-package', $package_dir );
		$this->assertEquals( 2, $count );

		// Test run phase (1 command)
		$count = $this->runner->run_phase( $this->env_info, 'run', 'test-package', $package_dir );
		$this->assertEquals( 1, $count );

		// Test teardown phase (1 command)
		$count = $this->runner->run_phase( $this->env_info, 'teardown', 'test-package', $package_dir );
		$this->assertEquals( 1, $count );
	}

	/**
	 * Test that run_phase returns 0 for missing manifest
	 */
	public function test_run_phase_missing_manifest(): void {
		$package_dir = $this->temp_dir . '/no-manifest';
		mkdir( $package_dir, 0777, true );

		$count = $this->runner->run_phase( $this->env_info, 'setup', 'no-manifest', $package_dir );
		$this->assertEquals( 0, $count );

		// Check output contains warning message
		$output = $this->output->fetch();
		$this->assertStringContainsString( 'no manifest.json', $output );
	}

	/**
	 * Test that run_phase returns 0 for empty phase
	 */
	public function test_run_phase_empty_phase(): void {
		$package_dir = $this->temp_dir . '/empty-phase';
		mkdir( $package_dir, 0777, true );
		
		$manifest = [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'e2e',
			'lifecycle' => []
		];
		
		file_put_contents( $package_dir . '/manifest.json', json_encode( $manifest ) );

		// Mock the parser
		$mock_parser = $this->createMock( TestPackageManifestParser::class );
		$mock_manifest = $this->createMock( TestPackageManifest::class );
		
		$mock_manifest->method( 'getPhaseCommands' )->willReturn( [] );
		$mock_parser->method( 'parse' )->willReturn( $mock_manifest );

		// Inject mock parser
		$reflection = new \ReflectionClass( $this->runner );
		$parser_property = $reflection->getProperty( 'parser' );
		$parser_property->setAccessible( true );
		$parser_property->setValue( $this->runner, $mock_parser );

		$count = $this->runner->run_phase( $this->env_info, 'setup', 'empty-phase', $package_dir );
		$this->assertEquals( 0, $count );
	}

	/**
	 * Test that globalSetup phase works correctly
	 */
	public function test_global_setup_phase(): void {
		$package_dir = $this->temp_dir . '/global-setup';
		mkdir( $package_dir, 0777, true );
		
		$manifest = [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup',
			'lifecycle' => [
				'globalSetup' => [
					'./init-database.sh',
					'composer install'
				]
			]
		];
		
		file_put_contents( $package_dir . '/manifest.json', json_encode( $manifest ) );

		// Mock the parser
		$mock_parser = $this->createMock( TestPackageManifestParser::class );
		$mock_manifest = $this->createMock( TestPackageManifest::class );
		
		$mock_manifest->method( 'getPhaseCommands' )
			->with( 'globalSetup' )
			->willReturn( [ './init-database.sh', 'composer install' ] );
		
		$mock_parser->method( 'parse' )->willReturn( $mock_manifest );

		// Inject mock parser
		$reflection = new \ReflectionClass( $this->runner );
		$parser_property = $reflection->getProperty( 'parser' );
		$parser_property->setAccessible( true );
		$parser_property->setValue( $this->runner, $mock_parser );

		$count = $this->runner->run_phase( $this->env_info, 'globalSetup', 'global-setup', $package_dir );
		$this->assertEquals( 2, $count );
	}

	/**
	 * Helper method to recursively delete directory
	 */
	private function deleteDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->deleteDirectory( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}