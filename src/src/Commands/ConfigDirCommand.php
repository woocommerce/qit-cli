<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDirCommand extends QITCommand {
	protected static $defaultName = 'qit:dir'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Prints the QIT config directory path.' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output, ?EnvInfo $env_info ): int {
		$output->writeln( Config::get_qit_dir() );
		return self::SUCCESS;
	}
}
