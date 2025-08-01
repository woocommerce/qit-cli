<?php

namespace QIT_CLI\PreCommand\Configuration\Parser;

use Opis\JsonSchema\{Errors\ErrorFormatter, Validator};

/**
 * Enhanced Parser for qit.json configuration files with full feature support
 */
class QitJsonParser {
	// Properties from BaseJsonParser
	protected Validator $validator;
	protected ErrorFormatter $error_formatter;
	/** @var array<string, mixed> */
	protected array $schema_cache = [];
	protected string $root_path;

	// QitJsonParser specific properties
	/** @var array<string, mixed> */
	private array $parsed_config = [];
	private TestPackageManifestParser $package_parser;
	/** @var array<string, array<string, mixed>> Cache for loaded test packages (arrays for backward compatibility). */
	private array $loaded_packages = [];
	/** @var array<string,\QIT_CLI\PreCommand\Objects\TestPackageManifest> Cache for loaded test package manifest objects. */
	private array $loaded_manifest_objects = [];
	/** @var array<string,array<string,mixed>> Cache for test package metadata. */
	private array $loaded_package_metadata = [];
	/** @var string Track the current file being parsed. */
	private string $current_file_path;
	/** @var array<string, string> Track which directory each path came from. */
	private array $path_contexts        = [];
	private ?string $url_extend_context = null; // Track context for URL extends

	public function __construct() {
		// Initialize validator and error formatter (from BaseJsonParser)
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 10 );
		$this->error_formatter = new ErrorFormatter();
		$this->load_schemas();
		
		// Initialize package parser
		$this->package_parser = new TestPackageManifestParser();
	}

	/**
	 * Load QIT schema into cache (specialized for qit.json files)
	 */
	protected function load_schemas(): void {
		$schema_dir = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas';

		// Only load the QIT schema - this parser is specialized for qit.json files
		$schema_file = $schema_dir . '/qit-schema.json';
		if ( file_exists( $schema_file ) ) {
			$this->schema_cache[ 'qit' ] = json_decode( file_get_contents( $schema_file ) );
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
		$schema_type = 'qit'; // Fixed schema type for QitJsonParser
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
	 * Format validation errors for output (from BaseJsonParser)
	 *
	 * @param mixed $errors
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
	 * Deep merge two arrays (from BaseJsonParser)
	 *
	 * @param array<string, mixed> $base
	 * @param array<string, mixed> $override
	 * @return array<string, mixed>
	 */
	protected function deep_merge( array $base, array $override ): array {
		$merged = $base;

		foreach ( $override as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				// Keys that should be merged and deduplicated for extends inheritance
				$merge_keys = [ 'plugins', 'themes', 'volumes', 'php_extensions' ];
				// Keys that should still replace rather than merge
				$replace_keys = [ 'envs', 'secrets', 'test_packages' ];

				if ( in_array( $key, $merge_keys, true ) ) {
					// Merge and deduplicate arrays for list options
					$merged[ $key ] = array_values( array_unique( array_merge( $merged[ $key ], $value ) ) );
				} elseif ( in_array( $key, $replace_keys, true ) ) {
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

	/**
	 * Extract plugin and theme configurations from resolved environments.
	 * Returns raw configuration arrays without creating Extension objects.
	 * 
	 * @param array<string,array<string,mixed>> $resolved_envs
	 * @param array<string> $env_names
	 * @return array{plugins: array<mixed>, themes: array<mixed>}
	 */
	public function extract_extensions_from_resolved_envs(
		array $resolved_envs,
		array $env_names
	): array {
		$plugins = [];
		$themes = [];
		
		foreach ($env_names as $env_name) {
			$env_config = $resolved_envs[$env_name] ?? [];
			
			// Extract plugins
			if (isset($env_config['plugins'])) {
				foreach ($env_config['plugins'] as $plugin_config) {
					$plugins[] = $plugin_config;
				}
			}
			
			// Extract themes
			if (isset($env_config['themes'])) {
				foreach ($env_config['themes'] as $theme_config) {
					$themes[] = $theme_config;
				}
			}
		}
		
		return [
			'plugins' => $plugins,
			'themes' => $themes
		];
	}

	/**
	 * Extract test package references from resolved profile configurations.
	 * Returns raw package reference arrays.
	 * 
	 * @param array<array<string,mixed>> $resolved_profiles
	 * @return array<string>
	 */
	public function extract_package_refs_from_resolved_profiles(
		array $resolved_profiles
	): array {
		$refs = [];
		foreach ($resolved_profiles as $test_config) {
			$refs = array_merge(
				$refs,
				$test_config['test_packages'] ?? []
			);
		}
		return $refs;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function parse( string $file_path ): array {
		$this->root_path = dirname( $file_path );

		// Load and validate JSON
		$config = $this->load_and_validate_json( $file_path );

		// Store the actual file path for extends resolution (without realpath)
		$this->current_file_path = $file_path;

		// Apply business logic
		return $this->apply_business_logic( $config );
	}

	/**
	 * @param array<string, mixed> $config
	 * @return array<string, mixed>
	 */
	protected function apply_business_logic( array $config ): array {
		$this->debug_log( '=== apply_business_logic called ===' );


		// Process SUT if it exists
		if ( isset( $config['sut'] ) ) {
			$this->debug_log( 'Processing SUT' );
			$config['sut'] = $this->process_sut( $config['sut'] );
		} else {
			$this->debug_log( 'No SUT provided, skipping SUT processing' );
		}



		// Process test packages to load manifests
		if ( isset( $config['test_types'] ) ) {
			$this->load_all_test_packages( $config['test_types'] );
		}

		// Process bootstrap_packages in environments
		if ( isset( $config['environments'] ) ) {
			$this->load_bootstrap_packages( $config['environments'] );
		}

		// Validate cross-references
		$this->debug_log( 'Starting cross-reference validation' );
		$this->validate_cross_references( $config );

		$this->parsed_config = $config;

		// Add loaded test packages to the config (use manifest objects)
		$this->parsed_config['test_packages']         = $this->loaded_manifest_objects;
		$this->parsed_config['test_package_metadata'] = $this->loaded_package_metadata;

		$this->debug_log( '=== apply_business_logic completed ===' );

		return $this->parsed_config;
	}

	/**
	 * Load all test packages referenced in test types
	 *
	 * @param array<string, mixed> $test_types
	 */
	private function load_all_test_packages( array $test_types ): void {
		foreach ( $test_types as $type => $profiles ) {
			foreach ( $profiles as $profile => $settings ) {
				if ( isset( $settings['test_packages'] ) ) {
					foreach ( $settings['test_packages'] as $package_ref ) {
						$this->get_test_package( $package_ref );
					}
				}
			}
		}
	}

	/**
	 * Load bootstrap packages from environments
	 *
	 * @param array<string, mixed> $environments
	 */
	private function load_bootstrap_packages( array $environments ): void {
		foreach ( $environments as $env_name => $env ) {
			if ( isset( $env['bootstrap_packages'] ) ) {
				foreach ( $env['bootstrap_packages'] as $package_ref ) {
					$this->get_test_package( $package_ref );
				}
			}
		}
	}

	/**
	 * Get a loaded test package by reference
	 *
	 * @return array<string, mixed>
	 */
	public function get_test_package( string $reference ): array {
		if ( isset( $this->loaded_packages[ $reference ] ) ) {
			return $this->loaded_packages[ $reference ];
		}

		if ( $this->is_local_package_reference( $reference ) ) {
			$context = $this->get_path_context( $reference );

			// If no context is set, use the root path
			if ( empty( $context ) ) {
				$context = $this->root_path;
			}

			$file_path = $this->resolve_path_with_context( $reference, $context );

			$this->debug_log( "Context for '$reference': $context" );
			$this->debug_log( "Resolved path: $file_path" );

			try {
				$this->debug_log( "Attempting to parse test package at: $file_path" );
				$manifest_object = $this->package_parser->parse( $file_path );

				// Store the manifest object
				$this->loaded_manifest_objects[ $reference ] = $manifest_object;

				// Store metadata separately
				$this->loaded_package_metadata[ $reference ] = [
					'reference' => $reference,
					'local'     => true,
					'remote'    => false,
					'path'      => $file_path,
				];

				// Keep backward compatibility with array format
				$this->loaded_packages[ $reference ]              = $manifest_object->jsonSerialize();
				$this->loaded_packages[ $reference ]['reference'] = $reference;
				$this->loaded_packages[ $reference ]['local']     = true;
				$this->loaded_packages[ $reference ]['path']      = $file_path;
			} catch ( \Exception $e ) {
				throw new \RuntimeException( "Failed to parse test package '$reference': " . $e->getMessage() );
			}
		} else {
			// Remote reference - create stub
			$stub                                = $this->create_remote_package_stub( $reference );
			$this->loaded_packages[ $reference ] = $stub;

			// For remote packages, we don't have manifest objects yet, only metadata
			$this->loaded_package_metadata[ $reference ] = [
				'reference' => $reference,
				'local'     => $stub['local'] ?? false,
				'remote'    => $stub['remote'] ?? true,
				'vendor'    => $stub['vendor'] ?? '',
				'package'   => $stub['package'] ?? '',
				'version'   => $stub['version'] ?? '',
			];
		}

		return $this->loaded_packages[ $reference ];
	}

	/**
	 * Get all test packages referenced in a test configuration
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_test_packages_for_profile( string $test_type, string $profile ): array {
		if ( ! isset( $this->parsed_config['test_types'][ $test_type ][ $profile ]['test_packages'] ) ) {
			return [];
		}

		$packages   = [];
		$references = $this->parsed_config['test_types'][ $test_type ][ $profile ]['test_packages'];

		foreach ( $references as $reference ) {
			$packages[ $reference ] = $this->get_test_package( $reference );
		}

		return $packages;
	}

	/**
	 * Get bootstrap packages for an environment
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_bootstrap_packages_for_environment( string $environment ): array {
		if ( ! isset( $this->parsed_config['environments'][ $environment ]['bootstrap_packages'] ) ) {
			return [];
		}

		$packages   = [];
		$references = $this->parsed_config['environments'][ $environment ]['bootstrap_packages'];

		foreach ( $references as $reference ) {
			$packages[ $reference ] = $this->get_test_package( $reference );
		}

		return $packages;
	}

	private function is_local_package_reference( string $reference ): bool {
		// Check for local/ prefix (new format)
		if ( strpos( $reference, 'local/' ) === 0 ) {
			return true;
		}

		// Check for file paths
		if ( strpos( $reference, '/' ) !== false && strpos( $reference, ':' ) === false ) {
			return true;
		}

		// Check for .json extension
		if ( substr( $reference, - 5 ) === '.json' ) {
			return true;
		}

		return false;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function create_remote_package_stub( string $reference ): array {
		// Handle local/ prefix for new format
		if ( strpos( $reference, 'local/' ) === 0 ) {
			$parts = explode( '/', substr( $reference, 6 ), 2 );

			return [
				'vendor'    => 'local',
				'package'   => $parts[0],
				'version'   => $parts[1] ?? 'latest',
				'remote'    => false,
				'local'     => true,
				'reference' => $reference,
			];
		}

		// Parse remote reference format: vendor/package:version
		if ( preg_match( '/^([^\/]+)\/([^:]+):(.+)$/', $reference, $matches ) ) {
			return [
				'vendor'    => $matches[1],
				'package'   => $matches[2],
				'version'   => $matches[3],
				'remote'    => true,
				'reference' => $reference,
			];
		}

		throw new \RuntimeException( "Invalid package reference format: $reference" );
	}

	/**
	 * Resolve file-level extends
	 */
	private function resolve_extends( array $config, string $current_file = null, array $visited = [] ): array {
		// Debug logging
		$this->debug_log( '=== resolve_extends called ===' );
		$this->debug_log( 'Current file: ' . ( $current_file ?? 'null' ) );
		$this->debug_log( 'Config has extends: ' . ( isset( $config['extends'] ) ? $config['extends'] : 'no' ) );
		$this->debug_log( 'Visited files: ' . json_encode( $visited ) );

		if ( ! isset( $config['extends'] ) ) {
			// Mark paths in this config with their context
			$this->mark_path_contexts( $config, dirname( $current_file ?: $this->current_file_path ) );

			return $config;
		}

		// Check for circular dependencies - normalize paths for comparison
		if ( $current_file !== null ) {
			$normalized_current = $this->normalize_path( $current_file );
			if ( in_array( $normalized_current, $visited, true ) ) {
				$this->debug_log( "ERROR: Circular dependency detected for file: $normalized_current" );
				throw new \RuntimeException( 'Circular dependency detected in extends' );
			}
			$visited[] = $normalized_current;
		}

		// Resolve the path to the extended config
		$base_path = $this->resolve_extends_path( $config['extends'] );
		$this->debug_log( "Resolved extends path: $base_path" );

		// Check if we're about to load a file we've already visited
		$normalized_base = $this->normalize_path( $base_path );
		if ( in_array( $normalized_base, $visited, true ) ) {
			$this->debug_log( "ERROR: About to load already visited file: $normalized_base" );
			throw new \RuntimeException( 'Circular dependency detected in extends' );
		}

		// Load base configuration
		$base_config = $this->load_and_validate_json( $base_path );

		// Temporarily change root path for base config processing
		$original_root   = $this->root_path;
		$this->root_path = dirname( $base_path );
		$base_config     = $this->resolve_extends( $base_config, $base_path, $visited );
		$this->root_path = $original_root;

		// Mark paths in the current config before merging
		$this->mark_path_contexts( $config, dirname( $current_file ?: $this->current_file_path ) );

		// Merge configurations
		unset( $config['extends'] );

		return $this->deep_merge( $base_config, $config );
	}

	/**
	 * Mark paths in configuration with their source directory context
	 */
	private function mark_path_contexts( array &$config, string $source_dir ): void {
		// If we're processing content from a URL extend, use the URL extend context
		$effective_context = $this->url_extend_context ?? $source_dir;

		// Mark test package paths
		if ( isset( $config['test_types'] ) ) {
			foreach ( $config['test_types'] as $type => $profiles ) {
				foreach ( $profiles as $profile => $settings ) {
					if ( isset( $settings['test_packages'] ) ) {
						foreach ( $settings['test_packages'] as $package ) {
							if ( $this->is_local_package_reference( $package ) ) {
								$this->path_contexts[ $package ] = $effective_context;
								$this->debug_log( "Marked path context: '$package' => '$effective_context'" );
							}
						}
					}
				}
			}
		}

		// Mark bootstrap_packages paths
		if ( isset( $config['environments'] ) ) {
			foreach ( $config['environments'] as $env_name => $env ) {
				if ( isset( $env['bootstrap_packages'] ) ) {
					foreach ( $env['bootstrap_packages'] as $package ) {
						if ( $this->is_local_package_reference( $package ) ) {
							$this->path_contexts[ $package ] = $effective_context;
							$this->debug_log( "Marked path context: '$package' => '$effective_context'" );
						}
					}
				}
			}
		}
	}

	/**
	 * Get the source directory context for a path
	 */
	private function get_path_context( string $path ): string {
		return $this->path_contexts[ $path ] ?? $this->root_path;
	}

	/**
	 * Resolve a path with its proper context directory
	 */
	private function resolve_path_with_context( string $path, string $context ): string {
		$this->debug_log( "resolve_path_with_context: path='$path', context='$context'" );

		// Check if it's an absolute path (starts with / on Unix or C:\ on Windows)
		if ( strpos( $path, '/' ) === 0 || preg_match( '/^[A-Za-z]:/', $path ) ) {
			$this->debug_log( "Path is absolute, returning as-is: $path" );

			return $path;
		}

		// It's a relative path - prepend the context directory
		$resolved = $context . '/' . $path;
		$this->debug_log( "Resolved relative path to: $resolved" );

		return $resolved;
	}

	private function resolve_extends_path( string $extends_path ): string {
		$this->debug_log( "Resolving extends path: $extends_path" );

		// Special test flag to simulate URL extends
		if ( strpos( $extends_path, './mimick-url-for-tests/' ) === 0 ) {
			// Extract the actual file path after the test prefix
			$actual_path   = str_replace( './mimick-url-for-tests/', './', $extends_path );
			$resolved_path = $this->resolve_path( $actual_path );

			if ( ! file_exists( $resolved_path ) ) {
				throw new \RuntimeException( "Extended config file not found: $actual_path" );
			}

			// Read the file content and create a temp file to simulate URL behavior
			$contents  = file_get_contents( $resolved_path );
			$temp_file = tempnam( sys_get_temp_dir(), 'qit_extends_test_' );
			file_put_contents( $temp_file, $contents );

			// Mark that this is a URL-based extend (simulated)
			$this->url_extend_context = $this->root_path;
			$this->debug_log( 'Simulated URL extend detected, marking context as: ' . $this->root_path );

			return $temp_file;
		}

		if ( filter_var( $extends_path, FILTER_VALIDATE_URL ) ) {
			$temp_file = tempnam( sys_get_temp_dir(), 'qit_extends_' );
			$contents  = file_get_contents( $extends_path );
			file_put_contents( $temp_file, $contents );

			// Mark that this is a URL-based extend
			$this->url_extend_context = $this->root_path;
			$this->debug_log( 'URL extend detected, marking context as: ' . $this->root_path );

			return $temp_file;
		}

		// Reset URL extend context for non-URL extends
		$this->url_extend_context = null;

		// Check if extends is just the filename without path
		if ( basename( $extends_path ) === $extends_path ) {
			// It's just a filename, resolve from current directory
			$path = $this->root_path . '/' . $extends_path;
		} else {
			// For relative paths, resolve from current root
			$path = $this->resolve_path( $extends_path );
		}

		$this->debug_log( "Resolved path: $path" );

		if ( ! file_exists( $path ) ) {
			throw new \RuntimeException( "Extended config file not found: $extends_path" );
		}

		return $path;
	}

	/**
	 * Normalize a path for comparison (remove . and .. components)
	 * This is only used for circular dependency detection
	 */
	private function normalize_path( string $path ): string {
		$parts      = explode( '/', str_replace( '\\', '/', $path ) );
		$normalized = [];

		foreach ( $parts as $part ) {
			if ( $part === '.' ) {
				continue;
			}
			if ( $part === '..' && count( $normalized ) > 0 && end( $normalized ) !== '..' ) {
				array_pop( $normalized );
			} else {
				$normalized[] = $part;
			}
		}

		return implode( '/', $normalized );
	}

	/**
	 * Debug logging helper
	 */
	private function debug_log( string $message ): void {
		if ( getenv( 'QIT_DEBUG' ) ) {
			$log_dir = '/tmp/qit';
			if ( ! is_dir( $log_dir ) ) {
				mkdir( $log_dir, 0777, true );
			}

			$log_file  = $log_dir . '/qit_debug.log';
			$timestamp = gmdate( 'Y-m-d H:i:s' );
			file_put_contents( $log_file, "[$timestamp] $message\n", FILE_APPEND );
		}
	}

	/**
	 * Process SUT with enhanced source type support
	 */
	private function process_sut( array $sut ): array {
		// Validate required fields
		if ( ! isset( $sut['type'] ) || ! in_array( $sut['type'], [ 'plugin', 'theme' ], true ) ) {
			throw new \RuntimeException( "SUT type must be 'plugin' or 'theme'" );
		}

		if ( ! isset( $sut['slug'] ) ) {
			throw new \RuntimeException( 'SUT slug is required' );
		}

		if ( ! isset( $sut['source'] ) || ! isset( $sut['source']['type'] ) ) {
			throw new \RuntimeException( 'SUT source configuration is required' );
		}

		// Validate source based on type
		$this->validate_sut_source( $sut['source'] );

		// Normalize source configuration
		switch ( $sut['source']['type'] ) {
			case 'local':
				if ( isset( $sut['source']['path'] ) ) {
					$sut['source']['resolved_path'] = $this->resolve_path( $sut['source']['path'] );
				}
				break;

			case 'build':
				if ( ! isset( $sut['source']['command'] ) || ! isset( $sut['source']['output'] ) ) {
					throw new \RuntimeException( "Build source requires 'command' and 'output'" );
				}
				$sut['source']['resolved_output'] = $this->resolve_path( $sut['source']['output'] );
				break;

			case 'wporg':
			case 'wccom':
				if ( ! isset( $sut['source']['version'] ) ) {
					$sut['source']['version'] = 'stable';
				}
				break;

			case 'url':
				if ( ! isset( $sut['source']['url'] ) ) {
					throw new \RuntimeException( "URL source requires 'url'" );
				}
				break;
		}

		return $sut;
	}

	/**
	 * Validate source paths exist without modifying them
	 */
	private function validate_sut_source( array $source ): void {
		switch ( $source['type'] ) {
			case 'local':
				if ( isset( $source['path'] ) ) {
					$path = $this->resolve_path( $source['path'] );
					if ( ! is_dir( $path ) ) {
						throw new \RuntimeException( "SUT directory not found: {$source['path']}" );
					}
				}
				break;

			case 'build':
				// Can't validate build output before build runs
				break;

			case 'url':
				if ( ! isset( $source['url'] ) || ! filter_var( $source['url'], FILTER_VALIDATE_URL ) ) {
					throw new \RuntimeException( 'Invalid URL in SUT source' );
				}
				break;

			case 'wporg':
			case 'wccom':
				// These will be validated during download
				break;

			default:
				throw new \RuntimeException( "Unknown SUT source type: {$source['type']}" );
		}
	}

	private function resolve_environment_extends( array $environments ): array {
		return $this->resolve_nested_extends( $environments, 'environments' );
	}

	private function resolve_test_type_extends( array $test_types ): array {
		foreach ( $test_types as $type => &$profiles ) {
			$profiles = $this->resolve_nested_extends( $profiles, "test type '$type'" );
		}

		return $test_types;
	}

	private function resolve_nested_extends( array $items, string $context ): array {
		$resolved = [];
		$pending  = $items;

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
					$resolved[ $name ] = $this->deep_merge( $base, $config );
					unset( $pending[ $name ] );
					$progress = true;
				}
			}

			if ( ! $progress && ! empty( $pending ) ) {
				throw new \RuntimeException(
					"Circular or missing extends in $context: " . implode( ', ', array_keys( $pending ) )
				);
			}
		}

		return $resolved;
	}

	private function validate_cross_references( array $config ): void {
		$this->debug_log( '=== validate_cross_references called ===' );
		$this->debug_log( 'Config keys: ' . json_encode( array_keys( $config ) ) );

		// Validate test_types reference existing environments and test packages
		if ( isset( $config['test_types'] ) ) {
			$this->debug_log( 'Validating test_types' );
			foreach ( $config['test_types'] as $type => $profiles ) {
				foreach ( $profiles as $profile => $settings ) {
					// Validate environment references only if environments exist
					if ( isset( $settings['environment'] ) && isset( $config['environments'] ) ) {
						if ( ! isset( $config['environments'][ $settings['environment'] ] ) ) {
							throw new \RuntimeException(
								"Environment '{$settings['environment']}' referenced in test type '$type:$profile' not found"
							);
						}
					}

					// Validate test package references exist (only for local files)
					if ( isset( $settings['test_packages'] ) ) {
						$this->debug_log( "Checking test packages for $type:$profile" );
						foreach ( $settings['test_packages'] as $package_ref ) {
							$this->debug_log( "Checking package reference: $package_ref" );
							if ( $this->is_local_package_reference( $package_ref ) ) {
								$this->debug_log( "Package is local reference: $package_ref" );
								$context = $this->get_path_context( $package_ref );
								$path    = $this->resolve_path_with_context( $package_ref, $context );
								$this->debug_log( "Resolved path with context: $path (context: $context)" );
								$this->debug_log( 'File exists: ' . ( file_exists( $path ) ? 'yes' : 'no' ) );
								if ( ! file_exists( $path ) ) {
									$this->debug_log( 'ERROR: Local package file not found!' );
									throw new \RuntimeException(
										"Test package file not found: $package_ref in '$type:$profile'"
									);
								}
							} else {
								$this->debug_log( "Package is remote reference: $package_ref" );
							}
						}
					}
				}
			}
		}

		// Validate groups reference existing test_types
		if ( isset( $config['groups'] ) && isset( $config['test_types'] ) ) {
			$this->debug_log( 'Validating groups' );
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

		// Validate bootstrap_packages references in environments
		if ( isset( $config['environments'] ) ) {
			$this->debug_log( 'Validating environment bootstrap_packages' );
			foreach ( $config['environments'] as $env_name => $env ) {
				if ( isset( $env['bootstrap_packages'] ) ) {
					foreach ( $env['bootstrap_packages'] as $package_ref ) {
						$this->debug_log( "Checking bootstrap_packages package: $package_ref" );
						if ( $this->is_local_package_reference( $package_ref ) ) {
							$context = $this->get_path_context( $package_ref );
							$path    = $this->resolve_path_with_context( $package_ref, $context );
							$this->debug_log( "Bootstrap package resolved path: $path (context: $context)" );
							if ( ! file_exists( $path ) ) {
								throw new \RuntimeException(
									"Bootstrap package file not found: $package_ref in environment '$env_name'"
								);
							}
						}
					}
				}
			}
		}

		$this->debug_log( '=== validate_cross_references completed ===' );
	}

	/**
	 * Resolve a path relative to the root directory
	 * This is used for validation only, not for modifying the config
	 */
	protected function resolve_path( string $path ): string {
		if ( strpos( $path, './' ) === 0 || strpos( $path, '../' ) === 0 ) {
			// Relative path - prepend the root path
			return $this->root_path . '/' . $path;
		}

		// Absolute path - return as is
		return $path;
	}

	/**
	 * Public accessors.
	 */
	public function get_config(): array {
		return $this->parsed_config;
	}

	/**
	 * Get environment configuration with full resolution
	 */
	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found" );
		}

		$env = $this->parsed_config['environments'][ $name ];

		// Process plugins array to ensure consistent format
		if ( isset( $env['plugins'] ) ) {
			$env['plugins'] = $this->normalize_extension_list( $env['plugins'], 'plugin' );
		}

		// Process themes array to ensure consistent format
		if ( isset( $env['themes'] ) ) {
			$env['themes'] = $this->normalize_extension_list( $env['themes'], 'theme' );
		}

		return $env;
	}

	/**
	 * Normalize extension list to consistent format
	 */
	private function normalize_extension_list( array $extensions, string $type ): array {
		$normalized = [];

		foreach ( $extensions as $extension ) {
			if ( is_string( $extension ) ) {
				// Simple string format - assume WordPress.org
				$normalized[] = [
					'slug'   => $extension,
					'type'   => $type,
					'source' => [
						'type'    => 'wporg',
						'version' => 'stable',
					],
				];
			} elseif ( is_array( $extension ) ) {
				// Already in array format
				$extension['type'] = $type;

				// Ensure source is properly formatted
				if ( ! isset( $extension['source'] ) ) {
					// Legacy format support
					if ( isset( $extension['from'] ) ) {
						$extension['source'] = [
							'type'    => $extension['from'],
							'version' => $extension['version'] ?? 'stable',
						];

						if ( isset( $extension['path'] ) ) {
							$extension['source']['path'] = $extension['path'];
						}
					} else {
						// Default to wporg
						$extension['source'] = [
							'type'    => 'wporg',
							'version' => 'stable',
						];
					}
				}

				$normalized[] = $extension;
			}
		}

		return $normalized;
	}

	/**
	 * Get test configuration with full resolution
	 */
	public function get_test_config( string $test_type, string $profile ): array {
		if ( ! isset( $this->parsed_config['test_types'][ $test_type ][ $profile ] ) ) {
			return [];
		}

		$config = $this->parsed_config['test_types'][ $test_type ][ $profile ];

		// Add resolved test packages
		if ( isset( $config['test_packages'] ) ) {
			$config['resolved_packages'] = [];
			foreach ( $config['test_packages'] as $package_ref ) {
				$config['resolved_packages'][ $package_ref ] = $this->get_test_package( $package_ref );
			}
		}

		return $config;
	}

	/**
	 * Get all groups
	 */
	public function get_groups(): array {
		return $this->parsed_config['groups'] ?? [];
	}

	/**
	 * Get specific group configuration
	 */
	public function get_group( string $name ): array {
		if ( ! isset( $this->parsed_config['groups'][ $name ] ) ) {
			throw new \RuntimeException( "Group '$name' not found" );
		}

		return $this->parsed_config['groups'][ $name ];
	}
}
