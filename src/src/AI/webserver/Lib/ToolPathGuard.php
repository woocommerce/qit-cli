<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Light‑weight guard for paths coming from the LLM.
 *
 * Rules
 * -----
 *  1. Paths **must stay inside** $workDir – no “..” escape.
 *  2. Both absolute and relative input is accepted:
 *     •  absolute, but inside $workDir  → strip prefix and return relative
 *     •  absolute and *outside*         → RuntimeException
 *  3. Normalise back‑slashes and duplicate slashes.
 *  4. We **don’t** check that the file / dir exists – the tool itself will
 *     return an error the model can read.
 */
class ToolPathGuard {
	private string $workDir;   // canonical absolute path with no trailing “/”

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
		$path = str_replace( '\\', '/', $path );
		$path = preg_replace( '#/+#', '/', $path );      // “foo//bar” → “foo/bar”
		$path = trim( $path );

		/* ------------------------------------------------ absolute input */
		if ( strpos( $path, '/' ) === 0 ) {
			// normalise and compare with workDir
			$real = realpath( $path );
			if ( $real === false ) {
				throw new \RuntimeException( 'Path is outside the working directory.' );
			}
			$real = str_replace( '\\', '/', $real );
			if ( strpos( $real, $this->workDir . '/' ) !== 0 ) {
				throw new \RuntimeException( 'Path escapes the working directory.' );
			}
			// strip the prefix and make relative
			$path = ltrim( substr( $real, strlen( $this->workDir ) ), '/' );
		}

		/* ------------------------------------ reject obvious traversal */
		if ( $path === '' || $path === '.' || $path === './' ) {
			return '.';
		}
		if ( preg_match( '#(^|/)\.\.(?:/|$)#', $path ) ) {
			throw new \RuntimeException( 'Directory traversal (“..”) is not allowed.' );
		}

		return ltrim( $path, '/' );
	}
}
