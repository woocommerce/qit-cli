<?php

namespace QIT_CLI\Environment;

use Dotenv\Dotenv;
use RuntimeException;

class EnvParser {
	/**
	 * @param array<string> $env_vars
	 * @param array<string> $env_files
	 * @return array<string, string>
	 */
	public function parse( array $env_vars = [], array $env_files = [] ): array {
		$parsed_vars = [];

		// Parse .env files
		foreach ( $env_files as $env_file ) {
			if ( ! file_exists( $env_file ) ) {
				throw new RuntimeException( sprintf( 'Environment file "%s" does not exist.', $env_file ) );
			}
			$parsed_vars = array_merge( $parsed_vars, Dotenv::parse( file_get_contents( $env_file ) ) );
		}

		// Parse CLI --env variables
		foreach ( $env_vars as $env_var ) {
			if ( ! is_string( $env_var ) ) {
				throw new RuntimeException( 'Environment variable must be a string, got ' . gettype( $env_var ) );
			}
			$parts = explode( '=', $env_var, 2 );
			if ( count( $parts ) !== 2 ) {
				throw new RuntimeException( 'Invalid environment variable format. Should be in the format "--env FOO=bar".' );
			}
			$key   = trim( $parts[0] );
			$value = trim( $parts[1] );
			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) ) {
				throw new RuntimeException( 'Invalid environment variable name. Must contain only letters, numbers, and underscores.' );
			}
			$parsed_vars[ $key ] = $value;
		}

		return $parsed_vars;
	}
}
