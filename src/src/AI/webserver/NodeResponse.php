<?php
/**
 * Standardized Node Response Handler
 * 
 * This class ensures consistent response formatting across all node handlers
 * and eliminates double-encoding issues.
 */

namespace QIT_AI_Webserver;

class NodeResponse {
    /**
     * Return a successful response with data
     * 
     * @param array $data The actual response data (NOT JSON encoded)
     * @param array $metadata Optional metadata (model, iterations, etc.)
     * @return void Outputs JSON response directly
     */
    public static function success(array $data, array $metadata = []): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data,  // Raw data - NOT pre-encoded
            'metadata' => array_merge([
                'timestamp' => time(),
            ], $metadata)
        ]);
    }

    /**
     * Return an error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array $details Optional error details
     * @return void Outputs JSON response directly
     */
    public static function error(string $message, int $code = 500, array $details = []): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code,
                'details' => $details
            ],
            'metadata' => [
                'timestamp' => time(),
            ]
        ]);
    }
}
