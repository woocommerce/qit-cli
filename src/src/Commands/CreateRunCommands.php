<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Auth;
use QIT_CLI\Environment;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CreateRunCommands extends DynamicCommandCreator {
	/** @var Environment $environment */
	protected $environment;

	/** @var Auth $auth */
	protected $auth;

	/** @var OutputInterface $output */
	protected $output;

	/** @var Upload $upload */
	protected $upload;

	/** @var WooExtensionsList $woo_extensions_list */
	protected $woo_extensions_list;

	public function __construct( Environment $environment, Auth $auth, Upload $upload, WooExtensionsList $woo_extensions_list ) {
		$this->environment         = $environment;
		$this->auth                = $auth;
		$this->output              = App::make( Output::class );
		$this->upload              = $upload;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	public function register_commands( Application $application ): void {
		foreach ( $this->environment->get_cache()->get_manager_sync_data( 'test_types' ) as $test_type ) {
			$this->register_command_by_schema( $application, $test_type, $this->environment->get_cache()->get_manager_sync_data( 'schemas' )[ $test_type ] );
		}
	}

	/**
	 * @param Application  $application An instance of the current DI.
	 * @param string       $test_type The test type.
	 * @param array<mixed> $schema The test type schema.
	 *
	 * @return void
	 */
	protected function register_command_by_schema( Application $application, string $test_type, array $schema ): void {
		$command = new class( $test_type, $this->auth, $this->upload, $this->woo_extensions_list ) extends DynamicCommand {
			/** @var Auth $auth */
			protected $auth;

			/** @var WooExtensionsList $woo_extensions_list */
			protected $woo_extensions_list;

			/** @var string $test_type */
			protected $test_type;

			/** @var Upload $upload */
			protected $upload;

			public function __construct( string $test_type, Auth $auth, Upload $upload, WooExtensionsList $woo_extensions_list ) {
				$this->auth                = $auth;
				$this->test_type           = $test_type;
				$this->woo_extensions_list = $woo_extensions_list;
				$this->upload              = $upload;
				parent::__construct();
			}

			public function execute( InputInterface $input, OutputInterface $output ) {
				try {
					$options = $this->parse_options( $input );
				} catch ( \Exception $e ) {
					$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

					return Command::FAILURE;
				}

				// Woo Extension ID / Slug.
				if ( is_numeric( $input->getArgument( 'woo_extension' ) ) ) {
					$options['woo_id'] = $input->getArgument( 'woo_extension' );
				} else {
					$options['woo_id'] = $this->woo_extensions_list->get_woo_extension_id_by_slug( $input->getArgument( 'woo_extension' ) );
				}

				// --zip without a value.
				if ( is_null( $input->getParameterOption( '--zip', 'NOT_SET' ) ) ) {
					$options['zip'] = $input->getArgument( 'woo_extension' ) . '.zip';

					/*
					 * Provide a custom error message if the inferred zip file does not exist,
					 * so that the user is aware he can also pass a path if he/she wishes.
					 */
					if ( ! file_exists( $options['zip'] ) ) {
						$output->writeln( sprintf(
							"<error>Error: The specified zip file '%s' does not exist.</error>" .
							"<info>\nTo run the command, use one of the following options:" .
							"\n1. Provide the zip file name without an argument to infer from the slug or ID:" .
							"\n   run:security my-extension --zip" .
							"\n\n2. Provide the zip path as a parameter:" .
							"\n   run:security my-extension --zip=/some/path/my-extension.zip</info>",
							$options['zip']
						) );

						return Command::FAILURE;
					}
				}

				// Upload zip.
				if ( ! empty( $options['zip'] ) ) {
					$options['upload_id'] = $this->upload->upload_build( $options['woo_id'], $input->getArgument( 'woo_extension' ), $options['zip'], $output );
					$options['event']     = 'cli_development_extension_test';
					unset( $options['zip'] );
				} else {
					$options['event'] = 'cli_published_extension_test';
				}

				// Convert "Additional Woo Plugins" Slugs to IDs.
				if ( ! empty( $options['additional_woo_plugins'] ) ) {
					$additional_woo_plugins = explode( ',', $options['additional_woo_plugins'] );
					foreach ( $additional_woo_plugins as &$awp ) {
						$awp = trim( $awp );
						if ( ! is_numeric( $awp ) ) {
							$awp = $this->woo_extensions_list->get_woo_extension_id_by_slug( $awp );
						}
					}

					$options['additional_woo_plugins'] = implode( ',', $additional_woo_plugins );
				}

				try {
					$output->writeln( sprintf( 'Running test...' ) );
					$json = ( new RequestBuilder( get_manager_url() . "/wp-json/cd/v1/enqueue-{$this->test_type}" ) )
						->with_method( 'POST' )
						->with_post_body( $options )
						->request();
				} catch ( \Exception $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				$response = json_decode( $json, true );

				if ( ! is_array( $response ) ) {
					return Command::FAILURE;
				}

				if ( ! isset( $response['test_run_id'] ) || ! isset( $response['test_results_manager_url'] ) ) {
					$output->writeln( 'Unexpected response. Missing "test_run_id" or "test_results_manager_url".' );

					return Command::FAILURE;
				}

				if ( $input->getOption( 'wait' ) ) {
					// Show a message if user aborts waiting.
					foreach ( [ \SIGINT, \SIGTERM ] as $signal ) {
						$this->getApplication()->getSignalRegistry()->register( $signal, static function () use ( $output ) {
							$output->writeln( sprintf( '<comment>The test is still executing on the QIT Servers, but we have skipped the wait. You can always check the status of the test by running the "%s" command.</comment>', GetCommand::getDefaultName() ) );
							exit( 124 );
						} );
					}

					// Minimum timeout is 10 seconds.
					$timeout = max( 10, $input->getOption( 'timeout' ) );

					// Maximum timeout is 2 hours.
					$timeout = min( 3600 * 2, $timeout );

					$start = time();
					do {
						$command = $this->getApplication()->find( GetCommand::getDefaultName() );

						// When checking for finished status, return status code is 0 if finished, 1 if not.
						$finished = $command->run( new ArrayInput( [
							'test_run_id'      => $response['test_run_id'],
							'--check_finished' => true,
						] ), $output );

						if ( $finished === 0 ) {
							break;
						}

						if ( time() - $start > $timeout ) {
							$output->writeln( '<comment>Timed out while waiting for test run to complete.</comment>' );
							$output->writeln( '<comment>The test is still executing in QIT servers, but the timeout for waiting was reached.</comment>' );

							// Timeout.
							return 124;
						}

						sleep( rand( 5, 15 ) );
					} while ( true );

					$output->writeln( sprintf( '<info>Test run completed.</info>' ) );
					$command = $this->getApplication()->find( GetCommand::getDefaultName() );

					// If waiting, the exit status code will come from the GetCommand.
					$exit_code = $command->run( new ArrayInput( [
						'test_run_id' => $response['test_run_id'],
						'--json'      => $input->getOption( 'json' ),
					] ), $output );

					if ( $input->getOption( 'ignore-fail' ) ) {
						return 0;
					} else {
						return $exit_code;
					}
				} else {
					if ( $input->getOption( 'ignore-fail' ) ) {
						$output->writeln( '<error>"--ignore-fail" can only be used with "--wait".</error>' );
					}
				}

				if ( $input->getOption( 'json' ) ) {
					$output->write( $json );

					return Command::SUCCESS;
				}

				$output->writeln( sprintf( '<info>Test started on the QIT Servers!</info>' ) );
				$table = new Table( $output );
				$table
					->setHorizontal()
					->setStyle( 'compact' )
					->setHeaders( [ 'Test Run ID', 'Result URL' ] );
				$table->addRow( [ $response['test_run_id'], $response['test_results_manager_url'] ] );
				$table->render();
				$output->writeln( '' );

				// Get the name of the script entrypoint.
				$bin = isset( $_SERVER['argv'][0] ) ? basename( $_SERVER['argv'][0] ) : '';

				$output->writeln( sprintf( '<info>You can follow up on the result of the test using the URL above, with the command "%s %s %d", or by passing the "--wait" option when running tests.</info>', $bin, GetCommand::getDefaultName(), $response['test_run_id'] ) );

				return Command::SUCCESS;
			}
		};

		$command
			->setName( "run:$test_type" );

		$this->add_schema_to_command( $command, $schema );

		// Extension slug/ID.
		$command->addArgument(
			'woo_extension',
			InputArgument::REQUIRED,
			'A WooCommerce Extension Slug or Marketplace ID.'
		);

		// Upload zip.
		$command->addOption(
			'zip',
			null,
			InputOption::VALUE_OPTIONAL,
			'(Optional) Run the test using a local zip file of the plugin. Useful for running the tests before publishing it to the Marketplace.'
		);

		// JSON Response.
		$command->addOption(
			'json',
			'j',
			InputOption::VALUE_NEGATABLE,
			'(Optional) Whether to return the JSON object of the test that was created.',
			false
		);

		// Wait for test to finish.
		$command->addOption(
			'wait',
			'w',
			InputOption::VALUE_NEGATABLE,
			'(Optional) Wait for the test to finish before finishing command execution.',
			false
		);

		// Timeout if waiting.
		$command->addOption(
			'timeout',
			't',
			InputOption::VALUE_OPTIONAL,
			'(Optional) Seconds to wait for a test to finish before failing the command. Default is 60 minutes. Min 10 seconds. Max 2 hours. (requires "--wait")',
			3600
		);

		// If set, exit status code will be zero even if test fails.
		$command->addOption(
			'ignore-fail',
			'i',
			InputOption::VALUE_NEGATABLE,
			'(Optional) If set, exit status code will be zero even if test fails. (requires "--wait")',
			false
		);

		$command->add_option_to_send( 'zip' );

		if ( $test_type === 'api' ) {
			$command->setHidden( true );
		}

		// This will be hidden while custom test type is in development, but kept as an alias to "woo-e2e".
		if ( $test_type === 'e2e' ) {
			try {
				$hide_e2e = $this->environment->get_cache()->get_manager_sync_data( 'hide_e2e' );
			} catch ( \Exception $e ) {
				// If it throws it's because it's not set, so we hide it.
				$hide_e2e = true;
			}

			if ( $hide_e2e ) {
				$command->setHidden( true );
			}
		}

		$application->add( $command );
	}
}
