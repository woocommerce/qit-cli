<?php

namespace QIT_CLI\Utils;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunE2ECommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\IO\Output;
use QIT_CLI\Environment\PackageOrchestrator;
use QIT_CLI\E2E\Result\TestResult;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\Performance\MetricsExtractor;
use QIT_CLI\Performance\Result\PerformanceTestResult;
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

	/** @var \QIT_CLI\Environment\CTRFValidator */
	protected $ctrf_validator;

	/** @var MetricsExtractor */
	protected $metrics_extractor;

	public function __construct(
		Zipper $zipper,
		OutputInterface $output,
		Upload $uploader,
		PrepareDebugLog $prepare_debug_log,
		PrepareQMLog $prepare_qm_log,
		\QIT_CLI\Environment\CTRFValidator $ctrf_validator,
		?MetricsExtractor $metrics_extractor = null
	) {
		$this->zipper            = $zipper;
		$this->output            = $output;
		$this->uploader          = $uploader;
		$this->prepare_debug_log = $prepare_debug_log;
		$this->prepare_qm_log    = $prepare_qm_log;
		$this->ctrf_validator    = $ctrf_validator;
		$this->metrics_extractor = $metrics_extractor ?: new MetricsExtractor();
	}

	/**
	 * @suppress PhanTypeArraySuspicious
	 *
	 * @param int                           $woo_extension_id
	 * @param string                        $woocommerce_version
	 * @param E2EEnvInfo|PerformanceEnvInfo $env_info
	 * @param bool                          $is_development
	 * @param bool                          $notify
	 * @param string                        $test_type The test type (e2e, activation, performance).
	 */
	public function notify_test_started( int $woo_extension_id, string $woocommerce_version, $env_info, bool $is_development, bool $notify, string $test_type = 'e2e' ): void {
		App::setVar( 'NOTIFY_TEST_STARTED_RAN', true );

		$additional_plugins = [];

		// Check if we're running a performance test (legacy check for backward compatibility).
		if ( getenv( 'QIT_ENVIRONMENT_TYPE' ) === 'performance' ) {
			$test_type = 'performance';
		}

		foreach ( $env_info->plugins as $plugin ) {
			if ( $plugin->type === 'plugin' && isset( $env_info->sut ) && $plugin->slug !== $env_info->sut['slug'] ) {
				$additional_plugins[] = $plugin->slug;
			}
		}

		$event       = getenv( 'CI' ) ? 'ci_run' : 'local_run';
		$sut_version = $env_info->sut['version'] ?? '';

		$body = [
			'woo_id'                  => $woo_extension_id,
			'woocommerce_version'     => $woocommerce_version,
			'wordpress_version'       => $env_info->wordpress_version,
			'php_version'             => $env_info->php_version,
			'additional_plugins'      => $additional_plugins,
			'will_have_allure_report' => 'true', // Always true now, Allure uploads only on failure
			'test_type'               => $test_type,
			'event'                   => $event,
			'is_development_build'    => $is_development ? 'true' : 'false',
			'send_notification'       => $notify ? 'true' : 'false',
			'sut_version'             => $sut_version,
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
	 * @param TestResult|PerformanceTestResult $test_result
	 * @param PackageOrchestrator|null         $orchestrator Optional orchestrator for progress display.
	 *
	 * @return array{string, int|null} The first element is the report URL, the second is the exit status code override, if any.
	 */
	public function notify_test_finished( $test_result, $orchestrator = null ): array {
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
					$test_results = $manifest->get_test_results();

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
			$this->prepare_debug_log->prepare_debug_log( $results_dir . '/debug.log', $prepared_debug_log_path, App::make( EnvInfo::class ) );
			$debug_log['debug_log'] = file_get_contents( $prepared_debug_log_path, false, null, 0, 8 * 1024 * 1024 ); // First 8mb of debug.log.
		}

		// Use artifacts directory if available, otherwise fall back to results directory
		if ( isset( $env_info->artifacts_dir ) && ! empty( $env_info->artifacts_dir ) ) {
			$allure_dir = $env_info->artifacts_dir . '/final/allure';
		} else {
			$allure_dir = $results_dir . '/allure';
		}

		// Check if Allure directory exists and we're not skipping
		$has_allure = is_dir( $allure_dir )
			&& ! App::getVar( 'skip_allure_upload' );

		// Determine if tests failed (we'll know after processing CTRF)
		$tests_failed = false;
		if ( $test_result instanceof TestResult ) {
			$tests_failed = $this->ctrf_has_failed( $result_json );
		}

		if ( $has_allure ) {
			if ( ! $tests_failed ) {
				// Tests passed - don't upload Allure to save bandwidth
				if ( $orchestrator ) {
					$orchestrator->post_processing_message( 'Allure report available locally (not uploaded - tests passed)' );
				}
			} else {
				// Tests failed - upload Allure for debugging
				$zip_path = $results_dir . '/allure-raw.zip';
				$this->zipper->zip_directory( $allure_dir, $zip_path );

				if ( filesize( $zip_path ) > 200 * 1024 * 1024 ) {
					if ( $orchestrator ) {
						$orchestrator->post_processing_message( 'Allure results too large to upload', false );
					} else {
						$this->output->writeln( '<error>Allure raw results are too large to upload. Skipping...</error>' );
					}
				} else {
					if ( $orchestrator ) {
						$orchestrator->post_processing_message( 'Uploading Allure report (tests failed)...' );
					}
					$this->uploader->upload_build(
						'test-report',
						$test_run_id,
						$zip_path,
						$orchestrator ? new \Symfony\Component\Console\Output\NullOutput() : $this->output,
						'e2e'
					);
					if ( $orchestrator ) {
						$orchestrator->post_processing_message( 'Allure report uploaded for debugging' );
					}
				}
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

		// Check for E2E test failures - use CTRF approach
		if ( is_null( $status ) ) {
			// Only check CTRF failures if we have actual test results
			// Empty result_json array means no CTRF file was generated (e.g., utility packages)
			if ( $test_result instanceof TestResult && ! empty( $result_json ) && $this->ctrf_has_failed( $result_json ) ) {
				// We consider it a test failure.
				$exit_status_code_override = Command::FAILURE; // i.e., exit code 1.
				$status                    = 'failed';
			}
		}

		// Check for Performance test failures.
		if ( is_null( $status ) && $test_result instanceof PerformanceTestResult ) {
			$metrics      = $test_result->get_metrics();
			$k6_exit_code = $metrics['k6_exit_code'] ?? null;

			if ( $k6_exit_code !== null && $k6_exit_code !== 0 ) {
				$status = 'failed';
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

		// Compress ctrf_json using same format as test_result_json_original.
		$ctrf_encoded = '';
		if ( ! empty( $result_json ) ) {
			$json_string = json_encode( $result_json );
			if ( $json_string === false ) {
				// json_encode failed - log warning but continue (CTRF will be empty).
				$this->output->writeln( sprintf(
					'<comment>Warning: Failed to encode CTRF JSON: %s</comment>',
					json_last_error_msg()
				) );
			} else {
				$compressed = gzcompress( $json_string );
				if ( $compressed !== false ) {
					$ctrf_encoded = base64_encode( $compressed );
				} else {
					// gzcompress failed, send uncompressed as fallback.
					$ctrf_encoded = $json_string;
				}
			}
		}

		$debug_log_json = json_encode( $debug_log );
		if ( $debug_log_json === false ) {
			// Invalid UTF-8 or other encoding issue.
			$debug_log_json = '';
		}

		$debug_log_compressed = '';
		if ( ! empty( $debug_log_json ) ) {
			$compressed           = gzcompress( $debug_log_json );
			$debug_log_compressed = ( $compressed !== false )
				? base64_encode( $compressed )
				: $debug_log_json; // Fallback to uncompressed.
		}

		$data = [
			'test_run_id'               => $test_run_id,
			'test_result_json'          => '',
			'test_result_json_original' => $test_result_json_original,
			'debug_log'                 => $debug_log_compressed,
			'status'                    => $status,
			'ctrf_json'                 => $ctrf_encoded,
		];

		// Extract performance metrics for performance tests.
		if ( $test_result instanceof PerformanceTestResult ) {
			$performance_results = $this->extract_combined_performance_metrics( $test_result );

			if ( ! empty( $performance_results ) ) {
				$data['cd_performance_results'] = json_encode( $performance_results );
			}
		}

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
	 *
	 * Note: ctrf-cli merge tool may produce null values for integer fields,
	 * which violates the schema but we need to handle gracefully.
	 *
	 * @param array<string, mixed> $ctrf
	 */
	private function ctrf_has_failed( array $ctrf ): bool {
		// Basic structure check
		if ( ! isset( $ctrf['results']['summary'] ) ) {
			// No summary = cannot determine status
			return true;
		}

		// Get failed count - handle both valid (integer) and invalid (null) values
		// that ctrf-cli merge might produce
		$failed = $ctrf['results']['summary']['failed'] ?? 0;

		// Convert to integer - null becomes 0, numeric strings become integers
		$failed_count = (int) $failed;

		return $failed_count > 0;
	}

	/**
	 * Extract and combine performance metrics from both baseline and main test results.
	 *
	 * @param PerformanceTestResult $test_result The main test result.
	 *
	 * @return array<string, mixed> The combined performance metrics.
	 */
	private function extract_combined_performance_metrics( PerformanceTestResult $test_result ): array {
		$performance_results = [
			'has_baseline' => false,
			'extension'    => [],
			'baseline'     => [],
		];

		// Extract main test (extension) metrics from the test result itself.
		$performance_results['extension'] = $this->metrics_extractor->extract_metrics( $test_result->get_metrics() );

		// Add failed checks for extension.
		$performance_results['extension']['failed_checks'] = $this->extract_failed_checks_from_result( $test_result );

		// Check if we have baseline results.
		$baseline_result = $test_result->get_baseline_result();
		if ( $baseline_result !== null ) {
			$performance_results['has_baseline'] = true;
			$performance_results['baseline']     = $this->metrics_extractor->extract_metrics( $baseline_result->get_metrics() );

			// Add failed checks for baseline.
			$performance_results['baseline']['failed_checks'] = $this->extract_failed_checks_from_result( $baseline_result );
		}

		return $performance_results;
	}


	/**
	 * Extract failed checks from a performance test result.
	 *
	 * @param PerformanceTestResult $test_result The test result to extract failed checks from.
	 *
	 * @return array<mixed> Array of failed check details.
	 */
	private function extract_failed_checks_from_result( PerformanceTestResult $test_result ): array {
		$result_file = $test_result->get_results_dir() . '/result.json';

		if ( ! file_exists( $result_file ) ) {
			return [];
		}

		$result_content = file_get_contents( $result_file );
		$result_data    = json_decode( $result_content, true );

		$checks = $result_data['root_group']['checks'] ?? [];
		if ( ! is_array( $checks ) ) {
			return [];
		}

		// Return checks that have failures (k6 format: "fails" > 0).
		return array_filter( $checks, function ( $check ) {
			return ( $check['fails'] ?? 0 ) > 0;
		} );
	}
}
