<?php

namespace QIT_CLI_Tests\BreakingChanges\Diff;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Diff\SymbolDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Extraction\FileParser;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\SymbolInfo;

class SymbolDifferTest extends TestCase {
	private SymbolDiffer $differ;

	protected function setUp(): void {
		parent::setUp();
		$this->differ = new SymbolDiffer();
	}

	public function test_detects_removed_class(): void {
		$old = new ExtractedSymbols();
		$old->add_class( new SymbolInfo( 'Foo\Bar', 'class', 'bar.php', 1 ) );
		$old->add_class( new SymbolInfo( 'Foo\Baz', 'class', 'baz.php', 1 ) );

		$new = new ExtractedSymbols();
		$new->add_class( new SymbolInfo( 'Foo\Bar', 'class', 'bar.php', 1 ) );

		$result = $this->differ->diff( $old, $new );

		$this->assertTrue( $result->has_removals() );
		$this->assertCount( 1, $result->removed );
		$this->assertEquals( 'Foo\Baz', $result->removed[0]->name );
	}

	public function test_detects_added_class(): void {
		$old = new ExtractedSymbols();
		$old->add_class( new SymbolInfo( 'Foo\Bar', 'class', 'bar.php', 1 ) );

		$new = new ExtractedSymbols();
		$new->add_class( new SymbolInfo( 'Foo\Bar', 'class', 'bar.php', 1 ) );
		$new->add_class( new SymbolInfo( 'Foo\Baz', 'class', 'baz.php', 1 ) );

		$result = $this->differ->diff( $old, $new );

		$this->assertFalse( $result->has_removals() );
		$this->assertCount( 1, $result->added );
		$this->assertEquals( 'Foo\Baz', $result->added[0]->name );
	}

	public function test_detects_removed_method(): void {
		$old = new ExtractedSymbols();
		$old->add_method( new SymbolInfo( 'foo', 'method', 'bar.php', 5, 'public', 'Foo\Bar' ) );
		$old->add_method( new SymbolInfo( 'baz', 'method', 'bar.php', 10, 'public', 'Foo\Bar' ) );

		$new = new ExtractedSymbols();
		$new->add_method( new SymbolInfo( 'foo', 'method', 'bar.php', 5, 'public', 'Foo\Bar' ) );

		$result = $this->differ->diff( $old, $new );

		$this->assertTrue( $result->has_removals() );
		$this->assertCount( 1, $result->removed );
		$this->assertEquals( 'baz', $result->removed[0]->name );
		$this->assertEquals( 'Foo\Bar', $result->removed[0]->parent_class );
	}

	public function test_detects_removed_function(): void {
		$old = new ExtractedSymbols();
		$old->add_function( new SymbolInfo( 'old_func', 'function', 'funcs.php', 1 ) );

		$new = new ExtractedSymbols();

		$result = $this->differ->diff( $old, $new );

		$this->assertTrue( $result->has_removals() );
		$this->assertCount( 1, $result->removed );
		$this->assertEquals( 'old_func', $result->removed[0]->name );
	}

	public function test_detects_removed_constant(): void {
		$old = new ExtractedSymbols();
		$old->add_constant( new SymbolInfo( 'MY_CONST', 'constant', 'file.php', 1 ) );

		$new = new ExtractedSymbols();

		$result = $this->differ->diff( $old, $new );

		$this->assertTrue( $result->has_removals() );
		$this->assertCount( 1, $result->removed );
		$this->assertEquals( 'MY_CONST', $result->removed[0]->name );
	}

	public function test_no_changes(): void {
		$old = new ExtractedSymbols();
		$old->add_class( new SymbolInfo( 'Foo\Bar', 'class', 'bar.php', 1 ) );
		$old->add_function( new SymbolInfo( 'my_func', 'function', 'funcs.php', 1 ) );

		$new = new ExtractedSymbols();
		$new->add_class( new SymbolInfo( 'Foo\Bar', 'class', 'bar.php', 1 ) );
		$new->add_function( new SymbolInfo( 'my_func', 'function', 'funcs.php', 1 ) );

		$result = $this->differ->diff( $old, $new );

		$this->assertFalse( $result->has_removals() );
		$this->assertEmpty( $result->removed );
		$this->assertEmpty( $result->added );
	}

	public function test_diff_with_fixture_plugins(): void {
		$extractor = new DirectoryExtractor( new FileParser() );

		$old = $extractor->extract( __DIR__ . '/../fixtures/sample-plugin-v1' );
		$new = $extractor->extract( __DIR__ . '/../fixtures/sample-plugin-v2' );

		$result = $this->differ->diff( $old, $new );

		// Removed symbols: SampleContract (interface), process_item (method),
		// deprecated_method (method), sample_plugin_deprecated_function (function),
		// SAMPLE_PLUGIN_DIR (constant),
		// SampleContract::execute (method), SampleContract::get_name (method).
		$this->assertTrue( $result->has_removals() );

		$removed_names = array_map(
			function ( SymbolInfo $s ) {
				return $s->get_key();
			},
			$result->removed
		);

		$this->assertContains( 'SamplePlugin\SampleContract', $removed_names );
		$this->assertContains( 'SamplePlugin\SampleManager::process_item', $removed_names );
		$this->assertContains( 'SamplePlugin\SampleHelper::deprecated_method', $removed_names );
		$this->assertContains( 'SamplePlugin\sample_plugin_deprecated_function', $removed_names );
		$this->assertContains( 'SAMPLE_PLUGIN_DIR', $removed_names );

		// Added symbols should include new classes/methods/functions/constants.
		$added_names = array_map(
			function ( SymbolInfo $s ) {
				return $s->get_key();
			},
			$result->added
		);

		$this->assertContains( 'SamplePlugin\SampleRegistry', $added_names );
		$this->assertContains( 'SamplePlugin\SampleHelper::sanitize_output', $added_names );
		$this->assertContains( 'SamplePlugin\sample_plugin_new_utility', $added_names );
		$this->assertContains( 'SAMPLE_PLUGIN_MIN_PHP', $added_names );
	}

	public function test_empty_inputs(): void {
		$old = new ExtractedSymbols();
		$new = new ExtractedSymbols();

		$result = $this->differ->diff( $old, $new );

		$this->assertFalse( $result->has_removals() );
		$this->assertEmpty( $result->removed );
		$this->assertEmpty( $result->added );
	}
}
