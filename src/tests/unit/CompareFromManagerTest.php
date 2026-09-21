<?php

use QIT_CLI\App;
use QIT_CLI\Compare\RunComparison;
use QIT_CLI\Compare\RunSnapshot;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

/**
 * `qit compare` against a Manager that computes the comparison itself (QIT-1096).
 * The local comparison, used against older Managers, is covered by CompareCommandTest.
 */
class CompareFromManagerTest extends \QIT_CLI_Tests\QITTestCase {
	/** @var \Symfony\Component\Console\Tester\ApplicationTester */
	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
		App::setVar( 'mocked_requests', [] );
	}

	/**
	 * @param array<int,array<string,string>> $tests
	 *
	 * @return array<string,mixed>
	 */
	private function run_record( int $id, array $tests ): array {
		return [
			'test_run_id'              => $id,
			'test_type'                => 'activation',
			'wordpress_version'        => '6.7',
			'woocommerce_version'      => $id === 1001 ? '10.9.4' : '11.0.0-rc.1',
			'php_version'              => '8.2',
			'extension_set'            => '',
			'version'                  => '1.0.0',
			'status'                   => 'success',
			'woo_extension'            => [ 'name' => 'My WooCommerce Plugin' ],
			'test_results_manager_url' => sprintf( 'https://qit.woo.com/?qit_results=%d.abc', $id ),
			'created_at'               => '2026-09-18 13:50:00',
			'ctrf_json'                => (string) json_encode( [
				'results' => [
					'summary' => [ 'tests' => count( $tests ) ],
					'tests'   => $tests,
				],
			] ),
		];
	}

	/**
	 * What the Manager's compare endpoint returns: the same document, plus schema,
	 * has_regressions and report_url.
	 *
	 * @return array<string,mixed>
	 */
	private function manager_comparison( bool $regressed ): array {
		$a = RunSnapshot::from_manager_run( '1001', $this->run_record( 1001, [ [ 'name' => 'plugin-a', 'status' => 'passed' ] ] ) );
		$b = RunSnapshot::from_manager_run( '1002', $this->run_record( 1002, [ [ 'name' => 'plugin-a', 'status' => $regressed ? 'failed' : 'passed' ] ] ) );

		$comparison = new RunComparison( $a, $b );

		return array_merge(
			[ 'schema' => 1 ],
			$comparison->to_array(),
			[
				'has_regressions' => $comparison->has_regressions(),
				'report_url'      => 'https://qit.woo.com/?qit_compare=1001.abc~1002.def',
			]
		);
	}

	/**
	 * @param array<string,mixed>|string $response
	 */
	private function mock_manager( $response ): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/compare' ),
			is_string( $response ) ? $response : (string) json_encode( $response )
		);
	}

	/**
	 * @param array<string,mixed> $options
	 */
	private function compare( array $options = [] ): int {
		return $this->application_tester->run( array_merge( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1002',
		], $options ), [ 'capture_stderr_separately' => true ] );
	}

	public function test_json_output_is_the_managers_document(): void {
		$comparison = $this->manager_comparison( true );
		$this->mock_manager( $comparison );

		$exit_code = $this->compare( [ '--format' => 'json' ] );

		$this->assertSame( Command::SUCCESS, $exit_code );
		$this->assertSame( $comparison, json_decode( $this->application_tester->getDisplay(), true ) );
	}

	public function test_asks_the_manager_and_does_not_fetch_the_runs(): void {
		$this->mock_manager( $this->manager_comparison( false ) );

		$this->compare( [ '--format' => 'json' ] );

		$requests = App::getVar( 'mocked_requests' );
		$this->assertCount( 1, $requests );
		$this->assertStringEndsWith( '/wp-json/cd/v1/compare', $requests[0]['url'] );
		$this->assertSame( 'POST', $requests[0]['method'] );
		$this->assertSame( '1001', $requests[0]['post_body']['a'] );
		$this->assertSame( '1002', $requests[0]['post_body']['b'] );
	}

	public function test_human_output_links_to_the_report_page(): void {
		$this->mock_manager( $this->manager_comparison( true ) );

		$this->compare();
		$display = $this->application_tester->getDisplay();

		$this->assertStringContainsString( 'Introduced failures (1)', $display );
		$this->assertStringContainsString( 'Report: https://qit.woo.com/?qit_compare=1001.abc~1002.def', $display );
	}

	public function test_exit_code_follows_the_managers_verdict(): void {
		$this->mock_manager( $this->manager_comparison( true ) );
		$this->assertSame( Command::FAILURE, $this->compare( [ '--exit-code' => true ] ) );

		$this->mock_manager( $this->manager_comparison( false ) );
		$this->assertSame( Command::SUCCESS, $this->compare( [ '--exit-code' => true ] ) );
	}

	public function test_a_comparison_the_manager_refuses_is_an_error(): void {
		$this->mock_manager( [ 'message' => 'Cannot compare a "activation" run against a "woo-api" run.' ] );

		$this->assertSame( Command::INVALID, $this->compare() );
		$this->assertStringContainsString( 'Cannot compare a "activation" run against a "woo-api" run.', $this->application_tester->getDisplay() );
	}

	public function test_an_unknown_run_says_what_to_do_next(): void {
		$this->mock_manager( [ 'message' => 'Test run with ID 1002 does not exist.' ] );

		$this->assertSame( Command::INVALID, $this->compare() );

		$display = $this->application_tester->getDisplay();
		$this->assertStringContainsString( 'Could not compare test runs 1001 and 1002: Test run with ID 1002 does not exist.', $display );
		$this->assertStringContainsString( 'qit list-tests', $display );
	}

	public function test_a_manager_without_the_endpoint_falls_back_to_comparing_locally(): void {
		$this->mock_manager( [ 'code' => 'rest_no_route', 'message' => 'No route was found matching the URL and request method.' ] );
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-multiple' ),
			(string) json_encode( [
				'1001' => $this->run_record( 1001, [ [ 'name' => 'plugin-a', 'status' => 'passed' ] ] ),
				'1002' => $this->run_record( 1002, [ [ 'name' => 'plugin-a', 'status' => 'failed' ] ] ),
			] )
		);

		$this->assertSame( Command::FAILURE, $this->compare( [ '--exit-code' => true ] ) );
		$this->assertStringNotContainsString( 'Report:', $this->application_tester->getDisplay() );
	}
}
