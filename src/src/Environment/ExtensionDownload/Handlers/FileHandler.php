<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionZip;

class FileHandler extends Handler {
	public function populate_extension_versions( array $extensions ): void {
		// No-op.
	}

	public function assign_handler_to_extension( string $extension_input, Extension $extension ): void {
		$extension->path = $extension_input;
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		foreach ( $extensions as $e ) {
			if ( ! empty( $e->path ) ) {
				// Extension already handled (possibly by a custom handler).
				continue;
			}

			if ( ! file_exists( $e->extension_identifier ) ) {
				throw new \RuntimeException( 'File not found: ' . $e->path );
			}

			clearstatcache( true, $e->extension_identifier );

			// Detect whether it's a WordPress Plugin or Theme.
			if ( is_dir( $e->extension_identifier ) ) {
				$this->find_type_in_dir( $e );
			} elseif ( is_file( $e->extension_identifier ) ) {
				// Must be a ".zip" file.
				if ( substr( $e->extension_identifier, - 4 ) !== '.zip' ) {
					throw new \RuntimeException( sprintf( 'When passing a local path, it must be either a directory or a ZIP file. Found: %s', $e->extension_identifier ) );
				}
				$this->find_type_in_zip( $e );
			} else {
				throw new \RuntimeException( 'Path is not a dir nor a file: ' . $e->extension_identifier );
			}

			// Basic validation, but should never happen.
			if ( empty( $e->type ) ) { // @phpstan-ignore-line
				throw new \RuntimeException( 'Could not determine the type of the extension.' );
			}

			$e->path = $e->extension_identifier;
		}
	}

	protected function find_type_in_dir( Extension $extension ): void {
		/** @var \DirectoryIterator $file */
		foreach ( new \DirectoryIterator( $extension->extension_identifier ) as $file ) {
			if ( ! $file->isFile() || $file->isDot() || $file->isLink() ) {
				continue;
			}

			// Search for 'Plugin Name:' in '.php' files.
			if ( $file->getExtension() === 'php' ) {
				$contents = file_get_contents( $file->getPathname() );
				if ( stripos( $contents, 'Plugin Name:' ) !== false ) {
					$extension->type = 'plugin';
					break;
				}
			} elseif ( $file->getExtension() === 'css' ) {
				$contents = file_get_contents( $file->getPathname() );
				if ( stripos( $contents, 'Theme Name:' ) !== false ) {
					$extension->type = 'theme';
					break;
				}
			}
		}
	}

	protected function find_type_in_zip( Extension $extension ): void {
		$tmp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit-cli-' );

		// Make tmp dir.
		if ( ! mkdir( $tmp_dir, 0755, true ) ) {
			throw new \RuntimeException( 'Could not create temporary directory: ' . $tmp_dir );
		}

		App::make( ExtensionZip::class )->extract_zip( $extension->path, $tmp_dir );
		$ext_copy                       = clone $extension;
		$ext_copy->extension_identifier = $tmp_dir;
		$this->find_type_in_dir( $ext_copy );
	}
}
