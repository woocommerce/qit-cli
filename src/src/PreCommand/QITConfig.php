<?php

namespace QIT_CLI\PreCommand;

class QITConfig {
	private ParserFactory $parser_factory;
	private array $parsed_config = [];

	public function __construct( string $config_file, ParserFactory $parser_factory ) {
		$this->parser_factory = $parser_factory;
		$raw_config           = $this->load_config( $config_file );

		foreach ( $raw_config as $key => $value ) {
			$parser                      = $this->parser_factory->get_parser( $key );
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