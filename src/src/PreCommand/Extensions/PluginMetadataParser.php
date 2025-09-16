<?php

namespace QIT_CLI\PreCommand\Extensions;

/**
 * Parses plugin metadata to extract dependencies.
 */
class PluginMetadataParser {
	/**
	 * Parses plugin metadata to extract dependencies.
	 *
	 * @param string $path Path to zip file or directory.
	 * @param string $type 'plugin' or 'theme'.
	 *
	 * @return string[] Array of required plugin slugs.
	 * @throws \RuntimeException If parsing fails.
	 */
	public function parse( string $path, string $type ): array {
		$dependencies = [];
		if ( $type !== 'plugin' ) {
			return $dependencies;
		}

		if ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			$dependencies = $this->parse_zip( $path );
		} elseif ( is_dir( $path ) ) {
			$dependencies = $this->parse_directory( $path );
		} else {
			throw new \RuntimeException( "Invalid path for dependency parsing: $path" );
		}

		return array_filter( $dependencies );
	}

	/**
	 * Parse dependencies from a ZIP file.
	 *
	 * @return array<int, string>
	 */
	protected function parse_zip( string $path ): array {
		$zip = new \ZipArchive();
		if ( $zip->open( $path ) !== true ) {
			throw new \RuntimeException( "Failed to open zip file: $path" );
		}

		$dependencies = [];
		for ( $i = 0; $i < $zip->numFiles; $i++ ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$filename = $zip->getNameIndex( $i );
			if ( preg_match( '#^[^/]+/[^/]+\.php$#', $filename ) ) {
				$contents = $zip->getFromIndex( $i );
				if ( $contents && preg_match( '#Plugin Name:#', $contents ) ) {
					if ( preg_match( '#Requires Plugins:\s*([^\r\n]+)#i', $contents, $matches ) ) {
						$dependencies = array_map( 'trim', explode( ',', $matches[1] ) );
					}
					break;
				}
			}
		}
		$zip->close();

		return $dependencies;
	}

	/**
	 * Parse dependencies from a directory.
	 *
	 * @return array<int, string>
	 */
	protected function parse_directory( string $path ): array {
		$dependencies = [];
		$iterator     = new \DirectoryIterator( $path );
		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$contents = file_get_contents( $file->getPathname() );
				if ( preg_match( '#Plugin Name:#', $contents ) ) {
					if ( preg_match( '#Requires Plugins:\s*([^\r\n]+)#i', $contents, $matches ) ) {
						$dependencies = array_map( 'trim', explode( ',', $matches[1] ) );
					}
					break;
				}
			}
		}

		return $dependencies;
	}
}
