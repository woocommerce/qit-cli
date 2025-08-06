<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\TestGroup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupRegisterCommand extends QITCommand {
	protected static $defaultName = 'group:register'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected TestGroup $test_group;

	public function __construct( TestGroup $test_group ) {
		$this->test_group = $test_group;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Register a test run group.' )
			->addOption( 'group-identifier', 'i', InputOption::VALUE_OPTIONAL, 'The group identifier.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$group_identifier = $input->getOption( 'group-identifier' );

		$group = $this->test_group->get();

		if ( empty( $group ) ) {
			$output->writeln( 'No group found. Please create one by using the any run:<test> command with the --group option.' );
			return Command::FAILURE;
		}

		try {
			$response_data = $this->test_group->dispatch( $group_identifier, false, false );
			$result        = $this->test_group->output_response( $response_data, $output );

			if ( $result === Command::FAILURE ) {
				$this->test_group->delete_group();
				return $result;
			}

			$output->writeln( '--------------------------------' );
			$output->writeln( 'To run the group, use the following command:' );
			$output->writeln( 'qit group:run' );
			$output->writeln( '--------------------------------' );

			foreach ( $group['tests'] as $index => $test ) {
				foreach ( $response_data['test_run_data'] as $test_run ) {
					if ( $test['params']['hash'] === $test_run['hash'] ) {
						$group['tests'][ $index ]['test_run'] = $test_run;
						break;
					}
				}
			}

			$group['group_id']   = $response_data['group_id'];
			$group['identifier'] = $group_identifier;
			$group['status']     = TestGroup::STATUS_REGISTERED;

			$this->test_group->update_group( $group );

			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to enqueue group: %s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		}
	}
}
