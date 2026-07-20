<?php
declare( strict_types=1 );

namespace QIT_CLI;

use QIT_CLI\Commands\GetCommand;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Configuration\EnvironmentConfigResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

class RemoteTestRunner {

	private Cache $cache;
	private Upload $upload;
	private WooExtensionsList $woo_extensions_list;
	private TestGroup $test_group;
	private Zipper $zipper;

	public function __construct(
		Cache $cache,
		Upload $upload,
		WooExtensionsList $woo_extensions_list,
		TestGroup $test_group,
		Zipper $zipper
	) {
		$this->cache               = $cache;
		$this->upload              = $upload;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->test_group          = $test_group;
		$this->zipper              = $zipper;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_options_to_send_for_schema( string $test_type ): array {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );
		if ( ! is_array( $schemas ) || ! isset( $schemas[ $test_type ] ) || ! is_array( $schemas[ $test_type ] ) ) {
			throw new \RuntimeException(
				sprintf( 'Could not load Manager schema for test type "%s". Please refresh QIT CLI sync data and try again.', $test_type )
			);
		}

		$properties = $schemas[ $test_type ]['properties'] ?? null;
		if ( ! is_array( $properties ) ) {
			throw new \RuntimeException(
				sprintf( 'Manager schema for test type "%s" is missing option definitions. Please refresh QIT CLI sync data and try again.', $test_type )
			);
		}

		$ignore  = [ 'client', 'event', 'woo_id', 'is_product_update', 'upload_id' ];
		$options = [];

		foreach ( array_keys( $properties ) as $property_name ) {
			if ( in_array( $property_name, $ignore, true ) ) {
				continue;
			}

			$options[ $property_name ] = '';
		}

		$options['zip'] = '';

		return $options;
	}

	/**
	 * Execute a remote Manager-backed test run.
	 *
	 * @param QITCommand          $command           The command being executed.
	 * @param string              $test_type         The Manager test type.
	 * @param array<string,mixed> $options_to_send   Options eligible to send to Manager.
	 * @param QITInput            $input             The command input.
	 * @param OutputInterface     $output            The command output.
	 * @param string|null         $profile_test_type Optional profile test type override.
	 */
	public function execute(
		QITCommand $command,
		string $test_type,
		array $options_to_send,
		QITInput $input,
		OutputInterface $output,
		?string $profile_test_type = null
	): int {
		try {
			$options = $this->build_options( $command, $test_type, $options_to_send, $input, $output, $profile_test_type );
		} catch ( \InvalidArgumentException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::INVALID;
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return $e->getCode() > 0 ? $e->getCode() : Command::FAILURE;
		}

		if ( $input->hasOption( 'group' ) && $input->getOption( 'group' ) ) {
			try {
				$this->test_group->create_or_update( $options, $test_type, $output, null );
				$output->writeln( '<info>Group item successfully added.</info>' );

				return Command::SUCCESS;
			} catch ( \Throwable $e ) {
				$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

				return Command::FAILURE;
			}
		}

		if ( getenv( 'QIT_SELF_TEST' ) === 'remote_test' ) {
			$output->write( json_encode( $options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			return Command::SUCCESS;
		}

		try {
			if ( ! $input->getOption( 'json' ) ) {
				$output->writeln( $input->getOption( 'async' ) ? 'Enqueueing test request...' : 'Starting test on QIT servers...' );
			}

			$json = ( new RequestBuilder( get_manager_url() . "/wp-json/cd/v1/enqueue-{$test_type}" ) )
				->with_method( 'POST' )
				->with_post_body( $options )
				->request();
		} catch ( \Throwable $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}

		$response = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );

		if ( $input->getOption( 'wait' ) ) {
			$output->writeln( '<comment>Warning: The --wait flag is deprecated and will be removed in a future version.</comment>' );
			$output->writeln( '<comment>Tests now wait for completion by default. Use --async to run tests asynchronously.</comment>' );
			$output->writeln( '' );
		}

		if ( $input->getOption( 'async' ) ) {
			if ( $input->getOption( 'json' ) ) {
				GetCommand::decode_json_fields( $response );
				$output->write( json_encode( $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
				return Command::SUCCESS;
			}

			$this->render_start_table( $output, $response, $input->getOption( 'print-report-url' ) );
			return Command::SUCCESS;
		}

		return $this->wait_for_completion( $input, $output, $response, $test_type );
	}

	/**
	 * Build the Manager enqueue payload.
	 *
	 * @param QITCommand          $command           The command being executed.
	 * @param string              $test_type         The Manager test type.
	 * @param array<string,mixed> $options_to_send   Options eligible to send to Manager.
	 * @param QITInput            $input             The command input.
	 * @param OutputInterface     $output            The command output.
	 * @param string|null         $profile_test_type Optional profile test type override.
	 * @return array<string,mixed>
	 */
	private function build_options(
		QITCommand $command,
		string $test_type,
		array $options_to_send,
		QITInput $input,
		OutputInterface $output,
		?string $profile_test_type = null
	): array {
		$profile_name      = $input->get_profile_name();
		$profile_test_type = $profile_test_type ?? $test_type;
		$profile           = $this->get_profile_with_fallback( $command, $profile_test_type, $profile_name );
		$options           = [];

		$environment_name = null;
		$cli_environment  = $input->hasOption( 'environment' ) ? $input->getOption( 'environment' ) : null;
		if ( is_string( $cli_environment ) ) {
			$environment_name = $cli_environment;
		} elseif ( isset( $profile['environment'] ) && is_string( $profile['environment'] ) ) {
			$environment_name = $profile['environment'];
		}

		if ( $environment_name !== null ) {
			$options = EnvironmentConfigResolver::normalize_aliases( $command->get_environment_config( $environment_name ) );
		}

		foreach ( $profile as $key => $value ) {
			if ( $key === 'environment' ) {
				continue;
			}

			$options[ $key ] = $value;
		}

		foreach ( array_keys( $options_to_send ) as $opt_name ) {
			if ( ! $input->hasOption( $opt_name ) ) {
				continue;
			}

			$cli_value = $input->getOption( $opt_name );

			if ( is_array( $cli_value ) && isset( $options[ $opt_name ] ) && is_array( $options[ $opt_name ] ) ) {
				$options[ $opt_name ] = array_values( array_unique( array_merge( $options[ $opt_name ], $cli_value ) ) );
			} else {
				$options[ $opt_name ] = $cli_value;
			}
		}

		$sut_arg = $input->getArgument( 'sut' ) ?: ( $options['sut']['slug'] ?? '' );
		if ( empty( $sut_arg ) ) {
			throw new \InvalidArgumentException( 'No System-Under-Test specified (argument or profile).' );
		}

		$options['woo_id'] = $this->slug_or_id_to_id( (string) $sut_arg );
		$this->process_zip_option( $input, $output, (string) $sut_arg, $options );

		if ( ! empty( $options['additional_woo_plugins'] ) ) {
			$options['additional_woo_plugins'] = $this->map_multiple_slugs_to_ids( $options['additional_woo_plugins'] );
		}

		return $options;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function get_profile_with_fallback( QITCommand $command, string $profile_test_type, string $profile_name ): array {
		if ( $profile_test_type !== 'woo-e2e' ) {
			return $command->get_current_test_profile( $profile_test_type, $profile_name );
		}

		if ( $command->has_test_type_config( 'woo-e2e' ) ) {
			return $command->get_current_test_profile( 'woo-e2e', $profile_name );
		}

		if ( $command->has_test_type_config( 'e2e' ) ) {
			return $command->get_current_test_profile( 'e2e', $profile_name );
		}

		return $command->get_current_test_profile( 'woo-e2e', $profile_name );
	}

	private function slug_or_id_to_id( string $slug_or_id ): int {
		return ctype_digit( $slug_or_id ) ? (int) $slug_or_id : $this->woo_extensions_list->get_woo_extension_id_by_slug( $slug_or_id );
	}

	/**
	 * @param InputInterface      $input          The command input.
	 * @param OutputInterface     $output         The command output.
	 * @param string              $sut_slug_or_id The SUT slug or Woo ID.
	 * @param array<string,mixed> $options        The payload options.
	 */
	private function process_zip_option( InputInterface $input, OutputInterface $output, string $sut_slug_or_id, array &$options ): void {
		if ( ! $input->hasOption( 'zip' ) ) {
			$options['event'] = 'cli_published_extension_test';

			return;
		}

		$zip_opt        = $input->getOption( 'zip' );
		$zip_flag_alone = $input->getParameterOption( '--zip', 'NOT_SET', true ) === null;
		$zip_path       = $zip_flag_alone ? $sut_slug_or_id . '.zip' : (string) $zip_opt;
		$temporary_zip  = null;

		if ( $zip_flag_alone && ! file_exists( $zip_path ) ) {
			throw new \RuntimeException( "The ZIP file '{$zip_path}' does not exist.", Command::FAILURE );
		}

		try {
			if ( is_dir( $zip_path ) ) {
				$temporary_zip = $this->make_temporary_zip_path();
				$this->zipper->zip_directory( $zip_path, $temporary_zip );
				$zip_path = $temporary_zip;
			} elseif ( filter_var( $zip_path, FILTER_VALIDATE_URL ) ) {
				$scheme = strtolower( (string) parse_url( $zip_path, PHP_URL_SCHEME ) );
				if ( ! in_array( $scheme, [ 'http', 'https' ], true ) ) {
					throw new \InvalidArgumentException( 'Remote ZIP URLs must use HTTP or HTTPS.' );
				}

				$temporary_zip = $this->make_temporary_zip_path();
				RequestBuilder::download_file( $zip_path, $temporary_zip );
				$zip_path = $temporary_zip;
			}

			$options['upload_id'] = $this->upload->upload_build( 'build', $options['woo_id'], $zip_path, $output );
			$options['event']     = 'cli_development_extension_test';
		} finally {
			if ( $temporary_zip !== null && file_exists( $temporary_zip ) ) {
				unlink( $temporary_zip );
			}
		}

		unset( $options['zip'] );
	}

	private function make_temporary_zip_path(): string {
		$path = tempnam( sys_get_temp_dir(), 'qit-remote-build-' );
		if ( $path === false ) {
			throw new \RuntimeException( 'Could not create a temporary ZIP path.' );
		}
		unlink( $path );

		return $path;
	}

	/**
	 * @param string|array<string> $slugs_or_ids
	 */
	private function map_multiple_slugs_to_ids( $slugs_or_ids ): string {
		$items = is_array( $slugs_or_ids ) ? $slugs_or_ids : explode( ',', (string) $slugs_or_ids );
		$ids   = array_map( function ( string $item ): int {
			$item = trim( $item );

			return ctype_digit( $item ) ? (int) $item : $this->woo_extensions_list->get_woo_extension_id_by_slug( $item );
		}, $items );

		return implode( ',', $ids );
	}

	/**
	 * @param InputInterface      $input     The command input.
	 * @param OutputInterface     $output    The command output.
	 * @param array<string,mixed> $response  The Manager enqueue response.
	 * @param string              $test_type The Manager test type.
	 */
	private function wait_for_completion( InputInterface $input, OutputInterface $output, array $response, string $test_type ): int {
		$test_run_id = $response['test_run_id'] ?? 0;
		if ( ! $test_run_id ) {
			$output->writeln( '<error>Unexpected Manager response - test_run_id missing.</error>' );
			return Command::FAILURE;
		}

		$default_timeout = 1800;
		if ( $test_type === 'woo-e2e' ) {
			$default_timeout = 7200;
		} elseif ( $test_type === 'api-fuzz' ) {
			$default_timeout = 2700;
		}

		$timeout       = max( 10, min( 7200, (int) ( $input->getOption( 'timeout' ) ?? $default_timeout ) ) );
		$poll_interval = $test_type === 'woo-e2e' ? 30 : 15;
		$start         = time();
		$is_ci         = ! empty( getenv( 'CI' ) );
		$is_json       = $input->getOption( 'json' );

		/** @var ConsoleSectionOutput|null $section */
		$section     = null;
		$last_result = null;
		$last_status = '';
		$completed   = false;
		$ticks       = 0;

		if ( $is_ci || $is_json ) {
			if ( ! $is_json ) {
				$output->writeln( 'Waiting for test completion (Test ID: ' . $test_run_id . ')...' );
			}
		} else {
			$output->writeln( 'Running test on QIT servers...' );
			$output->writeln( '' );
		}

		if ( ! $is_ci && ! $is_json && $output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface ) {
			$section = $output->section();
		}

		if ( ! $is_ci && extension_loaded( 'pcntl' ) ) {
			pcntl_signal( SIGINT, function () use ( $test_run_id, $output ) {
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
			if ( extension_loaded( 'pcntl' ) ) {
				pcntl_signal_dispatch();
			}

			if ( time() - $start >= $timeout ) {
				if ( $section ) {
					$section->clear();
				}
				$output->writeln( '<error>Test execution timed out after ' . $timeout . ' seconds.</error>' );
				return Command::FAILURE;
			}

			if ( $ticks >= $poll_interval || $last_result === null ) {
				try {
					$result_json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
						->with_method( 'POST' )
						->with_post_body( [ 'test_run_id' => $test_run_id ] )
						->request();
					$last_result = json_decode( $result_json, true );
					$ticks       = 0;
				} catch ( \Exception $e ) {
					if ( $last_result === null ) {
						sleep( 1 );
						continue;
					}
				}

				$completed = ( isset( $last_result['update_complete'] ) && $last_result['update_complete'] === true )
					|| ( isset( $last_result['status'] ) && $last_result['status'] === 'cancelled' );
			}

			if ( $last_result !== null ) {
				$last_status = $last_result['status'] ?? 'unknown';
				if ( $section && ! $is_ci && ! $is_json ) {
					$this->render_wait_table( $section, $last_result, $test_run_id, $completed, time() - $start, (bool) $input->getOption( 'print-report-url' ) );
				}
			}

			if ( ! $completed ) {
				sleep( 1 );
				++$ticks;
			}
		}

		$exit_code = Command::SUCCESS;
		if ( in_array( $last_status, [ 'failed', 'cancelled', 'hanged' ], true ) ) {
			$exit_code = Command::FAILURE;
		} elseif ( $last_status === 'warning' ) {
			$exit_code = 3;
		}

		$this->render_completion( $input, $output, $section, $last_result, $last_status, $test_run_id, $start, $is_ci, $is_json );

		return $exit_code;
	}

	/**
	 * @param ConsoleSectionOutput $section     The console section output.
	 * @param array<string,mixed>  $result      The Manager test run result.
	 * @param int                  $test_run_id The Manager test run ID.
	 * @param bool                 $completed   Whether the test has completed.
	 * @param int                  $elapsed     Elapsed seconds.
	 * @param bool                 $print_report_url Whether to include the sensitive report URL.
	 */
	private function render_wait_table( ConsoleSectionOutput $section, array $result, int $test_run_id, bool $completed, int $elapsed, bool $print_report_url ): void {
		$section->clear();

		// Status: animated spinner while running, ✓/✗ glyph when finished.
		$status = $result['status'] ?? 'unknown';
		if ( ! $completed ) {
			$spinner_frames = [ '⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏' ];
			$status         = $spinner_frames[ $elapsed % count( $spinner_frames ) ] . ' ' . $status;
		} elseif ( $status === 'success' ) {
			$status = '✓ ' . $status;
		} elseif ( $status === 'failed' ) {
			$status = '✗ ' . $status;
		}

		$rows = [ [ 'Test Run ID', $result['test_run_id'] ?? $test_run_id ] ];

		if ( isset( $result['test_type_display'] ) ) {
			$rows[] = [ 'Test Type', $result['test_type_display'] ];
		}
		if ( isset( $result['wordpress_version'] ) ) {
			$rows[] = [ 'WordPress Version', $result['wordpress_version'] ];
		}
		if ( isset( $result['woocommerce_version'] ) ) {
			$rows[] = [ 'WooCommerce Version', $result['woocommerce_version'] ];
		}
		if ( isset( $result['min_php_version'] ) && isset( $result['max_php_version'] ) ) {
			$rows[] = [ 'PHP Version Range', $result['min_php_version'] . ' - ' . $result['max_php_version'] ];
		} elseif ( isset( $result['php_version'] ) ) {
			$rows[] = [ 'PHP Version', $result['php_version'] ];
		}

		$rows[] = [ 'Status', $status ];

		$campaign_state = $this->get_campaign_state( $result );
		if ( $campaign_state !== null ) {
			$rows[] = [ 'Campaign State', $campaign_state ];
		}

		if ( isset( $result['woo_extension']['name'] ) ) {
			$rows[] = [ 'Woo Extension', $result['woo_extension']['name'] ];
		}
		if ( isset( $result['version'] ) && ! empty( trim( (string) $result['version'] ) ) ) {
			$rows[] = [ 'Version', $result['version'] ];
		}
		if ( $print_report_url && isset( $result['test_results_manager_url'] ) && ! empty( $result['test_results_manager_url'] ) ) {
			$rows[] = [ 'Result URL', $result['test_results_manager_url'] ];
		}
		if ( isset( $result['test_summary'] ) && ! empty( $result['test_summary'] ) ) {
			$rows[] = [ 'Test Summary', $result['test_summary'] ];
		}

		$table = new Table( $section );
		$table->setStyle( 'box' );
		$table->setHeaders( [ 'Field', 'Value' ] );
		$table->setRows( $rows );
		$table->render();

		if ( $completed ) {
			return;
		}

		$section->writeln( '' );

		$elapsed_info = sprintf( 'Elapsed: %d:%02d', floor( $elapsed / 60 ), $elapsed % 60 );

		// Estimated completion time per test type, when known.
		$estimated_times = [
			'activation'       => 210,
			'malware'          => 90,
			'phpcompatibility' => 90,
			'phpstan'          => 120,
			'plugin-check'     => 90,
			'security'         => 90,
			'validation'       => 90,
			'woo-api'          => 180,
			'woo-e2e'          => 2100,
			'api-fuzz'         => 1200,
		];
		if ( isset( $result['test_type'], $estimated_times[ $result['test_type'] ] ) ) {
			$estimated     = $estimated_times[ $result['test_type'] ];
			$elapsed_info .= sprintf( ' (estimated: ~%d:%02d)', floor( $estimated / 60 ), $estimated % 60 );
		}

		$section->writeln( '<info>' . $elapsed_info . '</info>' );

		$section->writeln( '' );
		if ( extension_loaded( 'pcntl' ) ) {
			$section->writeln( '<comment>Press Ctrl+C to stop waiting (test continues running)</comment>' );
		} else {
			$section->writeln( sprintf( '<comment>Press Ctrl+C to stop waiting. If interrupted, check results with: qit get %d</comment>', $test_run_id ) );
		}
	}

	/**
	 * @param InputInterface           $input        The command input.
	 * @param OutputInterface          $output       The command output.
	 * @param OutputInterface|null     $section      The console section output.
	 * @param array<string,mixed>|null $last_result  The final Manager test run result.
	 * @param string                   $last_status  The final test run status.
	 * @param int                      $test_run_id  The Manager test run ID.
	 * @param int                      $start        Unix timestamp when waiting began.
	 * @param bool                     $is_ci        Whether output is running in CI mode.
	 * @param bool                     $is_json      Whether JSON output was requested.
	 */
	private function render_completion( InputInterface $input, OutputInterface $output, ?OutputInterface $section, ?array $last_result, string $last_status, int $test_run_id, int $start, bool $is_ci, bool $is_json ): void {
		if ( $is_json ) {
			if ( is_array( $last_result ) ) {
				GetCommand::decode_json_fields( $last_result );
			}
			$output->writeln( json_encode( $last_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			return;
		}

		if ( ! $is_ci && $section ) {
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
			return;
		}

		$output->writeln( '<info>Test completed.</info>' );
		$output->writeln( '' );
		$output->writeln( sprintf( '<comment>Test ID:</comment> %d', $test_run_id ) );

		$status_display = $last_status;
		if ( $last_status === 'success' ) {
			$status_display = '<info>' . $last_status . '</info>';
		} elseif ( $last_status === 'failed' ) {
			$status_display = '<error>' . $last_status . '</error>';
		} elseif ( $last_status === 'warning' ) {
			$status_display = '<comment>' . $last_status . '</comment>';
		}
		$output->writeln( '<comment>Status:</comment> ' . $status_display );

		if ( $last_result ) {
			$campaign_state = $this->get_campaign_state( $last_result );
			if ( $campaign_state !== null ) {
				$output->writeln( '<comment>Campaign State:</comment> ' . $campaign_state );
			}
		}

		if ( $last_result && isset( $last_result['test_summary'] ) && ! empty( $last_result['test_summary'] ) ) {
			$output->writeln( '<comment>Summary:</comment> ' . $last_result['test_summary'] );
		}

		if ( $input->getOption( 'print-report-url' ) && $last_result && isset( $last_result['test_results_manager_url'] ) ) {
			$output->writeln( '<comment>Report URL:</comment> ' . $last_result['test_results_manager_url'] );
		} elseif ( ! $input->getOption( 'print-report-url' ) ) {
			$output->writeln( '' );
			$output->writeln( sprintf( '<comment>View full results: qit get %d</comment>', $test_run_id ) );
			$output->writeln( '<comment>Note: Add --print-report-url next time to include the report URL in output</comment>' );
		}
	}

	/**
	 * @param OutputInterface     $output           The command output.
	 * @param array<string,mixed> $response         The Manager enqueue response.
	 * @param bool                $print_report_url Whether to print the report URL.
	 */
	private function render_start_table( OutputInterface $output, array $response, bool $print_report_url = false ): void {
		$output->writeln( '<info>✓ Test enqueued successfully</info>' );
		$output->writeln( '' );

		$test_run_id = $response['test_run_id'] ?? '–';
		$output->writeln( '<comment>Test ID:</comment> ' . $test_run_id );
		$output->writeln( '<comment>Status:</comment> ' . ( $response['status'] ?? 'pending' ) );

		if ( $print_report_url && isset( $response['test_results_manager_url'] ) ) {
			$output->writeln( '<comment>Result URL:</comment> ' . $response['test_results_manager_url'] );
		}

		$bin = basename( $_SERVER['argv'][0] ?? 'qit' );
		$output->writeln( '<comment>Check status:</comment> ' . sprintf( '%s %s %s', $bin, GetCommand::getDefaultName(), $test_run_id ) );

		if ( ! $print_report_url ) {
			$output->writeln( '<comment>Note:</comment> Report URL available with --print-report-url (use cautiously in public logs)' );
		}
	}

	/**
	 * Read the API-fuzz campaign state from the existing normalized result payload.
	 *
	 * @param array<string,mixed> $test_run Manager test run data.
	 */
	private function get_campaign_state( array $test_run ): ?string {
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
