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
				$this->setName( "run:$test_type" );
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
				$zip_opt        = $input->getOption( 'zip' );
				$zip_flag_alone = $input->getParameterOption( '--zip', 'NOT_SET' ) === null;

				if ( $zip_opt !== null || $zip_flag_alone ) {
					// Determine the path/URL to use
					if ( $zip_flag_alone ) {
						$sut_for_zip    = $sut_arg ?: ( $options['sut']['slug'] ?? 'extension' );
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

				// Handle QIT_SELF_TEST=remote_test
				if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
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
					->setHeaders( [ 'Test Run ID', 'Test Results URL' ] )
					->setRows( [
						[ $response['test_run_id'], $response['test_results_manager_url'] ],
					] );
				$table->render();

				return Command::SUCCESS;
			}

			private function waitForTestCompletion( array $response, InputInterface $input, OutputInterface $output ): int {
				$output->writeln( '<info>Waiting for test completion...</info>' );

				$get_command = App::make( GetCommand::class );
				$get_input   = new ArrayInput( [
					'test_run_id' => $response['test_run_id'],
					'--wait'      => true,
				] );

				return $get_command->run( $get_input, $output );
			}
		};

		self::add_schema_to_command( $command, $schema );
		$application->add( $command );
	}
}
