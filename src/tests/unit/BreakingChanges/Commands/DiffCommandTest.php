<?php

namespace QIT_CLI_Tests\BreakingChanges\Commands;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Commands\DiffCommand;
use QIT_CLI\BreakingChanges\Diff\HookDiffer;
use QIT_CLI\BreakingChanges\Diff\SymbolDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Extraction\FileParser;
use QIT_CLI\BreakingChanges\PluginSourceResolver;
use QIT_CLI\BreakingChanges\Renderers\DiffRenderer;
use QIT_CLI\CachedDownloader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DiffCommandTest extends TestCase {
	private string $fixtures_dir;

	protected function setUp(): void {
		parent::setUp();
		$this->fixtures_dir = dirname( __DIR__ ) . '/fixtures';
	}

	private function make_command_tester(): CommandTester {
		$downloader = $this->createMock( CachedDownloader::class );
		$resolver   = new PluginSourceResolver( $downloader );
		$extractor  = new DirectoryExtractor( new FileParser() );

		$command = new DiffCommand(
			$resolver,
			$extractor,
			new SymbolDiffer(),
			new HookDiffer(),
			new DiffRenderer()
		);

		$application = new Application();
		$application->add( $command );

		return new CommandTester( $application->find( 'breaking-changes:diff' ) );
	}

	public function test_detects_breaking_changes_between_fixtures(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'slug'   => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'  => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'  => $this->fixtures_dir . '/sample-plugin-v2',
		] );

		// Should exit 1 because there are removals.
		$this->assertEquals( 1, $exit_code );

		$output = $tester->getDisplay();
		$this->assertStringContainsString( 'Breaking changes detected', $output );
	}

	public function test_no_changes_returns_success(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'slug'  => $this->fixtures_dir . '/sample-plugin-v1',
			'--old' => $this->fixtures_dir . '/sample-plugin-v1',
			'--new' => $this->fixtures_dir . '/sample-plugin-v1',
		] );

		$this->assertEquals( 0, $exit_code );

		$output = $tester->getDisplay();
		$this->assertStringContainsString( 'No breaking changes detected', $output );
	}

	public function test_json_output_format(): void {
		$tester = $this->make_command_tester();

		$tester->execute( [
			'slug'     => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'    => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'    => $this->fixtures_dir . '/sample-plugin-v2',
			'--format' => 'json',
		] );

		$output = $tester->getDisplay();
		$data   = json_decode( $output, true );

		$this->assertIsArray( $data );
		$this->assertTrue( $data['summary']['has_breaking_changes'] );
		$this->assertGreaterThan( 0, $data['summary']['removed_symbols'] );
		$this->assertGreaterThan( 0, $data['summary']['removed_hooks'] );
	}

	public function test_github_output_format(): void {
		$tester = $this->make_command_tester();

		$tester->execute( [
			'slug'     => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'    => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'    => $this->fixtures_dir . '/sample-plugin-v2',
			'--format' => 'github',
		] );

		$output = $tester->getDisplay();
		$this->assertStringContainsString( '::error file=', $output );
		$this->assertStringContainsString( '::notice file=', $output );
	}

	public function test_requires_old_option(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'slug' => 'some-plugin',
		] );

		$this->assertEquals( 1, $exit_code );
		$this->assertStringContainsString( '--old option is required', $tester->getDisplay() );
	}
}
