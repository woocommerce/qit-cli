<?php

namespace CallGraph;

use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, Error};
use PhpParser\NodeFinder;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Enhanced CallGraphBuilder with selective file scanning
 */
class CallGraphBuilder {
	private $parser;
	private $callGraph = [];
	private $currentFile;
	private $visited = [];
	private $files = [];
	private $symbolTable = [];
	private $maxFiles = 1000;
	private $useSelectiveScanning = true;
	private $scanner;
	private $baseDirectory;
	private $verbose = false;
	private $quiet = false;

	public function __construct(array $files = [], string $priorityFile = null, array $options = []) {
		$this->parser = (new ParserFactory)->createForNewestSupportedVersion();
		$this->maxFiles = $options['max_files'] ?? 1000;
		$this->useSelectiveScanning = $options['selective_scanning'] ?? true;
		$this->baseDirectory = $options['base_directory'] ?? getcwd();
		$this->verbose = $options['verbose'] ?? false;
		$this->quiet = $options['quiet'] ?? false;

		// Initialize selective scanner
		$ignoreDirs = $options['ignore_dirs'] ?? ['vendor', 'node_modules'];
		$this->scanner = new SelectiveFileScanner($this->baseDirectory, $ignoreDirs, $this->maxFiles);

		if ($this->useSelectiveScanning && $priorityFile) {
			// Don't build full symbol table yet - wait for specific symbol request
			$this->files = $files;
		} else {
			// Original behavior
			if (count($files) > $this->maxFiles) {
				$this->info("Warning: Too many files (" . count($files) . "). Limiting to {$this->maxFiles} files.");

				if ($priorityFile && in_array($priorityFile, $files)) {
					$filesWithoutPriority = array_diff($files, [$priorityFile]);
					$this->files = array_merge(
						[$priorityFile],
						array_slice($filesWithoutPriority, 0, $this->maxFiles - 1)
					);
				} else {
					$this->files = array_slice($files, 0, $this->maxFiles);
				}
			} else {
				$this->files = $files;
			}

			$this->buildSymbolTable();
		}
	}

	/**
	 * Output debug message respecting quiet/verbose flags
	 */
	private function debug($message) {
		if (!$this->quiet && $this->verbose) {
			fwrite(STDERR, $message . "\n");
		}
	}

	/**
	 * Output info message (only suppressed in quiet mode)
	 */
	private function info($message) {
		if (!$this->quiet) {
			fwrite(STDERR, $message . "\n");
		}
	}

	/**
	 * Build symbol table with progress reporting
	 */
	private function buildSymbolTable() {
		$startTime = time();
		$maxExecutionTime = 30;
		$processedFiles = 0;
		$totalFiles = count($this->files);

		$this->debug("Building symbol table for $totalFiles files...");

		foreach ($this->files as $file) {
			if (time() - $startTime > $maxExecutionTime) {
				$this->info("Warning: Symbol table building timed out after {$maxExecutionTime} seconds. Processed {$processedFiles} files.");
				break;
			}

			if (!file_exists($file)) {
				continue;
			}

			try {
				$code = file_get_contents($file);
				$ast = $this->parser->parse($code);

				$visitor = new SymbolTableVisitor($file);
				$traverser = new NodeTraverser();
				$traverser->addVisitor($visitor);
				$traverser->traverse($ast);

				$this->symbolTable = array_merge($this->symbolTable, $visitor->getSymbols());
				$processedFiles++;

				// Progress reporting
				if ($processedFiles % 100 === 0) {
					$percent = round(($processedFiles / $totalFiles) * 100);
					$this->debug("Progress: $processedFiles/$totalFiles files processed ($percent%)");
				}
			} catch (Error $e) {
				// Skip files with parse errors
			}
		}

		$this->debug("Symbol table built: $processedFiles files processed");
	}

	/**
	 * Find symbol at specific file:line
	 */
	public function findSymbolAtLine( string $file, int $line ) {
		if ( ! file_exists( $file ) ) {
			return null;
		}

		try {
			$code = file_get_contents( $file );
			$ast  = $this->parser->parse( $code );

			$finder = new NodeFinder();
			$symbol = $finder->findFirst( $ast, function ( Node $node ) use ( $line ) {
				if ( $node instanceof Node\Stmt\Function_ ||
				     $node instanceof Node\Stmt\ClassMethod ) {
					$startLine = $node->getStartLine();
					$endLine   = $node->getEndLine();

					return $line >= $startLine && $line <= $endLine;
				}

				return false;
			} );

			if ( $symbol instanceof Node\Stmt\Function_ ) {
				return [ 'type' => 'function', 'name' => $symbol->name->toString() ];
			} elseif ( $symbol instanceof Node\Stmt\ClassMethod ) {
				$class = $this->findParentClass( $ast, $symbol );

				return [
					'type'  => 'method',
					'class' => $class ? $class->name->toString() : 'Unknown',
					'name'  => $symbol->name->toString()
				];
			}
		} catch ( Error $e ) {
			return null;
		}

		return null;
	}

	/**
	 * Build call graph for a function
	 */
	public function buildForFunction( string $functionName ) {
		$this->callGraph = [];
		$this->visited   = [];

		$symbol = $this->findSymbolDefinition( 'function', $functionName );
		if ( ! $symbol ) {
			// Function definition not found, search for references
			$references = $this->findFunctionReferences( $functionName );
			if ( ! empty( $references ) ) {
				return [
					'symbol' => $functionName,
					'trace' => $references
				];
			}
			return [ 'error' => "Function '$functionName' not found" ];
		}

		$this->analyzeFunction( $symbol['file'], $functionName );

		return $this->callGraph;
	}

	/**
	 * Build call graph for a method
	 */
	public function buildForMethod( string $className, string $methodName ) {
		$this->callGraph = [];
		$this->visited   = [];

		$symbol = $this->findSymbolDefinition( 'method', $methodName, $className );
		if ( ! $symbol ) {
			return [ 'error' => "Method '$className::$methodName' not found" ];
		}

		$this->analyzeMethod( $symbol['file'], $className, $methodName );

		return $this->callGraph;
	}

	/**
	 * Build call graph from file:line with selective scanning
	 */
	public function buildFromFileLine(string $file, int $line) {
		// First, find the symbol at the given line
		$symbol = $this->findSymbolAtLine($file, $line);
		if (!$symbol) {
			// Check for WordPress hooks
			$hook = $this->findWordPressHookAtLine($file, $line);
			if ($hook) {
				return $this->buildFromWordPressHook($file, $hook);
			}
			return ['error' => "No function/method found at $file:$line"];
		}

		// If selective scanning is enabled, find relevant files
		if ($this->useSelectiveScanning) {
			$relevantFiles = $this->scanner->findRelevantFiles($file, $symbol);

			$this->debug("Selective scanning found " . count($relevantFiles) . " relevant files for {$symbol['type']} " . 
				   ($symbol['type'] === 'method' ? "{$symbol['class']}::{$symbol['name']}" : $symbol['name']));

			// Build symbol table only for relevant files
			$this->files = $relevantFiles;
			$this->symbolTable = [];
			$this->buildSymbolTable();
		}

		// Continue with original logic
		if ($symbol['type'] === 'function') {
			$references = $this->findFunctionReferences($symbol['name']);
			if (!empty($references)) {
				return [
					'symbol' => $symbol['name'],
					'trace' => $references
				];
			}
			return $this->buildForFunction($symbol['name']);
		} else {
			// Handle method visibility
			$methodSymbol = $this->findSymbolDefinition('method', $symbol['name'], $symbol['class']);
			if ($methodSymbol && isset($methodSymbol['visibility'])) {
				if ($methodSymbol['visibility'] === 'private' || $methodSymbol['visibility'] === 'protected') {
					$callers = $this->findMethodCallers($symbol['class'], $symbol['name']);
					if (!empty($callers)) {
						$publicCallers = [];
						$hookTraces = [];

						foreach ($callers as $caller) {
							if ($caller['type'] === 'method' && 
								(!isset($caller['visibility']) || $caller['visibility'] === 'public')) {
								$hookRefs = $this->findMethodHookReferences($caller['class'], $caller['name']);
								if (!empty($hookRefs)) {
									$hookTraces = array_merge($hookTraces, $hookRefs);
								}

								// Enhanced: Trace this caller further up the chain
								$caller = $this->enhanceCallerWithTraces($caller);
								$publicCallers[] = $caller;
							} elseif ($caller['type'] === 'function') {
								$hookRefs = $this->findFunctionReferences($caller['name']);
								if (!empty($hookRefs)) {
									$hookTraces = array_merge($hookTraces, $hookRefs);
								}

								// Enhanced: Trace this caller further up the chain
								$caller = $this->enhanceCallerWithTraces($caller);
								$publicCallers[] = $caller;
							}
						}

						$instantiations = $this->findClassInstantiations($symbol['class']);

						// Enhanced: Trace instantiation chains further up
						$instantiations = $this->enhanceInstantiationsWithTraces($instantiations);

						if (!empty($hookTraces)) {
							$result = [
								'symbol' => $symbol['class'] . '::' . $symbol['name'],
								'visibility' => $methodSymbol['visibility'],
								'trace' => $hookTraces,
								'called_by' => $publicCallers
							];
							if (!empty($instantiations)) {
								$result['instantiated_by'] = $instantiations;
							}
							return $result;
						} else {
							$result = [
								'symbol' => $symbol['class'] . '::' . $symbol['name'],
								'visibility' => $methodSymbol['visibility'],
								'called_by' => $publicCallers,
								'note' => 'Method is ' . $methodSymbol['visibility'] . ' and cannot be hooked directly'
							];
							if (!empty($instantiations)) {
								$result['instantiated_by'] = $instantiations;
							}
							return $result;
						}
					} else {
						$instantiations = $this->findClassInstantiations($symbol['class']);
						$result = [
							'symbol' => $symbol['class'] . '::' . $symbol['name'],
							'visibility' => $methodSymbol['visibility'],
							'note' => 'Method is ' . $methodSymbol['visibility'] . ' and no callers found'
						];
						if (!empty($instantiations)) {
							$result['instantiated_by'] = $instantiations;
						}
						return $result;
					}
				}
			}

			// For public methods, prioritize forward call graph (original behavior)
			$forwardCallGraph = $this->buildForMethod($symbol['class'], $symbol['name']);

			// If the forward call graph has content, return it (maintains backward compatibility)
			if (!empty($forwardCallGraph) && !isset($forwardCallGraph['error'])) {
				return $forwardCallGraph;
			}

			// If no forward calls found, then check for callers/instantiations/hooks (new behavior)
			$callers = $this->findMethodCallers($symbol['class'], $symbol['name']);
			$instantiations = $this->findClassInstantiations($symbol['class']);
			$hookRefs = $this->findMethodHookReferences($symbol['class'], $symbol['name']);

			// If we found callers, instantiations, or hooks, return the new format
			if (!empty($callers) || !empty($instantiations) || !empty($hookRefs)) {
				$result = [
					'symbol' => $symbol['class'] . '::' . $symbol['name'],
					'visibility' => $methodSymbol['visibility'] ?? 'public'
				];

				if (!empty($hookRefs)) {
					$result['trace'] = $hookRefs;
				}

				if (!empty($callers)) {
					$result['called_by'] = $callers;
				}

				if (!empty($instantiations)) {
					$result['instantiated_by'] = $instantiations;
				}

				return $result;
			}

			// If nothing found, return the forward call graph anyway (even if empty)
			return $forwardCallGraph;
		}
	}

	/**
	 * Find WordPress hook at specific file:line
	 */
	public function findWordPressHookAtLine( string $file, int $line ) {
		if ( ! file_exists( $file ) ) {
			return null;
		}

		try {
			$code = file_get_contents( $file );
			$ast  = $this->parser->parse( $code );

			$finder = new NodeFinder();
			$hookCall = $finder->findFirst( $ast, function ( Node $node ) use ( $line ) {
				if ( $node instanceof Node\Expr\FuncCall && 
				     $node->name instanceof Node\Name &&
				     $node->getStartLine() <= $line && 
				     $node->getEndLine() >= $line ) {

					$funcName = $node->name->toString();
					return in_array( $funcName, [ 'add_action', 'add_filter', 'do_action', 'apply_filters' ] );
				}

				return false;
			} );

			if ( $hookCall ) {
				$funcName = $hookCall->name->toString();
				$hookData = [
					'type' => 'wp_hook',
					'hook_type' => $funcName,
					'line' => $hookCall->getStartLine()
				];

				// Extract hook name
				if ( isset( $hookCall->args[0] ) && $hookCall->args[0]->value instanceof Node\Scalar\String_ ) {
					$hookData['name'] = $hookCall->args[0]->value->value;
				}

				// For add_action/add_filter, extract callback
				if ( in_array( $funcName, [ 'add_action', 'add_filter' ] ) && isset( $hookCall->args[1] ) ) {
					$callback = $this->resolveCallbackFromNode( $hookCall->args[1]->value );
					if ( $callback ) {
						$hookData['callback'] = $callback;
					}
				}

				return $hookData;
			}
		} catch ( Error $e ) {
			return null;
		}

		return null;
	}

	/**
	 * Resolve callback from a node (similar to CallGraphVisitor::resolveCallback)
	 */
	private function resolveCallbackFromNode( $node ) {
		if ( $node instanceof Node\Scalar\String_ ) {
			return [
				'type' => 'function',
				'name' => $node->value,
				'line' => $node->getLine()
			];
		} elseif ( $node instanceof Node\Expr\Array_ && count( $node->items ) === 2 ) {
			// Array callback like [$this, 'methodName'] or ['ClassName', 'methodName']
			$class  = null;
			$method = null;

			if ( $node->items[0]->value instanceof Node\Expr\Variable &&
			     $node->items[0]->value->name === 'this' ) {
				$class = 'Unknown'; // We don't have context for $this here
			} elseif ( $node->items[0]->value instanceof Node\Scalar\String_ ) {
				$class = $node->items[0]->value->value;
			}

			if ( $node->items[1]->value instanceof Node\Scalar\String_ ) {
				$method = $node->items[1]->value->value;
			}

			if ( $class && $method ) {
				return [
					'type'  => 'method',
					'class' => $class,
					'name'  => $method,
					'line'  => $node->getLine()
				];
			}
		}

		return null;
	}

	/**
	 * Build call graph starting from a WordPress hook
	 */
	public function buildFromWordPressHook( string $file, array $hook ) {
		$this->callGraph = [];
		$this->visited   = [];

		$hookKey = "wp_hook:{$hook['hook_type']}:{$hook['name']}";
		$calls = [ $hook ];

		// If there's a callback, analyze it recursively
		if ( isset( $hook['callback'] ) ) {
			$callback = $hook['callback'];
			$calls[] = $callback;

			if ( $callback['type'] === 'function' ) {
				$this->analyzeFunction( $file, $callback['name'] );
			} elseif ( $callback['type'] === 'method' && isset( $callback['class'] ) ) {
				$this->analyzeMethod( $file, $callback['class'], $callback['name'] );
			}
		}

		$this->callGraph[ $hookKey ] = $calls;

		return $this->callGraph;
	}

	/**
	 * Find methods that call a specific method (for tracing protected/private methods)
	 */
	private function findMethodCallers( string $className, string $methodName ) {
		$callers = [];

		foreach ( $this->files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			try {
				$code = file_get_contents( $file );
				$ast  = $this->parser->parse( $code );

				$finder = new NodeFinder();

				// Find method calls that reference this method
				$methodCalls = $finder->find( $ast, function ( Node $node ) use ( $methodName ) {
					if ( $node instanceof Node\Expr\MethodCall && 
					     $node->name instanceof Node\Identifier &&
					     $node->name->toString() === $methodName ) {
						return true;
					} elseif ( $node instanceof Node\Expr\StaticCall &&
					           $node->name instanceof Node\Identifier &&
					           $node->name->toString() === $methodName ) {
						return true;
					}
					return false;
				} );

				foreach ( $methodCalls as $methodCall ) {
					// Find the containing method/function
					$containingMethod = $this->findContainingMethod( $ast, $methodCall );
					if ( $containingMethod ) {
						$callers[] = [
							'type' => $containingMethod['type'],
							'class' => $containingMethod['class'] ?? null,
							'name' => $containingMethod['name'],
							'file' => $file,
							'line' => $methodCall->getStartLine(),
							'visibility' => $containingMethod['visibility'] ?? 'public'
						];
					}
				}

			} catch ( Error $e ) {
				// Skip files with parse errors
			}
		}

		return $callers;
	}

	/**
	 * Find the containing method or function for a given node
	 */
	private function findContainingMethod( $ast, $targetNode ) {
		$finder = new NodeFinder();

		// Find all methods and functions
		$methods = $finder->find( $ast, function ( Node $node ) use ( $targetNode ) {
			if ( ( $node instanceof Node\Stmt\ClassMethod || $node instanceof Node\Stmt\Function_ ) &&
			     $targetNode->getStartLine() >= $node->getStartLine() &&
			     $targetNode->getEndLine() <= $node->getEndLine() ) {
				return true;
			}
			return false;
		} );

		// Find the most specific (innermost) containing method
		$containingMethod = null;
		$smallestRange = PHP_INT_MAX;

		foreach ( $methods as $method ) {
			$range = $method->getEndLine() - $method->getStartLine();
			if ( $range < $smallestRange ) {
				$smallestRange = $range;
				$containingMethod = $method;
			}
		}

		if ( $containingMethod ) {
			if ( $containingMethod instanceof Node\Stmt\ClassMethod ) {
				// Find the class this method belongs to
				$class = $this->findParentClass( $ast, $containingMethod );
				$visibility = 'public';
				if ( $containingMethod->isPrivate() ) {
					$visibility = 'private';
				} elseif ( $containingMethod->isProtected() ) {
					$visibility = 'protected';
				}

				return [
					'type' => 'method',
					'class' => $class ? $class->name->toString() : 'Unknown',
					'name' => $containingMethod->name->toString(),
					'visibility' => $visibility
				];
			} elseif ( $containingMethod instanceof Node\Stmt\Function_ ) {
				return [
					'type' => 'function',
					'name' => $containingMethod->name->toString()
				];
			}
		}

		return null;
	}

	/**
	 * Find class instantiations for a specific class
	 */
	private function findClassInstantiations( string $className, array $visitedClasses = [] ) {
		// Prevent infinite recursion by tracking visited classes
		if ( in_array( $className, $visitedClasses ) ) {
			return [];
		}

		$visitedClasses[] = $className;
		$instantiations = [];

		foreach ( $this->files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			try {
				$code = file_get_contents( $file );
				$ast  = $this->parser->parse( $code );

				$finder = new NodeFinder();

				// Find new ClassName() calls
				$newCalls = $finder->find( $ast, function ( Node $node ) use ( $className ) {
					if ( $node instanceof Node\Expr\New_ && 
					     $node->class instanceof Node\Name &&
					     $node->class->toString() === $className ) {
						return true;
					}
					return false;
				} );

				foreach ( $newCalls as $newCall ) {
					// Find the containing method/function
					$containingMethod = $this->findContainingMethod( $ast, $newCall );
					if ( $containingMethod ) {
						$instantiation = [
							'type' => $containingMethod['type'],
							'class' => $containingMethod['class'] ?? null,
							'name' => $containingMethod['name'],
							'file' => $file,
							'line' => $newCall->getStartLine(),
							'visibility' => $containingMethod['visibility'] ?? 'public'
						];

						// If this is a method, recursively find where this class is instantiated
						if ( $containingMethod['type'] === 'method' && $containingMethod['class'] ) {
							$parentInstantiations = $this->findClassInstantiations( $containingMethod['class'], $visitedClasses );
							if ( ! empty( $parentInstantiations ) ) {
								$instantiation['instantiated_by'] = $parentInstantiations;
							}
						}

						$instantiations[] = $instantiation;
					} else {
						// Global instantiation (not inside a method/function)
						$instantiation = [
							'type' => 'global',
							'name' => 'global scope',
							'file' => $file,
							'line' => $newCall->getStartLine()
						];
						$instantiations[] = $instantiation;
					}
				}

			} catch ( Error $e ) {
				// Skip files with parse errors
			}
		}

		return $instantiations;
	}

	/**
	 * Find WordPress hook references for a specific method
	 */
	private function findMethodHookReferences( string $className, string $methodName ) {
		$references = [];

		foreach ( $this->files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			try {
				$code = file_get_contents( $file );
				$ast  = $this->parser->parse( $code );

				$finder = new NodeFinder();

				// Find WordPress hooks that reference this method
				$hookCalls = $finder->find( $ast, function ( Node $node ) use ( $className, $methodName ) {
					if ( $node instanceof Node\Expr\FuncCall && 
					     $node->name instanceof Node\Name ) {

						$funcName = $node->name->toString();
						if ( in_array( $funcName, [ 'add_action', 'add_filter' ] ) ) {
							// Check if the callback references our method
							if ( isset( $node->args[1] ) ) {
								$callback = $this->resolveCallbackFromNode( $node->args[1]->value );
								if ( $callback && $callback['type'] === 'method' && 
								     isset( $callback['class'] ) && $callback['class'] === $className &&
								     $callback['name'] === $methodName ) {
									return true;
								}
							}
						}
					}
					return false;
				} );

				foreach ( $hookCalls as $hookCall ) {
					$funcName = $hookCall->name->toString();
					$hookData = [
						'type' => 'wp_hook',
						'hook_type' => $funcName,
						'file' => $file,
						'line' => $hookCall->getStartLine()
					];

					// Extract hook name
					if ( isset( $hookCall->args[0] ) && $hookCall->args[0]->value instanceof Node\Scalar\String_ ) {
						$hookData['hook_name'] = $hookCall->args[0]->value->value;
					}

					$references[] = $hookData;
				}

			} catch ( Error $e ) {
				// Skip files with parse errors
			}
		}

		return $references;
	}

	/**
	 * Find references to a function throughout the codebase
	 */
	private function findFunctionReferences( string $functionName ) {
		$references = [];

		foreach ( $this->files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			try {
				$code = file_get_contents( $file );
				$ast  = $this->parser->parse( $code );

				$finder = new NodeFinder();

				// Find WordPress hooks that reference this function
				$hookCalls = $finder->find( $ast, function ( Node $node ) use ( $functionName ) {
					if ( $node instanceof Node\Expr\FuncCall && 
					     $node->name instanceof Node\Name ) {

						$funcName = $node->name->toString();
						if ( in_array( $funcName, [ 'add_action', 'add_filter' ] ) ) {
							// Check if the callback references our function
							if ( isset( $node->args[1] ) ) {
								$callback = $this->resolveCallbackFromNode( $node->args[1]->value );
								if ( $callback && $callback['type'] === 'function' && $callback['name'] === $functionName ) {
									return true;
								}
							}
						}
					}
					return false;
				} );

				foreach ( $hookCalls as $hookCall ) {
					$funcName = $hookCall->name->toString();
					$hookData = [
						'type' => 'wp_hook',
						'hook_type' => $funcName,
						'file' => $file,
						'line' => $hookCall->getStartLine()
					];

					// Extract hook name
					if ( isset( $hookCall->args[0] ) && $hookCall->args[0]->value instanceof Node\Scalar\String_ ) {
						$hookData['hook_name'] = $hookCall->args[0]->value->value;
					}

					$references[] = $hookData;
				}

				// Find direct function calls
				$functionCalls = $finder->find( $ast, function ( Node $node ) use ( $functionName ) {
					return $node instanceof Node\Expr\FuncCall && 
					       $node->name instanceof Node\Name &&
					       $node->name->toString() === $functionName;
				} );

				foreach ( $functionCalls as $funcCall ) {
					$references[] = [
						'type' => 'function_call',
						'name' => $functionName,
						'file' => $file,
						'line' => $funcCall->getStartLine()
					];
				}

			} catch ( Error $e ) {
				// Skip files with parse errors
			}
		}

		return $references;
	}

	private function findSymbolDefinition( $type, $name, $class = null ) {
		foreach ( $this->symbolTable as $symbol ) {
			if ( $symbol['type'] === $type && $symbol['name'] === $name ) {
				if ( $type === 'method' && $symbol['class'] !== $class ) {
					continue;
				}

				return $symbol;
			}
		}

		return null;
	}

	private function analyzeFunction( $file, $functionName ) {
		$key = "function:$functionName";
		if ( isset( $this->visited[ $key ] ) ) {
			return;
		}
		$this->visited[ $key ] = true;

		try {
			$code = file_get_contents( $file );
			$ast  = $this->parser->parse( $code );

			$visitor   = new CallGraphVisitor( $this, $file, $functionName );
			$traverser = new NodeTraverser();
			$traverser->addVisitor( $visitor );
			$traverser->traverse( $ast );

			$calls = $visitor->getCalls();
			if ( ! empty( $calls ) ) {
				$this->callGraph[ $key ] = $calls;

				// Recursively analyze called functions
				foreach ( $calls as $call ) {
					if ( $call['type'] === 'function' ) {
						$this->analyzeFunction( $call['file'] ?? $file, $call['name'] );
					} elseif ( $call['type'] === 'method' && isset( $call['class'] ) ) {
						$this->analyzeMethod( $call['file'] ?? $file, $call['class'], $call['name'] );
					}
				}
			}
		} catch ( Error $e ) {
			// Skip on parse error
		}
	}

	private function analyzeMethod( $file, $className, $methodName ) {
		$key = "method:$className::$methodName";
		if ( isset( $this->visited[ $key ] ) ) {
			return;
		}
		$this->visited[ $key ] = true;

		try {
			$code = file_get_contents( $file );
			$ast  = $this->parser->parse( $code );

			$visitor   = new CallGraphVisitor( $this, $file, $methodName, $className );
			$traverser = new NodeTraverser();
			$traverser->addVisitor( $visitor );
			$traverser->traverse( $ast );

			$calls = $visitor->getCalls();
			if ( ! empty( $calls ) ) {
				$this->callGraph[ $key ] = $calls;

				// Recursively analyze called functions
				foreach ( $calls as $call ) {
					if ( $call['type'] === 'function' ) {
						$this->analyzeFunction( $call['file'] ?? $file, $call['name'] );
					} elseif ( $call['type'] === 'method' && isset( $call['class'] ) ) {
						$this->analyzeMethod( $call['file'] ?? $file, $call['class'], $call['name'] );
					}
				}
			}
		} catch ( Error $e ) {
			// Skip on parse error
		}
	}

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

	public function getSymbolTable() {
		return $this->symbolTable;
	}

	/**
	 * Enhanced: Trace a caller further up the call chain
	 */
	private function enhanceCallerWithTraces($caller) {
		if ($caller['type'] === 'method') {
			// Find where this public method is called from
			$callerCallers = $this->findMethodCallers($caller['class'], $caller['name']);
			if (!empty($callerCallers)) {
				$caller['called_by'] = $callerCallers;

				// Also find hooks for these callers
				$callerHooks = [];
				foreach ($callerCallers as $cc) {
					if ($cc['type'] === 'method') {
						$ccHooks = $this->findMethodHookReferences($cc['class'], $cc['name']);
						if (!empty($ccHooks)) {
							$callerHooks = array_merge($callerHooks, $ccHooks);
						}
					} elseif ($cc['type'] === 'function') {
						$ccHooks = $this->findFunctionReferences($cc['name']);
						if (!empty($ccHooks)) {
							$callerHooks = array_merge($callerHooks, $ccHooks);
						}
					}
				}
				if (!empty($callerHooks)) {
					$caller['called_by_hooks'] = $callerHooks;
				}
			}

			// Find hooks for this method itself
			$methodHooks = $this->findMethodHookReferences($caller['class'], $caller['name']);
			if (!empty($methodHooks)) {
				$caller['hooks'] = $methodHooks;
			}
		} elseif ($caller['type'] === 'function') {
			// Find hooks for this function
			$functionHooks = $this->findFunctionReferences($caller['name']);
			if (!empty($functionHooks)) {
				$caller['hooks'] = $functionHooks;
			}
		}

		return $caller;
	}

	/**
	 * Enhanced: Trace instantiation chains further up
	 */
	private function enhanceInstantiationsWithTraces($instantiations) {
		foreach ($instantiations as &$inst) {
			if ($inst['type'] === 'method') {
				// Find where this instantiating method is called
				$instCallers = $this->findMethodCallers($inst['class'], $inst['name']);
				if (!empty($instCallers)) {
					$inst['called_by'] = $instCallers;
				}

				// Find hooks for this method
				$instHooks = $this->findMethodHookReferences($inst['class'], $inst['name']);
				if (!empty($instHooks)) {
					$inst['hooks'] = $instHooks;
				}
			} elseif ($inst['type'] === 'function') {
				// Find hooks for this function
				$instHooks = $this->findFunctionReferences($inst['name']);
				if (!empty($instHooks)) {
					$inst['hooks'] = $instHooks;
				}
			}
		}

		return $instantiations;
	}
}

class SymbolTableVisitor extends NodeVisitorAbstract {
	private $symbols = [];
	private $currentFile;
	private $currentClass = null;

	public function __construct( $file ) {
		$this->currentFile = $file;
	}

	public function enterNode( Node $node ) {
		if ( $node instanceof Node\Stmt\Class_ ) {
			// Handle anonymous classes or classes with null names
			if ( $node->name !== null ) {
				$this->currentClass = $node->name->toString();
			}
		} elseif ( $node instanceof Node\Stmt\Function_ ) {
			// Handle functions with null names
			if ( $node->name !== null ) {
				$this->symbols[] = [
					'type' => 'function',
					'name' => $node->name->toString(),
					'file' => $this->currentFile,
					'line' => $node->getStartLine()
				];
			}
		} elseif ( $node instanceof Node\Stmt\ClassMethod ) {
			// Handle methods with null names
			if ( $node->name !== null ) {
				// Determine method visibility
				$visibility = 'public'; // Default
				if ( $node->isPrivate() ) {
					$visibility = 'private';
				} elseif ( $node->isProtected() ) {
					$visibility = 'protected';
				}

				$this->symbols[] = [
					'type'       => 'method',
					'class'      => $this->currentClass,
					'name'       => $node->name->toString(),
					'file'       => $this->currentFile,
					'line'       => $node->getStartLine(),
					'visibility' => $visibility,
					'static'     => $node->isStatic()
				];
			}
		}
	}

	public function leaveNode( Node $node ) {
		if ( $node instanceof Node\Stmt\Class_ ) {
			$this->currentClass = null;
		}
	}

	public function getSymbols() {
		return $this->symbols;
	}
}

class CallGraphVisitor extends NodeVisitorAbstract {
	private $calls = [];
	private $builder;
	private $currentFile;
	private $targetFunction;
	private $targetClass;
	private $inTarget = false;

	public function __construct( CallGraphBuilder $builder, $file, $targetFunction, $targetClass = null ) {
		$this->builder        = $builder;
		$this->currentFile    = $file;
		$this->targetFunction = $targetFunction;
		$this->targetClass    = $targetClass;
	}

	public function enterNode( Node $node ) {
		// Check if we're entering our target function/method
		if ( $node instanceof Node\Stmt\Function_ && $node->name !== null && $node->name->toString() === $this->targetFunction && ! $this->targetClass ) {
			$this->inTarget = true;
		} elseif ( $node instanceof Node\Stmt\ClassMethod && $node->name !== null && $node->name->toString() === $this->targetFunction && $this->targetClass ) {
			$class = $this->findParentClass( $node );
			if ( $class === $this->targetClass ) {
				$this->inTarget = true;
			}
		}

		// Collect calls if we're in the target function/method
		if ( $this->inTarget ) {
			if ( $node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name ) {
				$funcName = $node->name->toString();

				// Check for WordPress hooks
				if ( in_array( $funcName, [ 'do_action', 'apply_filters', 'add_action', 'add_filter' ] ) ) {
					$this->handleWordPressHook( $node, $funcName );
				} else {
					$this->calls[] = [
						'type' => 'function',
						'name' => $funcName,
						'line' => $node->getLine()
					];
				}
			} elseif ( $node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier ) {
				$methodName = $node->name->toString();
				$className  = $this->resolveClassName( $node->var );

				$this->calls[] = [
					'type'  => 'method',
					'class' => $className ?: 'Unknown',
					'name'  => $methodName,
					'line'  => $node->getLine()
				];
			} elseif ( $node instanceof Node\Expr\StaticCall &&
			           $node->class instanceof Node\Name &&
			           $node->name instanceof Node\Identifier ) {
				$this->calls[] = [
					'type'   => 'method',
					'class'  => $node->class->toString(),
					'name'   => $node->name->toString(),
					'static' => true,
					'line'   => $node->getLine()
				];
			}
		}
	}

	public function leaveNode( Node $node ) {
		if ( ( $node instanceof Node\Stmt\Function_ && $node->name !== null && $node->name->toString() === $this->targetFunction && ! $this->targetClass ) ||
		     ( $node instanceof Node\Stmt\ClassMethod && $node->name !== null && $node->name->toString() === $this->targetFunction && $this->targetClass ) ) {
			$this->inTarget = false;
		}
	}

	private function handleWordPressHook( $node, $hookType ) {
		if ( ! isset( $node->args[0] ) ) {
			return;
		}

		$hookName = null;
		if ( $node->args[0]->value instanceof Node\Scalar\String_ ) {
			$hookName = $node->args[0]->value->value;
		}

		if ( $hookName ) {
			$this->calls[] = [
				'type'      => 'wp_hook',
				'hook_type' => $hookType,
				'name'      => $hookName,
				'line'      => $node->getLine()
			];

			// For add_action/add_filter, also track the callback
			if ( in_array( $hookType, [ 'add_action', 'add_filter' ] ) && isset( $node->args[1] ) ) {
				$callback = $this->resolveCallback( $node->args[1]->value );
				if ( $callback ) {
					$this->calls[] = $callback;
				}
			}
		}
	}

	private function resolveCallback( $node ) {
		if ( $node instanceof Node\Scalar\String_ ) {
			return [
				'type' => 'function',
				'name' => $node->value,
				'line' => $node->getLine()
			];
		} elseif ( $node instanceof Node\Expr\Array_ && count( $node->items ) === 2 ) {
			// Array callback like [$this, 'methodName'] or ['ClassName', 'methodName']
			$class  = null;
			$method = null;

			if ( $node->items[0]->value instanceof Node\Expr\Variable &&
			     $node->items[0]->value->name === 'this' ) {
				$class = $this->targetClass ?: 'Unknown';
			} elseif ( $node->items[0]->value instanceof Node\Scalar\String_ ) {
				$class = $node->items[0]->value->value;
			}

			if ( $node->items[1]->value instanceof Node\Scalar\String_ ) {
				$method = $node->items[1]->value->value;
			}

			if ( $class && $method ) {
				return [
					'type'  => 'method',
					'class' => $class,
					'name'  => $method,
					'line'  => $node->getLine()
				];
			}
		}

		return null;
	}

	private function resolveClassName( $node ) {
		if ( $node instanceof Node\Expr\Variable ) {
			if ( $node->name === 'this' ) {
				return $this->targetClass;
			}
			// Could implement more sophisticated type inference here
		}

		return null;
	}

	private function findParentClass( $node ) {
		// This is a simplified version - in real implementation,
		// you'd need to traverse up the AST to find the parent class
		return $this->targetClass;
	}

	public function getCalls() {
		return $this->calls;
	}
}

// CLI interface
if ( php_sapi_name() === 'cli' && isset( $argv[0] ) && basename( $argv[0] ) === basename( __FILE__ ) ) {
	$options = getopt( 'f:l:m:c:d:', [ 'function:', 'line:', 'method:', 'class:', 'directory:' ] );

	if ( empty( $options ) ) {
		echo "Usage:\n";
		echo "  php callgraph.php -f <function_name> [-d <directory>]\n";
		echo "  php callgraph.php -c <class_name> -m <method_name> [-d <directory>]\n";
		echo "  php callgraph.php -f <file> -l <line> [-d <directory>]\n";
		echo "\nOptions:\n";
		echo "  -f, --function   Function name or file path\n";
		echo "  -l, --line       Line number (use with -f for file path)\n";
		echo "  -c, --class      Class name\n";
		echo "  -m, --method     Method name\n";
		echo "  -d, --directory  Directory to scan for PHP files (default: current directory)\n";
		exit( 1 );
	}

	// Determine directory to scan
	$directory = $options['d'] ?? $options['directory'] ?? '.';

	// Find all PHP files
	$files    = [];
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $directory ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $iterator as $file ) {
		if ( $file->isFile() && $file->getExtension() === 'php' ) {
			$files[] = $file->getRealPath();
		}
	}

	$builder = new CallGraphBuilder( $files );

	// Determine what to analyze
	if ( isset( $options['l'] ) || isset( $options['line'] ) ) {
		// File:line mode
		$file = $options['f'] ?? $options['function'] ?? '';
		$line = intval( $options['l'] ?? $options['line'] ?? 0 );

		if ( ! file_exists( $file ) ) {
			echo json_encode( [ 'error' => "File not found: $file" ] ) . "\n";
			exit( 1 );
		}

		$result = $builder->buildFromFileLine( $file, $line );
	} elseif ( ( isset( $options['c'] ) || isset( $options['class'] ) ) &&
	           ( isset( $options['m'] ) || isset( $options['method'] ) ) ) {
		// Method mode
		$class  = $options['c'] ?? $options['class'] ?? '';
		$method = $options['m'] ?? $options['method'] ?? '';
		$result = $builder->buildForMethod( $class, $method );
	} elseif ( isset( $options['f'] ) || isset( $options['function'] ) ) {
		// Function mode
		$function = $options['f'] ?? $options['function'] ?? '';
		$result   = $builder->buildForFunction( $function );
	} else {
		echo json_encode( [ 'error' => 'Invalid options provided' ] ) . "\n";
		exit( 1 );
	}

	// Output JSON
	echo json_encode( $result, JSON_PRETTY_PRINT ) . "\n";
}
