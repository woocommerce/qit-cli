<?php
namespace QIT_AI_Webserver\Lib;

/**
 * Guard that converts a user‑supplied path to a *relative* path under $workDir
 * and rejects anything that violates Path‑Contract v3 (root‑relative).
 */
class ToolPathGuard {
	private string $workDir;                // canonical absolute path (no trailing "/")
	private string $sutDir;                 // SUT directory relative to workDir

	public function __construct( string $workDir, string $sutDir = '' ) {
		$real = realpath( $workDir );
		if ( $real === false || ! is_dir( $real ) ) {
			throw new \RuntimeException( "Invalid working directory: {$workDir}" );
		}
		$this->workDir = rtrim( str_replace( '\\', '/', $real ), '/' );
		$this->sutDir  = rtrim( $sutDir, '/' );
	}

	/**
	 * Resolve user path to absolute path, trying both WP-relative and SUT-relative notation
	 *
	 * @param string $userPath  The raw path coming from the LLM/tool‑call
	 * @return string           Absolute path to existing file
	 * @throws \RuntimeException on any contract violation or if file not found
	 */
	public function resolve( string $userPath ): string {
		$userPath = ltrim( str_replace( '\\', '/', $userPath ), '/' );

		// Check if path still contains unresolved placeholders
		if ( preg_match( '/__(?:WP_ROOT|SUT_DIR|DEP_\[[^\]]+\])__/', $userPath ) ) {
			throw new \RuntimeException(
				"Unresolved placeholder in path: {$userPath}. " .
				'Placeholders should have been resolved before reaching path guard.'
			);
		}

		// ① absolute "WP‑relative"      wp-content/plugins/…
		$cand1 = "{$this->workDir}/{$userPath}";

		// ② "SUT‑relative"              includes/admin.php ⇒ wpRoot/sutDir/…
		$cand2 = "{$this->workDir}/{$this->sutDir}/{$userPath}";

		foreach ( [ $cand1, $cand2 ] as $p ) {
			if ( ( is_file( $p ) || is_dir( $p ) ) && substr( realpath( $p ), 0, strlen( $this->workDir ) ) === $this->workDir ) {
				return $p;
			}
		}
		throw new \RuntimeException( "Path outside workspace: {$userPath}" );
	}

	/**
	 * @param string $path  The raw path coming from the LLM/tool‑call
	 * @return string       Normalised *relative* path
	 * @throws \RuntimeException on any contract violation
	 */
	public function normalise( string $path ): string {
		// ① Canonicalise separators, trim whitespace
		$path = str_replace( '\\', '/', trim( $path ) );

		// ② Fast contract checks (Path‑Contract v3)
		if ( $path === '' || $path[0] === '/' ) {
			throw new \RuntimeException( "Path must be root‑relative (no leading '/'): {$path}" );
		}
		if ( str_contains( $path, '..' ) ) {
			throw new \RuntimeException( "Path must not contain '..' segments: {$path}" );
		}
		if ( ! preg_match( '#^[A-Za-z0-9_/\.\-]+$#', $path ) ) {
			throw new \RuntimeException( "Path contains invalid characters: {$path}" );
		}

		// ③ Build absolute candidate *inside* workspace
		$candidate = $this->workDir . '/' . $path;
		$real      = realpath( $candidate ) ?: $this->pseudoRealpath( $candidate );

		// ④ Still inside workspace?
		if ( $real !== $this->workDir && substr( $real, 0, strlen( $this->workDir . '/' ) ) !== $this->workDir . '/' ) {
			throw new \RuntimeException( "Path escapes workspace: {$path}" );
		}

		// ⑤ Return relative
		return ltrim( substr( $real, strlen( $this->workDir ) ), '/' );
	}

	/** Fallback when file does not exist yet (string‑based realpath) */
	private function pseudoRealpath( string $path ): string {
		$parts = [];
		foreach ( explode( '/', preg_replace( '#/+#', '/', $path ) ) as $part ) {
			if ( $part === '' || $part === '.' ) {
				continue;
			}
			if ( $part === '..' ) {
				array_pop( $parts );
				continue; }
			$parts[] = $part;
		}
		return '/' . implode( '/', $parts );
	}
}
