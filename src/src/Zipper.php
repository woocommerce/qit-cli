<?php

namespace QIT_CLI;

use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Zipper {
	/** @var OutputInterface */
	private $output;

	/** @var Docker */
	private $docker;

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
		if ( ! file_exists( '/.dockerenv' ) ) {
			if ( strpos( normalize_path( $extract_to ), Config::get_qit_dir() ) !== 0 && strpos( normalize_path( $extract_to ), normalize_path( sys_get_temp_dir() ) ) !== 0 ) {
				throw new \RuntimeException( 'Invalid directory.' );
			}
		}

		$this->validate_zip( $zip_file );

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

		$this->pull_zip();

		$args = [
			$this->docker->find_docker(),
			'run',
			'--rm',
			'-v',
			"$zip_file:/home/docker/file.zip",
			'-v',
			normalize_path( $extract_to ) . ':/home/docker/extracted',
			'automattic/qit-runner-zip:latest',
			'sh',
			'-c',
			'unzip /home/docker/file.zip -d /home/docker/extracted',
		];

		if ( Docker::should_set_user() ) {
			// After '--rm'.
			array_splice( $args, 3, 0, [ '--user', implode( ':', Docker::get_user_and_group() ) ] );
		}

		$zip_process = new Process( $args );

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

	protected function pull_zip(): void {
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( 'Pulling Docker ZIP image.' );
		}
		$pull_process = new Process( [ $this->docker->find_docker(), 'pull', 'automattic/qit-runner-zip:latest' ] );
		$pull_process->setEnv([
			'DOCKER_CLI_HINTS' => 'false',
		]);
		$pull_process->run( function ( $type, $out ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->write( 'Docker ZIP Pull: ' . $out );
			}
		} );
	}

	public function validate_zip( string $zip_file ): void {
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

		$this->pull_zip();

		$start = microtime( true );

		// Using Docker as a fallback if ZipArchive does not exist or fails.
		// Zips generated by MacOS Archive Utility can fail the consistency check but still be valid.
		$args = [
			$this->docker->find_docker(),
			'run',
			'--rm',
			'-v',
			"$zip_file:/home/docker/file.zip",
			'automattic/qit-runner-zip:latest',
			'sh',
			'-c',
			'zip -T /home/docker/file.zip',
		];

		if ( Docker::should_set_user() ) {
			// After --rm.
			array_splice( $args, 3, 0, [ '--user', implode( ':', Docker::get_user_and_group() ) ] );
		}

		$zip_process = new Process( $args );

		$zip_process->run( function ( $type, $out ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->write( 'Docker ZIP Validation: ' . $out );
			}
		} );

		if ( ! $zip_process->isSuccessful() ) {
			throw new \RuntimeException( sprintf( 'The zip file "%s" appears to be invalid. For details, re-run the command with the --verbose flag.' ) );
		}

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( sprintf( 'Docker ZIP validation of %s successful (%f seconds).', basename( $zip_file ), microtime( true ) - $start ) );
		}
	}

	/**
	 * @param string        $source_dir The path to the directory to be zipped.
	 * @param string        $output_zip_file The path to the output zip file.
	 * @param array<string> $exclude An array of files or directories (accepts wildcards) to exclude from the zip.
	 *
	 * @return void
	 */
	public function zip_directory( string $source_dir, string $output_zip_file, array $exclude = [] ): void {
		$source_dir_realpath = rtrim( realpath( $source_dir ), DIRECTORY_SEPARATOR );

		if ( ! is_dir( $source_dir_realpath ) ) {
			throw new \InvalidArgumentException( "Directory '$source_dir' not found." );
		}

		// Check if directory is empty.
		if ( count( scandir( $source_dir_realpath ) ) === 2 ) {
			throw new \InvalidArgumentException( "Directory '$source_dir' is empty." );
		}

		// Creating a temporary directory to store the zipped file.
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'zip_', true );
		if ( ! mkdir( $temp_dir, 0755, true ) ) {
			throw new \RuntimeException( 'Could not create temporary directory.' );
		}

		// Building the exclusion string for the Docker command.
		$exclude_string = '';
		foreach ( $exclude as $item ) {
			$exclude_string .= " '$item' ";
		}

		$this->pull_zip();

		$docker_command = [
			$this->docker->find_docker(),
			'run',
			'--rm',
			'-v',
			"$source_dir_realpath:/home/docker/source",
			'-v',
			"$temp_dir:/home/docker/dest",
			'automattic/qit-runner-zip:latest',
			'sh',
			'-c',
			"cd /home/docker/source && zip -r /home/docker/dest/output.zip . -x $exclude_string",
		];

		if ( Docker::should_set_user() ) {
			// After --rm.
			array_splice( $docker_command, 3, 0, [ '--user', implode( ':', Docker::get_user_and_group() ) ] );
		}

		$zip_process = new Process( $docker_command );
		$zip_process->mustRun();

		// Move the zipped file from the temp directory to the desired output location.
		rename( "$temp_dir/output.zip", $output_zip_file );

		// Clean up the temporary directory.
		rmdir( $temp_dir );
	}
}
