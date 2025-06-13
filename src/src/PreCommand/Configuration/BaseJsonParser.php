<?php

namespace QIT_CLI\PreCommand\Configuration;

use Opis\JsonSchema\{Validator, ValidationResult, Errors\ErrorFormatter};

/**
 * Base JSON parser with common functionality for schema validation and JSON processing
 */
abstract class BaseJsonParser {
	protected Validator $validator;
	protected ErrorFormatter $errorFormatter;
	protected array $schemaCache = [];
	protected string $rootPath;

	public function __construct() {
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 10 );
		$this->errorFormatter = new ErrorFormatter();
		$this->loadSchemas();
	}

	/**
	 * Get the schema type this parser handles
	 */
	abstract protected function getSchemaType(): string;

	/**
	 * Apply business logic after validation
	 */
	abstract protected function applyBusinessLogic( array $config ): array;

	/**
	 * Load schemas into cache
	 */
	protected function loadSchemas(): void {
		$schemaDir = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas';

		// Load all available schemas
		$schemas = [
			'qit'          => 'qit-schema.json',
			'test-package' => 'test-package-manifest-schema.json',
		];

		foreach ( $schemas as $type => $filename ) {
			$schemaFile = $schemaDir . '/' . $filename;
			if ( file_exists( $schemaFile ) ) {
				$this->schemaCache[ $type ] = json_decode( file_get_contents( $schemaFile ) );
			}
		}
	}

	/**
	 * Parse a JSON file with schema validation
	 */
	public function parse( string $filePath ): array {
		$this->rootPath = dirname( $filePath );

		// Load and validate JSON
		$config = $this->loadAndValidateJson( $filePath );

		// Apply business logic
		return $this->applyBusinessLogic( $config );
	}

	/**
	 * Load JSON file and validate against schema
	 */
	protected function loadAndValidateJson( string $filePath ): array {
		if ( ! file_exists( $filePath ) ) {
			throw new \RuntimeException( "File not found: $filePath" );
		}

		$contents = file_get_contents( $filePath );
		$data     = json_decode( $contents );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \RuntimeException( "Invalid JSON in $filePath: " . json_last_error_msg() );
		}

		// Validate against schema
		$schemaType = $this->getSchemaType();
		if ( ! isset( $this->schemaCache[ $schemaType ] ) ) {
			throw new \RuntimeException( "Unknown schema type: $schemaType" );
		}

		$result = $this->validator->validate( $data, $this->schemaCache[ $schemaType ] );

		if ( ! $result->isValid() ) {
			$errors   = $this->errorFormatter->format( $result->error() );
			$errorMsg = $this->formatValidationErrors( $errors, $filePath );
			throw new \RuntimeException( "Schema validation failed for $filePath:\n$errorMsg" );
		}

		// Return as array
		return json_decode( $contents, true );
	}

	/**
	 * Format validation errors for output
	 */
	protected function formatValidationErrors( $errors, string $context ): string {
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
	protected function deepMerge( array $base, array $override ): array {
		$merged = $base;

		foreach ( $override as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				// Keys that should replace rather than merge
				$replaceKeys = [ 'plugins', 'themes', 'volumes', 'env_vars', 'envs', 'secrets', 'test_packages' ];

				if ( in_array( $key, $replaceKeys ) ) {
					$merged[ $key ] = $value;
				} else {
					$merged[ $key ] = $this->deepMerge( $merged[ $key ], $value );
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}
}
