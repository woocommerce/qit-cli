<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\PluginActivationReportRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class PluginActivationReportRendererTest extends QITTestCase {
	private string $temporary_env = '';

	public function tearDown(): void {
		if ( $this->temporary_env !== '' ) {
			$this->recursive_rmdir( $this->temporary_env );
		}

		parent::tearDown();
	}

	public function test_failed_activation_throws_by_default(): void {
		$env_info = $this->make_env_info_with_activation_report();
		$output   = new BufferedOutput();
		$renderer = new PluginActivationReportRenderer( $output );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Plugin woocommerce-gateway-stripe/woocommerce-gateway-stripe.php failed to activate.' );
		$this->expectExceptionMessage( 'WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method' );

		$renderer->render_php_activation_report( $env_info, 'activation output' );
	}

	public function test_failed_activation_can_be_returned_without_throwing(): void {
		$env_info = $this->make_env_info_with_activation_report();
		$output   = new BufferedOutput();
		$renderer = new PluginActivationReportRenderer( $output );

		$failures = $renderer->render_php_activation_report( $env_info, 'activation output', false );

		$this->assertCount( 1, $failures );
		$this->assertSame( 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php', $failures[0]['plugin'] );
		$this->assertSame( 'activation output', $failures[0]['output'] );
		$this->assertStringContainsString(
			'WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method',
			$failures[0]['debug_log'][0]
		);
		$this->assertStringContainsString(
			'Plugin woocommerce-gateway-stripe/woocommerce-gateway-stripe.php failed to activate.',
			$output->fetch()
		);
	}

	private function make_env_info_with_activation_report(): E2EEnvInfo {
		$this->temporary_env = sys_get_temp_dir() . '/qit-activation-report-' . uniqid();
		mkdir( $this->temporary_env . '/bin', 0755, true );

		$report = [
			[
				'plugin'    => 'woocommerce/woocommerce.php',
				'activated' => true,
				'debug_log' => [],
			],
			[
				'plugin'    => 'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php',
				'activated' => false,
				'debug_log' => [
					'[26-Jun-2026 21:44:47 UTC] PHP Fatal error:  Class WC_Stripe_Agentic_Commerce_Csv_Feed contains 1 abstract method and must therefore be declared abstract or implement the remaining methods (Automattic\\WooCommerce\\Internal\\ProductFeed\\Feed\\FeedInterface::get_entry_count) in /var/www/html/wp-content/plugins/woocommerce-gateway-stripe/includes/agentic-commerce/class-wc-stripe-agentic-commerce-csv-feed.php on line 31',
				],
			],
		];

		file_put_contents(
			$this->temporary_env . '/bin/plugin-activation-report.json',
			json_encode( $report )
		);

		$env_info                = new E2EEnvInfo();
		$env_info->temporary_env = $this->temporary_env . '/';

		return $env_info;
	}
}
