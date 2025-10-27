<?php

namespace QIT_CLI\Commands\Backend;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\ManagerBackend;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveBackend extends QITCommand {
	protected static $defaultName = 'backend:remove'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected ManagerBackend $manager_backend;

	public function __construct( ManagerBackend $manager_backend ) {
		$this->manager_backend = $manager_backend;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Removes an environment that had been previously configured. This is a non-destructive action that does not remove any data in remote servers.' )
			->addArgument( 'environment', InputArgument::REQUIRED, 'The environment to remove.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$manager_backend = $input->getArgument( 'environment' );

		try {
			$this->manager_backend->remove_manager_backend( $manager_backend );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

			return Command::FAILURE;
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

			return Command::SUCCESS;
		}

		$output->writeln( "<info>ManagerBackend '$manager_backend' unset successfully.</info>" );

		return Command::SUCCESS;
	}
}
