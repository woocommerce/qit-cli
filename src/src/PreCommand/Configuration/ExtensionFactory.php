<?php

namespace QIT_CLI\PreCommand\Configuration;

use QIT_CLI\Environment\Extension;
use function QIT_CLI\debug_log;
use function QIT_CLI\debug_dump;

/**
 * Factory for creating Extension objects from configuration arrays.
 * 
 * This class handles the pure object construction logic that was previously
 * scattered in ConfigurationResolver. It converts parsed configuration arrays
 * into Extension domain objects without any side effects.
 */
final class ExtensionFactory {
	
	/**
	 * Create an Extension from a plugin configuration array.
	 *
	 * @param array<string, mixed> $config Plugin configuration
	 * @return Extension
	 */
	public function fromPluginConfig( array $config ): Extension {
		return $this->create_extension_from_config( $config, 'plugin' );
	}
	
	/**
	 * Create an Extension from a theme configuration array.
	 *
	 * @param array<string, mixed> $config Theme configuration
	 * @return Extension
	 */
	public function fromThemeConfig( array $config ): Extension {
		return $this->create_extension_from_config( $config, 'theme' );
	}
	
	/**
	 * Create an Extension for the System Under Test.
	 *
	 * @param array<string, mixed> $sut SUT configuration
	 * @return Extension
	 */
	public function forSut( array $sut ): Extension {
		debug_log( "Creating SUT extension for: {$sut['slug']} ({$sut['type']})" );
		debug_dump( $sut, 'SUT configuration' );

		$extension = new Extension( $sut['slug'], $sut['type'] );

		switch ( $sut['source']['type'] ) {
			case 'local':
				$extension->from      = 'local';
				$extension->directory = $sut['source']['resolved_path'] ?? $sut['source']['path'];
				$extension->source    = $extension->directory;
				debug_log( "SUT source: local at {$extension->directory}" );
				break;

			case 'build':
				$extension->from   = 'build';
				$extension->source = $sut['source'];
				debug_log( "SUT source: build command '{$sut['source']['command']}'" );
				break;

			case 'url':
				$extension->from   = 'url';
				$extension->source = $sut['source']['url'];
				debug_log( "SUT source: URL {$extension->source}" );
				break;

			case 'wporg':
				$extension->from    = 'wporg';
				$extension->version = $sut['source']['version'] ?? 'stable';
				debug_log( "SUT source: wporg version {$extension->version}" );
				break;

			case 'wccom':
				$extension->from    = 'wccom';
				$extension->version = $sut['source']['version'] ?? 'stable';
				debug_log( "SUT source: wccom version {$extension->version}" );
				break;

			default:
				debug_log( "Unknown SUT source type: {$sut['source']['type']}", 'error' );
		}

		$extension->priority = Extension::PRIORITY_HIGH;

		return $extension;
	}
	
	/**
	 * Create an Extension from a generic configuration array.
	 *
	 * @param array<string, mixed> $config Extension configuration
	 * @param string $type Extension type ('plugin' or 'theme')
	 * @return Extension
	 */
	private function create_extension_from_config( array $config, string $type ): Extension {
		debug_log( "Creating extension from config: {$config['slug']} ($type)" );
		debug_dump( $config, 'Extension config' );

		$extension = new Extension( $config['slug'], $type );

		if ( isset( $config['from'] ) ) {
			debug_log( "Using 'from' property: {$config['from']}" );
			$extension->from = $config['from'];

			switch ( $config['from'] ) {
				case 'wporg':
					$extension->version = $config['version'] ?? 'stable';
					debug_log( "Extension source: wporg, version: {$extension->version}" );
					break;

				case 'wccom':
					$extension->version = $config['version'] ?? 'stable';
					debug_log( "Extension source: wccom, version: {$extension->version}" );
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $config['path'];
					$extension->source    = $config['path'];
					debug_log( "Extension source: local, path: {$config['path']}" );
					$full_path = realpath( $config['path'] );
					if ( ! $full_path || ! is_dir( $full_path ) ) {
						debug_log( "WARNING: Local path does not exist or is not a directory: {$config['path']}", 'error' );
					}
					break;

				case 'url':
					$extension->source = $config['url'];
					debug_log( "Extension source: url, {$config['url']}" );
					break;

				case 'build':
					$extension->source = [
						'type'    => 'build',
						'command' => $config['command'],
						'output'  => $config['output'],
					];
					debug_log( "Extension source: build, command: {$config['command']}" );
					break;

				default:
					debug_log( "Unknown 'from' type: {$config['from']}", 'error' );
			}
		} elseif ( isset( $config['source'] ) ) {
			debug_log( "Using legacy 'source' property" );
			$source = $config['source'];

			switch ( $source['type'] ) {
				case 'wporg':
					$extension->from    = 'wporg';
					$extension->version = $source['version'] ?? 'stable';
					debug_log( "Extension source: wporg (legacy), version: {$extension->version}" );
					break;

				case 'wccom':
					$extension->from    = 'wccom';
					$extension->version = $source['version'] ?? 'stable';
					debug_log( "Extension source: wccom (legacy), version: {$extension->version}" );
					break;

				case 'local':
					$extension->from      = 'local';
					$extension->directory = $source['path'];
					$extension->source    = $source['path'];
					debug_log( "Extension source: local (legacy), path: {$source['path']}" );
					break;

				case 'url':
					$extension->from   = 'url';
					$extension->source = $source['url'];
					debug_log( "Extension source: url (legacy), {$source['url']}" );
					break;

				default:
					debug_log( "Unknown source type: {$source['type']}", 'error' );
			}
		} else {
			debug_log( "Extension config missing both 'from' and 'source' properties!", 'error' );
		}

		$extension->added_automatically = 'Added from environment configuration';

		debug_log( "Created extension: {$extension->slug} from {$extension->from}" );

		return $extension;
	}
}