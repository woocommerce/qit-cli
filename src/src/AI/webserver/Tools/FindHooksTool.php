<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FindHooksTool extends BaseTool {
	public function getName(): string {
		return 'find_hooks';
	}

	public function getDescription(): string {
		return 'Locate add_action / add_filter calls';
	}

	public function getFunctionInfo(): FunctionInfo {
		return new FunctionInfo(
			$this->getName(),
			$this,
			$this->getDescription(),
			[
				new Parameter( 'type', 'string', 'action | filter | both', [ 'action', 'filter', 'both' ] ),
				new Parameter( 'hook_names', 'array', 'Exact hook names to match (optional)', [], null, 'string' ),
				new Parameter( 'callbacks', 'array', 'Callback names to match (optional)', [], null, 'string' ),
				new Parameter( 'directory', 'string', 'Directory to scan (default ".")' ),
				new Parameter( 'max_results', 'int', 'Ceiling on matches (default 100)' ),
				new Parameter( 'max_depth', 'int', 'Directory depth (default 10)' ),
			]
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
		$typeFilter  = $p['type'] ?? null;                  // both|action|filter
		$hooksFilter = $p['hook_names'] ?? null;                  // null|array
		$cbFilter    = $p['callbacks'] ?? null;                  // null|array
		$directory   = $p['directory'] ?? '.';
		$maxResults  = (int) ( $p['max_results'] ?? 100 );
		$maxDepth    = (int) ( $p['max_depth'] ?? 10 );

		if ( $typeFilter && ! in_array( $typeFilter, [ 'action', 'filter', 'both' ], true ) ) {
			throw new \InvalidArgumentException( '`type` must be "action", "filter", "both", or null.' );
		}
		$hooksFilter = is_array( $hooksFilter ) ? $hooksFilter : null;
		$cbFilter    = is_array( $cbFilter ) ? $cbFilter : null;

		$re = '/add_(action|filter)\s*\(\s*' .
		      '[\'"](?P<hook>[^\'"]+)[\'"]\s*,\s*' .
		      '(?P<callback>[^,\)\s]+).*?' .
		      '(?:,\s*(?P<priority>\d+))?' .
		      '(?:,\s*(?P<args>\d+))?' .
		      '\s*\)/i';

		$absDir = $this->r->toAbsolute( $directory );
		$it     = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$absDir,
				\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$it->setMaxDepth( $maxDepth );

		$hits = [];
		foreach ( $it as $file ) {
			if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
				continue;
			}
			$relPath = $this->r->toRelative( $file->getPathname() );

			$lines = file( $file->getPathname(), FILE_IGNORE_NEW_LINES );
			foreach ( $lines as $ln => $line ) {
				if ( ! preg_match( $re, $line, $m ) ) {
					continue;
				}

				$kind = strtolower( $m[1] ); // action|filter
				if ( $typeFilter && $typeFilter !== 'both' && $kind !== $typeFilter ) {
					continue;
				}

				$hook = $m['hook'];
				if ( $hooksFilter && ! in_array( $hook, $hooksFilter, true ) ) {
					continue;
				}

				$cb = trim( $m['callback'], " \t\n\r\0\x0B'\"" );
				if ( $cbFilter && ! in_array( $cb, $cbFilter, true ) ) {
					continue;
				}

				$hits[] = [
					'file'          => $relPath,
					'line'          => $ln + 1,
					'type'          => $kind,
					'hook'          => $hook,
					'callback'      => $cb,
					'priority'      => isset( $m['priority'] ) && $m['priority'] !== '' ? (int) $m['priority'] : 10,
					'accepted_args' => isset( $m['args'] ) && $m['args'] !== '' ? (int) $m['args'] : 1,
					'snippet'       => trim( $line ),
				];

				if ( count( $hits ) >= $maxResults ) {
					return [ 'results' => $hits, 'truncated' => true ];
				}
			}
		}

		return [ 'results' => $hits, 'truncated' => false ];
	}
}
