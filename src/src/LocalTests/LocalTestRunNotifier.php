<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\MetricsExtractor;
use QIT_CLI\LocalTests\Performance\Result\PerformanceTestResult;
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

	/** @var PlaywrightToPuppeteerConverter */
	protected $playwright_to_puppeteer_converter;

	/** @var MetricsExtractor */
	protected $metrics_extractor;

	public function __construct(
		Zipper $zipper,
		OutputInterface $output,
		Upload $uploader,
		PrepareDebugLog $prepare_debug_log,
		PrepareQMLog $prepare_qm_log,
		PlaywrightToPuppeteerConverter $playwright_to_puppeteer_converter,
		?MetricsExtractor $metrics_extractor = null
	) {
		$this->zipper                            = $zipper;
		$this->output                            = $output;
		$this->uploader                          = $uploader;
		$this->prepare_debug_log                 = $prepare_debug_log;
		$this->prepare_qm_log                    = $prepare_qm_log;
		$this->playwright_to_puppeteer_converter = $playwright_to_puppeteer_converter;
		$this->metrics_extractor                 = $metrics_extractor ?: new MetricsExtractor();
	}

	/**
	 * @suppress PhanTypeArraySuspicious
	 *
	 * @param int                           $woo_extension_id
	 * @param string                        $woocommerce_version
	 * @param E2EEnvInfo|PerformanceEnvInfo $env_info
	 * @param bool                          $is_development
	 * @param bool                          $notify
	 */
	public function notify_test_started( int $woo_extension_id, string $woocommerce_version, $env_info, bool $is_development, bool $notify ): void {
		App::setVar( 'NOTIFY_TEST_STARTED_RAN', true );

		$additional_plugins = [];

		$test_type = 'e2e';

		// Check if we're running a performance test.
		if ( getenv( 'QIT_ENVIRONMENT_TYPE' ) === 'performance' ) {
			$test_type = 'performance';
		}

		foreach ( $env_info->plugins as $plugin ) {
			// Are we running an activation test?
			if ( $plugin['type'] === 'plugin' && $plugin['slug'] === 'woocommerce' ) {
				if ( ! empty( $plugin['test_tags'] ) && is_array( $plugin['test_tags'] ) ) {
					foreach ( $plugin['test_tags'] as $t ) {
						if ( $t === 'activation' ) {
							$test_type = 'activation';
						}
					}
				}
			}

			if ( $plugin['type'] === 'plugin' && $plugin['slug'] !== $env_info->sut_slug ) {
				$additional_plugins[] = $plugin['slug'];
			}
		}

		$event = getenv( 'CI' ) ? 'ci_run' : 'local_run';

		$body = [
			'woo_id'                  => $woo_extension_id,
			'woocommerce_version'     => $woocommerce_version,
			'wordpress_version'       => $env_info->wp,
			'php_version'             => $env_info->php_version,
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
	 * @param TestResult|PerformanceTestResult $test_result
	 *
	 * @return array{string, int|null} The first element is the report URL, the second is the exit status code override, if any.
	 */
	public function notify_test_finished( $test_result ): array {
		$test_run_id = App::getVar( 'test_run_id' );

		if ( empty( $test_run_id ) ) {
			throw new \RuntimeException( 'Test run ID not set.' );
		}

		$results_dir = $test_result->get_results_dir();

		$result_file               = $results_dir . '/result.json';
		$ctrf_file                 = $results_dir . '/ctrf/ctrf-report.json';
		$qm_logs_path              = $results_dir . '/logs';
		$test_result_json_original = '';

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

		$this->prepare_debug_log->set_sut_slug( $test_result->get_env_info()->sut_slug ?: '' );
		$this->prepare_qm_log->set_sut_slug( $test_result->get_env_info()->sut_slug ?: '' );

		if ( file_exists( $result_file ) ) {
			$result_json = file_get_contents( $result_file );

			if ( empty( json_decode( $result_json, true ) ) ) {
				throw new \RuntimeException( 'Result file not a JSON.' );
			}

			$test_result_json_original = $result_json;

			// Skip Playwright to Puppeteer conversion for performance tests.
			if ( $test_result instanceof PerformanceTestResult ) {
				$result_json = json_decode( $result_json, true );
			} else {
				$result_json = $this->playwright_to_puppeteer_converter->convert_pw_to_puppeteer( json_decode( $result_json, true ) );
			}
		} else {
			$result_json = [];
		}

		if ( file_exists( $ctrf_file ) ) {
			$ctrf_json = json_decode( file_get_contents( $ctrf_file ), true );

			if ( ! empty( $ctrf_json ) ) {
				$ctrf_json = $ctrf_json;
			}
		} else {
			$ctrf_json = [];
		}

		if ( file_exists( $results_dir . '/debug.log' ) ) {
			$prepared_debug_log_path = $results_dir . '/debug-prepared.log';
			$this->prepare_debug_log->prepare_debug_log( $results_dir . '/debug.log', $prepared_debug_log_path, App::getVar( E2EEnvInfo::class ) );
			$debug_log['debug_log'] = file_get_contents( $prepared_debug_log_path, false, null, 0, 8 * 1024 * 1024 ); // First 8mb of debug.log.
		}

		if ( file_exists( $results_dir . '/allure-playwright' ) && App::getVar( 'should_upload_report' ) ) {
			$this->zipper->zip_directory( $results_dir . '/allure-playwright', $results_dir . '/allure-playwright.zip' );
			if ( filesize( $results_dir . '/allure-playwright.zip' ) > 200 * 1024 * 1024 ) {
				$this->output->writeln( '<error>Report is too large to upload. Skipping...</error>' );
			} else {
				$this->uploader->upload_build( 'test-report', $test_run_id, $results_dir . '/allure-playwright.zip', $this->output, 'e2e' );
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

		// Check for E2E test failures.
		if ( is_null( $status ) && $test_result instanceof TestResult && $this->playwright_to_puppeteer_converter->has_failed( $result_json ) ) {
			$status = 'failed';
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

		if ( function_exists( 'gzcompress' ) && ! empty( $test_result_json_original ) ) {
			$test_result_json_original = base64_encode( gzcompress( $test_result_json_original ) );
		}

		$data = [
			'test_run_id'               => $test_run_id,
			'test_result_json'          => $result_json,
			'test_result_json_original' => $test_result_json_original,
			'bootstrap_log'             => json_encode( $test_result->bootstrap ),
			'debug_log'                 => json_encode( $debug_log ),
			'status'                    => $status,
			'ctrf_json'                 => $ctrf_json,
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
			'comparison'   => [],
		];

		// Extract main test (extension) metrics from the test result itself.
		$performance_results['extension'] = $this->metrics_extractor->extract_metrics( $test_result->get_metrics() );
		
		// Add failed checks for extension
		$performance_results['extension']['failed_checks'] = $this->extract_failed_checks_from_result( $test_result );

		// Check if we have baseline results.
		$baseline_result = $test_result->get_baseline_result();
		if ( $baseline_result !== null ) {
			$performance_results['has_baseline'] = true;
			$performance_results['baseline']     = $this->metrics_extractor->extract_metrics( $baseline_result->get_metrics() );
			$performance_results['comparison']   = $this->extract_comparison_metrics( $test_result );
			
			// Add failed checks for baseline.
			$performance_results['baseline']['failed_checks'] = $this->extract_failed_checks_from_result( $baseline_result );
		}

		return $performance_results;
	}

	/**
	 * Extract comparison metrics from a test result.
	 *
	 * @param PerformanceTestResult $test_result The main test result.
	 *
	 * @return array<string, mixed> The comparison metrics.
	 */
	private function extract_comparison_metrics( PerformanceTestResult $test_result ): array {
		$comparison_metrics = [];
		$all_metrics        = $test_result->get_metrics();

		// Look for comparison metrics (those ending with _vs_baseline_percent or _vs_baseline_diff).
		foreach ( $all_metrics as $metric_name => $metric_value ) {
			if ( str_contains( $metric_name, '_vs_baseline_' ) ) {
				$comparison_metrics[ $metric_name ] = $metric_value;
			}
		}

		return $comparison_metrics;
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
		$result_data = json_decode( $result_content, true );
		
		$checks = $result_data['root_group']['checks'] ?? [];
		if ( ! is_array( $checks ) ) {
			return [];
		}

		// Return checks that have failures (k6 format: "fails" > 0)
		return array_filter( $checks, function( $check ) {
			return ( $check['fails'] ?? 0 ) > 0;
		} );
	}
}
