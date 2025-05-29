<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SourceParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Parsing source config: " . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source config must be an array\n", FILE_APPEND );
			throw new \RuntimeException( 'Source config must be an array.' );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source missing type\n", FILE_APPEND );
			throw new \RuntimeException( 'Source must contain a "type" key with a string value.' );
		}

		$valid_types = [ 'build', 'directory', 'url', 'zip', 'wccom', 'wporg' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Invalid source type: {$value['type']}\n", FILE_APPEND );
			throw new \RuntimeException( "Invalid source type '{$value['type']}'. Must be one of: " . implode( ', ', $valid_types ) );
		}

		switch ( $value['type'] ) {
			case 'build':
				if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) || empty( $value['command'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Build source missing command\n", FILE_APPEND );
					throw new \RuntimeException( 'Build source must contain a non-empty "command" string.' );
				}
				if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) || empty( $value['output'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Build source missing output\n", FILE_APPEND );
					throw new \RuntimeException( 'Build source must contain a non-empty "output" string.' );
				}
				if ( ! preg_match( '/\.zip$/', $value['output'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Build source output must be a .zip file\n", FILE_APPEND );
					throw new \RuntimeException( 'Build source output must be a .zip file.' );
				}
				break;
			case 'directory':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Directory source missing path\n", FILE_APPEND );
					throw new \RuntimeException( 'Directory source must contain a non-empty "path" string.' );
				}
				break;
			case 'url':
				if ( ! isset( $value['url'] ) || ! is_string( $value['url'] ) || empty( $value['url'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: URL source missing url\n", FILE_APPEND );
					throw new \RuntimeException( 'URL source must contain a non-empty "url" string.' );
				}
				if ( ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $value['url'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: URL source must be a valid HTTPS URL\n", FILE_APPEND );
					throw new \RuntimeException( 'URL source must be a valid HTTPS URL ending in .zip.' );
				}
				break;
			case 'zip':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Zip source missing path\n", FILE_APPEND );
					throw new \RuntimeException( 'Zip source must contain a non-empty "path" string.' );
				}
				if ( ! preg_match( '/\.zip$/', $value['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Zip source path must be a .zip file\n", FILE_APPEND );
					throw new \RuntimeException( 'Zip source path must be a .zip file.' );
				}
				break;
			case 'wccom':
			case 'wporg':
				if ( ! isset( $context['slug'] ) || ! is_string( $context['slug'] ) || empty( $context['slug'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: {$value['type']} source missing slug\n", FILE_APPEND );
					throw new \RuntimeException( "{$value['type']} source must have a non-empty 'slug' from context." );
				}
				if ( isset( $value['version'] ) && ( ! is_string( $value['version'] ) || empty( $value['version'] ) ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: {$value['type']} source version invalid\n", FILE_APPEND );
					throw new \RuntimeException( "If version is provided for {$value['type']} source, it must be a non-empty string." );
				}
				break;
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source parsing completed\n", FILE_APPEND );

		return $value;
	}
}