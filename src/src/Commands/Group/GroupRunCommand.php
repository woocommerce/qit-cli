<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Cache;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function QIT_CLI\get_manager_url;

class GroupRunCommand extends Command {
	protected static $defaultName = 'group:run';

	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
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

		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			$output->writeln( 'No group found. Please create one by using the any `run:<test> command with the --group option.' );
			return Command::FAILURE;
		}

		if ( ! empty( $group_identifier ) ) {
			$group['identifier'] = $group_identifier;
		}

		if ( ! empty( $skip_grouping ) ) {
			$group['skip_grouping'] = true;
		}

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/enqueue-group' ) )
				->with_method( 'POST' )
				->with_post_body( $group )
				->request();

			$response_data = json_decode( $response, true );

			if ( isset( $response_data['code'] ) &&
				$response_data['code'] === 'rest_invalid_group_param'
			) {
				$output->writeln( '<comment>There was an error enqueuing the group. Please fix the following errors and try again:</comment>' );

				$data = $response_data['data'];
				$output->writeln( sprintf( 'Message: <error>%s</error>', $response_data['message'] ) );

				foreach ( $data as $test_type => $errors ) {
					$output->writeln( '--------------------------------' );
					$output->writeln( sprintf( 'Test Type: <comment>%s</comment>', ucfirst( $test_type ) ) );
					$output->writeln( '--------------------------------' );
					foreach ( $errors as $error ) {
						$output->writeln( sprintf( 'Invalid parameter: <comment>%s</comment>', $error['param'] ) );
						$output->writeln( sprintf( 'Value: <comment>%s</comment>', $error['value'] ) );
						$output->writeln( sprintf( 'Error: <comment>%s</comment>', $error['error'] ) );
						$output->writeln( '-------------' );
					}
				}

				$this->cache->delete( 'group' );
				return Command::FAILURE;
			}

			$output->writeln( '<info>Group enqueued on QIT servers!</info>' );

			if ( $response_data['group_id'] ) {
				$output->writeln( sprintf( '<info>Group ID: %s</info>', $response_data['group_id'] ) );
			}

			if ( $response_data['group_identifier'] ) {
				$output->writeln( sprintf( '<info>Group Identifier: %s</info>', $response_data['group_identifier'] ) );
			}

			foreach ( $response_data['test_run_data'] as $test ) {
				$output->writeln( '--------------------------------' );
				$output->writeln( sprintf( '<info>Test Run ID: %s</info>', $test['test_run_id'] ) );
				$output->writeln( sprintf( '<info>Test Type: %s</info>', $test['test_type_display'] ) );
				$output->writeln( sprintf( '<info>Test Results Manager URL: %s</info>', $test['test_results_manager_url'] ) );
			}

			$this->cache->delete( 'group' );

			return Command::SUCCESS;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to enqueue group: %s</error>', $e->getMessage() ) );
			$this->cache->delete( 'group' );
			return Command::FAILURE;
		}
	}
}
