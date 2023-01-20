<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchEnvironment extends Command {
	protected static $defaultName = 'environment:switch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Switch to another QIT CLI environment.' )
			->addArgument( 'environment', InputArgument::REQUIRED, 'The environment to switch to.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$environment = $input->getArgument( 'environment' );

		try {
			$this->environment->switch_to_environment( $environment );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		$output->writeln( "<comment>Switched to environment '$environment' successfully.</comment>" );

		return Command::SUCCESS;
	}
}
