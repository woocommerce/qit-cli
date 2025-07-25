<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class PackageDownloadCommand extends QITCommand {
	protected static $defaultName = 'package:download'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private const MAX_RETRIES = 3;
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
			->setDescription( 'Download test packages from QIT registry' )
			->setHelp(
				'Downloads test packages from the QIT registry to your local machine.' . "\n\n" .
				'Package identifier structure: namespace/package-name:[version]' . "\n" .
				'Examples:' . "\n" .
				'  woocommerce/e2e:stable' . "\n" .
				'  woocommerce/e2e:1.0.0' . "\n" .
				'  my-extension/integration-tests:latest' . "\n\n" .
				'By default, packages are downloaded, extracted, and dependencies are installed automatically.' . "\n" .
				'Zip files are removed after successful extraction to keep your workspace clean.' . "\n\n" .
				'Use --no-cleanup-zips to preserve zip files after extraction.' . "\n" .
				'Use --no-extract to keep only the zip files without extracting them.'
			)
			->addArgument(
				'packages',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				'Package identifiers in namespace/package-name:version format'
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
				'no-cleanup-zips',
				null,
				InputOption::VALUE_NONE,
				'Keep zip files after extraction (cleanup by default)'
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
		$io = new SymfonyStyle( $input, $output );

		$packages     = $input->getArgument( 'packages' );
		$output_dir   = rtrim( $input->getOption( 'output-dir' ), '/' ) . '/';
		$verify       = $input->getOption( 'verify' );
		$extract      = ! $input->getOption( 'no-extract' ); // Extract by default unless --no-extract
		$install      = ! $input->getOption( 'no-install' ); // Install by default unless --no-install
		$force        = $input->getOption( 'force' );
		$format       = $input->getOption( 'format' );
		$cleanup_zips = $extract && ! $input->getOption( 'no-cleanup-zips' ); // Cleanup when extracting unless --no-cleanup-zips

		/* ---------------------------------------------------------------------
		 * Explain the workflow
		 * -------------------------------------------------------------------*/
		$io->title( 'Download Test Packages' );
		$io->writeln( '<comment>This command downloads test packages from the QIT registry.</comment>' );
		$io->writeln( '<comment>Files will be saved to your local machine.</comment>' );
		$io->writeln( '' );
		$io->writeln( '<info>Workflow:</info>' );
		$io->writeln( '  1. <info>Download</info> → Fetch package zip files from QIT registry' );
		if ( $extract ) {
			$io->writeln( '  2. <info>Extract</info> → Unzip packages to directories' );
			if ( $cleanup_zips ) {
				$io->writeln( '  3. <info>Cleanup</info> → Remove zip files after extraction (default)' );
			} else {
				$io->writeln( '  3. <info>Preserve</info> → Keep zip files after extraction (--no-cleanup-zips)' );
			}
		}
		if ( $install ) {
			$step_num = $extract ? '4' : '2';
			$io->writeln( sprintf( '  %s. <info>Install</info> → Run npm/composer install for dependencies', $step_num ) );
		}
		$io->writeln( '' );

		/* ---------------------------------------------------------------------
		 * Show package details
		 * -------------------------------------------------------------------*/
		$io->writeln( '<comment>Package identifier structure:</comment>' );
		$io->writeln( '  <info>namespace/package-name:[version]</info>' );
		$io->writeln( '' );
		$io->writeln( sprintf( '📦 Downloading <info>%d</info> package(s):', count( $packages ) ) );
		foreach ( $packages as $package ) {
			$io->writeln( sprintf( '   • <info>%s</info>', $package ) );
		}
		$io->writeln( sprintf( '📁 Output directory: <info>%s</info>', $output_dir ) );
		$io->writeln( '' );

		// Validate package format
		foreach ( $packages as $package ) {
			if ( ! $this->validate_package_id_format( $package ) ) {
				$io->error( sprintf( 'Invalid package identifier format: %s', $package ) );
				$io->writeln( 'Expected format: <info>namespace/package-name:version</info>' );
				$io->writeln( 'Example: <info>woocommerce/e2e:stable</info>' );

				return self::FAILURE;
			}
		}

		// Create output directory if it doesn't exist
		if ( ! is_dir( $output_dir ) ) {
			if ( ! mkdir( $output_dir, 0755, true ) ) {
				$io->error( sprintf( 'Failed to create output directory: %s', $output_dir ) );

				return self::FAILURE;
			}
			$io->writeln( sprintf( '✓ Created output directory: <info>%s</info>', $output_dir ) );
		}

		// Fetch download URLs from QIT Manager
		try {
			$io->writeln( '🔍 Fetching download URLs from QIT registry...' );
			$download_urls = $this->fetch_download_urls( $packages );
			$io->writeln( '✓ Download URLs retrieved' );
		} catch ( \Exception $e ) {
			$io->error( sprintf( 'Failed to fetch download URLs: %s', $e->getMessage() ) );

			return self::FAILURE;
		}

		// Download packages sequentially
		$results = $this->download_packages( $packages, $download_urls, $output_dir, $verify, $extract, $install, $force, $cleanup_zips, $output );

		// Output results
		$this->output_results( $results, $format, $output, $io );

		// Calculate proper exit code based on results
		$successful = count( array_filter( $results, function ( $result ) {
			return $result['status'] === 'success';
		} ) );
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

	/**
	 * Contact the Manager and retrieve download URLs for the requested packages.
	 *
	 * @param array<string> $packages List of package IDs.
	 *
	 * @return array<string, array<string,mixed>>
	 */
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

	/**
	 * Download all packages to the local filesystem.
	 *
	 * @param array<string> $packages Original package ID order.
	 * @param array<string, array<string,mixed>> $download_urls Map of package_id => URL metadata.
	 *
	 * @return array<string,mixed>
	 */
	private function download_packages( array $packages, array $download_urls, string $output_dir, bool $verify, bool $extract, bool $install, bool $force, bool $cleanup_zips, OutputInterface $output ): array {
		$results = [];
		$total   = count( $packages );

		$output->writeln( '⬇️  Starting downloads...' );
		$output->writeln( '' );

		foreach ( $packages as $index => $package ) {
			$current = $index + 1;
			$output->write( sprintf( '[%d/%d] <info>%s</info> ', $current, $total, $package ) );

			try {
				if ( ! isset( $download_urls[ $package ] ) ) {
					throw new \RuntimeException( 'Package not found or access denied' );
				}

				$url_info = $download_urls[ $package ];
				$result   = $this->download_single_package( $package, $url_info, $output_dir, $verify, $extract, $install, $force, $cleanup_zips );

				$file_size   = $result['size'] ?? 0;
				$size_mb_val = $file_size / 1024 / 1024;
				if ( $size_mb_val < 0.1 ) {
					$display_size = round( $file_size / 1024, 1 ) . ' KB';
				} else {
					$display_size = round( $size_mb_val, 1 ) . ' MB';
				}

				$status_parts = [];

				if ( isset( $result['extracted_to'] ) ) {
					// When extracted, show final directory status
					$dir_name       = basename( $result['extracted_to'] );
					$status_parts[] = sprintf( "✓ Ready at %s (%s)", $dir_name, $display_size );

					// Show cleanup status (only mention if non-default behavior)
					if ( isset( $result['zip_cleaned_up'] ) ) {
						// Default behavior - don't clutter output unless verbose
					} else {
						$status_parts[] = 'zip preserved';
					}
				} else {
					// When not extracted, show zip file status
					$status_parts[] = sprintf( "✓ Downloaded (%s)", $display_size );
				}

				if ( $verify && isset( $result['checksum_verified'] ) && $result['checksum_verified'] ) {
					$status_parts[] = 'checksum ✓';
				}

				if ( isset( $result['dependencies_installed'] ) && $result['dependencies_installed'] ) {
					$status_parts[] = 'deps ✓';
				}

				$output->writeln( implode( ', ', $status_parts ) );

				$results[ $package ] = [
					'status' => 'success',
					'data'   => $result,
				];
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '✗ Failed (%s)', $e->getMessage() ) );
				$results[ $package ] = [
					'status' => 'failed',
					'error'  => $e->getMessage(),
				];
			}
		}

		return $results;
	}

	/**
	 * Download a single package and return the result information.
	 *
	 * @param string $package Package identifier.
	 * @param array{url:string,checksum:string|null} $url_info Metadata for the download.
	 *
	 * @return array<string,mixed>
	 */
	private function download_single_package( string $package, array $url_info, string $output_dir, bool $verify, bool $extract, bool $install, bool $force, bool $cleanup_zips ): array {
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
			'size'          => filesize( $file_path ),
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
						// Log dependency installation failure while continuing execution.
						fwrite( STDERR, 'Dependency installation failed: ' . $e->getMessage() . PHP_EOL );
						$result['dependencies_installed'] = false;
						$result['install_error']          = $e->getMessage();
					}
				}

				// Clean up zip file after successful extraction (default behavior)
				if ( $cleanup_zips && file_exists( $file_path ) ) {
					unlink( $file_path );
					$result['zip_cleaned_up'] = true;
				}
			} catch ( \Exception $e ) {
				// If extraction fails, don't fail the entire download
				// Just log the error and continue
				fwrite( STDERR, 'Extraction failed: ' . $e->getMessage() . PHP_EOL );
			}
		}

		if ( $verify && isset( $url_info['checksum'] ) ) {
			$result['checksum_verified'] = true;
		}

		return $result;
	}

	private function generate_filename( string $package ): string {
		// Convert vendor/package:version to vendor-package-version.zip
		// Use single dash for all separators for cleaner filenames
		$safe_package = str_replace( '/', '-', $package );
		$safe_package = str_replace( ':', '-', $safe_package );

		return $safe_package . '.zip';
	}

	private function download_with_retry( string $url, string $destination ): void {
		$last_exception = null;

		for ( $attempt = 1; $attempt <= self::MAX_RETRIES; $attempt ++ ) {
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

		return strcasecmp( $actual_checksum, $expected_checksum ) === 0;
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

	/**
	 * Display download results to the user.
	 *
	 * @param array<string, array{status:string,message:string}> $results
	 */
	private function output_results( array $results, string $format, OutputInterface $output, SymfonyStyle $io ): void {
		$successful = count( array_filter( $results, function ( $result ) {
			return $result['status'] === 'success';
		} ) );
		$failed     = count( $results ) - $successful;

		$output->writeln( '' );

		if ( $failed === 0 ) {
			$io->success( sprintf( 'All %d package(s) downloaded successfully! 🎉', $successful ) );
		} elseif ( $successful === 0 ) {
			$io->error( sprintf( 'All %d package(s) failed to download', $failed ) );
		} else {
			$io->warning( sprintf( 'Mixed results: %d successful, %d failed', $successful, $failed ) );
		}

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
				$output->writeln( '<error>❌ Failed downloads:</error>' );
				foreach ( $results as $package_id => $result ) {
					if ( $result['status'] === 'failed' ) {
						$output->writeln( sprintf( '  • <info>%s</info>: %s', $package_id, $result['error'] ) );
					}
				}
			}
		}
	}
}