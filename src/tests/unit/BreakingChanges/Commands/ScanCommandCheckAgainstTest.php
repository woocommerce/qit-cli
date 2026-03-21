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
use QIT_CLI\BreakingChanges\WooDevelopedFetcher;
use QIT_CLI\CachedDownloader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ScanCommandCheckAgainstTest extends TestCase {
	private string $fixtures_dir;

	protected function setUp(): void {
		parent::setUp();
		$this->fixtures_dir = dirname( __DIR__ ) . '/fixtures';
	}

	private function make_command_tester( ?WooDevelopedFetcher $fetcher = null ): CommandTester {
		$downloader = $this->createMock( CachedDownloader::class );
		$parser     = new FileParser();
		$resolver   = new PluginSourceResolver( $downloader );

		$command = new ScanCommand(
			$resolver,
			new DirectoryExtractor( $parser ),
			new SymbolDiffer(),
			new HookDiffer(),
			new ReferenceScanner( $parser ),
			new ScanRenderer(),
			$fetcher
		);

		$application = new Application();
		$application->add( $command );

		return new CommandTester( $application->find( 'breaking-changes:scan' ) );
	}

	public function test_check_against_comma_separated_local_paths(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'--dependency'    => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'           => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'           => $this->fixtures_dir . '/sample-plugin-v2',
			'--check-against' => $this->fixtures_dir . '/target-plugin,' . $this->fixtures_dir . '/sample-plugin-v2',
		] );

		$output = $tester->getDisplay();
		// target-plugin has breaking references, sample-plugin-v2 does not.
		$this->assertStringContainsString( 'target-plugin', $output );
	}

	public function test_check_against_no_target_needed(): void {
		$tester = $this->make_command_tester();

		// Should work without 'target' argument when --check-against is provided.
		$exit_code = $tester->execute( [
			'--dependency'    => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'           => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'           => $this->fixtures_dir . '/sample-plugin-v2',
			'--check-against' => $this->fixtures_dir . '/target-plugin',
		] );

		// Should find references in target-plugin.
		$this->assertEquals( 1, $exit_code );
	}

	public function test_check_against_json_format(): void {
		$tester = $this->make_command_tester();

		$tester->execute( [
			'--dependency'    => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'           => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'           => $this->fixtures_dir . '/sample-plugin-v2',
			'--check-against' => $this->fixtures_dir . '/target-plugin,' . $this->fixtures_dir . '/sample-plugin-v2',
			'--format'        => 'json',
		] );

		$data = json_decode( $tester->getDisplay(), true );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'plugins', $data );
		$this->assertArrayHasKey( 'summary', $data );
		$this->assertCount( 2, $data['plugins'] );
	}

	public function test_check_against_woo_developed_requires_fetcher(): void {
		$tester = $this->make_command_tester( null );

		$exit_code = $tester->execute( [
			'--dependency'    => $this->fixtures_dir . '/sample-plugin-v1',
			'--old'           => $this->fixtures_dir . '/sample-plugin-v1',
			'--new'           => $this->fixtures_dir . '/sample-plugin-v2',
			'--check-against' => 'woo-developed',
		] );

		$this->assertEquals( 1, $exit_code );
		$this->assertStringContainsString( 'not connected to QIT backend', $tester->getDisplay() );
	}

	public function test_requires_target_or_check_against(): void {
		$tester = $this->make_command_tester();

		$exit_code = $tester->execute( [
			'--dependency' => 'woocommerce',
			'--old'        => '9.4.0',
		] );

		$this->assertEquals( 1, $exit_code );
		$this->assertStringContainsString( 'Either a target argument or --check-against', $tester->getDisplay() );
	}
}
