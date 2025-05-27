<?php

namespace QIT_CLI\PreCommand\Download\Extensions;

class DependencyParser {
	/**
	 * Parses plugin metadata to extract dependencies.
	 *
	 * @param string $path Path to zip file or directory.
	 * @param string $type 'plugin' or 'theme'.
	 *
	 * @return array<string> Array of required plugin slugs.
	 * @throws \RuntimeException If parsing fails.
	 */
	public function parse( string $path, string $type ): array {
		$dependencies = [];
		if ( $type !== 'plugin' ) {
			return $dependencies;
		}

		if ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			$zip = new \ZipArchive();
			if ( $zip->open( $path ) !== true ) {
				throw new \RuntimeException( "Failed to open zip file: $path" );
			}

			for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
				$filename = $zip->getNameIndex( $i );
				if ( preg_match( '#^[^/]+/[^/]+\.php$#', $filename ) && $zip->getFromIndex( $i ) ) {
					$contents = $zip->getFromIndex( $i );
					if ( preg_match( '#Plugin Name:#', $contents ) ) {
						if ( preg_match( '#Requires Plugins:\s*([^\r\n]+)#i', $contents, $matches ) ) {
							$dependencies = array_map( 'trim', explode( ',', $matches[1] ) );
						}
						break;
					}
				}
			}
			$zip->close();
		} elseif ( is_dir( $path ) ) {
			$iterator = new \DirectoryIterator( $path );
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
		} else {
			throw new \RuntimeException( "Invalid path for dependency parsing: $path" );
		}

		return array_filter( $dependencies );
	}
}