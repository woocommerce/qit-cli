<?php

namespace QIT_CLI\Commands\Tags;

use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadTestTagsCommand extends Command {
	protected static $defaultName = 'tag:upload'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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
			->addArgument( 'test_tag', InputArgument::REQUIRED, 'The test tag to upload, can be "my-extension", or "my-extension:my-test-tag". If test tag is not specified, it is the same as running "my-extension:default".' )
			->addArgument( 'test_path', InputArgument::REQUIRED, 'The path to the custom tests to upload.' )
			->addArgument( 'test_type', InputArgument::OPTIONAL, 'The test type.', 'e2e' )
			->setDescription( 'Uploads your custom test to QIT.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$test_path = $input->getArgument( 'test_path' );
		$test_type = $input->getArgument( 'test_type' );
		$tag       = $input->getArgument( 'test_tag' );

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

		if ( strpos( $tag, ':' ) !== false ) {
			$tag_parts = explode( ':', $tag );
			if ( count( $tag_parts ) !== 2 ) {
				$output->writeln( '<error>Invalid test tag. Expected format: slug:tag</error>' );

				return Command::FAILURE;
			}
			$extension = $tag_parts[0];
			$tag       = $tag_parts[1];
		} else {
			$extension = $tag;
			$tag       = 'default';
		}

		// Woo Extension ID / Slug. Bail if not found.
		if ( is_numeric( $extension ) ) {
			$extension_id = $extension;
		} else {
			try {
				$extension_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $extension );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		if ( is_file( $test_path ) ) {
			// If it's a file, it must be a zip. Validation is done upstream.
			try {
				$this->zipper->validate_zip( $test_path );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}

			$zip_to_upload = $test_path;
		} else {
			try {
				// If it doesn't have a "bootstrap" directory, let the user know and proceed.
				if ( ! is_dir( $test_path . '/bootstrap' ) ) {
					$output->writeln( '<comment>No "bootstrap" directory found.</comment>' );
				}

				$zip_to_upload = sys_get_temp_dir() . '/' . uniqid( 'e2e-test-' ) . '.zip';

				/*
				 * If it's a directory, we need to zip it, excluding disallowed files such as:
				 * - "node_modules" directories
				 * - playwright.config.js
				 * - playwright.config.ts
				 */
				$this->zipper->zip_directory( $test_path, $zip_to_upload, static::get_files_excluded_from_published_test_build() );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}
		}

		$upload_id = $this->uploader->upload_build( 'custom-test', $extension_id, $zip_to_upload, $output, $test_type, $tag );

		if ( empty( $upload_id ) ) {
			$output->writeln( '<error>Failed to upload test.</error>' );

			return Command::FAILURE;
		}

		$output->writeln( sprintf( '<info>Tests updated for extension \'%s\' successfully.</info>', $extension ) );

		return Command::SUCCESS;
	}

	/**
	 * @return array<string> The files to exclude when zipping a custom test to be published.
	 */
	public static function get_files_excluded_from_published_test_build(): array {
		return [
			'.github/*',
			'.git/*',
			'.gitignore',
			'node_modules/*',
			'vendor/*',
		];
	}

	/**
	 * @return array<string> The files to exclude when zipping a custom test to be used locally.
	 */
	public static function get_files_excluded_from_local_test_build(): array {
		return [
			'.github/*',
			'.git/*',
			'.gitignore',
			'node_modules/*',
			'vendor/*',
		];
	}
}
