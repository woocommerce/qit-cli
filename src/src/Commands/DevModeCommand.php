<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

class DevModeCommand extends QITCommand {
	protected static $defaultName = 'dev'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Enabled QIT CLI development mode.' )
			->setHidden( true );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		if ( Config::is_development_mode() ) {
			$output->writeln( '<info>QIT CLI is already in development mode.</info>' );
			return self::SUCCESS;
		}
		Config::set_development_mode( true );
		$output->writeln( '<info>Enabled QIT CLI development mode.</info>' );
		return self::SUCCESS;
	}
}
