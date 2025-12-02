<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\PackageType;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\get_manager_url;

class PackagePublishCommand extends QITCommand {
	protected TestPackageManifestParser $manifest_parser;
	protected Zipper $zipper;
	protected WooExtensionsList $woo_extensions_list;

	public function __construct( TestPackageManifestParser $manifest_parser, Zipper $zipper, WooExtensionsList $woo_extensions_list ) {
		parent::__construct();
		$this->manifest_parser     = $manifest_parser;
		$this->zipper              = $zipper;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setName( 'package:publish' )
			->setDescription( 'Publish a test package to QIT' )
			->setHelp(
				'Publishes a test package by reading the qit-test.json file for package details.' . "\n\n" .
				'Package identifier structure: namespace/package-name:[version]' . "\n" .
				'Example: woocommerce/e2e:latest' . "\n" .
				'  - namespace: read from qit-test.json' . "\n" .
				'  - package-name: read from qit-test.json' . "\n" .
				'  - [version]: specified as argument (you choose this now)'
			)
			->addArgument( 'path', InputArgument::REQUIRED, 'Path to directory or zip file containing qit-test.json' )
			->addArgument( 'version', InputArgument::OPTIONAL, 'Version for this release (e.g. 1.0.0, 1.0.0-beta.1, latest) [default: latest]' )
			->addOption( 'skip-validate', null, InputOption::VALUE_NONE, 'Skip manifest validation' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$io            = new SymfonyStyle( $input, $output );
		$path          = $input->getArgument( 'path' );
		$version       = $input->getArgument( 'version' );
		$skip_validate = $input->getOption( 'skip-validate' );

		// Set default version if none provided
		$version_was_provided = $version !== null;
		if ( $version === null ) {
			$version = 'latest';
		}

		/*
		---------------------------------------------------------------------
		 * Explain the workflow
		 * -------------------------------------------------------------------
		 */
		$io->title( 'Publish Test Package' );
		$io->writeln( '<comment>This command uploads your test package to the QIT registry.</comment>' );
		$io->writeln( '<comment>Package details will be read from qit-test.json</comment>' );
		$io->writeln( '' );

		try {
			/*
			---------------------------------------------------------------------
			 * Step 1: Find and read qit-test.json
			 * -------------------------------------------------------------------
			 */
			$manifest_path = $this->find_manifest_in_path( $path );
			if ( ! $manifest_path ) {
				throw new \RuntimeException( 'No qit-test.json found in the specified path. Make sure you\'re in a scaffolded test package directory.' );
			}

			$io->writeln( '<info>Reading package details from qit-test.json...</info>' );
			$manifest = $this->manifest_parser->parse( $manifest_path );

			$namespace    = $manifest->get_namespace();
			$package_name = $manifest->get_package_name();
			$test_type    = $manifest->get_test_type();

			/*
			---------------------------------------------------------------------
			 * Step 2: Validate namespace ownership
			 * -------------------------------------------------------------------
			 */
			$this->validate_namespace_maintenance( $namespace, $io );

			/*
			---------------------------------------------------------------------
			 * Step 3: Validate version format
			 * -------------------------------------------------------------------
			 */
			// Allow alphanumeric characters, dashes, underscores, and dots
			if ( ! preg_match( '/^[a-zA-Z0-9._-]+$/', $version ) ) {
				throw new \RuntimeException(
					'Version must contain only alphanumeric characters, dashes, underscores, and dots (e.g., 1.0.0, stable, rc-1, nightly_build)'
				);
			}

			/*
			---------------------------------------------------------------------
			 * Step 4: Build and display package identifier
			 * -------------------------------------------------------------------
			 */
			$package_identifier = sprintf( '%s/%s:%s', $namespace, $package_name, $version );

			$io->writeln( '' );
			$io->writeln( '<comment>Package identifier structure:</comment>' );
			$io->writeln( '  <info>namespace/package-name:[version]</info>' );
			$io->writeln( '' );
			$io->writeln( sprintf( '📦 Publishing: <info>%s</info>', $package_identifier ) );
			$io->writeln( sprintf( '   Namespace: <info>%s</info>', $namespace ) );
			$io->writeln( sprintf( '   Package name: <info>%s</info>', $package_name ) );

			// Show version with note if using default
			$version_display = $version;
			if ( $version === 'latest' && ! $version_was_provided ) {
				$version_display = sprintf( '%s <comment>(default, no version specified)</comment>', $version );
			}
			$io->writeln( sprintf( '   Version: <info>%s</info>', $version_display ) );

			// Show package type
			$package_type = $manifest->get_package_type();
			$io->writeln( sprintf( '   Package type: <info>%s</info>', $package_type ) );

			// Only show test_type for test packages
			if ( $package_type === PackageType::TEST ) {
				$io->writeln( sprintf( '   Test type: <info>%s</info>', $test_type ) );
			}

			/*
			---------------------------------------------------------------------
			 * Step 5: Confirmation (unless non-interactive)
			 * -------------------------------------------------------------------
			 */
			if ( $input->isInteractive() ) {
				$io->writeln( '' );
				$confirm_question = new ConfirmationQuestion( 'Proceed with publishing? [Y/n] ', true );
				$question_helper  = $this->getHelper( 'question' );

				if ( ! $question_helper->ask( $input, $output, $confirm_question ) ) {
					$io->writeln( 'Publishing cancelled.' );

					return 1;
				}
			}

			/*
			---------------------------------------------------------------------
			 * Step 6: Prepare zip file
			 * -------------------------------------------------------------------
			 */
			$zip_path = $this->prepare_zip( $path, $output );

			// Calculate checksum of generated zip so the Manager can validate integrity.
			$checksum = hash_file( 'sha256', $zip_path );

			/*
			---------------------------------------------------------------------
			 * Step 7: Validate manifest in zip (unless --skip-validate)
			 * -------------------------------------------------------------------
			 */
			if ( ! $skip_validate ) {
				$this->validate_manifest_in_zip( $zip_path, $namespace, $package_name, $output );
			}

			/*
			---------------------------------------------------------------------
			 * Step 8: Upload package to QIT registry (with subpackages if any)
			 * -------------------------------------------------------------------
			 */
			// Validate subpackage namespaces before upload
			if ( $manifest->has_subpackages() ) {
				$io->writeln( '<info>Validating subpackage namespaces...</info>' );
				$subpackages = $manifest->get_subpackages();
				foreach ( $subpackages as $subpackage_id => $subpackage_config ) {
					// Parse subpackage identifier
					if ( ! str_contains( $subpackage_id, '/' ) ) {
						throw new \RuntimeException( sprintf( 'Invalid subpackage identifier: %s (must be namespace/name format)', $subpackage_id ) );
					}

					[ $sub_namespace, $sub_name ] = explode( '/', $subpackage_id, 2 );

					// Validate namespace ownership for subpackage
					$this->validate_namespace_maintenance( $sub_namespace, $io );
				}
			}

			// Upload package (the server will handle subpackages from the manifest)
			// Note: force=true allows owners to replace their own packages
			$upload_result = $this->upload_to_manager( $package_identifier, $zip_path, $test_type, $package_type, true, $checksum, $output );

			/*
			---------------------------------------------------------------------
			 * Step 9: Success!
			 * -------------------------------------------------------------------
			 */
			$io->writeln( '' );
			$io->success( sprintf( 'Package published successfully: %s', $package_identifier ) );
			$io->writeln( sprintf( 'Upload ID: <info>%s</info>', $upload_result['upload_id'] ) );
			if ( isset( $upload_result['checksum'] ) ) {
				$io->writeln( sprintf( 'Checksum: <info>%s</info>', $upload_result['checksum'] ) );
			}

			if ( $manifest->has_subpackages() ) {
				$io->writeln( sprintf( 'Subpackages published: <info>%d</info>', count( $manifest->get_subpackages() ) ) );
			}

			// Clean up temporary zip if we created it
			if ( is_dir( $path ) && file_exists( $zip_path ) && $zip_path !== $path ) {
				unlink( $zip_path );
			}

			return 0;

		} catch ( \Exception $e ) {
			$io->error( "Error: {$e->getMessage()}" );

			return 1;
		}
	}

	/**
	 * Find qit-test.json in the given path (directory or zip)
	 */
	private function find_manifest_in_path( string $path ): ?string {
		if ( is_dir( $path ) ) {
			// Check root directory
			if ( file_exists( $path . '/qit-test.json' ) ) {
				return $path . '/qit-test.json';
			}

			// Check one level deep (common with downloaded archives)
			$entries = scandir( $path );
			foreach ( $entries as $entry ) {
				if ( $entry === '.' || $entry === '..' ) {
					continue;
				}

				$entry_path = $path . '/' . $entry;
				if ( is_dir( $entry_path ) && file_exists( $entry_path . '/qit-test.json' ) ) {
					return $entry_path . '/qit-test.json';
				}
			}
		} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			// Extract to temp directory to find manifest
			$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_manifest_' );
			$this->zipper->extract_zip( $path, $temp_dir );

			try {
				$manifest_path = $this->find_manifest( $temp_dir );
				if ( $manifest_path ) {
					// Copy manifest to a temp file so we can parse it after cleanup
					$temp_manifest = sys_get_temp_dir() . '/' . uniqid( 'manifest_' ) . '.json';
					copy( $manifest_path, $temp_manifest );

					return $temp_manifest;
				}
			} finally {
				$this->recursive_rmdir( $temp_dir );
			}
		}

		return null;
	}

	/**
	 * Validate that the user maintains the specified namespace.
	 */
	private function validate_namespace_maintenance( string $namespace_param, SymfonyStyle $io ): void {
		if ( ! $this->woo_extensions_list->user_maintains( $namespace_param ) ) {
			throw new \RuntimeException(
				sprintf( 'You are not a maintainer of namespace "%s". You can only publish packages under namespaces you maintain.', $namespace_param )
			);
		}

		$io->writeln( sprintf( '✓ You are a maintainer of namespace "<info>%s</info>"', $namespace_param ) );
	}

	/**
	 * Prepare zip file from directory or existing zip
	 */
	private function prepare_zip( string $path, OutputInterface $output ): string {
		if ( is_dir( $path ) ) {
			$output->writeln( "📦 Creating zip from directory: $path" );
			$zip_path = sys_get_temp_dir() . '/' . uniqid( 'qit_package_' ) . '.zip';

			// Comprehensive exclude list for test packages
			$exclude_patterns = [
				// All hidden files and directories (starting with dot)
				'.*',           // All hidden files at root
				'*/.*',         // Hidden files in subdirectories

				// QIT runtime files (should not be packaged)
				'qit.json',     // QIT runtime config

				// Version control (redundant with .* but kept for clarity)
				'.git/*',
				'.gitignore',
				'.svn/*',

				// System files (redundant with .* but kept for clarity)
				'.DS_Store',
				'Thumbs.db',
				'*.swp',
				'*~',

				// Dependencies (should be installed during test run)
				'node_modules/*',
				'*/node_modules/*',  // Nested node_modules
				'vendor/*',
				'*/vendor/*',        // Nested vendor directories

				// Test results (should be generated during test run)
				'results/*',
				'results-*/*',
				'test-results/*',
				'allure-results/*',
				'playwright-report/*',
				'coverage/*',

				// Build artifacts
				'dist/*',
				'build/*',
				'.cache/*',

				// Log files
				'*.log',
				'*.tmp',
				'logs/*',

				// IDE files (redundant with .* but kept for clarity)
				'.idea/*',
				'.vscode/*',
				'*.sublime-*',
			];

			// Count what's being excluded for informative message
			$excluded_types = [];
			if ( is_dir( $path . '/node_modules' ) ) {
				$excluded_types[] = 'node_modules';
			}
			if ( is_dir( $path . '/vendor' ) ) {
				$excluded_types[] = 'vendor';
			}
			if ( is_dir( $path . '/.git' ) ) {
				$excluded_types[] = '.git';
			}
			if ( file_exists( $path . '/qit.json' ) ) {
				$excluded_types[] = 'qit.json';
			}

			// Count hidden files/dirs
			$entries      = scandir( $path );
			$hidden_count = 0;
			foreach ( $entries as $entry ) {
				if ( $entry !== '.' && $entry !== '..' && strpos( $entry, '.' ) === 0 ) {
					++$hidden_count;
				}
			}
			if ( $hidden_count > 0 ) {
				$excluded_types[] = "$hidden_count hidden files/dirs";
			}

			// Show informative message about exclusions (non-spammy)
			if ( ! empty( $excluded_types ) ) {
				$output->writeln( sprintf( '<comment>Excluding from package: %s</comment>', implode( ', ', $excluded_types ) ) );
			}

			$this->zipper->zip_directory( $path, $zip_path, $exclude_patterns );

			// Show the size of the created zip
			if ( file_exists( $zip_path ) ) {
				$size_bytes = filesize( $zip_path );
				$size_mb    = round( $size_bytes / 1024 / 1024, 2 );

				if ( $size_mb > 10 ) {
					$output->writeln( sprintf( '<warning>⚠️  Package size: %s MB (consider excluding unnecessary files)</warning>', $size_mb ) );
				} else {
					$output->writeln( sprintf( '📦 Package size: %s MB', $size_mb ) );
				}

				// Warn if package is very large
				if ( $size_mb > 50 ) {
					$output->writeln( '<error>❌ Package is very large (>50MB). This may cause upload timeouts.</error>' );
					$output->writeln( '<comment>Common causes: node_modules, vendor, test results, or build artifacts included</comment>' );
				}
			}

			return $zip_path;
		} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			$output->writeln( "📦 Using existing zip file: $path" );

			return $path;
		} else {
			throw new \InvalidArgumentException( "Path must be a directory or zip file: $path" );
		}
	}

	/**
	 * Validate qit-test.json in the zip matches expected values
	 */
	private function validate_manifest_in_zip( string $zip_path, string $expected_namespace, string $expected_package, OutputInterface $output ): void {
		$output->writeln( '🔍 Validating manifest in package...' );

		// Extract zip to temporary directory
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_validate_' );
		$this->zipper->extract_zip( $zip_path, $temp_dir );

		try {
			// Find qit-test.json
			$manifest_path = $this->find_manifest( $temp_dir );
			if ( ! $manifest_path ) {
				throw new \RuntimeException( 'No qit-test.json found in package' );
			}

			// Parse and validate manifest
			$manifest = $this->manifest_parser->parse( $manifest_path );

			if ( $expected_namespace !== $manifest->get_namespace() ) {
				throw new \RuntimeException( "Manifest namespace '{$manifest->get_namespace()}' does not match expected '{$expected_namespace}'" );
			}

			if ( $expected_package !== $manifest->get_package_name() ) {
				throw new \RuntimeException( "Manifest package name '{$manifest->get_package_name()}' does not match expected '{$expected_package}'" );
			}

			$output->writeln( '✓ Manifest validation passed' );

		} finally {
			// Clean up temporary directory
			$this->recursive_rmdir( $temp_dir );
		}
	}

	/**
	 * Upload package to Manager endpoint
	 *
	 * @return array<string, mixed>
	 */
	private function upload_to_manager( string $package_identifier, string $zip_path, string $test_type, string $package_type, bool $force, string $checksum, OutputInterface $output ): array {
		$output->writeln( '🚀 Uploading to QIT registry...' );

		$post_data = [
			'package_id' => $package_identifier,
		];

		// Only include test_type for test packages (not utilities)
		if ( $package_type === PackageType::TEST ) {
			$post_data['test_type'] = $test_type;
		}

		if ( $force ) {
			$post_data['force'] = true;
		}

		$post_data['checksum'] = $checksum;

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v2/cli/publish-test-package' ) )
				->with_method( 'POST' )
				->with_file( 'file', $zip_path )
				->with_post_body( $post_data )
				->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'Failed to upload package: ' . $e->getMessage() );
		}

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Invalid response from upload API' );
		}

		if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
			$error_message = '';
			if ( isset( $data['message'] ) && is_string( $data['message'] ) && trim( $data['message'] ) !== '' ) {
				$error_message = $data['message'];
			}

			if ( $error_message === '' ) {
				$error_message = 'Upload failed';
			}

			throw new \RuntimeException( $error_message );
		}

		return $data;
	}

	/**
	 * Find qit-test.json in extracted package
	 */
	private function find_manifest( string $dir ): ?string {
		// Check root directory
		if ( file_exists( $dir . '/qit-test.json' ) ) {
			return $dir . '/qit-test.json';
		}

		// Check one level deep (common with GitHub archives)
		$entries = scandir( $dir );
		foreach ( $entries as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$entry_path = $dir . '/' . $entry;
			if ( is_dir( $entry_path ) && file_exists( $entry_path . '/qit-test.json' ) ) {
				return $entry_path . '/qit-test.json';
			}
		}

		return null;
	}

	/**
	 * Recursively remove directory
	 */
	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			if ( is_dir( $path ) ) {
				$this->recursive_rmdir( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}
}
