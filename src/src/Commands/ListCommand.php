<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Auth;
use QIT_CLI\Environment;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class ListCommand extends Command {
	protected static $defaultName = 'list-tests'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	/** @var Auth $auth */
	protected $auth;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	public function __construct( Environment $environment, Auth $auth, WooExtensionsList $woo_extensions_list ) {
		$this->environment         = $environment;
		$this->auth                = $auth;
		$this->woo_extensions_list = $woo_extensions_list;
		parent::__construct();
	}

	protected function configure() {
		$test_types_list = implode( ', ', $this->environment->get_cache()->get_manager_sync_data( 'test_types' ) );
		$this
			->setDescription( 'List test runs.' )
			->addOption( 'test_status', 's', InputOption::VALUE_OPTIONAL, '(Optional) What test status to retrieve.' )
			->addOption( 'test_types', 't', InputOption::VALUE_OPTIONAL, '(Optional) What test types to retrieve. Allowed values: ' . $test_types_list, $test_types_list )
			->addOption( 'page', 'p', InputOption::VALUE_OPTIONAL, '(Optional) The page to get.', 1 )
			->addOption( 'per_page', 'e', InputOption::VALUE_OPTIONAL, '(Optional) How many results per page.', 10 )
			->addOption( 'woo_ids', 'w', InputOption::VALUE_OPTIONAL, '(Optional) Retrieve results for these Woo IDs.' )
			->setHelp( 'View a list of test runs.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( $input->getOption( 'woo_ids' ) ) {
			$woo_ids = $input->getOption( 'woo_ids' );
		} else {
			$woo_ids = [];

			foreach ( $this->woo_extensions_list->get_woo_extension_list() as $i ) {
				$woo_ids[] = $i['id'];
			}

			$woo_ids = implode( ',', $woo_ids );
		}

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'woo_ids'     => $woo_ids,
					'test_status' => $input->getOption( 'test_status' ),
					'test_types'  => $input->getOption( 'test_types' ),
					'page'        => $input->getOption( 'page' ),
					'per_page'    => $input->getOption( 'per_page' ),
				] )
				->request();
		} catch ( \Exception $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$test_runs = json_decode( $response, true );

		if ( ! is_array( $test_runs ) ) {
			$output->writeln( '<error>Could not retrieve test runs.</error>' );

			return Command::FAILURE;
		}

		if ( empty( $test_runs ) ) {
			$output->writeln( 'No test runs found.' );

			return Command::SUCCESS;
		}

		$columns_to_hide = [
			'test_log',
			'test_result_json',
			'test_result_aws_expiration',
			'test_results_manager_url',
			'test_results_manager_expiration',
			'is_development',
			'version',
			'client',
			'event',
			'debug_log',
		];

		// Prepare the data to be rendered.
		foreach ( $test_runs as $k => &$t ) {
			// Get the version before we remove it on the second foreach.
			foreach ( $t as $test_key => &$v ) {
				if ( $test_key === 'woo_extension' ) {
					if ( array_key_exists( 'version', $t ) ) {
						$v = sprintf( '%s (%s)', $this->getHelper( 'formatter' )->truncate( $v['name'], 20, '.' ), $t['version'] );
					}
				}
			}

			foreach ( $t as $test_key => &$v ) {
				switch ( $test_key ) {
					case 'test_result_aws_url':
						// Replace big test result URLS with "Available". They can be seen using the "get" command.
						if ( ! empty( $v ) ) {
							$v = 'Available';
						}
						break;
					case 'is_development':
						if ( ! empty( $v ) ) {
							$v = 'Yes';
						}
						break;
				}

				if ( ! is_scalar( $v ) ) {
					// Remove non-scalar values so that we can render it on the table.
					unset( $test_runs[ $k ][ $test_key ] );
					continue;
				}

				// Remove some columns.
				if ( in_array( $test_key, $columns_to_hide, true ) ) {
					unset( $test_runs[ $k ][ $test_key ] );
					continue;
				}
			}
		}

		// Rename columns. Try to make them shorter to fit most terminals.
		foreach ( $test_runs as $k => &$t ) {
			foreach ( $t as $test_key => $v ) {
				switch ( $test_key ) {
					case 'woo_extension':
						$t['Name/Version'] = $v;
						break;
					case 'test_type':
						$t['Test'] = $v;
						break;
					case 'wordpress_version':
						$t['WP'] = $v;
						break;
					case 'woocommerce_version':
						$t['WC'] = $v;
						break;
					case 'test_result_aws_url':
						$t['Report'] = $v;
						break;
					default:
						// woo_extensions => Woo Extensions.
						$t[ ucwords( str_replace( '_', ' ', $test_key ) ) ] = $v;
				}
				unset( $test_runs[ $k ][ $test_key ] );
			}
		}

		// Remove empty columns.
		$columns = array_keys( $test_runs[ array_rand( $test_runs ) ] );
		foreach ( $columns as $column ) {
			$all_empty = true;

			// Traverse over all results to find what is empty amongst all of them.
			foreach ( $test_runs as $test_run ) {
				if ( ! empty( $test_run[ $column ] ) ) {
					$all_empty = false;
					break;
				}
			}

			if ( $all_empty ) {
				// Remove the empty column.
				foreach ( $test_runs as $k => &$t ) {
					foreach ( $t as $test_key => $v ) {
						if ( $test_key === $column ) {
							unset( $test_runs[ $k ][ $test_key ] );
						}
					}
				}
			}
		}

		// Show the newest test runs on the bottom for convenience.
		$test_runs = array_reverse( $test_runs, true );

		$table = new Table( $output );
		$table
			->setHeaders( array_keys( $test_runs[ array_rand( $test_runs ) ] ) )
			->setRows( $test_runs );
		$table->render();

		return Command::SUCCESS;
	}
}
