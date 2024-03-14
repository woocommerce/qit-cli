<?php

namespace QIT_CLI\Commands\Backend;

use QIT_CLI\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CurrentBackend extends Command {
	protected static $defaultName = 'backend:current'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Prints the current environment.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$output->writeln( Config::get_current_manager_backend() );

		return Command::SUCCESS;
	}
}
