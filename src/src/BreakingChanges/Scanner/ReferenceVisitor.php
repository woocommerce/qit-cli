<?php

namespace QIT_CLI\BreakingChanges\Scanner;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use QIT_CLI\BreakingChanges\Models\FoundReference;
use QIT_CLI\BreakingChanges\Models\HookDiffResult;
use QIT_CLI\BreakingChanges\Models\SymbolDiffResult;

class ReferenceVisitor extends NodeVisitorAbstract {
	/** @var array<string, true> Removed class FQNs */
	private array $removed_classes = [];

	/** @var array<string, true> Removed method keys (Class::method) */
	private array $removed_methods = [];

	/** @var array<string, true> Removed function FQNs */
	private array $removed_functions = [];

	/** @var array<string, true> Removed constant names */
	private array $removed_constants = [];

	/** @var array<string, true> Removed hook names */
	private array $removed_hooks = [];

	/** @var FoundReference[] */
	private array $references = [];

	private string $file;

	/** @var array<string, true> Hook registration functions */
	private static array $hook_functions = [
		'add_action'    => true,
		'add_filter'    => true,
		'remove_action' => true,
		'remove_filter' => true,
		'has_action'    => true,
		'has_filter'    => true,
	];

	public function __construct(
		SymbolDiffResult $symbol_diff,
		HookDiffResult $hook_diff,
		string $file
	) {
		$this->file = $file;

		foreach ( $symbol_diff->removed as $symbol ) {
			switch ( $symbol->type ) {
				case 'class':
					$this->removed_classes[ $symbol->get_key() ] = true;
					break;
				case 'method':
					$this->removed_methods[ $symbol->get_key() ] = true;
					break;
				case 'function':
					$this->removed_functions[ $symbol->get_key() ] = true;
					break;
				case 'constant':
					$this->removed_constants[ $symbol->get_key() ] = true;
					break;
			}
		}

		foreach ( $hook_diff->removed as $hook ) {
			$this->removed_hooks[ $hook->name ] = true;
		}
	}

	/**
	 * @return FoundReference[]
	 */
	public function get_references(): array {
		return $this->references;
	}

	/**
	 * @return int|null
	 */
	public function enterNode( Node $node ) {
		if ( $node instanceof Expr\New_ ) {
			$this->check_class_instantiation( $node );
		} elseif ( $node instanceof Expr\StaticCall ) {
			$this->check_static_call( $node );
		} elseif ( $node instanceof Expr\StaticPropertyFetch ) {
			$this->check_static_property( $node );
		} elseif ( $node instanceof Expr\ClassConstFetch ) {
			$this->check_class_const( $node );
		} elseif ( $node instanceof Expr\FuncCall ) {
			$this->check_function_call( $node );
		} elseif ( $node instanceof Expr\ConstFetch ) {
			$this->check_constant_access( $node );
		} elseif ( $node instanceof Node\Stmt\Class_ ) {
			$this->check_class_extends( $node );
			$this->check_implements( $node );
		} elseif ( $node instanceof Node\Stmt\Enum_ ) {
			$this->check_implements( $node );
		}

		return null;
	}

	private function check_class_instantiation( Expr\New_ $node ): void {
		if ( ! $node->class instanceof Name ) {
			return;
		}

		$class_name = $node->class->toString();
		if ( isset( $this->removed_classes[ $class_name ] ) ) {
			$this->references[] = new FoundReference(
				$class_name,
				'class_usage',
				$this->file,
				$node->getStartLine(),
				"new {$class_name}(...)"
			);
		}
	}

	private function check_static_call( Expr\StaticCall $node ): void {
		if ( ! $node->class instanceof Name ) {
			return;
		}

		$class_name = $node->class->toString();

		// Check if the class itself is removed.
		if ( isset( $this->removed_classes[ $class_name ] ) ) {
			$this->references[] = new FoundReference(
				$class_name,
				'class_usage',
				$this->file,
				$node->getStartLine(),
				"{$class_name}::..."
			);
			return;
		}

		// Check if the specific method is removed.
		if ( $node->name instanceof Node\Identifier ) {
			$method_key = $class_name . '::' . $node->name->toString();
			if ( isset( $this->removed_methods[ $method_key ] ) ) {
				$this->references[] = new FoundReference(
					$method_key,
					'static_call',
					$this->file,
					$node->getStartLine(),
					"{$method_key}(...)"
				);
			}
		}
	}

	private function check_static_property( Expr\StaticPropertyFetch $node ): void {
		if ( ! $node->class instanceof Name ) {
			return;
		}

		$class_name = $node->class->toString();
		if ( isset( $this->removed_classes[ $class_name ] ) ) {
			$this->references[] = new FoundReference(
				$class_name,
				'class_usage',
				$this->file,
				$node->getStartLine(),
				"{$class_name}::\$..."
			);
		}
	}

	private function check_class_const( Expr\ClassConstFetch $node ): void {
		if ( ! $node->class instanceof Name ) {
			return;
		}

		$class_name = $node->class->toString();
		if ( isset( $this->removed_classes[ $class_name ] ) ) {
			$this->references[] = new FoundReference(
				$class_name,
				'class_usage',
				$this->file,
				$node->getStartLine(),
				"{$class_name}::CONST"
			);
		}
	}

	private function check_function_call( Expr\FuncCall $node ): void {
		if ( ! $node->name instanceof Name ) {
			return;
		}

		$func_name = $node->name->toString();

		// Check if it's a removed function.
		if ( isset( $this->removed_functions[ $func_name ] ) ) {
			$this->references[] = new FoundReference(
				$func_name,
				'function_call',
				$this->file,
				$node->getStartLine(),
				"{$func_name}(...)"
			);
			return;
		}

		// Check if it's a hook registration referencing a removed hook.
		$func_lower = $node->name->toLowerString();
		if ( isset( self::$hook_functions[ $func_lower ] ) ) {
			$this->check_hook_reference( $node );
		}
	}

	private function check_hook_reference( Expr\FuncCall $node ): void {
		if ( count( $node->args ) < 1 ) {
			return;
		}

		$first_arg = $node->args[0];
		if ( ! $first_arg instanceof Arg ) {
			return;
		}

		if ( ! $first_arg->value instanceof String_ ) {
			return;
		}

		$hook_name = $first_arg->value->value;
		if ( isset( $this->removed_hooks[ $hook_name ] ) ) {
			$func_name          = $node->name instanceof Name ? $node->name->toString() : 'hook_call';
			$this->references[] = new FoundReference(
				$hook_name,
				'hook_registration',
				$this->file,
				$node->getStartLine(),
				"{$func_name}( '{$hook_name}', ... )"
			);
		}
	}

	private function check_constant_access( Expr\ConstFetch $node ): void {
		$const_name = $node->name->toString();
		if ( isset( $this->removed_constants[ $const_name ] ) ) {
			$this->references[] = new FoundReference(
				$const_name,
				'constant_access',
				$this->file,
				$node->getStartLine(),
				$const_name
			);
		}
	}

	private function check_class_extends( Node\Stmt\Class_ $node ): void {
		if ( $node->extends === null ) {
			return;
		}

		$parent = $node->extends->toString();
		if ( isset( $this->removed_classes[ $parent ] ) ) {
			$this->references[] = new FoundReference(
				$parent,
				'class_usage',
				$this->file,
				$node->getStartLine(),
				"extends {$parent}"
			);
		}
	}

	/**
	 * @param Node\Stmt\Class_|Node\Stmt\Enum_ $node
	 */
	private function check_implements( Node $node ): void {
		$implements = $node->implements ?? [];
		foreach ( $implements as $interface ) {
			$name = $interface->toString();
			if ( isset( $this->removed_classes[ $name ] ) ) {
				$this->references[] = new FoundReference(
					$name,
					'class_usage',
					$this->file,
					$node->getStartLine(),
					"implements {$name}"
				);
			}
		}
	}
}
