<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCustomTestCommand extends Command {
	protected static $defaultName = 'upload:test'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Zipper */
	protected $zipper;

	/** @var Upload */
	protected $uploader;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	public function __construct( Zipper $zipper, Upload $uploader, WooExtensionsList $woo_extensions_list ) {
		parent::__construct();
		$this->zipper              = $zipper;
		$this->uploader            = $uploader;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	protected function configure() {
		$this
			->addArgument( 'extension', InputArgument::REQUIRED, 'The Woo extension to upload this for.' )
			->addArgument( 'test_path', InputArgument::REQUIRED, 'The path to the custom tests to upload.' )
			->addArgument( 'test_type', InputArgument::OPTIONAL, 'The test type.', 'e2e' )
			->setDescription( 'Uploads your custom test to QIT.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$test_path = $input->getArgument( 'test_path' );
		$test_type = $input->getArgument( 'test_type' );

		// Early bail: We only support E2E for now.
		if ( $test_type !== 'e2e' ) {
			$output->writeln( '<error>Invalid test type.</error>' );

			return Command::FAILURE;
		}

		// Early bail: File doesn't exist.
		if ( ! file_exists( $test_path ) ) {
			$output->writeln( '<error>Test path does not exist.</error>' );

			return Command::FAILURE;
		}

		// Woo Extension ID / Slug. Bail if not found.
		if ( is_numeric( $input->getArgument( 'extension' ) ) ) {
			$extension_id = $input->getArgument( 'extension' );
		} else {
			try {
				$extension_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $input->getArgument( 'extension' ) );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		if ( is_file( $test_path ) ) {
			// If it's a file, it must be a zip.
			try {
				$this->zipper->validate_zip( $test_path );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}

			$zip_to_upload = $test_path;
		} else {
			$zip_to_upload = sys_get_temp_dir() . '/' . uniqid( 'e2e-test-' ) . '.zip';

			/*
			 * If it's a directory, we need to zip it, excluding disallowed files such as:
			 * - "node_modules" directories
			 * - playwright.config.js
			 * - playwright.config.ts
			 */
			$this->zipper->zip_directory( $test_path, $zip_to_upload, static::get_exclude_files() );
		}

		$upload_id = $this->uploader->upload_build( 'custom-test', $extension_id, $zip_to_upload, $output, $test_type );

		if ( empty( $upload_id ) ) {
			$output->writeln( '<error>Failed to upload test.</error>' );

			return Command::FAILURE;
		}

		$output->writeln( sprintf( '<info>Tests updated for extension \'%s\' successfully.</info>', $input->getArgument( 'extension' ) ) );

		return Command::SUCCESS;
	}

	public static function get_exclude_files(): array {
		return [
			'.github/*',
			'.git/*',
			'.gitignore',
			'node_modules/*',
			'playwright.config.js',
			'playwright.config.ts',
			'package-lock.json',
			'package.json',
		];
	}
}
