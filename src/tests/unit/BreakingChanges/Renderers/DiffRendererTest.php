<?php

namespace QIT_CLI_Tests\BreakingChanges\Renderers;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Models\DiffResult;
use QIT_CLI\BreakingChanges\Models\HookDiffResult;
use QIT_CLI\BreakingChanges\Models\HookInfo;
use QIT_CLI\BreakingChanges\Models\SymbolDiffResult;
use QIT_CLI\BreakingChanges\Models\SymbolInfo;
use QIT_CLI\BreakingChanges\Renderers\DiffRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class DiffRendererTest extends TestCase {
	private DiffRenderer $renderer;

	protected function setUp(): void {
		parent::setUp();
		$this->renderer = new DiffRenderer();
	}

	private function make_sample_result(): DiffResult {
		$symbols = new SymbolDiffResult(
			[ new SymbolInfo( 'OldClass', 'class', 'old.php', 10 ) ],
			[ new SymbolInfo( 'NewClass', 'class', 'new.php', 5 ) ]
		);

		$hooks = new HookDiffResult(
			[ new HookInfo( 'old_hook', 'action', 'hooks.php', 20 ) ],
			[ new HookInfo( 'new_hook', 'filter', 'hooks.php', 30 ) ]
		);

		return new DiffResult( $symbols, $hooks );
	}

	public function test_render_table_format(): void {
		$output = new BufferedOutput();
		$result = $this->make_sample_result();

		$this->renderer->render( $result, $output, 'table' );

		$text = $output->fetch();
		$this->assertStringContainsString( 'OldClass', $text );
		$this->assertStringContainsString( 'NewClass', $text );
		$this->assertStringContainsString( 'old_hook', $text );
		$this->assertStringContainsString( 'new_hook', $text );
		$this->assertStringContainsString( 'Breaking changes detected', $text );
	}

	public function test_render_json_format(): void {
		$output = new BufferedOutput();
		$result = $this->make_sample_result();

		$this->renderer->render( $result, $output, 'json' );

		$text = $output->fetch();
		$data = json_decode( $text, true );

		$this->assertIsArray( $data );
		$this->assertCount( 1, $data['symbols']['removed'] );
		$this->assertCount( 1, $data['symbols']['added'] );
		$this->assertCount( 1, $data['hooks']['removed'] );
		$this->assertCount( 1, $data['hooks']['added'] );
		$this->assertTrue( $data['summary']['has_breaking_changes'] );
		$this->assertEquals( 'OldClass', $data['symbols']['removed'][0]['name'] );
	}

	public function test_render_github_format(): void {
		$output = new BufferedOutput();
		$result = $this->make_sample_result();

		$this->renderer->render( $result, $output, 'github' );

		$text = $output->fetch();
		$this->assertStringContainsString( '::error file=old.php,line=10::Removed class: OldClass', $text );
		$this->assertStringContainsString( '::error file=hooks.php,line=20::Removed action hook: old_hook', $text );
		$this->assertStringContainsString( '::notice file=new.php,line=5::Added class: NewClass', $text );
		$this->assertStringContainsString( '::notice file=hooks.php,line=30::Added filter hook: new_hook', $text );
	}

	public function test_render_no_changes(): void {
		$output = new BufferedOutput();
		$result = new DiffResult(
			new SymbolDiffResult(),
			new HookDiffResult()
		);

		$this->renderer->render( $result, $output, 'table' );

		$text = $output->fetch();
		$this->assertStringContainsString( 'No symbol changes detected', $text );
		$this->assertStringContainsString( 'No hook changes detected', $text );
		$this->assertStringContainsString( 'No breaking changes detected', $text );
	}

	public function test_render_json_no_changes(): void {
		$output = new BufferedOutput();
		$result = new DiffResult(
			new SymbolDiffResult(),
			new HookDiffResult()
		);

		$this->renderer->render( $result, $output, 'json' );

		$data = json_decode( $output->fetch(), true );

		$this->assertFalse( $data['summary']['has_breaking_changes'] );
		$this->assertEquals( 0, $data['summary']['removed_symbols'] );
	}

	public function test_render_method_with_parent_class(): void {
		$output  = new BufferedOutput();
		$symbols = new SymbolDiffResult(
			[ new SymbolInfo( 'doStuff', 'method', 'class.php', 15, 'public', 'App\MyClass' ) ],
			[]
		);

		$result = new DiffResult( $symbols, new HookDiffResult() );
		$this->renderer->render( $result, $output, 'json' );

		$data = json_decode( $output->fetch(), true );
		$this->assertEquals( 'App\MyClass::doStuff', $data['symbols']['removed'][0]['name'] );
	}
}
