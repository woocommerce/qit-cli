<?php
namespace QIT_AI_Webserver\Lib;

/**
 * Guard that converts a user‑supplied path to a *relative* path under $workDir
 * and rejects anything that violates Path‑Contract v3 (root‑relative).
 */
class ToolPathGuard {
	/**
	 * Canonical absolute path (no trailing "/")
	 */
	private string $work_dir;
	/**
	 * SUT directory relative to work_dir
	 */
	private string $sut_dir;

	public function __construct( string $work_dir, string $sut_dir = '' ) {
		$real = realpath( $work_dir );
		if ( $real === false || ! is_dir( $real ) ) {
			throw new \RuntimeException( "Invalid working directory: {$work_dir}" );
		}
		$this->work_dir = rtrim( str_replace( '\\', '/', $real ), '/' );
		$this->sut_dir  = rtrim( $sut_dir, '/' );
	}

	/**
	 * Resolve user path to absolute path, trying both WP-relative and SUT-relative notation
	 *
	 * @param string $user_path The raw path coming from the LLM/tool‑call.
	 * @return string           Absolute path to existing file
	 * @throws \RuntimeException On any contract violation or if file not found.
	 */
	public function resolve( string $user_path ): string {
		$user_path = ltrim( str_replace( '\\', '/', $user_path ), '/' );

		// Check if path still contains unresolved placeholders
		if ( preg_match( '/__(?:WP_ROOT|SUT_DIR|DEP_\[[^\]]+\])__/', $user_path ) ) {
			throw new \RuntimeException(
				"Unresolved placeholder in path: {$user_path}. " .
				'Placeholders should have been resolved before reaching path guard.'
			);
		}

		// ① absolute "WP‑relative"      wp-content/plugins/…
		$cand1 = "{$this->work_dir}/{$user_path}";

		// ② "SUT‑relative"              includes/admin.php ⇒ wpRoot/sutDir/…
		$cand2 = "{$this->work_dir}/{$this->sut_dir}/{$user_path}";

		foreach ( [ $cand1, $cand2 ] as $p ) {
			if ( ( is_file( $p ) || is_dir( $p ) ) && substr( realpath( $p ), 0, strlen( $this->work_dir ) ) === $this->work_dir ) {
				return $p;
			}
		}
		throw new \RuntimeException( "Path outside workspace: {$user_path}" );
	}

	/**
	 * @param string $path The raw path coming from the LLM/tool‑call.
	 * @return string       Normalised *relative* path
	 * @throws \RuntimeException On any contract violation.
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
		$candidate = $this->work_dir . '/' . $path;
		$real      = realpath( $candidate ) ?: $this->pseudo_realpath( $candidate );

		// ④ Still inside workspace?
		if ( $real !== $this->work_dir && substr( $real, 0, strlen( $this->work_dir . '/' ) ) !== $this->work_dir . '/' ) {
			throw new \RuntimeException( "Path escapes workspace: {$path}" );
		}

		// ⑤ Return relative
		return ltrim( substr( $real, strlen( $this->work_dir ) ), '/' );
	}

	/** Fallback when file does not exist yet (string‑based realpath) */
	private function pseudo_realpath( string $path ): string {
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
