<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\TestGroup;
use QIT_CLI\Commands\QITCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GroupRunCommand extends QITCommand {
	protected static $defaultName = 'group:run'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected TestGroup $test_group;

	public function __construct( TestGroup $test_group ) {
		$this->test_group = $test_group;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Run a group of tests.' )
			->addOption( 'group-identifier', 'i', InputOption::VALUE_OPTIONAL, 'The group identifier.' )
			->addOption( 'skip-grouping', 's', InputOption::VALUE_NEGATABLE, 'Skip triggering tests as a logical group and instead treats them as a batch.', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$group_identifier = $input->getOption( 'group-identifier' );
		$skip_grouping    = $input->getOption( 'skip-grouping' );
		$group            = $this->test_group->get();

		if ( empty( $group ) ) {
			$output->writeln( 'No group found. Please create one by using the any run:<test> command with the --group option.' );
			return Command::FAILURE;
		}

		try {
			$response_data = $this->test_group->dispatch( $group_identifier, $skip_grouping, true );

			if ( ! empty( $response_data ) ) {

				if ( $group['status'] === TestGroup::STATUS_REGISTERED ) {
					$output->writeln( '--------------------------------' );
					$output->writeln( '<comment>Remote Tests Enqueued on QIT servers!</comment>' );
					$output->writeln( '--------------------------------' );

					foreach ( $response_data['test_run_data'] as $test_run ) {
						$output->writeln( sprintf( '<info>Test Run ID: %s</info>', $test_run['test_run_id'] ) );
						$output->writeln( sprintf( '<info>Woo ID: %s</info>', $test_run['woo_id'] ) );
						$output->writeln( sprintf( '<info>Slug: %s</info>', $test_run['slug'] ) );
						$output->writeln( sprintf( '<info>Type: %s</info>', $test_run['test_type_display'] ) );
						$output->writeln( sprintf( '<info>Status: %s</info>', TestGroup::STATUS_RUNNING ) );
						$output->writeln( '--------------------------------' );
					}
				} else {
					$result = $this->test_group->output_response( $response_data, $output, true );

					if ( $result === Command::FAILURE ) {
						$this->test_group->delete_group();
						return $result;
					}

					$group['group_id']   = $response_data['group_id'] ?? '';
					$group['identifier'] = $group_identifier ?? '';
					$group['status']     = TestGroup::STATUS_RUNNING;

					foreach ( $group['tests'] as $index => $test ) {
						foreach ( $response_data['test_run_data'] as $test_run ) {
							if ( $test['hash'] === $test_run['hash'] ) {
								$group['tests'][ $index ]['test_run'] = $test_run;
								break;
							}
						}
					}

					$this->test_group->update_group( $group );
				}
			}

			$application = $this->getApplication();
			$this->test_group->run_local_tests( $application, $output );

			$this->test_group->update_group_test_runs();

			$output->writeln( '--------------------------------' );
			$output->writeln( '<info>Group run successfully triggered.</info>' );
			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to enqueue group: %s</error>', $e->getMessage() ) );
			$this->test_group->delete_group();
			return Command::FAILURE;
		}
	}
}
