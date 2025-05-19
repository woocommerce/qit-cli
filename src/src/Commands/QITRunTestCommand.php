<?php

namespace QIT_CLI\Commands;

use Symfony\Component\Console\Input\InputOption;

abstract class QITRunTestCommand extends QITCommand {
	protected function configure(): void {
		parent::configure();
		$this->addOption(
			'profile',
			'p',
			InputOption::VALUE_OPTIONAL,
			'The profile to use for the test. If not set, will use the default profile.',
			'default'
		);
	}
}