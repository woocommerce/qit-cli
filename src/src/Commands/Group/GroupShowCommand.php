<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\TestGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupShowCommand extends Command {
	protected static $defaultName = 'group:show'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected $test_group;

	public function __construct( TestGroup $test_group ) {
		$this->test_group = $test_group;
		parent::__construct();
	}

	protected function configure() {
		$this->setName( 'group:show' )
			->setDescription( 'Show the currently cached group' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$group = $this->test_group->get();

		if ( empty( $group ) ) {
			$output->writeln( '<error>No group found.</error>' );
			return Command::FAILURE;
		}

		$tests = $group['tests'];

		foreach ( $tests as $test ) {
			$test_type = ucfirst( $test['type'] );
			$params    = $test['params'];

			$output->writeln( sprintf( 'Test Type: <info>%s</info>', $test_type ) );
			$output->writeln( sprintf( 'Params: <comment>%s</comment>', json_encode( $params ) ) );
			$output->writeln( '--------------------------------' );
		}

		return Command::SUCCESS;
	}
}
