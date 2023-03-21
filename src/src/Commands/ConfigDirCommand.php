<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDirCommand extends Command {
	protected static $defaultName = 'qit:dir'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Prints the QIT config directory path.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$output->writeln( Config::get_qit_dir() );

		return Command::SUCCESS;
	}
}
