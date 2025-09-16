<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;
use function QIT_CLI\open_in_browser;

class GetMultipleCommand extends QITCommand {
	protected static $defaultName = 'get-multiple'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Get multiple test runs.' )
			->setHelp( 'Get multiple test runs by providing a comma-separated list of test_run_ids. Exit status codes: 0 (success), 1 (failed), 2 (warning), 3 (others).' )
			->addArgument( 'test_run_ids', InputArgument::REQUIRED, 'Comma-separated list of test run IDs.' )
			->addOption( 'open', 'o', InputOption::VALUE_NEGATABLE, 'Open the test runs in the browser.', false )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			->addOption( 'check_finished', null, InputOption::VALUE_NONE, 'Return success if all tests have finished. Failure if any not finished.', null );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$test_run_ids = $input->getArgument( 'test_run_ids' );
		$test_run_ids = array_filter( array_map( 'trim', explode( ',', $test_run_ids ) ) );

		if ( empty( $test_run_ids ) ) {
			$output->writeln( '<error>No test_run_ids provided.</error>' );

			return self::FAILURE;
		}

		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-multiple' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_ids' => implode( ',', $test_run_ids ),
				] )
				->with_retry( 3 )
				->request();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return self::FAILURE;
		}

		$test_runs = json_decode( $json, true );

		if ( ! is_array( $test_runs ) ) {
			return self::FAILURE;
		}

		// Determine exit codes for each test run and choose the "worst" one.
		// Mapping from status to code.
		$status_map        = [
			'success' => 0,
			'failed'  => 1,
			'warning' => 2,
		];
		$default_exit_code = 3; // Others.
		$overall_exit_code = 0; // If all success, 0. If any fail/warn, that sets a higher code.

		$check_finished = $input->getOption( 'check_finished' );
		$json_output    = $input->getOption( 'json' );
		$open_browser   = $input->getOption( 'open' );

		// If JSON requested, we print raw JSON once and exit.
		if ( $json_output ) {
			$output->write( $json );
			// Determine overall exit code based on each test.
			foreach ( $test_runs as $tr ) {
				$code = $this->determine_exit_code( $tr, $status_map, $default_exit_code );
				if ( $code > $overall_exit_code ) {
					$overall_exit_code = $code;
				}
			}

			return $overall_exit_code;
		}

		// If checking finished, return success if all are finished, otherwise fail.
		if ( $check_finished ) {
			foreach ( $test_runs as $tr ) {
				if ( ! isset( $tr['update_complete'] ) || $tr['update_complete'] !== true ) {
					// If any test is not finished, fail.
					return self::FAILURE;
				}
			}

			// All tests finished.
			return self::SUCCESS;
		}

		// Not JSON, not check_finished:
		// Print each test run in a table.
		foreach ( $test_runs as $test_run_id => $tr ) {
			$code = $this->determine_exit_code( $tr, $status_map, $default_exit_code );
			if ( $code > $overall_exit_code ) {
				$overall_exit_code = $code;
			}

			if ( $open_browser && isset( $tr['test_results_manager_url'] ) ) {
				$output->writeln( "<info>Test Run ID {$test_run_id}:</info>" );
				$output->writeln( '<info>To view this test run, please open this URL:</info>' );
				$output->writeln( $tr['test_results_manager_url'] );

				try {
					open_in_browser( $tr['test_results_manager_url'] );
				} catch ( \Exception $e ) {
					if ( $output->isVerbose() ) {
						$output->writeln( sprintf( 'Could not open URL in browser. Reason: %s', $e->getMessage() ) );
					}
				}

				continue; // No need to show table if we are opening in browser.
			}

			// Prepare data for table display.
			$display_data = $this->prepare_table_data( $tr );

			$output->writeln( "<info>Test Run ID {$test_run_id}:</info>" );
			$table = new Table( $output );
			$table->setColumnMaxWidth( 1, 80 );
			$table
				->setHorizontal()
				->setStyle( 'compact' )
				->setHeaders( array_keys( $display_data ) )
				->setRows( [ $display_data ] );
			$table->render();
			$output->writeln( '' );
		}

		return $overall_exit_code;
	}

	/**
	 * Determine the exit code from the test run.
	 *
	 * @param array<string,mixed> $test_run
	 * @param array<string,int>   $status_map
	 * @param int                 $default
	 *
	 * @return int
	 */
	protected function determine_exit_code( array $test_run, array $status_map, int $default ): int { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound
		$status = $test_run['status'] ?? '';
		if ( isset( $status_map[ $status ] ) ) {
			return $status_map[ $status ];
		}

		return $default;
	}

	/**
	 * Prepare table data for console output.
	 *
	 * @param array<string,mixed> $test_run
	 *
	 * @return array<string,mixed>
	 */
	protected function prepare_table_data( array $test_run ): array {
		$columns_to_hide = [
			'test_result_aws_expiration',
			'test_results_manager_expiration',
			'test_result_json',
			'event',
			'client',
			'run_id',
			'send_notifications',
			'update_complete',
			'ai_suggestion_status',
		];

		foreach ( $test_run as $test_key => &$v ) {
			// Remove empty columns.
			if ( empty( $v ) && $v !== '0' && $v !== false ) {
				unset( $test_run[ $test_key ] );
				continue;
			}

			if ( is_array( $v ) ) {
				// For WooExtension, render just the name.
				if ( $test_key === 'woo_extension' && isset( $v['name'] ) ) {
					$test_run[ $test_key ] = $v['name'];
				} else {
					unset( $test_run[ $test_key ] );
				}
				continue;
			}

			if ( in_array( $test_key, $columns_to_hide, true ) ) {
				unset( $test_run[ $test_key ] );
				continue;
			}

			switch ( $test_key ) {
				case 'is_development':
					// If set and not empty => Yes.
					$test_run[ $test_key ] = 'Yes';
					break;
				case 'test_results_manager_url':
					$test_run['result_url'] = $v;
					unset( $test_run['test_results_manager_url'] );
					break;
			}
		}
		unset( $v );

		// Rename keys: underscore to space, ucwords.
		$renamed = [];
		foreach ( $test_run as $test_key => $v ) {
			$renamed_key             = ucwords( str_replace( '_', ' ', $test_key ) );
			$renamed[ $renamed_key ] = $v;
		}

		return $renamed;
	}
}
