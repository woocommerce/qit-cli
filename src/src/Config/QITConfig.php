<?php

namespace QIT_CLI\Config;

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

	// Rest of the methods remain unchanged
	public function get( string $key, $default = null ) {
		return $this->parsed_config[ $key ] ?? $default;
	}

	public function get_all(): array {
		return $this->parsed_config;
	}

	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found." );
		}

		return $this->parsed_config['environments'][ $name ];
	}

	public function get_custom_test_package( string $name ): array {
		[ $test_type, $package_name ] = explode( '.', $name, 2 );
		if ( ! isset( $this->parsed_config['custom_test_packages'][ $test_type ][ $package_name ] ) ) {
			throw new \RuntimeException( "Custom test package '$test_type:$package_name' not found." );
		}

		return $this->parsed_config['custom_test_packages'][ $test_type ][ $package_name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		if ( ! isset( $this->parsed_config['tests'][ $test_type ][ $profile ] ) ) {
			throw new \RuntimeException( "Test configuration '$test_type:$profile' not found." );
		}

		return $this->parsed_config['tests'][ $test_type ][ $profile ];
	}

	public function get_group( string $group_name ): array {
		if ( ! isset( $this->parsed_config['groups'][ $group_name ] ) ) {
			throw new \RuntimeException( "Group '$group_name' not found." );
		}

		return $this->parsed_config['groups'][ $group_name ];
	}

	public function get_group_tests( string $group_name ): array {
		$group = $this->get_group( $group_name );
		if ( empty( $group ) ) {
			return [];
		}

		$tests = [];
		foreach ( $group as $test_type => $profiles ) {
			foreach ( $profiles as $profile ) {
				$tests[] = [
					'type'    => $test_type,
					'profile' => $profile,
					'config'  => $this->get_test_config( $test_type, $profile ),
				];
			}
		}

		return $tests;
	}

	public function get_compatibility_tests( string $test_type, string $profile ): array {
		$test_config  = $this->get_test_config( $test_type, $profile );
		$compat_tests = $test_config['compatibility_tests'] ?? [];
		if ( ! is_array( $compat_tests ) ) {
			throw new \RuntimeException( "Invalid compatibility_tests for '$test_type:$profile'. Must be an array." );
		}

		return $compat_tests;
	}
}