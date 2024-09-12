<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Upload;
use QIT_CLI\Zipper;
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

	public function __construct(
		Zipper $zipper,
		OutputInterface $output,
		Upload $uploader,
		PrepareDebugLog $prepare_debug_log,
		PrepareQMLog $prepare_qm_log,
		PlaywrightToPuppeteerConverter $playwright_to_puppeteer_converter
	) {
		$this->zipper                            = $zipper;
		$this->output                            = $output;
		$this->uploader                          = $uploader;
		$this->prepare_debug_log                 = $prepare_debug_log;
		$this->prepare_qm_log                    = $prepare_qm_log;
		$this->playwright_to_puppeteer_converter = $playwright_to_puppeteer_converter;
	}

	/**
	 * @suppress PhanTypeArraySuspicious
	 */
	public function notify_test_started( string $woo_extension_id, string $woocommerce_version, E2EEnvInfo $env_info, bool $is_development, bool $notify ): void {
		App::setVar( 'NOTIFY_TEST_STARTED_RAN', true );

		$additional_plugins = [];

		foreach ( $env_info->plugins as $plugin ) {
			if ( $plugin['type'] === 'plugin' && $plugin['slug'] !== $env_info->sut_slug ) {
				$additional_plugins[] = $plugin['slug'];
			}
		}

		$body = [
			'woo_id'                  => $woo_extension_id,
			'woocommerce_version'     => $woocommerce_version,
			'wordpress_version'       => $env_info->wp,
			'php_version'             => $env_info->php_version,
			'additional_plugins'      => $additional_plugins,
			'will_have_allure_report' => App::getVar( 'should_upload_report' ) ? 'true' : 'false',
			'test_type'               => 'e2e',
			'event'                   => 'e2e_local_run',
			'is_development_build'    => $is_development ? 'true' : 'false',
			'send_notification'       => $notify ? 'true' : 'false',
		];

		/**
		 * If specified, a test run will be updated instead of created.
		 */
		if ( getenv( 'QIT_TEST_RUN_ID' ) ) {
			$body['test_run_id'] = getenv( 'QIT_TEST_RUN_ID' );
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
	 * @return string The Report URL in QIT.
	 */
	public function notify_test_finished( TestResult $test_result ): string {
		$test_run_id = App::getVar( 'test_run_id' );

		if ( empty( $test_run_id ) ) {
			throw new \RuntimeException( 'Test run ID not set.' );
		}

		$results_dir = $test_result->get_results_dir();

		$result_file  = $results_dir . '/result.json';
		$qm_logs_path = $results_dir . '/logs';

		/**
		 * If the logs directory exists, we will send the Query Monitor logs as well.
		 */
		$use_query_monitor_logs = is_dir( $qm_logs_path );
		$debug_log              = '';

		if ( file_exists( $result_file ) ) {
			$result_json = file_get_contents( $result_file );

			if ( empty( json_decode( $result_json, true ) ) ) {
				throw new \RuntimeException( 'Result file not a JSON.' );
			}

			$result_json = $this->playwright_to_puppeteer_converter->convert_pw_to_puppeteer( json_decode( $result_json, true ) );
		} else {
			$result_json = [];
		}

		if ( file_exists( $results_dir . '/debug.log' ) ) {
			$prepared_debug_log_path = $results_dir . '/debug-prepared.log';
			$this->prepare_debug_log->prepare_debug_log( $results_dir . '/debug.log', $prepared_debug_log_path, App::getVar( E2EEnvInfo::class ) );
			$debug_log = file_get_contents( $prepared_debug_log_path, false, null, 0, 8 * 1024 * 1024 ); // First 8mb of debug.log.
		}

		if ( file_exists( $results_dir . '/allure-playwright' ) && App::getVar( 'should_upload_report' ) ) {
			$this->zipper->zip_directory( $results_dir . '/allure-playwright', $results_dir . '/allure-playwright.zip' );
			if ( filesize( $results_dir . '/allure-playwright.zip' ) > 200 * 1024 * 1024 ) {
				$this->output->writeln( '<error>Report is too large to upload. Skipping...</error>' );
			} else {
				$this->uploader->upload_build( 'test-report', $test_run_id, $results_dir . '/allure-playwright.zip', $this->output, 'e2e' );
			}
		}

		/**
		 * Allowed status:
		 * - success
		 * - failed
		 * - warning
		 * - cancelled
		 */
		$status = null;

		if ( $test_result->status === 'cancelled' ) {
			$status = 'cancelled';
		}

		// If there's anything on debug.log, it's a warning.
		if ( is_null( $status ) && ! empty( $debug_log ) ) {
			$status = 'warning';
		}

		// If it has failed any assertion, it's a failure.
		if ( is_null( $status ) && $this->playwright_to_puppeteer_converter->has_failed( $result_json ) ) {
			$status = 'failed';
		}

		// If nothing above matched, it's a success.
		if ( is_null( $status ) ) {
			$status = 'success';
		}

		if ( $use_query_monitor_logs ) {
			$this->output->writeln( 'Parsing Query Monitor Logs' );

			$qm_logs              = $this->prepare_qm_log->prepare_qm_logs( $results_dir );
			$qm_logs['debug_log'] = $debug_log;
			$debug_log            = json_encode( $qm_logs );
		}

		$data = [
			'test_run_id'      => $test_run_id,
			'test_result_json' => $result_json,
			'bootstrap_log'    => json_encode( $test_result->bootstrap ),
			'debug_log'        => $debug_log,
			'status'           => $status,
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

		return $response['report_url'];
	}
}
