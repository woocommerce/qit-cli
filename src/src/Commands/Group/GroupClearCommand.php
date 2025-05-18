<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupClearCommand extends QITCommand {
	protected static $defaultName = 'group:clear'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected Cache $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Clear the group cache.' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$this->cache->delete( 'group' );

		$output->writeln( '<info>Group cleared.</info>' );

		return self::SUCCESS;
	}
}
