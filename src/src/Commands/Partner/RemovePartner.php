<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemovePartner extends Command {
	protected static $defaultName = 'partner:remove'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Removes a partner setup that had been previously configured. This is a non-destructive action that does not remove any data in remote servers.' )
			->addArgument( 'user', InputArgument::REQUIRED, 'The partner user config to remove.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$user = $input->getArgument( 'user' );

		try {
			$this->environment->remove_partner( $user );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

			return Command::FAILURE;
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

			return Command::SUCCESS;
		}

		$output->writeln( "<info>Partner config '$user' removed successfully.</info>" );

		return Command::SUCCESS;
	}
}
