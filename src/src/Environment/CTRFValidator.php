<?php

namespace QIT_CLI\Environment;

use Opis\JsonSchema\{Errors\ErrorFormatter, Validator};

/**
 * Validates CTRF (Common Test Report Format) files against the schema
 */
class CTRFValidator {
	private Validator $validator;
	private ErrorFormatter $error_formatter;
	private ?object $schema = null;

	public function __construct() {
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 10 );
		$this->error_formatter = new ErrorFormatter();
		$this->load_schema();
	}

	/**
	 * Load CTRF schema
	 */
	private function load_schema(): void {
		$schema_file = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas/ctrf-schema.json';
		if ( file_exists( $schema_file ) ) {
			$contents     = file_get_contents( $schema_file );
			$this->schema = json_decode( $contents );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new \RuntimeException( 'Failed to load CTRF schema: ' . json_last_error_msg() );
			}
		} else {
			throw new \RuntimeException( "CTRF schema file not found: $schema_file" );
		}
	}

	/**
	 * Validate CTRF data against schema
	 *
	 * @param array<string, mixed> $data CTRF data to validate.
	 * @return array{valid: bool, errors: string|null}
	 */
	public function validate( array $data ): array {
		// Fix known issues with non-compliant CTRF reporters
		// See: https://github.com/ctrf-io/playwright-ctrf-json-reporter/issues/23
		$data = $this->fix_known_ctrf_issues( $data );

		// Convert to object for validation
		$json_data = json_decode( json_encode( $data ) );

		$result = $this->validator->validate( $json_data, $this->schema );

		if ( ! $result->isValid() ) {
			$errors    = $this->error_formatter->format( $result->error() );
			$error_msg = $this->format_validation_errors( $errors );

			return [
				'valid'  => false,
				'errors' => $error_msg,
			];
		}

		return [
			'valid'  => true,
			'errors' => null,
		];
	}


	/**
	 * Fix known issues with non-compliant CTRF reporters
	 *
	 * @param array<string, mixed> $data CTRF data to fix.
	 * @return array<string, mixed> Fixed CTRF data (immutable - returns new array).
	 */
	private function fix_known_ctrf_issues( array $data ): array {
		// Create a copy to avoid mutating the original
		$fixed = $data;

		// Add missing reportFormat and specVersion if not present
		// These are required by CTRF spec but missing in playwright-ctrf-json-reporter <= 0.0.22
		// See: https://github.com/ctrf-io/playwright-ctrf-json-reporter/issues/23
		if ( ! isset( $fixed['reportFormat'] ) ) {
			$fixed['reportFormat'] = 'CTRF';
		}

		if ( ! isset( $fixed['specVersion'] ) ) {
			$fixed['specVersion'] = '0.1.0';
		}

		return $fixed;
	}

	/**
	 * Format validation errors for output
	 *
	 * @param mixed $errors
	 */
	private function format_validation_errors( $errors ): string {
		$output = '';

		foreach ( $errors as $path => $messages ) {
			if ( is_string( $messages ) ) {
				$messages = [ $messages ];
			}

			foreach ( $messages as $message ) {
				$output .= "  - $path: $message\n";
			}
		}

		return $output;
	}

	/**
	 * Validate CTRF file
	 *
	 * @param string $file_path Path to CTRF JSON file.
	 * @return array{valid: bool, errors: string|null}
	 */
	public function validate_file( string $file_path ): array {
		if ( ! file_exists( $file_path ) ) {
			return [
				'valid'  => false,
				'errors' => "File not found: $file_path",
			];
		}

		$contents = file_get_contents( $file_path );
		$data     = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return [
				'valid'  => false,
				'errors' => "Invalid JSON in $file_path: " . json_last_error_msg(),
			];
		}

		return $this->validate( $data );
	}
}
