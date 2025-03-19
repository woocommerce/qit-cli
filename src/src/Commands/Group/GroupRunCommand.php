<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\TestGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupRunCommand extends Command {
	protected static $defaultName = 'group:run'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var TestGroup */
	protected $test_group;

	public function __construct( TestGroup $test_group ) {
		$this->test_group = $test_group;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Run a group of tests.' )
			->addOption( 'group-identifier', 'i', InputOption::VALUE_OPTIONAL, 'The group identifier.' )
			->addOption( 'skip-grouping', 's', InputOption::VALUE_NEGATABLE, 'Skip triggering tests as a logical group and instead treats them as a batch.', false );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$group_identifier = $input->getOption( 'group-identifier' );
		$skip_grouping    = $input->getOption( 'skip-grouping' );

		$group = $this->test_group->get();

		if ( empty( $group ) ) {
			$output->writeln( 'No group found. Please create one by using the any `run:<test> command with the --group option.' );
			return Command::FAILURE;
		}

		try {
			$response_data = $this->test_group->dispatch( $group_identifier, $skip_grouping );
			$result        = $this->test_group->output_response( $response_data, $output );

			if ( $result === Command::FAILURE ) {
				$this->test_group->delete_group();
				return $result;
			}

			$group = [
				'group_id'   => $response_data['group_id'],
				'tests'      => $response_data['test_run_data'],
				'identifier' => $group_identifier,
				'status'     => 'pending',
			];

			$this->test_group->update_group( $group );

			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to enqueue group: %s</error>', $e->getMessage() ) );
			$this->test_group->delete_group();
			return Command::FAILURE;
		}
	}
}
