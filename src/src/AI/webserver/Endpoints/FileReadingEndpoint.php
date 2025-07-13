<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\ToolRegistry;

/**
 * File Reading Endpoint
 *
 * Handles secure file reading operations within extracted WordPress plugin/theme directories.
 */
class FileReadingEndpoint extends AbstractEndpoint {
	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/read-file';
	}

	/**
	 * Handle file reading request
	 *
	 * @param array $input Request input data
	 *
	 * @return string JSON response
	 */
	public function handle( array $input ): string {
		$this->log_info( 'Starting file reading endpoint', [
			'input_keys'       => array_keys( $input ),
			'has_file'         => isset( $input['file'] ),
			'has_extract_path' => isset( $input['extract_path'] ),
		] );

		// Access parameters directly from input (consistent with Actions)
		if ( ! isset( $input['file'] ) || empty( $input['file'] ) ) {
			$this->log_error( 'No file provided for reading' );
			http_response_code( 400 );
			return NodeResponse::error( 'Missing file parameter' );
		}

		$filePath = $input['file'];

		// Use centralized path resolution
		try {
			$extractPath = ExtractPathResolver::resolve( $input );
			$this->log_info( 'Extract path resolved for file reading', [ 'extract_path' => $extractPath ] );
		} catch ( Exception $e ) {
			$this->log_error( 'Path resolution failed for file reading', [
				'error'       => $e->getMessage(),
				'file'        => $filePath,
				'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input ),
			] );
			http_response_code( 400 );
			return NodeResponse::error( $e->getMessage() );
		}

		// SECURITY: Prevent directory traversal attacks
		if ( strpos( $filePath, '..' ) !== false ) {
			$this->log_error( 'Directory traversal attempt detected in file', [
				'file' => $filePath,
			] );
			http_response_code( 400 );
			return NodeResponse::error( 'Directory traversal sequences (..) are not allowed in file.' );
		}

		// SECURITY: Reject any path containing null bytes
		if ( strpos( $filePath, "\0" ) !== false ) {
			$this->log_error( 'Null byte injection attempt detected in file', [
				'file' => $filePath,
			] );
			http_response_code( 400 );
			return NodeResponse::error( 'Null bytes are not allowed in file.' );
		}

		try {
			$this->log_info( 'Reading file content', [
				'file'         => $filePath,
				'extract_path' => $extractPath,
			] );

			// Initialize ToolRegistry with the extract path as work directory
			$registry = new ToolRegistry( $extractPath );

			// Use the read_file tool to read the file content
			$result = $registry->execute_tool( 'read_file', [
				'file' => $filePath,
			] );

			if ( ! $result['success'] ) {
				$this->log_error( 'Failed to read file', [
					'file'  => $filePath,
					'error' => $result['error'],
				] );

				http_response_code( 404 );
				return NodeResponse::error( 'File reading failed: ' . $result['error'] );
			}

			// FileReadingEndpoint::handle()  – right before NodeResponse::success()
			$content  = $result['data']['content'] ?? '';
			$lines    = $result['data']['total_lines'] ?? 0;
			$rawLines = explode( "\n", $content );
			$numbered = [];
			foreach ( $rawLines as $idx => $l ) {
				// human-friendly 1-based index, 6-char wide
				$numbered[] = str_pad( $idx + 1, 6, ' ', STR_PAD_LEFT ) . '│ ' . $l;
			}

			$this->log_info( 'File read successfully', [
				'file'         => $filePath,
				'content_size' => strlen( $content ),
				'total_lines'  => $lines,
			] );

			// Return clean response
			return NodeResponse::success( [
				'file_content'              => $content,
				'file_lines'                => $lines,
				'file_size'                 => strlen( $content ),
				'content_with_line_numbers' => implode( "\n", $numbered ),
				'file'                      => $filePath,
				'extract_path'              => $extractPath,
			], 'file_reading' );

		} catch ( Exception $e ) {
			$this->log_error( 'File reading failed: ' . $e->getMessage(), [
				'file'         => $filePath,
				'extract_path' => $extractPath,
			] );

			return NodeResponse::error( 'File reading failed', 500, [ 'message' => $e->getMessage() ] );
		}
	}
}
