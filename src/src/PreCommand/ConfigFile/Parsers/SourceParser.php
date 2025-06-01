<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SourceParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Parsing source config: " . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( "Source config must be an array for {$context['context']}." );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			throw new \RuntimeException( "Source must contain a 'type' key with a string value for {$context['context']}." );
		}

		$valid_types = [ 'build', 'directory', 'url', 'zip', 'wccom', 'wporg' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			throw new \RuntimeException( "Invalid source type '{$value['type']}' for {$context['context']}. Must be one of: " . implode( ', ', $valid_types ) );
		}

		$context_name = $context['context'] ?? 'unknown';

		switch ( $value['type'] ) {
			case 'build':
				if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) || empty( $value['command'] ) ) {
					throw new \RuntimeException( "Build source must contain a non-empty \"command\" string" );
				}
				if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) || empty( $value['output'] ) ) {
					throw new \RuntimeException( "Build source must contain a non-empty 'output' string for {$context_name}." );
				}
				if ( ! preg_match( '/\.zip$/', $value['output'] ) ) {
					throw new \RuntimeException( "Build source output must be a .zip file for {$context_name}." );
				}
				break;
			case 'directory':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					throw new \RuntimeException( "Directory source must contain a non-empty 'path' string for {$context_name}." );
				}
				if ( ! is_dir( $value['path'] ) ) {
					throw new \RuntimeException( "Directory does not exist: {$value['path']}" );
				}
				break;
			case 'url':
				if ( ! isset( $value['url'] ) || ! is_string( $value['url'] ) || empty( $value['url'] ) ) {
					throw new \RuntimeException( "URL source must contain a non-empty 'url' string for {$context_name}." );
				}
				if ( ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $value['url'] ) ) {
					throw new \RuntimeException( "URL source must be a valid HTTPS URL ending in .zip for {$context_name}." );
				}
				break;
			case 'zip':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					throw new \RuntimeException( "Zip source must contain a non-empty 'path' string for {$context_name}." );
				}
				if ( ! file_exists( $value['path'] ) ) {
					throw new \RuntimeException( "Zip file does not exist: {$value['path']}" );
				}
				if ( ! preg_match( '/\.zip$/', $value['path'] ) ) {
					throw new \RuntimeException( "Zip source path must be a .zip file for {$context_name}." );
				}
				break;
			case 'wccom':
			case 'wporg':
				if ( ! isset( $context['slug'] ) || ! is_string( $context['slug'] ) || empty( $context['slug'] ) ) {
					throw new \RuntimeException( "{$value['type']} source must have a non-empty 'slug' from context for {$context_name}." );
				}
				if ( isset( $value['version'] ) && ( ! is_string( $value['version'] ) || empty( $value['version'] ) ) ) {
					throw new \RuntimeException( "If version is provided for {$value['type']} source, it must be a non-empty string for {$context_name}." );
				}
				break;
		}

		return $value;
	}
}
