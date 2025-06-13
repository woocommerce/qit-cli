<?php

namespace QIT_CLI\PreCommand\Configuration;

use Opis\JsonSchema\{Validator, ValidationResult, Errors\ErrorFormatter};

/**
 * Base JSON parser with common functionality for schema validation and JSON processing
 */
abstract class BaseJsonParser {
	protected Validator $validator;
	protected ErrorFormatter $error_formatter;
	protected array $schema_cache = [];
	protected string $root_path;

	public function __construct() {
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 10 );
		$this->error_formatter = new ErrorFormatter();
		$this->load_schemas();
	}

	/**
	 * Get the schema type this parser handles
	 */
	abstract protected function get_schema_type(): string;

	/**
	 * Apply business logic after validation
	 */
	abstract protected function apply_business_logic( array $config ): array;

	/**
	 * Load schemas into cache
	 */
	protected function load_schemas(): void {
		$schema_dir = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas';

		// Load all available schemas
		$schemas = [
			'qit'          => 'qit-schema.json',
			'test-package' => 'test-package-manifest-schema.json',
		];

		foreach ( $schemas as $type => $filename ) {
			$schema_file = $schema_dir . '/' . $filename;
			if ( file_exists( $schema_file ) ) {
				$this->schema_cache[ $type ] = json_decode( file_get_contents( $schema_file ) );
			}
		}
	}

	/**
	 * Parse a JSON file with schema validation
	 */
	public function parse( string $file_path ): array {
		$this->root_path = dirname( $file_path );

		// Load and validate JSON
		$config = $this->load_and_validate_json( $file_path );

		// Apply business logic
		return $this->apply_business_logic( $config );
	}

	/**
	 * Load JSON file and validate against schema
	 */
	protected function load_and_validate_json( string $file_path ): array {
		if ( ! file_exists( $file_path ) ) {
			throw new \RuntimeException( "File not found: $file_path" );
		}

		$contents = file_get_contents( $file_path );
		$data     = json_decode( $contents );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \RuntimeException( "Invalid JSON in $file_path: " . json_last_error_msg() );
		}

		// Validate against schema
		$schema_type = $this->get_schema_type();
		if ( ! isset( $this->schema_cache[ $schema_type ] ) ) {
			throw new \RuntimeException( "Unknown schema type: $schema_type" );
		}

		$result = $this->validator->validate( $data, $this->schema_cache[ $schema_type ] );

		if ( ! $result->isValid() ) {
			$errors    = $this->error_formatter->format( $result->error() );
			$error_msg = $this->format_validation_errors( $errors, $file_path );
			throw new \RuntimeException( "Schema validation failed for $file_path:\n$error_msg" );
		}

		// Return as array
		return json_decode( $contents, true );
	}

	/**
	 * Format validation errors for output
	 */
	protected function format_validation_errors( $errors, string $context ): string {
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
	 * Deep merge two arrays
	 */
	protected function deep_merge( array $base, array $override ): array {
		$merged = $base;

		foreach ( $override as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				// Keys that should replace rather than merge
				$replace_keys = [ 'plugins', 'themes', 'volumes', 'env_vars', 'envs', 'secrets', 'test_packages' ];

				if ( in_array( $key, $replace_keys ) ) {
					$merged[ $key ] = $value;
				} else {
					$merged[ $key ] = $this->deep_merge( $merged[ $key ], $value );
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}
}
