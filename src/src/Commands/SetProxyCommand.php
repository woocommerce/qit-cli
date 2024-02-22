<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\ManagerBackend;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class SetProxyCommand extends Command {
	protected static $defaultName = 'proxy'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var ManagerBackend $manager_backend */
	protected $manager_backend;

	public function __construct( ManagerBackend $manager_backend ) {
		$this->manager_backend = $manager_backend;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Change the default URL that we use to connect to Automattic proxy locally.' )
			->addArgument( 'proxy_url', InputArgument::OPTIONAL, 'The URL to use to connect to the proxy.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( empty( $input->getArgument( 'proxy_url' ) ) ) {
			// Make sure devs understand this is optional.
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( '<question>The proxy comes pre-configured with default values (127.0.0.1:8080). Are you sure you want to change the URL we use to connect to the local Automattic Proxy? (y/n)</question>', false ) ) ) {
				$output->writeln( 'Operation cancelled.' );

				return Command::SUCCESS;
			}

			$proxy_url = $this->getHelper( 'question' )->ask( $input, $output, new Question( '<question>Please enter the URL to connect to the Automattic Proxy locally.</question> ' ) );
		} else {
			$proxy_url = $input->getArgument( 'proxy_url' );
		}

		Config::set_proxy_url( $proxy_url );

		$output->writeln( '<info>Changed the URL we use to connect to Automattic Proxy locally.</info>' );

		return Command::SUCCESS;
	}
}
