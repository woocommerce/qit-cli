<?php

namespace QIT_AI_Webserver\Tools;

use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class FindHooksTool extends BaseTool {

	public function get_name(): string {
		return 'find_hooks';
	}

	public function get_description(): string {
		return 'Locate add_action / add_filter calls';
	}

	public function get_function_info(): FunctionInfo {
		$params = [
			new Parameter( 'type', 'string', 'action | filter | both', [ 'action', 'filter', 'both' ] ),
			new Parameter( 'hook_names', 'array', 'Exact hook names to match (optional)', [], null, 'string' ),
			new Parameter( 'callbacks', 'array', 'Callback names to match (optional)', [], null, 'string' ),
			new Parameter( 'directory', 'string', 'Directory to scan (default ".")' ),
			new Parameter( 'max_results', 'integer', 'Ceiling on matches (default 100)' ),
			new Parameter( 'max_depth', 'integer', 'Directory depth (default 10)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'find_hooks' ],
			$this->get_description(),
			$params,
			[]              // no required parameters
		);
	}

	/**
	 * @param array<string>|null $hook_names
	 * @param array<string>|null $callbacks
	 */
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

	/**
	 * @param array<string, mixed> $p
	 * @return array<string, mixed>
	 */
	protected function do( array $p ) {
		$type_filter  = $p['type'] ?? null;   // action|filter|both|null
		$hooks_filter = $p['hook_names'] ?? null;   // array|null
		$cb_filter    = $p['callbacks'] ?? null;   // array|null
		$directory    = $p['directory'] ?? '.';
		$max_results  = $p['max_results'] ?? 100;
		$max_depth    = $p['max_depth'] ?? 10;

		$directory = $this->safe_path( $directory );

		$abs_dir = $this->file_path_resolver->to_absolute( $directory );

		$files   = [];
		$results = [];

		// Recursively collect PHP files
		$this->collect_php_files( $abs_dir, $files, 0, $max_depth );

		$parser = ( new ParserFactory() )->create( ParserFactory::PREFER_PHP7 );
		$finder = new NodeFinder();

		foreach ( $files as $filepath ) {
			if ( count( $results ) >= $max_results ) {
				break;
			}

			try {
				$code = file_get_contents( $filepath );
				$ast  = $parser->parse( $code );

				if ( ! $ast ) {
					continue;
				}

				// Find function calls
				$func_calls = $finder->findInstanceOf( $ast, 'PhpParser\Node\Expr\FuncCall' );

				foreach ( $func_calls as $call ) {
					if ( count( $results ) >= $max_results ) {
						break 2;
					}

					if ( ! isset( $call->name->name ) ) {
						continue;
					}

					$func_name = $call->name->name;

					// Filter by type
					if ( $type_filter && $type_filter !== 'both' ) {
						if ( $type_filter === 'action' && $func_name !== 'add_action' ) {
							continue;
						}
						if ( $type_filter === 'filter' && $func_name !== 'add_filter' ) {
							continue;
						}
					} elseif ( ! in_array( $func_name, [ 'add_action', 'add_filter' ], true ) ) {
						continue;
					}

					$args = $call->getArgs();

					if ( count( $args ) < 2 ) {
						continue;
					}

					// Extract hook name
					$hook_name = null;
					$callback  = null;

					if ( $args[0] && $args[0]->value instanceof \PhpParser\Node\Scalar\String_ ) {
						$hook_name = $args[0]->value->value;
					}

					// Extract callback
					if ( $args[1] ) {
						$printer  = new PrettyPrinter\Standard();
						$callback = $printer->prettyPrintExpr( $args[1]->value );
					}

					// Apply filters
					if ( $hooks_filter && ! in_array( $hook_name, $hooks_filter, true ) ) {
						continue;
					}

					if ( $cb_filter && ! $this->callback_matches( $callback, $cb_filter ) ) {
						continue;
					}

					$rel_path = $this->file_path_resolver->to_relative( $filepath );

					$results[] = [
						'file'      => $rel_path,
						'function'  => $func_name,
						'hook_name' => $hook_name,
						'callback'  => $callback,
						'line'      => $call->getLine(),
					];
				}
			} catch ( \Exception $e ) {
				// Skip files that can't be parsed
				continue;
			}
		}

		return [
			'matches'   => $results,
			'truncated' => count( $results ) >= $max_results,
		];
	}

	private function collect_php_files( string $dir, array &$files, int $current_depth, int $max_depth ): void {
		if ( $current_depth >= $max_depth ) {
			return;
		}

		if ( ! is_dir( $dir ) ) {
			return;
		}

		$items = glob( $dir . '/*' );
		if ( ! $items ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( is_file( $item ) && str_ends_with( $item, '.php' ) ) {
				$files[] = $item;
			} elseif ( is_dir( $item ) ) {
				$this->collect_php_files( $item, $files, $current_depth + 1, $max_depth );
			}
		}
	}

	/**
	 * @param array<string> $cb_filter
	 */
	private function callback_matches( ?string $callback, array $cb_filter ): bool {
		if ( ! $callback ) {
			return false;
		}

		foreach ( $cb_filter as $filter ) {
			if ( strpos( $callback, $filter ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
