<?php

namespace QIT_CLI\Commands\Backend;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class CurrentBackend extends QITCommand {
	protected static $defaultName = 'backend:current'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Prints the current environment.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$output->writeln( Config::get_current_manager_backend() );

		return self::SUCCESS;
	}
}
