<?php

namespace QIT_CLI\BreakingChanges\Scanner;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use QIT_CLI\BreakingChanges\Extraction\FileParser;
use QIT_CLI\BreakingChanges\Models\HookDiffResult;
use QIT_CLI\BreakingChanges\Models\ScanResult;
use QIT_CLI\BreakingChanges\Models\SymbolDiffResult;

class ReferenceScanner {
	/** @var string[] Directories to skip */
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
	 * Scan a directory for references to removed symbols/hooks.
	 */
	public function scan(
		string $directory,
		SymbolDiffResult $symbol_diff,
		HookDiffResult $hook_diff,
		string $plugin_slug = ''
	): ScanResult {
		$all_references = [];
		$warnings       = [];
		$base_path      = rtrim( $directory, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		if ( empty( $plugin_slug ) ) {
			$plugin_slug = basename( $directory );
		}

		// No removals means nothing to scan for.
		if ( ! $symbol_diff->has_removals() && ! $hook_diff->has_removals() ) {
			return new ScanResult( $plugin_slug );
		}

		$php_files = $this->find_php_files( $directory );

		foreach ( $php_files as $file ) {
			$relative_path = str_replace( $base_path, '', $file );
			$ast           = $this->parser->parse( $file );

			if ( $ast === null ) {
				$warnings[] = "Failed to parse: {$relative_path}";
				continue;
			}

			$visitor   = new ReferenceVisitor( $symbol_diff, $hook_diff, $relative_path );
			$traverser = new NodeTraverser();
			$traverser->addVisitor( new NameResolver() );
			$traverser->addVisitor( $visitor );
			$traverser->traverse( $ast );

			$all_references = array_merge( $all_references, $visitor->get_references() );
		}

		return new ScanResult( $plugin_slug, $all_references, $warnings );
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
