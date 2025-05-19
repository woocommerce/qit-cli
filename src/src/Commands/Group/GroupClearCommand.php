<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Cache;
use QIT_CLI\TestGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupClearCommand extends Command {
	protected static $defaultName = 'group:clear'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Cache */
	protected $cache;

	/** @var TestGroup */
	protected $test_group;

	public function __construct( Cache $cache, TestGroup $test_group ) {
		$this->cache      = $cache;
		$this->test_group = $test_group;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Clear the group cache.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$group = $this->test_group->get();

		if ( empty( $group ) ) {
			$output->writeln( '<error>No group found.</error>' );
			return Command::FAILURE;
		}

		$this->test_group->output_group( $output );

		$this->cache->delete( 'group' );
		$output->writeln( '<info>Group cleared.</info>' );

		return Command::SUCCESS;
	}
}
