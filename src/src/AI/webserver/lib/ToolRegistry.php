<?php

namespace QIT_CLI\AI\WebServer;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

require_once __DIR__ . '/FilePathResolver.php';

/**
 * Tool Registry for AI function calling - embedded to avoid file dependencies
 */
class ToolRegistry {
	private array $tools = [];
	private FilePathResolver $resolver;

	public function __construct( string $work_directory = '' ) {
		$this->resolver = new FilePathResolver($work_directory);
		$this->register_default_tools();
	}

	public function set_work_directory( string $work_directory ): void {
		$this->resolver = new FilePathResolver($work_directory);
	}

	public function register_tool( string $name, callable $handler ): void {
		$this->tools[ $name ] = $handler;
	}

	public function execute_tool( string $name, array $params ): array {
		if ( ! isset( $this->tools[ $name ] ) ) {
			return [ 'error' => "Unknown tool: $name" ];
		}

		try {
			return $this->tools[$name]( $params );
		} catch ( Exception $e ) {
			return [ 'error' => $e->getMessage() ];
		}
	}


	public function get_available_tools(): array {
		return array_keys( $this->tools );
	}

	private function register_default_tools(): void {
		// Tool 1: Read file contents
		$this->register_tool( 'read_file', function ( $params ) {
			$path       = $params['path'] ?? null;
			$start_line = $params['start_line'] ?? null;
			$end_line   = $params['end_line'] ?? null;

			if ( ! $path ) {
				return [ 'error' => 'Path is required' ];
			}

			try {
				$content = $this->resolver->readFile($path);
				$lines = explode( "\n", $content );

				// Apply line filtering if specified
				if ( $start_line !== null || $end_line !== null ) {
					$start          = max( 0, ( $start_line ?? 1 ) - 1 );
					$end            = min( count( $lines ), $end_line ?? count( $lines ) );
					$selected_lines = array_slice( $lines, $start, $end - $start );
					$content        = implode( "\n", $selected_lines );
				}

				return [
					'content'    => $content,
					'path'       => $path,
					'lines_read' => [ $start_line ?? 1, $end_line ?? substr_count( $content, "\n" ) + 1 ],
					'total_lines' => count( $lines )
				];
			} catch ( Exception $e ) {
				return [ 'error' => 'File not found: ' . $path ];
			}
		} );

		// Tool 2: Search for pattern in PHP files
		$this->register_tool( 'search_pattern', function ( $params ) {
			$pattern     = $params['pattern'] ?? null;
			$max_results = $params['max_results'] ?? 50;

			if ( ! $pattern ) {
				return [ 'error' => 'Pattern is required' ];
			}

			$results = [];
			$baseDir = $this->resolver->toAbsolute('');

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $baseDir, RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() || $file->getExtension() !== 'php' ) continue;

				$relativePath = $this->resolver->toRelative( $file->getPathname() );
				$content = file_get_contents( $file->getPathname() );
				$lines = explode( "\n", $content );

				foreach ( $lines as $lineNum => $line ) {
					if ( preg_match( '/' . preg_quote( $pattern, '/' ) . '/i', $line ) ) {
						$results[] = [
							'file'    => $relativePath,
							'line'    => $lineNum + 1,
							'content' => trim( $line ),
							'context' => $this->getContext( $lines, $lineNum )
						];

						if ( count( $results ) >= $max_results ) {
							return [
								'pattern'   => $pattern,
								'results'   => $results,
								'count'     => count( $results ),
								'truncated' => true
							];
						}
					}
				}
			}

			return [
				'pattern'   => $pattern,
				'results'   => $results,
				'count'     => count( $results ),
				'truncated' => false
			];
		} );

		// Tool 3: List files
		$this->register_tool( 'list_files', function ( $params ) {
			$directory = $params['directory'] ?? '.';

			// Normalize directory path
			$relativeDir = trim( $directory, '/' );
			if ( $relativeDir === '.' || $relativeDir === '' ) {
				$absoluteDir = $this->resolver->toAbsolute( '' );
			} else {
				$absoluteDir = $this->resolver->toAbsolute( $relativeDir );
			}

			if ( ! is_dir( $absoluteDir ) ) {
				return [ 'error' => 'Directory not found: ' . $directory ];
			}

			$files = [];
			$dirs = [];

			foreach ( scandir( $absoluteDir ) as $item ) {
				if ( $item === '.' || $item === '..' ) continue;

				$itemPath = $absoluteDir . '/' . $item;
				$relativePath = $this->resolver->toRelative( $itemPath );

				if ( is_dir( $itemPath ) ) {
					$dirs[] = $relativePath;
				} else {
					$files[] = [
						'path'      => $relativePath,
						'size'      => filesize( $itemPath ),
						'extension' => pathinfo( $item, PATHINFO_EXTENSION )
					];
				}
			}

			return [
				'directory'        => $relativeDir === '' ? '.' : $relativeDir,
				'files'            => $files,
				'directories'      => $dirs,
				'total_files'      => count( $files ),
				'total_directories' => count( $dirs )
			];
		} );
	}

	private function getContext( array $lines, int $lineNum, int $contextLines = 2 ): array {
		$start = max( 0, $lineNum - $contextLines );
		$end = min( count( $lines ) - 1, $lineNum + $contextLines );

		$context = [];
		for ( $i = $start; $i <= $end; $i++ ) {
			$context[] = [
				'line'    => $i + 1,
				'content' => $lines[$i],
				'current' => $i === $lineNum
			];
		}

		return $context;
	}
}
