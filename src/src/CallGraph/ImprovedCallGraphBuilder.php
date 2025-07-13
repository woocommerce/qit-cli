<?php

namespace CallGraph;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, Error};
use PhpParser\NodeFinder;

class ImprovedCallGraphBuilder {
	private $parser;
	private $callGraph = [];
	private $currentFile;
	private $visited     = [];
	private $files       = [];
	private $symbolTable = [];
	private $maxFiles    = 1000;
	private $scanner;
	private $useSelectiveAnalysis = true;
	private $symbolCache          = [];

	public function __construct( array $files = [], string $priorityFile = null, array $options = [] ) {
		$this->parser               = ( new ParserFactory() )->createForNewestSupportedVersion();
		$this->maxFiles             = $options['max_files'] ?? 1000;
		$this->useSelectiveAnalysis = $options['selective_analysis'] ?? true;

		// Initialize the selective file scanner
		$baseDir       = $options['base_directory'] ?? dirname( $priorityFile ?: $files[0] ?? __DIR__ );
		$ignoreDirs    = $options['ignore_dirs'] ?? [ 'vendor', 'node_modules', 'build', 'dist' ];
		$this->scanner = new SelectiveFileScanner( $baseDir, $ignoreDirs, $this->maxFiles );

		$this->files = $files;

		// Don't build full symbol table immediately if using selective analysis
		if ( ! $this->useSelectiveAnalysis ) {
			$this->buildSymbolTable();
		}
	}

	/**
	 * Build call graph starting from file:line with selective analysis
	 */
	public function buildFromFileLine( string $file, int $line ) {
		// First, find the symbol at the specified line
		$symbol = $this->findSymbolAtLine( $file, $line );
		if ( ! $symbol ) {
			return [ 'error' => "No function/method found at $file:$line" ];
		}

		// If selective analysis is enabled, find relevant files first
		if ( $this->useSelectiveAnalysis ) {
			$relevantFiles = $this->scanner->findRelevantFiles( $file, $symbol );

			// If we found relevant files, use them; otherwise fall back to all files
			if ( ! empty( $relevantFiles ) ) {
				$this->files = $this->scanner->prioritizeFiles( $this->files, $relevantFiles );
			}

			// Now build symbol table only for relevant files
			$this->buildSymbolTable( $this->files );
		}

		// Continue with normal analysis
		if ( $symbol['type'] === 'function' ) {
			return $this->buildForFunction( $symbol['name'] );
		} else {
			return $this->buildForMethod( $symbol['class'], $symbol['name'] );
		}
	}

	/**
	 * Build symbol table with timeout and progress tracking
	 */
	private function buildSymbolTable( $files = null ) {
		$files            = $files ?: $this->files;
		$startTime        = time();
		$maxExecutionTime = 30;
		$processedFiles   = 0;
		$totalFiles       = count( $files );

		// Clear previous symbol table if rebuilding
		$this->symbolTable = [];

		foreach ( $files as $file ) {
			// Check for timeout
			if ( time() - $startTime > $maxExecutionTime ) {
				fwrite( STDERR, "Warning: Symbol table building timed out after {$maxExecutionTime} seconds. Processed {$processedFiles}/{$totalFiles} files.\n" );
				break;
			}

			if ( ! file_exists( $file ) ) {
				continue;
			}

			// Check cache first
			$cacheKey = md5( $file . filemtime( $file ) );
			if ( isset( $this->symbolCache[ $cacheKey ] ) ) {
				$this->symbolTable = array_merge( $this->symbolTable, $this->symbolCache[ $cacheKey ] );
				++$processedFiles;
				continue;
			}

			try {
				$code = file_get_contents( $file );
				$ast  = $this->parser->parse( $code );

				$visitor   = new SymbolTableVisitor( $file );
				$traverser = new NodeTraverser();
				$traverser->addVisitor( $visitor );
				$traverser->traverse( $ast );

				$symbols                        = $visitor->getSymbols();
				$this->symbolCache[ $cacheKey ] = $symbols;
				$this->symbolTable              = array_merge( $this->symbolTable, $symbols );
				++$processedFiles;

				// Progress indicator for large codebases
				if ( $processedFiles % 100 === 0 ) {
					fwrite( STDERR, "Progress: Processed {$processedFiles}/{$totalFiles} files...\n" );
				}
			} catch ( Error $e ) {
				// Skip files with parse errors
			}
		}
	}

	/**
	 * Adaptive analysis - expand scope if needed
	 */
	private function expandAnalysisScope( $symbol ) {
		$additionalFiles = [];

		// If we didn't find enough information, progressively expand the search
		if ( count( $this->callGraph ) < 3 ) {
			fwrite( STDERR, "Expanding analysis scope for better results...\n" );

			// Get more files from the scanner
			$allFiles         = $this->scanner->getAllPhpFiles( $this->maxFiles * 2 );
			$currentFileCount = count( $this->files );

			foreach ( $allFiles as $file ) {
				if ( ! in_array( $file, $this->files ) ) {
					$additionalFiles[] = $file;
					if ( count( $additionalFiles ) >= 500 ) {
						break;
					}
				}
			}

			if ( ! empty( $additionalFiles ) ) {
				$this->files = array_merge( $this->files, $additionalFiles );
				$this->buildSymbolTable( $additionalFiles );
				return true;
			}
		}

		return false;
	}

	/**
	 * Find symbol at specific file:line with caching
	 */
	public function findSymbolAtLine( string $file, int $line ) {
		$cacheKey = "$file:$line";
		if ( isset( $this->symbolCache[ $cacheKey ] ) ) {
			return $this->symbolCache[ $cacheKey ];
		}

		if ( ! file_exists( $file ) ) {
			return null;
		}

		try {
			$code = file_get_contents( $file );
			$ast  = $this->parser->parse( $code );

			$finder = new NodeFinder();
			$symbol = $finder->findFirst($ast, function ( Node $node ) use ( $line ) {
				if ( $node instanceof Node\Stmt\Function_ ||
					$node instanceof Node\Stmt\ClassMethod ) {
					$startLine = $node->getStartLine();
					$endLine   = $node->getEndLine();

					return $line >= $startLine && $line <= $endLine;
				}

				return false;
			});

			$result = null;

			if ( $symbol instanceof Node\Stmt\Function_ ) {
				$result = [
					'type' => 'function',
					'name' => $symbol->name->toString(),
				];
			} elseif ( $symbol instanceof Node\Stmt\ClassMethod ) {
				$class  = $this->findParentClass( $ast, $symbol );
				$result = [
					'type'  => 'method',
					'class' => $class ? $class->name->toString() : 'Unknown',
					'name'  => $symbol->name->toString(),
				];
			}

			$this->symbolCache[ $cacheKey ] = $result;
			return $result;

		} catch ( Error $e ) {
			return null;
		}
	}

	/**
	 * Build call graph for a function
	 */
	public function buildForFunction( string $functionName ) {
		$this->callGraph = [];
		$this->visited   = [];

		$this->analyzeFunction( $functionName );

		// Try to expand scope if we didn't find much
		if ( $this->useSelectiveAnalysis && count( $this->callGraph ) < 3 ) {
			$this->expandAnalysisScope( [
				'name' => $functionName,
				'type' => 'function',
			] );
			$this->analyzeFunction( $functionName ); // Re-analyze with expanded scope
		}

		return [
			'function'       => $functionName,
			'call_graph'     => $this->callGraph,
			'files_analyzed' => count( $this->files ),
			'symbols_found'  => count( $this->symbolTable ),
		];
	}

	/**
	 * Build call graph for a method
	 */
	public function buildForMethod( string $className, string $methodName ) {
		$this->callGraph = [];
		$this->visited   = [];

		$this->analyzeMethod( $className, $methodName );

		// Try to expand scope if we didn't find much
		if ( $this->useSelectiveAnalysis && count( $this->callGraph ) < 3 ) {
			$this->expandAnalysisScope( [
				'name'  => $methodName,
				'class' => $className,
				'type'  => 'method',
			] );
			$this->analyzeMethod( $className, $methodName ); // Re-analyze with expanded scope
		}

		return [
			'class'          => $className,
			'method'         => $methodName,
			'call_graph'     => $this->callGraph,
			'files_analyzed' => count( $this->files ),
			'symbols_found'  => count( $this->symbolTable ),
		];
	}

	/**
	 * Analyze a function and build its call graph
	 */
	private function analyzeFunction( string $functionName ) {
		if ( in_array( $functionName, $this->visited ) ) {
			return;
		}

		$this->visited[] = $functionName;

		// Find function definition in symbol table
		$functionInfo = $this->findFunctionInSymbolTable( $functionName );
		if ( ! $functionInfo ) {
			return;
		}

		// Parse the function and find calls
		$calls = $this->findCallsInFunction( $functionInfo );

		if ( ! empty( $calls ) ) {
			$this->callGraph[ $functionName ] = $calls;

			// Recursively analyze called functions
			foreach ( $calls as $call ) {
				if ( $call['type'] === 'function' ) {
					$this->analyzeFunction( $call['name'] );
				} elseif ( $call['type'] === 'method' ) {
					$this->analyzeMethod( $call['class'], $call['name'] );
				}
			}
		}
	}

	/**
	 * Analyze a method and build its call graph
	 */
	private function analyzeMethod( string $className, string $methodName ) {
		$methodKey = "$className::$methodName";

		if ( in_array( $methodKey, $this->visited ) ) {
			return;
		}

		$this->visited[] = $methodKey;

		// Find method definition in symbol table
		$methodInfo = $this->findMethodInSymbolTable( $className, $methodName );
		if ( ! $methodInfo ) {
			return;
		}

		// Parse the method and find calls
		$calls = $this->findCallsInMethod( $methodInfo );

		if ( ! empty( $calls ) ) {
			$this->callGraph[ $methodKey ] = $calls;

			// Recursively analyze called functions/methods
			foreach ( $calls as $call ) {
				if ( $call['type'] === 'function' ) {
					$this->analyzeFunction( $call['name'] );
				} elseif ( $call['type'] === 'method' ) {
					$this->analyzeMethod( $call['class'], $call['name'] );
				}
			}
		}
	}

	/**
	 * Find function in symbol table
	 */
	private function findFunctionInSymbolTable( string $functionName ) {
		foreach ( $this->symbolTable as $symbol ) {
			if ( $symbol['type'] === 'function' && $symbol['name'] === $functionName ) {
				return $symbol;
			}
		}
		return null;
	}

	/**
	 * Find method in symbol table
	 */
	private function findMethodInSymbolTable( string $className, string $methodName ) {
		$methodKey = "$className::$methodName";
		foreach ( $this->symbolTable as $symbol ) {
			if ( $symbol['type'] === 'method' && $symbol['full_name'] === $methodKey ) {
				return $symbol;
			}
		}
		return null;
	}

	/**
	 * Find calls within a function
	 */
	private function findCallsInFunction( array $functionInfo ) {
		return $this->findCallsInCode( $functionInfo['file'], $functionInfo['line'] );
	}

	/**
	 * Find calls within a method
	 */
	private function findCallsInMethod( array $methodInfo ) {
		return $this->findCallsInCode( $methodInfo['file'], $methodInfo['line'] );
	}

	/**
	 * Find function/method calls in code starting from a specific line
	 */
	private function findCallsInCode( string $file, int $startLine ) {
		$calls = [];

		if ( ! file_exists( $file ) ) {
			return $calls;
		}

		try {
			$code = file_get_contents( $file );
			$ast  = $this->parser->parse( $code );

			$visitor   = new CallFinderVisitor( $startLine );
			$traverser = new NodeTraverser();
			$traverser->addVisitor( $visitor );
			$traverser->traverse( $ast );

			return $visitor->getCalls();
		} catch ( Error $e ) {
			return $calls;
		}
	}

	/**
	 * Find parent class of a method node
	 */
	private function findParentClass( $ast, $method ) {
		$finder = new NodeFinder();

		return $finder->findFirst($ast, function ( Node $node ) use ( $method ) {
			if ( $node instanceof Node\Stmt\Class_ ) {
				foreach ( $node->stmts as $stmt ) {
					if ( $stmt === $method ) {
						return true;
					}
				}
			}

			return false;
		});
	}

	/**
	 * Get the current call graph
	 */
	public function getCallGraph() {
		return $this->callGraph;
	}

	/**
	 * Get symbol table statistics
	 */
	public function getStats() {
		return [
			'files_processed' => count( $this->files ),
			'symbols_found'   => count( $this->symbolTable ),
			'call_graph_size' => count( $this->callGraph ),
			'cache_size'      => count( $this->symbolCache ),
		];
	}
}

/**
 * Visitor to find function and method calls
 */
class CallFinderVisitor extends NodeVisitorAbstract {
	private $calls = [];
	private $targetStartLine;
	private $inTargetFunction = false;
	private $braceLevel       = 0;

	public function __construct( int $targetStartLine ) {
		$this->targetStartLine = $targetStartLine;
	}

	public function enterNode( Node $node ) {
		// Check if we're entering the target function/method
		if ( ( $node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod )
			&& $node->getStartLine() === $this->targetStartLine ) {
			$this->inTargetFunction = true;
			$this->braceLevel       = 0;
		}

		// Track brace levels to know when we exit the function
		if ( $this->inTargetFunction && $node instanceof Node\Stmt ) {
			// This is a simplified approach - in reality, you'd want more sophisticated tracking
		}

		// Look for function calls
		if ( $this->inTargetFunction && $node instanceof Node\Expr\FuncCall ) {
			if ( $node->name instanceof Node\Name ) {
				$this->calls[] = [
					'type' => 'function',
					'name' => $node->name->toString(),
					'line' => $node->getStartLine(),
				];
			}
		}

		// Look for method calls
		if ( $this->inTargetFunction && $node instanceof Node\Expr\MethodCall ) {
			$this->calls[] = [
				'type'  => 'method',
				'class' => 'Unknown', // Would need more analysis to determine class
				'name'  => $node->name->name ?? 'unknown',
				'line'  => $node->getStartLine(),
			];
		}

		// Look for static method calls
		if ( $this->inTargetFunction && $node instanceof Node\Expr\StaticCall ) {
			$className = 'Unknown';
			if ( $node->class instanceof Node\Name ) {
				$className = $node->class->toString();
			}

			$this->calls[] = [
				'type'  => 'method',
				'class' => $className,
				'name'  => $node->name->name ?? 'unknown',
				'line'  => $node->getStartLine(),
			];
		}

		return null;
	}

	public function leaveNode( Node $node ) {
		// Check if we're leaving the target function/method
		if ( $this->inTargetFunction &&
			( $node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod )
			&& $node->getStartLine() === $this->targetStartLine ) {
			$this->inTargetFunction = false;
		}

		return null;
	}

	public function getCalls() {
		return $this->calls;
	}
}
