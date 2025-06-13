<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\TestGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupFetchCommand extends QITCommand {

	protected static $defaultName = 'group:fetch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected TestGroup $test_group;

	public function __construct( TestGroup $test_group ) {
		$this->test_group = $test_group;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Fetch a group of tests using a group identifier.' )
			->addOption( 'group-identifier', 'i', InputOption::VALUE_REQUIRED, 'The group identifier.' )
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Output the results in JSON format.' );
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 */
	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$group_identifier = $input->getOption( 'group-identifier' );

		if ( empty( $group_identifier ) ) {
			$output->writeln( '<error>Group identifier is required.</error>' );
			return Command::FAILURE;
		}

		$group = $this->test_group->fetch( $group_identifier );

		if ( empty( $group ) ) {
			$output->writeln( '<error>No group found.</error>' );
			return Command::FAILURE;
		}

		if ( $input->getOption( 'json' ) ) {
			$output->writeln( json_encode( $group, JSON_PRETTY_PRINT ) );
			return Command::SUCCESS;
		}

		$output->writeln( '--------------------------------' );
		$output->writeln( sprintf( '<info>Group Identifier: %s</info>', $group['group_identifier'] ) );
		$output->writeln( '--------------------------------' );

		foreach ( $group['test_runs'] as $test_run ) {
			$output->writeln( sprintf( '<info>Test Run ID: %s</info>', $test_run['test_run_id'] ) );
			$output->writeln( sprintf( '<info>Woo ID: %s</info>', $test_run['woo_extension']['id'] ) );
			$output->writeln( sprintf( '<info>Extension Name: %s</info>', $test_run['woo_extension']['name'] ) );
			$output->writeln( sprintf( '<info>Test Type: %s</info>', $test_run['test_type_display'] ) );
			$output->writeln( sprintf( '<info>Test Results Manager URL: %s</info>', $test_run['test_results_manager_url'] ) );
			$output->writeln( sprintf( '<info>Test Run Status: %s</info>', $test_run['status'] ) );
			$output->writeln( '--------------------------------' );
		}

		return Command::SUCCESS;
	}
}
