<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Tool: tree_directory
 *
 * Return a recursive listing (like `tree -L <depth>`) for any directory
 * inside the workspace.  Uses the same macro‑aware path rules as other tools.
 */
class TreeDirectoryTool extends BaseTool {

	/** @var string relative path such as wp-content/plugins/foo-bar */
	private string $sutDir;

	public function __construct( string $workDirectory, string $sutDirectory = '' ) {
		parent::__construct( $workDirectory, $sutDirectory );
		$this->sutDir = ltrim( $sutDirectory, '/' );
	}

	public function getName(): string {
		return 'tree_directory';
	}

	public function getDescription(): string {
		return 'Recursively list a directory up to "depth" levels. Path may use macros: $WP_ROOT, $SUT, $DEP[slug] or be WP_ROOT-relative/SUT-relative.';
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'path', 'string', 'Start directory (required)' ),
			new Parameter( 'depth', 'integer', 'Max depth 1‑5 (default 2)' ),
		];

		return new FunctionInfo(
			$this->getName(),
			[ $this, 'tree_directory' ],
			$this->getDescription(),
			$params,
			[ $params[0] ]            // “path” is required
		);
	}

	public function tree_directory( string $path, int $depth = 2 ): string {
		return json_encode( $this->execute( compact( 'path', 'depth' ) ), JSON_UNESCAPED_SLASHES );
	}

	/* ─────────────── macro resolver ─────────────── */
	private function resolveMacroPath( string $userPath ): string {
		// Handle macro variables like $WP_ROOT, $SUT, $DEP[slug]
		if ( strpos( $userPath, '$WP_ROOT' ) === 0 ) {
			$remainder = substr( $userPath, 8 ); // Remove '$WP_ROOT' prefix
			return ltrim( $remainder, '/' ); // Remove leading slash if present
		}

		if ( strpos( $userPath, '$SUT' ) === 0 ) {
			$remainder = substr( $userPath, 4 ); // Remove '$SUT' prefix
			$sutPath = $this->getSutRelativePath();

			if ( empty( $remainder ) || $remainder === '/' ) {
				// Just $SUT, return the SUT directory path
				return $sutPath;
			} else {
				// $SUT/something, combine SUT path with remainder
				return $sutPath . '/' . ltrim( $remainder, '/' );
			}
		}

		// Handle $DEP[slug] pattern
		if ( preg_match( '/^\$DEP\[([^\]]+)\](.*)$/', $userPath, $matches ) ) {
			$depSlug = $matches[1];
			$depPath = $matches[2];
			// Construct path to dependency: wp-content/plugins/{slug} or wp-content/themes/{slug}
			// Default to plugins for now
			$basePath = "wp-content/plugins/{$depSlug}";
			if ( empty( $depPath ) || $depPath === '/' ) {
				return $basePath;
			} else {
				return $basePath . '/' . ltrim( $depPath, '/' );
			}
		}

		// No macro, return as-is
		return $userPath;
	}

	private function getSutRelativePath(): string {
		return $this->sutDir;
	}

	/* ─────────────── core ─────────────── */
	protected function do( array $p ) {
		$rawPath = $p['path'] ?? '';
		$resolvedPath = $this->resolveMacroPath( $rawPath );
		$path  = $this->safePath( $resolvedPath );
		$depth = max( 1, min( (int) ( $p['depth'] ?? 2 ), 5 ) );   // clamp to 1‑5

		$abs = $this->file_path_resolver->toAbsolute( $path );
		if ( ! is_dir( $abs ) ) {
			throw new \InvalidArgumentException( "Not a directory: {$path}" );
		}

		$iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $abs,
				\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS ),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$iter->setMaxDepth( $depth );

		$entries   = [];
		$maxLines  = 500;
		$truncated = false;

		foreach ( $iter as $file ) {
			$rel = substr( $file->getPathname(), strlen( $abs ) + 1 ); // relative to start dir
			if ( $rel === '' ) {
				continue;
			}
			$entries[] = $rel . ( $file->isDir() ? '/' : '' );
			if ( count( $entries ) >= $maxLines ) {
				$truncated = true;
				break;
			}
		}

		return [
			'start_dir' => $this->file_path_resolver->toRelative( $abs ),
			'entries'   => $entries,
			'depth'     => $depth,
			'truncated' => $truncated,
		];
	}
}
