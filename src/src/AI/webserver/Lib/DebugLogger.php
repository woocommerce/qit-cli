<?php

namespace QIT_AI_Webserver\Lib;

final class DebugLogger {
	/** Append a structured message to debug log */
	public static function log( string $stage, array $payload ): void {
		$debugDir = rtrim(sys_get_temp_dir(), '/\\') . '/qit-node/debug';
		if (!is_dir($debugDir)) {
			mkdir($debugDir, 0700, true);
		}
		$logFile = $debugDir . '/debug-prompt.log';

		$dbg = is_file( $logFile )
			? json_decode( file_get_contents( $logFile ), true ) ?? []
			: [];

		$dbg[] = [
			'ts_ms' => (int) ( microtime( true ) * 1000 ),
			'stage' => $stage,
			'data'  => $payload,
		];

		file_put_contents( $logFile,
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
