<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SetProxyCommand extends Command {
	protected static $defaultName = 'change_proxy'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Change the default URL that we use to connect to Automattic proxy locally.' )
			->addArgument( 'proxy_url', InputArgument::OPTIONAL, 'The URL to use to connect to the proxy.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( empty( $input->getArgument( 'proxy_url' ) ) ) {
			$proxy_url = $this->getHelper( 'question' )->ask( $input, $output, new Question( "<question>Please enter the URL to connect to the Automattic Proxy locally.</question> " ) );
		} else {
			$proxy_url = $input->getArgument( 'proxy_url' );
		}

		Config::set_proxy_url( $proxy_url );

		$output->writeln( '<info>Changed the URL we use to connect to Automattic Proxy locally.</info>' );

		return Command::SUCCESS;
	}
}
