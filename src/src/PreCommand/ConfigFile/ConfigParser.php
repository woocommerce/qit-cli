<?php

namespace QIT_CLI\PreCommand\ConfigFile;

use QIT_CLI\App;
use QIT_CLI\RequestBuilder;

// Add import
use QIT_CLI\PreCommand\ConfigFile\Parsers\CustomTestPackageParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\EnvironmentParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\GroupParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SimpleValueParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SutParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\TestParser;

class ConfigParser {
	public array $parsed_config = [];

	public function __construct( string $config_file ) {
		$this->parsed_config = $this->parse_config( $config_file, [], true );
	}

	protected function parse_config( string $config_file, array $parsed_files, bool $is_top_level = false ): array {
		if ( in_array( $config_file, $parsed_files, true ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Circular dependency detected: $config_file\n", FILE_APPEND );
			throw new \RuntimeException( "Circular dependency detected in qit.json configuration: $config_file" );
		}

		$parsed_files[] = $config_file;

		if ( ! file_exists( $config_file ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Config file '$config_file' not found\n", FILE_APPEND );
			throw new \RuntimeException( "Config file '$config_file' not found." );
		}

		$contents   = file_get_contents( $config_file );
		$raw_config = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $raw_config ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Invalid JSON format: " . json_last_error_msg() . "\n", FILE_APPEND );
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed raw config for $config_file: " . print_r( $raw_config, true ) . "\n", FILE_APPEND );

		$parsed_config = [];
		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Initial parsed_config for $config_file: " . print_r( $parsed_config, true ) . "\n", FILE_APPEND );

		// Parse all fields first, except extends
		foreach ( $raw_config as $key => $value ) {
			if ( $key === 'extends' ) {
				continue; // Handle extends last
			}
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Processing key '$key' for $config_file\n", FILE_APPEND );
			switch ( $key ) {
				case 'sut':
					if ( isset( $value ) ) {
						file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing SUT for $config_file: " . print_r( $value, true ) . "\n", FILE_APPEND );
						$parsed_config[ $key ] = App::make( SutParser::class )->parse( $value );
						file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed SUT for $config_file: " . print_r( $parsed_config[ $key ], true ) . "\n", FILE_APPEND );
					}
					break;
				case '$schema':
					$parsed_config[ $key ] = App::make( SimpleValueParser::class )->parse( $value, $key );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed schema for $config_file\n", FILE_APPEND );
					break;
				case 'test_types':
					$parsed_config[ $key ] = App::make( TestParser::class )->parse( $value, $raw_config['test_packages'] ?? [] );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed test_types for $config_file\n", FILE_APPEND );
					break;
				case 'test_groups':
					$parsed_config[ $key ] = App::make( GroupParser::class )->parse( $value );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed test_groups for $config_file\n", FILE_APPEND );
					break;
				case 'environments':
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing environments for $config_file with SUT: " . ( isset( $parsed_config['sut'] ) ? print_r( $parsed_config['sut'], true ) : 'null' ) . "\n", FILE_APPEND );
					$parsed_config[ $key ] = App::make( EnvironmentParser::class )->parse( $value, $raw_config['test_packages'] ?? [], $parsed_config['sut'] ?? null );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Environments parsed for $config_file\n", FILE_APPEND );
					break;
				case 'test_packages':
					$parsed_config[ $key ] = App::make( CustomTestPackageParser::class )->parse( $value );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed test_packages for $config_file\n", FILE_APPEND );
					break;
				default:
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Unknown configuration key: $key for $config_file\n", FILE_APPEND );
					throw new \RuntimeException( "Unknown configuration $key in qit.json." );
			}
		}

		// Handle extends last
		if ( isset( $raw_config['extends'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Handling extends '{$raw_config['extends']}' for $config_file with parsed_config: " . print_r( $parsed_config, true ) . "\n", FILE_APPEND );
			$base_file   = $this->resolve_extends_path( $raw_config['extends'], $config_file );
			$base_config = $this->parse_config( $base_file, $parsed_files, false );
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Base config for $base_file: " . print_r( $base_config, true ) . "\n", FILE_APPEND );
			$parsed_config = $this->merge_configs( $base_config, $parsed_config, $raw_config );
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Merged config for $config_file: " . print_r( $parsed_config, true ) . "\n", FILE_APPEND );
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing completed for $config_file with final parsed_config: " . print_r( $parsed_config, true ) . "\n", FILE_APPEND );

		// Require sut only for the top-level configuration
		if ( $is_top_level && ! isset( $parsed_config['sut'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: SUT configuration is required for top-level config $config_file\n", FILE_APPEND );
			throw new \RuntimeException( "SUT configuration is required." );
		}

		return $parsed_config;
	}

	protected function resolve_extends_path( string $extends, string $current_file ): string {
		if ( ! filter_var( $extends, FILTER_VALIDATE_URL ) ) {
			$base_dir      = dirname( $current_file );
			$resolved_path = realpath( $base_dir . DIRECTORY_SEPARATOR . $extends );
			if ( $resolved_path === false ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Base config file '$extends' not found\n", FILE_APPEND );
				throw new \RuntimeException( "Base config file '$extends' not found." );
			}

			return $resolved_path;
		}

		// Use RequestBuilder for URL-based extends
		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Fetching base config from URL '$extends' using RequestBuilder\n", FILE_APPEND );
		try {
			$request  = new RequestBuilder( $extends );
			$contents = $request->request();
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Failed to fetch base config from URL '$extends': " . $e->getMessage() . "\n", FILE_APPEND );
			throw new \RuntimeException( "Failed to fetch base config from URL '$extends'." );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_base_' );
		file_put_contents( $temp_file, $contents );

		return $temp_file;
	}

	protected function merge_configs( array $base, array $child, array $child_raw ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Merging configs - Base: " . print_r( $base, true ) . "\nChild parsed: " . print_r( $child, true ) . "\nChild raw: " . print_r( $child_raw, true ) . "\n", FILE_APPEND );

		$merged = $base;

		// Preserve child's parsed SUT if available
		if ( isset( $child['sut'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Using child's parsed SUT: " . print_r( $child['sut'], true ) . "\n", FILE_APPEND );
			$merged['sut'] = $child['sut'];
		} elseif ( isset( $child_raw['sut'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing child SUT for merge: " . print_r( $child_raw['sut'], true ) . "\n", FILE_APPEND );
			$merged['sut'] = App::make( SutParser::class )->parse( $child_raw['sut'] );
		} else {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: No SUT in child parsed or raw config\n", FILE_APPEND );
		}

		// Merge other fields from child_raw
		foreach ( $child_raw as $key => $value ) {
			if ( $key === 'extends' || $key === 'sut' ) {
				continue;
			}
			if ( isset( $base[ $key ] ) && is_array( $base[ $key ] ) && is_array( $value ) ) {
				if ( in_array( $key, [ 'environments', 'test_types', 'test_packages', 'test_groups' ], true ) ) {
					$merged[ $key ] = array_replace_recursive( $base[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			} else {
				$merged[ $key ] = $value;
			}
		}

		// Merge parsed fields from child (except sut, handled above)
		foreach ( $child as $key => $value ) {
			if ( $key === 'sut' ) {
				continue;
			}
			$merged[ $key ] = $value;
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Merged config result: " . print_r( $merged, true ) . "\n", FILE_APPEND );

		return $merged;
	}

	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Environment '$name' not found\n", FILE_APPEND );
			throw new \RuntimeException( "Environment '$name' not found." );
		}

		return $this->parsed_config['environments'][ $name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		return $this->parsed_config['test_types'][ $test_type ][ $profile ] ?? [];
	}
}