<?php

namespace QIT_CLI_Tests;

use RuntimeException;
use ZipArchive;

class ZipBuilderHelper {
	private $filename;
	private $files = array();
	/**
	 * @var string
	 */
	private $filepath;

	/**
	 * @param string $filename The name of the zip file to be created.
	 */
	public function __construct( $filename ) {
		$this->filename = $filename;
		$this->filepath = __DIR__ . "/$this->filename";
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

		if ( $zip->open( $this->get_file_path(), ZipArchive::CREATE ) !== true ) {
			throw new RuntimeException( 'Could not create zip file.' );
		}

		foreach ( $this->files as $file ) {
			if ( ! $zip->addFile( $file['source'], $file['target'] ) ) {
				throw new RuntimeException( 'Could not add file to zip: ' . $file['source'] );
			}
		}

		$zip->close();

		clearstatcache();

		if ( ! file_exists( $this->get_file_path() ) ) {
			throw new RuntimeException( 'Zip file was not created.' );
		}

		return $this->get_file_path();
	}

	function corrupt() {
		$data = file_get_contents( $this->get_file_path() );
		if ( ! $data ) {
			throw new \RuntimeException( 'Could not read zip file.' );
		}

		$data = substr_replace( $data, "", 20, 4 ); // Removing 4 bytes from offset 20

		file_put_contents( $this->get_file_path(), $data );
	}

	public function get_file_path(): string {
		return $this->filepath;
	}
}
