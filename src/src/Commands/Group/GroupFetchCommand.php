<?php

namespace QIT_CLI\Commands\Group;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

/**
 * Fetch status of a registered group from QIT Manager.
 *
 * Groups are registered when running `qit run:group` with tests that use --async.
 * This command allows you to check the status of all tests in a registered group.
 */
class GroupFetchCommand extends QITCommand {

	protected static $defaultName = 'group:fetch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Fetch status of a registered group from QIT Manager' )
			->addArgument( 'group-identifier', InputArgument::REQUIRED, 'The group identifier (shown after running a group)' )
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Output the results in JSON format' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$group_identifier = $input->getArgument( 'group-identifier' );

		try {
			$group = $this->fetch_from_manager( $group_identifier );

			if ( empty( $group ) ) {
				$output->writeln( sprintf( '<error>No group found with identifier: %s</error>', $group_identifier ) );
				return Command::FAILURE;
			}

			if ( $input->getOption( 'json' ) ) {
				$output->writeln( json_encode( $group, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
				return Command::SUCCESS;
			}

			$this->display_group_status( $group, $output );
			return Command::SUCCESS;

		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to fetch group: %s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		}
	}

	/**
	 * Fetch group data from QIT Manager.
	 *
	 * @param string $group_identifier The group identifier.
	 * @return array<string, mixed> Group data from Manager.
	 * @throws \Exception If the API request fails.
	 */
	private function fetch_from_manager( string $group_identifier ): array {
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-group-test-runs' ) )
			->with_method( 'POST' )
			->with_post_body( [ 'group_identifier' => $group_identifier ] )
			->request();

		return json_decode( $response, true ) ?? [];
	}

	/**
	 * Display group status in a human-readable format.
	 *
	 * @param array<string, mixed> $group  Group data from Manager.
	 * @param OutputInterface      $output Output interface.
	 * @return void
	 */
	private function display_group_status( array $group, OutputInterface $output ): void {
		$output->writeln( '<info>═══════════════════════════════════════</info>' );
		$output->writeln( '<info>Group Status</info>' );
		$output->writeln( '<info>═══════════════════════════════════════</info>' );
		$output->writeln( '' );

		if ( isset( $group['group_id'] ) ) {
			$output->writeln( sprintf( 'Group ID: <comment>%s</comment>', $group['group_id'] ) );
		}
		if ( isset( $group['group_identifier'] ) ) {
			$output->writeln( sprintf( 'Group Identifier: <comment>%s</comment>', $group['group_identifier'] ) );
		}

		$output->writeln( '' );

		if ( ! isset( $group['test_runs'] ) || empty( $group['test_runs'] ) ) {
			$output->writeln( '<comment>No test runs found in this group.</comment>' );
			return;
		}

		$output->writeln( sprintf( '<comment>Test Runs (%d total):</comment>', count( $group['test_runs'] ) ) );
		$output->writeln( '' );

		foreach ( $group['test_runs'] as $test_run ) {
			$this->display_test_run( $test_run, $output );
		}
	}

	/**
	 * Display a single test run's status.
	 *
	 * @param array<string, mixed> $test_run Test run data.
	 * @param OutputInterface      $output   Output interface.
	 * @return void
	 */
	private function display_test_run( array $test_run, OutputInterface $output ): void {
		// Status icon
		$status = $test_run['status'] ?? 'unknown';
		$icon   = $this->get_status_icon( $status );

		$output->writeln( sprintf( '  %s Test Run ID: <info>%s</info>', $icon, $test_run['test_run_id'] ?? 'N/A' ) );

		// Extension info
		if ( isset( $test_run['woo_extension'] ) ) {
			$ext = $test_run['woo_extension'];
			$output->writeln( sprintf( '    Extension: %s (ID: %s)', $ext['name'] ?? 'N/A', $ext['id'] ?? 'N/A' ) );
		}

		// Test type
		if ( isset( $test_run['test_type_display'] ) ) {
			$output->writeln( sprintf( '    Test Type: %s', $test_run['test_type_display'] ) );
		}

		// Status with color
		$status_display = $this->format_status( $status );
		$output->writeln( sprintf( '    Status: %s', $status_display ) );

		// URL
		if ( isset( $test_run['test_results_manager_url'] ) ) {
			$output->writeln( sprintf( '    URL: <comment>%s</comment>', $test_run['test_results_manager_url'] ) );
		}

		$output->writeln( '' );
	}

	/**
	 * Get icon for test status.
	 *
	 * @param string $status Test run status.
	 * @return string Status icon.
	 */
	private function get_status_icon( string $status ): string {
		switch ( $status ) {
			case 'success':
				return '<info>✓</info>';
			case 'failed':
			case 'hanged':
				return '<error>✗</error>';
			case 'warning':
				return '<comment>⚠</comment>';
			case 'pending':
			case 'running':
				return '<comment>→</comment>';
			case 'cancelled':
			case 'skipped':
				return '<comment>⊘</comment>';
			default:
				return '<comment>?</comment>';
		}
	}

	/**
	 * Format status with appropriate color.
	 *
	 * @param string $status Test run status.
	 * @return string Formatted status.
	 */
	private function format_status( string $status ): string {
		switch ( $status ) {
			case 'success':
				return '<info>' . strtoupper( $status ) . '</info>';
			case 'failed':
			case 'hanged':
				return '<error>' . strtoupper( $status ) . '</error>';
			case 'warning':
				return '<comment>' . strtoupper( $status ) . '</comment>';
			default:
				return strtoupper( $status );
		}
	}
}
