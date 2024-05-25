<?php

namespace QIT_CLI\LocalTests;

use QIT_CLI\App;
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

	public function __construct(
		Zipper $zipper,
		OutputInterface $output,
		Upload $uploader,
		PrepareDebugLog $prepare_debug_log
	) {
		$this->zipper   = $zipper;
		$this->output   = $output;
		$this->uploader = $uploader;
		$this->prepare_debug_log = $prepare_debug_log;
	}

	public function notify_test_started( string $woo_extension_id, string $woocommerce_version, E2EEnvInfo $env_info ): void {
		$r = App::make( RequestBuilder::class )
				->with_url( get_manager_url() . '/wp-json/cd/v1/local-test-started' )
				->with_method( 'POST' )
				->with_expected_status_codes( [ 200 ] )
				->with_timeout_in_seconds( 60 )
				->with_post_body( [
					'woo_id'              => $woo_extension_id,
					'woocommerce_version' => $woocommerce_version,
					'wordpress_version'   => $env_info->wp,
					'php_version'         => $env_info->php_version,
					'test_type'           => 'e2e',
					'event'               => 'e2e_local_run',
				] )
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
		App::setVar( 'attachment_base_url', $response['report_url'] );
	}

	public function notify_test_finished( TestResult $test_result ): void {
		$test_run_id = App::getVar( 'test_run_id' );

		if ( empty( $test_run_id ) ) {
			throw new \RuntimeException( 'Test run ID not set.' );
		}

		$results_dir = $test_result->get_results_dir();

		$result_file = $results_dir . '/result.json';

		if ( ! file_exists( $result_file ) ) {
			throw new \RuntimeException( 'Result file not found.' );
		}

		$result_json = file_get_contents( $result_file );

		if ( empty( json_decode( $result_json, true ) ) ) {
			throw new \RuntimeException( 'Result file not a JSON.' );
		}

		$test_log = '';

		if ( file_exists( $results_dir . '/debug.log' ) ) {
			$prepared_debug_log_path = $results_dir . '/debug-prepared.log';
			$this->prepare_debug_log->prepare_debug_log( $results_dir . '/debug.log', $prepared_debug_log_path, App::getVar( E2EEnvInfo::class ) );
			$test_log = file_get_contents( $prepared_debug_log_path, false, null, 0, 1 * 1024 * 1024 ); // First 1mb of debug.log
		}

		if ( file_exists( $results_dir . '/allure-playwright' ) ) {
			$this->zipper->zip_directory( $results_dir . '/allure-playwright', $results_dir . '/allure-playwright.zip' );
			if ( filesize( $results_dir . '/allure-playwright.zip' ) > 200 * 1024 * 1024 ) {
				$this->output->writeln( '<error>Report is too large to upload. Skipping...</error>' );
			} else {
				$upload_id    = $this->uploader->upload_build( 'test-report', $test_run_id, $results_dir . '/allure-playwright.zip', $this->output, 'e2e' );
			}
		}

		$data = [
			'test_run_id'      => $test_run_id,
			'test_result_json' => $result_json,
			'bootstrap_log'    => json_encode( $test_result->bootstrap ),
			'test_log'         => $test_log,
		];

		if ( isset( $upload_id ) ) {
			$data['upload_id'] = $upload_id;
		}

		$r = App::make( RequestBuilder::class )
				->with_url( get_manager_url() . '/wp-json/cd/v1/local-test-finished' )
				->with_method( 'POST' )
				->with_expected_status_codes( [ 200 ] )
				->with_timeout_in_seconds( 60 )
				->with_post_body( $data )
				->request();
	}
}
