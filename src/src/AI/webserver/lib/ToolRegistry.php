<?php

namespace QIT_CLI\AI\WebServer;

use Exception;

/**
 * Tool Registry for AI function calling - embedded to avoid file dependencies
 */
class ToolRegistry {
	private array $tools = [];
	private string $work_directory;

	public function __construct( string $work_directory = '' ) {
		$this->work_directory = $work_directory;
		$this->register_default_tools();
	}

	public function set_work_directory( string $work_directory ): void {
		$this->work_directory = $work_directory;
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

			// Security: Ensure path is within work directory
			if ( ! $this->work_directory ) {
				return [ 'error' => 'Work directory not set' ];
			}

			$full_path     = realpath( $this->work_directory . '/' . ltrim( $path, '/' ) );
			$real_work_dir = realpath( $this->work_directory );

			if ( ! $full_path || strpos( $full_path, $real_work_dir ) !== 0 ) {
				return [ 'error' => 'Invalid path or file outside work directory' ];
			}

			if ( ! file_exists( $full_path ) ) {
				return [ 'error' => 'File not found' ];
			}

			$content = file_get_contents( $full_path );
			if ( $content === false ) {
				return [ 'error' => 'Failed to read file' ];
			}

			// Apply line filtering if specified
			if ( $start_line !== null || $end_line !== null ) {
				$lines          = explode( "\n", $content );
				$start          = max( 0, ( $start_line ?? 1 ) - 1 );
				$end            = min( count( $lines ), $end_line ?? count( $lines ) );
				$selected_lines = array_slice( $lines, $start, $end - $start );
				$content        = implode( "\n", $selected_lines );
			}

			return [
				'content'    => $content,
				'path'       => $path,
				'lines_read' => [ $start_line ?? 1, $end_line ?? substr_count( $content, "\n" ) + 1 ]
			];
		} );

		// Tool 2: Search for pattern in PHP files
		$this->register_tool( 'search_pattern', function ( $params ) {
			$pattern     = $params['pattern'] ?? null;
			$max_results = $params['max_results'] ?? 50;

			if ( ! $pattern ) {
				return [ 'error' => 'Pattern is required' ];
			}

			if ( ! $this->work_directory ) {
				return [ 'error' => 'Work directory not set' ];
			}

			// Use grep to search for pattern
			$cmd = sprintf(
				'grep -rin %s %s --include="*.php" 2>/dev/null | head -n %d',
				escapeshellarg( $pattern ),
				escapeshellarg( $this->work_directory ),
				$max_results
			);

			exec( $cmd, $output, $return_code );

			$results = [];
			foreach ( $output as $line ) {
				// Parse grep output: filename:line_number:matched_line
				if ( preg_match( '/^(.+?):(\d+):(.*)$/', $line, $matches ) ) {
					$results[] = [
						'file'    => str_replace( $this->work_directory . '/', '', $matches[1] ),
						'line'    => (int) $matches[2],
						'content' => trim( $matches[3] )
					];
				}
			}

			return [
				'results'   => $results,
				'count'     => count( $results ),
				'pattern'   => $pattern,
				'truncated' => count( $results ) >= $max_results
			];
		} );

		// Tool 3: List PHP files
		$this->register_tool( 'list_files', function ( $params ) {
			$directory = $params['directory'] ?? '.';

			if ( ! $this->work_directory ) {
				return [ 'error' => 'Work directory not set' ];
			}

			$search_dir    = realpath( $this->work_directory . '/' . ltrim( $directory, '/' ) );
			$real_work_dir = realpath( $this->work_directory );

			if ( ! $search_dir || strpos( $search_dir, $real_work_dir ) !== 0 ) {
				return [ 'error' => 'Invalid directory' ];
			}

			// Use find to list PHP files
			$cmd = sprintf(
				'find %s -name "*.php" -type f 2>/dev/null | head -n 500',
				escapeshellarg( $search_dir )
			);

			exec( $cmd, $output, $return_code );

			$files = [];
			foreach ( $output as $file ) {
				$files[] = str_replace( $this->work_directory . '/', '', $file );
			}

			return [
				'files'     => $files,
				'count'     => count( $files ),
				'directory' => $directory
			];
		} );
	}
}