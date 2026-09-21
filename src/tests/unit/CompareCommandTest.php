<?php

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

/**
 * `qit compare` renders a comparison the QIT Manager computed. The comparison
 * itself is tested in the Manager; the documents in data/compare are its output.
 */
class CompareCommandTest extends \QIT_CLI_Tests\QITTestCase {
	use MatchesSnapshots;

	/** @var \Symfony\Component\Console\Tester\ApplicationTester */
	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
		App::setVar( 'mocked_requests', [] );
	}

	/**
	 * @return array<string,mixed>
	 */
	private function document( string $name ): array {
		return json_decode( (string) file_get_contents( __DIR__ . "/data/compare/{$name}.json" ), true );
	}

	/**
	 * @param array<string,mixed>|string $response The Manager's response body.
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

	private function display(): string {
		return $this->application_tester->getDisplay();
	}

	public function test_compare_human_output(): void {
		$this->mock_manager( $this->document( 'human-output' ) );

		$this->assertSame( Command::SUCCESS, $this->compare() );
		$this->assertMatchesSnapshot( $this->display() );
	}

	public function test_compare_human_output_respects_limit(): void {
		$this->mock_manager( $this->document( 'limit' ) );

		$this->compare( [ '--limit' => '2' ] );

		$this->assertStringContainsString( 'Newly failing (5)', $this->display() );
		$this->assertStringContainsString( '... and 3 more. Use --limit=0 to show all.', $this->display() );
		$this->assertStringNotContainsString( 'plugin-4', $this->display() );
	}

	public function test_human_output_links_to_the_report_page(): void {
		$this->mock_manager( [ 'report_url' => 'https://qit.woo.com/?qit_compare=1001.abc~1002.def' ] + $this->document( 'human-output' ) );

		$this->compare();

		$this->assertStringContainsString( 'Report: https://qit.woo.com/?qit_compare=1001.abc~1002.def', $this->display() );
	}

	public function test_canary_human_output_names_every_bucket(): void {
		$this->mock_manager( $this->document( 'canary-every-bucket' ) );

		$this->assertSame( Command::SUCCESS, $this->compare() );

		foreach ( [ 'Ecosystem canary findings', 'Introduced (1)', 'Moved between probes (1)', 'Not looked for on the candidate (1)', 'Nothing in: Resolved', 'Probe state changes (1)', 'could not be judged' ] as $expected ) {
			$this->assertStringContainsString( $expected, $this->display() );
		}
	}

	public function test_the_summary_does_not_call_a_move_a_regression(): void {
		$this->mock_manager( $this->document( 'canary-move-not-regression' ) );

		$this->compare();

		$this->assertStringContainsString( 'Moved between probes (1)', $this->display() );
		$this->assertStringContainsString( 'No failures introduced by the candidate.', $this->display() );
	}

	public function test_the_report_reconciles_a_move_with_the_test_buckets(): void {
		$this->mock_manager( $this->document( 'canary-move-reconciled' ) );

		$this->compare();

		$this->assertStringContainsString( 'Neither is a change in what the build does', $this->display() );
	}

	public function test_the_summary_separates_findings_from_unexplained_failures(): void {
		$this->mock_manager( $this->document( 'canary-findings-and-failures' ) );

		$this->compare();

		$this->assertStringContainsString( 'The candidate introduced 1 canary finding(s) and 1 unexplained failure(s).', $this->display() );
	}

	public function test_the_summary_names_findings_when_that_is_all_there_is(): void {
		$this->mock_manager( $this->document( 'canary-findings-only' ) );

		$this->compare();

		$this->assertStringContainsString( 'The candidate introduced 2 canary finding(s).', $this->display() );
	}

	public function test_runs_that_cannot_be_compared_end_without_a_verdict(): void {
		$document                        = $this->document( 'human-output' );
		$document['guard']['comparable'] = false;
		$document['guard']['warnings']   = [ 'The runs used different canary profiles (hpos against synthetic).' ];
		$this->mock_manager( $document );

		$this->compare();

		$this->assertStringContainsString( 'Warning: The runs used different canary profiles (hpos against synthetic).', $this->display() );
		$this->assertStringContainsString( 'Not comparable: the runs differ in more than the version under test', $this->display() );
		$this->assertStringNotContainsString( 'introduced', $this->display() );
	}

	public function test_json_output_is_the_managers_document(): void {
		$document = $this->document( 'canary-every-bucket' );
		$this->mock_manager( $document );

		$this->assertSame( Command::SUCCESS, $this->compare( [ '--format' => 'json' ] ) );
		$this->assertSame( $document, json_decode( $this->display(), true ) );
	}

	public function test_asks_the_manager_once(): void {
		$this->mock_manager( $this->document( 'human-output' ) );

		$this->compare( [ '--format' => 'json' ] );

		$requests = App::getVar( 'mocked_requests' );
		$this->assertCount( 1, $requests );
		$this->assertStringEndsWith( '/wp-json/cd/v1/compare', $requests[0]['url'] );
		$this->assertSame( 'POST', $requests[0]['method'] );
		$this->assertSame( '1001', $requests[0]['post_body']['a'] );
		$this->assertSame( '1002', $requests[0]['post_body']['b'] );
	}

	public function test_exit_code_follows_the_managers_verdict(): void {
		$this->mock_manager( [ 'has_regressions' => true ] + $this->document( 'human-output' ) );
		$this->assertSame( Command::FAILURE, $this->compare( [ '--exit-code' => true ] ) );

		$this->mock_manager( [ 'has_regressions' => false ] + $this->document( 'human-output' ) );
		$this->assertSame( Command::SUCCESS, $this->compare( [ '--exit-code' => true ] ) );
	}

	public function test_without_exit_code_a_regression_still_exits_zero(): void {
		$this->mock_manager( [ 'has_regressions' => true ] + $this->document( 'human-output' ) );

		$this->assertSame( Command::SUCCESS, $this->compare() );
	}

	public function test_a_comparison_the_manager_refuses_is_an_error(): void {
		$this->mock_manager( [ 'message' => 'Cannot compare a "activation" run against a "woo-api" run.' ] );

		$this->assertSame( Command::INVALID, $this->compare() );
		$this->assertStringContainsString( 'Cannot compare a "activation" run against a "woo-api" run.', $this->display() );
	}

	public function test_an_unknown_run_says_what_to_do_next(): void {
		$this->mock_manager( [ 'message' => 'Test run with ID 1002 does not exist.' ] );

		$this->assertSame( Command::INVALID, $this->compare() );
		$this->assertStringContainsString( 'Could not compare test runs 1001 and 1002: Test run with ID 1002 does not exist.', $this->display() );
		$this->assertStringContainsString( 'qit list-tests', $this->display() );
	}

	public function test_a_network_error_is_passed_through(): void {
		$this->mock_manager( 'exception: Could not resolve host: qit.woo.com' );

		$this->assertSame( Command::INVALID, $this->compare() );
		$this->assertStringContainsString( 'Could not resolve host: qit.woo.com', $this->display() );
	}

	public function test_a_manager_without_the_endpoint_is_an_error_not_a_local_comparison(): void {
		$this->mock_manager( [
			'code'    => 'rest_no_route',
			'message' => 'No route was found matching the URL and request method.',
		] );

		$this->assertSame( Command::INVALID, $this->compare() );
		$this->assertStringContainsString( 'This QIT Manager cannot compare test runs yet.', $this->display() );
		$this->assertCount( 1, App::getVar( 'mocked_requests' ) );
	}

	public function test_a_document_in_a_newer_format_asks_for_an_update(): void {
		$this->mock_manager( [ 'schema' => 2 ] + $this->document( 'human-output' ) );

		$this->assertSame( Command::INVALID, $this->compare() );
		$this->assertStringContainsString( 'format version 2', $this->display() );
		$this->assertStringContainsString( 'Update the QIT CLI', $this->display() );
	}

	public function test_compare_rejects_comparing_a_run_against_itself(): void {
		$this->assertSame( Command::INVALID, $this->compare( [ 'run_b' => '1001' ] ) );
		$this->assertStringContainsString( 'against itself', $this->display() );
		$this->assertCount( 0, App::getVar( 'mocked_requests' ) );
	}

	public function test_compare_rejects_an_unknown_format(): void {
		$this->assertSame( Command::INVALID, $this->compare( [ '--format' => 'yaml' ] ) );
		$this->assertStringContainsString( 'Invalid --format', $this->display() );
	}
}
