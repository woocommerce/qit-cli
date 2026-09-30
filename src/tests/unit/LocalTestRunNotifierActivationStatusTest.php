<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\RunE2ECommand;
use QIT_CLI\E2E\Result\TestResult;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Utils\LocalTestRunNotifier;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

/**
 * An activation `warning` means the extension works but logged non-fatal PHP
 * errors; consumers may count it as passing, so a fatal or a failed assertion
 * must never end as `warning`.
 */
class LocalTestRunNotifierActivationStatusTest extends QITTestCase {
	private const NOTICE    = [
		'message' => 'Function _load_textdomain_just_in_time was called incorrectly.',
		'file'    => '/var/www/html/wp-includes/functions.php',
		'line'    => '6121',
	];
	private const FATAL_LOG = '[26-Jun-2026 00:00:00 UTC] PHP Fatal error: Uncaught Error: Call to undefined function wc_missing() in /var/www/html/wp-content/plugins/my-extension/my-extension.php:12';

	private string $results_dir = '';

	public function tearDown(): void {
		if ( $this->results_dir !== '' ) {
			$this->recursive_rmdir( $this->results_dir );
		}

		parent::tearDown();
	}

	/**
	 * @return array<string, array{bool, bool, int, string, int|null}>
	 */
	public function activation_status_cases(): array {
		return [
			'clean run'                         => [ false, false, 0, 'success', null ],
			'non-fatal PHP errors only'         => [ true, false, 0, 'warning', RunE2ECommand::WARNING ],
			'fatal alongside non-fatal errors'  => [ true, true, 0, 'failed', Command::FAILURE ],
			'failed assertion alongside notice' => [ true, false, 1, 'failed', Command::FAILURE ],
		];
	}

	/**
	 * @dataProvider activation_status_cases
	 */
	public function test_activation_run_is_warning_only_for_non_fatal_errors( bool $notice, bool $fatal, int $failed_assertions, string $expected_status, ?int $expected_exit_code ): void {
		$env_info          = new E2EEnvInfo();
		$env_info->sut     = [
			'slug'    => 'my-extension',
			'version' => '1.0.0',
		];
		$this->results_dir = sys_get_temp_dir() . '/qit-activation-status-' . uniqid();

		mkdir( $this->results_dir . '/logs', 0755, true );
		mkdir( $this->results_dir . '/final/ctrf', 0755, true );

		file_put_contents( $this->results_dir . '/logs/qm.json', json_encode( [ 'doing_it_wrong' => $notice ? [ self::NOTICE ] : [] ] ) );
		$ctrf_summary = [
			'tests'  => 13,
			'passed' => 13 - $failed_assertions,
			'failed' => $failed_assertions,
		];
		file_put_contents( $this->results_dir . '/final/ctrf/ctrf-report.json', json_encode( [ 'results' => [ 'summary' => $ctrf_summary ] ] ) );

		if ( $fatal ) {
			file_put_contents( $this->results_dir . '/debug.log', self::FATAL_LOG );
		}

		App::setVar( 'test_run_id', 4242 );
		App::singleton( EnvInfo::class, $env_info );
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/local-test-finished' ),
			json_encode( [
				'success'    => true,
				'report_url' => 'https://example.com/report',
			] )
		);

		$test_result = new class( $env_info, $this->results_dir ) extends TestResult {
			public function __construct( E2EEnvInfo $env_info, string $results_dir ) {
				$this->status      = 'success';
				$this->env_info    = $env_info;
				$this->results_dir = $results_dir;
			}
		};

		[ , $exit_code ] = App::make( LocalTestRunNotifier::class )->notify_test_finished( $test_result, null, 'activation' );

		$this->assertSame( $expected_status, App::getVar( 'mocked_request' )['post_body']['status'] );
		$this->assertSame( $expected_exit_code, $exit_code );
	}
}
