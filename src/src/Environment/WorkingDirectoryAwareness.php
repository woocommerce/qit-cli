<?php

namespace QIT_CLI\Environment;

class WorkingDirectoryAwareness {
	public function detect_working_directory_type(): ?string {
		if ( $this->is_plugin() ) {
			return 'plugin';
		}

		if ( $this->is_theme() ) {
			return 'theme';
		}

		return null;
	}

	public function is_plugin(): bool {
		/** @var \DirectoryIterator $file_info */
		foreach ( new \DirectoryIterator( getcwd() ) as $file_info ) {
			if ( $file_info->isFile() && $file_info->getExtension() === 'php' && ! $file_info->isLink() ) {
				/**
				 * Get the first 8kb of the file contents, similar to WordPress Core get_file_data();
				 */
				$contents = file_get_contents( $file_info->getPathname(), false, null, 0, 8 * 1024 );
				if ( stripos( $contents, 'Plugin Name:' ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	public function is_theme(): bool {
		/** @var \DirectoryIterator $file_info */
		foreach ( new \DirectoryIterator( getcwd() ) as $file_info ) {
			if ( $file_info->getBasename() === 'style.css' && ! $file_info->isLink() ) {
				$contents = file_get_contents( $file_info->getPathname(), false, null, 0, 8 * 1024 );
				if ( stripos( $contents, 'Theme Name:' ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}
}
