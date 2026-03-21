<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunE2ECommand;
use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use QIT_CLI\TestGroup;
use QIT_CLI\Upload;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

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
		$ignored_test_types = [ 'activation', 'performance' ]; // Activation handled locally, performance both remotely and locally.
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
			$this->upload,
			$this->woo_extensions_list,
			$this->test_group
		) extends DynamicCommand {

			/** @var Upload */
			private Upload $upload;
			/** @var WooExtensionsList */
			private WooExtensionsList $woo_extensions_list;
			/** @var TestGroup */
			private TestGroup $test_group;

			public function __construct(
				string $test_type,
				Upload $upload,
				WooExtensionsList $woo_extensions_list,
				TestGroup $test_group
			) {
				$this->upload              = $upload;
				$this->woo_extensions_list = $woo_extensions_list;
				$this->test_group          = $test_group;
				parent::__construct( $test_type );
				$this->setName( "run:$test_type" );
				$this->setDescription( "Run $test_type tests on QIT (waits for completion by default)" );
			}

			/**
			 * Main execution
			 *
			 * @param QITInput        $input
			 * @param OutputInterface $output
			 *
			 * @return int
			 */
			public function doExecute( QITInput $input, OutputInterface $output ): int {

				/****************************************************************
				 * 1.  Base configuration comes from qit.json profile
				 */
				$profile_name = $input->get_profile_name();

				$profile = $this->get_current_test_profile( $this->test_type, $profile_name );
				if ( ! \is_array( $profile ) ) {
					$profile = [];
				}

				/****************************************************************
				 * 1b. Resolve environment reference — merge env as base,
				 *     profile inline values override environment values.
				 */
				$options = [];
				if ( isset( $profile['environment'] ) && is_string( $profile['environment'] ) ) {
					$env_config = $this->get_environment_config( $profile['environment'] );
					$env_config = \QIT_CLI\PreCommand\Configuration\EnvironmentConfigResolver::normalize_aliases( $env_config );
					$options    = $env_config;
				}
				// Profile values override environment (except the 'environment' key itself)
				foreach ( $profile as $key => $value ) {
					if ( $key === 'environment' ) {
						continue;
					}
					$options[ $key ] = $value;
				}

				/****************************************************************
				 * 2.  Apply CLI overrides (only what user provided)
				 */

				// Fix: Iterate over array keys, not values (values are empty strings)
				foreach ( array_keys( $this->options_to_send ) as $opt_name ) {

					if ( ! $input->hasOption( $opt_name ) ) {
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
					if ( $input->getOption( 'async' ) ) {
						$output->writeln( 'Enqueueing test request...' );
					} else {
						$output->writeln( 'Starting test on QIT servers...' );
					}

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
				 * 9.  Handle async vs sync execution (QIT 1.0 behavior)
				 */
				// Show deprecation warning if --wait is used
				if ( $input->getOption( 'wait' ) ) {
					$output->writeln( '<comment>Warning: The --wait flag is deprecated and will be removed in a future version.</comment>' );
					$output->writeln( '<comment>Tests now wait for completion by default. Use --async to run tests asynchronously.</comment>' );
					$output->writeln( '' );
				}

				// In QIT 1.0, we wait by default unless --async is specified
				if ( $input->getOption( 'async' ) ) {
					// Async mode: enqueue and return immediately
					if ( $input->getOption( 'json' ) ) {
						$output->write( $json );
						return Command::SUCCESS;
					}

					$this->render_start_table( $output, $response, $input->getOption( 'print-report-url' ) );
					return Command::SUCCESS;
				}

				// Default behavior: wait for completion
				return $this->wait_for_completion( $input, $output, $response );
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
				if ( ! $input->hasOption( 'zip' ) ) {
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

				// Remove the zip option from the array since it's CLI-only and should not be sent to the backend.
				// The zip path may contain '../' which triggers the firewall path traversal detection.
				unset( $options['zip'] );
			}

			/**
			 * Convert comma‑/array list of slugs/IDs to comma‑separated IDs
			 *
			 * @param string|array<string> $slugs_or_ids
			 * @return string
			 */
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

			/**
			 * Wait‑loop logic with interactive table updates
			 *
			 * @param InputInterface      $input
			 * @param OutputInterface     $output
			 * @param array<string,mixed> $response
			 * @return int
			 */
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
				$is_ci = ! empty( getenv( 'CI' ) );

				// Determine poll interval based on test type
				$poll_interval = ( $this->test_type === 'woo-e2e' ) ? 30 : 15;

				// CI Mode: Simple waiting message
				if ( $is_ci ) {
					$output->writeln( 'Waiting for test completion (Test ID: ' . $test_run_id . ')...' );
				} else {
					// Interactive Mode: Will use section for redrawable output
					$output->writeln( 'Running test on QIT servers...' );
					$output->writeln( '' );
				}

				// Create a section for interactive updates (only works on ConsoleOutput)
				$section = null;
				if ( ! $is_ci && $output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface ) {
					$section = $output->section();
				}

				$completed          = false;
				$last_status        = '';
				$last_result        = null;
				$spinner_frames     = [ '⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏' ];
				$spinner_index      = 0;
				$last_poll_time     = 0;
				$seconds_since_poll = 0;

				// Estimated completion times for test types (in seconds)
				$estimated_times = [
					'activation'       => 210,  // 3.5 minutes
					'malware'          => 90,   // 1.5 minutes
					// 'performance'      => 300,  // 5 minutes.
					'phpcompatibility' => 90,   // 1.5 minutes
					'phpstan'          => 120,  // 2 minutes
					'plugin-check'     => 90,  // 1.5 minutes
					'security'         => 90,   // 1.5 minutes
					'validation'       => 90,   // 1.5 minute
					'woo-api'          => 180,  // 3 minutes
					'woo-e2e'          => 2100, // 35 minutes
				];

				// Set up Ctrl+C handler if pcntl is available
				$interrupted = false;
				$has_pcntl   = ! $is_ci && extension_loaded( 'pcntl' );

				if ( $has_pcntl ) {
					pcntl_signal( SIGINT, function () use ( &$interrupted, $test_run_id, $output ) {
						$interrupted = true;
						// Don't clear the section - keep the table visible
						$output->writeln( '' );
						$output->writeln( '' );
						$output->writeln( '<comment>───────────────────────────────────────</comment>' );
						$output->writeln( '<info>Stopped waiting. Test continues running on QIT servers.</info>' );
						$output->writeln( sprintf( '<info>Test ID: %d</info>', $test_run_id ) );
						$output->writeln( sprintf( '<info>Check results: qit get %d</info>', $test_run_id ) );
						$output->writeln( '<comment>───────────────────────────────────────</comment>' );
						exit( 0 );
					} );
				}

				while ( ! $completed ) {
					// Check for signals if pcntl is available
					if ( extension_loaded( 'pcntl' ) ) {
						pcntl_signal_dispatch();
					}

					$elapsed = time() - $start;

					// Check timeout
					if ( $elapsed >= $timeout ) {
						if ( $section ) {
							$section->clear();
						}
						$output->writeln( '<error>Test execution timed out after ' . $timeout . ' seconds.</error>' );
						return Command::FAILURE;
					}

					// Poll for new status if it's time
					if ( $seconds_since_poll >= $poll_interval || $last_result === null ) {
						try {
							$result_json        = ( new \QIT_CLI\RequestBuilder( \QIT_CLI\get_manager_url() . '/wp-json/cd/v1/get-single' ) )
								->with_method( 'POST' )
								->with_post_body( [ 'test_run_id' => $test_run_id ] )
								->request();
							$last_result        = json_decode( $result_json, true );
							$last_poll_time     = time();
							$seconds_since_poll = 0;
						} catch ( \Exception $e ) {
							// If we can't get status, wait and try again
							if ( $last_result === null ) {
								sleep( 1 );
								continue;
							}
							// Otherwise use the last known result
						}

						// Check if test is complete
						if ( isset( $last_result['update_complete'] ) && $last_result['update_complete'] === true ) {
							$completed = true;
						}
					}

					// Update display based on mode
					if ( ! $is_ci && $section && $last_result !== null ) {
						// Interactive mode: Redraw table every second
						$section->clear();

						// Format elapsed time
						$elapsed_min = floor( $elapsed / 60 );
						$elapsed_sec = $elapsed % 60;
						$elapsed_str = sprintf( '%d:%02d', $elapsed_min, $elapsed_sec );

						// Get current status with spinner if still running
						$status = $last_result['status'] ?? 'unknown';
						if ( ! $completed ) {
							$status = $spinner_frames[ $spinner_index ] . ' ' . $status;
							++$spinner_index;
							$spinner_index = $spinner_index % count( $spinner_frames );
						} else {
							// Add checkmark for completed statuses
							if ( $status === 'success' ) {
								$status = '✓ ' . $status;
							} elseif ( $status === 'failed' ) {
								$status = '✗ ' . $status;
							}
						}

						// Build table data
						$table_data   = [];
						$table_data[] = [ 'Test Run ID', $last_result['test_run_id'] ?? $test_run_id ];

						if ( isset( $last_result['test_type_display'] ) ) {
							$table_data[] = [ 'Test Type', $last_result['test_type_display'] ];
						}
						if ( isset( $last_result['wordpress_version'] ) ) {
							$table_data[] = [ 'WordPress Version', $last_result['wordpress_version'] ];
						}
						if ( isset( $last_result['woocommerce_version'] ) ) {
							$table_data[] = [ 'WooCommerce Version', $last_result['woocommerce_version'] ];
						}
						if ( isset( $last_result['min_php_version'] ) && isset( $last_result['max_php_version'] ) ) {
							$table_data[] = [ 'PHP Version Range', $last_result['min_php_version'] . ' - ' . $last_result['max_php_version'] ];
						} elseif ( isset( $last_result['php_version'] ) ) {
							$table_data[] = [ 'PHP Version', $last_result['php_version'] ];
						}

						$table_data[] = [ 'Status', $status ];

						if ( isset( $last_result['woo_extension']['name'] ) ) {
							$table_data[] = [ 'Woo Extension', $last_result['woo_extension']['name'] ];
						}
						if ( isset( $last_result['version'] ) && ! empty( trim( $last_result['version'] ) ) ) {
							$table_data[] = [ 'Version', $last_result['version'] ];
						}
						if ( isset( $last_result['test_results_manager_url'] ) && ! empty( $last_result['test_results_manager_url'] ) ) {
							$table_data[] = [ 'Result URL', $last_result['test_results_manager_url'] ];
						}
						if ( isset( $last_result['test_summary'] ) && ! empty( $last_result['test_summary'] ) ) {
							$table_data[] = [ 'Test Summary', $last_result['test_summary'] ];
						}

						// Render table
						$table = new Table( $section );
						$table->setStyle( 'box' );
						$table->setHeaders( [ 'Field', 'Value' ] );
						$table->setRows( $table_data );
						$table->render();

						// Show elapsed time and estimated completion outside table
						if ( ! $completed ) {
							$section->writeln( '' );

							// Show elapsed time
							$elapsed_info = sprintf( 'Elapsed: %s', $elapsed_str );

							// Add estimated completion if available
							if ( isset( $last_result['test_type'] ) && isset( $estimated_times[ $last_result['test_type'] ] ) ) {
								$estimated_seconds = $estimated_times[ $last_result['test_type'] ];
								$estimated_minutes = floor( $estimated_seconds / 60 );
								$estimated_seconds = $estimated_seconds % 60;
								$estimated_str     = $estimated_minutes > 0
									? sprintf( '%d:%02d', $estimated_minutes, $estimated_seconds )
									: sprintf( '0:%02d', $estimated_seconds );
								$elapsed_info     .= sprintf( ' (estimated: ~%s)', $estimated_str );
							}

							$section->writeln( '<info>' . $elapsed_info . '</info>' );

							// Show abort message
							$section->writeln( '' );
							if ( $has_pcntl ) {
								$section->writeln( '<comment>Press Ctrl+C to stop waiting (test continues running)</comment>' );
							} else {
								// Without pcntl, we can't show a clean message on Ctrl+C, so be more explicit
								$section->writeln( sprintf( '<comment>Press Ctrl+C to stop waiting. If interrupted, check results with: qit get %d</comment>', $test_run_id ) );
							}
						}
					}

					// Store last status
					if ( $last_result !== null ) {
						$last_status = $last_result['status'] ?? 'unknown';
					}

					if ( ! $completed ) {
						sleep( 1 ); // Update every second
						++$seconds_since_poll;
					}
				}

				// Determine exit code based on status
				$exit_code = Command::SUCCESS;
				if ( $last_status === 'failed' ) {
					$exit_code = Command::FAILURE;
				} elseif ( $last_status === 'warning' ) {
					$exit_code = 3; // Exit code 3 for warnings
				}

				// Show completion message
				if ( ! $is_ci && $section ) {
					// Interactive mode - we already showed the full table, just show completion message
					// Calculate total time
					$total_elapsed = time() - $start;
					$total_min     = floor( $total_elapsed / 60 );
					$total_sec     = $total_elapsed % 60;

					$output->writeln( '' );
					if ( $last_status === 'success' ) {
						$output->writeln( sprintf( '<info>✅ Test completed successfully in %dm %ds</info>', $total_min, $total_sec ) );
					} elseif ( $last_status === 'failed' ) {
						$output->writeln( sprintf( '<error>❌ Test failed after %dm %ds</error>', $total_min, $total_sec ) );
					} else {
						$output->writeln( sprintf( '<comment>Test completed in %dm %ds</comment>', $total_min, $total_sec ) );
					}
				} else {
					// CI mode - show minimal output
					if ( ! $input->getOption( 'json' ) ) {
						$output->writeln( '<info>Test completed.</info>' );
						$output->writeln( '' );
						$output->writeln( sprintf( '<comment>Test ID:</comment> %d', $test_run_id ) );

						// Show status
						$status_display = $last_status;
						if ( $last_status === 'success' ) {
							$status_display = '<info>' . $last_status . '</info>';
						} elseif ( $last_status === 'failed' ) {
							$status_display = '<error>' . $last_status . '</error>';
						} elseif ( $last_status === 'warning' ) {
							$status_display = '<comment>' . $last_status . '</comment>';
						}
						$output->writeln( '<comment>Status:</comment> ' . $status_display );

						// Show test summary if available
						if ( $last_result && isset( $last_result['test_summary'] ) && ! empty( $last_result['test_summary'] ) ) {
							$output->writeln( '<comment>Summary:</comment> ' . $last_result['test_summary'] );
						}

						// Only show URL if explicitly requested
						if ( $input->getOption( 'print-report-url' ) ) {
							if ( $last_result && isset( $last_result['test_results_manager_url'] ) ) {
								$output->writeln( '<comment>Report URL:</comment> ' . $last_result['test_results_manager_url'] );
							}
						} else {
							$output->writeln( '' );
							$output->writeln( sprintf( '<comment>View full results: qit get %d</comment>', $test_run_id ) );
							$output->writeln( '<comment>Note: Add --print-report-url next time to include the report URL in output</comment>' );
						}
					} else {
						// JSON mode in CI - output the last result as JSON
						$output->writeln( json_encode( $last_result ) );
					}
				}

				return $exit_code;
			}

			/**
			 * Pretty table for async mode
			 *
			 * @param OutputInterface     $output
			 * @param array<string,mixed> $response
			 * @param bool                $print_report_url Whether to print the report URL.
			 * @return void
			 */
			private function render_start_table( OutputInterface $output, array $response, bool $print_report_url = false ): void {
				$output->writeln( '<info>✓ Test enqueued successfully</info>' );
				$output->writeln( '' );

				$test_run_id = $response['test_run_id'] ?? '–';

				$output->writeln( '<comment>Test ID:</comment> ' . $test_run_id );

				// Only show Result URL if explicitly requested (for security in public logs)
				if ( $print_report_url && isset( $response['test_results_manager_url'] ) ) {
					$output->writeln( '<comment>Result URL:</comment> ' . $response['test_results_manager_url'] );
				}

				$bin = basename( $_SERVER['argv'][0] ?? 'qit' );
				$output->writeln( '<comment>Check status:</comment> ' . sprintf( '%s %s %s', $bin, GetCommand::getDefaultName(), $test_run_id ) );

				// Don't show URL hint in async mode for security reasons
				if ( ! $print_report_url ) {
					$output->writeln( '<comment>Note:</comment> Report URL available with --print-report-url (use cautiously in public logs)' );
				}
			}
		};

		/**
		 * CLI definition helpers (schema‑driven)
		 * Note: add_schema_to_command appends alias info (--wp, --woo, --php) to option descriptions
		 */
		self::add_schema_to_command( $command, $schema );

		/* Standard non‑schema arguments / flags */
		$command
			->addArgument( 'sut', InputArgument::OPTIONAL, 'Extension slug or WooCommerce.com ID' )
			->addOption( 'zip', null, InputOption::VALUE_OPTIONAL, '(Optional) Local ZIP / dir / URL build to test' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, '(Optional) Output raw JSON response', false )
			->addOption( 'async', null, InputOption::VALUE_NEGATABLE, '(Optional) Enqueue test and return immediately without waiting', false )
			->addOption( 'wait', 'w', InputOption::VALUE_NEGATABLE, '(Deprecated) Wait for test completion - this is now the default behavior', false )
			->addOption( 'print-report-url', null, InputOption::VALUE_NEGATABLE, '(Optional) Print the test report URL (contains sensitive data - use cautiously in public logs)', false )
			->addOption( 'timeout', 't', InputOption::VALUE_OPTIONAL, '(Optional) Wait timeout in seconds', null )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the run into a group', false );

		// Ensure zip gets forwarded
		$command->add_option_to_send( 'zip' );

		/* Hide old "e2e" alias if Manager says so */
		if ( $test_type === 'e2e' ) {
			$hide = $this->cache->get_manager_sync_data( 'hide_e2e' );
			if ( ! $hide ) {
				$application->add( App::make( RunE2ECommand::class ) );
			}
		} else {
			$application->add( $command );
		}
	}
}
