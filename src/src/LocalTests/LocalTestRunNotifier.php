<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Upload;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class LocalTestRunNotifier {
	/** @var Zipper */
	protected $zipper;

	/** @var OutputInterface */
	protected $output;

	/** @var Upload */
	protected $uploader;

	/** @var PrepareDebugLog */
	protected $prepare_debug_log;

	/** @var PrepareQMLog */
	protected $prepare_qm_log;

	public function __construct(
		Zipper $zipper,
		OutputInterface $output,
		Upload $uploader,
		PrepareDebugLog $prepare_debug_log,
		PrepareQMLog $prepare_qm_log
	) {
		$this->zipper            = $zipper;
		$this->output            = $output;
		$this->uploader          = $uploader;
		$this->prepare_debug_log = $prepare_debug_log;
		$this->prepare_qm_log    = $prepare_qm_log;
	}

	/**
	 * @suppress PhanTypeArraySuspicious
	 *
	 * @param int        $woo_extension_id
	 * @param string     $woocommerce_version
	 * @param E2EEnvInfo $env_info
	 * @param bool       $is_development
	 * @param bool       $notify
	 */
	public function notify_test_started( int $woo_extension_id, string $woocommerce_version, E2EEnvInfo $env_info, bool $is_development, bool $notify ): void {
		App::setVar( 'NOTIFY_TEST_STARTED_RAN', true );

		$additional_plugins = [];

		$test_type = 'e2e';

		foreach ( $env_info->plugins as $plugin ) {
			// Handle both Extension objects and arrays (from JSON deserialization)
			if ( is_array( $plugin ) ) {
				// Convert array to Extension object
				$plugin = \QIT_CLI\PreCommand\Objects\Extension::fromArray( $plugin );
			} elseif ( ! $plugin instanceof \QIT_CLI\PreCommand\Objects\Extension ) {
				throw new \TypeError( 'Expected Extension object or array in plugins array, got ' . gettype( $plugin ) );
			}
			
			// Are we running an activation test?
			if ( $plugin->type === 'plugin' && $plugin->slug === 'woocommerce' ) {
				if ( ! empty( $plugin->test_tags ) && is_array( $plugin->test_tags ) ) {
					foreach ( $plugin->test_tags as $t ) {
						if ( $t === 'activation' ) {
							$test_type = 'activation';
						}
					}
				}
			}

			if ( $plugin->type === 'plugin' && isset( $env_info->sut ) && $plugin->slug !== $env_info->sut['slug'] ) {
				$additional_plugins[] = $plugin->slug;
			}
		}

		$event = getenv( 'CI' ) ? 'ci_run' : 'local_run';

		$body = [
			'woo_id'                  => $woo_extension_id,
			'woo'                     => $woocommerce_version,
			'wp'                      => $env_info->wp,
			'php'                     => $env_info->php,
			'additional_plugins'      => $additional_plugins,
			'will_have_allure_report' => App::getVar( 'should_upload_report' ) ? 'true' : 'false',
			'test_type'               => $test_type,
			'event'                   => $event,
			'is_development_build'    => $is_development ? 'true' : 'false',
			'send_notification'       => $notify ? 'true' : 'false',
		];

		/**
		 * If specified, a test run will be updated instead of created.
		 */
		if ( getenv( 'QIT_TEST_RUN_ID' ) ) {
			$body['test_run_id'] = getenv( 'QIT_TEST_RUN_ID' );
		}

		if ( getenv( 'QIT_TEST_GROUP_ID' ) ) {
			$body['group_id'] = getenv( 'QIT_TEST_GROUP_ID' );
		}

		$r = App::make( RequestBuilder::class )
				->with_url( get_manager_url() . '/wp-json/cd/v1/local-test-started' )
				->with_method( 'POST' )
				->with_expected_status_codes( [ 200 ] )
				->with_timeout_in_seconds( 60 )
				->with_post_body( $body )
				->request();

		// Decode response as JSON.
		$response = json_decode( $r, true );

		// Expected "success" true, and "test_run_id" to be set.
		if ( ! is_array( $response ) || empty( $response['test_run_id'] ) ) {
			throw new \UnexpectedValueException( "Couldn't communicate with QIT Manager servers to record test run." );
		}

		if ( App::make( Output::class )->isVerbose() ) {
			App::make( Output::class )->writeln( "Test run created with ID: {$response['test_run_id']}" );
		}

		App::setVar( 'test_run_id', $response['test_run_id'] );
		App::setVar( 'attachment_base_url', $response['allure_report_url'] );
	}

	/**
	 * @param TestResult $test_result
	 *
	 * @return array{string, int|null} The first element is the report URL, the second is the exit status code override, if any.
	 */
	public function notify_test_finished( TestResult $test_result ): array {
		$test_run_id = App::getVar( 'test_run_id' );

		if ( empty( $test_run_id ) ) {
			throw new \RuntimeException( 'Test run ID not set.' );
		}

		$env_info    = $test_result->get_env_info();
		$results_dir = $test_result->get_results_dir();

		// Use artifacts directory if available, otherwise fall back to results directory
		if ( isset( $env_info->artifacts_dir ) && ! empty( $env_info->artifacts_dir ) ) {
			$ctrf_file = $env_info->artifacts_dir . '/final/ctrf/ctrf-report.json';
		} else {
			$ctrf_file = $results_dir . '/final/ctrf/ctrf-report.json';
		}
		$qm_logs_path              = $results_dir . '/logs';
		$test_result_json_original = '';

		// Try to read test_result_json_original from manifest's json property
		$env_info = $test_result->get_env_info();
		if ( ! empty( $env_info->test_packages_metadata ) ) {
			foreach ( $env_info->test_packages_metadata as $pkg_id => $pkg_info ) {
				if ( isset( $pkg_info['manifest'] ) && $pkg_info['manifest'] instanceof \QIT_CLI\PreCommand\Objects\TestPackageManifest ) {
					$manifest     = $pkg_info['manifest'];
					$test_results = $manifest->getTestResults();

					// Check if 'json' property exists in manifest results
					if ( isset( $test_results['json'] ) ) {
						$json_file_path = $pkg_info['path'] . '/' . ltrim( $test_results['json'], './' );

						// Read the JSON file if it exists
						if ( file_exists( $json_file_path ) && is_readable( $json_file_path ) ) {
							$test_result_json_original = file_get_contents( $json_file_path );
							$test_result_json_original = base64_encode( gzcompress( $test_result_json_original ) );
							break; // Use the first found JSON file
						}
					}
				}
			}
		}

		/**
		 * If the logs directory exists, we will send the Query Monitor logs as well.
		 */
		$use_query_monitor_logs = is_dir( $qm_logs_path );
		$debug_log              = [
			'debug_log' => '',
			'qm_logs'   => [
				'non_fatal' => [],
				'fatal'     => [],
			],
		];

		$env_info = $test_result->get_env_info();
		$sut_slug = isset( $env_info->sut ) ? ( $env_info->sut['slug'] ?? '' ) : '';
		$this->prepare_debug_log->set_sut_slug( $sut_slug );
		$this->prepare_qm_log->set_sut_slug( $sut_slug );

		if ( file_exists( $ctrf_file ) ) {
			$result_json = json_decode( file_get_contents( $ctrf_file ), true );

			if ( empty( $result_json ) ) {
				throw new \RuntimeException( 'Result file not a JSON.' );
			}
		} else {
			$result_json = [];
		}

		if ( file_exists( $results_dir . '/debug.log' ) ) {
			$prepared_debug_log_path = $results_dir . '/debug-prepared.log';
			$this->prepare_debug_log->prepare_debug_log( $results_dir . '/debug.log', $prepared_debug_log_path, App::getVar( E2EEnvInfo::class ) );
			$debug_log['debug_log'] = file_get_contents( $prepared_debug_log_path, false, null, 0, 8 * 1024 * 1024 ); // First 8mb of debug.log.
		}

		// Use artifacts directory if available, otherwise fall back to results directory
		if ( isset( $env_info->artifacts_dir ) && ! empty( $env_info->artifacts_dir ) ) {
			$allure_dir = $env_info->artifacts_dir . '/final/allure';
		} else {
			$allure_dir = $results_dir . '/allure';
		}

		if ( is_dir( $allure_dir ) && App::getVar( 'should_upload_report' ) ) {
			$zip_path = $results_dir . '/allure-raw.zip';
			$this->zipper->zip_directory( $allure_dir, $zip_path );

			if ( filesize( $zip_path ) > 200 * 1024 * 1024 ) {
				$this->output->writeln( '<error>Allure raw results are too large to upload. Skipping...</error>' );
			} else {
				$this->uploader->upload_build(
					'test-report',
					$test_run_id,
					$zip_path,
					$this->output,
					'e2e'
				);
			}
		}

		if ( $use_query_monitor_logs ) {
			$this->output->writeln( 'Parsing Query Monitor Logs' );

			$debug_log['qm_logs'] = $this->prepare_qm_log->prepare_qm_logs( $results_dir );
		}

		/**
		 * Allowed status:
		 * - success
		 * - failed
		 * - warning
		 * - cancelled
		 */
		$status                    = null;
		$exit_status_code_override = null;

		if ( $test_result->status === 'cancelled' ) {
			$status = 'cancelled';
		}

		// If it has failed any assertion, it's a failure.
		if ( is_null( $status ) ) {
			if ( $this->ctrf_has_failed( $result_json ) ) {
				// We consider it a test failure.
				$exit_status_code_override = Command::FAILURE; // i.e., exit code 1.
				$status                    = 'failed';
			}
		}

		// If there's anything on debug.log, it's a warning.
		if ( is_null( $status ) ) {
			if ( ! empty( $debug_log['qm_logs']['fatal'] ) ) {
				// We exit with a 1 if it has fatal errors. If Playwright has failed an assertion from a user-perspective, the exit status code is already 1.
				$exit_status_code_override = Command::FAILURE;
				$status                    = 'failed';
			} elseif ( ! empty( $debug_log['qm_logs']['non_fatal'] ) ) {
				// We exit with a 2 if it has non-fatal errors.
				$exit_status_code_override = RunE2ECommand::WARNING;
				$status                    = 'warning';
			}
		}

		// If nothing above matched, it's a success.
		if ( is_null( $status ) ) {
			$status = 'success';
		}

		$data = [
			'test_run_id'               => $test_run_id,
			'test_result_json'          => '',
			'test_result_json_original' => $test_result_json_original,
			'bootstrap_log'             => json_encode( $test_result->bootstrap ),
			'debug_log'                 => json_encode( $debug_log ),
			'status'                    => $status,
			'ctrf_json'                 => $result_json,
		];

		$r = App::make( RequestBuilder::class )
				->with_url( get_manager_url() . '/wp-json/cd/v1/local-test-finished' )
				->with_method( 'POST' )
				->with_expected_status_codes( [ 200 ] )
				->with_timeout_in_seconds( 60 )
				->with_post_body( $data )
				->request();

		// Decode response as JSON.
		$response = json_decode( $r, true );

		// Expected "success" true, and "test_run_id" to be set.
		if ( ! is_array( $response ) || ! ( $response['success'] ) ) {
			throw new \UnexpectedValueException( "Couldn't communicate with QIT Manager servers to record test run." );
		}

		App::make( Cache::class )->set( 'QIT_LAST_LOCAL_TEST_FINISHED', $test_run_id, DAY_IN_SECONDS );

		if ( ! empty( getenv( 'QIT_WRITE_MANAGER_NOTIFIED' ) ) ) {
			if ( ! touch( getenv( 'QIT_WRITE_MANAGER_NOTIFIED' ) ) ) {
				throw new \RuntimeException( 'Could not write to file ' . getenv( 'QIT_WRITE_MANAGER_NOTIFIED' ) );
			}
		}

		return [ $response['report_url'], $exit_status_code_override ];
	}

	/**
	 * Checks if the CTRF report indicates a failing test.
	 * If the schema is missing crucial fields, we treat it as failed to be safe.
	 */
	private function ctrf_has_failed( array $ctrf ): bool {
		// 1. Basic structure check: results & results.summary must exist.
		if (
			! isset( $ctrf['results'] ) ||
			! is_array( $ctrf['results'] ) ||
			! isset( $ctrf['results']['summary'] ) ||
			! is_array( $ctrf['results']['summary'] )
		) {
			// If we can’t verify, we assume failed, to be safe.
			return true;
		}

		// 2. 'failed' must be set and numeric (schema says integer).
		if (
			! isset( $ctrf['results']['summary']['failed'] ) ||
			! is_numeric( $ctrf['results']['summary']['failed'] )
		) {
			// If missing or invalid type, treat as failed.
			return true;
		}

		$failed_count = (int) $ctrf['results']['summary']['failed'];
		// 3. If the number of failed tests is > 0, it’s a fail.
		if ( $failed_count > 0 ) {
			return true;
		}

		// Otherwise, no CTRF-based failures.
		return false;
	}
}
