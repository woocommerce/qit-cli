<?php

namespace QIT_CLI\PreCommand\Configuration\Parser;

use Opis\JsonSchema\{Errors\ErrorFormatter, Validator};
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Parser for test package manifest files
 */
class TestPackageManifestParser {
	/** @var Validator Properties from BaseJsonParser */
	protected Validator $validator;
	protected ErrorFormatter $error_formatter;
	/** @var array<string, mixed> */
	protected array $schema_cache = [];
	protected string $root_path;

	public function __construct() {
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 10 );
		$this->error_formatter = new ErrorFormatter();
		$this->load_schemas();
	}

	/**
	 * Load test package schema into cache (specialized for test package manifest files)
	 */
	protected function load_schemas(): void {
		$schema_dir = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas';

		// Only load the test-package schema - this parser is specialized for manifest files
		$schema_file = $schema_dir . '/test-package-manifest-schema.json';
		if ( file_exists( $schema_file ) ) {
			$this->schema_cache['test-package'] = json_decode( file_get_contents( $schema_file ) );
		}
	}

	/**
	 * Load JSON file and validate against schema (from BaseJsonParser)
	 *
	 * @return array<string, mixed>
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
		$schema_type = 'test-package'; // Fixed schema type for TestPackageManifestParser
		if ( ! isset( $this->schema_cache[ $schema_type ] ) ) {
			throw new \RuntimeException( "Unknown schema type: $schema_type" );
		}

		$result = $this->validator->validate( $data, $this->schema_cache[ $schema_type ] );

		if ( ! $result->isValid() ) {
			$errors = $this->error_formatter->format( $result->error() );

			// Check if this is a secrets validation error
			$is_secrets_error = false;
			foreach ( $errors as $path => $messages ) {
				if ( $path === '/requires/secrets' ) {
					$is_secrets_error = true;
					break;
				}
			}

			if ( $is_secrets_error ) {
				// Custom error message for secrets configuration issue
				throw new \RuntimeException(
					"Invalid secrets format in $file_path\n\n" .
					"Secrets must be an array of environment variable names, not key-value pairs.\n\n" .
					"Wrong:   \"secrets\": {\"API_KEY\": \"value\"}\n" .
					"Correct: \"secrets\": [\"API_KEY\"]\n\n" .
					'The actual values should be provided as environment variables when running the test.'
				);
			} else {
				// Standard validation error for other issues
				$error_msg = $this->format_validation_errors( $errors, $file_path );
				throw new \RuntimeException( "Schema validation failed for $file_path:\n$error_msg" );
			}
		}

		// Return as array
		return json_decode( $contents, true );
	}

	/**
	 * Format validation errors for output (from BaseJsonParser)
	 *
	 * @param mixed $errors
	 */
	protected function format_validation_errors( $errors, string $context ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
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
	 * {@inheritDoc}
	 *
	 * @return TestPackageManifest
	 */
	public function parse( string $file_path ): TestPackageManifest {
		// Accept directory entries during parsing
		if ( is_dir( $file_path ) && file_exists( $file_path . '/qit-test.json' ) ) {
			$file_path .= '/qit-test.json';
		}

		$this->root_path = dirname( $file_path );

		// Load and validate JSON
		$config = $this->load_and_validate_json( $file_path );

		// Apply business logic
		$config = $this->apply_business_logic( $config );

		return new TestPackageManifest( $config );
	}

	/**
	 * @param array<string, mixed> $config
	 * @return array<string, mixed>
	 */
	protected function apply_business_logic( array $config ): array {
		// Normalize phase commands
		if ( isset( $config['test']['phases'] ) ) {
			foreach ( $config['test']['phases'] as $phase => &$commands ) {
				$config['test']['phases'][ $phase ] = $this->normalize_phase_commands( $commands );
			}
		}

		// Keep paths relative - they should be relative to the manifest file
		// No normalization needed for mu_plugins, test_results, or test_dir

		// Convert env to strings
		if ( isset( $config['envs'] ) ) {
			foreach ( $config['envs'] as $key => &$value ) {
				if ( is_bool( $value ) ) {
					$value = $value ? 'true' : 'false';
				} elseif ( is_numeric( $value ) ) {
					$value = (string) $value;
				}
			}
		}

		// Validate secrets format
		if ( isset( $config['requires']['secrets'] ) && ! is_array( $config['requires']['secrets'] ) ) {
			throw new \RuntimeException(
				'Invalid secrets format in manifest. Secrets must be an array of environment variable names.' . "\n" .
				'Example: "secrets": ["API_KEY", "SECRET_TOKEN"]' . "\n" .
				'SECURITY: Never put actual secret values in the manifest!'
			);
		}

		// Check if secrets is an associative array (security issue - might contain values)
		if ( isset( $config['requires']['secrets'] ) && is_array( $config['requires']['secrets'] ) ) {
			$first_key = array_key_first( $config['requires']['secrets'] );
			if ( $first_key !== null && ! is_int( $first_key ) ) {
				throw new \RuntimeException(
					'SECURITY ERROR: Invalid secrets format in manifest!' . "\n" .
					'Found key-value pairs but secrets must be a simple array of environment variable names.' . "\n" .
					'NEVER put secret values in the manifest - they should only be provided via environment variables at runtime.' . "\n" .
					'Incorrect: "secrets": {"API_KEY": "actual-secret-value"}' . "\n" .
					'Correct: "secrets": ["API_KEY"]' . "\n" .
					'Then provide the value at runtime: export API_KEY="actual-secret-value"'
				);
			}
		}

		// Validate test_dir exists if specified (relative to manifest location)
		if ( isset( $config['test_dir'] ) ) {
			$test_dir = $this->resolve_path( $config['test_dir'] );
			if ( ! is_dir( $test_dir ) ) {
				throw new \RuntimeException( 'Test directory not found: ' . $config['test_dir'] );
			}
		}

		return $config;
	}

	/**
	 * Normalize phase commands
	 *
	 * @param mixed $commands
	 * @return array<int, string|array<string, mixed>>
	 */
	private function normalize_phase_commands( $commands ): array {
		if ( ! is_array( $commands ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $commands as $command ) {
			if ( is_string( $command ) ) {
				$normalized[] = $command;
			} elseif ( is_array( $command ) && isset( $command['command'] ) ) {
				// Add safety check for command string
				if ( ! is_string( $command['command'] ) ) {
					throw new \RuntimeException( 'Command must be a string, got: ' . gettype( $command['command'] ) );
				}

				// Check if command references a file
				if ( strpos( $command['command'], './' ) === 0 ) {
					$file_path = $this->resolve_path( $command['command'] );
					if ( ! file_exists( $file_path ) ) {
						throw new \RuntimeException( "Phase script not found: {$command['command']}" );
					}
				}
				$normalized[] = $command;
			}
		}

		return $normalized;
	}

	/**
	 * Resolve a path relative to the manifest file location
	 * This is only used for validation, not for modifying the config
	 */
	private function resolve_path( string $path ): string {
		if ( strpos( $path, './' ) === 0 ) {
			return $this->root_path . '/' . substr( $path, 2 );
		}

		return $path;
	}
}
