<?php

namespace QIT_CLI_Tests\BreakingChanges\Scanner;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Diff\HookDiffer;
use QIT_CLI\BreakingChanges\Diff\SymbolDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Extraction\FileParser;
use QIT_CLI\BreakingChanges\Scanner\ReferenceScanner;

class ReferenceScannerTest extends TestCase {
	private ReferenceScanner $scanner;
	private DirectoryExtractor $extractor;
	private string $fixtures_dir;

	protected function setUp(): void {
		parent::setUp();
		$parser          = new FileParser();
		$this->scanner   = new ReferenceScanner( $parser );
		$this->extractor = new DirectoryExtractor( $parser );
		$this->fixtures_dir = dirname( __DIR__ ) . '/fixtures';
	}

	public function test_finds_references_to_removed_symbols(): void {
		$old = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v1' );
		$new = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v2' );

		$symbol_diff = ( new SymbolDiffer() )->diff( $old, $new );
		$hook_diff   = ( new HookDiffer() )->diff( $old, $new );

		$result = $this->scanner->scan(
			$this->fixtures_dir . '/target-plugin',
			$symbol_diff,
			$hook_diff,
			'target-plugin'
		);

		$this->assertTrue( $result->has_breaking_references() );
		$this->assertEquals( 'target-plugin', $result->plugin_slug );

		$names = array_map( function ( $ref ) {
			return $ref->name;
		}, $result->references );

		// Should find references to:
		// - SAMPLE_PLUGIN_DIR (removed constant)
		// - sample_plugin_init (removed hook)
		// - sample_plugin_before_process (removed hook)
		// - sample_plugin_deprecated_function (removed function - namespaced)
		$this->assertContains( 'SAMPLE_PLUGIN_DIR', $names );
		$this->assertContains( 'sample_plugin_init', $names );
		$this->assertContains( 'sample_plugin_before_process', $names );

		// Should NOT find reference to sample_plugin_items (still exists in v2).
		$this->assertNotContains( 'sample_plugin_items', $names );
	}

	public function test_returns_empty_when_no_removals(): void {
		$old = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v1' );

		$symbol_diff = ( new SymbolDiffer() )->diff( $old, $old );
		$hook_diff   = ( new HookDiffer() )->diff( $old, $old );

		$result = $this->scanner->scan(
			$this->fixtures_dir . '/target-plugin',
			$symbol_diff,
			$hook_diff
		);

		$this->assertFalse( $result->has_breaking_references() );
		$this->assertEmpty( $result->references );
	}

	public function test_handles_nonexistent_directory(): void {
		$old = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v1' );
		$new = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v2' );

		$symbol_diff = ( new SymbolDiffer() )->diff( $old, $new );
		$hook_diff   = ( new HookDiffer() )->diff( $old, $new );

		$result = $this->scanner->scan(
			'/nonexistent/directory',
			$symbol_diff,
			$hook_diff
		);

		$this->assertFalse( $result->has_breaking_references() );
	}

	public function test_no_warnings_on_valid_fixtures(): void {
		$old = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v1' );
		$new = $this->extractor->extract( $this->fixtures_dir . '/sample-plugin-v2' );

		$symbol_diff = ( new SymbolDiffer() )->diff( $old, $new );
		$hook_diff   = ( new HookDiffer() )->diff( $old, $new );

		$result = $this->scanner->scan(
			$this->fixtures_dir . '/target-plugin',
			$symbol_diff,
			$hook_diff
		);

		$this->assertEmpty( $result->warnings );
	}
}
