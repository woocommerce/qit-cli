<?php

namespace QIT_AI_Webserver\Lib;

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

/**
 * JSON Schema Validator for QIT AI Webserver
 *
 * Validates inbound and outbound requests against their JSON schemas using justinrainbow/json-schema
 */
class JsonSchemaValidator {
	private static ?self $instance = null;
	private string $schemas_path;

	private function __construct() {
		$this->schemas_path = __DIR__ . '/../schemas/';
	}

	public static function get_instance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Validate inbound request data
	 *
	 * @param array  $data Request data to validate.
	 * @param string $request_type Request type (basic-prompt, vulnerability-scan, etc.).
	 * @return array Validation result with 'valid' boolean and 'errors' array
	 */
	public function validate_inbound( array $data, string $request_type ): array {
		$schema_path = $this->schemas_path . 'inbound/' . $request_type . '.json';
		return $this->validate_against_schema( $data, $schema_path );
	}

	/**
	 * Validate outbound request data
	 *
	 * @param array  $data Request data to validate.
	 * @param string $request_type Request type (node-registration, task-callback-request-success, etc.).
	 * @return array Validation result with 'valid' boolean and 'errors' array
	 */
	public function validate_outbound( array $data, string $request_type ): array {
		$schema_path = $this->schemas_path . 'outbound/' . $request_type . '.json';
		return $this->validate_against_schema( $data, $schema_path );
	}

	/**
	 * Validate data against a JSON schema using justinrainbow/json-schema
	 *
	 * @param array  $payload Data to validate.
	 * @param string $schema_path Path to the JSON schema file.
	 * @return array Validation result with 'valid' boolean and 'errors' array
	 */
	private function validate_against_schema( array $payload, string $schema_path ): array {
		// Log which schema we are about to use
		if ( function_exists( '\\log_debug' ) ) {
			\log_debug('Schema validation started', [
				'schema'       => basename( $schema_path ),
				'payload_keys' => array_keys( $payload ),
			]);
		}

		if ( ! file_exists( $schema_path ) ) {
			$msg = "Schema file not found: {$schema_path}";
			if ( function_exists( '\\log_error' ) ) {
				\log_error( 'Schema validation failed – missing schema', [ 'schema' => $schema_path ] );
			}
			return [
				'valid'  => false,
				'errors' => [ $msg ],
			];
		}

		$schema_content = file_get_contents( $schema_path );
		if ( $schema_content === false ) {
			return [
				'valid'  => false,
				'errors' => [ "Failed to read schema file: {$schema_path}" ],
			];
		}

		$schema = json_decode( $schema_content );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$err = "Invalid JSON in schema file {$schema_path}: " . json_last_error_msg();
			if ( function_exists( '\\log_error' ) ) {
				\log_error( 'Schema validation failed – invalid schema JSON', [ 'error' => $err ] );
			}
			return [
				'valid'  => false,
				'errors' => [ $err ],
			];
		}

		$validator      = new Validator();
		$payload_object = json_decode( json_encode( $payload ) ); // Convert array to object
		$validator->validate( $payload_object, $schema, Constraint::CHECK_MODE_TYPE_CAST );

		if ( $validator->isValid() ) {
			if ( function_exists( '\\log_debug' ) ) {
				\log_debug( 'Schema validation passed', [ 'schema' => basename( $schema_path ) ] );
			}
			return [
				'valid'  => true,
				'errors' => [],
			];
		}

		$errors = array_map(
			fn( $e ) => "{$e['property']}: {$e['message']}",
			$validator->getErrors()
		);

		if ( function_exists( '\\log_warning' ) ) {
			\log_warning('Schema validation failed', [
				'schema' => basename( $schema_path ),
				'errors' => $errors,
			]);
		}

		return [
			'valid'  => false,
			'errors' => $errors,
		];
	}

	/**
	 * Get list of available inbound schema types
	 *
	 * @return array
	 */
	public function get_inbound_schema_types(): array {
		return $this->get_schema_types( 'inbound' );
	}

	/**
	 * Get list of available outbound schema types
	 *
	 * @return array
	 */
	public function get_outbound_schema_types(): array {
		return $this->get_schema_types( 'outbound' );
	}

	/**
	 * Get schema types for a given direction
	 *
	 * @param string $type
	 * @return array
	 */
	private function get_schema_types( string $type ): array {
		$schema_dir = $this->schemas_path . $type . '/';

		if ( ! is_dir( $schema_dir ) ) {
			return [];
		}

		$files = glob( $schema_dir . '*.json' );
		$types = [];

		foreach ( $files as $file ) {
			$types[] = basename( $file, '.json' );
		}

		return $types;
	}
}
