<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\RequestBuilder;
use QIT_CLI\TestGroup;
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
	protected Cache $cache;
	protected Auth $auth;
	protected Upload $upload;
	protected WooExtensionsList $woo_extensions_list;
	protected TestGroup $test_group;

	public function __construct( Cache $cache, Auth $auth, Upload $upload, WooExtensionsList $woo_extensions_list, TestGroup $test_group ) {
		$this->cache               = $cache;
		$this->auth                = $auth;
		$this->upload              = $upload;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->test_group          = $test_group;
	}

	public function register_commands( Application $application ): void {
		$ignored_test_types = [ 'activation' ];
		foreach ( $this->cache->get_manager_sync_data( 'test_types' ) as $test_type ) {
			if ( in_array( $test_type, $ignored_test_types, true ) ) {
				continue;
			}
			$this->register_command_by_schema( $application, $test_type, $this->cache->get_manager_sync_data( 'schemas' )[ $test_type ] );
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
		$command = new class( $test_type, $this->auth, $this->upload, $this->woo_extensions_list, $this->test_group ) extends DynamicCommand {
			protected Auth $auth;
			protected WooExtensionsList $woo_extensions_list;
			protected Upload $upload;
			protected TestGroup $test_group;

			public function __construct( string $test_type, Auth $auth, Upload $upload, WooExtensionsList $woo_extensions_list, TestGroup $test_group ) {
				$this->auth                = $auth;
				$this->woo_extensions_list = $woo_extensions_list;
				$this->upload              = $upload;
				$this->test_group          = $test_group;
				parent::__construct( $test_type );
			}

			public function doExecute( InputInterface $input, OutputInterface $output ): int {
				// Get the test profile configuration using the simplified API
				$profile_name = $input->getOption( 'profile' ) ?? 'default';
				$test_config  = $this->get_current_test_profile( $this->test_type, $profile_name );

				// Use the merged test configuration as the base for API options
				$options = $test_config;

				// Add/override with CLI-specific options
				/* ─────────────────── Resolve SUT ────────────────────── */
				$sut_arg = $input->getArgument( 'sut' );
				
				// Check if SUT is provided either via CLI argument or config
				if ( empty( $sut_arg ) && empty( $options['sut']['slug'] ?? '' ) ) {
					$output->writeln(
						'<error>No System‑Under‑Test (SUT) specified. '
						. 'Provide a slug argument or define "sut" in test profile.</error>'
					);
					return Command::INVALID;
				}
				
				// Handle SUT argument (slug or ID)
				if ( ! empty( $sut_arg ) ) {
					if ( is_numeric( $sut_arg ) ) {
						$options['woo_id'] = $sut_arg;
					} else {
						$options['woo_id'] = $this->woo_extensions_list->get_woo_extension_id_by_slug( $sut_arg );
					}
				} elseif ( ! empty( $options['sut']['slug'] ?? '' ) ) {
					// Use SUT from config if no CLI argument provided
					$sut_slug = $options['sut']['slug'];
					if ( is_numeric( $sut_slug ) ) {
						$options['woo_id'] = $sut_slug;
					} else {
						$options['woo_id'] = $this->woo_extensions_list->get_woo_extension_id_by_slug( $sut_slug );
					}
				}

				// Handle zip option with smart detection
				$zip_opt = $input->getOption( 'zip' );
				$zip_flag_alone = $input->getParameterOption( '--zip', 'NOT_SET' ) === null;

				if ( $zip_opt !== null || $zip_flag_alone ) {
					// Determine the path/URL to use
					if ( $zip_flag_alone ) {
						$sut_for_zip = $sut_arg ?: ( $options['sut']['slug'] ?? 'extension' );
						$options['zip'] = $sut_for_zip . '.zip';
					} else {
						$options['zip'] = $zip_opt;
					}

					// For bare --zip flag, check if file exists
					if ( $zip_flag_alone && ! file_exists( $options['zip'] ) ) {
						$output->writeln( sprintf(
							"<error>Error: The specified ZIP file '%s' does not exist.</error>" .
							"<info>\nTo run the command, use one of the following options:" .
							"\n1. Provide the ZIP file name without an argument to infer from the slug or ID:" .
							"\n   run:security my-extension --zip" .
							"\n\n2. Provide the ZIP path as a parameter:" .
							"\n   run:security my-extension --zip=/some/path/my-extension.zip" .
							"\n\n3. Provide a URL:" .
							"\n   run:security my-extension --zip=https://example.com/plugin.zip" .
							"\n\n4. Provide a directory path:" .
							"\n   run:security my-extension --zip=/path/to/plugin/directory</info>",
							$options['zip']
						) );

						return Command::FAILURE;
					}
				}

				// Upload zip if provided
				if ( ! empty( $options['zip'] ) ) {
					$options['upload_id'] = $this->upload->upload_build( 'build', $options['woo_id'], $options['zip'], $output );
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

				// Handle group
				if ( $input->getOption( 'group' ) ) {
					try {
						$this->test_group->create_or_update( $options, $this->test_type, $output, null );
					} catch ( \Exception $e ) {
						$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

						return Command::FAILURE;
					}

					$output->writeln( sprintf( '<info>Group item successfully added.</info>' ) );

					return Command::SUCCESS;
				}

				// Handle QIT_SELF_TEST=options
				if ( getenv( 'QIT_SELF_TEST' ) === 'options' ) {
					$output->write( json_encode( $options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

					return Command::SUCCESS;
				}

				// Send to API
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

				// Handle --wait option
				if ( $input->getOption( 'wait' ) ) {
					return $this->waitForTestCompletion( $response, $input, $output );
				}

				// Output result
				if ( $input->getOption( 'json' ) ) {
					$output->write( $json );

					return Command::SUCCESS;
				}

				$output->writeln( sprintf( '<info>Test started on QIT servers!</info>' ) );
				$table = new Table( $output );
				$table
					->setHorizontal()
					->setStyle( 'compact' )
					->setHeaders( [ 'Test Run ID', 'Result URL' ] );
				$table->addRow( [ $response['test_run_id'], $response['test_results_manager_url'] ] );
				$table->render();
				$output->writeln( '' );

				$bin = isset( $_SERVER['argv'][0] ) ? basename( $_SERVER['argv'][0] ) : '';
				$output->writeln( sprintf( '<info>You can follow up on the result of the test using the URL above, with the command "%s %s %d", or by passing the "--wait" option when running tests.</info>', $bin, GetCommand::getDefaultName(), $response['test_run_id'] ) );

				return Command::SUCCESS;
			}

			/**
			 * @param array<string, mixed> $response
			 */
			protected function waitForTestCompletion( array $response, InputInterface $input, OutputInterface $output ): int {
				// Show a message if user aborts waiting.
				foreach ( [ \SIGINT, \SIGTERM ] as $signal ) {
					$this->getApplication()->getSignalRegistry()->register( $signal, static function () use ( $output ) {
						$output->writeln( sprintf( '<comment>The test is still executing on the QIT Servers, but we have skipped the wait. You can always check the status of the test by running the "%s" command.</comment>', GetCommand::getDefaultName() ) );
						exit( 124 );
					} );
				}

				$timeout = $input->getOption( 'timeout' );
				if ( is_null( $timeout ) ) {
					$timeout = ( $this->test_type === 'woo-e2e' ) ? 3600 * 2 : 1800;
				}
				$timeout = min( 3600 * 2, max( 10, $timeout ) );

				$start = time();
				do {
					$poll_interval = rand( 5, 15 );
					if ( getenv( 'QIT_POLL_INTERVAL' ) && is_numeric( getenv( 'QIT_POLL_INTERVAL' ) ) ) {
						$poll_interval = min( 300, max( 5, getenv( 'QIT_POLL_INTERVAL' ) ) );
					}

					sleep( $poll_interval );

					$command  = $this->getApplication()->find( GetCommand::getDefaultName() );
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

						return 124;
					}
				} while ( true );

				$output->writeln( sprintf( '<info>Test run completed.</info>' ) );
				$command   = $this->getApplication()->find( GetCommand::getDefaultName() );
				$exit_code = $command->run( new ArrayInput( [
					'test_run_id' => $response['test_run_id'],
					'--json'      => $input->getOption( 'json' ),
				] ), $output );

				if ( $input->getOption( 'ignore-fail' ) ) {
					return 0;
				} else {
					return $exit_code;
				}
			}
		};

		$command->setName( "run:$test_type" );

		// Add schema-based options
		static::add_schema_to_command( $command, $schema );

		// Add standard options
		$command->addArgument( 'sut', InputArgument::OPTIONAL, 'Extension slug or ID (system‑under‑test)' );
		$command->addOption( 'zip', null, InputOption::VALUE_OPTIONAL, 'Use a custom ZIP (or directory/URL) as the SUT build' );
		$command->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, '(Optional) Whether to return the JSON object of the test that was created.', false );
		$command->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Optional) Wait for the test to finish before finishing command execution.', false );
		$command->addOption( 'timeout', 't', InputOption::VALUE_OPTIONAL, '(Optional) Seconds to wait for a test to finish before failing the command.', null );
		$command->addOption( 'ignore-fail', 'i', InputOption::VALUE_NEGATABLE, '(Optional) If set, exit status code will be zero even if test fails.', false );
		$command->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the test run into a group.', false );

		$command->add_option_to_send( 'zip' );

		if ( $test_type === 'api' ) {
			$command->setHidden( true );
		}

		// This will be hidden while custom test type is in development, but kept as an alias to "woo-e2e".
		if ( $test_type === 'e2e' ) {
			try {
				$hide_e2e = $this->cache->get_manager_sync_data( 'hide_e2e' );
			} catch ( \Exception $e ) {
				$hide_e2e = true;
			}

			if ( ! $hide_e2e ) {
				$application->add( App::make( RunE2ECommand::class ) );
			}
		} else {
			$application->add( $command );
		}
	}
}
