<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SearchStringsTool extends BaseTool {
	public function getName(): string {
		return 'search_strings';
	}

	public function getDescription(): string {
		return $this->baseDescription(
			'Find literal substrings in source files. "directory_or_file" may be WP_ROOT‑relative, '
			. 'or start with the placeholders __WP_ROOT__, __SUT_DIR__, __DEP_[slug]__.',
			[
				'search_strings(["wp_ajax"], "__WP_ROOT__/wp-admin")',
				'search_strings(["add_action", "add_filter"], "__SUT_DIR__")',
				'search_strings(["nonce"], "__SUT_DIR__/includes", ["php"], false, 100)',
				'search_strings(["woocommerce"], "__DEP_[woocommerce]__", ["php", "js"])',
			]
		);
	}

	/**
	 * LLPhant function meta
	 */
	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'needles', 'array', 'Array of substrings to match (required)', [], null, 'string' ),
			new Parameter( 'directory_or_file', 'string', 'Directory or file to search (default ".")' ),
			new Parameter( 'file_types', 'array', 'e.g. ["php","js"] (default ["php"])', [], null, 'string' ),
			new Parameter( 'case_sensitive', 'boolean', 'Case‑sensitive search? (default false)' ),
			new Parameter( 'max_results', 'integer', 'Ceiling on matches (default 50)' ),
			new Parameter( 'max_depth', 'integer', 'Directory depth (default 10)' ),
		];

		return new FunctionInfo(
			$this->getName(),
			[ $this, 'search_strings' ],
			$this->getDescription(),
			$params,
			[ $params[0] ]              // pass a reference to the required parameters
		);
	}

	public function search_strings(
		array $needles,
		string $directory_or_file = '.',
		array $file_types = [ 'php' ],
		bool $case_sensitive = false,
		int $max_results = 50,
		int $max_depth = 10
	): string {
		$res = $this->execute( compact(
			'needles', 'directory_or_file', 'file_types', 'case_sensitive', 'max_results', 'max_depth'
		) );

		return json_encode( $res, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Core implementation
	 */
	protected function do( array $p ) {
		$needles           = $p['needles'] ?? [];
		$directory_or_file = $p['directory_or_file'] ?? '.';
		$file_types        = $p['file_types'] ?? [ 'php' ];
		$case              = (bool) ( $p['case_sensitive'] ?? false );
		$max_results       = (int) ( $p['max_results'] ?? 50 );
		$max_depth         = (int) ( $p['max_depth'] ?? 10 );

		if ( $needles === [] || ! is_array( $needles ) ) {
			throw new \InvalidArgumentException( '`needles` must be a non‑empty array of strings.' );
		}

		// normalise needles once
		if ( ! $case ) {
			$needles = array_map( 'mb_strtolower', $needles );
		}

		$abs_path = $this->file_path_resolver->toAbsolute( $directory_or_file );
		$hits     = [];

		// Check if the path is a file or directory
		if ( is_file( $abs_path ) ) {
			// Handle single file
			$file = new \SplFileInfo( $abs_path );
			if ( in_array( $file->getExtension(), $file_types, true ) ) {
				$hits = $this->searchInFile( $file, $needles, $case, $max_results );
			}
		} elseif ( is_dir( $abs_path ) ) {
			// Handle directory - use existing recursive logic
			$it = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$abs_path,
					\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
				),
				RecursiveIteratorIterator::SELF_FIRST
			);
			$it->setMaxDepth( $max_depth );

			foreach ( $it as $file ) {
				if ( ! $file->isFile() ) {
					continue;
				}
				if ( ! in_array( $file->getExtension(), $file_types, true ) ) {
					continue;
				}

				$file_hits = $this->searchInFile( $file, $needles, $case, $max_results - count( $hits ) );
				$hits      = array_merge( $hits, $file_hits );

				if ( count( $hits ) >= $max_results ) {
					return [
						'results'   => $hits,
						'truncated' => true,
					];
				}
			}
		} else {
			throw new \InvalidArgumentException( "Path does not exist: {$directory_or_file}" );
		}

		return [
			'results'   => $hits,
			'truncated' => false,
		];
	}

	/**
	 * Search for needles in a single file
	 */
	private function searchInFile( \SplFileInfo $file, array $needles, bool $case_sensitive, int $max_results ): array {
		$hits    = [];
		$content = file_get_contents( $file->getPathname() );
		if ( $content === false ) {
			return $hits;
		}

		$lines = explode( "\n", str_replace( "\r\n", "\n", $content ) );
		foreach ( $lines as $ln => $text ) {
			$haystack = $case_sensitive ? $text : mb_strtolower( $text );
			foreach ( $needles as $needle ) {
				if ( strpos( $haystack, $needle ) !== false ) {
					$hits[] = [
						'file'    => $this->file_path_resolver->to_relative( $file->getPathname() ),
						'line'    => $ln + 1,
						'needle'  => $needle,
						'snippet' => trim( $text ),
					];
					if ( count( $hits ) >= $max_results ) {
						return $hits;
					}
					break; // no need to check remaining needles for this line
				}
			}
		}

		return $hits;
	}
}
