<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Centralized Extract Path Resolution
 * 
 * This class provides a single source of truth for resolving extract paths:
 * - Use extract_path from input when provided (normal operation)
 * - Fall back to current working directory if needed
 * - All paths are relative to the resolved extract path
 * - Single source of truth for all endpoints
 */
class ExtractPathResolver {

    /**
     * Resolve the extract path from input data
     * 
     * @param array $input Input data that should contain extract_path
     * @return string Validated extract path
     * @throws \RuntimeException If path resolution fails
     */
    public static function resolve(array $input): string {
        // Primary approach: use extract_path from input (normal operation)
        if (isset($input['extract_path']) && !empty($input['extract_path'])) {
            $path = $input['extract_path'];

            // Validate it exists and is readable
            if (!is_dir($path)) {
                throw new \RuntimeException(
                    "Extract path does not exist: {$path}. " .
                    "Check that zip extraction completed successfully."
                );
            }

            if (!is_readable($path)) {
                throw new \RuntimeException(
                    "Extract path is not readable: {$path}. " .
                    "Check directory permissions."
                );
            }

            return $path;
        }

        // Fallback: use current working directory (for testing/development)
        $path = getcwd();

        if ($path === false) {
            throw new \RuntimeException("Could not determine current working directory.");
        }

        // Validate it exists and is readable
        if (!is_dir($path)) {
            throw new \RuntimeException(
                "Current working directory does not exist: {$path}."
            );
        }

        if (!is_readable($path)) {
            throw new \RuntimeException(
                "Current working directory is not readable: {$path}. " .
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
