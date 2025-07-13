<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Centralized Extract Path Resolution
 *
 * This class provides a single source of truth for resolving extract paths:
 * - Requires extract_path from input (deterministic behavior)
 * - Validates that the path exists and is readable
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
	public static function resolve( array $input ): string {
		// Require extract_path from input (no fallback for deterministic behavior)
		if ( ! isset( $input['extract_path'] ) || empty( $input['extract_path'] ) ) {
			throw new \RuntimeException(
				'Extract path is required but not provided in input. ' .
				'Available input keys: ' . implode( ', ', array_keys( $input ) )
			);
		}

		$path = $input['extract_path'];

		// Validate it exists and is readable
		if ( ! is_dir( $path ) ) {
			throw new \RuntimeException(
				"Extract path does not exist: {$path}. " .
				'Check that zip extraction completed successfully.'
			);
		}

		if ( ! is_readable( $path ) ) {
			throw new \RuntimeException(
				"Extract path is not readable: {$path}. " .
				'Check directory permissions.'
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
	public static function isValidExtractPath( string $path ): bool {
		return ! empty( $path ) && is_dir( $path ) && is_readable( $path );
	}

	/**
	 * Get helpful error message for debugging path resolution issues
	 *
	 * @param array $input Input data to analyze
	 * @return string Diagnostic message
	 */
	public static function getDiagnosticMessage( array $input ): string {
		$diagnostics = [
			'has_extract_path'    => isset( $input['extract_path'] ),
			'extract_path_value'  => $input['extract_path'] ?? 'not_set',
			'extract_path_exists' => isset( $input['extract_path'] ) ? is_dir( $input['extract_path'] ) : false,
			'input_keys'          => array_keys( $input ),
			'session_id'          => $input['session_id'] ?? 'not_set',
		];

		return 'Path resolution diagnostics: ' . json_encode( $diagnostics, JSON_PRETTY_PRINT );
	}
}
