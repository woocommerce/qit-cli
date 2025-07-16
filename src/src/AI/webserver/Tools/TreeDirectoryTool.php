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
	private string $sut_dir;

	public function __construct( string $work_directory, string $sut_directory = '', ?\QIT_AI_Webserver\ToolContext $context = null ) {
		parent::__construct( $work_directory, $sut_directory, $context );
		$this->sut_dir = ltrim( $sut_directory, '/' );
	}

	public function getName(): string {
		return 'tree_directory';
	}

	public function getDescription(): string {
		return $this->baseDescription(
			'Recursively list a directory up to "depth" levels. "path" may be WP_ROOT‑relative, '
			. 'or start with the placeholders __WP_ROOT__, __SUT_DIR__, __DEP_[slug]__.',
			[
				'tree_directory("__WP_ROOT__/wp-content/plugins")',
				'tree_directory("__SUT_DIR__", 3)',
				'tree_directory("__SUT_DIR__/includes")',
				'tree_directory("__DEP_[woocommerce]__/includes", 4)',
			]
		);
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


	/**
	 * Core directory tree functionality.
	 */
	protected function do( array $p ) {
		$path  = $this->safePath( $p['path'] ?? '' );
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
		$max_lines = 500;
		$truncated = false;

		foreach ( $iter as $file ) {
			$rel = substr( $file->getPathname(), strlen( $abs ) + 1 ); // relative to start dir
			if ( $rel === '' ) {
				continue;
			}
			$entries[] = $rel . ( $file->isDir() ? '/' : '' );
			if ( count( $entries ) >= $max_lines ) {
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
