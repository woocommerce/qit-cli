<?php

namespace QIT_CLI_Tests;

use RuntimeException;
use ZipArchive;

class ZipBuilderHelper {
	private $filename;
	private $files = array();

	/**
	 * @param string $filename The name of the zip file to be created.
	 */
	public function __construct( $filename ) {
		$this->filename = $filename;
	}

	/**
	 * Adds a new file to the archive.
	 *
	 * @param string $source The file to be added.
	 * @param string $target The target path in the archive.
	 *
	 * @return self The current ZipBuilder instance.
	 */
	public function with_file( $source, $target ): self {
		$this->files[] = array(
			'source' => $source,
			'target' => $target,
		);

		return $this;
	}

	/**
	 * Builds the zip archive.
	 *
	 * @return string The zip file path.
	 * @throws RuntimeException If the zip file could not be created.
	 *
	 */
	public function build(): string {
		$zip = new ZipArchive();

		if ( $zip->open( __DIR__ . "/$this->filename", ZipArchive::CREATE ) !== true ) {
			throw new RuntimeException( 'Could not create zip file.' );
		}

		foreach ( $this->files as $file ) {
			if ( ! $zip->addFile( $file['source'], $file['target'] ) ) {
				throw new RuntimeException( 'Could not add file to zip: ' . $file['source'] );
			}
		}

		$zip->close();

		clearstatcache();

		if ( ! file_exists( __DIR__ . "/$this->filename" ) ) {
			throw new RuntimeException( 'Zip file was not created.' );
		}

		return __DIR__ . "/$this->filename";
	}
}
