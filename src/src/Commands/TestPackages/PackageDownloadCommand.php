<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class PackageDownloadCommand extends QITCommand {
	protected static $defaultName = 'package:download'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private const MAX_RETRIES      = 3;
	private const RETRY_DELAY_BASE = 1;

	private Zipper $zipper;

	public function __construct( Zipper $zipper ) {
		parent::__construct();
		$this->zipper = $zipper;
	}

	protected function configure(): void {
		parent::configure();

		$this
			->setName( 'package:download' )
			->setDescription( 'Download test packages sequentially' )
			->addArgument(
				'packages',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				'Package in vendor/package:version format'
			)
			->addOption(
				'output-dir',
				null,
				InputOption::VALUE_OPTIONAL,
				'Target directory for downloads',
				'./qit-packages/'
			)
			->addOption(
				'verify',
				null,
				InputOption::VALUE_NEGATABLE,
				'Enable checksum verification',
				true
			)
			->addOption(
				'no-extract',
				null,
				InputOption::VALUE_NONE,
				'Skip auto-extraction of packages (extracts by default)'
			)
			->addOption(
				'no-install',
				null,
				InputOption::VALUE_NONE,
				'Skip dependency installation after extraction (installs by default)'
			)
			->addOption(
				'force',
				null,
				InputOption::VALUE_NONE,
				'Overwrite existing files without prompting'
			)
			->addOption(
				'format',
				null,
				InputOption::VALUE_OPTIONAL,
				'Output format (table|json)',
				'table'
			);
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$packages   = $input->getArgument( 'packages' );
		$output_dir = rtrim( $input->getOption( 'output-dir' ), '/' ) . '/';
		$verify     = $input->getOption( 'verify' );
		$extract    = ! $input->getOption( 'no-extract' ); // Extract by default unless --no-extract
		$install    = ! $input->getOption( 'no-install' ); // Install by default unless --no-install
		$force      = $input->getOption( 'force' );
		$format     = $input->getOption( 'format' );

		// Validate package format
		foreach ( $packages as $package ) {
			if ( ! $this->validate_package_id_format( $package ) ) {
				$output->writeln( "<error>Invalid package format: $package. Expected format: namespace/package:version</error>" );
				return self::FAILURE;
			}
		}

		// Create output directory if it doesn't exist
		if ( ! is_dir( $output_dir ) ) {
			if ( ! mkdir( $output_dir, 0755, true ) ) {
				$output->writeln( "<error>Failed to create output directory: $output_dir</error>" );
				return self::FAILURE;
			}
		}

		// Fetch download URLs from QIT Manager
		try {
			$download_urls = $this->fetch_download_urls( $packages );
		} catch ( \Exception $e ) {
			$output->writeln( "<error>Failed to fetch download URLs: {$e->getMessage()}</error>" );
			return self::FAILURE;
		}

		// Download packages sequentially
		$results = $this->download_packages( $packages, $download_urls, $output_dir, $verify, $extract, $install, $force, $output );

		// Output results
		$this->output_results( $results, $format, $output );

		// Calculate proper exit code based on results
		$successful = count( array_filter( $results, fn( $result ) => $result['status'] === 'success' ) );
		$failed     = count( $results ) - $successful;

		if ( $failed === 0 ) {
			return self::SUCCESS; // 0 = all success
		} elseif ( $failed === count( $results ) ) {
			return 2; // 2 = total failure
		} else {
			return 1; // 1 = partial success
		}
	}

	private function validate_package_id_format( string $package_id ): bool {
		return preg_match( '/^[a-zA-Z0-9_.-]+\/[a-zA-Z0-9_.-]+:[a-zA-Z0-9_.-]+$/', $package_id ) === 1;
	}

	private function fetch_download_urls( array $packages ): array {
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'package_ids' => $packages,
			] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || ! isset( $data['urls'] ) ) {
			throw new \RuntimeException( 'Invalid response from package download API' );
		}

		return $data['urls'];
	}

	private function download_packages( array $packages, array $download_urls, string $output_dir, bool $verify, bool $extract, bool $install, bool $force, OutputInterface $output ): array {
		$results = [];
		$total   = count( $packages );

		$output->writeln( 'Downloading packages...' );

		foreach ( $packages as $index => $package ) {
			$current = $index + 1;
			$output->write( "[$current/$total] $package " );

			try {
				if ( ! isset( $download_urls[ $package ] ) ) {
					throw new \RuntimeException( 'Package not found or access denied' );
				}

				$url_info = $download_urls[ $package ];
				$result   = $this->download_single_package( $package, $url_info, $output_dir, $verify, $extract, $install, $force );

				$size_mb = round( ( $result['size'] ?? 0 ) / 1024 / 1024, 1 );
				$output->writeln( "✓ Downloaded ({$size_mb} MB)" );

				$results[ $package ] = [
					'status' => 'success',
					'data'   => $result,
				];
			} catch ( \Exception $e ) {
				$output->writeln( "✗ Failed ({$e->getMessage()})" );
				$results[ $package ] = [
					'status' => 'failed',
					'error'  => $e->getMessage(),
				];
			}
		}

		return $results;
	}

	private function download_single_package( string $package, array $url_info, string $output_dir, bool $verify, bool $extract, bool $install, bool $force ): array {
		$filename  = $this->generate_filename( $package );
		$file_path = $output_dir . $filename;

		// Check if file exists and handle force flag
		if ( file_exists( $file_path ) && ! $force ) {
			throw new \RuntimeException( 'File already exists (use --force to overwrite)' );
		}

		// Download with retry logic
		$this->download_with_retry( $url_info['url'], $file_path );

		// Verify checksum if enabled
		if ( $verify && isset( $url_info['checksum'] ) ) {
			if ( ! $this->verify_checksum( $file_path, $url_info['checksum'] ) ) {
				unlink( $file_path ); // Clean up failed download
				throw new \RuntimeException( 'Checksum verification failed' );
			}
		}

		$result = [
			'package'       => $package,
			'downloaded_to' => $file_path,
			'size'          => $url_info['size'] ?? filesize( $file_path ),
			'checksum'      => $url_info['checksum'] ?? null,
			'version'       => $url_info['version'] ?? 'unknown',
		];

		// Extract if requested
		if ( $extract ) {
			// Extract to output directory using new Zipper whitelist functionality
			$extract_dir = rtrim( $output_dir, '/' ) . '/' . pathinfo( $filename, PATHINFO_FILENAME );
			$this->zipper->allow_extract_into( [ $output_dir ] );
			try {
				$this->extract_package( $file_path, $extract_dir, $force );
				$result['extracted_to'] = $extract_dir;

				// Install dependencies if requested
				if ( $install ) {
					try {
						$this->install_dependencies( $extract_dir );
						$result['dependencies_installed'] = true;
					} catch ( \Exception $e ) {
						// If installation fails, don't fail the entire download
						// Just log the error and continue
						error_log( 'Dependency installation failed: ' . $e->getMessage() );
						$result['dependencies_installed'] = false;
						$result['install_error']          = $e->getMessage();
					}
				}
			} catch ( \Exception $e ) {
				// If extraction fails, don't fail the entire download
				// Just log the error and continue
				error_log( 'Extraction failed: ' . $e->getMessage() );
			}
		}

		return $result;
	}

	private function generate_filename( string $package ): string {
		// Convert vendor/package:version to vendor-package__version.zip
		// Use __ for version separator to prevent collisions with dashes in vendor names
		$safe_package = str_replace( '/', '-', $package );
		$safe_package = str_replace( ':', '__', $safe_package );
		return $safe_package . '.zip';
	}

	private function download_with_retry( string $url, string $destination ): void {
		$last_exception = null;

		for ( $attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++ ) {
			try {
				RequestBuilder::download_file( $url, $destination );
				return; // Success
			} catch ( \Exception $e ) {
				$last_exception = $e;

				if ( $attempt < self::MAX_RETRIES ) {
					$delay = self::RETRY_DELAY_BASE * pow( 2, $attempt - 1 ); // 1s, 2s, 4s
					sleep( $delay );
				}
			}
		}

		throw new \RuntimeException( 'Download failed after ' . self::MAX_RETRIES . ' attempts: ' . $last_exception->getMessage() );
	}

	private function verify_checksum( string $file_path, string $expected_checksum ): bool {
		$actual_checksum = hash_file( 'sha256', $file_path );
		return $actual_checksum === $expected_checksum;
	}

	private function extract_package( string $zip_path, string $extract_dir, bool $force ): void {
		$this->zipper->validate_zip( $zip_path );

		// Only remove existing directory if force flag is set
		if ( is_dir( $extract_dir ) ) {
			if ( $force ) {
				$this->recursive_rmdir( $extract_dir );
			} else {
				throw new \RuntimeException( 'Extract directory already exists (use --force to overwrite)' );
			}
		}

		$this->zipper->extract_zip( $zip_path, $extract_dir );
	}

	private function install_dependencies( string $extract_dir ): void {
		$installed_something = false;

		// Check for package.json and run appropriate npm command
		if ( file_exists( $extract_dir . '/package.json' ) ) {
			// Use npm ci if package-lock.json exists, otherwise use npm install
			$use_ci          = file_exists( $extract_dir . '/package-lock.json' );
			$npm_command     = 'cd ' . escapeshellarg( $extract_dir ) . ' && npm ' . ( $use_ci ? 'ci' : 'install' );
			$npm_output      = [];
			$npm_return_code = 0;

			exec( $npm_command . ' 2>&1', $npm_output, $npm_return_code );

			if ( $npm_return_code !== 0 ) {
				$command_used = $use_ci ? 'npm ci' : 'npm install';
				throw new \RuntimeException( $command_used . ' failed: ' . implode( "\n", $npm_output ) );
			}

			$installed_something = true;
		}

		// Check for composer.json and run composer install
		if ( file_exists( $extract_dir . '/composer.json' ) ) {
			$composer             = escapeshellcmd( 'composer' );
			$composer_command     = 'cd ' . escapeshellarg( $extract_dir ) . ' && ' . $composer . ' install --no-dev --optimize-autoloader';
			$composer_output      = [];
			$composer_return_code = 0;

			exec( $composer_command . ' 2>&1', $composer_output, $composer_return_code );

			if ( $composer_return_code !== 0 ) {
				throw new \RuntimeException( 'composer install failed: ' . implode( "\n", $composer_output ) );
			}

			$installed_something = true;
		}

		if ( ! $installed_something ) {
			// No package.json or composer.json found, nothing to install
			return;
		}
	}

	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursive_rmdir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}

	private function output_results( array $results, string $format, OutputInterface $output ): void {
		$successful = count( array_filter( $results, fn( $result ) => $result['status'] === 'success' ) );
		$failed     = count( $results ) - $successful;

		$output->writeln( '' );
		$output->writeln( "Summary: $successful successful, $failed failed" );

		if ( $format === 'json' ) {
			$json_output = [
				'success' => $failed === 0,
				'summary' => [
					'requested'  => count( $results ),
					'successful' => $successful,
					'failed'     => $failed,
				],
				'results' => $results,
			];
			$output->writeln( json_encode( $json_output, JSON_PRETTY_PRINT ) );
		} else {
			// Table format (default)
			if ( $failed > 0 ) {
				$output->writeln( '' );
				$output->writeln( '<error>Failed downloads:</error>' );
				foreach ( $results as $package_id => $result ) {
					if ( $result['status'] === 'failed' ) {
						$output->writeln( "  - $package_id: {$result['error']}" );
					}
				}
			}
		}
	}
}
