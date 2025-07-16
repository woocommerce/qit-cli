<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class SearchStringsTool extends BaseTool {

	public function get_name(): string {
		return 'search_strings';
	}

	public function get_description(): string {
		return $this->base_description(
			'Search for strings/patterns in files using grep-like functionality',
			[
				'Search for function: needles=["function_name"], directory="src"',
				'Multiple patterns: needles=["class", "function"], file_types=["php"]',
			]
		);
	}

	public function get_function_info(): FunctionInfo {
		$params = [
			new Parameter( 'needles', 'array', 'Strings/patterns to search for', [], null, 'string' ),
			new Parameter( 'directory', 'string', 'Directory to search in (default: ".")' ),
			new Parameter( 'file_types', 'array', 'File extensions to include (default: ["php"])', [], null, 'string' ),
			new Parameter( 'case_sensitive', 'boolean', 'Case sensitive search (default: false)' ),
			new Parameter( 'max_results', 'integer', 'Maximum results (default: 100)' ),
			new Parameter( 'max_depth', 'integer', 'Maximum directory depth (default: 10)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'search_strings' ],
			$this->get_description(),
			$params,
			[ 'needles' ]    // required parameters
		);
	}

	/**
	 * @param array<string> $needles
	 * @param array<string> $file_types
	 */
	public function search_strings(
		array $needles,
		string $directory = '.',
		array $file_types = [ 'php' ],
		bool $case_sensitive = false,
		int $max_results = 100,
		int $max_depth = 10
	): string {
		$result = $this->execute( compact(
			'needles', 'directory', 'file_types', 'case_sensitive', 'max_results', 'max_depth'
		) );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param array<string, mixed> $p
	 * @return array<string, mixed>
	 */
	protected function do( array $p ) {
		$needles        = $p['needles'];
		$directory      = $p['directory'] ?? '.';
		$file_types     = $p['file_types'] ?? [ 'php' ];
		$case_sensitive = $p['case_sensitive'] ?? false;
		$max_results    = $p['max_results'] ?? 100;
		$max_depth      = $p['max_depth'] ?? 10;

		if ( empty( $needles ) || ! is_array( $needles ) ) {
			throw new \InvalidArgumentException( 'needles parameter must be a non-empty array' );
		}

		$directory = $this->safe_path( $directory );
		$abs_dir   = $this->file_path_resolver->to_absolute( $directory );

		if ( ! is_dir( $abs_dir ) ) {
			throw new \InvalidArgumentException( "Directory does not exist: {$directory}" );
		}

		$files   = [];
		$results = [];

		// Collect files
		$this->collect_files( $abs_dir, $files, $file_types, 0, $max_depth );

		foreach ( $files as $file_path ) {
			if ( count( $results ) >= $max_results ) {
				break;
			}

			$matches = $this->searchInFile( $file_path, $needles, $case_sensitive );
			if ( ! empty( $matches ) ) {
				$rel_path = $this->file_path_resolver->to_relative( $file_path );
				$results[] = [
					'file'    => $rel_path,
					'matches' => $matches,
				];
			}
		}

		return [
			'results'       => $results,
			'total_matches' => array_sum( array_map( fn( $r ) => count( $r['matches'] ), $results ) ),
			'truncated'     => count( $results ) >= $max_results,
		];
	}

	/**
	 * @param array<string> $file_types
	 */
	private function collect_files( string $dir, array &$files, array $file_types, int $current_depth, int $max_depth ): void {
		if ( $current_depth >= $max_depth ) {
			return;
		}

		$items = glob( $dir . '/*' );
		if ( ! $items ) {
			return;
		}

		foreach ( $items as $item ) {
			if ( is_file( $item ) ) {
				$ext = pathinfo( $item, PATHINFO_EXTENSION );
				if ( in_array( $ext, $file_types, true ) ) {
					$files[] = $item;
				}
			} elseif ( is_dir( $item ) ) {
				$this->collect_files( $item, $files, $file_types, $current_depth + 1, $max_depth );
			}
		}
	}

	/**
	 * @param array<string> $needles
	 * @return array<array{line: int, content: string, needle: string}>
	 */
	private function searchInFile( string $file_path, array $needles, bool $case_sensitive ): array {
		$content = file_get_contents( $file_path );
		if ( $content === false ) {
			return [];
		}

		$lines   = explode( "\n", $content );
		$matches = [];

		foreach ( $lines as $line_num => $line_content ) {
			foreach ( $needles as $needle ) {
				$search_line   = $case_sensitive ? $line_content : strtolower( $line_content );
				$search_needle = $case_sensitive ? $needle : strtolower( $needle );

				if ( strpos( $search_line, $search_needle ) !== false ) {
					$matches[] = [
						'line'    => $line_num + 1,
						'content' => trim( $line_content ),
						'needle'  => $needle,
					];
				}
			}
		}

		return $matches;
	}
}
