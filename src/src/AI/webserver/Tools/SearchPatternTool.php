<?php

namespace QIT_AI_Webserver\Tools;

use Exception;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use QIT_AI_Webserver\Lib\FilePathResolver;

class SearchPatternTool implements ToolInterface {
	private string $workDirectory;
	private FilePathResolver $resolver;

	public function __construct( string $workDirectory ) {
		$this->workDirectory = rtrim( $workDirectory, '/\\' );
		$this->resolver      = new FilePathResolver( $this->workDirectory );
	}

	public function getName(): string {
		return 'search_pattern';
	}

	public function getDescription(): string {
		return 'Search for a pattern in PHP files';
	}

	public function search_pattern(
		string $pattern,
		int $max_results = 50,
		string $directory = '',
		bool $is_regex = true
	): string {
		$result = $this->execute( [
			'pattern'     => $pattern,
			'max_results' => $max_results,
			'directory'   => $directory,
			'is_regex'    => $is_regex,
		] );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	public function getFunctionInfo(): FunctionInfo {
		$parameters = [
			new Parameter( 'pattern', 'string', 'The search pattern' ),
			new Parameter( 'max_results', 'int', 'Maximum number of results (default: 50)' ),
			new Parameter( 'directory', 'string', 'Directory to search in (default: root)' ),
			new Parameter( 'is_regex', 'bool', 'Whether pattern is regex (default: true)' )
		];

		return new FunctionInfo(
			$this->getName(),
			$this,
			$this->getDescription(),
			$parameters
		);
	}

	public function execute( array $params ): array {
		$pattern     = $params['pattern'] ?? null;
		$max_results = $params['max_results'] ?? 50;
		$directory   = $params['directory'] ?? '';
		$is_regex    = $params['is_regex'] ?? true;

		if ( ! $pattern ) {
			return [ 'error' => 'Pattern is required' ];
		}

		$results   = [];
		$searchDir = $this->workDirectory;

		// If directory specified, resolve it
		if ( ! empty( $directory ) && $directory !== '.' ) {
			$searchDir = $this->resolver->toAbsolute( $directory );

			// Verify directory is within bounds
			$realWorkDir   = realpath( $this->workDirectory );
			$realSearchDir = realpath( $searchDir );

			if ( $realSearchDir === false || strpos( $realSearchDir, $realWorkDir ) !== 0 ) {
				return [ 'error' => 'Directory not found or outside bounds: ' . $directory ];
			}
		}

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
	}

	public function __invoke( string $pattern, int $max_results = 50, string $directory = '', bool $is_regex = true ): string {
		$result = $this->execute( [
			'pattern'     => $pattern,
			'max_results' => $max_results,
			'directory'   => $directory,
			'is_regex'    => $is_regex
		] );

		return json_encode( $result );
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