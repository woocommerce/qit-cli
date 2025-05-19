<?php

namespace QIT_CLI\Config;

use Symfony\Component\Console\Application;

class QITConfig {
	private ConfigFileLoader $config_loader;
	private ParserFactory $parser_factory;
	private array $parsed_config = [];
	private InputPriorityHandler $input_priority_handler;

	public function __construct( string $config_file = 'qit.json', Application $console_application = null ) {
		$this->config_loader          = new ConfigFileLoader();
		$this->parser_factory         = new ParserFactory( $console_application ?? new Application() );
		$this->input_priority_handler = new InputPriorityHandler();
		$raw_config                   = $this->config_loader->load_config( $config_file );

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

	public function get( string $key, $default = null ) {
		return $this->parsed_config[ $key ] ?? $default;
	}

	public function get_all(): array {
		return $this->parsed_config;
	}

	public function get_config_file(): string {
		return $this->config_loader->get_config_file();
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