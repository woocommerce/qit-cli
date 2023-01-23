<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchPartner extends Command {
	protected static $defaultName = 'partner:switch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Switch to another WooCommerce.com Partner.' )
			->addArgument( 'user', InputArgument::REQUIRED, 'The partner user to switch to.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$user = $input->getArgument( 'user' );

		try {
			$this->environment->switch_to_environment( "partner-$user" );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		$output->writeln( "<comment>Switched to partner '$user' successfully.</comment>" );

		return Command::SUCCESS;
	}
}
