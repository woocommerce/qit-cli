<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class TreeDirectoryTool extends BaseTool {

	public function get_name(): string {
		return 'tree_directory';
	}

	public function get_description(): string {
		return $this->base_description(
			'Generate a tree structure of a directory',
			[
				'Show directory tree: directory="src"',
				'Limited depth: directory="src", max_depth=3',
			]
		);
	}

	public function get_function_info(): FunctionInfo {
		$params = [
			new Parameter( 'directory', 'string', 'Directory to generate tree for (default: ".")' ),
			new Parameter( 'max_depth', 'integer', 'Maximum depth to traverse (default: 5)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'tree_directory' ],
			$this->get_description(),
			$params,
			[]               // no required parameters
		);
	}

	public function tree_directory(
		string $directory = '.',
		int $max_depth = 5
	): string {
		$result = $this->execute( compact( 'directory', 'max_depth' ) );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param array<string, mixed> $p
	 * @return array<string, mixed>
	 */
	protected function do( array $p ) {
		$directory = $this->safe_path( $p['directory'] ?? '.' );
		$max_depth = $p['max_depth'] ?? 5;

		$abs_dir = $this->file_path_resolver->to_absolute( $directory );

		if ( ! is_dir( $abs_dir ) ) {
			throw new \InvalidArgumentException( "Directory does not exist: {$directory}" );
		}

		$tree = $this->build_tree( $abs_dir, 0, $max_depth );

		return [
			'directory' => $directory,
			'tree'      => $tree,
			'format'    => 'nested_array',
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function build_tree( string $dir, int $current_depth, int $max_depth ): array {
		if ( $current_depth >= $max_depth ) {
			return [ '_truncated' => true ];
		}

		$items = glob( $dir . '/*' );
		if ( ! $items ) {
			return [];
		}

		$tree = [];

		foreach ( $items as $item ) {
			$basename = basename( $item );
			$rel_path = $this->file_path_resolver->to_relative( $item );

			if ( is_dir( $item ) ) {
				$tree[ $basename ] = [
					'type'     => 'directory',
					'path'     => $rel_path,
					'children' => $this->build_tree( $item, $current_depth + 1, $max_depth ),
				];
			} else {
				$tree[ $basename ] = [
					'type' => 'file',
					'path' => $rel_path,
					'size' => filesize( $item ),
				];
			}
		}

		return $tree;
	}
}
