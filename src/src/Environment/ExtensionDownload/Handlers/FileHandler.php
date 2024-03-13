<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionZip;

class FileHandler extends Handler {
	public function maybe_download( Extension $extension, string $cache_dir, EnvInfo $env_info ): void {
		if ( ! file_exists( $extension->extension_identifier ) ) {
			throw new \RuntimeException( 'File not found: ' . $extension->path );
		}

		clearstatcache( true, $extension->extension_identifier );

		// Detect whether it's a WordPress Plugin or Theme.
		if ( is_dir( $extension->extension_identifier ) ) {
			$this->find_type_in_dir( $extension );
		} elseif ( is_file( $extension->extension_identifier ) ) {
			// Must be a ".zip" file.
			if ( ! substr( $extension->extension_identifier, - 4 ) === '.zip' ) {
				throw new \RuntimeException( sprintf( 'When passing a local path, it must be either a directory or a zip file. Found: %s', $extension->extension_identifier ) );
			}
			$this->find_type_in_zip( $extension );
		} else {
			throw new \RuntimeException( 'Path is not a dir nor a file: ' . $extension->extension_identifier );
		}

		// Basic validation, but should never happen.
		if ( empty( $extension->type ) ) {
			throw new \RuntimeException( 'Could not determine the type of the extension.' );
		}

		$extension->path = $extension->extension_identifier;
	}

	protected function find_type_in_dir( Extension $extension ): void {
		/** @var \DirectoryIterator $file */
		foreach ( new \DirectoryIterator( $extension->extension_identifier ) as $file ) {
			if ( ! $file->isFile() || $file->isDot() || $file->isLink() ) {
				continue;
			}

			// Search for 'Plugin Name:' in '.php' files
			if ( $file->getExtension() === 'php' ) {
				$contents = file_get_contents( $file->getPathname() );
				if ( strpos( $contents, 'Plugin Name:' ) !== false ) {
					$extension->type = 'plugin';
					break;
				}
			} elseif ( $file->getExtension() === 'css' ) {
				$contents = file_get_contents( $file->getPathname() );
				if ( strpos( $contents, 'Theme Name:' ) !== false ) {
					$extension->type = 'theme';
					break;
				}
			}
		}
	}

	protected function find_type_in_zip( Extension $extension ): void {
		$tmp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit-cli-' );

		// Make tmp dir.
		if ( ! mkdir( $tmp_dir, 0777, true ) ) {
			throw new \RuntimeException( 'Could not create temporary directory: ' . $tmp_dir );
		}

		App::make( ExtensionZip::class )->extract_zip( $extension->path, $tmp_dir );
		$ext_copy       = clone $extension;
		$ext_copy->path = $tmp_dir;
		$this->find_type_in_dir( $ext_copy );
	}
}