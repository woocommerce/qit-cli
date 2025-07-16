<?php

namespace QIT_AI_Webserver\Lib;

final class DebugLogger {
	/** 
	 * Append a structured message to debug log 
	 * @param array<string, mixed> $payload
	 */
	public static function log( string $stage, array $payload ): void {
		$debug_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/qit-node/debug';
		if ( ! is_dir( $debug_dir ) ) {
			mkdir( $debug_dir, 0700, true );
		}
		$log_file = $debug_dir . '/debug-prompt.log';

		$dbg = is_file( $log_file )
			? json_decode( file_get_contents( $log_file ), true ) ?? []
			: [];

		$dbg[] = [
			'ts_ms' => (int) ( microtime( true ) * 1000 ),
			'stage' => $stage,
			'data'  => $payload,
		];

		file_put_contents( $log_file,
			json_encode( $dbg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);
	}

	public static function dir_tree( string $dir, int $depth = 2, int $max_lines = 400 ): string {
		$dir = rtrim( $dir, '/\\' );

		// -- 1) Try native `tree`
		$cmd = 'command -v tree';
		if ( trim( shell_exec( $cmd ) ?? '' ) !== '' ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec,WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec - This call is safe as-is.
			$tree_cmd = sprintf(
				'tree -a -L %d --dirsfirst %s 2>/dev/null | head -n %d',
				$depth,
				escapeshellarg( $dir ),
				$max_lines
			);
			$out      = shell_exec( $tree_cmd );  // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec,WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec - This call is safe as-is.
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
			if ( count( $lines ) >= $max_lines ) {
				break;
			}
		}

		return implode( "\n", $lines );
	}
}
