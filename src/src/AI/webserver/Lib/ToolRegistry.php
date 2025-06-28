<?php

namespace QIT_AI_Webserver\Lib;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Tool Registry for AI function calling - with proper directory constraints
 */
class ToolRegistry {
	private array $tools = [];
	private FilePathResolver $resolver;
	private string $workDirectory;

	public function __construct( string $work_directory = '' ) {
		// Ensure work directory is set and valid
		if ( empty( $work_directory ) ) {
			throw new Exception( 'Work directory must be specified' );
		}

		// Normalize and validate the work directory
		$this->workDirectory = rtrim( $work_directory, '/\\' );

		if ( ! is_dir( $this->workDirectory ) ) {
			throw new Exception( "Work directory does not exist: {$this->workDirectory}" );
		}

		$this->resolver = new FilePathResolver( $this->workDirectory );
		$this->register_default_tools();
	}

	public function set_work_directory( string $work_directory ): void {
		if ( ! is_dir( $work_directory ) ) {
			throw new Exception( "Work directory does not exist: $work_directory" );
		}
		$this->workDirectory = rtrim( $work_directory, '/\\' );
		$this->resolver      = new FilePathResolver( $this->workDirectory );
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
				$content = $this->resolver->readFile( $path );
				$lines   = explode( "\n", $content );

				// Apply line filtering if specified
				if ( $start_line !== null || $end_line !== null ) {
					$start          = max( 0, ( $start_line ?? 1 ) - 1 );
					$end            = min( count( $lines ), $end_line ?? count( $lines ) );
					$selected_lines = array_slice( $lines, $start, $end - $start );
					$content        = implode( "\n", $selected_lines );
				}

				return [
					'content'     => $content,
					'path'        => $path,
					'lines_read'  => [ $start_line ?? 1, $end_line ?? substr_count( $content, "\n" ) + 1 ],
					'total_lines' => count( $lines )
				];
			} catch ( Exception $e ) {
				return [ 'error' => 'File not found: ' . $path ];
			}
		} );

		// Tool 2: Search for pattern in PHP files
		// In ToolRegistry.php, replace the search_pattern tool implementation:

		$this->register_tool( 'search_pattern', function ( $params ) {
			$pattern     = $params['pattern'] ?? null;
			$max_results = $params['max_results'] ?? 50;
			$directory   = $params['directory'] ?? '';
			$is_regex    = $params['is_regex'] ?? true; // Allow both regex and literal search

			if ( ! $pattern ) {
				return [ 'error' => 'Pattern is required' ];
			}

			$results   = [];
			$searchDir = $this->workDirectory;

			// Directory validation code remains the same...

			try {
				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						$searchDir,
						RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS
					),
					RecursiveIteratorIterator::SELF_FIRST,
					RecursiveIteratorIterator::CATCH_GET_CHILD
				);

				$iterator->setMaxDepth( 10 );

				foreach ( $iterator as $file ) {
					if ( ! $file->isFile() || $file->getExtension() !== 'php' ) {
						continue;
					}

					$filePath     = $file->getPathname();
					$realFilePath = realpath( $filePath );
					$realWorkDir  = realpath( $this->workDirectory );

					if ( $realFilePath === false || strpos( $realFilePath, $realWorkDir ) !== 0 ) {
						continue;
					}

					$relativePath = $this->resolver->toRelative( $filePath );
					$content      = @file_get_contents( $filePath );
					if ( $content === false ) {
						continue;
					}

					$lines = explode( "\n", $content );

					foreach ( $lines as $lineNum => $line ) {
						$matches = false;

						if ( $is_regex ) {
							// Use regex pattern directly
							try {
								$matches = @preg_match( '/' . $pattern . '/i', $line );
							} catch ( Exception $e ) {
								// If regex is invalid, treat as literal
								$matches = stripos( $line, $pattern ) !== false;
							}
						} else {
							// Literal string search
							$matches = stripos( $line, $pattern ) !== false;
						}

						if ( $matches ) {
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
			} catch ( Exception $e ) {
				return [
					'error'   => 'Search failed: ' . $e->getMessage(),
					'pattern' => $pattern
				];
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
				$absoluteDir = $this->workDirectory;
			} else {
				$absoluteDir = $this->resolver->toAbsolute( $relativeDir );
			}

			// Verify directory is within bounds
			$realWorkDir = realpath( $this->workDirectory );
			$realDir     = realpath( $absoluteDir );

			if ( $realDir === false || strpos( $realDir, $realWorkDir ) !== 0 ) {
				return [ 'error' => 'Directory not found or outside bounds: ' . $directory ];
			}

			if ( ! is_dir( $absoluteDir ) ) {
				return [ 'error' => 'Directory not found: ' . $directory ];
			}

			$files = [];
			$dirs  = [];

			$items = @scandir( $absoluteDir );
			if ( $items === false ) {
				return [ 'error' => 'Cannot read directory: ' . $directory ];
			}

			foreach ( $items as $item ) {
				if ( $item === '.' || $item === '..' ) {
					continue;
				}

				$itemPath     = $absoluteDir . '/' . $item;
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
				'directory'         => $relativeDir === '' ? '.' : $relativeDir,
				'files'             => $files,
				'directories'       => $dirs,
				'total_files'       => count( $files ),
				'total_directories' => count( $dirs )
			];
		} );
	}

	private function getContext( array $lines, int $lineNum, int $contextLines = 2 ): array {
		$start = max( 0, $lineNum - $contextLines );
		$end   = min( count( $lines ) - 1, $lineNum + $contextLines );

		$context = [];
		for ( $i = $start; $i <= $end; $i ++ ) {
			$context[] = [
				'line'    => $i + 1,
				'content' => $lines[ $i ],
				'current' => $i === $lineNum
			];
		}

		return $context;
	}
}