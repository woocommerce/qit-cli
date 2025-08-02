<?php
declare( strict_types=1 );

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
use function QIT_CLI\is_option_explicitly_provided;

/**
 * Dynamically registers remote test‑type commands (run:security, run:phpstan, …)
 * based on schemas fetched from the Manager.
 *
 * – Merges profile‑level configuration from qit.json with CLI overrides
 * – Supports SUT, ZIP uploads, groups, wait/timeout, etc.
 */
class CreateRunCommands extends DynamicCommandCreator {

	/** @var Cache */
	protected Cache $cache;
	/** @var Auth */
	protected Auth $auth;
	/** @var Upload */
	protected Upload $upload;
	/** @var WooExtensionsList */
	protected WooExtensionsList $woo_extensions_list;
	/** @var TestGroup */
	protected TestGroup $test_group;

	public function __construct(
		Cache $cache,
		Auth $auth,
		Upload $upload,
		WooExtensionsList $woo_extensions_list,
		TestGroup $test_group
	) {
		$this->cache               = $cache;
		$this->auth                = $auth;
		$this->upload              = $upload;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->test_group          = $test_group;
	}

	/**
	 * Public API
	 */
	public function register_commands( Application $application ): void {
		$ignored_test_types = [ 'activation' ];            // activation handled locally
		$schemas            = $this->cache->get_manager_sync_data( 'schemas' );

		foreach ( $this->cache->get_manager_sync_data( 'test_types' ) as $test_type ) {
			if ( \in_array( $test_type, $ignored_test_types, true ) ) {
				continue;
			}
			$this->register_command_by_schema( $application, $test_type, $schemas[ $test_type ] ?? [] );
		}
	}

	/**
	 * Register a command by schema.
	 *
	 * @param Application  $application The application instance.
	 * @param string       $test_type   The test type.
	 * @param array<mixed> $schema      The schema configuration.
	 */
	protected function register_command_by_schema( Application $application, string $test_type, array $schema ): void {

		/**
		 * Anonymous DynamicCommand – one per test‑type
		 */
		$command = new class(
			$test_type,
			$this->auth,
			$this->upload,
			$this->woo_extensions_list,
			$this->test_group
		) extends DynamicCommand {

			/** @var Auth */
			private Auth $auth;
			/** @var Upload */
			private Upload $upload;
			/** @var WooExtensionsList */
			private WooExtensionsList $woo_extensions_list;
			/** @var TestGroup */
			private TestGroup $test_group;

			public function __construct(
				string $test_type,
				Auth $auth,
				Upload $upload,
				WooExtensionsList $woo_extensions_list,
				TestGroup $test_group
			) {
				$this->auth                = $auth;
				$this->upload              = $upload;
				$this->woo_extensions_list = $woo_extensions_list;
				$this->test_group          = $test_group;
				parent::__construct( $test_type );
				$this->setName( "run:$test_type" );
				$this->setDescription( "Run $test_type tests remotely on QIT" );
			}

			/**
			 * Main execution
			 */
			public function doExecute( InputInterface $input, OutputInterface $output ): int {

				/****************************************************************
				 * 1.  Base configuration comes from qit.json profile
				 */
				$profile_name = is_option_explicitly_provided( $input, 'profile' )
					? (string) $input->getOption( 'profile' )
					: 'default';

				$options = $this->get_current_test_profile( $this->test_type, $profile_name );
				if ( ! \is_array( $options ) ) {
					$options = [];
				}

				/****************************************************************
				 * 2.  Apply CLI overrides (only what user provided)
				 */
				foreach ( $this->options_to_send as $opt_name ) {

					if ( ! is_option_explicitly_provided( $input, $opt_name ) ) {
						continue;                               // not typed – keep profile value
					}

					$cli_value = $input->getOption( $opt_name );

					// Merge list‑type options instead of replacing
					if ( \is_array( $cli_value ) && isset( $options[ $opt_name ] ) && \is_array( $options[ $opt_name ] ) ) {
						$options[ $opt_name ] = array_values( array_unique( array_merge(
							$options[ $opt_name ],
							$cli_value
						) ) );
					} else {
						$options[ $opt_name ] = $cli_value;
					}
				}

				/****************************************************************
				 * 3.  Resolve SUT (CLI arg > profile)
				 */
				$sut_arg = $input->getArgument( 'sut' ) ?: ( $options['sut']['slug'] ?? '' );
				if ( empty( $sut_arg ) ) {
					$output->writeln( '<error>No System‑Under‑Test specified (argument or profile).</error>' );

					return Command::INVALID;
				}

				$options['woo_id'] = $this->slug_or_id_to_id( $sut_arg );

				/****************************************************************
				 * 4.  Handle --zip (build upload)
				 */
				$this->process_zip_option( $input, $output, $sut_arg, $options );

				/****************************************************************
				 * 5.  Additional Woo plugins (comma‑separated slugs/IDs → IDs)
				 */
				if ( ! empty( $options['additional_woo_plugins'] ) ) {
					$options['additional_woo_plugins'] = $this->map_multiple_slugs_to_ids(
						$options['additional_woo_plugins']
					);
				}

				/****************************************************************
				 * 6.  Group creation if requested
				 */
				if ( $input->getOption( 'group' ) ) {
					try {
						$this->test_group->create_or_update( $options, $this->test_type, $output, null );
						$output->writeln( '<info>Group item successfully added.</info>' );

						return Command::SUCCESS;
					} catch ( \Throwable $e ) {
						$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

						return Command::FAILURE;
					}
				}

				/****************************************************************
				 * 7.  Self‑test shortcut
				 */
				if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
					$output->write( json_encode( $options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

					return Command::SUCCESS;
				}

				/****************************************************************
				 * 8.  Send request to Manager
				 */
				try {
					$output->writeln( 'Running test on QIT servers…' );
					$json = ( new RequestBuilder( get_manager_url() . "/wp-json/cd/v1/enqueue-{$this->test_type}" ) )
						->with_method( 'POST' )
						->with_post_body( $options )
						->request();
				} catch ( \Throwable $e ) {
					$output->writeln( "<error>{$e->getMessage()}</error>" );

					return Command::FAILURE;
				}

				$response = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );

				/****************************************************************
				 * 9.  --wait support (delegates to get command)
				 */
				if ( $input->getOption( 'wait' ) ) {
					return $this->wait_for_completion( $input, $output, $response );
				}

				/****************************************************************
				 * 10.  Standard non‑wait output
				 */
				if ( $input->getOption( 'json' ) ) {
					$output->write( $json );

					return Command::SUCCESS;
				}

				$this->render_start_table( $output, $response );

				return Command::SUCCESS;
			}

			/**
			 * Helpers
			 */

			/** Slug or numeric Woo ID -> numeric Woo ID */
			private function slug_or_id_to_id( string $slug_or_id ): int {
				if ( ctype_digit( $slug_or_id ) ) {
					return (int) $slug_or_id;
				}

				return $this->woo_extensions_list->get_woo_extension_id_by_slug( $slug_or_id );
			}

			/**
			 * Handle --zip upload logic & mutate $options in‑place.
			 *
			 * @param InputInterface      $input          The input interface.
			 * @param OutputInterface     $output         The output interface.
			 * @param string              $sut_slug_or_id The SUT slug or ID.
			 * @param array<string,mixed> $options        The options array.
			 */
			private function process_zip_option(
				InputInterface $input,
				OutputInterface $output,
				string $sut_slug_or_id,
				array &$options
			): void {
				if ( ! $input->hasOption( 'zip' ) || ! is_option_explicitly_provided( $input, 'zip' ) ) {
					$options['event'] = 'cli_published_extension_test';

					return;
				}

				$zip_opt        = $input->getOption( 'zip' );
				$zip_flag_alone = $input->getParameterOption( '--zip', 'NOT_SET', true ) === null;

				$zip_path = $zip_flag_alone
					? $sut_slug_or_id . '.zip'
					: (string) $zip_opt;

				if ( $zip_flag_alone && ! file_exists( $zip_path ) ) {
					$output->writeln( "<error>The ZIP file '{$zip_path}' does not exist.</error>" );
					throw new \RuntimeException( 'ZIP not found', Command::FAILURE );
				}

				$upload_id            = $this->upload->upload_build( 'build', $options['woo_id'], $zip_path, $output );
				$options['upload_id'] = $upload_id;
				$options['event']     = 'cli_development_extension_test';
			}

			/** Convert comma‑/array list of slugs/IDs to comma‑separated IDs */
			private function map_multiple_slugs_to_ids( $slugs_or_ids ): string {
				$items = \is_array( $slugs_or_ids ) ? $slugs_or_ids : explode( ',', (string) $slugs_or_ids );
				$ids   = array_map( function ( string $item ): int {
					$item = trim( $item );

					return ctype_digit( $item )
						? (int) $item
						: $this->woo_extensions_list->get_woo_extension_id_by_slug( $item );
				}, $items );

				return implode( ',', $ids );
			}

			/** Wait‑loop logic (moved verbatim, minor refactor) */
			private function wait_for_completion(
				InputInterface $input,
				OutputInterface $output,
				array $response
			): int {
				$test_run_id = $response['test_run_id'] ?? 0;
				if ( ! $test_run_id ) {
					$output->writeln( '<error>Unexpected Manager response – test_run_id missing.</error>' );
					return Command::FAILURE;
				}

				$timeout = $input->getOption( 'timeout' ) ?? ( $this->test_type === 'woo-e2e' ? 7200 : 1800 );
				$timeout = max( 10, min( 7200, (int) $timeout ) );

				$start = time();
				$get   = $this->getApplication()->find( GetCommand::getDefaultName() );

				do {
					sleep( (int) rand( 5, 15 ) );

					$finished = $get->run(
						new ArrayInput( [
							'test_run_id'      => $test_run_id,
							'--check_finished' => true,
						] ),
						$output
					);

					if ( $finished === 0 ) {
						break;
					}

					if ( time() - $start > $timeout ) {
						$output->writeln( '<comment>Timed out waiting for remote test.</comment>' );
						return 124;
					}
				} while ( true );

				$output->writeln( '<info>Test run completed.</info>' );

				$exit = $get->run(
					new ArrayInput( [
						'test_run_id' => $test_run_id,
						'--json'      => $input->getOption( 'json' ),
					] ),
					$output
				);

				return $input->getOption( 'ignore-fail' ) ? Command::SUCCESS : $exit;
			}

			/** Pretty table for non‑wait scenario */
			private function render_start_table( OutputInterface $output, array $response ): void {
				$table = ( new Table( $output ) )
					->setHorizontal()
					->setStyle( 'compact' )
					->setHeaders( [ 'Test Run ID', 'Result URL' ] )
					->addRow( [
						$response['test_run_id'] ?? '–',
						$response['test_results_manager_url'] ?? '–',
					] );
				$output->writeln( '<info>Test started on QIT servers!</info>' );
				$table->render();
				$output->writeln( '' );

				$bin = basename( $_SERVER['argv'][0] ?? 'qit' );
				$output->writeln( sprintf(
					'<info>You can monitor the run with "%s %s %d" or add "--wait".</info>',
					$bin,
					GetCommand::getDefaultName(),
					$response['test_run_id'] ?? 0
				) );
			}
		};

		/**
		 * CLI definition helpers (schema‑driven)
		 */
		self::add_schema_to_command( $command, $schema );

		/* Standard non‑schema arguments / flags */
		$command
			->addArgument( 'sut', InputArgument::OPTIONAL, 'Extension slug or WooCommerce.com ID' )
			->addOption( 'zip', null, InputOption::VALUE_OPTIONAL, '(Optional) Local ZIP / dir / URL build to test' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, '(Optional) Output raw JSON response', false )
			->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Optional) Wait until the test finishes', false )
			->addOption( 'timeout', 't', InputOption::VALUE_OPTIONAL, '(Optional) Wait timeout in seconds', null )
			->addOption( 'ignore-fail', 'i', InputOption::VALUE_NEGATABLE, '(Optional) Exit 0 even if test fails', false )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the run into a group', false );

		// Ensure zip gets forwarded
		$command->add_option_to_send( 'zip' );

		/* Hide old “e2e” alias if Manager says so */
		if ( $test_type === 'e2e' ) {
			$hide = $this->cache->get_manager_sync_data( 'hide_e2e' ) ?? true;
			if ( ! $hide ) {
				$application->add( App::make( RunE2ECommand::class ) );
			}
		}

		$application->add( $command );
	}
}
