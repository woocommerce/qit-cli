<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class QITCommand extends Command {
	protected QITConfig $config;

	protected function configure(): void {
		$this->addOption(
			'config',
			null,
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file.',
			'qit.json'
		);
	}

	public static function add_profile_option( Command $command ) {
		$command->addOption(
			'profile',
			null,
			InputOption::VALUE_OPTIONAL,
			'The profile to use for the test. If not set, will use the default profile.',
			'default'
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$configFile = $input->getOption( 'config' );
		try {
			$this->config = new QITConfig( $configFile, $this->getApplication() );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		return $this->doExecute( $input, $output );
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}