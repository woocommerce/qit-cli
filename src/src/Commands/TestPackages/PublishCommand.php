<?php

namespace QIT_CLI\Commands\TestPackages;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
			->setHelp( 'Note: if you authenticate with an e‑mail address you must publish under an extension slug you maintain; personal namespaces are reserved for partner aliases.' )
			->addArgument( 'path', InputArgument::REQUIRED, 'Path to directory or zip file' )
			->addOption( 'version', null, InputOption::VALUE_REQUIRED, 'SemVer or tag for this release (e.g. 1.0.0, 1.0.0-beta.1)', null )
			->addOption( 'test-type', null, InputOption::VALUE_REQUIRED, 'Test type', 'e2e' )
			->addOption( 'force', null, InputOption::VALUE_NONE, 'Force overwrite existing package' )
			->addOption( 'skip-validate', null, InputOption::VALUE_NONE, 'Skip manifest validation' );
	}


	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$path          = $input->getArgument( 'path' );
		$version       = $input->getOption( 'version' );
		$test_type     = $input->getOption( 'test-type' );
		$force         = $input->getOption( 'force' );
		$skip_validate = $input->getOption( 'skip-validate' );

		try {
			// Step 1: Parse manifest to get vendor/package
			$manifest_path = $this->find_manifest_in_zip_or_dir( $path );
			if ( ! $manifest_path ) {
				throw new \RuntimeException( 'No manifest.json found in package' );
			}

			$manifest = $this->manifest_parser->parse( $manifest_path );

			// Step 2: Collect version (from option or interactive prompt)
			if ( ! $version ) {
				$question_helper = $this->getHelper( 'question' );
				$question = new Question( 'Version to publish [default: 1.0.0]: ', '1.0.0' );
				$version = $question_helper->ask( $input, $output, $question );
			}

			// Validate version format (simple SemVer check)
			if ( ! preg_match( '/^\d+\.\d+\.\d+(-.+)?$/', $version ) ) {
				throw new \RuntimeException( 'Version must be in SemVer format (e.g., 1.0.0, 1.0.0-beta.1)' );
			}

			// Step 3: Build reference from manifest + version
			$reference = $manifest->getVendor() . '/' . $manifest->getPackage() . ':' . $version;

			// Step 4: Handle directory or zip
			$zip_path = $this->prepare_zip( $path, $output );

			// Step 5: Resolve reference & owner
			$resolved_reference = $this->resolve_reference( $reference );
			$output->writeln( "Using reference {$resolved_reference['reference']} from manifest" );

			// Step 6: Parse and validate manifest (unless --skip-validate)
			if ( ! $skip_validate ) {
				$this->validate_manifest( $zip_path, $resolved_reference['reference'], $output );
			}

			// Step 7: Upload to Manager
			$upload_result = $this->upload_to_manager( $resolved_reference, $zip_path, $test_type, $force, $output );

			// Step 8: Surface success to user
			$output->writeln( '<info>Package published successfully!</info>' );
			$output->writeln( "Upload ID: {$upload_result['upload_id']}" );
			if ( isset( $upload_result['checksum'] ) ) {
				$output->writeln( "Checksum: {$upload_result['checksum']}" );
			}

			// Clean up temporary zip if we created it
			if ( is_dir( $path ) && file_exists( $zip_path ) ) {
				unlink( $zip_path );
			}

			return 0;

		} catch ( \Exception $e ) {
			$output->writeln( "<error>Error: {$e->getMessage()}</error>" );
			return 1;
		}
	}
	/**
	 * Resolve reference and determine owner (Woo extension or free vendor)
	 *
	 * @return array<string,mixed>
	 */
	private function resolve_reference( string $reference ): array {
		// Parse reference format: vendor/pkg:version
		if ( ! preg_match( '/^([^\/]+)\/([^:]+):(.+)$/', $reference, $matches ) ) {
			throw new \InvalidArgumentException( 'Invalid reference format. Expected: vendor/pkg:version' );
		}

		$vendor  = $matches[1];
		$package = $matches[2];
		$version = $matches[3];

		// Validate extension slug exists in the system
		try {
			$this->woo_extensions_list->get_woo_extension_id_by_slug( $vendor );
		} catch ( \UnexpectedValueException $e ) {
			throw new \InvalidArgumentException(
				"Unknown Woo extension slug '{$vendor}'. Please check the slug is correct."
			);
		}

		return [
			'reference' => $reference,
			'vendor'    => $vendor,
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
	private function validate_manifest( string $zip_path, string $reference, OutputInterface $output ): void {
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

			// Validate reference matches manifest
			$resolved_reference = $this->resolve_reference( $reference );

			if ( $resolved_reference['vendor'] !== $manifest->getVendor() ) {
				throw new \RuntimeException( "Reference vendor '{$resolved_reference['vendor']}' does not match manifest vendor '{$manifest->getVendor()}'" );
			}

			if ( $resolved_reference['package'] !== $manifest->getPackage() ) {
				throw new \RuntimeException( "Reference package '{$resolved_reference['package']}' does not match manifest package '{$manifest->getPackage()}'" );
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
	 * @param array<string,mixed> $resolved_reference
	 * @return array<string,mixed>
	 */
	private function upload_to_manager( array $resolved_reference, string $zip_path, string $test_type, bool $force, OutputInterface $output ): array {
		$output->writeln( 'Uploading to QIT Manager...' );

		$post_data = [
			'reference' => $resolved_reference['reference'],
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
