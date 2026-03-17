<?php

namespace QIT_CLI\BreakingChanges\Visitors;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\SymbolInfo;

class SymbolVisitor extends NodeVisitorAbstract {
	private ExtractedSymbols $symbols;
	private string $file;

	/** @var string|null Current class/interface/trait context */
	private ?string $current_class = null;

	public function __construct( ExtractedSymbols $symbols, string $file ) {
		$this->symbols = $symbols;
		$this->file    = $file;
	}

	public function get_symbols(): ExtractedSymbols {
		return $this->symbols;
	}

	/**
	 * @return int|null
	 */
	public function enterNode( Node $node ) {
		if ( $node instanceof Stmt\Class_ ) {
			$this->visit_class( $node );
		} elseif ( $node instanceof Stmt\Interface_ ) {
			$this->visit_interface( $node );
		} elseif ( $node instanceof Stmt\Trait_ ) {
			$this->visit_trait( $node );
		} elseif ( $node instanceof Stmt\Enum_ ) {
			$this->visit_enum( $node );
		} elseif ( $node instanceof Stmt\ClassMethod ) {
			$this->visit_method( $node );
		} elseif ( $node instanceof Stmt\Function_ ) {
			$this->visit_function( $node );
		} elseif ( $node instanceof Stmt\Const_ ) {
			$this->visit_const_statement( $node );
		} elseif ( $node instanceof FuncCall ) {
			$this->visit_define_call( $node );
		}

		return null;
	}

	/**
	 * @return int|Node|null
	 */
	public function leaveNode( Node $node ) {
		if (
			$node instanceof Stmt\Class_
			|| $node instanceof Stmt\Interface_
			|| $node instanceof Stmt\Trait_
			|| $node instanceof Stmt\Enum_
		) {
			$this->current_class = null;
		}

		return null;
	}

	private function visit_class( Stmt\Class_ $node ): void {
		if ( $node->name === null ) {
			return; // Anonymous class.
		}

		$fqn = $this->get_fqn( $node );

		$this->current_class = $fqn;

		$this->symbols->add_class( new SymbolInfo(
			$fqn,
			'class',
			$this->file,
			$node->getStartLine(),
			'public'
		) );
	}

	private function visit_interface( Stmt\Interface_ $node ): void {
		$fqn = $this->get_fqn( $node );

		$this->current_class = $fqn;

		$this->symbols->add_class( new SymbolInfo(
			$fqn,
			'class',
			$this->file,
			$node->getStartLine(),
			'public'
		) );
	}

	private function visit_trait( Stmt\Trait_ $node ): void {
		$fqn = $this->get_fqn( $node );

		$this->current_class = $fqn;

		$this->symbols->add_class( new SymbolInfo(
			$fqn,
			'class',
			$this->file,
			$node->getStartLine(),
			'public'
		) );
	}

	private function visit_enum( Stmt\Enum_ $node ): void {
		$fqn = $this->get_fqn( $node );

		$this->current_class = $fqn;

		$this->symbols->add_class( new SymbolInfo(
			$fqn,
			'class',
			$this->file,
			$node->getStartLine(),
			'public'
		) );
	}

	private function visit_method( Stmt\ClassMethod $node ): void {
		if ( $this->current_class === null ) {
			return;
		}

		// Only collect public methods.
		if ( ! $node->isPublic() ) {
			return;
		}

		$method_name = $node->name->toString();

		$this->symbols->add_method( new SymbolInfo(
			$method_name,
			'method',
			$this->file,
			$node->getStartLine(),
			'public',
			$this->current_class
		) );
	}

	private function visit_function( Stmt\Function_ $node ): void {
		$fqn = $this->get_fqn( $node );

		$this->symbols->add_function( new SymbolInfo(
			$fqn,
			'function',
			$this->file,
			$node->getStartLine(),
			'public'
		) );
	}

	private function visit_const_statement( Stmt\Const_ $node ): void {
		foreach ( $node->consts as $const ) {
			$fqn = $this->get_fqn_from_const( $const );

			$this->symbols->add_constant( new SymbolInfo(
				$fqn,
				'constant',
				$this->file,
				$const->getStartLine(),
				'public'
			) );
		}
	}

	private function visit_define_call( FuncCall $node ): void {
		if ( ! $node->name instanceof Node\Name ) {
			return;
		}

		$func_name = $node->name->toLowerString();
		if ( $func_name !== 'define' ) {
			return;
		}

		if ( count( $node->args ) < 2 ) {
			return;
		}

		$first_arg = $node->args[0];
		if ( ! $first_arg instanceof Node\Arg ) {
			return;
		}

		if ( ! $first_arg->value instanceof String_ ) {
			return;
		}

		$const_name = $first_arg->value->value;

		$this->symbols->add_constant( new SymbolInfo(
			$const_name,
			'constant',
			$this->file,
			$node->getStartLine(),
			'public'
		) );
	}

	/**
	 * Get fully qualified name from a named node.
	 *
	 * @param Stmt\Class_|Stmt\Interface_|Stmt\Trait_|Stmt\Enum_|Stmt\Function_ $node
	 */
	private function get_fqn( Node $node ): string {
		// NameResolver sets the 'namespacedName' attribute.
		if ( $node->namespacedName !== null ) {
			return $node->namespacedName->toString();
		}

		if ( isset( $node->name ) && $node->name !== null ) {
			return $node->name->toString();
		}

		return '';
	}

	private function get_fqn_from_const( Const_ $node ): string {
		if ( $node->namespacedName !== null ) {
			return $node->namespacedName->toString();
		}

		return $node->name->toString();
	}
}
