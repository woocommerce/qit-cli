<?php

namespace QIT_AI_Webserver\Lib;

final class DebugLogger {
	/** Append a structured message to /tmp/debug-prompt.log */
	public static function log( string $stage, array $payload ): void {
		$dbg = is_file( '/tmp/debug-prompt.log' )
			? json_decode( file_get_contents( '/tmp/debug-prompt.log' ), true ) ?? []
			: [];

		$dbg[] = [
			'ts_ms' => (int) ( microtime( true ) * 1000 ),
			'stage' => $stage,
			'data'  => $payload,
		];

		file_put_contents( '/tmp/debug-prompt.log',
			json_encode( $dbg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);
	}

	public static function dirTree( string $dir, int $depth = 2, int $maxLines = 400 ): string {
		$dir = rtrim( $dir, '/\\' );

		// -- 1) Try native `tree`
		$cmd = 'command -v tree';
		if ( trim( shell_exec( $cmd ) ?? '' ) !== '' ) {
			$treeCmd = sprintf(
				'tree -a -L %d --dirsfirst %s 2>/dev/null | head -n %d',
				$depth,
				escapeshellarg( $dir ),
				$maxLines
			);
			$out     = shell_exec( $treeCmd );
			if ( $out !== null ) {
				return $out;
			}
		}

		// -- 2) Fallback: PHP iterator
		$lines = [];
		$iter  = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir,
				\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		$iter->setMaxDepth( $depth );
		foreach ( $iter as $path => $info ) {
			$rel     = substr( $path, strlen( $dir ) + 1 );
			$pad     = str_repeat( '│   ', $iter->getDepth() );
			$lines[] = $pad . ( $info->isDir() ? '├── ' : '└── ' ) . $rel;
			if ( count( $lines ) >= $maxLines ) {
				break;
			}
		}

		return implode( "\n", $lines );
	}
}