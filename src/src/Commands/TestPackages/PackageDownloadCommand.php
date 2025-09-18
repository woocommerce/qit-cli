<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PackageDownloadCommand extends QITCommand {
	protected static $defaultName = 'package:download'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private TestPackageDownloader $package_downloader;

	public function __construct( TestPackageDownloader $package_downloader ) {
		parent::__construct();
		$this->package_downloader = $package_downloader;
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
				'./qit-tests/'
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

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io = new SymfonyStyle( $input, $output );

		/** @var array<string>|string|null $packages */
		$packages = $input->getArgument( 'packages' );
		// Ensure packages is always an array (it's defined as IS_ARRAY)
		if ( ! is_array( $packages ) ) {
			$packages = empty( $packages ) ? [] : [ $packages ];
		}
		$output_dir   = rtrim( $input->getOption( 'output-dir' ), '/' ) . '/';
		$verify       = $input->getOption( 'verify' );
		$extract      = ! $input->getOption( 'no-extract' ); // Extract by default unless --no-extract.
		$install      = ! $input->getOption( 'no-install' ); // Install by default unless --no-install.
		$force        = $input->getOption( 'force' );
		$format       = $input->getOption( 'format' );
		$cleanup_zips = $extract && ! $input->getOption( 'no-cleanup-zips' ); // Cleanup when extracting unless --no-cleanup-zips.

		/*
		---------------------------------------------------------------------
		 * Explain the workflow.
		 * -------------------------------------------------------------------
		 */
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

		/*
		---------------------------------------------------------------------
		 * Show package details.
		 * -------------------------------------------------------------------
		 */
		$io->writeln( '<comment>Package identifier structure:</comment>' );
		$io->writeln( '  <info>namespace/package-name:[version]</info>' );
		$io->writeln( '' );
		$io->writeln( sprintf( '📦 Downloading <info>%d</info> package(s):', count( $packages ) ) );
		foreach ( $packages as $package ) {
			$io->writeln( sprintf( '   • <info>%s</info>', $package ) );
		}
		$io->writeln( sprintf( '📁 Output directory: <info>%s</info>', $output_dir ) );
		$io->writeln( '' );

		// Validate package format.
		foreach ( $packages as $package ) {
			if ( ! $this->package_downloader->is_valid_package_identifier( $package ) ) {
				$io->error( sprintf( 'Invalid package identifier format: %s', $package ) );
				$io->writeln( 'Expected format: <info>namespace/package-name:version</info>' );
				$io->writeln( 'Example: <info>woocommerce/e2e:stable</info>' );

				return self::FAILURE;
			}
		}

		// Create output directory if it doesn't exist.
		if ( ! is_dir( $output_dir ) ) {
			if ( ! mkdir( $output_dir, 0755, true ) ) {
				$io->error( sprintf( 'Failed to create output directory: %s', $output_dir ) );

				return self::FAILURE;
			}
			$io->writeln( sprintf( '✓ Created output directory: <info>%s</info>', $output_dir ) );
		}

		// Fetch download URLs from QIT Manager.
		try {
			$io->writeln( '🔍 Fetching download URLs from QIT registry...' );
			$response      = $this->package_downloader->fetch_download_urls( $packages );
			$download_urls = $response['urls'];
			$io->writeln( '✓ Download URLs retrieved' );
		} catch ( \Exception $e ) {
			$io->error( sprintf( 'Failed to fetch download URLs: %s', $e->getMessage() ) );

			return self::FAILURE;
		}

		// Download packages sequentially.
		$results = $this->download_packages( $packages, $download_urls, $output_dir, $verify, $extract, $install, $force, $cleanup_zips, $output );

		// Output results.
		$this->output_results( $results, $format, $output, $io );

		// Calculate proper exit code based on results.
		$successful = count( array_filter( $results, function ( $result ) {
			return $result['status'] === 'success';
		} ) );
		$failed     = count( $results ) - $successful;

		if ( $failed === 0 ) {
			return self::SUCCESS; // 0 = all success.
		} elseif ( $failed === count( $results ) ) {
			return 2; // 2 = total failure.
		} else {
			return 1; // 1 = partial success.
		}
	}

	/**
	 * Download all packages to the local filesystem.
	 *
	 * @param array<string>                      $packages Original package ID order.
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
					// When extracted, show final directory status.
					$dir_name       = basename( $result['extracted_to'] );
					$status_parts[] = sprintf( '✓ Ready at %s (%s)', $dir_name, $display_size );

					// Show cleanup status (only mention if non-default behavior).
					if ( ! isset( $result['zip_cleaned_up'] ) ) {
						$status_parts[] = 'zip preserved';
					}
				} else {
					// When not extracted, show zip file status.
					$status_parts[] = sprintf( '✓ Downloaded (%s)', $display_size );
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
	 * @param string                                                 $package Package identifier.
	 * @param array{url:string,checksum:string|null,version?:string} $url_info Metadata for the download.
	 *
	 * @return array<string,mixed>
	 */
	private function download_single_package( string $package, array $url_info, string $output_dir, bool $verify, bool $extract, bool $install, bool $force, bool $cleanup_zips ): array {
		// Use the shared TestPackageDownloader for core processing including dependency installation and ZIP cleanup
		$result = $this->package_downloader->process_package( $package, $url_info, $output_dir, $verify, $extract, $force, $install, $cleanup_zips );

		// Handle dependency installation errors by writing to STDERR (preserve same user experience)
		if ( $install && isset( $result['install_error'] ) ) {
			fwrite( STDERR, 'Dependency installation failed: ' . $result['install_error'] . PHP_EOL );
		}

		return $result;
	}

	/**
	 * Display download results to the user.
	 *
	 * @param array<string, array{status:string,error:string}> $results
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
			$output->writeln( json_encode( $json_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
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
