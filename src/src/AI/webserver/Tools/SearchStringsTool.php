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
		return 'Find literal substrings in source files';
	}

	/* ---------- LLPhant function meta ---------- */
	public function getFunctionInfo(): FunctionInfo {
		return new FunctionInfo(
			$this->getName(),
			$this,
			$this->getDescription(),
			[
				new Parameter( 'directory_or_file', 'string', 'Directory or file to search (default ".")' ),
				new Parameter( 'file_types', 'array', 'e.g. ["php","js"] (default ["php"])', [], null, 'string' ),
				new Parameter( 'case_sensitive', 'bool', 'Case‑sensitive search? (default false)' ),
				new Parameter( 'max_results', 'int', 'Ceiling on matches (default 50)' ),
				new Parameter( 'max_depth', 'int', 'Directory depth (default 10)' ),
			],
			[
				new Parameter( 'needles', 'array', 'Array of substrings to match (required)', [], null, 'string' ),
			]
		);
	}

	public function search_strings(
		array   $needles,
		string  $directory_or_file = '.',
		array   $file_types        = ['php'],
		bool    $case_sensitive    = false,
		int     $max_results       = 50,
		int     $max_depth         = 10
	): string {
		$res = $this->execute(compact(
			'needles','directory_or_file','file_types','case_sensitive','max_results','max_depth'
		));

		return json_encode($res, JSON_UNESCAPED_SLASHES);
	}

	/* ---------- core implementation ---------- */
	protected function do( array $p ) {
		$needles         = $p['needles'] ?? [];
		$directoryOrFile = $p['directory_or_file'] ?? '.';
		$fileTypes       = $p['file_types'] ?? [ 'php' ];
		$case            = (bool) ( $p['case_sensitive'] ?? false );
		$maxResults      = (int) ( $p['max_results'] ?? 50 );
		$maxDepth        = (int) ( $p['max_depth'] ?? 10 );

		if ( $needles === [] || ! is_array( $needles ) ) {
			throw new \InvalidArgumentException( '`needles` must be a non‑empty array of strings.' );
		}

		// normalise needles once
		if ( ! $case ) {
			$needles = array_map( 'mb_strtolower', $needles );
		}

		$absPath = $this->file_path_resolver->toAbsolute( $directoryOrFile );
		$hits = [];

		// Check if the path is a file or directory
		if ( is_file( $absPath ) ) {
			// Handle single file
			$file = new \SplFileInfo( $absPath );
			if ( in_array( $file->getExtension(), $fileTypes, true ) ) {
				$hits = $this->searchInFile( $file, $needles, $case, $maxResults );
			}
		} elseif ( is_dir( $absPath ) ) {
			// Handle directory - use existing recursive logic
			$it = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$absPath,
					\FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
				),
				RecursiveIteratorIterator::SELF_FIRST
			);
			$it->setMaxDepth( $maxDepth );

			foreach ( $it as $file ) {
				if ( ! $file->isFile() ) {
					continue;
				}
				if ( ! in_array( $file->getExtension(), $fileTypes, true ) ) {
					continue;
				}

				$fileHits = $this->searchInFile( $file, $needles, $case, $maxResults - count( $hits ) );
				$hits = array_merge( $hits, $fileHits );

				if ( count( $hits ) >= $maxResults ) {
					return [ 'results' => $hits, 'truncated' => true ];
				}
			}
		} else {
			throw new \InvalidArgumentException( "Path does not exist: {$directoryOrFile}" );
		}

		return [ 'results' => $hits, 'truncated' => false ];
	}

	/**
	 * Search for needles in a single file
	 */
	private function searchInFile( \SplFileInfo $file, array $needles, bool $case, int $maxResults ): array {
		$hits = [];
		$content = file_get_contents( $file->getPathname() );
		if ( $content === false ) {
			return $hits;
		}

		$lines = explode( "\n", str_replace( "\r\n", "\n", $content ) );
		foreach ( $lines as $ln => $text ) {
			$haystack = $case ? $text : mb_strtolower( $text );
			foreach ( $needles as $needle ) {
				if ( strpos( $haystack, $needle ) !== false ) {
					$hits[] = [
						'file'    => $this->file_path_resolver->toRelative( $file->getPathname() ),
						'line'    => $ln + 1,
						'needle'  => $case ? $needle : $needle, // already LC if !case
						'snippet' => trim( $text ),
					];
					if ( count( $hits ) >= $maxResults ) {
						return $hits;
					}
					break; // no need to check remaining needles for this line
				}
			}
		}

		return $hits;
	}
}
