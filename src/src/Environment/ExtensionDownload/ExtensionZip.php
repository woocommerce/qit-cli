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

	/** @var array<string, string> Keys are paths, values are checksum hashes. We use this to make sure we don't validate the same file on this request. */
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

		// Make sure $extract_to is within Config Dir or sys_get_temp_dir.
		if ( strpos( normalize_path( $extract_to ), Config::get_qit_dir() ) !== 0 && strpos( normalize_path( $extract_to ), normalize_path( sys_get_temp_dir() ) ) !== 0 ) {
			throw new \RuntimeException( 'Invalid directory.' );
		}

		if ( ! array_key_exists( normalize_path( $zip_file ), $this->already_validated ) || $this->already_validated[ normalize_path( $zip_file ) ] !== md5_file( $zip_file ) ) {
			$this->validate_zip( $zip_file );
		}

		$start = microtime( true );

		if ( class_exists( 'ZipArchive' ) ) {
			// Use zip from host PHP if available, as it's faster on Macs.
			$zip = new \ZipArchive();
			if ( $zip->open( $zip_file ) === true ) {
				$zip->extractTo( $extract_to );
				$zip->close();

				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( sprintf( 'ZipArchive extraction of %s successful (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
				}

				return;
			} else {
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( sprintf( 'ZipArchive extraction of %s failed, falling back to Docker (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
				}
			}
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

		$start = microtime( true );
		$zip_process->mustRun( function ( $type, $out ) {
			if ( $this->output->isVeryVerbose() ) {
				'Docker ZIP Extraction: ' . $this->output->writeln( $out );
			}
		} );
		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( sprintf( 'Docker ZIP extraction of %s successful (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
		}
	}

	public function validate_zip( string $zip_file ): void {
		// Normalize and hash the zip file path.
		$normalized_path                             = normalize_path( $zip_file );
		$this->already_validated[ $normalized_path ] = md5_file( $zip_file );

		$start = microtime( true );

		if ( class_exists( 'ZipArchive' ) ) {
			// Use zip from host PHP if available, as it's faster on Macs.
			$zip = new \ZipArchive();
			if ( $zip->open( $zip_file, \ZipArchive::CHECKCONS ) === true ) {
				$zip->close();

				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( sprintf( 'ZipArchive validation of %s successful (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
				}

				return;
			} else {
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( sprintf( 'ZipArchive validation of %s failed, falling back to Docker (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
				}
			}
		}

		$start = microtime( true );

		// Using Docker as a fallback if ZipArchive does not exist or fails.
		// Zips generated by MacOS Archive Utility can fail the consistency check but still be valid.
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
			if ( $this->output->isVeryVerbose() ) {
				$this->output->write( 'Docker ZIP Validation: ' . $out );
			}
		} );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( sprintf( 'Docker ZIP validation of %s successful (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
		}
	}
}
