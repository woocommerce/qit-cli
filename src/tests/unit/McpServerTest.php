<?php

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\MCP\McpServer;
use QIT_CLI\MCP\StdioTransport;
use QIT_CLI\MCP\ToolRegistry;
use QIT_CLI_Tests\QITTestCase;
use function QIT_CLI\get_manager_url;
use function QIT_CLI\is_mcp_command_argv;

class McpServerTest extends QITTestCase {
	/** @var array<string> */
	private array $created_dirs = [];

	protected function tearDown(): void {
		foreach ( $this->created_dirs as $dir ) {
			$this->recursive_rmdir( $dir );
		}
		$this->created_dirs = [];
		parent::tearDown();
	}

	public function test_initialize_returns_server_info(): void {
		$response = App::make( McpServer::class )->handle( [
			'jsonrpc' => '2.0',
			'id'      => 1,
			'method'  => 'initialize',
			'params'  => [],
		] );

		$this->assertSame( '2.0', $response['jsonrpc'] );
		$this->assertSame( 1, $response['id'] );
		$this->assertSame( 'qit', $response['result']['serverInfo']['name'] );
		$this->assertArrayHasKey( 'tools', $response['result']['capabilities'] );
	}

	public function test_tools_list_includes_read_only_qit_tools(): void {
		$response = App::make( McpServer::class )->handle( [
			'jsonrpc' => '2.0',
			'id'      => 2,
			'method'  => 'tools/list',
		] );

		$names = array_column( $response['result']['tools'], 'name' );

		$this->assertContains( 'qit_get_run', $names );
		$this->assertContains( 'qit_get_results', $names );
		$this->assertContains( 'qit_get_failures', $names );
		$this->assertContains( 'qit_get_last_local_run_context', $names );
		$this->assertContains( 'qit_list_environments', $names );
		$this->assertContains( 'qit_get_artifacts', $names );
		$this->assertNotContains( 'qit_run_test', $names );
	}

	public function test_unknown_tool_returns_structured_tool_error(): void {
		$response = $this->call_tool_response( 'qit_does_not_exist', [] );

		$this->assertTrue( $response['result']['isError'] );
		$this->assertStringContainsString( 'Unknown tool', $response['result']['content'][0]['text'] );
	}

	public function test_stdio_transport_writes_only_protocol_messages(): void {
		$input  = fopen( 'php://temp', 'r+' );
		$output = fopen( 'php://temp', 'r+' );
		$error  = fopen( 'php://temp', 'r+' );

		fwrite( $input, json_encode( [
			'jsonrpc' => '2.0',
			'id'      => 1,
			'method'  => 'initialize',
			'params'  => [],
		] ) . "\n" );
		rewind( $input );

		( new StdioTransport( $input, $output, $error ) )->run( App::make( McpServer::class ) );

		rewind( $output );
		$lines = array_values( array_filter( explode( "\n", stream_get_contents( $output ) ), 'strlen' ) );

		$this->assertCount( 1, $lines );
		$this->assertSame( '2.0', json_decode( $lines[0], true )['jsonrpc'] );
	}

	public function test_stdio_transport_substitutes_invalid_utf8_in_response(): void {
		$input  = fopen( 'php://temp', 'r+' );
		$output = fopen( 'php://temp', 'r+' );
		$error  = fopen( 'php://temp', 'r+' );

		fwrite( $input, json_encode( [
			'jsonrpc' => '2.0',
			'id'      => 7,
			'method'  => 'tools/call',
			'params'  => [
				'name'      => 'invalid_utf8_fixture',
				'arguments' => new stdClass(),
			],
		] ) . "\n" );
		rewind( $input );

		$registry = new class() extends ToolRegistry {
			public function __construct() {}

			public function call( string $name, array $arguments ): array {
				return [
					'debug_log' => "Fatal error before invalid byte \xB1 after invalid byte",
				];
			}

			public function list_tools(): array {
				return [];
			}
		};

		( new StdioTransport( $input, $output, $error ) )->run( new McpServer( $registry ) );

		rewind( $output );
		$line = trim( stream_get_contents( $output ) );

		$this->assertNotSame( '', $line );
		$this->assertStringContainsString( '\\ufffd', $line );

		$response = json_decode( $line, true );
		$this->assertSame( JSON_ERROR_NONE, json_last_error() );
		$this->assertSame( 7, $response['id'] );
		$this->assertArrayHasKey( 'result', $response );
		$this->assertStringContainsString( '\\ufffd', $response['result']['content'][0]['text'] );
	}

	public function test_mcp_mode_only_matches_command_token(): void {
		$this->assertTrue( is_mcp_command_argv( [ 'qit', 'mcp' ] ) );
		$this->assertTrue( is_mcp_command_argv( [ 'qit', '--no-interaction', 'mcp' ] ) );
		$this->assertFalse( is_mcp_command_argv( [ 'qit', 'run:e2e', 'mcp' ] ) );
		$this->assertFalse( is_mcp_command_argv( [ 'qit', 'run:e2e', '--filter', 'mcp' ] ) );
		$this->assertFalse( is_mcp_command_argv( [ 'qit', 'get', 'mcp', '--json-results' ] ) );
		$this->assertFalse( is_mcp_command_argv( [ 'qit', '--help' ] ) );
		$this->assertFalse( is_mcp_command_argv( [ 'qit' ] ) );
	}

	public function test_get_run_decodes_results_and_redacts_sensitive_report_url(): void {
		$this->mock_get_single_response( $this->make_e2e_response() );

		$result = $this->call_tool( 'qit_get_run', [
			'test_run_id' => 98765,
		] );

		$this->assertSame( 98765, $result['test_run_id'] );
		$this->assertSame( 'https://qit.woo.com/results/[REDACTED]', $result['result_url'] );
		$this->assertIsArray( $result['results']['ctrf_json'] );
		$this->assertSame( 'playwright', $result['results']['ctrf_json']['results']['tool']['name'] );
	}

	public function test_get_results_prefers_ctrf(): void {
		$this->mock_get_single_response( $this->make_e2e_response() );

		$result = $this->call_tool( 'qit_get_results', [
			'test_run_id' => 98765,
		] );

		$this->assertSame( 'ctrf_json', $result['source'] );
		$this->assertSame( 1, $result['results']['results']['summary']['failed'] );
	}

	public function test_get_failures_extracts_ctrf_failures_and_debug_signals(): void {
		$response              = $this->make_e2e_response();
		$response['debug_log'] = json_encode( [
			'debug_log' => "[01-Jan-2025 00:00:00 UTC] PHP Fatal error: Uncaught RuntimeException\nplain line",
		] );
		$this->mock_get_single_response( $response );

		$result = $this->call_tool( 'qit_get_failures', [
			'test_run_id' => 98765,
		] );

		$this->assertCount( 1, $result['failures'] );
		$this->assertSame( 'can apply coupon at checkout', $result['failures'][0]['name'] );
		$this->assertSame( 1, $result['debug_signals']['matching_lines'] );
		$this->assertNotEmpty( $result['next_steps'] );
	}

	public function test_get_failures_max_debug_log_lines_zero_returns_no_debug_lines(): void {
		$response              = $this->make_e2e_response();
		$response['debug_log'] = json_encode( [
			'debug_log' => "[01-Jan-2025 00:00:00 UTC] PHP Fatal error: Uncaught RuntimeException\nplain line",
		] );
		$this->mock_get_single_response( $response );

		$result = $this->call_tool( 'qit_get_failures', [
			'test_run_id'         => 98765,
			'max_debug_log_lines' => 0,
		] );

		$this->assertSame( 2, $result['debug_signals']['total_lines'] );
		$this->assertSame( 0, $result['debug_signals']['matching_lines'] );
		$this->assertSame( [], $result['debug_signals']['lines'] );
	}

	public function test_embedded_result_urls_are_redacted_in_failures_and_debug_logs(): void {
		$response = $this->make_e2e_response();
		$ctrf     = json_decode( $response['ctrf_json'], true );

		$ctrf['results']['tests'][1]['message'] = 'See https://qit.woo.com/results/98765.secret?auth=abc before retrying.';
		$response['ctrf_json']                  = json_encode( $ctrf );
		$response['debug_log']                  = json_encode( [
			'debug_log' => '[01-Jan-2025 00:00:00 UTC] PHP Fatal error: See https://qit.woo.com/results/98765.secret?auth=abc',
		] );

		$this->mock_get_single_response( $response );

		$result = $this->call_tool( 'qit_get_failures', [
			'test_run_id' => 98765,
		] );

		$this->assertSame( 'See https://qit.woo.com/results/[REDACTED]?[REDACTED] before retrying.', $result['failures'][0]['message'] );
		$this->assertStringContainsString(
			'https://qit.woo.com/results/[REDACTED]?[REDACTED]',
			$result['debug_signals']['lines'][0]
		);
		$this->assertStringNotContainsString( '98765.secret', $result['failures'][0]['message'] );
		$this->assertStringNotContainsString( 'auth=abc', $result['debug_signals']['lines'][0] );
	}

	public function test_get_failures_extracts_legacy_security_messages(): void {
		$this->mock_get_single_response( $this->make_security_response() );

		$result = $this->call_tool( 'qit_get_failures', [
			'test_run_id' => 55555,
		] );

		$this->assertCount( 2, $result['failures'] );
		$this->assertSame( 'my-plugin/includes/class-api.php', $result['failures'][0]['file'] );
		$this->assertSame( 'PHPCS.Security.SQLInjection', $result['failures'][0]['rule'] );
	}

	public function test_last_local_run_context_redacts_remote_report(): void {
		$last_run = [
			'run_id'        => 'local-123',
			'remote_report' => 'https://qit.woo.com/results/local.secret-token?auth=abc',
			'artifacts'     => [
				'reports' => [
					[
						'type' => 'ctrf',
						'path' => '/tmp/qit/results/ctrf-report.json',
					],
				],
			],
		];
		file_put_contents( rtrim( Config::get_qit_dir(), '/' ) . '/last-run.json', json_encode( $last_run ) );

		$result = $this->call_tool( 'qit_get_last_local_run_context', [] );

		$this->assertTrue( $result['found'] );
		$this->assertSame( 'https://qit.woo.com/results/[REDACTED]?[REDACTED]', $result['context']['remote_report'] );
	}

	public function test_malformed_last_local_run_returns_tool_error(): void {
		file_put_contents( rtrim( Config::get_qit_dir(), '/' ) . '/last-run.json', '{not-json' );

		$response = $this->call_tool_response( 'qit_get_last_local_run_context', [] );

		$this->assertTrue( $response['result']['isError'] );
		$this->assertStringContainsString( 'JSON file is malformed', $response['result']['content'][0]['text'] );
	}

	public function test_list_environments_returns_running_environment(): void {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$result = $this->call_tool( 'qit_list_environments', [] );

		$this->assertCount( 1, $result['environments'] );
		$this->assertSame( 'mcp-env-1', $result['environments'][0]['env_id'] );
	}

	/**
	 * @param array<string,mixed> $response
	 */
	private function mock_get_single_response( array $response ): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);
	}

	/**
	 * @param string              $name
	 * @param array<string,mixed> $arguments
	 * @return array<string,mixed>
	 */
	private function call_tool( string $name, array $arguments ): array {
		$response = $this->call_tool_response( $name, $arguments );

		$this->assertArrayNotHasKey( 'error', $response );
		$this->assertArrayHasKey( 'structuredContent', $response['result'] );

		return $response['result']['structuredContent'];
	}

	/**
	 * @param string              $name
	 * @param array<string,mixed> $arguments
	 * @return array<string,mixed>
	 */
	private function call_tool_response( string $name, array $arguments ): array {
		return App::make( McpServer::class )->handle( [
			'jsonrpc' => '2.0',
			'id'      => 10,
			'method'  => 'tools/call',
			'params'  => [
				'name'      => $name,
				'arguments' => $arguments,
			],
		] );
	}

	private function make_env_info(): EnvInfo {
		$temp_envs_dir = Environment::get_temp_envs_dir();
		$env_dir       = $temp_envs_dir . 'e2e-mcp-env';

		if ( ! is_dir( $env_dir ) ) {
			mkdir( $env_dir, 0755, true );
			$this->created_dirs[] = $env_dir;
		}

		$env_info                = EnvInfo::from_array( [ 'environment' => 'e2e' ] );
		$env_info->temporary_env = $env_dir;
		$env_info->env_id        = 'mcp-env-1';
		$env_info->created_at    = 1708728299;
		$env_info->status        = 'running';
		$env_info->site_url      = 'http://localhost:8080';

		return $env_info;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function make_e2e_response(): array {
		return [
			'test_run_id'              => 98765,
			'run_id'                   => 98765,
			'test_type'                => 'e2e',
			'test_type_display'        => 'E2E',
			'wordpress_version'        => '6.7',
			'woocommerce_version'      => '9.5.1',
			'php_version'              => '8.2',
			'test_result_json'         => json_encode( [
				'summary'     => 'Tests: 3 total, 2 passed, 1 failed',
				'testResults' => [
					[
						'tests' => [
							'Checkout flow' => [
								[
									'status' => 'passed',
									'title'  => 'can add product to cart',
								],
								[
									'status' => 'failed',
									'title'  => 'can apply coupon at checkout',
								],
							],
						],
					],
				],
			] ),
			'ctrf_json'                => json_encode( [
				'results' => [
					'tool'    => [ 'name' => 'playwright' ],
					'summary' => [
						'tests'   => 3,
						'passed'  => 2,
						'failed'  => 1,
						'pending' => 0,
						'skipped' => 0,
						'other'   => 0,
					],
					'tests'   => [
						[
							'name'   => 'can add product to cart',
							'status' => 'passed',
						],
						[
							'name'    => 'can apply coupon at checkout',
							'status'  => 'failed',
							'message' => 'Expected coupon discount to be applied.',
						],
					],
				],
			] ),
			'status'                   => 'failed',
			'woo_extension'            => [
				'id'   => 12345,
				'name' => 'My WooCommerce Plugin',
				'type' => 'plugin',
			],
			'test_results_manager_url' => 'https://qit.woo.com/results/98765.abc123',
			'test_summary'             => 'Tests: 3 total, 2 passed, 1 failed',
			'debug_log'                => '',
			'update_complete'          => true,
			'test_media'               => [],
			'created_at'               => '2025-01-15 10:30:00',
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	private function make_security_response(): array {
		return [
			'test_run_id'              => 55555,
			'run_id'                   => 55555,
			'test_type'                => 'security',
			'test_type_display'        => 'Security',
			'test_result_json'         => json_encode( [
				'tool'    => 'phpcs-security-audit',
				'summary' => '2 warnings found',
				'files'   => [
					'my-plugin/includes/class-api.php' => [
						'messages' => [
							[
								'message'  => 'Possible SQL injection via $wpdb->prepare().',
								'source'   => 'PHPCS.Security.SQLInjection',
								'severity' => 5,
								'line'     => 42,
								'column'   => 15,
								'type'     => 'WARNING',
							],
							[
								'message'  => 'User input output without escaping.',
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
			'ctrf_json'                => '',
			'status'                   => 'warning',
			'test_results_manager_url' => 'https://qit.woo.com/results/55555.def456',
			'test_summary'             => '2 warnings found',
			'debug_log'                => '',
			'update_complete'          => true,
		];
	}
}
