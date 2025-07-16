<?php

namespace QIT_AI_Webserver\Tools;

use Exception;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use QIT_AI_Webserver\Lib\DebugLogger;
use QIT_AI_Webserver\Lib\FilePathResolver;
use QIT_AI_Webserver\ToolContext;

abstract class BaseTool {
	protected FilePathResolver $file_path_resolver;
	protected string $work_dir;
	protected ?ToolContext $context = null;

	public function __construct(
		string $work_directory,
		string $sut_directory = '',
		?ToolContext $context = null
	) {
		$this->work_dir           = rtrim( $work_directory, '/\\' );
		$this->file_path_resolver = new FilePathResolver( $this->work_dir, $sut_directory );
		$this->context            = $context;
	}

	/**
	 * Get the tool name
	 */
	abstract public function get_name(): string;

	/**
	 * Get the tool description
	 */
	abstract public function get_description(): string;

	/**
	 * Get the FunctionInfo object for LLPhant
	 */
	abstract public function get_function_info(): FunctionInfo;

	/** Canonical, safe, *relative* path – throws if invalid */
	protected function safe_path( string $user_path ): string {
		return $this->file_path_resolver->canon_relative( $user_path );
	}

	protected function base_description( string $core, array $examples = [] ): string {
		if ( $this->context === null ) {
			return $core;
		}
		$deps       = array_map( fn( $d ) => $d['slug'], $this->context->deps );
		$macro_note = sprintf(
			"\n\nPath placeholders:\n• __WP_ROOT__ = %s\n• __SUT_DIR__ = %s\n• __DEP_[slug]__ where slug ∈ {%s}",
			$this->context->wpRoot,
			$this->context->sutDir,
			implode( ', ', $deps ) ?: '–'
		);

		// Add examples if provided
		if ( ! empty( $examples ) ) {
			$macro_note .= "\n\nExamples:";
			foreach ( $examples as $example ) {
				$macro_note .= "\n• " . $example;
			}
		}

		return $core . $macro_note;
	}

	/**
	 * Child classes must implement the "real" work here.
	 * On success return ANY serialisable value.
	 *
	 * @throws \Throwable To trigger error envelope.
	 */
	abstract protected function do( array $params );

	public function execute( array $params ): array {
		try {
			// Resolve placeholders
			$resolved_params = $this->resolve_macros_in_params( $params );
			$data            = $this->do( $resolved_params );

			return [
				'success'   => true,
				'data'      => $data,
				'truncated' => $data['truncated'] ?? false,
				'error'     => null,
				'debug'     => [],
			];
		} catch ( Exception | \Throwable $e ) {
			DebugLogger::log( static::class . '_error', [
				'args'  => $params,
				'error' => $e->getMessage(),
				'tree'  => DebugLogger::dir_tree( $this->work_dir, 2, 150 ),
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
	protected function resolve_macros_in_params( array $params ): array {
		// Common path parameters that might contain macros
		$path_params = [ 'file', 'directory_or_file', 'path', 'directory' ];

		foreach ( $path_params as $param_name ) {
			if ( isset( $params[ $param_name ] ) && is_string( $params[ $param_name ] ) ) {
				$params[ $param_name ] = $this->resolve_macro_path( $params[ $param_name ] );
			}
		}

		return $params;
	}

	/**
	 * Resolve macro path to regular relative path
	 */
	protected function resolve_macro_path( string $user_path ): string {
		if ( $this->context === null ) {
			return $user_path;
		}

		// Much simpler pattern matching with underscores
		$user_path = trim( $user_path );

		// Handle __WP_ROOT__
		if ( strpos( $user_path, '__WP_ROOT__' ) === 0 ) {
			$remainder = substr( $user_path, 11 ); // length of '__WP_ROOT__'
			return ltrim( $remainder, '/' );
		}

		// Handle __SUT_DIR__
		if ( strpos( $user_path, '__SUT_DIR__' ) === 0 ) {
			$remainder    = substr( $user_path, 11 ); // length of '__SUT_DIR__'
			$sut_relative = $this->file_path_resolver->to_relative( $this->context->sutDir );

			if ( empty( $remainder ) || $remainder === '/' ) {
				return $sut_relative;
			} else {
				return $sut_relative . '/' . ltrim( $remainder, '/' );
			}
		}

		// Handle __DEP_[slug]__
		if ( preg_match( '/^__DEP_\[([^\]]+)\]__(.*)$/', $user_path, $matches ) ) {
			$dep_slug = $matches[1];
			$dep_path = $matches[2];

			foreach ( $this->context->deps as $dep ) {
				if ( $dep['slug'] === $dep_slug ) {
					$base_path = $dep['type'] === 'plugin'
						? "wp-content/plugins/{$dep_slug}"
						: "wp-content/themes/{$dep_slug}";

					if ( empty( $dep_path ) || $dep_path === '/' ) {
						return $base_path;
					} else {
						return $base_path . '/' . ltrim( $dep_path, '/' );
					}
				}
			}
			throw new \InvalidArgumentException( "Unknown dependency: {$dep_slug}" );
		}

		// No placeholder found, return as-is
		return $user_path;
	}
}
