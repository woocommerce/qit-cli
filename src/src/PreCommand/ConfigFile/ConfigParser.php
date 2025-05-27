<?php

namespace QIT_CLI\PreCommand\ConfigFile;

use QIT_CLI\App;
use QIT_CLI\PreCommand\ConfigFile\Parsers\CustomTestPackageParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\EnvironmentParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\GroupParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\PreTestBuildParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SimpleValueParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\TestParser;

class ConfigParser {
	private array $parsed_config = [];

	public function __construct( string $config_file ) {
		if ( ! file_exists( $config_file ) ) {
			return;
		}

		$contents   = file_get_contents( $config_file );
		$raw_config = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $raw_config ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		foreach ( $raw_config as $key => $value ) {
			switch ( $key ) {
				case '$schema':
				case 'slug':
				case 'type':
					$this->parsed_config[ $key ] = App::make( SimpleValueParser::class )->parse( $value, $key );
					break;
				case 'pre_test_build':
					$this->parsed_config[ $key ] = App::make( PreTestBuildParser::class )->parse( $value );
					break;
				case 'environments':
					$this->parsed_config[ $key ] = App::make( EnvironmentParser::class )->parse( $value );
					break;
				case 'tests':
					$this->parsed_config[ $key ] = App::make( TestParser::class )->parse( $value, $raw_config['custom_test_packages'] ?? [] );
					break;
				case 'custom_test_packages':
					$this->parsed_config[ $key ] = App::make( CustomTestPackageParser::class )->parse( $value );
					break;
				case 'groups':
					$this->parsed_config[ $key ] = App::make( GroupParser::class )->parse( $value );
					break;
				default:
					throw new \RuntimeException( "Unknown configuration $key in qit.json." );
			}


		}
	}

	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found." );
		}

		return $this->parsed_config['environments'][ $name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		return $this->parsed_config['tests'][ $test_type ][ $profile ] ?? [];
	}
}
