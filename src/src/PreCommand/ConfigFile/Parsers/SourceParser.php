<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SourceParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Parsing source config: " . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source config must be an array\n", FILE_APPEND );
			throw new \RuntimeException( 'Source config must be an array.' );
		}

		if ( ! isset( $value['source_type'] ) || ! is_string( $value['source_type'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source missing source_type\n", FILE_APPEND );
			throw new \RuntimeException( 'Source must contain a "source_type" key with a string value.' );
		}

		$valid_types = [ 'build', 'directory', 'url', 'zip', 'wccom', 'wporg' ];
		if ( ! in_array( $value['source_type'], $valid_types, true ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Invalid source_type: {$value['source_type']}\n", FILE_APPEND );
			throw new \RuntimeException( "Invalid source_type '{$value['source_type']}'. Must be one of: " . implode( ', ', $valid_types ) );
		}

		switch ( $value['source_type'] ) {
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
				if ( ! isset( $value['slug'] ) || ! is_string( $value['slug'] ) || empty( $value['slug'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: {$value['source_type']} source missing slug\n", FILE_APPEND );
					throw new \RuntimeException( "{$value['source_type']} source must contain a non-empty 'slug' string." );
				}
				if ( isset( $value['version'] ) && ( ! is_string( $value['version'] ) || empty( $value['version'] ) ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: {$value['source_type']} source version invalid\n", FILE_APPEND );
					throw new \RuntimeException( "If version is provided for {$value['source_type']} source, it must be a non-empty string." );
				}
				break;
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source parsing completed\n", FILE_APPEND );

		return $value;
	}
}