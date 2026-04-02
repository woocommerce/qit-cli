<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigSetCommand extends QITCommand {
	protected static $defaultName = 'config:set'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Set a configuration value.' )
			->setHidden( true )
			->addArgument( 'key', InputArgument::REQUIRED, 'The configuration key' )
			->addArgument( 'value', InputArgument::REQUIRED, 'The value to set' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$key   = $input->getArgument( 'key' );
		$value = $input->getArgument( 'value' );

		App::make( Config::class )->set( $key, $value );
		$output->writeln( sprintf( '<info>Set %s = %s</info>', $key, $value ) );

		return self::SUCCESS;
	}
}
