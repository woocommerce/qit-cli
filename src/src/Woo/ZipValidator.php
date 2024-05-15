<?php

namespace QIT_CLI\Woo;

use ZipArchive;

class ZipValidator {

	/**
	 * @var string[]
	 */
	protected $system_files = [
		// https://github.com/github/gitignore/blob/main/Global/Windows.gitignore.
		'Thumbs.db',
		'Thumbs.db:encryptable',
		'Desktop.ini',
		'desktop.ini',
		'ehthumbs.db',
		'ehthumbs_vista.db',
		'$RECYCLE.BIN/',
		// https://github.com/github/gitignore/blob/main/Global/Linux.gitignore.
		'~',
		'.directory',
		// https://github.com/github/gitignore/blob/main/Global/macOS.gitignore.
		'.DS_Store',
		'.AppleDouble',
		'.LSOverride',
		'.Spotlight-V100',
		'.Trashes',
		'.fseventsd',
	];

	/**
	 * @param string $filepath
	 *
	 * @return \ZipArchive
	 * @throws \RuntimeException If the file is not a valid zip file.
	 * @throws \UnexpectedValueException If the zip file is inconsistent.
	 */
	private function open_file( string $filepath ) {
		$zip    = new ZipArchive();
		$opened = $zip->open( $filepath, ZipArchive::CHECKCONS );

		if ( $opened === 21 ) {
			throw new \UnexpectedValueException( 'ZIP file is inconsistent. ZIP files generated by the Archive Utility from macOS may be the cause.' );
		}

		if ( $opened !== true ) {
			throw new \RuntimeException( 'This is not a valid ZIP file.' );
		}

		return $zip;
	}

	/**
	 * @param string $filepath
	 *
	 * @return void
	 */
	public function validate_zip( string $filepath ) {
		$zip = $this->open_file( $filepath );

		// Example (foo => foo/).
		$plugin_slug = $this->extract_slug_from_filepath( $filepath );
		$parent_dir  = strtolower( trim( trim( $plugin_slug ), '/' ) . '/' );

		$found_parent_directory = false;

		$left  = 0;
		$right = $zip->numFiles - 1; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		while ( $left <= $right ) {
			foreach ( [ $left, $right ] as $i ) {
				$info = $zip->statIndex( $i );

				if ( ! $info ) {
					throw new \RuntimeException( 'Cannot read ZIP index.', 400 );
				}

				if ( str_contains( $info['name'], '/vendor' ) || str_contains( $info['name'], '/node_modules' ) ) {
					continue;
				}

				if ( $this->is_file_invalid( $info['name'] ) ) {
					throw new \RuntimeException( sprintf( 'Invalid (%s) file/folder inside the provided ZIP file', $info['name'] ), 400 );
				}

				if ( ! $found_parent_directory && str_starts_with( strtolower( $info['name'] ), $parent_dir ) ) {
					$found_parent_directory = true;
				}
			}

			++$left;
			--$right;
		}

		$zip->close();
	}

	/**
	 * @param string $filepath
	 *
	 * @return string
	 */
	private function extract_slug_from_filepath( string $filepath ): string {
		$filename = basename( $filepath );

		return substr( $filename, 0, strpos( $filename, '.' ) );
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	protected function is_file_invalid( string $name ): bool {
		foreach ( $this->system_files as $system_file ) {
			if ( str_ends_with( $name, $system_file ) ) {
				return true;
			}
		}

		return false;
	}
}
