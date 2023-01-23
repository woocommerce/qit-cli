<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListPartner extends Command {
	protected static $defaultName = 'partner:list'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'List the configured Partners.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$environments = $this->environment->get_configured_partners( true );

		if ( empty( $environments ) ) {
			$output->writeln( "<info>No Partners configured.</info>" );

			return Command::SUCCESS;
		}

		$output->writeln( "<info>Configured Partners:</info>" );

		foreach ( $environments as $e ) {
			$output->write( '<info>' );
			$output->write( "$e" );
			if ( "partner-$e" === $this->environment->get_current_environment() ) {
				$output->write( " (Current)" );
			}
			$output->write( '</info>' );
			$output->write( PHP_EOL );
		}

		return Command::SUCCESS;
	}
}
