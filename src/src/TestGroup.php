<?php

namespace QIT_CLI;

use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class TestGroup {

	public const STATUS_NOT_STARTED = 'not_started';
	public const STATUS_PENDING     = 'pending';
	public const STATUS_COMPLETED   = 'completed';
	public const STATUS_RUNNING     = 'running';
	public const STATUS_REGISTERED  = 'registered';

	public const LOCAL_TEST_TYPES = [ 'e2e', 'activation' ];

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

	/**
	 * Creates a new test group or updates an existing one.
	 *
	 * @param array<string, mixed>  $options The options for the test.
	 * @param string                $test_type The type of test.
	 * @param InputInterface|null   $input The input.
	 * @param array<string, string> $env_vars The environment variables.
	 */
	public function create_or_update( array $options, string $test_type, ?InputInterface $input = null, array $env_vars = [] ): void {
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
		$hash             = ! is_null( $input ) ? md5( json_encode( $input->getOptions() ) ) : md5( json_encode( $options ) );

		if ( count( $group['tests'] ) > 0 ) {
			foreach ( $group['tests'] as $test ) {
				if ( $test['type'] === $test_type ) {
					if ( $test['hash'] === $hash ) {
						$test_type_exists = true;
						break;
					}
				}
			}
		}

		if ( $test_type_exists ) {
			throw new \Exception( sprintf( 'A "%s" test for extension ID %s with identical parameters already exists in the group. Please modify the type, parameters or the extension being tested. Alternatively, you can delete the existing test with `group:clear` and run this command again.', $test_type, $options['woo_id'] ) );
		}

		$filtered_env_vars = [];

		foreach ( $env_vars as $key => $value ) {
			if ( str_contains( $key, 'QIT_' ) ) {
				$filtered_env_vars[ $key ] = $value;
			}
		}

		$test = [
			'type'          => $test_type,
			'hash'          => $hash,
			'params'        => [
				'client' => 'qit_cli',
				'hash'   => $hash,
			],
			'env_vars'      => $filtered_env_vars,
			'input_options' => is_null( $input ) ? [] : $input->getOptions(),
			'input_args'    => is_null( $input ) ? [] : $input->getArguments(),
		];

		foreach ( $options as $key => $value ) {
			$test['params'][ $key ] = $value;
		}

		$group['tests'][] = $test;

		$this->cache->set( 'group', $group, -1 );
	}

	/**
	 * Dispatches the test group to the QIT servers.
	 *
	 * @param string|null $group_identifier The identifier for the group.
	 * @param bool        $skip_grouping Whether to skip grouping the tests.
	 * @param bool        $enqueue Whether to enqueue the group.
	 * @return array<string, mixed>
	 */
	public function dispatch( $group_identifier = null, $skip_grouping = false, bool $enqueue = true ): array {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			throw new \Exception( 'No test group found. Please create one by using the any `run:<test> command with the --group option.' );
		}

		if ( $group['status'] !== self::STATUS_NOT_STARTED &&
			$group['status'] !== self::STATUS_REGISTERED
		) {
			throw new \Exception( sprintf( 'Expected test group to be in "%s" or "%s" status, but got "%s".', self::STATUS_NOT_STARTED, self::STATUS_REGISTERED, $group['status'] ) );
		}

		if ( ! empty( $group_identifier ) ) {
			$group['group_identifier'] = $group_identifier;
		}

		if ( $skip_grouping ) {
			$group['skip_grouping'] = true;
		}

		$group['enqueue'] = $enqueue;

		/**
		 * Input and env vars are not needed on the QIT servers.
		 */
		foreach ( $group['tests'] as $test ) {
			unset( $test['env_vars'] );
			unset( $test['input'] );
		}

		if ( $group['status'] === self::STATUS_REGISTERED ) {
			$test_run_ids = [];

			foreach ( $group['tests'] as $test ) {
				// Only run remote tests.
				if ( in_array( ! $test['type'], self::LOCAL_TEST_TYPES ) ) {
					$test_run_ids[] = $test['test_run']['test_run_id'];
				}
			}

			if ( empty( $test_run_ids ) ) {
				return [];
			}

			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/run-group' ) )
				->with_method( 'POST' )
				->with_post_body( $test_run_ids )
				->request();
		} else {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/create-group' ) )
				->with_method( 'POST' )
				->with_post_body( $group )
				->request();
		}

		$response_data = json_decode( $response, true );

		return $response_data;
	}

	/**
	 * Outputs the response from the QIT servers.
	 *
	 * @param array<string, mixed> $response_data The response from the QIT servers.
	 * @param OutputInterface      $output The output interface.
	 * @param bool                 $enqueue Whether the group was enqueued.
	 * @return int The exit code.
	 */
	public function output_response( array $response_data, OutputInterface $output, bool $enqueue = true ): int {

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

		if ( isset( $response_data['group_id'] ) ) {
			if ( $enqueue ) {
				$output->writeln( '<info>Group enqueued on QIT servers!</info>' );
			} else {
				$output->writeln( '<info>Group registered on QIT servers!</info>' );
			}

			$output->writeln( sprintf( '<info>Group ID: %s</info>', $response_data['group_id'] ) );
		} else {
			$output->writeln( '<info>Batch enqueued on QIT servers!</info>' );
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

	/**
	 * Updates the group in the cache.
	 *
	 * @param array<string, mixed> $group The group to update.
	 * @return void
	 */
	public function update_group( array $group ): void {
		$this->cache->set( 'group', $group, -1 );
	}

	/**
	 * @return array<string, mixed>|array<empty>
	 */
	public function get(): array {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return [];
		}

		return $group;
	}

	// TODO: Update this implementation to use new group structure.
	/**
	 * Fetches the matching pending test that is expected to be running locally.
	 *
	 * @param InputInterface $input The input.
	 * @return array{test_run_id?: string, test_type?: string, group_id?: string}|array<empty>
	 */
	public function fetch_local_group_info( InputInterface $input ): array {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return [];
		}

		if ( $group['status'] !== self::STATUS_PENDING &&
			$group['status'] !== self::STATUS_RUNNING
		) {
			return [];
		}

		$hash = md5( json_encode( $input->getOptions() ) );

		foreach ( $group['tests'] as $test ) {
			$data = isset( $test['params'] ) ? $test['params'] : $test;

			if ( $data['hash'] === $hash ) {
				return [
					'test_run_id' => $test['test_run']['test_run_id'],
					'test_type'   => $test['type'],
					'group_id'    => $group['group_id'],
				];
			}
		}

		return [];
	}

	/**
	 * Updates the status of a test in the group.
	 *
	 * @param array<string, mixed> $group The group to update.
	 * @param array<string, mixed> $item The item to update.
	 */
	private function update_group_item( array &$group, array $item ): void {
		foreach ( $group['tests'] as $index => $test ) {
			if ( $test['test_run']['test_run_id'] === $item['test_run_id'] ) {
				$group['tests'][ $index ]['status'] = $item['status'];
			}
		}
	}

	/**
	 * Updates the test runs for the group.
	 *
	 * @return void
	 */
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

		$this->cache->set( 'group', $group, -1 );
	}

	public function update_test_status( string $test_run_id, string $status ): void {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			return;
		}

		foreach ( $group['tests'] as $test ) {
			if (
				isset( $test['test_run_id'] ) &&
				$test['test_run_id'] === $test_run_id
			) {
				$test['status'] = $status;
				break;
			}
		}

		$this->cache->set( 'group', $group, -1 );
	}

	public function run_local_tests( Application $application, OutputInterface $output ): void {
		$group = $this->cache->get( 'group' );

		if ( empty( $group ) ) {
			throw new \Exception( 'No group found.' );
		}

		if ( ! in_array( $group['status'], [ self::STATUS_RUNNING, self::STATUS_REGISTERED ] )
		) {
			throw new \Exception( sprintf( 'Expected test group to be in "%s" or "%s" status, but got "%s".', self::STATUS_RUNNING, self::STATUS_NOT_STARTED, $group['status'] ) );
		}

		// Set the test group id to be used by the local tests when notifying the manager.
		putenv( sprintf( 'QIT_TEST_GROUP_ID=%s', $group['group_id'] ) );

		foreach ( $group['tests'] as $test ) {

			try {
				if ( in_array( $test['type'], self::LOCAL_TEST_TYPES ) ) {
					$output->writeln( sprintf( '<info>Running local test: %s</info>', $test['test_run']['test_run_id'] ) );
					putenv( sprintf( 'QIT_TEST_RUN_ID=%s', $test['test_run']['test_run_id'] ) );
					$this->run_local_test( $test, $application, $output );
					RunE2ECommand::shutdown_test_run();
					putenv( 'QIT_TEST_RUN_ID' );
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>Failed to run local tests with Parameters: %s. Error: %s</error>', json_encode( $test['params'] ), $e->getMessage() ) );
				putenv( 'QIT_TEST_RUN_ID' );
			}
		}

		// Remove the test group id from the environment variables.
		putenv( 'QIT_TEST_GROUP_ID' );
	}

	private function run_local_test( array $test, Application $application, OutputInterface $output ) {
		$run_e2e_command = App::make( RunE2ECommand::class );
		$run_e2e_command->setApplication( $application );

		$resource_stream = fopen( 'php://temp', 'w+' );

		$options   = $test['input_options'];
		$env_vars  = $test['env_vars'];
		$arguments = $test['input_args'];

		foreach ( $env_vars as $key => $value ) {
			putenv( sprintf( '%s=%s', $key, $value ) );
		}

		$input_array = $this->build_input_array( $options, $arguments );

		$run_e2e_exit_code = $run_e2e_command->run(
			new ArrayInput( $input_array ),
			new StreamOutput( $resource_stream )
		);

		$run_e2e_output = stream_get_contents( $resource_stream, - 1, 0 );
		$output->writeln( $run_e2e_output );

		if ( $run_e2e_exit_code === 1 ) {
			throw new \Exception( $run_e2e_output );
		}

		foreach ( $env_vars as $key => $value ) {
			putenv( $key );
		}
	}

	public function build_input_array( array $options, array $arguments ): array {
		$input_array = [];

		foreach ( $options as $key => $value ) {

			if ( 'group' === $key ) {
				continue;
			}

			$input_array[ '--' . $key ] = $value;
		}

		foreach ( $arguments as $key => $argument ) {
			$input_array[ $key ] = $argument;
		}

		return $input_array;
	}
}
