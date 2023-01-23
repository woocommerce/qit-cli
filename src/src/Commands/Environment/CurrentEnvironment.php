<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CurrentEnvironment extends Command {
	protected static $defaultName = 'env:current'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment         = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Prints the current environment.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$output->writeln( $this->environment->get_current_environment() );

		return Command::SUCCESS;
	}
}
