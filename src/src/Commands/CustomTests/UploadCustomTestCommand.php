<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\ExtensionDownload\ExtensionZip;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadCustomTestCommand extends Command {
	protected static $defaultName = 'upload:test'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var ExtensionZip */
	protected $zip;

	public function __construct( ExtensionZip $zip ) {
		parent::__construct();
		$this->zip = $zip;
	}

	protected function configure() {
		$this
			->addArgument( 'extension', InputArgument::REQUIRED, 'The Woo extension to upload this for.' )
			->addArgument( 'test_path', InputArgument::REQUIRED, 'The path to the custom tests to upload.' )
			->addArgument( 'test_type', InputArgument::OPTIONAL, 'The test type.', 'e2e' )
			->setDescription( 'Manipulates the QIT Cache' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$extension = $input->getArgument( 'extension' );
		$test_path = $input->getArgument( 'test_path' );
		$test_type = $input->getArgument( 'test_type' );

		// We only support E2E for now.
		if ( $test_type !== 'e2e' ) {
			$output->writeln( '<error>Invalid test type.</error>' );

			return Command::FAILURE;
		}

		if ( ! file_exists( $test_path ) ) {
			$output->writeln( '<error>Test path does not exist.</error>' );

			return Command::FAILURE;
		}

		if ( is_file( $test_path ) ) {
			// If it's a file, it must be a zip.

			try {
				$this->zip->validate_zip( $test_path );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>{$e->getMessage()}</error>" );

				return Command::FAILURE;
			}

			return Command::FAILURE;
		}

		/*
		 * If it's a directory, we need to zip it, excluding disallowed files such as:
		 * - "node_modules" directories
		 * - playwright.config.js
		 * - playwright.config.ts
		 */


	}
}