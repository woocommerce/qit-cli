<?php

namespace QIT_CLI_Tests\BreakingChanges\Diff;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Diff\HookDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Extraction\FileParser;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\HookInfo;

class HookDifferTest extends TestCase {
	private HookDiffer $differ;

	protected function setUp(): void {
		parent::setUp();
		$this->differ = new HookDiffer();
	}

	public function test_detects_removed_hook(): void {
		$old = new ExtractedSymbols();
		$old->add_hook( new HookInfo( 'old_hook', 'action', 'file.php', 10 ) );
		$old->add_hook( new HookInfo( 'kept_hook', 'filter', 'file.php', 20 ) );

		$new = new ExtractedSymbols();
		$new->add_hook( new HookInfo( 'kept_hook', 'filter', 'file.php', 20 ) );

		$result = $this->differ->diff( $old, $new );

		$this->assertTrue( $result->has_removals() );
		$this->assertCount( 1, $result->removed );
		$this->assertEquals( 'old_hook', $result->removed[0]->name );
	}

	public function test_detects_added_hook(): void {
		$old = new ExtractedSymbols();
		$old->add_hook( new HookInfo( 'existing_hook', 'action', 'file.php', 10 ) );

		$new = new ExtractedSymbols();
		$new->add_hook( new HookInfo( 'existing_hook', 'action', 'file.php', 10 ) );
		$new->add_hook( new HookInfo( 'new_hook', 'filter', 'file.php', 20 ) );

		$result = $this->differ->diff( $old, $new );

		$this->assertFalse( $result->has_removals() );
		$this->assertCount( 1, $result->added );
		$this->assertEquals( 'new_hook', $result->added[0]->name );
	}

	public function test_skips_dynamic_hooks(): void {
		$old = new ExtractedSymbols();
		$old->add_hook( new HookInfo( 'dynamic_hook', 'action', 'file.php', 10, true ) );

		$new = new ExtractedSymbols();

		$result = $this->differ->diff( $old, $new );

		// Dynamic hook should be skipped, so no removals detected.
		$this->assertFalse( $result->has_removals() );
		$this->assertEmpty( $result->removed );
	}

	public function test_no_changes(): void {
		$old = new ExtractedSymbols();
		$old->add_hook( new HookInfo( 'hook_a', 'action', 'file.php', 10 ) );
		$old->add_hook( new HookInfo( 'hook_b', 'filter', 'file.php', 20 ) );

		$new = new ExtractedSymbols();
		$new->add_hook( new HookInfo( 'hook_a', 'action', 'file.php', 10 ) );
		$new->add_hook( new HookInfo( 'hook_b', 'filter', 'file.php', 20 ) );

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

		$this->assertTrue( $result->has_removals() );

		$removed_names = array_map(
			function ( HookInfo $h ) {
				return $h->name;
			},
			$result->removed
		);

		// Removed hooks: sample_plugin_init, sample_plugin_before_process, sample_plugin_after_process.
		$this->assertContains( 'sample_plugin_init', $removed_names );
		$this->assertContains( 'sample_plugin_before_process', $removed_names );
		$this->assertContains( 'sample_plugin_after_process', $removed_names );

		$added_names = array_map(
			function ( HookInfo $h ) {
				return $h->name;
			},
			$result->added
		);

		// Added hooks: sample_plugin_initialized, sample_plugin_before_batch, sample_plugin_after_batch,
		// sample_plugin_sanitize_output, sample_plugin_registered, sample_plugin_registry_get, sample_plugin_utility_result.
		$this->assertContains( 'sample_plugin_initialized', $added_names );
		$this->assertContains( 'sample_plugin_before_batch', $added_names );
		$this->assertContains( 'sample_plugin_after_batch', $added_names );
		$this->assertContains( 'sample_plugin_sanitize_output', $added_names );
		$this->assertContains( 'sample_plugin_registered', $added_names );
		$this->assertContains( 'sample_plugin_registry_get', $added_names );
		$this->assertContains( 'sample_plugin_utility_result', $added_names );
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
