<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

/**
 * Run a group of tests defined in qit.json.
 *
 * This command reads group definitions from qit.json, creates all test runs
 * on the Manager using /create-group, then executes local tests.
 *
 * Example qit.json:
 * {
 *   "groups": {
 *     "ci-quick": {
 *       "e2e": ["smoke"],
 *       "security": ["default"]
 *     }
 *   }
 * }
 *
 * Usage: qit run:group ci-quick
 */
class RunGroupCommand extends QITCommand {
	protected static $defaultName = 'run:group'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var WooExtensionsList */
	protected WooExtensionsList $woo_extensions_list;

	public function __construct( WooExtensionsList $woo_extensions_list ) {
		$this->woo_extensions_list = $woo_extensions_list;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Run a group of tests defined in qit.json' )
			->addArgument( 'group', InputArgument::REQUIRED, 'Group name from qit.json' )
			->addOption( 'only', null, InputOption::VALUE_REQUIRED, 'Run only specific test type (e.g., e2e, security)' )
			->addOption( 'async', null, InputOption::VALUE_NEGATABLE, 'Run tests asynchronously (default: true)', true )
			->addOption( 'wait', null, InputOption::VALUE_NEGATABLE, 'Wait for all tests to complete (overrides --async)', false )
			->addOption( 'group-identifier', 'i', InputOption::VALUE_OPTIONAL, 'Identifier for this group run (auto-generated if not provided)' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// 1. Load and validate group configuration
		$config     = $this->get_resolved_config();
		$group_name = $input->getArgument( 'group' );

		if ( ! isset( $config['groups'][ $group_name ] ) ) {
			$available = empty( $config['groups'] ) ? 'none' : implode( ', ', array_keys( $config['groups'] ) );
			throw new \RuntimeException(
				"Group '{$group_name}' not found in qit.json. Available groups: {$available}"
			);
		}

		$group = $config['groups'][ $group_name ];

		// 2. Filter by --only if specified
		$only = $input->getOption( 'only' );
		if ( $only !== null ) {
			if ( ! isset( $group[ $only ] ) ) {
				$available_types = implode( ', ', array_keys( $group ) );
				throw new \RuntimeException(
					"Test type '{$only}' not found in group '{$group_name}'. Available test types: {$available_types}"
				);
			}
			$group = [ $only => $group[ $only ] ];
		}

		// 3. Get SUT information
		$sut_slug = $config['sut']['slug'] ?? null;
		if ( $sut_slug === null ) {
			throw new \RuntimeException(
				'No SUT defined in qit.json. Groups require a SUT to test.'
			);
		}

		// Get SUT ID (woo_id) from the extension
		$sut_id = $this->get_sut_id( $sut_slug );

		// 4. Determine async behavior (--wait overrides)
		$should_enqueue = $input->getOption( 'wait' ) ? false : $input->getOption( 'async' );

		// 5. Build payload for /create-group
		$payload = $this->build_create_group_payload(
			$group,
			$sut_id,
			$group_name,
			$input->getOption( 'group-identifier' ),
			$should_enqueue
		);

		$output->writeln(
			sprintf(
				'<info>Creating group "%s" with %d test(s)...</info>',
				$group_name,
				count( $payload['tests'] )
			)
		);
		$output->writeln( '' );

		// 6. Call Manager API to create group
		$group_response = $this->create_group_on_manager( $payload, $output );

		if ( $group_response === null ) {
			return Command::FAILURE;
		}

		// Debug: Show what Manager returned
		if ( $output->isVerbose() ) {
			$output->writeln( '[DEBUG] Manager response:' );
			$output->writeln( json_encode( $group_response, JSON_PRETTY_PRINT ) );
		}

		// 7. Display created test runs
		$this->display_created_tests( $group_response, $output );

		// 8. Execute local tests
		$local_results = $this->execute_local_tests( $group_response['test_run_data'], $group, $output );

		// 9. Display final summary
		return $this->display_summary( $group_response, $local_results, $output );
	}

	/**
	 * Get the SUT ID (woo_id) from the extension slug.
	 *
	 * @param string $slug Extension slug.
	 * @return int Extension ID.
	 * @throws \RuntimeException If extension not found.
	 */
	private function get_sut_id( string $slug ): int {
		try {
			return $this->woo_extensions_list->get_woo_extension_id_by_slug( $slug );
		} catch ( \Exception $e ) {
			throw new \RuntimeException(
				"Extension '{$slug}' not found. Make sure you have access to this extension. Error: {$e->getMessage()}"
			);
		}
	}

	/**
	 * Build the payload for /create-group Manager endpoint.
	 *
	 * @param array<string, array<string>> $group             Group configuration from qit.json.
	 * @param int                          $sut_id            SUT woo_id.
	 * @param string                       $group_name        Group name.
	 * @param string|null                  $group_identifier  Optional group identifier.
	 * @param bool                         $should_enqueue    Whether to enqueue remote tests.
	 * @return array{group_identifier: string, tests: array<array{type: string, params: array<string, mixed>, enqueue: bool}>} Payload for Manager API.
	 */
	private function build_create_group_payload(
		array $group,
		int $sut_id,
		string $group_name,
		?string $group_identifier,
		bool $should_enqueue
	): array {
		$tests = [];

		foreach ( $group as $test_type => $profiles ) {
			foreach ( $profiles as $profile ) {
				// Get full test options from profile
				$profile_options = $this->get_current_test_profile( $test_type, $profile );

				// Determine if this test runs locally
				// Tests with test_packages are local (custom E2E tests)
				// Activation and performance tests are also local
				$has_test_packages = ! empty( $profile_options['test_packages'] );
				$is_local          = $has_test_packages || in_array( $test_type, [ 'activation', 'performance' ], true );

				// Build params for this test
				$params = array_merge(
					[ 'woo_id' => $sut_id ],
					$profile_options,
					[ 'local' => $is_local ] // Tell Manager if this runs locally
				);

				$tests[] = [
					'type'    => $test_type,
					'params'  => $params,
					'enqueue' => ! $is_local && $should_enqueue,
				];
			}
		}

		return [
			'group_identifier' => $group_identifier ?? $this->generate_group_identifier( $group_name ),
			'tests'            => $tests,
		];
	}

	/**
	 * Call Manager API to create the group.
	 *
	 * @param array<string, mixed> $payload Payload for /create-group.
	 * @param OutputInterface      $output  Output interface.
	 * @return array<string, mixed>|null Group response or null on failure.
	 */
	private function create_group_on_manager( array $payload, OutputInterface $output ): ?array {
		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/create-group' ) )
				->with_method( 'POST' )
				->with_post_body( $payload )
				->request();

			$data = json_decode( $response, true );

			if ( ! $data || isset( $data['error'] ) ) {
				$error = $data['error'] ?? 'Unknown error creating group';
				$output->writeln( sprintf( '<error>Failed to create group: %s</error>', $error ) );
				return null;
			}

			return $data;
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to create group: %s</error>', $e->getMessage() ) );
			return null;
		}
	}

	/**
	 * Display the created test runs.
	 *
	 * @param array<string, mixed> $group_response Response from Manager.
	 * @param OutputInterface      $output         Output interface.
	 * @return void
	 */
	private function display_created_tests( array $group_response, OutputInterface $output ): void {
		$output->writeln( '<info>Group created successfully!</info>' );
		$output->writeln( sprintf( 'Group ID: <comment>%s</comment>', $group_response['group_id'] ?? 'N/A' ) );
		$output->writeln( sprintf( 'Group Identifier: <comment>%s</comment>', $group_response['group_identifier'] ?? 'N/A' ) );
		$output->writeln( '' );

		if ( ! empty( $group_response['test_run_data'] ) ) {
			$output->writeln( '<comment>Test Runs Created:</comment>' );
			foreach ( $group_response['test_run_data'] as $test ) {
				$output->writeln(
					sprintf(
						'  <info>→</info> %s (ID: %s) - %s',
						$test['test_type_display'] ?? $test['type'],
						$test['test_run_id'],
						$test['status']
					)
				);

				if ( ! empty( $test['test_results_manager_url'] ) ) {
					$output->writeln( sprintf( '      URL: %s', $test['test_results_manager_url'] ) );
				}
			}
			$output->writeln( '' );
		}
	}

	/**
	 * Execute local tests (e2e, activation, performance).
	 *
	 * @param array<array<string, mixed>>  $test_run_data Test run data from Manager.
	 * @param array<string, array<string>> $group         Group configuration.
	 * @param OutputInterface              $output        Output interface.
	 * @return array<string, array<string, mixed>> Results of local test executions.
	 */
	private function execute_local_tests( array $test_run_data, array $group, OutputInterface $output ): array {
		$local_results = [];
		$local_tests   = array_filter( $test_run_data, fn( $t ) => ! empty( $t['local'] ) );

		if ( empty( $local_tests ) ) {
			return $local_results;
		}

		$output->writeln( '<comment>Executing local tests...</comment>' );
		$output->writeln( '' );

		foreach ( $local_tests as $test ) {
			$test_type = $test['type'];
			$test_id   = $test['test_run_id'];

			$output->writeln( sprintf( 'Running %s (ID: %s)...', $test['test_type_display'] ?? $test_type, $test_id ) );

			try {
				$exit_code = $this->run_local_test( $test, $output );

				$local_results[ $test_type ] = [
					'test_run_id' => $test_id,
					'exit_code'   => $exit_code,
					'success'     => $exit_code === Command::SUCCESS,
				];

				if ( $exit_code === Command::SUCCESS ) {
					$output->writeln( '<info>  ✓ Test completed successfully</info>' );
				} else {
					$output->writeln( '<error>  ✗ Test failed</error>' );
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>  Failed to execute: %s</error>', $e->getMessage() ) );
				$local_results[ $test_type ] = [
					'test_run_id' => $test_id,
					'exit_code'   => Command::FAILURE,
					'success'     => false,
					'error'       => $e->getMessage(),
				];
			}

			$output->writeln( '' );
		}

		return $local_results;
	}

	/**
	 * Run a single local test by invoking the appropriate command.
	 *
	 * @param array<string, mixed> $test   Test data from Manager.
	 * @param OutputInterface      $output Output interface.
	 * @return int Exit code from test execution.
	 * @throws \Exception If command not found.
	 */
	private function run_local_test( array $test, OutputInterface $output ): int {
		$test_type    = $test['type'];
		$command_name = "run:{$test_type}";

		try {
			$command = $this->getApplication()->find( $command_name );
		} catch ( \Exception $e ) {
			throw new \Exception( "Command '{$command_name}' not found. Local test type '{$test_type}' may not be supported." );
		}

		// Build parameters for the command
		// The test was already created on the Manager with test_run_id
		// We need to pass the SUT and any other required parameters
		$params = [
			'sut' => $test['slug'] ?? null,
		];

		// Add test_run_id if the command supports it (for reporting back to Manager)
		if ( $command->getDefinition()->hasOption( 'test_run_id' ) ) {
			$params['--test_run_id'] = $test['test_run_id'];
		}

		// Add profile if available
		if ( ! empty( $test['profile'] ) && $command->getDefinition()->hasOption( 'profile' ) ) {
			$params['--profile'] = $test['profile'];
		}

		// For e2e tests, add test packages if available
		if ( ! empty( $test['test_packages'] ) && $command->getDefinition()->hasOption( 'test-package' ) ) {
			// test_packages might be an array
			if ( is_array( $test['test_packages'] ) ) {
				foreach ( $test['test_packages'] as $package ) {
					if ( ! isset( $params['--test-package'] ) ) {
						$params['--test-package'] = [];
					}
					$params['--test-package'][] = $package;
				}
			} else {
				$params['--test-package'] = $test['test_packages'];
			}
		}

		// Set QIT_TEST_RUN_ID environment variable so LocalTestRunNotifier can report status
		if ( ! empty( $test['test_run_id'] ) ) {
			putenv( 'QIT_TEST_RUN_ID=' . $test['test_run_id'] );
		}

		// Execute the command
		$input     = new \Symfony\Component\Console\Input\ArrayInput( $params );
		$exit_code = Command::FAILURE; // Default to failure in case of exception

		try {
			$exit_code = $command->run( $input, $output );
		} finally {
			// Clean up environment variable
			putenv( 'QIT_TEST_RUN_ID' );
		}

		return $exit_code;
	}

	/**
	 * Display final summary.
	 *
	 * @param array<string, mixed>                $group_response Group response from Manager.
	 * @param array<string, array<string, mixed>> $local_results  Local test results.
	 * @param OutputInterface                     $output         Output interface.
	 * @return int Exit code.
	 */
	private function display_summary( array $group_response, array $local_results, OutputInterface $output ): int {
		$output->writeln( '<info>═══════════════════════════════════════</info>' );
		$output->writeln( '<info>Group Summary</info>' );
		$output->writeln( '<info>═══════════════════════════════════════</info>' );
		$output->writeln( '' );

		$test_run_data = $group_response['test_run_data'] ?? [];
		$remote_tests  = array_filter( $test_run_data, fn( $t ) => empty( $t['local'] ) );
		$local_tests   = array_filter( $test_run_data, fn( $t ) => ! empty( $t['local'] ) );

		// Remote tests
		if ( ! empty( $remote_tests ) ) {
			$output->writeln( sprintf( '<comment>Remote Tests (%d):</comment>', count( $remote_tests ) ) );
			foreach ( $remote_tests as $test ) {
				$status = $test['status'] === 'pending' ? 'Enqueued' : ucfirst( $test['status'] );
				$output->writeln(
					sprintf(
						'  <info>→</info> %s - %s (ID: %s)',
						$test['test_type_display'] ?? $test['type'],
						$status,
						$test['test_run_id']
					)
				);
				$output->writeln( sprintf( '      Check: qit get %s', $test['test_run_id'] ) );
				if ( ! empty( $test['test_results_manager_url'] ) ) {
					$output->writeln( sprintf( '      URL: %s', $test['test_results_manager_url'] ) );
				}
			}
			$output->writeln( '' );
		}

		// Local tests
		if ( ! empty( $local_tests ) ) {
			$local_passed = 0;
			$local_failed = 0;

			$output->writeln( sprintf( '<comment>Local Tests (%d):</comment>', count( $local_tests ) ) );
			foreach ( $local_tests as $test ) {
				$test_type = $test['type'];
				$result    = $local_results[ $test_type ] ?? null;

				if ( $result && isset( $result['success'] ) ) {
					if ( $result['success'] ) {
						$status_icon = '<info>✓</info>';
						$status_text = 'PASSED';
						++$local_passed;
					} else {
						$status_icon = '<error>✗</error>';
						$status_text = 'FAILED';
						++$local_failed;
					}
				} else {
					$status_icon = '<comment>→</comment>';
					$status_text = 'EXECUTED';
				}

				$output->writeln(
					sprintf(
						'  %s %s - %s (ID: %s)',
						$status_icon,
						$test['test_type_display'] ?? $test['type'],
						$status_text,
						$test['test_run_id']
					)
				);

				if ( ! empty( $test['test_results_manager_url'] ) ) {
					$output->writeln( sprintf( '      URL: %s', $test['test_results_manager_url'] ) );
				}

				if ( $result && ! empty( $result['error'] ) ) {
					$output->writeln( sprintf( '      <error>Error: %s</error>', $result['error'] ) );
				}
			}
			$output->writeln( '' );
		}

		// Group tracking
		if ( ! empty( $group_response['group_identifier'] ) ) {
			$output->writeln( '<info>Track all tests in this group:</info>' );
			$output->writeln( sprintf( '  qit group:fetch %s', $group_response['group_identifier'] ) );
			$output->writeln( '' );
		}

		// Return failure if any local tests failed
		$has_local_failures = false;
		foreach ( $local_results as $result ) {
			if ( isset( $result['success'] ) && ! $result['success'] ) {
				$has_local_failures = true;
				break;
			}
		}

		return $has_local_failures ? Command::FAILURE : Command::SUCCESS;
	}

	/**
	 * Generate a unique group identifier.
	 *
	 * @param string $group_name Group name from qit.json.
	 * @return string Generated identifier in format: group-name-YYYYMMDD-HHmmss
	 */
	private function generate_group_identifier( string $group_name ): string {
		return sprintf(
			'%s-%s',
			$group_name,
			gmdate( 'Ymd-His' )
		);
	}
}
