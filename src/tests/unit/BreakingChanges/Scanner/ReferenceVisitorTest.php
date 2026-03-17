<?php

namespace QIT_CLI_Tests\BreakingChanges\Scanner;

use PHPUnit\Framework\TestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use QIT_CLI\BreakingChanges\Models\HookDiffResult;
use QIT_CLI\BreakingChanges\Models\HookInfo;
use QIT_CLI\BreakingChanges\Models\SymbolDiffResult;
use QIT_CLI\BreakingChanges\Models\SymbolInfo;
use QIT_CLI\BreakingChanges\Scanner\ReferenceVisitor;

class ReferenceVisitorTest extends TestCase {
	private function scan_code( string $code, SymbolDiffResult $symbol_diff, HookDiffResult $hook_diff ): array {
		$parser  = ( new ParserFactory() )->createForHostVersion();
		$ast     = $parser->parse( $code );
		$visitor = new ReferenceVisitor( $symbol_diff, $hook_diff, 'test.php' );

		$traverser = new NodeTraverser();
		$traverser->addVisitor( new NameResolver() );
		$traverser->addVisitor( $visitor );
		$traverser->traverse( $ast );

		return $visitor->get_references();
	}

	public function test_detects_removed_class_instantiation(): void {
		$symbol_diff = new SymbolDiffResult(
			[ new SymbolInfo( 'App\OldClass', 'class', 'old.php', 1 ) ]
		);

		$refs = $this->scan_code(
			'<?php use App\OldClass; $obj = new OldClass();',
			$symbol_diff,
			new HookDiffResult()
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'App\OldClass', $refs[0]->name );
		$this->assertEquals( 'class_usage', $refs[0]->type );
	}

	public function test_detects_removed_static_call(): void {
		$symbol_diff = new SymbolDiffResult(
			[ new SymbolInfo( 'doStuff', 'method', 'cls.php', 5, 'public', 'App\Helper' ) ]
		);

		$refs = $this->scan_code(
			'<?php use App\Helper; Helper::doStuff();',
			$symbol_diff,
			new HookDiffResult()
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'App\Helper::doStuff', $refs[0]->name );
		$this->assertEquals( 'static_call', $refs[0]->type );
	}

	public function test_detects_removed_function_call(): void {
		$symbol_diff = new SymbolDiffResult(
			[ new SymbolInfo( 'old_function', 'function', 'funcs.php', 1 ) ]
		);

		$refs = $this->scan_code(
			'<?php old_function();',
			$symbol_diff,
			new HookDiffResult()
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'old_function', $refs[0]->name );
		$this->assertEquals( 'function_call', $refs[0]->type );
	}

	public function test_detects_removed_constant(): void {
		$symbol_diff = new SymbolDiffResult(
			[ new SymbolInfo( 'OLD_CONST', 'constant', 'const.php', 1 ) ]
		);

		$refs = $this->scan_code(
			'<?php $val = OLD_CONST;',
			$symbol_diff,
			new HookDiffResult()
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'OLD_CONST', $refs[0]->name );
		$this->assertEquals( 'constant_access', $refs[0]->type );
	}

	public function test_detects_removed_hook_registration(): void {
		$hook_diff = new HookDiffResult(
			[ new HookInfo( 'old_hook', 'action', 'hooks.php', 10 ) ]
		);

		$refs = $this->scan_code(
			"<?php add_action( 'old_hook', 'my_callback' );",
			new SymbolDiffResult(),
			$hook_diff
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'old_hook', $refs[0]->name );
		$this->assertEquals( 'hook_registration', $refs[0]->type );
	}

	public function test_detects_remove_filter_reference(): void {
		$hook_diff = new HookDiffResult(
			[ new HookInfo( 'old_filter', 'filter', 'hooks.php', 10 ) ]
		);

		$refs = $this->scan_code(
			"<?php remove_filter( 'old_filter', 'my_callback' );",
			new SymbolDiffResult(),
			$hook_diff
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'old_filter', $refs[0]->name );
	}

	public function test_ignores_existing_symbols(): void {
		$symbol_diff = new SymbolDiffResult(
			[ new SymbolInfo( 'App\Removed', 'class', 'old.php', 1 ) ]
		);

		// ExistingClass is NOT in the removed list.
		$refs = $this->scan_code(
			'<?php $obj = new ExistingClass();',
			$symbol_diff,
			new HookDiffResult()
		);

		$this->assertEmpty( $refs );
	}

	public function test_ignores_existing_hooks(): void {
		$hook_diff = new HookDiffResult(
			[ new HookInfo( 'removed_hook', 'action', 'hooks.php', 10 ) ]
		);

		// existing_hook is NOT in the removed list.
		$refs = $this->scan_code(
			"<?php add_action( 'existing_hook', 'my_callback' );",
			new SymbolDiffResult(),
			$hook_diff
		);

		$this->assertEmpty( $refs );
	}

	public function test_detects_class_extends_removed(): void {
		$symbol_diff = new SymbolDiffResult(
			[ new SymbolInfo( 'App\BaseClass', 'class', 'base.php', 1 ) ]
		);

		$refs = $this->scan_code(
			'<?php use App\BaseClass; class Child extends BaseClass {}',
			$symbol_diff,
			new HookDiffResult()
		);

		$this->assertGreaterThanOrEqual( 1, count( $refs ) );
		$names = array_column( $refs, 'name' );
		$this->assertContains( 'App\BaseClass', $names );
	}

	public function test_has_filter_reference(): void {
		$hook_diff = new HookDiffResult(
			[ new HookInfo( 'old_filter', 'filter', 'hooks.php', 10 ) ]
		);

		$refs = $this->scan_code(
			"<?php if ( has_filter( 'old_filter' ) ) {}",
			new SymbolDiffResult(),
			$hook_diff
		);

		$this->assertCount( 1, $refs );
		$this->assertEquals( 'old_filter', $refs[0]->name );
	}
}
