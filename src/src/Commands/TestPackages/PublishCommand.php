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

class PublishCommand extends QITCommand {
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
			->setHelp( 'You can only publish test packages under the namespace of extensions you maintain.' )
			->addArgument( 'path', InputArgument::REQUIRED, 'Path to directory or zip file' )
			->addArgument( 'version', InputArgument::OPTIONAL, 'Alphanumeric with dashes or dots for this release (e.g. 1.0.0, 1.0.0-beta.1, stable)', 'stable' )
			->addOption( 'test-type', null, InputOption::VALUE_REQUIRED, 'Test type', 'e2e' )
			->addOption( 'namespace', null, InputOption::VALUE_REQUIRED, 'Namespace slug you maintain (required)' )
			->addOption( 'package', null, InputOption::VALUE_REQUIRED, 'Package slug (folder name)', 'tests' )
			->addOption( 'force', null, InputOption::VALUE_NONE, 'Force overwrite existing package' )
			->addOption( 'skip-validate', null, InputOption::VALUE_NONE, 'Skip manifest validation' );
	}


	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$io             = new SymfonyStyle( $input, $output );
		$path           = $input->getArgument( 'path' );
		$version        = $input->getArgument( 'version' );
		$test_type      = $input->getOption( 'test-type' );
		$namespace_slug = $input->getOption( 'namespace' );
		$package_slug   = $input->getOption( 'package' );
		$force          = $input->getOption( 'force' );
		$skip_validate  = $input->getOption( 'skip-validate' );

		try {

			// Step 2: Get namespace slug from option or ask interactively
			if ( empty( $namespace_slug ) ) {
				$namespace_slug = $this->ask_for_namespace_slug( $input, $output, $io );
			}

			// Step 3: Validate namespace ownership (must succeed)
			$this->validate_namespace_maintenance( $namespace_slug, $io );

			// Step 4: Get package slug from option or ask interactively
			if ( empty( $package_slug ) ) {
				$package_slug = $this->ask_for_package_slug( $input, $output, $io );
			}

			// Step 5: Build package id and show confirmation
			$package_id       = $namespace_slug . '/' . $package_slug;
			$final_package_id = $package_id . ':' . $version;

			$io->writeln( '' );
			$io->writeln( sprintf( 'Package ID will be <info>%s</info>', $package_id ) );

			$confirm_question = new ConfirmationQuestion( 'Confirm? [Y/n] ', true );
			$question_helper  = $this->getHelper( 'question' );

			if ( ! $question_helper->ask( $input, $output, $confirm_question ) ) {
				$io->writeln( 'Cancelled.' );
				return 1;
			}

			// Step 6: Validate version format (alphanumeric with dashes or dots or 'stable')
			if ( $version !== 'stable' && ! preg_match( '/^\d+\.\d+\.\d+(-.+)?$/', $version ) ) {
				throw new \RuntimeException( 'Version must be alphanumeric with dashes or dots (e.g., 1.0.0, 1.0.0-beta.1) or "stable"' );
			}

			// Step 7: Handle directory or zip
			$zip_path = $this->prepare_zip( $path, $output );

			// Step 8: Manifest will be validated as-is (no patching needed)

			// Step 9: Resolve package id & owner
			$resolved_package_id = $this->resolve_package_id( $final_package_id );

			// Step 10: Parse and validate manifest (unless --skip-validate)
			if ( ! $skip_validate ) {
				$this->validate_manifest( $zip_path, $resolved_package_id['id'], $output );
			}

			// Step 11: Upload to Manager
			$upload_result = $this->upload_to_manager( $resolved_package_id, $zip_path, $test_type, $force, $output );

			// Step 12: Surface success to user
			$io->writeln( '' );
			$io->success( 'Package published successfully!' );
			$io->writeln( "Upload ID: {$upload_result['upload_id']}" );
			if ( isset( $upload_result['checksum'] ) ) {
				$io->writeln( "Checksum: {$upload_result['checksum']}" );
			}

			// Clean up temporary zip if we created it
			if ( is_dir( $path ) && file_exists( $zip_path ) ) {
				unlink( $zip_path );
			}

			return 0;

		} catch ( \Exception $e ) {
			$io->error( "Error: {$e->getMessage()}" );
			return 1;
		}
	}


	/**
	 * Ask for namespace slug with validation
	 */
	private function ask_for_namespace_slug( InputInterface $input, OutputInterface $output, SymfonyStyle $io ): string {
		$question_helper = $this->getHelper( 'question' );

		$namespace_question = new Question(
			'Namespace slug (must be one you maintain) > '
		);

		$namespace_question->setValidator( function ( $answer ) {
			// Namespace slug is required
			if ( empty( $answer ) ) {
				throw new \RuntimeException( 'Namespace slug is required. You can only publish test packages under the namespace of extensions you maintain.' );
			}

			// Validate format
			if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $answer ) ) {
				throw new \RuntimeException( 'Namespace slug must contain only letters, numbers, underscores, dots, and hyphens.' );
			}

			return trim( $answer );
		} );

		return $question_helper->ask( $input, $output, $namespace_question );
	}


	/**
	 * Validate that the user maintains the specified namespace
	 *
	 * @param string       $namespace_slug
	 * @param SymfonyStyle $io
	 * @throws \RuntimeException If namespace doesn't exist or user doesn't maintain it.
	 */
	private function validate_namespace_maintenance( string $namespace_slug, SymfonyStyle $io ): void {
		if ( ! $this->woo_extensions_list->user_maintains( $namespace_slug ) ) {
			throw new \RuntimeException(
				sprintf( 'You are not a maintainer of namespace "%s".', $namespace_slug )
			);
		}

		$io->writeln( sprintf( '✓ Found "%s"', $namespace_slug ) );
		$io->writeln( '✓ You are a maintainer of this namespace' );
	}

	/**
	 * Ask for package slug with default
	 */
	private function ask_for_package_slug( InputInterface $input, OutputInterface $output, SymfonyStyle $io ): string {
		$question_helper = $this->getHelper( 'question' );

		$package_question = new Question( 'Package slug (folder name) [tests]: ', 'tests' );

		$package_question->setValidator( function ( $answer ) {
			if ( empty( $answer ) ) {
				$answer = 'tests';
			}

			// Validate format
			if ( ! preg_match( '/^[a-zA-Z0-9_.-]+$/', $answer ) ) {
				throw new \RuntimeException( 'Package slug must contain only letters, numbers, underscores, dots, and hyphens.' );
			}

			return trim( $answer );
		} );

		return $question_helper->ask( $input, $output, $package_question );
	}


	/**
	 * Resolve package id and validate namespace slug
	 *
	 * @return array<string,mixed>
	 */
	private function resolve_package_id( string $package_id ): array {
		// Parse package id format: namespace/pkg:version
		if ( ! preg_match( '/^([^\/]+)\/([^:]+):(.+)$/', $package_id, $matches ) ) {
			throw new \InvalidArgumentException( 'Invalid package id format. Expected: namespace/pkg:version' );
		}

		$namespace = $matches[1];
		$package   = $matches[2];
		$version   = $matches[3];

		// Validate namespace slug exists in the system
		// All namespaces must be valid extension slugs
		try {
			$this->woo_extensions_list->get_woo_extension_id_by_slug( $namespace );
		} catch ( \UnexpectedValueException $e ) {
			throw new \InvalidArgumentException(
				"Unknown Woo extension slug '{$namespace}'. Please check the slug is correct."
			);
		}

		return [
			'id'        => $package_id,
			'namespace' => $namespace,
			'package'   => $package,
			'version'   => $version,
		];
	}

	/**
	 * Prepare zip file from directory or existing zip
	 */
	private function prepare_zip( string $path, OutputInterface $output ): string {
		if ( is_dir( $path ) ) {
			$output->writeln( "Zipping directory: $path" );
			$zip_path = sys_get_temp_dir() . '/' . uniqid( 'qit_package_' ) . '.zip';

			// Use same exclude list as custom tests
			$exclude_patterns = [
				'.git',
				'.gitignore',
				'.DS_Store',
				'Thumbs.db',
				'node_modules',
				'vendor',
				'*.log',
				'*.tmp',
			];

			$this->zipper->zip_directory( $path, $zip_path, $exclude_patterns );

			return $zip_path;
		} elseif ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'zip' ) {
			$output->writeln( "Using existing zip: $path" );

			return $path;
		} else {
			throw new \InvalidArgumentException( "Path must be a directory or zip file: $path" );
		}
	}

	/**
	 * Validate manifest.json in the zip
	 */
	private function validate_manifest( string $zip_path, string $package_id, OutputInterface $output ): void {
		$output->writeln( 'Validating manifest...' );

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

			// Validate package id matches manifest
			$resolved_package_id = $this->resolve_package_id( $package_id );

			if ( $resolved_package_id['namespace'] !== $manifest->getVendor() ) {
				throw new \RuntimeException( "Package ID namespace '{$resolved_package_id['namespace']}' does not match manifest namespace '{$manifest->getVendor()}'" );
			}

			if ( $resolved_package_id['package'] !== $manifest->getPackage() ) {
				throw new \RuntimeException( "Package ID package '{$resolved_package_id['package']}' does not match manifest package '{$manifest->getPackage()}'" );
			}

			$output->writeln( 'Manifest validation passed' );

		} finally {
			// Clean up temporary directory
			$this->recursive_rmdir( $temp_dir );
		}
	}

	/**
	 * Upload package to Manager endpoint
	 *
	 * @param array<string,mixed> $resolved_package_id
	 *
	 * @return array<string,mixed>
	 */
	private function upload_to_manager( array $resolved_package_id, string $zip_path, string $test_type, bool $force, OutputInterface $output ): array {
		$output->writeln( 'Uploading to QIT Manager...' );

		$post_data = [
			'reference' => $resolved_package_id['id'],
			'test_type' => $test_type,
		];

		if ( $force ) {
			$post_data['force'] = true;
		}

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/test-packages' ) )
			->with_method( 'POST' )
			->with_file( 'file', $zip_path )
			->with_post_body( $post_data )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Invalid response from upload API' );
		}

		if ( isset( $data['code'] ) && $data['code'] !== 200 ) {
			throw new \RuntimeException( $data['message'] ?? 'Upload failed' );
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

	/**
	 * Find manifest.json in directory or zip file
	 */
	private function find_manifest_in_zip_or_dir( string $path ): ?string {
		if ( is_dir( $path ) ) {
			// Check root directory
			if ( file_exists( $path . '/manifest.json' ) ) {
				return $path . '/manifest.json';
			}

			// Check one level deep
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
}
