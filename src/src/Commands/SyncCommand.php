<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\ManagerSync;
use QIT_CLI\Commands\QITCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends QITCommand {
	protected static $defaultName = 'sync'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected ManagerSync $manager_sync;

	public function __construct( ManagerSync $manager_sync ) {
		parent::__construct();
		$this->manager_sync = $manager_sync;
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Re-syncs with the Manager.' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$this->manager_sync->maybe_sync( true );

		$output->writeln( 'Sync completed.' );

		return self::SUCCESS;
	}
}
