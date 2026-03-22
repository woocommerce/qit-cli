<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClaudeTipDismissCommand extends Command {
	protected static $defaultName = 'claude-tip:dismiss'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Permanently dismiss the Claude Code plugin recommendation' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$tip_file = Config::get_qit_dir() . '.claude-tip-shown';
		file_put_contents( $tip_file, 'dismissed' );
		$output->writeln( 'Claude Code tip dismissed permanently.' );

		return Command::SUCCESS;
	}
}
