<?php

namespace CallGraph;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, Error};
use PhpParser\NodeFinder;

/**
 * Selective file scanner for intelligent file discovery
 */
class SelectiveFileScanner {
	private $baseDirectory;
	private $ignoreDirs;
	private $maxFiles;
	private $parser;
	private $cache = [];

	public function __construct(
		$baseDirectory, array $ignoreDirs = [
		'vendor',
		'node_modules',
		'tests',
		'vendor_prefixed',
		'vendor_scoped',
	], $maxFiles = 1000
	) {
		$this->baseDirectory = $baseDirectory;
		$this->ignoreDirs    = $ignoreDirs;
		$this->maxFiles      = $maxFiles;
		$this->parser        = ( new ParserFactory )->createForNewestSupportedVersion();
	}

	/**
	 * Find files relevant to a specific symbol with multi-phase approach
	 */
	public function findRelevantFiles( $targetFile, $targetSymbol, $maxDepth = 3 ) {
		$relevantFiles    = [];
		$processedSymbols = [];

		// Always include the target file
		if ( file_exists( $targetFile ) ) {
			$relevantFiles[] = realpath( $targetFile );
		}

		// Phase 1: Direct symbol discovery
		$phase1Files   = $this->discoverDirectReferences( $targetSymbol );
		$relevantFiles = array_merge( $relevantFiles, $phase1Files );

		// Phase 2: Recursive dependency resolution (with depth limit)
		$currentDepth     = 0;
		$symbolsToProcess = [ $targetSymbol ];

		while ( ! empty( $symbolsToProcess ) && $currentDepth < $maxDepth ) {
			$nextSymbols = [];

			foreach ( $symbolsToProcess as $symbol ) {
				$symbolKey = $this->getSymbolKey( $symbol );
				if ( in_array( $symbolKey, $processedSymbols ) ) {
					continue;
				}
				$processedSymbols[] = $symbolKey;

				// Find dependencies for this symbol
				$dependencies = $this->findSymbolDependencies( $symbol, array_unique( $relevantFiles ) );

				foreach ( $dependencies['files'] as $file ) {
					if ( ! in_array( $file, $relevantFiles ) ) {
						$relevantFiles[] = $file;
					}
				}

				foreach ( $dependencies['symbols'] as $depSymbol ) {
					$nextSymbols[] = $depSymbol;
				}
			}

			$symbolsToProcess = $nextSymbols;
			$currentDepth ++;
		}

		return array_unique( $relevantFiles );
	}

	/**
	 * Phase 1: Direct reference discovery using text search
	 */
	private function discoverDirectReferences( $targetSymbol ) {
		$files = [];

		if ( $targetSymbol['type'] === 'method' ) {
			$className  = $targetSymbol['class'];
			$methodName = $targetSymbol['name'];

			// Class definitions
			$files = array_merge( $files, $this->findFilesContainingText( "class $className", 10 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "class $className ", 10 ) );

			// Method calls
			$files = array_merge( $files, $this->findFilesContainingText( "->$methodName(", 50 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "::$methodName(", 50 ) );

			// Class instantiations
			$files = array_merge( $files, $this->findFilesContainingText( "new $className", 30 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "new $className(", 30 ) );

			// WordPress hooks (method references in strings)
			$files = array_merge( $files, $this->findFilesContainingText( "'$methodName'", 20 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "\"$methodName\"", 20 ) );

			// Array callbacks
			$files = array_merge( $files, $this->findFilesContainingText( "array($", 20 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "[$", 20 ) );

		} elseif ( $targetSymbol['type'] === 'function' ) {
			$functionName = $targetSymbol['name'];

			// Function definitions
			$files = array_merge( $files, $this->findFilesContainingText( "function $functionName", 10 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "function $functionName(", 10 ) );

			// Function calls
			$files = array_merge( $files, $this->findFilesContainingText( "$functionName(", 50 ) );

			// WordPress hooks
			$files = array_merge( $files, $this->findFilesContainingText( "'$functionName'", 20 ) );
			$files = array_merge( $files, $this->findFilesContainingText( "\"$functionName\"", 20 ) );
		}

		return array_unique( $files );
	}

	/**
	 * Find dependencies for a given symbol by analyzing relevant files
	 */
	private function findSymbolDependencies( $symbol, $filesToAnalyze ) {
		$dependencies = [
			'files'   => [],
			'symbols' => []
		];

		foreach ( $filesToAnalyze as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			try {
				$ast = $this->parseFile( $file );
				if ( ! $ast ) {
					continue;
				}

				if ( $symbol['type'] === 'method' ) {
					// Find callers of this method
					$callers = $this->findMethodCallersInAst( $ast, $symbol['class'], $symbol['name'], $file );
					foreach ( $callers as $caller ) {
						if ( ! in_array( $file, $dependencies['files'] ) ) {
							$dependencies['files'][] = $file;
						}
						$dependencies['symbols'][] = $caller;
					}

					// Find instantiations of the class
					$instantiations = $this->findClassInstantiationsInAst( $ast, $symbol['class'], $file );
					foreach ( $instantiations as $inst ) {
						if ( ! in_array( $file, $dependencies['files'] ) ) {
							$dependencies['files'][] = $file;
						}
						$dependencies['symbols'][] = $inst;
					}
				}
			} catch ( Error $e ) {
				// Skip files with parse errors
			}
		}

		return $dependencies;
	}

	/**
	 * Parse file with caching
	 */
	private function parseFile( $file ) {
		if ( isset( $this->cache[ $file ] ) ) {
			return $this->cache[ $file ];
		}

		try {
			$code                 = file_get_contents( $file );
			$ast                  = $this->parser->parse( $code );
			$this->cache[ $file ] = $ast;

			return $ast;
		} catch ( Error $e ) {
			$this->cache[ $file ] = null;

			return null;
		}
	}

	/**
	 * Find method callers in AST
	 */
	private function findMethodCallersInAst( $ast, $className, $methodName, $file ) {
		$callers = [];
		$finder  = new NodeFinder();

		$methodCalls = $finder->find( $ast, function ( Node $node ) use ( $methodName ) {
			return ( $node instanceof Node\Expr\MethodCall ||
			         $node instanceof Node\Expr\StaticCall ) &&
			       $node->name instanceof Node\Identifier &&
			       $node->name->toString() === $methodName;
		} );

		foreach ( $methodCalls as $call ) {
			$containingMethod = $this->findContainingMethod( $ast, $call );
			if ( $containingMethod ) {
				$callers[] = [
					'type'  => $containingMethod['type'],
					'class' => $containingMethod['class'] ?? null,
					'name'  => $containingMethod['name'],
					'file'  => $file
				];
			}
		}

		return $callers;
	}

	/**
	 * Find class instantiations in AST
	 */
	private function findClassInstantiationsInAst( $ast, $className, $file ) {
		$instantiations = [];
		$finder         = new NodeFinder();

		$newCalls = $finder->find( $ast, function ( Node $node ) use ( $className ) {
			return $node instanceof Node\Expr\New_ &&
			       $node->class instanceof Node\Name &&
			       $node->class->toString() === $className;
		} );

		foreach ( $newCalls as $call ) {
			$containingMethod = $this->findContainingMethod( $ast, $call );
			if ( $containingMethod ) {
				$instantiations[] = [
					'type'  => $containingMethod['type'],
					'class' => $containingMethod['class'] ?? null,
					'name'  => $containingMethod['name'],
					'file'  => $file
				];
			}
		}

		return $instantiations;
	}

	/**
	 * Find containing method for a node
	 */
	private function findContainingMethod( $ast, $targetNode ) {
		$finder = new NodeFinder();

		$methods = $finder->find( $ast, function ( Node $node ) use ( $targetNode ) {
			return ( $node instanceof Node\Stmt\ClassMethod ||
			         $node instanceof Node\Stmt\Function_ ) &&
			       $targetNode->getStartLine() >= $node->getStartLine() &&
			       $targetNode->getEndLine() <= $node->getEndLine();
		} );

		$containingMethod = null;
		$smallestRange    = PHP_INT_MAX;

		foreach ( $methods as $method ) {
			$range = $method->getEndLine() - $method->getStartLine();
			if ( $range < $smallestRange ) {
				$smallestRange    = $range;
				$containingMethod = $method;
			}
		}

		if ( $containingMethod instanceof Node\Stmt\ClassMethod ) {
			$class = $this->findParentClass( $ast, $containingMethod );

			return [
				'type'  => 'method',
				'class' => $class ? $class->name->toString() : 'Unknown',
				'name'  => $containingMethod->name->toString()
			];
		} elseif ( $containingMethod instanceof Node\Stmt\Function_ ) {
			return [
				'type' => 'function',
				'name' => $containingMethod->name->toString()
			];
		}

		return null;
	}

	/**
	 * Find parent class for a method
	 */
	private function findParentClass( $ast, $method ) {
		$finder = new NodeFinder();

		return $finder->findFirst( $ast, function ( Node $node ) use ( $method ) {
			if ( $node instanceof Node\Stmt\Class_ ) {
				foreach ( $node->stmts as $stmt ) {
					if ( $stmt === $method ) {
						return true;
					}
				}
			}

			return false;
		} );
	}

	/**
	 * Fast text-based file search
	 */
	private function findFilesContainingText( $searchText, $maxResults = 50 ) {
		$files = [];

		// Try grep first (fastest)
		if ( $this->isGrepAvailable() ) {
			return $this->grepSearch( $searchText, $maxResults );
		}

		// Fallback to PHP-based search
		return $this->phpSearch( $searchText, $maxResults );
	}

	/**
	 * Check if grep is available
	 */
	private function isGrepAvailable() {
		$output     = [];
		$returnCode = 0;
		exec( 'which grep 2>/dev/null', $output, $returnCode );

		return $returnCode === 0;
	}

	/**
	 * Grep-based search
	 */
	private function grepSearch( $searchText, $maxResults ) {
		$files         = [];
		$escapedSearch = escapeshellarg( $searchText );
		$escapedDir    = escapeshellarg( $this->baseDirectory );

		$excludePatterns = '';
		foreach ( $this->ignoreDirs as $dir ) {
			$excludePatterns .= " --exclude-dir=" . escapeshellarg( $dir );
		}

		$command = "grep -l -r --include='*.php' $excludePatterns $escapedSearch $escapedDir 2>/dev/null | head -n $maxResults";
		exec( $command, $output, $returnCode );

		foreach ( $output as $file ) {
			if ( file_exists( $file ) ) {
				$files[] = realpath( $file );
			}
		}

		return $files;
	}

	/**
	 * PHP-based search (slower but portable)
	 */
	private function phpSearch( $searchText, $maxResults ) {
		$files = [];
		$count = 0;

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $this->baseDirectory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $maxResults ) {
				break;
			}

			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				// Skip ignored directories
				$shouldSkip = false;
				foreach ( $this->ignoreDirs as $ignoreDir ) {
					if ( strpos( $file->getPathname(), DIRECTORY_SEPARATOR . $ignoreDir . DIRECTORY_SEPARATOR ) !== false ) {
						$shouldSkip = true;
						break;
					}
				}

				if ( ! $shouldSkip ) {
					// Quick check if file contains the search text
					$content = file_get_contents( $file->getRealPath() );
					if ( strpos( $content, $searchText ) !== false ) {
						$files[] = $file->getRealPath();
						$count ++;
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Get unique key for a symbol
	 */
	private function getSymbolKey( $symbol ) {
		if ( $symbol['type'] === 'method' ) {
			return "method:{$symbol['class']}::{$symbol['name']}";
		} else {
			return "function:{$symbol['name']}";
		}
	}

	/**
	 * Get all PHP files with limit
	 */
	public function getAllPhpFiles( $limit = null ) {
		$files = [];
		$count = 0;
		$limit = $limit ?: $this->maxFiles;

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $this->baseDirectory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}

			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$shouldSkip = false;
				foreach ( $this->ignoreDirs as $ignoreDir ) {
					if ( strpos( $file->getPathname(), DIRECTORY_SEPARATOR . $ignoreDir . DIRECTORY_SEPARATOR ) !== false ) {
						$shouldSkip = true;
						break;
					}
				}

				if ( ! $shouldSkip ) {
					$files[] = $file->getRealPath();
					$count ++;
				}
			}
		}

		return $files;
	}
}
