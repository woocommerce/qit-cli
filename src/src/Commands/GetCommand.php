<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;
use function QIT_CLI\open_in_browser;

class GetCommand extends QITCommand {
	protected static $defaultName = 'get'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Get a single test run.' )
			->setHelp( 'Get a single test run. Exit status codes: 0 (success), 1 (failed or cancelled), 3 (warning).' )
			->addArgument( 'test_run_id', InputArgument::REQUIRED, 'The ID of the test run.' )
			->addOption( 'open', 'o', InputOption::VALUE_NEGATABLE, 'Open the test run in the browser.', false )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return structured JSON output.', false )
			->addOption( 'json-results', null, InputOption::VALUE_NONE, 'Output only the test results as JSON.' )
			->addOption( 'print-report-url', null, InputOption::VALUE_NEGATABLE, 'Print the report URL (contains a sensitive bearer-style token).', false )
			->addOption( 'check_finished', null, InputOption::VALUE_NONE, 'Return success if test has finished. Failure if not.', null );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_id' => $input->getArgument( 'test_run_id' ),
				] )
				->with_retry( 3 )
				->request();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );
			$output->writeln( '<comment>If this persists, run: qit feedback "describe the issue"</comment>' );

			return Command::FAILURE;
		}

		$test_run = json_decode( $json, true );

		if ( ! is_array( $test_run ) || ! array_key_exists( 'status', $test_run ) ) {
			return Command::FAILURE;
		}

		switch ( $test_run['status'] ) {
			case 'success':
				$exit_status_code = 0;
				break;
			case 'failed':
				$exit_status_code = 1;
				break;
			case 'warning':
				$exit_status_code = 3; // 2 is reserved by OS
				break;
			default:
				$exit_status_code = 1; // Default to failure for unknown status
		}

		if ( $input->getOption( 'json' ) ) {
			$data = json_decode( $json, true );
			if ( is_array( $data ) ) {
				self::decode_json_fields( $data );
			}
			$output->write( json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			return $exit_status_code;
		}

		if ( $input->getOption( 'json-results' ) ) {
			$results = null;
			if ( ! empty( $test_run['ctrf_json'] ) ) {
				$results = json_decode( $test_run['ctrf_json'], true );
			}
			if ( is_null( $results ) && ! empty( $test_run['test_result_json'] ) ) {
				$results = json_decode( $test_run['test_result_json'], true );
			}
			if ( is_null( $results ) ) {
				$output->writeln( '<error>No test results available. The test may still be running.</error>' );

				return Command::FAILURE;
			}
			$output->write( json_encode( $results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			return $exit_status_code;
		}

		if ( $input->getOption( 'check_finished' ) ) {
			if ( $test_run['update_complete'] === true || $test_run['status'] === 'cancelled' ) {
				return Command::SUCCESS;
			} else {
				return Command::FAILURE;
			}
		}

		/**
		 * Some test types can't be properly rendered in CLI context,
		 * so if the user requests it, we open it in browser/show the link.
		 */
		if ( $input->getOption( 'open' ) && isset( $test_run['test_results_manager_url'] ) ) {
			$output->writeln( '<info>To view this test run, please open this URL:</info>' );
			$output->writeln( $test_run['test_results_manager_url'] );

			try {
				open_in_browser( $test_run['test_results_manager_url'] );
			} catch ( \Exception $e ) {
				if ( $output->isVerbose() ) {
					$output->writeln( sprintf( 'Could not open URL in browser. Reason: %s', $e->getMessage() ) );
				}
			}

			return $exit_status_code;
		}

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
			'ctrf_json',
			'debug_log',
			'test_type',
			'runner',
			'workflow_id',
		];

		$campaign_state = self::get_campaign_state( $test_run );

		// Prepare the data to be rendered.
		foreach ( $test_run as $test_key => &$v ) {
			// Remove empty columns.
			if ( empty( $v ) ) {
				unset( $test_run[ $test_key ] );
				continue;
			}

			switch ( $test_key ) {
				case 'is_development':
					$v = 'Yes'; // If this is not empty, it's "Yes".
					break;
			}

			if ( ! is_scalar( $v ) ) {
				// For WooExtension, render just the name.
				if ( $test_key === 'woo_extension' ) {
					$test_run[ $test_key ] = $test_run[ $test_key ]['name'];
					continue;
				}
				// Remove non-scalar values so that we can render it on the table.
				unset( $test_run[ $test_key ] );
				continue;
			}

			// Remove some columns.
			if ( in_array( $test_key, $columns_to_hide, true ) ) {
				unset( $test_run[ $test_key ] );
			}

			// Rename "Test Results Manager URL" to "Result URL".
			if ( $test_key === 'test_results_manager_url' ) {
				if ( $input->getOption( 'print-report-url' ) ) {
					$test_run['result_url'] = $v;
				}
				unset( $test_run['test_results_manager_url'] );
			}
		}

		unset( $v );

		if ( $campaign_state !== null ) {
			$test_run['campaign_state'] = $campaign_state;
		}

		// woo_extensions => Woo Extensions.
		foreach ( $test_run as $test_key => $v ) {
			$test_run[ ucwords( str_replace( '_', ' ', $test_key ) ) ] = $v;
			unset( $test_run[ $test_key ] );
			continue;
		}

		$table = new Table( $output );
		$table
			->setHorizontal()
			->setStyle( 'compact' )
			->setHeaders( array_keys( $test_run ) )
			->setRows( [ $test_run ] );
		$table->render();

		return $exit_status_code;
	}

	/**
	 * Decode stringified JSON fields into proper arrays.
	 *
	 * @param array<string,mixed> $data The test run data to modify in-place.
	 */
	public static function decode_json_fields( array &$data ): void {
		$json_fields = [ 'ctrf_json', 'test_result_json', 'debug_log' ];
		foreach ( $json_fields as $field ) {
			if ( ! empty( $data[ $field ] ) && is_string( $data[ $field ] ) ) {
				$decoded = json_decode( $data[ $field ], true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$data[ $field ] = $decoded;
				}
			}
		}
	}

	/**
	 * Read the API-fuzz campaign state without changing the Manager response contract.
	 *
	 * @param array<string,mixed> $test_run Manager test run data.
	 */
	public static function get_campaign_state( array $test_run ): ?string {
		if ( ! isset( $test_run['test_type'] ) || $test_run['test_type'] !== 'api-fuzz' || empty( $test_run['test_result_json'] ) ) {
			return null;
		}

		$results = $test_run['test_result_json'];
		if ( is_string( $results ) ) {
			$results = json_decode( $results, true );
		}

		if ( ! is_array( $results ) || ! isset( $results['campaign']['state'] ) || ! is_string( $results['campaign']['state'] ) ) {
			return null;
		}

		return $results['campaign']['state'];
	}
}
