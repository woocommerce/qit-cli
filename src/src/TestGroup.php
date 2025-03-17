<?php

namespace QIT_CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class TestGroup {

	public const STATUS_NOT_STARTED = 'not_started';
	public const STATUS_PENDING     = 'pending';
	public const STATUS_COMPLETED   = 'completed';

	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	public function pending_test_group_exists(): bool {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return false;
		}

		if ( $group['status'] !== self::STATUS_PENDING ) {
			return false;
		}

		return true;
	}

	public function create_or_update( array $options, string $test_type ): void {
		$group = $this->cache->get( 'group' );

		if ( $this->pending_test_group_exists() ) {
			throw new \Exception( 'A pending test group already exists. Either wait for it to complete or delete it with `group:clear`.' );
		}

		if ( empty( $group ) ) {
			$group = [
				'identifier' => $options['identifier'],
				'status'     => self::STATUS_NOT_STARTED,
				'tests'      => [],
			];
		}

		$test_type_exists = false;

		if ( count( $group['tests'] ) > 0 ) {
			foreach ( $group['tests'] as $test ) {
				if ( $test['type'] === $test_type ) {
					if ( in_array( $test_type, [ 'activation', 'e2e' ] ) ) {
						// If we're adding a test WITH extension_set
						if ( isset( $options['extension_set'] ) ) {
							// It's a duplicate only if existing test has same extension_set
							if ( isset( $test['params']['extension_set'] ) &&
								$test['params']['extension_set'] === $options['extension_set']
							) {
								$test_type_exists = true;
								break;
							}
						} else { // If we're adding a test WITHOUT extension_set
							if ( ! isset( $test['params']['extension_set'] ) ) {
								$test_type_exists = true;
								break;
							}
						}
					} else {
						$test_type_exists = true;
						break;
					}
				}
			}
		}

		if ( $test_type_exists ) {
			if ( isset( $options['extension_set'] ) ) {
				throw new \Exception( sprintf( 'Test type "%s" with extension set "%s" already exists in the group. Please use a different test type or delete the existing test with `group:clear`.', $test_type, $options['extension_set'] ) );
			} else {
				throw new \Exception( sprintf( 'Test type "%s" already exists in the group. Please use a different test type or delete the existing test with `group:clear`.', $test_type ) );
			}
		}

		$test = [
			'type'   => $test_type,
			'params' => [
				'client' => 'qit_cli',
			],
		];

		foreach ( $options as $key => $value ) {
			$test['params'][ $key ] = $value;
		}

		$group['tests'][] = $test;

		// Cache for 2 hours.
		$this->cache->set( 'group', $group, 7200 );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function dispatch( $group_identifier = null, $skip_grouping = false ): array {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			throw new \Exception( 'No test group found. Please create one by using the any `run:<test> command with the --group option.' );
		}

		if ( $group['status'] !== self::STATUS_NOT_STARTED ) {
			throw new \Exception( sprintf( 'Expected test group to be in "%s" status, but got "%s".', self::STATUS_NOT_STARTED, $group['status'] ) );
		}

		if ( ! empty( $group_identifier ) ) {
			$group['identifier'] = $group_identifier;
		}

		if ( $skip_grouping ) {
			$group['skip_grouping'] = true;
		}

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/enqueue-group' ) )
		->with_method( 'POST' )
		->with_post_body( $group )
		->request();

		$response_data = json_decode( $response, true );

		return $response_data;
	}

	public function output_response( array $response_data, OutputInterface $output ): int {

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

		if ( isset( $response_data['group_id'] ) ) {
			$output->writeln( sprintf( '<info>Group ID: %s</info>', $response_data['group_id'] ) );
		}

		if ( isset( $response_data['group_identifier'] ) ) {
			$output->writeln( sprintf( '<info>Group Identifier: %s</info>', $response_data['group_identifier'] ) );
		}

		foreach ( $response_data['test_run_data'] as $test ) {
			$output->writeln( '--------------------------------' );
			$output->writeln( sprintf( '<info>Test Run ID: %s</info>', $test['test_run_id'] ) );
			$output->writeln( sprintf( '<info>Test Type: %s</info>', $test['test_type_display'] ) );
			$output->writeln( sprintf( '<info>Test Results Manager URL: %s</info>', $test['test_results_manager_url'] ) );
			$output->writeln( sprintf( '<info>Test Run Status: %s</info>', $test['status'] ) );
		}

		return Command::SUCCESS;
	}

	public function delete_group(): void {
		$this->cache->delete( 'group' );
	}

	public function update_group( array $group ): void {
		$this->cache->set( 'group', $group, 7200 );
	}

	public function get(): array {
		return $this->cache->get( 'group' );
	}
}
