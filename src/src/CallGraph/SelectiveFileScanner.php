<?php

namespace CallGraph;

class SelectiveFileScanner {
	private $baseDir;
	private $ignoreDirs;
	private $maxFiles;
	private $fileCache = [];

	public function __construct( string $baseDir, array $ignoreDirs = [], int $maxFiles = 1000 ) {
		$this->baseDir    = rtrim( $baseDir, '/' );
		$this->ignoreDirs = $ignoreDirs;
		$this->maxFiles   = $maxFiles;
	}

	/**
	 * Find files relevant to the given symbol
	 */
	public function findRelevantFiles( string $priorityFile, array $symbol ): array {
		$relevantFiles = [];
		$symbolName    = $symbol['name'] ?? '';

		if ( empty( $symbolName ) ) {
			return [];
		}

		// Always include the priority file first
		if ( file_exists( $priorityFile ) ) {
			$relevantFiles[] = $priorityFile;
		}

		// Search for files that might contain references to this symbol
		$allFiles = $this->getAllPhpFiles( $this->maxFiles );

		foreach ( $allFiles as $file ) {
			if ( in_array( $file, $relevantFiles ) ) {
				continue;
			}

			if ( $this->fileContainsSymbol( $file, $symbolName ) ) {
				$relevantFiles[] = $file;

				// Limit the number of files to process
				if ( count( $relevantFiles ) >= $this->maxFiles ) {
					break;
				}
			}
		}

		return $relevantFiles;
	}

	/**
	 * Prioritize files based on relevance
	 */
	public function prioritizeFiles( array $allFiles, array $relevantFiles ): array {
		// Put relevant files first, then add remaining files
		$prioritized = $relevantFiles;

		foreach ( $allFiles as $file ) {
			if ( ! in_array( $file, $prioritized ) ) {
				$prioritized[] = $file;

				if ( count( $prioritized ) >= $this->maxFiles ) {
					break;
				}
			}
		}

		return $prioritized;
	}

	/**
	 * Get all PHP files in the base directory
	 */
	public function getAllPhpFiles( int $maxFiles = null ): array {
		$maxFiles = $maxFiles ?: $this->maxFiles;
		$cacheKey = "all_php_files_{$maxFiles}";

		if ( isset( $this->fileCache[ $cacheKey ] ) ) {
			return $this->fileCache[ $cacheKey ];
		}

		$files = [];
		$this->scanDirectory( $this->baseDir, $files, $maxFiles );

		$this->fileCache[ $cacheKey ] = $files;
		return $files;
	}

	/**
	 * Recursively scan directory for PHP files
	 */
	private function scanDirectory( string $dir, array &$files, int $maxFiles ): void {
		if ( count( $files ) >= $maxFiles ) {
			return;
		}

		if ( ! is_dir( $dir ) || ! is_readable( $dir ) ) {
			return;
		}

		$dirName = basename( $dir );
		if ( in_array( $dirName, $this->ignoreDirs ) ) {
			return;
		}

		try {
			$iterator = new \DirectoryIterator( $dir );

			foreach ( $iterator as $fileInfo ) {
				if ( count( $files ) >= $maxFiles ) {
					break;
				}

				if ( $fileInfo->isDot() ) {
					continue;
				}

				$filePath = $fileInfo->getPathname();

				if ( $fileInfo->isDir() ) {
					$this->scanDirectory( $filePath, $files, $maxFiles );
				} elseif ( $fileInfo->isFile() && $fileInfo->getExtension() === 'php' ) {
					$files[] = $filePath;
				}
			}
		} catch ( \Exception $e ) {
			// Skip directories that can't be read
		}
	}

	/**
	 * Check if a file contains references to a symbol
	 */
	private function fileContainsSymbol( string $file, string $symbolName ): bool {
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return false;
		}

		try {
			$content = file_get_contents( $file );

			// Simple string search for the symbol name
			// This could be enhanced with more sophisticated parsing
			return strpos( $content, $symbolName ) !== false;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
