<?php

namespace QIT_CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpenCommand extends Command {
	protected static $defaultName = 'open'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Open a test run result in the browser.' )
			->addArgument( 'test_run_id', InputArgument::REQUIRED );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$command = $this->getApplication()->find( GetCommand::getDefaultName() );

		return $command->run( new ArrayInput( [
			'test_run_id' => $input->getArgument( 'test_run_id' ),
			'--open'      => true,
		] ), $output );
	}
}
