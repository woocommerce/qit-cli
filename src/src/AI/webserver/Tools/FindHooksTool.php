<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\Error as PhpParserError;

class FindHooksTool extends BaseTool {
	public function getName(): string {
		return 'find_hooks';
	}

	public function getDescription(): string {
		return 'Locate add_action / add_filter calls';
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'type', 'string', 'action | filter | both', [ 'action', 'filter', 'both' ] ),
			new Parameter( 'hook_names', 'array', 'Exact hook names to match (optional)', [], null, 'string' ),
			new Parameter( 'callbacks', 'array', 'Callback names to match (optional)', [], null, 'string' ),
			new Parameter( 'directory', 'string', 'Directory to scan (default ".")' ),
			new Parameter( 'max_results', 'integer', 'Ceiling on matches (default 100)' ),
			new Parameter( 'max_depth', 'integer', 'Directory depth (default 10)' ),
		];

		return new FunctionInfo(
			$this->getName(),
			[ $this, 'find_hooks' ],
			$this->getDescription(),
			$params,
			[]              // no required parameters
		);
	}

	public function find_hooks(
		?string $type = null,
		?array $hook_names = null,
		?array $callbacks = null,
		string $directory = '.',
		int $max_results = 100,
		int $max_depth = 10
	): string {
		$res = $this->execute( compact(
			'type', 'hook_names', 'callbacks', 'directory', 'max_results', 'max_depth'
		) );

		return json_encode( $res, JSON_UNESCAPED_SLASHES );
	}

	protected function do( array $p ) {
		$typeFilter  = $p['type'] ?? null;   // action|filter|both|null
		$hooksFilter = $p['hook_names'] ?? null;   // array|null
		$cbFilter    = $p['callbacks'] ?? null;   // array|null
		$directory   = $p['directory'] ?? '.';
		$maxResults  = (int) ( $p['max_results'] ?? 100 );
		$maxDepth    = (int) ( $p['max_depth'] ?? 10 );

		$absDir = $this->file_path_resolver->toAbsolute( $directory );
		$it     = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$absDir,
				\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
			),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		$it->setMaxDepth( $maxDepth );

		$parser   = ( new ParserFactory() )->createForNewestSupportedVersion();
		$nodeFind = new NodeFinder();

		$hits = [];

		foreach ( $it as $file ) {
			if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
				continue;
			}

			try {
				$code = file_get_contents( $file->getPathname() );
				// quick micro‑filter so we don’t waste time parsing files that clearly have no hooks
				if ( stripos( $code, 'add_action' ) === false && stripos( $code, 'add_filter' ) === false ) {
					continue;
				}
				$stmts = $parser->parse( $code );
			} catch ( PhpParserError $e ) {
				// Skip un‑parseable files; you can log $e->getMessage() if needed
				continue;
			}

			// Find all function calls whose name is add_action / add_filter
			$calls = $nodeFind->findInstanceOf( $stmts, Node\Expr\FuncCall::class );

			foreach ( $calls as $call ) {
				$name = $call->name instanceof Node\Name ? $call->name->toString() : '';
				if ( ! in_array( $name, [ 'add_action', 'add_filter' ], true ) ) {
					continue;
				}

				$kind = $name === 'add_action' ? 'action' : 'filter';
				if ( $typeFilter && $typeFilter !== 'both' && $kind !== $typeFilter ) {
					continue;
				}

				// Expect at least “hook” & “callback” args
				$args = $call->getArgs();
				if ( count( $args ) < 2 ) {
					continue; // malformed call
				}

				// Resolve Arg #0 (hook name) – constant string only; otherwise '<dynamic>'
				$hook = ( $args[0]->value instanceof Node\Scalar\String_ )
					? $args[0]->value->value
					: '<dynamic>';
				if ( $hooksFilter && ! in_array( $hook, $hooksFilter, true ) ) {
					continue;
				}

				// Resolve Arg #1 (callback) – handles strings & array callables
				$callback = $this->renderCallback( $args[1]->value );
				if ( $cbFilter && ! in_array( $callback, $cbFilter, true ) ) {
					continue;
				}

				$priority     = isset( $args[2] ) && $args[2]->value instanceof Node\Scalar\LNumber
					? (int) $args[2]->value->value
					: 10;
				$acceptedArgs = isset( $args[3] ) && $args[3]->value instanceof Node\Scalar\LNumber
					? (int) $args[3]->value->value
					: 1;

				$hits[] = [
					'file'          => $this->file_path_resolver->toRelative( $file->getPathname() ),
					'line'          => $call->getLine(),
					'type'          => $kind,
					'hook'          => $hook,
					'callback'      => $callback,
					'priority'      => $priority,
					'accepted_args' => $acceptedArgs,
					'snippet'       => trim( $this->lineFromFile( $file->getPathname(), $call->getLine() ) ),
				];

				if ( count( $hits ) >= $maxResults ) {
					return [ 'results' => $hits, 'truncated' => true ];
				}
			}
		}

		return [ 'results' => $hits, 'truncated' => false ];
	}

	/**
	 * Render a callback expression into a readable string
	 * Examples:
	 *   - 'MyClass::method'
	 *   - [$obj, 'method']
	 *   - function() { … }  ->  '<anonymous>'
	 */
	private function renderCallback( Node $expr ): string {
		if ( $expr instanceof Node\Scalar\String_ ) {
			return $expr->value;
		}
		if ( $expr instanceof Node\Expr\Array_ ) {
			// [$obj, 'method'] or ['Class', 'method']
			$parts = [];
			foreach ( $expr->items as $item ) {
				$parts[] = $this->renderCallback( $item->value );
			}

			return implode( '::', $parts );
		}
		if ( $expr instanceof Node\Expr\ClassConstFetch ) {
			return $expr->class->toString() . '::' . $expr->name->toString();
		}
		if ( $expr instanceof Node\Expr\Closure ) {
			return '<anonymous>';
		}

		// Fallback – try to pretty‑print
		return ( new Standard )->prettyPrintExpr( $expr );
	}

	/** Read a single specific line from a file (used for snippet) */
	private function lineFromFile( string $path, int $lineNo ): string {
		$f = new \SplFileObject( $path );
		$f->seek( $lineNo - 1 );          // SplFileObject is 0‑based

		return rtrim( $f->current(), "\r\n" );
	}
}
