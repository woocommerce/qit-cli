<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Light‑weight guard for paths coming from the LLM.
 *
 * Rules
 * -----
 *  1. Paths must stay **inside** $workDir – no “..” escape.
 *  2. Absolute *and* relative input is accepted:
 *       •  absolute, but inside $workDir  → strip prefix and return relative
 *       •  absolute and outside           → RuntimeException
 *  3. Normalise back‑slashes and duplicate slashes.
 *  4. We don’t check that the file/dir exists – the tool itself will
 *     return an error the model can read.
 */
class ToolPathGuard {
	private string $workDir;           // canonical absolute path, no trailing “/”

	public function __construct( string $workDir ) {
		$real = realpath( $workDir );
		if ( $real === false || ! is_dir( $real ) ) {
			throw new \RuntimeException( "Invalid working directory: {$workDir}" );
		}
		$this->workDir = rtrim( str_replace( '\\', '/', $real ), '/' );
	}

	/**
	 * Convert user‑supplied $path to a *relative* canonical path or throw.
	 */
	public function normalise( string $path ): string {

		$path = str_replace( '\\', '/', trim( $path ) );

		/* ① “.” or ""  → project root  */
		if ( $path === '' || $path === '.' ) {
			return '';
		}

		/* ② Build an absolute candidate path */
		$isAbsolute = $path[0] === '/' || preg_match( '#^[A-Za-z]:/#', $path ); // *nix or Win drive
		$candidate  = $isAbsolute ? $path : $this->workDir . '/' . ltrim( $path, '/' );

		/* ③ Canonicalise.  If the target does not exist realpath() returns
		      false – in that case fall back to a purely string‑based clean‑up. */
		$real = realpath( $candidate );
		if ( $real === false ) {
			$parts = [];
			foreach ( explode( '/', preg_replace( '#/+#', '/', $candidate ) ) as $part ) {
				if ( $part === '' || $part === '.' ) {
					continue;
				}
				if ( $part === '..' ) {
					array_pop( $parts );
				} else {
					$parts[] = $part;
				}
			}
			$real = '/' . implode( '/', $parts );
		}
		$real = str_replace( '\\', '/', $real );         // win → unix slashes

		/* ④ Verify the path is inside $workDir */
		if ( $real !== $this->workDir && ! str_starts_with( $real, $this->workDir . '/' ) ) {
			throw new \RuntimeException( "Path escapes workspace: {$path}" );
		}

		/* ⑤ Return the path *relative* to the extraction root */

		return ltrim( substr( $real, strlen( $this->workDir ) ), '/' );
	}
}
