<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Woo\ZipValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WooValidateZipCommand extends Command {
	protected static $defaultName = 'woo:validate-zip'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private $zip_validator;

	public function __construct( ZipValidator $zip_validator ) {
		$this->zip_validator = $zip_validator;

		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Validate a local ZIP file\'s content.' )
			->setHelp( 'If invalid content or wrong format is found in ZIP file, an error will be shown.' )
			->addArgument( 'path', InputArgument::REQUIRED, 'The ZIP file path' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		try {
			$zip_file = $input->getArgument( 'path' );

			$this->zip_validator->validate_zip( $zip_file );

			$output->writeln( '<info>Zip file content is valid.</info>' );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>An error occurred while validating the ZIP file. Error: %s</error>',
			$e->getMessage() ) );

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
