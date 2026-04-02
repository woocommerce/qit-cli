<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigGetCommand extends QITCommand {
	protected static $defaultName = 'config:get'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Get a configuration value.' )
			->setHidden( true )
			->addArgument( 'key', InputArgument::REQUIRED, 'The configuration key' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$key   = $input->getArgument( 'key' );
		$value = App::make( Config::class )->get( $key );

		if ( $value === null ) {
			$output->writeln( sprintf( '<comment>%s is not set</comment>', $key ) );
		} else {
			$output->writeln( (string) $value );
		}

		return self::SUCCESS;
	}
}
