<?php

namespace QIT_AI_Webserver\Tools;

use Exception;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use QIT_AI_Webserver\Lib\DebugLogger;
use QIT_AI_Webserver\Lib\FilePathResolver;
use QIT_AI_Webserver\ToolContext;

abstract class BaseTool {
	protected FilePathResolver $file_path_resolver;
	protected string $workDir;
	protected ?ToolContext $context = null;

	public function __construct(
		string $workDirectory,
		string $sutDirectory = '',
		?ToolContext $context = null
	) {
		$this->workDir            = rtrim( $workDirectory, '/\\' );
		$this->file_path_resolver = new FilePathResolver( $this->workDir, $sutDirectory );
		$this->context            = $context;
	}

	/**
	 * Get the tool name
	 */
	abstract public function getName(): string;

	/**
	 * Get the tool description
	 */
	abstract public function getDescription(): string;

	/**
	 * Get the FunctionInfo object for LLPhant
	 */
	abstract public function getFunctionInfo(): FunctionInfo;

	/** Canonical, safe, *relative* path – throws if invalid */
	protected function safePath( string $userPath ): string {
		return $this->file_path_resolver->canonRelative( $userPath );
	}

	protected function baseDescription(string $core, array $examples = []): string
	{
		if ($this->context === null) {
			return $core;
		}
		$deps = array_map(fn($d) => $d['slug'], $this->context->deps);
		$macroNote = sprintf(
			"\n\nPath placeholders:\n• __WP_ROOT__ = %s\n• __SUT_DIR__ = %s\n• __DEP_[slug]__ where slug ∈ {%s}",
			$this->context->wpRoot,
			$this->context->sutDir,
			implode(', ', $deps) ?: '–'
		);

		// Add examples if provided
		if (!empty($examples)) {
			$macroNote .= "\n\nExamples:";
			foreach ($examples as $example) {
				$macroNote .= "\n• " . $example;
			}
		}

		return $core . $macroNote;
	}

	/** ----------------------------------------------------------------
	 *  Child classes must implement the "real" work here.
	 *  On success return ANY serialisable value.
	 * @throws \Throwable to trigger error envelope
	 *-----------------------------------------------------------------*/
	abstract protected function do( array $params );

	public function execute( array $params ): array {
		try {
			// Resolve placeholders
			$resolvedParams = $this->resolveMacrosInParams($params);
			$data = $this->do( $resolvedParams );

			return [
				'success'   => true,
				'data'      => $data,
				'truncated' => $data['truncated'] ?? false,
				'error'     => null,
				'debug'     => [],
			];
		} catch ( Exception|\Throwable $e ) {
			DebugLogger::log( static::class . '_error', [
				'args'  => $params,
				'error' => $e->getMessage(),
				'tree'  => DebugLogger::dirTree( $this->workDir, 2, 150 ),
			] );

			return [
				'success'   => false,
				'data'      => null,
				'truncated' => false,
				'error'     => $e->getMessage(),
				'debug'     => [ 'args' => $params ],
			];
		}
	}

	/**
	 * Resolve macros in path-related parameters
	 */
	protected function resolveMacrosInParams(array $params): array {
		// Common path parameters that might contain macros
		$pathParams = ['file', 'directory_or_file', 'path', 'directory'];

		foreach ($pathParams as $paramName) {
			if (isset($params[$paramName]) && is_string($params[$paramName])) {
				$params[$paramName] = $this->resolveMacroPath($params[$paramName]);
			}
		}

		return $params;
	}

	/**
	 * Resolve macro path to regular relative path
	 */
	protected function resolveMacroPath(string $userPath): string {
		if ($this->context === null) {
			return $userPath;
		}

		// Much simpler pattern matching with underscores
		$userPath = trim($userPath);

		// Handle __WP_ROOT__
		if (strpos($userPath, '__WP_ROOT__') === 0) {
			$remainder = substr($userPath, 11); // length of '__WP_ROOT__'
			return ltrim($remainder, '/');
		}

		// Handle __SUT_DIR__
		if (strpos($userPath, '__SUT_DIR__') === 0) {
			$remainder = substr($userPath, 11); // length of '__SUT_DIR__'
			$sutRelative = $this->file_path_resolver->toRelative($this->context->sutDir);

			if (empty($remainder) || $remainder === '/') {
				return $sutRelative;
			} else {
				return $sutRelative . '/' . ltrim($remainder, '/');
			}
		}

		// Handle __DEP_[slug]__
		if (preg_match('/^__DEP_\[([^\]]+)\]__(.*)$/', $userPath, $matches)) {
			$depSlug = $matches[1];
			$depPath = $matches[2];

			foreach ($this->context->deps as $dep) {
				if ($dep['slug'] === $depSlug) {
					$basePath = $dep['type'] === 'plugin' 
						? "wp-content/plugins/{$depSlug}"
						: "wp-content/themes/{$depSlug}";

					if (empty($depPath) || $depPath === '/') {
						return $basePath;
					} else {
						return $basePath . '/' . ltrim($depPath, '/');
					}
				}
			}
			throw new \InvalidArgumentException("Unknown dependency: {$depSlug}");
		}

		// No placeholder found, return as-is
		return $userPath;
	}
}
