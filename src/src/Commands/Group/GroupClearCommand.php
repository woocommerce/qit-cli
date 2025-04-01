<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupClearCommand extends Command {
	protected static $defaultName = 'group:clear'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Clear the group cache.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$this->cache->delete( 'group' );

		$output->writeln( '<info>Group cleared.</info>' );

		return Command::SUCCESS;
	}
}
