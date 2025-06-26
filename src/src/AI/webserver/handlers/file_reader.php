<?php
/**
 * File Reading Handler
 */

require_once __DIR__ . '/../NodeResponse.php';
require_once __DIR__ . '/../lib/ToolRegistry.php';
require_once __DIR__ . '/../lib/FilePathResolver.php';
require_once __DIR__ . '/../lib/ExtractPathResolver.php';

use QIT_CLI\AI\WebServer\FilePathResolver;
use QIT_CLI\AI\WebServer\ToolRegistry;
use QIT_CLI\AI\WebServer\ExtractPathResolver;

function handle_file_reading( $input ) {
    log_info( 'Starting file reading handler', [
        'input_keys'     => array_keys( $input ),
        'has_file_path'  => isset( $input['file_path'] ),
        'has_extract_path' => isset( $input['extract_path'] )
    ] );

    // Parse the input
    $params = json_decode( $input['prompt'], true );
    if ( ! isset( $params['file_path'] ) || empty( $params['file_path'] ) ) {
        log_error( 'No file path provided for reading' );
        http_response_code( 400 );
        NodeResponse::error( 'Missing file_path parameter' );
        return;
    }

    $file_path = $params['file_path'];

    // Use centralized path resolution
    try {
        $extract_path = ExtractPathResolver::resolve($params);
        log_info("Extract path resolved for file reading", ['extract_path' => $extract_path]);
    } catch (Exception $e) {
        log_error("Path resolution failed for file reading", [
            'error' => $e->getMessage(),
            'file_path' => $file_path,
            'diagnostics' => ExtractPathResolver::getDiagnosticMessage($params)
        ]);
        http_response_code(400);
        NodeResponse::error($e->getMessage());
        return;
    }

    // SECURITY: Prevent directory traversal attacks
    if ( strpos( $file_path, '..' ) !== false ) {
        log_error( 'Directory traversal attempt detected in file_path', [
            'file_path' => $file_path
        ] );
        http_response_code( 400 );
        NodeResponse::error( 'Directory traversal sequences (..) are not allowed in file_path.' );
        return;
    }

    // SECURITY: Reject any path containing null bytes
    if ( strpos( $file_path, "\0" ) !== false ) {
        log_error( 'Null byte injection attempt detected in file_path', [
            'file_path' => $file_path
        ] );
        http_response_code( 400 );
        NodeResponse::error( 'Null bytes are not allowed in file_path.' );
        return;
    }

    try {
        log_info( 'Reading file content', [
            'file_path'    => $file_path,
            'extract_path' => $extract_path
        ] );

        // Initialize ToolRegistry with the extract path as work directory
        $registry = new ToolRegistry( $extract_path );

        // Use the read_file tool to read the file content
        $result = $registry->execute_tool( 'read_file', [
            'path' => $file_path
        ] );

        if ( isset( $result['error'] ) ) {
            log_error( 'Failed to read file', [
                'file_path' => $file_path,
                'error'     => $result['error']
            ] );

            http_response_code( 404 );
            NodeResponse::error( 'File reading failed: ' . $result['error'] );
            return;
        }

        log_info( 'File read successfully', [
            'file_path'   => $file_path,
            'content_size' => strlen( $result['content'] ?? '' ),
            'total_lines' => $result['total_lines'] ?? 0
        ] );

        // Return clean response
        NodeResponse::success( [
            'file_content' => $result['content'] ?? '',
            'file_lines'   => $result['total_lines'] ?? 0,
            'file_size'    => strlen( $result['content'] ?? '' ),
            'file_path'    => $file_path,
            'extract_path' => $extract_path
        ] );

    } catch ( Exception $e ) {
        log_error( 'File reading failed: ' . $e->getMessage(), [
            'file_path'    => $file_path,
            'extract_path' => $extract_path
        ] );

        http_response_code( 500 );
        NodeResponse::error( 'File reading failed', [ 'message' => $e->getMessage() ] );
    }
}
