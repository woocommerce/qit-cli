<?php

namespace QIT_CLI_Tests\BreakingChanges\Commands;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Commands\ScanCommand;
use QIT_CLI\BreakingChanges\Diff\HookDiffer;
use QIT_CLI\BreakingChanges\Diff\SymbolDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Extraction\FileParser;
use QIT_CLI\BreakingChanges\PluginSourceResolver;
use QIT_CLI\BreakingChanges\Renderers\ScanRenderer;
use QIT_CLI\BreakingChanges\Scanner\ReferenceScanner;
use QIT_CLI\CachedDownloader;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ScanCommandTest extends TestCase {
	private string $fixtures_dir;

	protected function setUp(): void {
		parent::setUp();
		$this->fixtures_dir = dirname( __DIR__ ) . '/fixtures';
	}

	private function make_command_tester(): CommandTester {
		$downloader = $this->createMock( CachedDownloader::class );
		$zipper     = $this->createMock( Zipper::class );
		$parser     = new FileParser();
		$resolver   = new PluginSourceResolver( $downloader, $zipper );

		$command = new ScanCommand(
			$resolver,
			new DirectoryExtractor( $parser ),
			new SymbolDiffer(),
			new HookDiffer(),
			new ReferenceScanner( $parser ),
			new ScanRenderer()
		);

		$application = new Application();
		$application->add( $command );

		return new CommandTester( $application->find( 'breaking-changes:scan' ) );
	}

	public function test_finds_breaking_references(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'target'       => $this->fixtures_dir . '/target-plugin',
			'--dependency' => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'        => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'        => $this->fixtures_dir . '/sample-plugin-v2',
		] );

		$this->assertEquals( 1, $exit_code );

		$output = $tester->getDisplay();
		$this->assertStringContainsString( 'reference(s) to removed symbols/hooks', $output );
	}

	public function test_returns_success_when_no_breaking_references(): void {
		$tester = $this->make_command_tester();

		// Scan v2 against v1→v2 changes (v2 doesn't reference its own removed symbols).
		$exit_code = $tester->execute( [
			'target'       => $this->fixtures_dir . '/sample-plugin-v2',
			'--dependency' => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'        => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'        => $this->fixtures_dir . '/sample-plugin-v2',
		] );

		$this->assertEquals( 0, $exit_code );
	}

	public function test_json_output_format(): void {
		$tester = $this->make_command_tester();

		$tester->execute( [
			'target'       => $this->fixtures_dir . '/target-plugin',
			'--dependency' => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'        => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'        => $this->fixtures_dir . '/sample-plugin-v2',
			'--format'     => 'json',
		] );

		$data = json_decode( $tester->getDisplay(), true );

		$this->assertIsArray( $data );
		$this->assertTrue( $data['has_breaking_references'] );
		$this->assertGreaterThan( 0, $data['reference_count'] );
	}

	public function test_github_output_format(): void {
		$tester = $this->make_command_tester();

		$tester->execute( [
			'target'       => $this->fixtures_dir . '/target-plugin',
			'--dependency' => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'        => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'        => $this->fixtures_dir . '/sample-plugin-v2',
			'--format'     => 'github',
		] );

		$output = $tester->getDisplay();
		$this->assertStringContainsString( '::error file=', $output );
	}

	public function test_requires_dependency_option(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'target' => 'some-plugin',
		] );

		$this->assertEquals( 1, $exit_code );
		$this->assertStringContainsString( '--dependency option is required', $tester->getDisplay() );
	}

	public function test_requires_old_option(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'target'       => 'some-plugin',
			'--dependency' => 'woocommerce',
		] );

		$this->assertEquals( 1, $exit_code );
		$this->assertStringContainsString( '--old option is required', $tester->getDisplay() );
	}

	public function test_no_breaking_changes_in_dependency(): void {
		$tester = $this->make_command_tester();

		// Same version for old and new — no changes.
		$exit_code = $tester->execute( [
			'target'       => $this->fixtures_dir . '/target-plugin',
			'--dependency' => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'        => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'        => $this->fixtures_dir . '/sample-plugin-v1',
		] );

		$this->assertEquals( 0, $exit_code );
		$this->assertStringContainsString( 'No breaking changes in dependency', $tester->getDisplay() );
	}
}
