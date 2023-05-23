<?php

namespace QIT_CLI\Commands;

use QIT_CLI\ManagerSync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command {
	protected static $defaultName = 'sync'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected $manager_sync;

	public function __construct( ManagerSync $manager_sync ) {
		$this->manager_sync = $manager_sync;
	}

	protected function configure() {
		$this
			->setDescription( 'Re-syncs with the Manager.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->manager_sync->maybe_sync( true );

		return Command::SUCCESS;
	}
}
