<?php

namespace QIT_CLI\BreakingChanges\Visitors;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\HookInfo;

class HookVisitor extends NodeVisitorAbstract {
	/** @var array<string, true> */
	private static array $action_functions = [
		'do_action'            => true,
		'do_action_ref_array'  => true,
		'do_action_deprecated' => true,
	];

	/** @var array<string, true> */
	private static array $filter_functions = [
		'apply_filters'            => true,
		'apply_filters_ref_array'  => true,
		'apply_filters_deprecated' => true,
	];

	private ExtractedSymbols $symbols;
	private string $file;

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
		if ( ! $node instanceof FuncCall ) {
			return null;
		}

		if ( ! $node->name instanceof Node\Name ) {
			return null;
		}

		$func_name = $node->name->toLowerString();

		$is_action = isset( self::$action_functions[ $func_name ] );
		$is_filter = isset( self::$filter_functions[ $func_name ] );

		if ( ! $is_action && ! $is_filter ) {
			return null;
		}

		if ( count( $node->args ) < 1 ) {
			return null;
		}

		$first_arg = $node->args[0];
		if ( ! $first_arg instanceof Arg ) {
			return null;
		}

		$hook_type = $is_action ? 'action' : 'filter';

		// Count additional arguments (excluding the hook name itself).
		$arg_count = count( $node->args ) - 1;

		if ( $first_arg->value instanceof String_ ) {
			$hook_name = $first_arg->value->value;

			$this->symbols->add_hook( new HookInfo(
				$hook_name,
				$hook_type,
				$this->file,
				$node->getStartLine(),
				false,
				$arg_count
			) );
		} else {
			// Dynamic hook name — can't determine statically.
			++$this->symbols->dynamic_hook_count;
		}

		return null;
	}
}
