<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\ManagerBackend;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class RemovePartner extends QITCommand {
	protected static $defaultName = 'partner:remove'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected ManagerBackend $manager_backend;

	public function __construct( ManagerBackend $manager_backend ) {
		$this->manager_backend = $manager_backend;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Removes a partner setup that had been previously configured. This is a non-destructive action that does not remove any data in remote servers.' )
			->addArgument( 'user', InputArgument::REQUIRED, 'The partner user config to remove.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$user = $input->getArgument( 'user' );

		try {
			$this->manager_backend->remove_partner( $user );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );
			return self::FAILURE;
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );
			return self::SUCCESS;
		}

		$output->writeln( "<info>Partner config '$user' removed successfully.</info>" );

		return self::SUCCESS;
	}
}
