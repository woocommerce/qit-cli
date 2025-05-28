<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SourceParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Source must be an array.' );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			throw new \RuntimeException( 'Source must contain a "type" key with a string value.' );
		}

		$valid_types = [ 'build', 'directory', 'url', 'zip', 'local', 'wccom', 'wporg' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			throw new \RuntimeException( "Invalid source type '{$value['type']}'. Must be one of: " . implode( ', ', $valid_types ) );
		}

		switch ( $value['type'] ) {
			case 'build':
				if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) || empty( $value['command'] ) ) {
					throw new \RuntimeException( 'Build source must contain a non-empty "command" string.' );
				}
				if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) || empty( $value['output'] ) ) {
					throw new \RuntimeException( 'Build source must contain a non-empty "output" string.' );
				}
				if ( ! preg_match( '/\.zip$/', $value['output'] ) ) {
					throw new \RuntimeException( 'Build source output must be a .zip file.' );
				}
				break;
			case 'directory':
			case 'local':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					throw new \RuntimeException( 'Directory or local source must contain a non-empty "path" string.' );
				}
				break;
			case 'url':
				if ( ! isset( $value['url'] ) || ! is_string( $value['url'] ) || empty( $value['url'] ) ) {
					throw new \RuntimeException( 'URL source must contain a non-empty "url" string.' );
				}
				if ( ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $value['url'] ) ) {
					throw new \RuntimeException( 'URL source must be a valid HTTPS URL ending in .zip.' );
				}
				break;
			case 'zip':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					throw new \RuntimeException( 'Zip source must contain a non-empty "path" string.' );
				}
				if ( ! preg_match( '/\.zip$/', $value['path'] ) ) {
					throw new \RuntimeException( 'Zip source path must be a .zip file.' );
				}
				break;
			case 'wccom':
			case 'wporg':
				if ( ! isset( $value['slug'] ) || ! is_string( $value['slug'] ) || empty( $value['slug'] ) ) {
					throw new \RuntimeException( 'wccom or wporg source must contain a non-empty "slug" string.' );
				}
				if ( isset( $value['version'] ) && ( ! is_string( $value['version'] ) || empty( $value['version'] ) ) ) {
					throw new \RuntimeException( 'If version is provided for wccom or wporg source, it must be a non-empty string.' );
				}
				break;
		}

		return $value;
	}
}