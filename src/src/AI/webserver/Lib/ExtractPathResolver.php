<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Centralized Extract Path Resolution
 * 
 * This class implements the "one way" approach to path resolution:
 * - Always require extract_path to be provided explicitly
 * - No complex fallback mechanisms or searching
 * - Fail fast with clear error messages
 * - Single source of truth for all handlers
 */
class ExtractPathResolver {
    
    /**
     * Single-method path resolution - no fallbacks, no complexity
     * 
     * @param array $input Input data that must contain extract_path
     * @return string Validated extract path
     * @throws \InvalidArgumentException If extract_path is missing or invalid
     * @throws \RuntimeException If extract_path doesn't exist
     */
    public static function resolve(array $input): string {
        // The ONE way: extract_path must be provided
        if (!isset($input['extract_path']) || empty($input['extract_path'])) {
            throw new \InvalidArgumentException(
                'extract_path is required and must be provided in input. ' .
                'Ensure your pipeline properly passes the extraction path from zip_extraction step.'
            );
        }
        
        $path = $input['extract_path'];
        
        // Validate it exists
        if (!is_dir($path)) {
            throw new \RuntimeException(
                "Extract path does not exist: {$path}. " .
                "Check that zip extraction completed successfully."
            );
        }
        
        // Additional validation: ensure it's readable
        if (!is_readable($path)) {
            throw new \RuntimeException(
                "Extract path is not readable: {$path}. " .
                "Check directory permissions."
            );
        }
        
        return $path;
    }
    
    /**
     * Validate that an extract path is properly formatted and accessible
     * 
     * @param string $path Path to validate
     * @return bool True if valid
     */
    public static function isValidExtractPath(string $path): bool {
        return !empty($path) && is_dir($path) && is_readable($path);
    }
    
    /**
     * Get helpful error message for debugging path resolution issues
     * 
     * @param array $input Input data to analyze
     * @return string Diagnostic message
     */
    public static function getDiagnosticMessage(array $input): string {
        $diagnostics = [
            'has_extract_path' => isset($input['extract_path']),
            'extract_path_value' => $input['extract_path'] ?? 'not_set',
            'extract_path_exists' => isset($input['extract_path']) ? is_dir($input['extract_path']) : false,
            'input_keys' => array_keys($input),
            'session_id' => $input['session_id'] ?? 'not_set'
        ];
        
        return 'Path resolution diagnostics: ' . json_encode($diagnostics, JSON_PRETTY_PRINT);
    }
}