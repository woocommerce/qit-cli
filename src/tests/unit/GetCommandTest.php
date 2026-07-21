<?php

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

class GetCommandTest extends \QIT_CLI_Tests\QITTestCase {
	use MatchesSnapshots;

	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
	}

	/**
	 * Build a realistic API response for a completed E2E test run.
	 * Contains both ctrf_json and test_result_json as plain JSON strings
	 * (the Manager decompresses on ingestion).
	 */
	private function make_e2e_response(): array {
		return [
			'test_run_id'                     => 98765,
			'run_id'                          => 98765,
			'test_type'                       => 'e2e',
			'test_type_display'               => 'E2E',
			'wordpress_version'               => '6.7',
			'woocommerce_version'             => '9.5.1',
			'php_version'                     => '8.2',
			'max_php_version'                 => '',
			'min_php_version'                 => '',
			'additional_woo_plugins'          => [],
			'additional_wp_plugins'           => [],
			'test_log'                        => '',
			'test_result_json'                => json_encode( [
				'summary'   => 'Tests: 3 total, 2 passed, 1 failed',
				'testResults' => [
					[
						'tests' => [
							'Checkout flow' => [
								[ 'status' => 'passed', 'title' => 'can add product to cart' ],
								[ 'status' => 'passed', 'title' => 'can complete checkout' ],
								[ 'status' => 'failed', 'title' => 'can apply coupon at checkout' ],
							],
						],
						'status' => 'failed',
					],
				],
			] ),
			'ctrf_json'                       => json_encode( [
				'results' => [
					'tool'    => [ 'name' => 'playwright' ],
					'summary' => [
						'tests'   => 3,
						'passed'  => 2,
						'failed'  => 1,
						'pending' => 0,
						'skipped' => 0,
						'other'   => 0,
						'start'   => 1706644023,
						'stop'    => 1706644043,
					],
					'tests'   => [
						[
							'name'     => 'can add product to cart',
							'status'   => 'passed',
							'duration' => 1200,
						],
						[
							'name'     => 'can complete checkout',
							'status'   => 'passed',
							'duration' => 2400,
						],
						[
							'name'     => 'can apply coupon at checkout',
							'status'   => 'failed',
							'duration' => 800,
							'message'  => 'Expected coupon discount to be applied but total remained unchanged.',
						],
					],
				],
			] ),
			'performance_results'             => '',
			'status'                          => 'failed',
			'test_result_aws_url'             => '',
			'test_result_aws_expiration'      => 0,
			'is_development'                  => false,
			'send_notifications'              => true,
			'woo_extension'                   => [
				'id'   => 12345,
				'host' => 'wccom',
				'name' => 'My WooCommerce Plugin',
				'type' => 'plugin',
			],
			'client'                          => 'qit_cli',
			'event'                           => 'cli',
			'optional_features'               => [ 'hpos' => true, 'new_product_editor' => false ],
			'test_results_manager_url'        => 'https://qit.woo.com/results/98765.abc123',
			'test_results_manager_expiration' => 1234567890,
			'test_summary'                    => 'Tests: 3 total, 2 passed, 1 failed, 0 skipped',
			'debug_log'                       => '',
			'version'                         => '1.0.0',
			'update_complete'                 => true,
			'ai_suggestion_status'            => 'none',
			'malware_whitelist_paths'         => [],
			'workflow_id'                     => '',
			'runner'                          => '',
			'test_media'                      => [],
			'extension_set'                   => '',
			'phpstan_level'                   => null,
			'test_variation'                  => '',
			'test_packages'                   => [],
			'test_group_id'                   => '',
			'created_at'                      => '2025-01-15 10:30:00',
		];
	}

	/**
	 * Build a realistic API response for a completed security test.
	 * Only test_result_json is populated; ctrf_json is empty.
	 */
	private function make_security_response(): array {
		return [
			'test_run_id'                     => 55555,
			'run_id'                          => 55555,
			'test_type'                       => 'security',
			'test_type_display'               => 'Security',
			'wordpress_version'               => '6.7',
			'woocommerce_version'             => '9.5.1',
			'php_version'                     => '8.2',
			'max_php_version'                 => '',
			'min_php_version'                 => '',
			'additional_woo_plugins'          => [],
			'additional_wp_plugins'           => [],
			'test_log'                        => '',
			'test_result_json'                => json_encode( [
				'tool'    => 'phpcs-security-audit',
				'summary' => '2 warnings found',
				'files'   => [
					'my-plugin/includes/class-api.php' => [
						'messages' => [
							[
								'message'  => 'Possible SQL injection via $wpdb->prepare() with unquoted placeholder',
								'source'   => 'PHPCS.Security.SQLInjection',
								'severity' => 5,
								'line'     => 42,
								'column'   => 15,
								'type'     => 'WARNING',
							],
							[
								'message'  => 'User input detected with $_GET used in output without escaping',
								'source'   => 'PHPCS.Security.XSSVulnerability',
								'severity' => 5,
								'line'     => 88,
								'column'   => 20,
								'type'     => 'WARNING',
							],
						],
					],
				],
			] ),
			'ctrf_json'                       => '',
			'performance_results'             => '',
			'status'                          => 'warning',
			'test_result_aws_url'             => '',
			'test_result_aws_expiration'      => 0,
			'is_development'                  => false,
			'send_notifications'              => true,
			'woo_extension'                   => [
				'id'   => 12345,
				'host' => 'wccom',
				'name' => 'My WooCommerce Plugin',
				'type' => 'plugin',
			],
			'client'                          => 'qit_cli',
			'event'                           => 'cli',
			'optional_features'               => [ 'hpos' => false, 'new_product_editor' => false ],
			'test_results_manager_url'        => 'https://qit.woo.com/results/55555.def456',
			'test_results_manager_expiration' => 1234567890,
			'test_summary'                    => '2 warnings found',
			'debug_log'                       => '',
			'version'                         => '1.0.0',
			'update_complete'                 => true,
			'ai_suggestion_status'            => 'none',
			'malware_whitelist_paths'         => [],
			'workflow_id'                     => '',
			'runner'                          => '',
			'test_media'                      => [],
			'extension_set'                   => '',
			'phpstan_level'                   => null,
			'test_variation'                  => '',
			'test_packages'                   => [],
			'test_group_id'                   => '',
			'created_at'                      => '2025-01-15 10:30:00',
		];
	}

	/**
	 * Normal (table) output for a completed E2E test.
	 * Demonstrates that ctrf_json and test_result_json are hidden.
	 */
	public function test_get_e2e_table_output(): void {
		$response = $this->make_e2e_response();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'     => 'get',
				'test_run_id' => '98765',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 1, $exit_code, 'E2E failed test should exit with 1' );
		$this->assertMatchesSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * JSON output for a completed E2E test.
	 * Demonstrates that ctrf_json and test_result_json are strings, not parsed objects.
	 */
	public function test_get_e2e_json_output(): void {
		$response = $this->make_e2e_response();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'     => 'get',
				'test_run_id' => '98765',
				'--json'      => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 1, $exit_code, 'E2E failed test should exit with 1' );
		$raw_output = $this->application_tester->getDisplay();
		$decoded    = json_decode( $raw_output, true );
		$this->assertNotNull( $decoded, 'JSON output must be valid JSON' );

		// After fix: ctrf_json and test_result_json are decoded into proper arrays.
		$this->assertIsArray( $decoded['ctrf_json'], 'ctrf_json should be a parsed object' );
		$this->assertIsArray( $decoded['test_result_json'], 'test_result_json should be a parsed object' );

		$this->assertMatchesJsonSnapshot( $decoded );
	}

	/**
	 * Normal (table) output for a completed security test.
	 * Shows that result_url is the only actionable piece of info.
	 */
	public function test_get_security_table_output(): void {
		$response = $this->make_security_response();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'     => 'get',
				'test_run_id' => '55555',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 3, $exit_code, 'Security warning test should exit with 3' );
		$this->assertMatchesSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * JSON output for a completed security test.
	 * Demonstrates that ctrf_json is empty for non-Playwright tests.
	 */
	public function test_get_security_json_output(): void {
		$response = $this->make_security_response();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'     => 'get',
				'test_run_id' => '55555',
				'--json'      => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 3, $exit_code, 'Security warning test should exit with 3' );
		$raw_output = $this->application_tester->getDisplay();
		$decoded    = json_decode( $raw_output, true );
		$this->assertNotNull( $decoded, 'JSON output must be valid JSON' );

		// ctrf_json is empty for non-Playwright managed tests.
		$this->assertSame( '', $decoded['ctrf_json'], 'ctrf_json should be empty for security tests' );
		// test_result_json is decoded into a proper array.
		$this->assertIsArray( $decoded['test_result_json'], 'test_result_json should be a parsed object' );
		$this->assertNotEmpty( $decoded['test_result_json'], 'test_result_json should not be empty' );

		$this->assertMatchesJsonSnapshot( $decoded );
	}

	/**
	 * Successful E2E test exits with 0.
	 */
	public function test_get_successful_test_exits_zero(): void {
		$response           = $this->make_e2e_response();
		$response['status'] = 'success';

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'     => 'get',
				'test_run_id' => '98765',
				'--json'      => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 0, $exit_code );
	}

	/**
	 * Warning status exits with 3.
	 */
	public function test_get_warning_test_exits_three(): void {
		$response           = $this->make_e2e_response();
		$response['status'] = 'warning';

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'     => 'get',
				'test_run_id' => '98765',
				'--json'      => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 3, $exit_code );
	}

	/**
	 * --json-results for E2E returns parsed CTRF JSON (preferred over test_result_json).
	 */
	public function test_get_e2e_json_results_output(): void {
		$response = $this->make_e2e_response();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'        => 'get',
				'test_run_id'    => '98765',
				'--json-results' => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 1, $exit_code );
		$raw_output = $this->application_tester->getDisplay();
		$decoded    = json_decode( $raw_output, true );
		$this->assertNotNull( $decoded, 'Output must be valid JSON' );

		// Should be the CTRF results, not test_result_json.
		$this->assertArrayHasKey( 'results', $decoded );
		$this->assertArrayHasKey( 'tool', $decoded['results'] );
		$this->assertSame( 'playwright', $decoded['results']['tool']['name'] );

		$this->assertMatchesJsonSnapshot( $decoded );
	}

	/**
	 * --json-results for security falls back to test_result_json when ctrf_json is empty.
	 */
	public function test_get_security_json_results_output(): void {
		$response = $this->make_security_response();

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'        => 'get',
				'test_run_id'    => '55555',
				'--json-results' => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 3, $exit_code );
		$raw_output = $this->application_tester->getDisplay();
		$decoded    = json_decode( $raw_output, true );
		$this->assertNotNull( $decoded, 'Output must be valid JSON' );

		// Should be the security test_result_json.
		$this->assertArrayHasKey( 'tool', $decoded );
		$this->assertSame( 'phpcs-security-audit', $decoded['tool'] );

		$this->assertMatchesJsonSnapshot( $decoded );
	}

	/**
	 * --json-results with no results available fails with error.
	 */
	public function test_get_json_results_no_data(): void {
		$response                     = $this->make_e2e_response();
		$response['ctrf_json']        = '';
		$response['test_result_json'] = '';

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run(
			[
				'command'        => 'get',
				'test_run_id'    => '98765',
				'--json-results' => true,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( 1, $exit_code );
		$display = $this->application_tester->getDisplay();
		$this->assertStringContainsString( 'No test results available', $display );
	}

	public function test_get_api_fuzz_displays_campaign_state_separately(): void {
		$response                     = $this->make_security_response();
		$response['test_type']         = 'api-fuzz';
		$response['test_type_display'] = 'API Fuzz';
		$response['test_result_json'] = json_encode( [
			'campaign' => [ 'state' => 'not_applicable' ],
			'findings' => [],
		] );

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run( [
			'command'     => 'get',
			'test_run_id' => '55555',
		] );

		$this->assertSame( 3, $exit_code );
		$this->assertStringContainsString( 'Campaign State', $this->application_tester->getDisplay() );
		$this->assertStringContainsString( 'not_applicable', $this->application_tester->getDisplay() );
	}

	public function test_get_check_finished_treats_cancelled_as_terminal(): void {
		$response                    = $this->make_security_response();
		$response['status']          = 'cancelled';
		$response['update_complete'] = false;

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$exit_code = $this->application_tester->run( [
			'command'          => 'get',
			'test_run_id'      => '55555',
			'--check_finished' => true,
		] );

		$this->assertSame( Command::SUCCESS, $exit_code );
	}

	public function test_get_displays_report_url(): void {
		$response = $this->make_security_response();
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);

		$tester = $this->make_application_tester();
		$tester->run( [
			'command'     => 'get',
			'test_run_id' => '55555',
		] );
		$this->assertStringContainsString( '55555.def456', $tester->getDisplay() );
	}
}
