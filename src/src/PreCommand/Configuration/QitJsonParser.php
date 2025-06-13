<?php

namespace QIT_CLI\PreCommand\Configuration;

use Opis\JsonSchema\{
	Validator,
	ValidationResult,
	Errors\ErrorFormatter,
};
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Application;

class QitJsonParser {
	public array $parsed_config = [];
	protected string $config_file;
	protected string $root_path;
	protected Application $console_application;
	protected Validator $validator;
	protected ErrorFormatter $error_formatter;
	protected array $schema_cache = [];

	public function __construct( string $config_file, Application $console_application ) {
		$this->config_file         = $config_file;
		$this->console_application = $console_application;
		$this->root_path           = dirname( $config_file );

		// Initialize validator and error formatter
		$this->init_validator();

		// Parse configuration (always as qit.json)
		$this->parsed_config = $this->parse_qit_config( $config_file );
	}

	/**
	 * Initialize the JSON Schema validator
	 */
	protected function init_validator(): void {
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 10 );
		$this->validator->setStopAtFirstError( false );
		$this->error_formatter = new ErrorFormatter();

		// Get the schema directory from App src_dir
		$schema_dir = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas';

		// Load and cache schemas
		$this->schema_cache['qit']          = $this->load_schema_file( $schema_dir . '/qit-schema.json' );
		$this->schema_cache['test-package'] = $this->load_schema_file( $schema_dir . '/test-package-manifest-schema.json' );
	}

	/**
	 * Load schema file and return as string
	 */
	protected function load_schema_file( string $schema_file ): string {
		if ( ! file_exists( $schema_file ) ) {
			throw new \RuntimeException( "Schema file not found: $schema_file" );
		}

		$schema_content = file_get_contents( $schema_file );

		// Validate it's valid JSON
		json_decode( $schema_content );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \RuntimeException( "Invalid schema JSON in $schema_file: " . json_last_error_msg() );
		}

		return $schema_content;
	}

	/**
	 * Parse a QIT configuration file
	 */
	protected function parse_qit_config( string $config_file ): array {
		// Step 1: Load and validate as QIT config
		$raw_config = $this->load_and_validate_json( $config_file, 'qit' );

		// Step 2: Resolve all extends to build complete configuration
		$resolved_config = $this->resolve_qit_configuration( $raw_config, $config_file );

		// Step 3: Validate the final resolved configuration
		$this->validate_json_against_schema( $resolved_config, 'qit', 'resolved QIT configuration' );

		// Step 4: Ensure required properties exist in resolved configuration
		if ( ! isset( $resolved_config['sut'] ) ) {
			throw new \RuntimeException( "The 'sut' property is required in the final configuration" );
		}

		// Step 5: Apply business logic transformations
		$final_config = $this->apply_qit_business_logic( $resolved_config, dirname( $config_file ) );

		return $final_config;
	}

	/**
	 * Load JSON and validate against specific schema
	 */
	protected function load_and_validate_json( string $file_path, string $schema_type ): array {
		if ( ! file_exists( $file_path ) ) {
			throw new \RuntimeException( "File not found: $file_path" );
		}

		$contents = file_get_contents( $file_path );
		$data     = json_decode( $contents );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \RuntimeException( "Invalid JSON in $file_path: " . json_last_error_msg() );
		}

		// Debug: log the data being validated
		file_put_contents( '/tmp/qit/qit_debug.log', "=== Loading and validating file: $file_path ===\n", FILE_APPEND );
		file_put_contents( '/tmp/qit/qit_debug.log', "Data to validate: " . json_encode( $data, JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );

		// Validate against the schema
		file_put_contents( '/tmp/qit/qit_debug.log', "Validating $file_path against schema: $schema_type\n", FILE_APPEND );

		if ( ! isset( $this->schema_cache[ $schema_type ] ) ) {
			throw new \RuntimeException( "Unknown schema type: $schema_type" );
		}

		// Parse the schema string to an object
		$schema_object = json_decode( $this->schema_cache[ $schema_type ] );

		// Debug: verify schema was parsed correctly
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ERROR: Failed to parse schema JSON: " . json_last_error_msg() . "\n", FILE_APPEND );
			throw new \RuntimeException( "Failed to parse schema JSON: " . json_last_error_msg() );
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "Schema object type: " . gettype( $schema_object ) . "\n", FILE_APPEND );
		file_put_contents( '/tmp/qit/qit_debug.log', "Schema properties: " . json_encode( array_keys( get_object_vars( $schema_object ) ), JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );

		/** @var ValidationResult $result */
		$result = $this->validator->validate( $data, $schema_object );

		file_put_contents( '/tmp/qit/qit_debug.log', "Validation result: " . ( $result->isValid() ? 'VALID' : 'INVALID' ) . "\n", FILE_APPEND );

		if ( ! $result->isValid() ) {
			$errors = $this->error_formatter->format( $result->error() );

			// Debug: log raw errors
			file_put_contents( '/tmp/qit/qit_debug.log', "Raw validation errors: " . json_encode( $errors, JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );

			$error_msg = $this->format_validation_errors( $errors, $file_path );
			throw new \RuntimeException( "Schema validation failed for $file_path:\n$error_msg" );
		}

		// Return as array for processing, but preserve object structure
		// Use a custom JSON decoder that preserves empty objects
		return $this->json_decode_preserve_objects( $contents );
	}

	/**
	 * Validate array data against schema
	 */
	protected function validate_json_against_schema( array $data, string $schema_type, string $context ): void {
		// Convert to object for validation
		$data_object = json_decode( json_encode( $data ) );

		if ( ! isset( $this->schema_cache[ $schema_type ] ) ) {
			throw new \RuntimeException( "Unknown schema type: $schema_type" );
		}

		// Parse the schema string to an object
		$schema_object = json_decode( $this->schema_cache[ $schema_type ] );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \RuntimeException( "Failed to parse schema JSON: " . json_last_error_msg() );
		}

		/** @var ValidationResult $result */
		$result = $this->validator->validate( $data_object, $schema_object );

		if ( ! $result->isValid() ) {
			$errors    = $this->error_formatter->format( $result->error() );
			$error_msg = $this->format_validation_errors( $errors, $context );
			throw new \RuntimeException( "Validation failed for $context:\n$error_msg" );
		}
	}

	/**
	 * Prepare data for validation by converting empty arrays to objects where appropriate
	 */
	protected function prepare_data_for_validation( array $data ) {
		// Convert to JSON and back to object to get proper structure
		$json   = json_encode( $data );
		$object = json_decode( $json );

		// Fix known empty arrays that should be objects
		if ( isset( $object->test_types ) ) {
			foreach ( $object->test_types as $type => $profiles ) {
				if ( is_object( $profiles ) ) {
					foreach ( $profiles as $profile_name => $profile_config ) {
						// If it's an empty array, cast to object
						if ( is_array( $profile_config ) && empty( $profile_config ) ) {
							$object->test_types->$type->$profile_name = new \stdClass();
						}
					}
				}
			}
		}

		return $object;
	}

	/**
	 * Clean up empty object markers from the configuration
	 */
	protected function clean_empty_object_markers( &$data ): void {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => &$value ) {
				if ( is_array( $value ) ) {
					// If this array only contains the __empty_object__ marker, replace with empty array
					if ( count( $value ) === 1 && isset( $value['__empty_object__'] ) && $value['__empty_object__'] === true ) {
						$data[ $key ] = [];
					} else {
						// Recurse into nested arrays
						$this->clean_empty_object_markers( $value );
					}
				}
			}
		}
	}

	/**
	 * Format validation errors from ErrorFormatter output
	 */
	protected function format_validation_errors( $errors, string $context ): string {
		$output = "In $context:\n";

		// ErrorFormatter returns an object/array where keys are paths and values are arrays of messages
		foreach ( $errors as $path => $messages ) {
			// Handle both string and array messages
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
	 * Resolve QIT configuration with extends
	 */
	protected function resolve_qit_configuration( array $config, string $config_file, array $visited = [] ): array {
		$config_file = realpath( $config_file );

		// Check for circular dependencies
		if ( in_array( $config_file, $visited, true ) ) {
			throw new \RuntimeException( "Circular dependency detected: $config_file" );
		}
		$visited[] = $config_file;

		// If no extends, return as-is
		if ( ! isset( $config['extends'] ) ) {
			return $config;
		}

		// Load and resolve base configuration
		$base_path   = $this->resolve_extends_path( $config['extends'], $config_file );
		$base_config = $this->load_and_validate_json( $base_path, 'qit' );
		$base_config = $this->resolve_qit_configuration( $base_config, $base_path, $visited );

		// Merge configurations
		unset( $config['extends'] );

		return $this->deep_merge_qit_configs( $base_config, $config );
	}

	/**
	 * Apply QIT-specific business logic
	 */
	protected function apply_qit_business_logic( array $config, string $root_path ): array {
		$processed = $config;

		// Resolve paths
		$processed = $this->resolve_paths( $processed, $root_path );

		// Process SUT
		if ( isset( $processed['sut'] ) ) {
			$processed['sut'] = $this->process_sut( $processed['sut'], $root_path );
		}

		// Process environments with extends resolution
		if ( isset( $processed['environments'] ) ) {
			$processed['environments'] = $this->resolve_environment_extends( $processed['environments'] );
		}

		// Process test types with extends resolution
		if ( isset( $processed['test_types'] ) ) {
			$processed['test_types'] = $this->resolve_test_type_extends( $processed['test_types'] );
		}

		// Process test packages (load and validate them)
		if ( isset( $processed['test_packages'] ) ) {
			$processed['test_packages'] = $this->process_test_packages( $processed['test_packages'], $root_path );
		}

		// Validate cross-references
		$this->validate_cross_references( $processed );

		return $processed;
	}

	/**
	 * Process test packages - load and validate each one
	 */
	protected function process_test_packages( array $package_definitions, string $root_path ): array {
		$processed = [];

		foreach ( $package_definitions as $package_def ) {
			if ( ! isset( $package_def['type'], $package_def['name'], $package_def['file'] ) ) {
				throw new \RuntimeException( "Test package definition must have 'type', 'name', and 'file'" );
			}

			$file_path = $this->normalize_path( $root_path . '/' . $package_def['file'] );

			// Load and validate test package manifest
			$package_config = $this->load_and_validate_json( $file_path, 'test-package' );

			// Apply test package specific processing
			$package_config = $this->process_test_package_manifest( $package_config, dirname( $file_path ) );

			// Store processed package
			if ( ! isset( $processed[ $package_def['type'] ] ) ) {
				$processed[ $package_def['type'] ] = [];
			}

			$processed[ $package_def['type'] ][ $package_def['name'] ] = [
				'config'     => $package_config,
				'definition' => $package_def,
				'file_path'  => $file_path,
			];
		}

		return $processed;
	}

	/**
	 * Process test package manifest
	 */
	protected function process_test_package_manifest( array $manifest, string $package_dir ): array {
		$processed = $manifest;

		// Normalize lifecycle commands
		if ( isset( $processed['lifecycle'] ) ) {
			foreach ( $processed['lifecycle'] as $phase => &$phase_config ) {
				foreach ( [ 'setup', 'teardown', 'run' ] as $hook ) {
					if ( isset( $phase_config[ $hook ] ) ) {
						$phase_config[ $hook ] = $this->normalize_lifecycle_commands(
							$phase_config[ $hook ],
							$package_dir
						);
					}
				}
			}
		}

		// Normalize paths in mu_plugins
		if ( isset( $processed['mu_plugins'] ) ) {
			foreach ( $processed['mu_plugins'] as &$plugin ) {
				$plugin = $this->normalize_relative_path( $plugin, $package_dir );
			}
		}

		// Normalize paths in test_results
		if ( isset( $processed['test_results'] ) ) {
			foreach ( $processed['test_results'] as $format => &$path ) {
				$path = $this->normalize_relative_path( $path, $package_dir );
			}
		}

		// Convert env_vars to strings
		if ( isset( $processed['env_vars'] ) ) {
			foreach ( $processed['env_vars'] as $key => &$value ) {
				if ( is_bool( $value ) ) {
					$value = $value ? 'true' : 'false';
				} else {
					$value = (string) $value;
				}
			}
		}

		return $processed;
	}

	/**
	 * Normalize lifecycle commands
	 */
	protected function normalize_lifecycle_commands( $commands, string $base_dir ): array {
		if ( ! is_array( $commands ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $commands as $command ) {
			if ( is_string( $command ) ) {
				$normalized[] = $command;
			} elseif ( is_array( $command ) && isset( $command['command'] ) ) {
				// Check if command references a file
				if ( strpos( $command['command'], './' ) === 0 ) {
					$file_path = $base_dir . '/' . substr( $command['command'], 2 );
					if ( ! file_exists( $file_path ) ) {
						throw new \RuntimeException( "Lifecycle script not found: $file_path" );
					}
				}
				$normalized[] = $command;
			}
		}

		return $normalized;
	}

	/**
	 * Deep merge for QIT configs
	 */
	protected function deep_merge_qit_configs( array $base, array $override ): array {
		$merged = $base;

		foreach ( $override as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				// Keys that should replace rather than merge
				$replace_keys = [ 'plugins', 'themes', 'volumes', 'env_vars', 'secrets', 'test_packages' ];

				if ( in_array( $key, $replace_keys ) ) {
					$merged[ $key ] = $value;
				} else {
					// Check if this should remain an object (associative array)
					$is_assoc = function ( $arr ) {
						if ( empty( $arr ) ) {
							return true;
						} // Empty arrays should be treated as objects

						return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
					};

					if ( $is_assoc( $value ) && $is_assoc( $merged[ $key ] ) ) {
						// Recursive merge for associative arrays
						$merged[ $key ] = $this->deep_merge_qit_configs( $merged[ $key ], $value );
					} else {
						// Replace for numeric arrays
						$merged[ $key ] = $value;
					}
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	/**
	 * Resolve environment extends
	 */
	protected function resolve_environment_extends( array $environments ): array {
		$resolved = [];
		$pending  = $environments;

		while ( ! empty( $pending ) ) {
			$progress = false;

			foreach ( $pending as $name => $config ) {
				if ( ! isset( $config['extends'] ) ) {
					$resolved[ $name ] = $config;
					unset( $pending[ $name ] );
					$progress = true;
				} elseif ( isset( $resolved[ $config['extends'] ] ) ) {
					$base = $resolved[ $config['extends'] ];
					unset( $config['extends'] );
					$resolved[ $name ] = $this->deep_merge_qit_configs( $base, $config );
					unset( $pending[ $name ] );
					$progress = true;
				}
			}

			if ( ! $progress && ! empty( $pending ) ) {
				$names = implode( ', ', array_keys( $pending ) );
				throw new \RuntimeException( "Circular or missing extends in environments: $names" );
			}
		}

		return $resolved;
	}

	/**
	 * Validate cross-references in the configuration
	 */
	protected function validate_cross_references( array $config ): void {
		// Validate test_types reference existing environments
		if ( isset( $config['test_types'] ) && isset( $config['environments'] ) ) {
			foreach ( $config['test_types'] as $type => $profiles ) {
				foreach ( $profiles as $profile => $settings ) {
					if ( isset( $settings['environment'] ) && ! isset( $config['environments'][ $settings['environment'] ] ) ) {
						throw new \RuntimeException(
							"Environment '{$settings['environment']}' referenced in test type '$type:$profile' not found"
						);
					}
				}
			}
		}

		// Validate groups reference existing test_types and profiles
		if ( isset( $config['groups'] ) && isset( $config['test_types'] ) ) {
			foreach ( $config['groups'] as $group => $tests ) {
				foreach ( $tests as $test_type => $profiles ) {
					if ( ! isset( $config['test_types'][ $test_type ] ) ) {
						throw new \RuntimeException( "Test type '$test_type' in group '$group' not found" );
					}
					foreach ( $profiles as $profile ) {
						if ( ! isset( $config['test_types'][ $test_type ][ $profile ] ) ) {
							throw new \RuntimeException(
								"Profile '$profile' for test type '$test_type' in group '$group' not found"
							);
						}
					}
				}
			}
		}

		// Validate test package references in test_types
		if ( isset( $config['test_types'] ) ) {
			foreach ( $config['test_types'] as $type => $profiles ) {
				foreach ( $profiles as $profile => $settings ) {
					if ( isset( $settings['test_packages'] ) ) {
						foreach ( $settings['test_packages'] as $package_ref ) {
							$this->validate_test_package_reference( $package_ref, $config['test_packages'] ?? [] );
						}
					}
				}
			}
		}
	}

	/**
	 * Validate a test package reference
	 */
	protected function validate_test_package_reference( string $reference, array $available_packages ): void {
		// Handle local file references (e.g., "tests/e2e/checkout.json")
		if ( strpos( $reference, '/' ) !== false && ! strpos( $reference, ':' ) ) {
			// This is a file path, will be validated when loaded
			return;
		}

		// Handle remote references (e.g., "woocommerce/checkout:stable")
		if ( preg_match( '/^([^\/]+)\/([^:]+):(.+)$/', $reference ) ) {
			// Remote package reference, assume valid
			return;
		}

		// Handle local package references
		if ( preg_match( '/^local\/([^:]+)$/', $reference, $matches ) ) {
			$package_name = $matches[1];

			// Check if package exists in any test type
			$found = false;
			foreach ( $available_packages as $type => $packages ) {
				if ( isset( $packages[ $package_name ] ) ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				throw new \RuntimeException( "Local test package '$package_name' not found in test_packages" );
			}
		}
	}

	// Helper methods
	protected function resolve_paths( array $config, string $root_path ): array {
		$resolved = $config;

		// Resolve SUT paths
		if ( isset( $resolved['sut']['source'] ) ) {
			$resolved['sut']['source'] = $this->resolve_source_paths( $resolved['sut']['source'], $root_path );
		}

		// Resolve environment paths
		if ( isset( $resolved['environments'] ) ) {
			foreach ( $resolved['environments'] as &$env ) {
				if ( isset( $env['volumes'] ) ) {
					foreach ( $env['volumes'] as &$volume ) {
						$parts = explode( ':', $volume );
						if ( count( $parts ) === 2 && strpos( $parts[0], './' ) === 0 ) {
							$parts[0] = $this->normalize_path( $root_path . '/' . substr( $parts[0], 2 ) );
							$volume   = implode( ':', $parts );
						}
					}
				}
			}
		}

		return $resolved;
	}

	protected function resolve_source_paths( array $source, string $root_path ): array {
		if ( isset( $source['path'] ) && strpos( $source['path'], './' ) === 0 ) {
			$source['path'] = $this->normalize_path( $root_path . '/' . substr( $source['path'], 2 ) );
		}
		if ( isset( $source['output'] ) && strpos( $source['output'], './' ) === 0 ) {
			$source['output'] = $this->normalize_path( $root_path . '/' . substr( $source['output'], 2 ) );
		}

		return $source;
	}

	protected function normalize_path( string $path ): string {
		return str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );
	}

	protected function normalize_relative_path( string $path, string $base_dir ): string {
		if ( strpos( $path, './' ) === 0 ) {
			return './' . str_replace( $base_dir . '/', '', $this->normalize_path( $base_dir . '/' . substr( $path, 2 ) ) );
		}

		return $path;
	}

	protected function process_sut( array $sut, string $root_path ): array {
		// Validate source type specific requirements
		if ( isset( $sut['source'] ) ) {
			switch ( $sut['source']['type'] ) {
				case 'directory':
				case 'local':
					if ( isset( $sut['source']['path'] ) && ! is_dir( $sut['source']['path'] ) ) {
						throw new \RuntimeException( "SUT directory not found: {$sut['source']['path']}" );
					}
					break;
				case 'zip':
					if ( isset( $sut['source']['path'] ) && ! file_exists( $sut['source']['path'] ) ) {
						throw new \RuntimeException( "SUT zip file not found: {$sut['source']['path']}" );
					}
					break;
			}
		}

		return $sut;
	}

	protected function resolve_test_type_extends( array $test_types ): array {
		foreach ( $test_types as $type => &$profiles ) {
			$profiles = $this->resolve_profile_extends( $profiles );
		}

		return $test_types;
	}

	protected function resolve_profile_extends( array $profiles ): array {
		$resolved = [];
		$pending  = $profiles;

		while ( ! empty( $pending ) ) {
			$progress = false;

			foreach ( $pending as $name => $config ) {
				if ( ! isset( $config['extends'] ) ) {
					$resolved[ $name ] = $config;
					unset( $pending[ $name ] );
					$progress = true;
				} elseif ( isset( $resolved[ $config['extends'] ] ) ) {
					$base = $resolved[ $config['extends'] ];
					unset( $config['extends'] );
					// Use deep merge to preserve structure
					$resolved[ $name ] = $this->deep_merge_qit_configs( $base, $config );
					unset( $pending[ $name ] );
					$progress = true;
				}
			}

			if ( ! $progress && ! empty( $pending ) ) {
				throw new \RuntimeException( "Circular or missing extends in test profiles" );
			}
		}

		return $resolved;
	}

	protected function resolve_extends_path( string $extends, string $current_file ): string {
		if ( ! filter_var( $extends, FILTER_VALIDATE_URL ) ) {
			$base_dir      = dirname( $current_file );
			$resolved_path = realpath( $base_dir . DIRECTORY_SEPARATOR . $extends );
			if ( $resolved_path === false ) {
				throw new \RuntimeException( "Extended config file not found: $extends" );
			}

			return $resolved_path;
		}

		// Handle URL extends
		try {
			$request  = new RequestBuilder( $extends );
			$contents = $request->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "Failed to fetch config from URL: $extends" );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_extends_' );
		file_put_contents( $temp_file, $contents );

		return $temp_file;
	}

	/**
	 * Decode JSON preserving empty objects
	 *
	 * @param string $json
	 *
	 * @return array
	 */
	protected function json_decode_preserve_objects( string $json ): array {
		// First decode as objects to identify structure
		$object_structure = json_decode( $json );

		// Then decode as array
		$array_data = json_decode( $json, true );

		// Preserve empty objects by marking them
		$this->preserve_empty_objects( $object_structure, $array_data );

		return $array_data;
	}

	/**
	 * Recursively preserve empty objects in array data
	 */
	protected function preserve_empty_objects( $object_structure, &$array_data, $path = '' ): void {
		if ( is_object( $object_structure ) ) {
			foreach ( $object_structure as $key => $value ) {
				$current_path = $path ? "$path.$key" : $key;

				// If it's an empty object in the original but empty array in the converted
				if ( is_object( $value ) &&
				     count( get_object_vars( $value ) ) === 0 &&
				     isset( $array_data[ $key ] ) &&
				     $array_data[ $key ] === [] ) {
					// Mark it as an empty object using a special marker
					$array_data[ $key ] = [ '__empty_object__' => true ];
				} elseif ( is_object( $value ) || is_array( $value ) ) {
					// Recurse into nested structures
					if ( isset( $array_data[ $key ] ) ) {
						$this->preserve_empty_objects( $value, $array_data[ $key ], $current_path );
					}
				}
			}
		} elseif ( is_array( $object_structure ) ) {
			foreach ( $object_structure as $index => $value ) {
				$current_path = $path ? "$path[$index]" : "[$index]";
				if ( ( is_object( $value ) || is_array( $value ) ) && isset( $array_data[ $index ] ) ) {
					$this->preserve_empty_objects( $value, $array_data[ $index ], $current_path );
				}
			}
		}
	}

	// Public accessors
	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found" );
		}

		return $this->parsed_config['environments'][ $name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		return $this->parsed_config['test_types'][ $test_type ][ $profile ] ?? [];
	}

	public function get_resolved_package( string $test_type, string $package_name ): array {
		if ( isset( $this->parsed_config['test_packages'][ $test_type ][ $package_name ] ) ) {
			return $this->parsed_config['test_packages'][ $test_type ][ $package_name ]['config'];
		}
		throw new \RuntimeException( "Test package '$test_type:$package_name' not found" );
	}
}