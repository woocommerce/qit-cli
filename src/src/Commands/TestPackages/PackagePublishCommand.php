<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
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
		$this
			->setName( 'package:publish' )
			->setDescription( 'Publish a test package to QIT' )
			->setHelp(
				'Publishes a test package by reading the manifest.json file for package details.' . "\n\n" .
				'Package identifier structure: namespace/package-name:[version]' . "\n" .
				'Example: woocommerce/e2e:latest' . "\n" .
				'  - namespace: read from manifest.json' . "\n" .
				'  - package-name: read from manifest.json' . "\n" .
				'  - [version]: specified as argument (you choose this now)'
			)
			->addArgument( 'path', InputArgument::REQUIRED, 'Path to directory or zip file containing manifest.json' )
			->addArgument( 'version', InputArgument::OPTIONAL, 'Version for this release (e.g. 1.0.0, 1.0.0-beta.1, latest) [default: latest]' )
			->addOption( 'force', null, InputOption::VALUE_NONE, 'Force overwrite existing package version' )
			->addOption( 'skip-validate', null, InputOption::VALUE_NONE, 'Skip manifest validation' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$io            = new SymfonyStyle( $input, $output );
		$path          = $input->getArgument( 'path' );
		$version       = $input->getArgument( 'version' );
		$force         = $input->getOption( 'force' );
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
		$io->writeln( '<comment>Package details will be read from manifest.json</comment>' );
		$io->writeln( '' );

		try {
			/*
			---------------------------------------------------------------------
			 * Step 1: Find and read manifest.json
			 * -------------------------------------------------------------------
			 */
			$manifest_path = $this->find_manifest_in_path( $path );
			if ( ! $manifest_path ) {
				throw new \RuntimeException( 'No manifest.json found in the specified path. Make sure you\'re in a scaffolded test package directory.' );
			}

			$io->writeln( '<info>Reading package details from manifest.json...</info>' );
			$manifest = $this->manifest_parser->parse( $manifest_path );

			$namespace    = $manifest->getNamespace();
			$package_name = $manifest->getPackage();
			$test_type    = $manifest->getTestType();

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
			if ( $version !== 'latest' && ! preg_match( '/^\d+\.\d+\.\d+(-.+)?$/', $version ) ) {
				throw new \RuntimeException( 'Version must be semantic version (e.g., 1.0.0, 1.0.0-beta.1) or "latest"' );
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
			$io->writeln( sprintf( '   Test type: <info>%s</info>', $test_type ) );

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
			 * Step 8: Upload to QIT registry
			 * -------------------------------------------------------------------
			 */
			$upload_result = $this->upload_to_manager( $package_identifier, $zip_path, $test_type, $force, $checksum, $output );

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
	 * Find manifest.json in the given path (directory or zip)
	 */
	private function find_manifest_in_path( string $path ): ?string {
		if ( is_dir( $path ) ) {
			// Check root directory
			if ( file_exists( $path . '/manifest.json' ) ) {
				return $path . '/manifest.json';
			}

			// Check one level deep (common with downloaded archives)
			$entries = scandir( $path );
			foreach ( $entries as $entry ) {
				if ( $entry === '.' || $entry === '..' ) {
					continue;
				}

				$entry_path = $path . '/' . $entry;
				if ( is_dir( $entry_path ) && file_exists( $entry_path . '/manifest.json' ) ) {
					return $entry_path . '/manifest.json';
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

			// Use same exclude list as custom tests
			$exclude_patterns = [
				'.git/*',
				'.gitignore',
				'.DS_Store',
				'Thumbs.db',
				'node_modules/*',
				'vendor/*',
				'*.log',
				'*.tmp',
			];

			$this->zipper->zip_directory( $path, $zip_path, $exclude_patterns );

			return $zip_path;
		} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			$output->writeln( "📦 Using existing zip file: $path" );

			return $path;
		} else {
			throw new \InvalidArgumentException( "Path must be a directory or zip file: $path" );
		}
	}

	/**
	 * Validate manifest.json in the zip matches expected values
	 */
	private function validate_manifest_in_zip( string $zip_path, string $expected_namespace, string $expected_package, OutputInterface $output ): void {
		$output->writeln( '🔍 Validating manifest in package...' );

		// Extract zip to temporary directory
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_validate_' );
		$this->zipper->extract_zip( $zip_path, $temp_dir );

		try {
			// Find manifest.json
			$manifest_path = $this->find_manifest( $temp_dir );
			if ( ! $manifest_path ) {
				throw new \RuntimeException( 'No manifest.json found in package' );
			}

			// Parse and validate manifest
			$manifest = $this->manifest_parser->parse( $manifest_path );

			if ( $expected_namespace !== $manifest->getNamespace() ) {
				throw new \RuntimeException( "Manifest namespace '{$manifest->getNamespace()}' does not match expected '{$expected_namespace}'" );
			}

			if ( $expected_package !== $manifest->getPackage() ) {
				throw new \RuntimeException( "Manifest package name '{$manifest->getPackage()}' does not match expected '{$expected_package}'" );
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
	private function upload_to_manager( string $package_identifier, string $zip_path, string $test_type, bool $force, string $checksum, OutputInterface $output ): array {
		$output->writeln( '🚀 Uploading to QIT registry...' );

		$post_data = [
			'package_id' => $package_identifier,
			'test_type'  => $test_type,
		];

		if ( $force ) {
			$post_data['force'] = true;
		}

		$post_data['checksum'] = $checksum;

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/publish-test-package' ) )
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
	 * Find manifest.json in extracted package
	 */
	private function find_manifest( string $dir ): ?string {
		// Check root directory
		if ( file_exists( $dir . '/manifest.json' ) ) {
			return $dir . '/manifest.json';
		}

		// Check one level deep (common with GitHub archives)
		$entries = scandir( $dir );
		foreach ( $entries as $entry ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$entry_path = $dir . '/' . $entry;
			if ( is_dir( $entry_path ) && file_exists( $entry_path . '/manifest.json' ) ) {
				return $entry_path . '/manifest.json';
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
