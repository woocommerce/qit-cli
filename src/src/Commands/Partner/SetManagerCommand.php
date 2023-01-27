<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment;
use QIT_CLI\ManagerSync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetManagerCommand extends Command {
	protected static $defaultName = 'partner:set_manager'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Config $config */
	protected $config;

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Config $config, Environment $environment ) {
		$this->config      = $config;
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Override the Manager URL used when in Partner mode.' )
			->addArgument( 'manager_url', InputArgument::REQUIRED, 'The URL of the Manager to use.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$parsed_url = parse_url( filter_var( $input->getArgument( 'manager_url' ) ) );

		if ( ! $parsed_url || empty( $parsed_url['host'] ) || empty( $parsed_url['scheme'] ) ) {
			throw new \InvalidArgumentException( 'Value must be a valid URL.' );
		}

		$this->config->set_cache( 'manager_url', $input->getArgument( 'manager_url' ), - 1 );
		$this->config->delete_cache( App::make( ManagerSync::class )->sync_cache_key );

		$output->writeln( sprintf( '<info>Overriden Manager URL to "%s" in the environment "%s".</info>', $input->getArgument( 'manager_url' ), $this->environment->get_current_environment() ) );

		return Command::SUCCESS;
	}
}
