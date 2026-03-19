<?php

namespace QIT_CLI\PreCommand\Configuration;

use Opis\JsonSchema\{Errors\ErrorFormatter, Validator};
use QIT_CLI\App;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\Extension;
use Symfony\Component\Filesystem\Path;
use function QIT_CLI\debug_log;

/**
 * Single-pass configuration resolver that handles JSON loading, validation,
 * extends resolution, CLI overrides, and extension creation.
 *
 * Replaces ResolvedConfiguration, ConfigMerger, ExtensionFactory, and parts of QitJsonParser.
 */
class ConfigResolver {
	private Validator $validator;
	private ErrorFormatter $error_formatter;
	/** @var array<string, mixed> */
	private array $schema_cache = [];
	private TestPackageManifestParser $package_parser;

	public function __construct( TestPackageManifestParser $package_parser ) {
		$this->validator = new Validator();
		$this->validator->setMaxErrors( 1 );
		$this->error_formatter = new ErrorFormatter();
		$this->package_parser  = $package_parser;
		$this->load_schemas();
	}

	/**
	 * Load and resolve complete configuration from file and CLI overrides.
	 *
	 * @param string|null         $config_file Path to qit.json file.
	 * @param array<string,mixed> $cli_overrides CLI parameter overrides.
	 * @return array<string,mixed> Fully resolved configuration
	 */
	public static function load( ?string $config_file, array $cli_overrides = [] ): array {
		return App::make( self::class )->resolve( $config_file, $cli_overrides );
	}

	/**
	 * Main resolution method - single pass through all configuration.
	 *
	 * @param string|null         $config_file
	 * @param array<string,mixed> $cli_overrides
	 * @return array<string,mixed>
	 */
	private function resolve( ?string $config_file, array $cli_overrides ): array {
		// Load and validate JSON
		$config = $config_file ? $this->load_and_validate_json( $config_file ) : [];

		// Apply default fallbacks
		$config = $this->apply_defaults( $config );

		// Resolve extends chains
		$config = $this->resolve_all_extends( $config );

		// Load and resolve test packages
		$config = $this->resolve_test_packages( $config, $config_file );

		// Apply CLI overrides
		$config = $this->apply_cli_overrides( $config, $cli_overrides );

		// Create extension objects
		$config = $this->create_extensions( $config );

		// Add metadata
		$config['metadata'] = [
			'config_file' => $config_file,
			'resolved_at' => time(),
		];

		debug_log( 'Configuration resolved successfully' );
		return $config;
	}

	/**
	 * Load JSON file and validate against schema.
	 *
	 * @param string $file_path
	 * @return array<string,mixed>
	 */
	private function load_and_validate_json( string $file_path ): array {
		if ( ! file_exists( $file_path ) ) {
			throw new \RuntimeException( "Configuration file not found: $file_path" );
		}

		$contents = file_get_contents( $file_path );
		$data     = json_decode( $contents );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \RuntimeException( "Invalid JSON in $file_path: " . json_last_error_msg() );
		}

		// Validate against schema
		if ( ! isset( $this->schema_cache['qit'] ) ) {
			throw new \RuntimeException( 'QIT schema not loaded' );
		}

		$result = $this->validator->validate( $data, $this->schema_cache['qit'] );

		if ( ! $result->isValid() ) {
			$errors    = $this->error_formatter->format( $result->error() );
			$error_msg = $this->format_validation_errors( $errors );
			throw new \RuntimeException( "Schema validation failed for $file_path:\n$error_msg" );
		}

		return json_decode( $contents, true );
	}

	/**
	 * Apply default configurations.
	 *
	 * @param array<string,mixed> $config
	 * @return array<string,mixed>
	 */
	private function apply_defaults( array $config ): array {
		// Ensure basic structure exists
		$config['environments']  = $config['environments'] ?? [];
		$config['test_types']    = $config['test_types'] ?? [];
		$config['groups']        = $config['groups'] ?? [];
		$config['test_packages'] = $config['test_packages'] ?? [];

		// Add default environment if missing
		if ( ! isset( $config['environments']['default'] ) ) {
			$config['environments']['default'] = [];
		}

		// Add default e2e profile if missing
		if ( ! isset( $config['test_types']['e2e']['default'] ) ) {
			$config['test_types']['e2e']['default'] = [];
		}

		return $config;
	}

	/**
	 * Resolve all extends chains in environments and test_types.
	 *
	 * @param array<string,mixed> $config
	 * @return array<string,mixed>
	 */
	private function resolve_all_extends( array $config ): array {
		// Resolve environment extends
		foreach ( $config['environments'] as $name => $env ) {
			$config['environments'][ $name ] = $this->flatten_extends( $env, $config['environments'] );
		}

		// Resolve test_type profile extends
		foreach ( $config['test_types'] as $test_type => $profiles ) {
			foreach ( $profiles as $profile_name => $profile ) {
				$config['test_types'][ $test_type ][ $profile_name ] = $this->flatten_extends( $profile, $profiles );
			}
		}

		return $config;
	}

	/**
	 * Recursively flatten extends chains.
	 *
	 * @param array<string,mixed>               $node
	 * @param array<string,array<string,mixed>> $pool
	 * @return array<string,mixed>
	 */
	private function flatten_extends( array $node, array $pool ): array {
		if ( empty( $node['extends'] ) ) {
			return $node;
		}

		$parent_name = $node['extends'];
		if ( ! isset( $pool[ $parent_name ] ) ) {
			throw new \RuntimeException( "Cannot extend from '$parent_name': not found" );
		}

		$parent = $pool[ $parent_name ];
		unset( $node['extends'] );

		// Recursively resolve parent first
		$resolved_parent = $this->flatten_extends( $parent, $pool );

		// Merge with child - array_replace_recursive gives child precedence over parent
		$result = array_replace_recursive( $resolved_parent, $node );

		// Special handling for list keys - merge and deduplicate instead of replace
		$list_keys = [ 'plugins', 'themes', 'volumes', 'php_extensions' ];
		foreach ( $list_keys as $list_key ) {
			if ( isset( $resolved_parent[ $list_key ] ) && isset( $node[ $list_key ] ) ) {
				$result[ $list_key ] = array_values(
					array_unique( array_merge( $resolved_parent[ $list_key ], $node[ $list_key ] ) )
				);
			}
		}

		return $result;
	}

	/**
	 * Load and resolve test packages.
	 *
	 * @param array<string,mixed> $config
	 * @param string|null         $config_file
	 * @return array<string,mixed>
	 */
	private function resolve_test_packages( array $config, ?string $config_file ): array {
		if ( empty( $config['test_packages'] ) ) {
			return $config;
		}

		$base_dir          = $config_file ? dirname( $config_file ) : getcwd();
		$resolved_packages = [];
		$package_metadata  = [];

		foreach ( $config['test_packages'] as $package_config ) {
			if ( is_string( $package_config ) ) {
				$package_config = [ 'reference' => $package_config ];
			}

			$reference = $package_config['reference'];

			// Resolve package path
			$package_path = $this->resolve_package_path( $package_config, $base_dir );

			// Load package manifest
			$manifest_path = $package_path . '/qit-test.json';
			if ( ! file_exists( $manifest_path ) ) {
				throw new \RuntimeException( "Test package manifest not found: $manifest_path" );
			}

			$manifest                        = $this->package_parser->parse( $manifest_path );
			$resolved_packages[ $reference ] = $manifest;
			$package_metadata[ $reference ]  = [
				'reference'     => $reference,
				'path'          => $package_path,
				'manifest_path' => $manifest_path,
			];
		}

		$config['resolved_test_packages'] = $resolved_packages;
		$config['test_package_metadata']  = $package_metadata;

		return $config;
	}

	/**
	 * Apply CLI parameter overrides with proper precedence.
	 *
	 * @param array<string,mixed> $config
	 * @param array<string,mixed> $cli_overrides
	 * @return array<string,mixed>
	 */
	private function apply_cli_overrides( array $config, array $cli_overrides ): array {
		if ( empty( $cli_overrides ) ) {
			return $config;
		}

		// List options that should be merged and deduplicated instead of replaced
		$list_options = [ 'plugins', 'themes', 'volumes', 'php_extensions' ];

		foreach ( $cli_overrides as $key => $value ) {
			if ( $value === null ) {
				continue; // Skip null values to preserve config/defaults
			}

			if ( in_array( $key, $list_options, true ) && is_array( $value ) ) {
				// For list options, collect existing values from environments and merge with CLI
				$existing = $this->collect_from_environments( $config, $key );
				$merged   = array_values( array_unique( array_merge( $existing, $value ) ) );

				// Store at top level for backward compatibility
				$config[ $key ] = $merged;

				// Also propagate back into environments that originally contained the key
				foreach ( $config['environments'] as $env_name => $env ) {
					if ( isset( $env[ $key ] ) ) {
						$config['environments'][ $env_name ][ $key ] = $merged;
					}
				}
			} else {
				$config[ $key ] = $value;
			}
		}

		return $config;
	}

	/**
	 * Collect values for a key from all environments.
	 *
	 * @param array<string,mixed> $config
	 * @param string              $key
	 * @return array<mixed>
	 */
	private function collect_from_environments( array $config, string $key ): array {
		$collected = [];

		foreach ( $config['environments'] as $env ) {
			if ( isset( $env[ $key ] ) && is_array( $env[ $key ] ) ) {
				$collected = array_merge( $collected, $env[ $key ] );
			}
		}

		return array_unique( $collected );
	}

	/**
	 * Create Extension objects from configuration.
	 *
	 * @param array<string,mixed> $config
	 * @return array<string,mixed>
	 */
	private function create_extensions( array $config ): array {
		$resolved_plugins = [];
		$resolved_themes  = [];

		// Create SUT extension if present
		if ( isset( $config['sut'] ) ) {
			$config['sut_extension'] = $this->create_sut_extension( $config['sut'] );
		}

		// Process plugins from environments
		// @phan-suppress-next-line PhanTypeSuspiciousNonTraversableForeach - False positive
		foreach ( $config['environments'] as $env_name => $env ) {
			if ( isset( $env['plugins'] ) && is_array( $env['plugins'] ) ) {
				foreach ( $env['plugins'] as $plugin_config ) {
					if ( is_string( $plugin_config ) ) {
						$plugin_config = [
							'slug' => $plugin_config,
							'from' => 'wporg',
						];
					}
					$slug = $plugin_config['slug'];
					if ( ! isset( $resolved_plugins[ $slug ] ) ) {
						$resolved_plugins[ $slug ] = $this->create_extension( $plugin_config, 'plugin' );
					}
				}
			}

			if ( isset( $env['themes'] ) && is_array( $env['themes'] ) ) {
				foreach ( $env['themes'] as $theme_config ) {
					if ( is_string( $theme_config ) ) {
						$theme_config = [
							'slug' => $theme_config,
							'from' => 'wporg',
						];
					}
					$slug = $theme_config['slug'];
					if ( ! isset( $resolved_themes[ $slug ] ) ) {
						$resolved_themes[ $slug ] = $this->create_extension( $theme_config, 'theme' );
					}
				}
			}
		}

		$config['resolved_plugins'] = $resolved_plugins;
		$config['resolved_themes']  = $resolved_themes;

		return $config;
	}

	/**
	 * Create Extension for System Under Test.
	 *
	 * @param array<string,mixed> $sut
	 * @return Extension
	 */
	private function create_sut_extension( array $sut ): Extension {
		debug_log( "Creating SUT extension for: {$sut['slug']} ({$sut['type']})" );

		$extension           = new Extension( $sut['slug'], $sut['type'] );
		$extension->priority = Extension::PRIORITY_HIGH;

		$source = $sut['source'];
		switch ( $source['type'] ) {
			case 'local':
				$extension->from      = 'local';
				$extension->directory = $source['resolved_path'] ?? $source['path'];
				$extension->source    = $extension->directory;
				if ( isset( $source['build'] ) ) {
					$extension->build_command = $source['build'];
					$extension->build_cwd     = $extension->directory;
				}
				break;

			case 'build':
				$extension->from   = 'build';
				$extension->source = $source;
				break;

			case 'url':
				$extension->from   = 'url';
				$extension->source = $source['url'];
				break;

			case 'wporg':
				$extension->from    = 'wporg';
				$extension->version = $source['version'] ?? 'stable';
				break;

			case 'wccom':
				$extension->from    = 'wccom';
				$extension->version = $source['version'] ?? 'stable';
				break;

			default:
				throw new \RuntimeException( "Unknown SUT source type: {$source['type']}" );
		}

		return $extension;
	}

	/**
	 * Create Extension from configuration array.
	 *
	 * @param array<string,mixed> $config
	 * @param string              $type
	 * @return Extension
	 */
	private function create_extension( array $config, string $type ): Extension {
		$extension                      = new Extension( $config['slug'], $type );
		$extension->added_automatically = 'Added from environment configuration';

		if ( isset( $config['from'] ) ) {
			$extension->from = $config['from'];

			switch ( $config['from'] ) {
				case 'wporg':
					$extension->version = $config['version'] ?? 'stable';
					break;

				case 'wccom':
					$extension->version = $config['version'] ?? 'stable';
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $config['path'];
					$extension->source    = $config['path'];
					if ( isset( $config['build'] ) ) {
						$extension->build_command = $config['build'];
						$extension->build_cwd     = $config['path'];
					}
					break;

				case 'url':
					$extension->source = $config['url'];
					break;

				case 'build':
					$extension->source = [
						'type'    => 'build',
						'command' => $config['command'],
						'output'  => $config['output'],
					];
					break;

				default:
					throw new \RuntimeException( "Unknown extension source type: {$config['from']}" );
			}
		} elseif ( isset( $config['source'] ) ) {
			// Legacy source format support
			$source = $config['source'];
			switch ( $source['type'] ) {
				case 'wporg':
					$extension->from    = 'wporg';
					$extension->version = $source['version'] ?? 'stable';
					break;

				case 'wccom':
					$extension->from    = 'wccom';
					$extension->version = $source['version'] ?? 'stable';
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $source['path'];
					$extension->source    = $source['path'];
					if ( isset( $source['build'] ) ) {
						$extension->build_command = $source['build'];
						$extension->build_cwd     = $source['path'];
					}
					break;

				case 'url':
					$extension->from   = 'url';
					$extension->source = $source['url'];
					break;

				default:
					throw new \RuntimeException( "Unknown legacy source type: {$source['type']}" );
			}
		} else {
			throw new \RuntimeException( "Extension config missing both 'from' and 'source' properties" );
		}

		return $extension;
	}

	/**
	 * Resolve test package path from configuration.
	 *
	 * @param array<string,mixed> $package_config
	 * @param string              $base_dir
	 * @return string
	 */
	private function resolve_package_path( array $package_config, string $base_dir ): string {
		if ( isset( $package_config['local'] ) ) {
			$path = $package_config['local'];
			if ( ! Path::isAbsolute( $path ) ) {
				$path = $base_dir . '/' . $path;
			}
			return realpath( $path ) ?: $path;
		}

		// For remote packages, we'd need to download them first
		// This is a simplified implementation
		throw new \RuntimeException( 'Remote test packages not yet supported in ConfigResolver' );
	}

	/**
	 * Load QIT schema into cache.
	 */
	private function load_schemas(): void {
		$schema_dir  = \QIT_CLI\App::getVar( 'src_dir' ) . '/PreCommand/Schemas';
		$schema_file = $schema_dir . '/qit-schema.json';

		if ( file_exists( $schema_file ) ) {
			$this->schema_cache['qit'] = json_decode( file_get_contents( $schema_file ) );
		} else {
			throw new \RuntimeException( "QIT schema file not found: $schema_file" );
		}
	}

	/**
	 * Format validation errors for output.
	 *
	 * @param mixed $errors
	 * @return string
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
}
