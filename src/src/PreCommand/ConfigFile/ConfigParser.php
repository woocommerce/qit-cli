<?php

namespace QIT_CLI\PreCommand\ConfigFile;

use QIT_CLI\App;
use QIT_CLI\PreCommand\ConfigFile\Parsers\CustomTestPackageParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\EnvironmentParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\GroupParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SimpleValueParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SutParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\TestParser;

class ConfigParser {
	public array $parsed_config = [];

	public function __construct( string $config_file ) {
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

		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed raw config: " . print_r( $raw_config, true ) . "\n", FILE_APPEND );

		foreach ( $raw_config as $key => $value ) {
			switch ( $key ) {
				case '$schema':
					$this->parsed_config[ $key ] = App::make( SimpleValueParser::class )->parse( $value, $key );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed schema\n", FILE_APPEND );
					break;
				case 'sut':
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing SUT: " . print_r( $value, true ) . "\n", FILE_APPEND );
					$this->parsed_config[ $key ] = App::make( SutParser::class )->parse( $value );
					break;
				case 'test_types':
					$this->parsed_config[ $key ] = App::make( TestParser::class )->parse( $value, $raw_config['test_packages'] ?? [] );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed test_types\n", FILE_APPEND );
					break;
				case 'test_groups':
					$this->parsed_config[ $key ] = App::make( GroupParser::class )->parse( $value );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed test_groups\n", FILE_APPEND );
					break;
				case 'environments':
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing environments\n", FILE_APPEND );
					$this->parsed_config[ $key ] = App::make( EnvironmentParser::class )->parse( $value, $raw_config['test_packages'] ?? [], $raw_config['sut'] ?? [] );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Environments parsed\n", FILE_APPEND );
					break;
				case 'test_packages':
					$this->parsed_config[ $key ] = App::make( CustomTestPackageParser::class )->parse( $value );
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsed test_packages\n", FILE_APPEND );
					break;
				default:
					file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Unknown configuration key: $key\n", FILE_APPEND );
					throw new \RuntimeException( "Unknown configuration $key in qit.json." );
			}
		}
		file_put_contents( '/tmp/qit/qit_debug.log', "ConfigParser: Parsing completed\n", FILE_APPEND );
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