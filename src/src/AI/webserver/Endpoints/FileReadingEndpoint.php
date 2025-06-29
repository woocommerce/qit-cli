<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\Lib\ToolRegistry;
use QIT_AI_Webserver\Lib\ExtractPathResolver;

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
	 * @return void Outputs JSON response
	 */
	public function handle( array $input ): void {
		$this->log_info( 'Starting file reading endpoint', [
			'input_keys'       => array_keys( $input ),
			'has_file_path'    => isset( $input['file_path'] ),
			'has_extract_path' => isset( $input['extract_path'] )
		] );

		// Parse the input
		$params = json_decode( $input['prompt'], true );
		if ( ! isset( $params['file_path'] ) || empty( $params['file_path'] ) ) {
			$this->log_error( 'No file path provided for reading' );
			http_response_code( 400 );
			NodeResponse::error( 'Missing file_path parameter' );

			return;
		}

		$filePath = $params['file_path'];

		// Use centralized path resolution
		try {
			$extractPath = ExtractPathResolver::resolve( $params );
			$this->log_info( 'Extract path resolved for file reading', [ 'extract_path' => $extractPath ] );
		} catch ( Exception $e ) {
			$this->log_error( 'Path resolution failed for file reading', [
				'error'       => $e->getMessage(),
				'file_path'   => $filePath,
				'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $params )
			] );
			http_response_code( 400 );
			NodeResponse::error( $e->getMessage() );

			return;
		}

		// SECURITY: Prevent directory traversal attacks
		if ( strpos( $filePath, '..' ) !== false ) {
			$this->log_error( 'Directory traversal attempt detected in file_path', [
				'file_path' => $filePath
			] );
			http_response_code( 400 );
			NodeResponse::error( 'Directory traversal sequences (..) are not allowed in file_path.' );

			return;
		}

		// SECURITY: Reject any path containing null bytes
		if ( strpos( $filePath, "\0" ) !== false ) {
			$this->log_error( 'Null byte injection attempt detected in file_path', [
				'file_path' => $filePath
			] );
			http_response_code( 400 );
			NodeResponse::error( 'Null bytes are not allowed in file_path.' );

			return;
		}

		try {
			$this->log_info( 'Reading file content', [
				'file_path'    => $filePath,
				'extract_path' => $extractPath
			] );

			// Initialize ToolRegistry with the extract path as work directory
			$registry = new ToolRegistry( $extractPath );

			// Use the read_file tool to read the file content
			$result = $registry->execute_tool( 'read_file', [
				'path' => $filePath
			] );

			if ( isset( $result['error'] ) ) {
				$this->log_error( 'Failed to read file', [
					'file_path' => $filePath,
					'error'     => $result['error']
				] );

				http_response_code( 404 );
				NodeResponse::error( 'File reading failed: ' . $result['error'] );

				return;
			}

			$this->log_info( 'File read successfully', [
				'file_path'    => $filePath,
				'content_size' => strlen( $result['content'] ?? '' ),
				'total_lines'  => $result['total_lines'] ?? 0
			] );

			// Return clean response
			NodeResponse::success( [
				'file_content' => $result['content'] ?? '',
				'file_lines'   => $result['total_lines'] ?? 0,
				'file_size'    => strlen( $result['content'] ?? '' ),
				'file_path'    => $filePath,
				'extract_path' => $extractPath
			], 'file_reading' );

		} catch ( Exception $e ) {
			$this->log_error( 'File reading failed: ' . $e->getMessage(), [
				'file_path'    => $filePath,
				'extract_path' => $extractPath
			] );

			NodeResponse::error( 'File reading failed', 500, [ 'message' => $e->getMessage() ] );
		}
	}
}
