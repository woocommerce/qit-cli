<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SourceParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Parsing source config: " . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source config must be an array for {$context['context']}\n", FILE_APPEND );
			throw new \RuntimeException( "Source config must be an array for {$context['context']}." );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source missing type for {$context['context']}\n", FILE_APPEND );
			throw new \RuntimeException( "Source must contain a 'type' key with a string value for {$context['context']}." );
		}

		$valid_types = [ 'build', 'directory', 'url', 'zip', 'wccom', 'wporg' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Invalid source type: {$value['type']} for {$context['context']}\n", FILE_APPEND );
			throw new \RuntimeException( "Invalid source type '{$value['type']}' for {$context['context']}. Must be one of: " . implode( ', ', $valid_types ) );
		}

		$context_name = $context['context'] ?? 'unknown';
		$root_path    = $context['root_path'] ?? getcwd();

		switch ( $value['type'] ) {
			case 'build':
				if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) || empty( $value['command'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Build source missing command for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "Build source must contain a non-empty \"command\" string" );
				}
				if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) || empty( $value['output'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Build source missing output for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "Build source must contain a non-empty 'output' string for {$context_name}." );
				}
				if ( ! preg_match( '/\.zip$/', $value['output'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Build source output must be a .zip file for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "Build source output must be a .zip file for {$context_name}." );
				}
				break;
			case 'directory':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Directory source missing path for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "Directory source must contain a non-empty 'path' string for {$context_name}." );
				}
				// Handle absolute vs relative paths
				$full_path = $value['path'];
				if ( ! str_starts_with( $value['path'], '/' ) ) {
					$full_path = $root_path . DIRECTORY_SEPARATOR . ltrim( $value['path'], './' );
				}
				if ( ! is_dir( $full_path ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Directory does not exist for {$context_name}: {$value['path']} (resolved as $full_path, base directory: $root_path)\n", FILE_APPEND );
					throw new \RuntimeException( "Directory does not exist: {$value['path']}" );
				}
				break;
			case 'url':
				if ( ! isset( $value['url'] ) || ! is_string( $value['url'] ) || empty( $value['url'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: URL source missing url for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "URL source must contain a non-empty 'url' string for {$context_name}." );
				}
				if ( ! preg_match( '/^https?:\/\/.+\/.+\.zip$/', $value['url'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: URL source must be a valid HTTPS URL for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "URL source must be a valid HTTPS URL ending in .zip for {$context_name}." );
				}
				break;
			case 'zip':
				if ( ! isset( $value['path'] ) || ! is_string( $value['path'] ) || empty( $value['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Zip source missing path for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "Zip source must contain a non-empty 'path' string for {$context_name}." );
				}
				if ( ! preg_match( '/\.zip$/', $value['path'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Zip source path must be a .zip file for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "Zip source path must be a .zip file for {$context_name}." );
				}
				break;
			case 'wccom':
			case 'wporg':
				if ( ! isset( $context['slug'] ) || ! is_string( $context['slug'] ) || empty( $context['slug'] ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: {$value['type']} source missing slug for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "{$value['type']} source must have a non-empty 'slug' from context for {$context_name}." );
				}
				if ( isset( $value['version'] ) && ( ! is_string( $value['version'] ) || empty( $value['version'] ) ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: {$value['type']} source version invalid for {$context_name}\n", FILE_APPEND );
					throw new \RuntimeException( "If version is provided for {$value['type']} source, it must be a non-empty string for {$context_name}." );
				}
				break;
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "SourceParser: Source parsing completed for {$context_name}\n", FILE_APPEND );

		return $value;
	}
}
