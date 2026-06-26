<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\RunE2ECommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\Utils\LocalTestRunNotifier;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

class LocalTestRunNotifierCompatibilityTest extends QITTestCase {
	private string $results_dir = '';

	public function tearDown(): void {
		if ( $this->results_dir !== '' ) {
			$this->recursive_rmdir( $this->results_dir );
		}

		parent::tearDown();
	}

	public function test_start_notification_includes_resolved_extension_specs(): void {
		$env_info = $this->make_env_info();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/local-test-started' ),
			json_encode( [
				'success'           => true,
				'test_run_id'       => 4242,
				'allure_report_url' => 'https://example.com/report',
			] )
		);

		App::make( LocalTestRunNotifier::class )->notify_test_started(
			123,
			'10.9.0',
			$env_info,
			false,
			false,
			'compatibility'
		);

		$request_body = App::getVar( 'mocked_request' )['post_body'];

		$this->assertSame( 'compatibility', $request_body['test_type'] );
		$this->assertSame( 'woocommerce-gateway-stripe', $request_body['extension_specs'][0]['slug'] );
		$this->assertSame( '10.5.x', $request_body['extension_specs'][0]['requested_version'] );
		$this->assertSame( '10.5.3', $request_body['extension_specs'][0]['resolved_version'] );
		$this->assertSame( 'github_release', $request_body['extension_specs'][0]['artifact_ref']['source'] );
	}

	public function test_finish_notification_preserves_debug_log_fatal_and_fails(): void {
		$env_info          = $this->make_env_info();
		$this->results_dir = sys_get_temp_dir() . '/qit-notifier-' . uniqid();
		mkdir( $this->results_dir );
		$debug_log_content = '[26-Jun-2026 00:00:00 UTC] PHP Fatal error: Class WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method and must therefore be declared abstract or implement the remaining methods (Automattic\WooCommerce\Internal\ProductFeed\Feed\FeedInterface::get_entry_count) in /var/www/html/wp-content/plugins/woocommerce-gateway-stripe/includes/agentic-commerce/class-wc-stripe-agentic-commerce-csv-feed.php on line 31';

		[ $request_body, $debug_log, $exit_status_override ] = $this->finish_notification_with_debug_log( $env_info, $debug_log_content, 'compatibility' );

		$this->assertSame( 'failed', $request_body['status'] );
		$this->assertSame( Command::FAILURE, $exit_status_override );
		$this->assertStringContainsString( 'WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method', $debug_log['debug_log'] );
		$this->assertSame( 'woocommerce-gateway-stripe', $request_body['extension_specs'][0]['slug'] );
	}

	public function test_finish_notification_warns_when_debug_log_only_mentions_fatal_error(): void {
		$env_info          = $this->make_env_info();
		$this->results_dir = sys_get_temp_dir() . '/qit-notifier-' . uniqid();
		mkdir( $this->results_dir );
		$debug_log_content = '[26-Jun-2026 00:00:00 UTC] PHP Warning: Fatal error was mentioned in compatibility guidance in /var/www/html/wp-content/plugins/woocommerce-gateway-stripe/includes/example.php on line 31';

		[ $request_body, $debug_log, $exit_status_override ] = $this->finish_notification_with_debug_log( $env_info, $debug_log_content, 'compatibility' );

		$this->assertSame( 'warning', $request_body['status'] );
		$this->assertSame( RunE2ECommand::WARNING, $exit_status_override );
		$this->assertStringContainsString( 'PHP Warning: Fatal error was mentioned', $debug_log['debug_log'] );
		$this->assertSame( 'woocommerce-gateway-stripe', $request_body['extension_specs'][0]['slug'] );
	}

	public function test_finish_notification_preserves_e2e_status_when_debug_log_has_fatal(): void {
		$env_info          = $this->make_env_info();
		$this->results_dir = sys_get_temp_dir() . '/qit-notifier-' . uniqid();
		mkdir( $this->results_dir );
		$debug_log_content = '[26-Jun-2026 00:00:00 UTC] PHP Fatal error: Class WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method and must therefore be declared abstract or implement the remaining methods (Automattic\WooCommerce\Internal\ProductFeed\Feed\FeedInterface::get_entry_count) in /var/www/html/wp-content/plugins/woocommerce-gateway-stripe/includes/agentic-commerce/class-wc-stripe-agentic-commerce-csv-feed.php on line 31';

		[ $request_body, $debug_log, $exit_status_override ] = $this->finish_notification_with_debug_log( $env_info, $debug_log_content, 'e2e' );

		$this->assertSame( 'success', $request_body['status'] );
		$this->assertNull( $exit_status_override );
		$this->assertStringContainsString( 'WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method', $debug_log['debug_log'] );
		$this->assertSame( 'woocommerce-gateway-stripe', $request_body['extension_specs'][0]['slug'] );
	}

	/**
	 * @return array{array<string,mixed>,array<string,mixed>,int|null}
	 */
	private function finish_notification_with_debug_log( E2EEnvInfo $env_info, string $debug_log_content, string $test_type ): array {

		file_put_contents(
			$this->results_dir . '/debug.log',
			$debug_log_content
		);

		App::setVar( 'test_run_id', 4242 );
		App::singleton( EnvInfo::class, $env_info );
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/local-test-finished' ),
			json_encode( [
				'success'    => true,
				'report_url' => 'https://example.com/report',
			] )
		);

		$test_result = new class( $env_info, $this->results_dir ) {
			public string $status = 'success';
			private E2EEnvInfo $env_info;
			private string $results_dir;

			public function __construct( E2EEnvInfo $env_info, string $results_dir ) {
				$this->env_info    = $env_info;
				$this->results_dir = $results_dir;
			}

			public function get_env_info(): E2EEnvInfo {
				return $this->env_info;
			}

			public function get_results_dir(): string {
				return $this->results_dir;
			}
		};

		[ , $exit_status_override ] = App::make( LocalTestRunNotifier::class )->notify_test_finished( $test_result, null, $test_type );

		$request_body = App::getVar( 'mocked_request' )['post_body'];
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Test decodes notifier payload.
		$debug_log = json_decode( gzuncompress( base64_decode( $request_body['debug_log'] ) ), true );

		return [ $request_body, $debug_log, $exit_status_override ];
	}

	private function make_env_info(): E2EEnvInfo {
		$env_info                    = new E2EEnvInfo();
		$env_info->sut               = [
			'slug'    => 'woocommerce',
			'version' => '10.9.0',
		];
		$env_info->wordpress_version = '6.8';
		$env_info->php_version       = '8.2';

		$stripe                    = new Extension( 'woocommerce-gateway-stripe', 'plugin' );
		$stripe->from              = 'wccom';
		$stripe->wccom_id          = 18619;
		$stripe->requested_version = '10.5.x';
		$stripe->version           = '10.5.3';
		$stripe->artifact_ref      = [
			'source' => 'github_release',
			'tag'    => '10.5.3',
			'url'    => 'https://github.com/woocommerce/woocommerce-gateway-stripe/releases/download/10.5.3/woocommerce-gateway-stripe.zip',
		];

		$env_info->plugins = [ $stripe ];

		return $env_info;
	}
}
