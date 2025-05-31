<?php

namespace QIT_CLI\PreCommand\ConfigFile;

use QIT_CLI\App;
use QIT_CLI\RequestBuilder;
use QIT_CLI\PreCommand\ConfigFile\Parsers\CustomTestPackageParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\EnvironmentParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\GroupParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SimpleValueParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\SutParser;
use QIT_CLI\PreCommand\ConfigFile\Parsers\TestParser;

class ConfigParser {
	public array $parsed_config = [];
	protected string $config_file;

	public function __construct( string $config_file ) {
		$this->config_file   = $config_file;
		$this->parsed_config = $this->parse_config( $config_file, [], true );
	}

	protected function parse_config( string $config_file, array $parsed_files, bool $is_top_level = false ): array {
		if ( in_array( $config_file, $parsed_files, true ) ) {
			throw new \RuntimeException( "Circular dependency detected in qit.json configuration: $config_file" );
		}

		$parsed_files[] = $config_file;

		if ( ! file_exists( $config_file ) ) {
			throw new \RuntimeException( "Config file '$config_file' not found." );
		}

		$contents   = file_get_contents( $config_file );
		$raw_config = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $raw_config ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		$parsed_config = [];

		if ( isset( $raw_config['sut'] ) ) {
			$parsed_config['sut'] = App::make( SutParser::class )->parse( $raw_config['sut'], [
				'root_path' => dirname( $config_file ),
				'context'   => 'sut.source'
			] );
		}

		if ( isset( $parsed_config['sut'] ) && isset( $raw_config['environments'] ) ) {
			$this->validate_sut_consistency( $parsed_config['sut'], $raw_config['environments'] );
		}

		foreach ( $raw_config as $key => $value ) {
			if ( $key === 'extends' || $key === 'sut' ) {
				continue;
			}
			switch ( $key ) {
				case '$schema':
					$parsed_config[ $key ] = App::make( SimpleValueParser::class )->parse( $value, $key );
					break;
				case 'test_types':
					$parsed_config[ $key ] = App::make( TestParser::class )->parse( $value, $raw_config['test_packages'] ?? [] );
					break;
				case 'test_groups':
					$parsed_config[ $key ] = App::make( GroupParser::class )->parse( $value );
					break;
				case 'environments':
					$parsed_config[ $key ] = App::make( EnvironmentParser::class )->parse( $value, [
						'test_packages' => $raw_config['test_packages'] ?? [],
						'root_path'     => dirname( $config_file )
					], $parsed_config['sut'] ?? null );
					break;
				case 'test_packages':
					$parsed_config[ $key ] = App::make( CustomTestPackageParser::class )->parse( $value, [ 'root_path' => dirname( $config_file ) ] );
					break;
				default:
					throw new \RuntimeException( "Unknown configuration $key in qit.json." );
			}
		}

		if ( isset( $raw_config['extends'] ) ) {
			$base_file     = $this->resolve_extends_path( $raw_config['extends'], $config_file );
			$base_config   = $this->parse_config( $base_file, $parsed_files, false );
			$parsed_config = $this->merge_configs( $base_config, $parsed_config, $raw_config );
		}

		if ( $is_top_level && ! isset( $parsed_config['sut'] ) ) {
			throw new \RuntimeException( "SUT configuration is required." );
		}

		return $parsed_config;
	}

	protected function validate_sut_consistency( array $sut_config, array $raw_environments ): void {
		foreach ( $raw_environments as $env_name => $env_config ) {
			if ( isset( $env_config['plugins'] ) ) {
				foreach ( $env_config['plugins'] as $plugin ) {
					if ( ! is_array( $plugin ) || ! isset( $plugin['slug'] ) ) {
						continue;
					}
					if ( $plugin['slug'] === $sut_config['slug'] ) {
						if ( ! isset( $plugin['source']['type'] ) || $plugin['source']['type'] !== $sut_config['source']['type'] ) {
							throw new \RuntimeException( "SUT configuration mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'directory' && ( ! isset( $plugin['source']['path'] ) || $plugin['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'zip' && ( ! isset( $plugin['source']['path'] ) || $plugin['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for plugin '{$sut_config['slug']}'" );
						}
					}
				}
			}
			if ( isset( $env_config['themes'] ) ) {
				foreach ( $env_config['themes'] as $theme ) {
					if ( ! is_array( $theme ) || ! isset( $theme['slug'] ) ) {
						continue;
					}
					if ( $theme['slug'] === $sut_config['slug'] ) {
						if ( ! isset( $theme['source']['type'] ) || $theme['source']['type'] !== $sut_config['source']['type'] ) {
							throw new \RuntimeException( "SUT configuration mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'directory' && ( ! isset( $theme['source']['path'] ) || $theme['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
						if ( $sut_config['source']['type'] === 'zip' && ( ! isset( $theme['source']['path'] ) || $theme['source']['path'] !== $sut_config['source']['path'] ) ) {
							throw new \RuntimeException( "SUT path mismatch between main config and environment '$env_name' for theme '{$sut_config['slug']}'" );
						}
					}
				}
			}
		}
	}

	protected function resolve_extends_path( string $extends, string $current_file ): string {
		if ( ! filter_var( $extends, FILTER_VALIDATE_URL ) ) {
			$base_dir      = dirname( $current_file );
			$resolved_path = realpath( $base_dir . DIRECTORY_SEPARATOR . $extends );
			if ( $resolved_path === false ) {
				throw new \RuntimeException( "Base config file '$extends' not found." );
			}

			return $resolved_path;
		}

		try {
			$request  = new RequestBuilder( $extends );
			$contents = $request->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "Failed to fetch base config from URL '$extends'." );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'qit_base_' );
		file_put_contents( $temp_file, $contents );

		return $temp_file;
	}

	protected function merge_configs( array $base, array $child, array $child_raw ): array {
		$merged = $base;

		if ( isset( $child['sut'] ) ) {
			$merged['sut'] = $child['sut'];
		} elseif ( isset( $child_raw['sut'] ) ) {
			$merged['sut'] = App::make( SutParser::class )->parse( $child_raw['sut'], [
				'root_path' => dirname( $this->config_file ),
				'context'   => 'sut.source'
			] );
		}

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

		foreach ( $child as $key => $value ) {
			if ( $key === 'sut' ) {
				continue;
			}
			$merged[ $key ] = $value;
		}

		return $merged;
	}

	public function get_environment( string $name ): array {
		if ( ! isset( $this->parsed_config['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found." );
		}

		return $this->parsed_config['environments'][ $name ];
	}

	public function get_test_config( string $test_type, string $profile ): array {
		return $this->parsed_config['test_types'][ $test_type ][ $profile ] ?? [];
	}
}