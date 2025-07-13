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
	private string $schemasPath;

	private function __construct() {
		$this->schemasPath = __DIR__ . '/../schemas/';
	}

	public static function getInstance(): self {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Validate inbound request data
	 *
	 * @param array  $data Request data to validate
	 * @param string $requestType Request type (basic-prompt, vulnerability-scan, etc.)
	 * @return array Validation result with 'valid' boolean and 'errors' array
	 */
	public function validateInbound( array $data, string $requestType ): array {
		$schemaPath = $this->schemasPath . 'inbound/' . $requestType . '.json';
		return $this->validateAgainstSchema( $data, $schemaPath );
	}

	/**
	 * Validate outbound request data
	 *
	 * @param array  $data Request data to validate
	 * @param string $requestType Request type (node-registration, task-callback-request-success, etc.)
	 * @return array Validation result with 'valid' boolean and 'errors' array
	 */
	public function validateOutbound( array $data, string $requestType ): array {
		$schemaPath = $this->schemasPath . 'outbound/' . $requestType . '.json';
		return $this->validateAgainstSchema( $data, $schemaPath );
	}

	/**
	 * Validate data against a JSON schema using justinrainbow/json-schema
	 *
	 * @param array  $payload Data to validate
	 * @param string $schemaPath Path to the JSON schema file
	 * @return array Validation result with 'valid' boolean and 'errors' array
	 */
	private function validateAgainstSchema( array $payload, string $schemaPath ): array {
		// Log which schema we are about to use
		if ( function_exists( '\\log_debug' ) ) {
			\log_debug('Schema validation started', [
				'schema'       => basename( $schemaPath ),
				'payload_keys' => array_keys( $payload ),
			]);
		}

		if ( ! file_exists( $schemaPath ) ) {
			$msg = "Schema file not found: {$schemaPath}";
			if ( function_exists( '\\log_error' ) ) {
				\log_error( 'Schema validation failed – missing schema', [ 'schema' => $schemaPath ] );
			}
			return [
				'valid'  => false,
				'errors' => [ $msg ],
			];
		}

		$schemaContent = file_get_contents( $schemaPath );
		if ( $schemaContent === false ) {
			return [
				'valid'  => false,
				'errors' => [ "Failed to read schema file: {$schemaPath}" ],
			];
		}

		$schema = json_decode( $schemaContent );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$err = "Invalid JSON in schema file {$schemaPath}: " . json_last_error_msg();
			if ( function_exists( '\\log_error' ) ) {
				\log_error( 'Schema validation failed – invalid schema JSON', [ 'error' => $err ] );
			}
			return [
				'valid'  => false,
				'errors' => [ $err ],
			];
		}

		$validator     = new Validator();
		$payloadObject = json_decode( json_encode( $payload ) ); // Convert array to object
		$validator->validate( $payloadObject, $schema, Constraint::CHECK_MODE_TYPE_CAST );

		if ( $validator->isValid() ) {
			if ( function_exists( '\\log_debug' ) ) {
				\log_debug( 'Schema validation passed', [ 'schema' => basename( $schemaPath ) ] );
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
				'schema' => basename( $schemaPath ),
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
	public function getInboundSchemaTypes(): array {
		return $this->getSchemaTypes( 'inbound' );
	}

	/**
	 * Get list of available outbound schema types
	 *
	 * @return array
	 */
	public function getOutboundSchemaTypes(): array {
		return $this->getSchemaTypes( 'outbound' );
	}

	/**
	 * Get schema types for a given direction
	 *
	 * @param string $type
	 * @return array
	 */
	private function getSchemaTypes( string $type ): array {
		$schemaDir = $this->schemasPath . $type . '/';

		if ( ! is_dir( $schemaDir ) ) {
			return [];
		}

		$files = glob( $schemaDir . '*.json' );
		$types = [];

		foreach ( $files as $file ) {
			$types[] = basename( $file, '.json' );
		}

		return $types;
	}
}
