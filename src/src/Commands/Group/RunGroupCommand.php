<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\CapturingOutput;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run a group of tests defined in qit.json.
 *
 * This command reads group definitions from qit.json and executes all test types/profiles
 * in that group by invoking the corresponding run:* commands programmatically.
 *
 * Example qit.json:
 * {
 *   "groups": {
 *     "ci-quick": {
 *       "e2e": ["smoke"],
 *       "security": ["scan"]
 *     }
 *   }
 * }
 *
 * Usage: qit run:group ci-quick
 */
class RunGroupCommand extends QITCommand {
	protected static $defaultName = 'run:group'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Run a group of tests defined in qit.json' )
			->addArgument( 'group', InputArgument::REQUIRED, 'Group name from qit.json' )
			->addOption( 'only', null, InputOption::VALUE_REQUIRED, 'Run only specific test type (e.g., e2e, security)' )
			->addOption( 'async', null, InputOption::VALUE_NEGATABLE, 'Run tests asynchronously (default: true)', true )
			->addOption( 'wait', null, InputOption::VALUE_NEGATABLE, 'Wait for all tests to complete (overrides --async)', false )
			->addOption( 'print-report-url', null, InputOption::VALUE_NEGATABLE, 'Print test report URLs', false );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// 1. Load config and validate group exists
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

		// 3. Determine async behavior (--wait overrides)
		$is_async = $input->getOption( 'wait' ) ? false : $input->getOption( 'async' );

		// 4. Get SUT if available
		$sut = $config['sut']['slug'] ?? null;
		if ( $sut === null ) {
			throw new \RuntimeException(
				'No SUT defined in qit.json. Groups require a SUT to test.'
			);
		}

		// 5. Execute each test:profile combination
		$results     = [];
		$total_count = $this->count_tests( $group );

		$output->writeln(
			sprintf(
				'<info>Running group "%s" (%d tests)...</info>',
				$group_name,
				$total_count
			)
		);
		$output->writeln( '' );

		foreach ( $group as $test_type => $profiles ) {
			foreach ( $profiles as $profile ) {
				$label = "{$test_type}:{$profile}";
				$output->writeln( "Executing {$label}..." );

				try {
					$test_result = $this->execute_test(
						$test_type,
						$profile,
						$sut,
						$is_async,
						$input->getOption( 'print-report-url' ),
						$output
					);

					$results[ $label ] = [
						'exit_code'    => $test_result['exit_code'],
						'success'      => $test_result['exit_code'] === Command::SUCCESS,
						'was_enqueued' => $test_result['was_enqueued'],
						'test_id'      => $test_result['test_id'],
						'report_url'   => $test_result['report_url'],
					];
				} catch ( \Exception $e ) {
					$output->writeln( "<error>Failed: {$e->getMessage()}</error>" );
					$results[ $label ] = [
						'exit_code'    => Command::FAILURE,
						'success'      => false,
						'was_enqueued' => false,
						'test_id'      => null,
						'report_url'   => null,
						'error'        => $e->getMessage(),
					];
				}

				$output->writeln( '' );
			}
		}

		// 6. Report aggregate results
		return $this->report_results( $results, $output );
	}

	/**
	 * Execute a single test by invoking the corresponding run:* command.
	 *
	 * @param string          $test_type         Test type (e.g., 'e2e', 'security').
	 * @param string          $profile           Profile name to run.
	 * @param string          $sut               System Under Test slug.
	 * @param bool            $is_async          Whether to run asynchronously.
	 * @param bool            $print_report_url  Whether to print report URLs.
	 * @param OutputInterface $output            Output interface.
	 * @return array{exit_code: int, was_enqueued: bool, test_id: string|null, report_url: string|null} Exit code, enqueue status, test ID, and report URL
	 */
	private function execute_test(
		string $test_type,
		string $profile,
		string $sut,
		bool $is_async,
		bool $print_report_url,
		OutputInterface $output
	): array {
		$command = $this->getApplication()->find( "run:{$test_type}" );

		$params = [
			'sut'       => $sut,
			'--profile' => $profile,
		];

		$was_enqueued = false;

		// Try to pass --async option for remote tests (they support it)
		// Local tests like run:e2e don't have this option, so we'll catch and ignore
		try {
			if ( $is_async && $command->getDefinition()->hasOption( 'async' ) ) {
				$params['--async'] = true;
				$was_enqueued      = true;
			}
		} catch ( \Exception $e ) {
			// Command doesn't have --async option, that's fine
		}

		// Always print report URL for enqueued tests, or if explicitly requested
		if ( $print_report_url || $was_enqueued ) {
			$params['--print-report-url'] = true;
		}

		// Create a capturing output that shows in real-time AND captures for parsing
		$capturing_output = new CapturingOutput( $output );

		// Run the command with capturing output
		$exit_code = $command->run( new ArrayInput( $params ), $capturing_output );

		// Get captured output for parsing
		$captured_output = $capturing_output->getCaptured();

		// Parse output for test ID and report URL
		$test_id    = $this->extract_test_id( $captured_output );
		$report_url = $this->extract_report_url( $captured_output );

		return [
			'exit_code'    => $exit_code,
			'was_enqueued' => $was_enqueued,
			'test_id'      => $test_id,
			'report_url'   => $report_url,
		];
	}

	/**
	 * Extract test ID from command output.
	 *
	 * @param string $output Command output.
	 * @return string|null Test ID if found.
	 */
	private function extract_test_id( string $output ): ?string {
		// Match "Test ID: 1413483" pattern
		if ( preg_match( '/Test ID:\s*(\d+)/i', $output, $matches ) ) {
			return $matches[1];
		}
		return null;
	}

	/**
	 * Extract report URL from command output.
	 *
	 * @param string $output Command output.
	 * @return string|null Report URL if found.
	 */
	private function extract_report_url( string $output ): ?string {
		// Match report URL patterns (Remote URL:, View Results:, etc.)
		if ( preg_match( '/(?:Remote URL|View Results|Report):\s*(https?:\/\/[^\s]+)/i', $output, $matches ) ) {
			return $matches[1];
		}

		// Match bare URLs from e2e test results (compatibility dashboard)
		if ( preg_match( '/(https?:\/\/[^\s]*compatibilitydashboard[^\s]+)/i', $output, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Count total number of tests in a group.
	 *
	 * @param array<string, array<string>> $group Group configuration.
	 * @return int Total number of tests
	 */
	private function count_tests( array $group ): int {
		$count = 0;
		foreach ( $group as $profiles ) {
			$count += count( $profiles );
		}
		return $count;
	}

	/**
	 * Report aggregate results.
	 *
	 * @param array<string, array<string, mixed>> $results Test results.
	 * @param OutputInterface                     $output  Output interface.
	 * @return int Exit code
	 */
	private function report_results( array $results, OutputInterface $output ): int {
		// Separate results into enqueued vs executed
		$enqueued = array_filter( $results, fn( $r ) => $r['was_enqueued'] );
		$executed = array_filter( $results, fn( $r ) => ! $r['was_enqueued'] );

		$executed_failures = array_filter( $executed, fn( $r ) => ! $r['success'] );
		$executed_passed   = array_filter( $executed, fn( $r ) => $r['success'] );

		$output->writeln( '<info>═══════════════════════════════════════</info>' );
		$output->writeln( '<info>Group Execution Summary</info>' );
		$output->writeln( '<info>═══════════════════════════════════════</info>' );
		$output->writeln( '' );

		// Show executed tests results
		if ( ! empty( $executed ) ) {
			$output->writeln( '<comment>Executed Tests:</comment>' );
			foreach ( $executed as $label => $result ) {
				if ( $result['success'] ) {
					$output->writeln( "  <info>✓</info> {$label} - PASSED" );
				} else {
					$error_msg = isset( $result['error'] ) ? ' (' . $result['error'] . ')' : '';
					$output->writeln( "  <error>✗</error> {$label} - FAILED{$error_msg}" );
				}

				// Show report URL for executed tests if available
				if ( ! empty( $result['report_url'] ) ) {
					$output->writeln( "      URL: {$result['report_url']}" );
				}
			}
			$output->writeln( '' );
		}

		// Show enqueued tests
		if ( ! empty( $enqueued ) ) {
			$output->writeln( '<comment>Enqueued Tests (Async):</comment>' );
			foreach ( $enqueued as $label => $result ) {
				$status_line = "  <info>→</info> {$label} - Enqueued";
				if ( ! empty( $result['test_id'] ) ) {
					$status_line .= " (ID: {$result['test_id']})";
				}
				$output->writeln( $status_line );

				// Show check status command
				if ( ! empty( $result['test_id'] ) ) {
					$output->writeln( "      Check: qit get {$result['test_id']}" );
				}

				// Show report URL if available
				if ( ! empty( $result['report_url'] ) ) {
					$output->writeln( "      URL: {$result['report_url']}" );
				}
			}
			$output->writeln( '' );
		}

		// Final summary
		$summary_parts = [];
		if ( ! empty( $executed_passed ) ) {
			$summary_parts[] = sprintf( '<info>%d passed</info>', count( $executed_passed ) );
		}
		if ( ! empty( $executed_failures ) ) {
			$summary_parts[] = sprintf( '<error>%d failed</error>', count( $executed_failures ) );
		}
		if ( ! empty( $enqueued ) ) {
			$summary_parts[] = sprintf( '<comment>%d enqueued</comment>', count( $enqueued ) );
		}

		$output->writeln( 'Overall: ' . implode( ', ', $summary_parts ) );
		$output->writeln( '' );

		// Return failure if any executed test failed
		return empty( $executed_failures ) ? Command::SUCCESS : Command::FAILURE;
	}
}
