<?php

namespace QIT_CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class TestGroup {

	public const STATUS_NOT_STARTED = 'not_started';
	public const STATUS_PENDING     = 'pending';
	public const STATUS_COMPLETED   = 'completed';
	public const STATUS_RUNNING     = 'running';
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
				'status' => self::STATUS_NOT_STARTED,
				'tests'  => [],
			];
		}

		$test_type_exists = false;

		if ( count( $group['tests'] ) > 0 ) {
			foreach ( $group['tests'] as $test ) {
				if ( $test['type'] === $test_type ) {
					if ( $test['hash'] === md5( json_encode( $options ) ) ) {
						$test_type_exists = true;
						break;
					}
				}
			}
		}

		if ( $test_type_exists ) {
			throw new \Exception( sprintf( 'A "%s" test for extension ID %s with identical parameters already exists in the group. Please modify the type, parameters or the extension being tested. Alternatively, you can delete the existing test with `group:clear` and run this command again.', $test_type, $options['woo_id'] ) );
		}

		$test = [
			'type'   => $test_type,
			'hash'   => md5( json_encode( $options ) ),
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
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return [];
		}

		return $group;
	}

	/**
	 * Fetches the matching pending test that is expected to be running locally.
	 *
	 * @return array
	 */
	public function fetch_local_group_info( array $info ): array {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return [];
		}

		if ( $group['status'] !== self::STATUS_PENDING ) {
			return [];
		}

		$woo_id        = $info['woo_id'];
		$extension_set = isset( $info['extension_set'] ) ? $info['extension_set'] : null;

		foreach ( $group['tests'] as $test ) {
			$data = isset( $test['params'] ) ? $test['params'] : $test;

			if ( is_null( $extension_set ) ) {
				if ( $data['woo_id'] === $woo_id &&
					$data['local'] === true &&
					$data['extension_set'] === ''
				) {
					return [
						'test_run_id' => $test['test_run_id'],
						'test_type'   => $test['type'],
						'group_id'    => $group['group_id'],
					];
				}
			} else {
				if (
					$data['woo_id'] === $woo_id &&
					$data['extension_set'] === $extension_set &&
					$data['local'] === true
				) {
					return [
						'test_run_id' => $test['test_run_id'],
						'test_type'   => $test['type'],
						'group_id'    => $group['group_id'],
					];
				}
			}
		}

		return [];
	}

	private function update_group_item( array &$group, array $item ): void {
		foreach ( $group['tests'] as $index => $test ) {
			if ( $test['test_run_id'] === $item['test_run_id'] ) {
				$group['tests'][ $index ]['status'] = $item['status'];
			}
		}
	}

	public function update_group_test_runs(): void {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return;
		}

		if ( ! isset( $group['group_id'] ) ) {
			return;
		}

		$body = [
			'group_id' => $group['group_id'],
		];

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-group-test-runs' ) )
			->with_method( 'POST' )
			->with_post_body( $body )
			->request();

		$response_data = json_decode( $response, true ) ?? [];
		$status        = self::STATUS_COMPLETED;

		/**
		 * If any test run is not completed, the group is still pending.
		 */
		foreach ( $response_data['test_runs'] as $test ) {
			if ( ! in_array( $test['status'], [ 'hanged', 'failed', 'success', 'cancelled' ] ) ) {
				$status = self::STATUS_PENDING;
				break;
			}
		}

		$group['status']           = $status;
		$group['group_identifier'] = $response_data['group_identifier'];
		$group['group_id']         = $response_data['group_id'];

		foreach ( $response_data['test_runs'] as $test ) {
			$this->update_group_item( $group, $test );
		}

		// Only cache fetched group data for 1 hour.
		$this->cache->set( 'group', $group, 3600 );
	}

	public function update_test_status( string $test_run_id, string $status ): void {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return;
		}

		foreach ( $group['tests'] as $test ) {
			if ( $test['test_run_id'] === $test_run_id ) {
				$test['status'] = $status;
				break;
			}
		}

		$this->cache->set( 'group', $group, 3600 );
	}
}
