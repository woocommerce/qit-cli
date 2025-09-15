<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDirCommand extends QITCommand {
	protected static $defaultName = 'qit:dir'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Prints the QIT config directory path.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$output->writeln( Config::get_qit_dir() );
		return self::SUCCESS;
	}
}
