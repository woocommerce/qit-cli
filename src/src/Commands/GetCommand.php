<?php

namespace QIT_CLI\Commands;

use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;
use function QIT_CLI\open_in_browser;

class GetCommand extends Command {
	protected static $defaultName = 'get'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Get a single test run.' )
			->setHelp( 'Get a single test run. Exit status codes: 0 (success), 1 (failed), 2 (warning), 3 (others).' )
			->addArgument( 'test_run_id', InputArgument::REQUIRED )
			->addOption( 'open', 'o', InputOption::VALUE_NEGATABLE, 'Open the test run in the browser.', false )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			->addOption( 'check_finished', null, InputOption::VALUE_NONE, 'Return success if test has finished. Failure if not.', null );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_id' => $input->getArgument( 'test_run_id' ),
				] )
				->request();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

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
				$exit_status_code = 2;
				break;
			default:
				$exit_status_code = 3;
		}

		if ( $input->getOption( 'json' ) ) {
			$output->write( $json );

			return $exit_status_code;
		}

		if ( $input->getOption( 'check_finished' ) ) {
			$non_finished = [ 'pending', 'dispatched' ];

			if ( in_array( $test_run['status'], $non_finished, true ) ) {
				return Command::FAILURE;
			} else {
				return Command::SUCCESS;
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

		$columns_to_hide = [ 'test_result_aws_expiration', 'test_result_manager_expiration', 'test_result_json', 'event', 'client' ];

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
				$test_run['result_url'] = $v;
				unset( $test_run['test_results_manager_url'] );
			}
		}

		unset( $v );

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
}
