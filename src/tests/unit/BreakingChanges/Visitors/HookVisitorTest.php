<?php

namespace QIT_CLI_Tests\BreakingChanges\Visitors;

use PHPUnit\Framework\TestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Visitors\HookVisitor;

class HookVisitorTest extends TestCase {
	private function extract_from_code( string $code ): ExtractedSymbols {
		$parser  = ( new ParserFactory() )->createForHostVersion();
		$ast     = $parser->parse( $code );
		$symbols = new ExtractedSymbols();
		$visitor = new HookVisitor( $symbols, 'test.php' );

		$traverser = new NodeTraverser();
		$traverser->addVisitor( new NameResolver() );
		$traverser->addVisitor( $visitor );
		$traverser->traverse( $ast );

		return $symbols;
	}

	public function test_extracts_do_action(): void {
		$code    = "<?php do_action( 'my_hook' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'my_hook', $symbols->hooks );
		$this->assertEquals( 'action', $symbols->hooks['my_hook']->type );
		$this->assertFalse( $symbols->hooks['my_hook']->is_dynamic );
	}

	public function test_extracts_apply_filters(): void {
		$code    = "<?php apply_filters( 'my_filter', \$value );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'my_filter', $symbols->hooks );
		$this->assertEquals( 'filter', $symbols->hooks['my_filter']->type );
	}

	public function test_extracts_do_action_ref_array(): void {
		$code    = "<?php do_action_ref_array( 'ref_hook', \$args );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'ref_hook', $symbols->hooks );
		$this->assertEquals( 'action', $symbols->hooks['ref_hook']->type );
	}

	public function test_extracts_apply_filters_ref_array(): void {
		$code    = "<?php apply_filters_ref_array( 'ref_filter', \$args );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'ref_filter', $symbols->hooks );
		$this->assertEquals( 'filter', $symbols->hooks['ref_filter']->type );
	}

	public function test_extracts_deprecated_hooks(): void {
		$code    = "<?php do_action_deprecated( 'old_hook', array(), '2.0' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'old_hook', $symbols->hooks );
		$this->assertEquals( 'action', $symbols->hooks['old_hook']->type );
	}

	public function test_counts_dynamic_hooks(): void {
		$code    = '<?php do_action( "prefix_{$type}_hook" );';
		$symbols = $this->extract_from_code( $code );

		$this->assertEmpty( $symbols->hooks );
		$this->assertEquals( 1, $symbols->dynamic_hook_count );
	}

	public function test_counts_arg_count(): void {
		$code    = "<?php do_action( 'my_hook', \$a, \$b, \$c );";
		$symbols = $this->extract_from_code( $code );

		$this->assertEquals( 3, $symbols->hooks['my_hook']->arg_count );
	}

	public function test_ignores_non_hook_functions(): void {
		$code    = "<?php some_function( 'not_a_hook' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertEmpty( $symbols->hooks );
		$this->assertEquals( 0, $symbols->dynamic_hook_count );
	}

	public function test_extracts_multiple_hooks(): void {
		$code = "<?php
		do_action( 'hook_one' );
		apply_filters( 'filter_one', \$val );
		do_action( 'hook_two', \$arg );";

		$symbols = $this->extract_from_code( $code );

		$this->assertCount( 3, $symbols->hooks );
		$this->assertArrayHasKey( 'hook_one', $symbols->hooks );
		$this->assertArrayHasKey( 'filter_one', $symbols->hooks );
		$this->assertArrayHasKey( 'hook_two', $symbols->hooks );
	}

	public function test_file_is_set_on_hooks(): void {
		$code    = "<?php do_action( 'my_hook' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertEquals( 'test.php', $symbols->hooks['my_hook']->file );
	}

	public function test_dynamic_concat_hook(): void {
		$code    = "<?php do_action( 'prefix_' . \$type . '_hook' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertEmpty( $symbols->hooks );
		$this->assertEquals( 1, $symbols->dynamic_hook_count );
	}

	public function test_apply_filters_deprecated(): void {
		$code    = "<?php apply_filters_deprecated( 'old_filter', array( \$val ), '3.0' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'old_filter', $symbols->hooks );
		$this->assertEquals( 'filter', $symbols->hooks['old_filter']->type );
	}
}
