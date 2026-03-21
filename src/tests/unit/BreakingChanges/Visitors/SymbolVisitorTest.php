<?php

namespace QIT_CLI_Tests\BreakingChanges\Visitors;

use PHPUnit\Framework\TestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Visitors\SymbolVisitor;

class SymbolVisitorTest extends TestCase {
	private function extract_from_code( string $code ): ExtractedSymbols {
		$parser  = ( new ParserFactory() )->createForHostVersion();
		$ast     = $parser->parse( $code );
		$symbols = new ExtractedSymbols();
		$visitor = new SymbolVisitor( $symbols, 'test.php' );

		$traverser = new NodeTraverser();
		$traverser->addVisitor( new NameResolver() );
		$traverser->addVisitor( $visitor );
		$traverser->traverse( $ast );

		return $symbols;
	}

	public function test_extracts_class(): void {
		$code    = '<?php namespace Foo; class Bar {}';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\Bar', $symbols->classes );
		$this->assertEquals( 'class', $symbols->classes['Foo\Bar']->type );
	}

	public function test_extracts_interface(): void {
		$code    = '<?php namespace Foo; interface Baz { public function run(): void; }';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\Baz', $symbols->classes );
		$this->assertArrayHasKey( 'Foo\Baz::run', $symbols->methods );
	}

	public function test_extracts_trait(): void {
		$code    = '<?php namespace Foo; trait MyTrait { public function helper(): void {} }';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\MyTrait', $symbols->classes );
		$this->assertArrayHasKey( 'Foo\MyTrait::helper', $symbols->methods );
	}

	public function test_extracts_public_methods_only(): void {
		$code = '<?php
		namespace Foo;
		class Bar {
			public function pub(): void {}
			protected function prot(): void {}
			private function priv(): void {}
		}';

		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\Bar::pub', $symbols->methods );
		$this->assertArrayNotHasKey( 'Foo\Bar::prot', $symbols->methods );
		$this->assertArrayNotHasKey( 'Foo\Bar::priv', $symbols->methods );
	}

	public function test_extracts_function(): void {
		$code    = '<?php namespace Foo; function my_func(): string { return "hi"; }';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\my_func', $symbols->functions );
		$this->assertEquals( 'function', $symbols->functions['Foo\my_func']->type );
	}

	public function test_extracts_global_function(): void {
		$code    = '<?php function global_func() {}';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'global_func', $symbols->functions );
	}

	public function test_extracts_const_statement(): void {
		$code    = '<?php namespace Foo; const MY_CONST = 42;';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\MY_CONST', $symbols->constants );
		$this->assertEquals( 'constant', $symbols->constants['Foo\MY_CONST']->type );
	}

	public function test_extracts_define_call(): void {
		$code    = "<?php define( 'MY_PLUGIN_VERSION', '1.0' );";
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'MY_PLUGIN_VERSION', $symbols->constants );
	}

	public function test_ignores_define_with_non_string_name(): void {
		$code    = '<?php define( $dynamic_name, "value" );';
		$symbols = $this->extract_from_code( $code );

		$this->assertEmpty( $symbols->constants );
	}

	public function test_ignores_anonymous_class(): void {
		$code    = '<?php $obj = new class { public function foo() {} };';
		$symbols = $this->extract_from_code( $code );

		$this->assertEmpty( $symbols->classes );
	}

	public function test_extracts_enum(): void {
		$code    = '<?php namespace Foo; enum Status { case Active; case Inactive; }';
		$symbols = $this->extract_from_code( $code );

		$this->assertArrayHasKey( 'Foo\Status', $symbols->classes );
	}

	public function test_method_has_parent_class(): void {
		$code = '<?php namespace Foo; class Bar { public function baz(): void {} }';

		$symbols = $this->extract_from_code( $code );

		$method = $symbols->methods['Foo\Bar::baz'];
		$this->assertEquals( 'Foo\Bar', $method->parent_class );
		$this->assertEquals( 'baz', $method->name );
	}

	public function test_file_is_set_on_symbols(): void {
		$code    = '<?php class Foo {}';
		$symbols = $this->extract_from_code( $code );

		$this->assertEquals( 'test.php', $symbols->classes['Foo']->file );
	}

	public function test_multiple_classes_in_one_file(): void {
		$code = '<?php
		namespace App;
		class First { public function a(): void {} }
		class Second { public function b(): void {} }';

		$symbols = $this->extract_from_code( $code );

		$this->assertCount( 2, $symbols->classes );
		$this->assertArrayHasKey( 'App\First', $symbols->classes );
		$this->assertArrayHasKey( 'App\Second', $symbols->classes );
		$this->assertArrayHasKey( 'App\First::a', $symbols->methods );
		$this->assertArrayHasKey( 'App\Second::b', $symbols->methods );
	}
}
