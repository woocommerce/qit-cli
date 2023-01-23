<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnsetEnvironment extends Command {
	protected static $defaultName = 'env:unset'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Unsets an environment that had been previously set-up. This is a non-destructive action that does not remove any data in remote servers.' )
			->addArgument( 'environment', InputArgument::REQUIRED, 'The environment to unset.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$environment = $input->getArgument( 'environment' );

		try {
			$this->environment->unset_environment( $environment );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<info>%s</info>', $e->getMessage() ) );

			return Command::SUCCESS;
		}

		$output->writeln( "<comment>Environment '$environment' unset successfully.</comment>" );

		return Command::SUCCESS;
	}
}
