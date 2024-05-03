<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Zip;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RunValidateZipCommand extends Command {
	protected static $defaultName = 'run:validate-zip'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Run the validate-zip command.' )
			->setHelp( 'Run the validate-zip command.' )
			->addArgument( 'zip', InputArgument::REQUIRED, 'The path to the zip file.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		try {
			$zip_file = $input->getArgument( 'zip' );

			ZIP::validate_zip( $zip_file );

			$output->writeln( 'Zip file is valid.' );
		} catch ( \Exception $e ) {
			$output->writeln( 'Error: ' . $e->getMessage() );

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
