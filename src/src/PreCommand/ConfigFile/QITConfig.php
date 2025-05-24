<?php

namespace QIT_CLI\PreCommand\ConfigFile;

use QIT_CLI\App;
use QIT_CLI\PreCommand\ConfigFile\Parsers\CustomTestPackageParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\EnvironmentParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\GroupParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\PreTestBuildParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SimpleValueParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\TestParser;

class QITConfig {
	private array $parsed_config = [];

	public function __construct( string $config_file ) {
		$raw_config = $this->load_config( $config_file );

		$parsers = [
			'$schema'              => SimpleValueParser::class,
			'slug'                 => SimpleValueParser::class,
			'type'                 => SimpleValueParser::class,
			'pre_test_build'       => PreTestBuildParser::class,
			'environments'         => EnvironmentParser::class,
			'tests'                => TestParser::class,
			'custom_test_packages' => CustomTestPackageParser::class,
			'groups'               => GroupParser::class,
		];

		foreach ( $raw_config as $key => $value ) {
			if ( ! isset( $parsers[ $key ] ) ) {
				throw new \RuntimeException( "No parser found for configuration key '$key'." );
			}

			$parser                      = App::make( $parsers[ $key ] );
			$context                     = [
				'key'                  => $key,
				'custom_test_packages' => $raw_config['custom_test_packages'] ?? [],
			];
			$this->parsed_config[ $key ] = $parser->parse( $value, $context );
		}

		// Apply global pre_test_build to test profiles if not set
		if ( isset( $this->parsed_config['pre_test_build'] ) && isset( $this->parsed_config['tests'] ) ) {
			foreach ( $this->parsed_config['tests'] as $test_type => &$profiles ) {
				foreach ( $profiles as $profile => &$config ) {
					if ( ! isset( $config['pre_test_build'] ) ) {
						$config['pre_test_build'] = $this->parsed_config['pre_test_build'];
					}
				}
			}
		}
	}

	private function load_config( string $config_file ): array {
		if ( ! file_exists( $config_file ) ) {
			return [];
		}

		$contents = file_get_contents( $config_file );
		$decoded  = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		return $decoded;
	}

	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found." );
		}

		return $this->parsed_config['environments'][ $name ];
	}
}