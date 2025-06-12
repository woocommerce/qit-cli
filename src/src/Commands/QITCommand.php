<?php

namespace QIT_CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class QITCommand extends Command {
	protected InputInterface $input;

	protected function configure(): void {
		$this->addOption(
			'config',
			null,
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file, which may extend a base configuration.',
			'qit.json'
		);

		if ( $this->needs_test_profile() ) {
			$this->addOption(
				'profile',
				null,
				InputOption::VALUE_OPTIONAL,
				'The profile to use for the test. If not set, will use the default profile.',
				'default'
			);
		}
	}

	public function execute( InputInterface $input, OutputInterface $output ): int {
		return $this->doExecute( $input, $output );
	}

	protected function needs_test_profile(): bool {
		return str_starts_with( static::getDefaultName() ?? '', 'run:' );
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}
