<?php

namespace QIT_CLI\BreakingChanges\Extraction;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Visitors\HookVisitor;
use QIT_CLI\BreakingChanges\Visitors\SymbolVisitor;

class DirectoryExtractor {
	/** @var string[] Directories to skip during extraction */
	private const SKIP_DIRS = [
		'vendor',
		'node_modules',
		'tests',
		'test',
		'.git',
	];

	private FileParser $parser;

	public function __construct( FileParser $parser ) {
		$this->parser = $parser;
	}

	/**
	 * Extract all symbols and hooks from PHP files in a directory.
	 */
	public function extract( string $directory ): ExtractedSymbols {
		$result    = new ExtractedSymbols();
		$php_files = $this->find_php_files( $directory );
		$base_path = rtrim( $directory, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		foreach ( $php_files as $file ) {
			$relative_path = str_replace( $base_path, '', $file );
			$ast           = $this->parser->parse( $file );

			if ( $ast === null ) {
				$result->add_warning( "Failed to parse: {$relative_path}" );
				continue;
			}

			$file_symbols = new ExtractedSymbols();

			$symbol_visitor = new SymbolVisitor( $file_symbols, $relative_path );
			$hook_visitor   = new HookVisitor( $file_symbols, $relative_path );

			$traverser = new NodeTraverser();
			$traverser->addVisitor( new NameResolver() );
			$traverser->addVisitor( $symbol_visitor );
			$traverser->addVisitor( $hook_visitor );
			$traverser->traverse( $ast );

			$result->merge( $file_symbols );
		}

		return $result;
	}

	/**
	 * Recursively find all .php files, skipping excluded directories.
	 *
	 * @return string[]
	 */
	private function find_php_files( string $directory ): array {
		$files     = [];
		$directory = rtrim( $directory, DIRECTORY_SEPARATOR );

		if ( ! is_dir( $directory ) ) {
			return $files;
		}

		$iterator = new \RecursiveDirectoryIterator(
			$directory,
			\RecursiveDirectoryIterator::SKIP_DOTS
		);

		$filter = new \RecursiveCallbackFilterIterator(
			$iterator,
			function ( \SplFileInfo $current, string $key, \RecursiveDirectoryIterator $iterator ): bool {
				if ( $current->isDir() ) {
					return ! in_array( $current->getFilename(), self::SKIP_DIRS, true );
				}

				return $current->getExtension() === 'php';
			}
		);

		$flat_iterator = new \RecursiveIteratorIterator( $filter );

		foreach ( $flat_iterator as $file ) {
			/** @var \SplFileInfo $file */
			$files[] = $file->getPathname();
		}

		sort( $files );

		return $files;
	}
}
