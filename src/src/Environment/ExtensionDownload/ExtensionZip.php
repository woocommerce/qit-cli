<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\normalize_path;

class ExtensionZip {
	/** @var OutputInterface */
	private $output;

	/** @var Docker */
	private $docker;

	private $already_validated = [];

	public function __construct( OutputInterface $output, Docker $docker ) {
		$this->output = $output;
		$this->docker = $docker;
	}

	public function extract_zip( string $zip_file, string $extract_to ): void {
		if ( ! file_exists( $zip_file ) ) {
			throw new \InvalidArgumentException( 'Zip file does not exist.' );
		}

		if ( ! file_exists( $extract_to ) ) {
			if ( ! mkdir( $extract_to, 0755, true ) ) {
				throw new \RuntimeException( 'Could not create directory.' );
			}
		}

		// Make sure $extract_to is within Config Dir.
		if ( strpos( normalize_path( $extract_to ), Config::get_qit_dir() ) !== 0 ) {
			throw new \RuntimeException( 'Invalid directory.' );
		}

		if ( ! array_key_exists( normalize_path( $zip_file ), $this->already_validated ) || $this->already_validated[ normalize_path( $zip_file ) ] !== md5_file( $zip_file ) ) {
			$this->validate_zip( $zip_file );
		}

		$zip_process = new Process( [
			$this->docker->find_docker(),
			'run',
			'--rm',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-v',
			"$zip_file:/app/file.zip",
			'-v',
			normalize_path( $extract_to ) . ':/app/extracted',
			'joshkeegan/zip:latest',
			'sh',
			'-c',
			'unzip /app/file.zip -d /app/extracted',
		] );

		$zip_process->mustRun( function ( $type, $out ) {
			if ( $this->output->isVerbose() ) {
				'ZIP Extraction: ' . $this->output->write( $out );
			}
		} );
	}

	public function validate_zip( string $zip_file ): void {
		// Prevent printing "test of $zip_file OK" by validating once.
		$this->already_validated[ normalize_path( $zip_file ) ] = md5_file( $zip_file );

		$zip_process = new Process( [
			$this->docker->find_docker(),
			'run',
			'--rm',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-v',
			"$zip_file:/app/file.zip",
			'joshkeegan/zip:latest',
			'sh',
			'-c',
			'zip -T /app/file.zip',
		] );

		$zip_process->mustRun( function ( $type, $out ) {
			if ( $this->output->isVerbose() ) {
				'ZIP Validation: ' . $this->output->write( $out );
			}
		} );
	}
}