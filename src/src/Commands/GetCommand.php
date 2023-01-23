<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Auth;
use QIT_CLI\Config;
use QIT_CLI\RequestBuilder;
use QIT_CLI\TestTypes;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;
use function QIT_CLI\open_in_browser;

class GetCommand extends Command {
	protected static $defaultName = 'get'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Config $config */
	protected $config;

	/** @var Auth $auth */
	protected $auth;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	/** @var TestTypes $test_types */
	protected $test_types;

	public function __construct( Config $config, Auth $auth, WooExtensionsList $woo_extensions_list, TestTypes $test_types ) {
		$this->config              = $config;
		$this->auth                = $auth;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->test_types          = $test_types;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Get a single test run.' )
			->setHelp( 'Get a single test run.' )
			->addArgument( 'test_run_id', InputArgument::REQUIRED );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_id' => $input->getArgument( 'test_run_id' ),
				] )
				->request();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$test_run = json_decode( $response, true );

		if ( ! is_array( $test_run ) ) {
			$output->writeln( '<error>Could not retrieve test run.</error>' );

			if ( $output->isVeryVerbose() ) {
				$output->writeln( 'Raw response:' );
				$output->writeln( $response );
			}

			return Command::FAILURE;
		}

		if ( empty( $test_run ) ) {
			$output->writeln( 'No test run found.' );

			return Command::SUCCESS;
		}

		// Some test runs (such as Compatibility) can't be properly rendered in CLI context, so we open it in browser/show the link.
		if ( isset( $test_run['link'] ) ) {
			$output->writeln( '<info>To view this test run, please open this URL:</info>' );
			$output->writeln( $test_run['link'] );

			try {
				open_in_browser( $test_run['link'] );
			} catch ( \Exception $e ) {
				if ( $output->isVerbose() ) {
					$output->writeln( sprintf( 'Could not open URL in browser. Reason: %s', $e->getMessage() ) );
				}
			}

			return Command::SUCCESS;
		}

		$columns_to_hide = [ 'test_result_aws_expiration', 'test_result_json' ];

		// Prepare the data to be rendered.
		foreach ( $test_run as $test_key => &$v ) {
			// Remove empty columns.
			if ( empty( $v ) ) {
				unset( $test_run[ $test_key ] );
			}

			switch ( $test_key ) {
				case 'is_development':
					if ( ! empty( $v ) ) {
						// 1 => Yes
						$v = 'Yes';
					}
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
			}

			// Remove some columns.
			if ( in_array( $test_key, $columns_to_hide, true ) ) {
				unset( $test_run[ $test_key ] );
			}
		}

		// woo_extensions => Woo Extensions.
		foreach ( $test_run as $test_key => $v ) {
			$test_run[ ucwords( str_replace( '_', ' ', $test_key ) ) ] = $v;
			unset( $test_run[ $test_key ] );
		}

		$table = new Table( $output );
		$table
			->setHorizontal()
			->setStyle( 'compact' )
			->setHeaders( array_keys( $test_run ) )
			->setRows( [ $test_run ] );
		$table->render();

		return Command::SUCCESS;
	}
}
