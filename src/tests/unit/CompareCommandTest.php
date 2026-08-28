<?php

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

class CompareCommandTest extends \QIT_CLI_Tests\QITTestCase {
	use MatchesSnapshots;

	/** @var \Symfony\Component\Console\Tester\ApplicationTester */
	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
	}

	/**
	 * An activation run, the motivating case: which plugins fail to activate against
	 * a given WooCommerce version.
	 *
	 * @param array<int,array<string,mixed>> $tests
	 * @param array<string,mixed>            $overrides
	 *
	 * @return array<string,mixed>
	 */
	private function make_run( int $id, array $tests, array $overrides = [] ): array {
		$summary = [
			'tests'   => count( $tests ),
			'passed'  => count( array_filter( $tests, function ( $t ) {
				return $t['status'] === 'passed';
			} ) ),
			'failed'  => count( array_filter( $tests, function ( $t ) {
				return $t['status'] === 'failed';
			} ) ),
			'pending' => 0,
			'skipped' => count( array_filter( $tests, function ( $t ) {
				return $t['status'] === 'skipped';
			} ) ),
			'other'   => 0,
			'start'   => 1706644023,
			'stop'    => 1706644043,
		];

		$run = [
			'test_run_id'              => $id,
			'test_type'                => 'activation',
			'wordpress_version'        => '6.7',
			'woocommerce_version'      => '11.0.1',
			'php_version'              => '8.2',
			'extension_set'            => '',
			'version'                  => '1.0.0',
			'status'                   => $summary['failed'] > 0 ? 'failed' : 'success',
			'woo_extension'            => [ 'name' => 'My WooCommerce Plugin' ],
			'test_results_manager_url' => sprintf( 'https://qit.woo.com/results/%d.abc', $id ),
			'created_at'               => '2025-01-15 10:30:00',
			'update_complete'          => true,
			'test_result_json'         => '',
			'ctrf_json'                => json_encode( [
				'reportFormat' => 'CTRF',
				'specVersion'  => '0.1.0',
				'results'      => [
					'tool'    => [ 'name' => 'qit-activation' ],
					'summary' => $summary,
					'tests'   => $tests,
					'extra'   => [
						'qitPackageMetadata' => [
							'packages' => [ [ 'packageId' => 'woocommerce/activation', 'version' => '1.2.0' ] ],
						],
					],
				],
			] ),
		];

		return array_merge( $run, $overrides );
	}

	/**
	 * @param array<string,mixed> $extra
	 *
	 * @return array<string,mixed>
	 */
	private function test_case( string $name, string $status, array $extra = [] ): array {
		return array_merge( [
			'name'     => $name,
			'status'   => $status,
			'duration' => 1000,
		], $extra );
	}

	/**
	 * @param array<int,array<string,mixed>> $runs
	 */
	private function mock_runs( array $runs ): void {
		$keyed = [];
		foreach ( $runs as $run ) {
			$keyed[ (string) $run['test_run_id'] ] = $run;
		}

		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-multiple' ),
			json_encode( $keyed )
		);
	}

	/**
	 * @param array<string,mixed> $args
	 *
	 * @return array{0:int,1:array<string,mixed>}
	 */
	private function run_compare_json( array $args = [] ): array {
		$exit_code = $this->application_tester->run( array_merge( [
			'command'  => 'compare',
			'run_a'    => '1001',
			'run_b'    => '1002',
			'--format' => 'json',
		], $args ), [ 'capture_stderr_separately' => true ] );

		$decoded = json_decode( $this->application_tester->getDisplay(), true );
		$this->assertIsArray( $decoded, 'Output must be valid JSON. Got: ' . $this->application_tester->getDisplay() );

		return [ $exit_code, $decoded ];
	}

	/**
	 * The motivating case: a WooCommerce bump makes one plugin newly fail to activate,
	 * and fixes another.
	 */
	public function test_compare_buckets_introduced_resolved_and_still_failing(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-b', 'failed' ),
				$this->test_case( 'plugin-c', 'failed' ),
				$this->test_case( 'plugin-d', 'passed' ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'failed' ),
				$this->test_case( 'plugin-b', 'passed' ),
				$this->test_case( 'plugin-c', 'failed' ),
				$this->test_case( 'plugin-d', 'passed' ),
			], [ 'woocommerce_version' => '11.1.0' ] ),
		] );

		list( $exit_code, $result ) = $this->run_compare_json();

		$this->assertSame( Command::FAILURE, $exit_code, 'An introduced failure must exit 1' );

		$this->assertSame( [ 'plugin-a' ], array_column( $result['tests']['introduced'], 'key' ) );
		$this->assertSame( [ 'plugin-b' ], array_column( $result['tests']['resolved'], 'key' ) );
		$this->assertSame( [ 'plugin-c' ], array_column( $result['tests']['still_failing'], 'key' ) );
		$this->assertSame( 1, $result['totals']['unchanged'] );
		$this->assertSame( [], $result['tests']['added'] );
		$this->assertSame( [], $result['tests']['removed'] );

		$this->assertSame( 'passed', $result['tests']['introduced'][0]['status']['a'] );
		$this->assertSame( 'failed', $result['tests']['introduced'][0]['status']['b'] );
	}

	/**
	 * A run with no introduced failures exits 0, so the command is usable as a CI gate.
	 */
	public function test_compare_without_regressions_exits_zero(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'plugin-a', 'failed' ),
				$this->test_case( 'plugin-b', 'passed' ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-b', 'passed' ),
			] ),
		] );

		list( $exit_code, $result ) = $this->run_compare_json();

		$this->assertSame( Command::SUCCESS, $exit_code );
		$this->assertSame( 0, $result['totals']['introduced'] );
		$this->assertSame( 1, $result['totals']['resolved'] );
	}

	/**
	 * Tests that only exist on one side are reported separately, so a failing test
	 * that simply disappeared does not read as "resolved".
	 */
	public function test_compare_reports_added_and_removed_tests_separately(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-gone', 'failed' ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-new', 'passed' ),
			] ),
		] );

		list( $exit_code, $result ) = $this->run_compare_json();

		$this->assertSame( Command::SUCCESS, $exit_code );
		$this->assertSame( [ 'plugin-new' ], array_column( $result['tests']['added'], 'key' ) );
		$this->assertSame( [ 'plugin-gone' ], array_column( $result['tests']['removed'], 'key' ) );
		$this->assertSame( [], $result['tests']['resolved'], 'A test that disappeared is not a resolved failure' );
	}

	/**
	 * A brand new test that fails is still a failure introduced by run B.
	 */
	public function test_compare_treats_a_new_failing_test_as_a_regression(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-new', 'failed' ),
			] ),
		] );

		list( $exit_code, $result ) = $this->run_compare_json();

		$this->assertSame( Command::FAILURE, $exit_code );
		$this->assertSame( 0, $result['totals']['introduced'] );
		$this->assertSame( [ 'plugin-new' ], array_column( $result['tests']['added'], 'key' ) );
	}

	/**
	 * Annotations are diffed per test, which is how packages get their structured
	 * data compared without this command knowing what it means.
	 */
	public function test_compare_diffs_annotations(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'plugin-a', 'failed', [
					'extra' => [
						'annotations' => [
							[ 'type' => 'contract-id', 'description' => 'woocommerce/orders#create' ],
							[ 'type' => 'finding', 'description' => 'deprecated-hook' ],
						],
					],
				] ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'failed', [
					'extra' => [
						'annotations' => [
							[ 'type' => 'contract-id', 'description' => 'woocommerce/orders#create' ],
							[ 'type' => 'finding', 'description' => 'missing-nonce' ],
						],
					],
				] ),
			] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertSame(
			[ [ 'test' => 'plugin-a', 'type' => 'finding', 'description' => 'missing-nonce' ] ],
			$result['annotations']['added']
		);
		$this->assertSame(
			[ [ 'test' => 'plugin-a', 'type' => 'finding', 'description' => 'deprecated-hook' ] ],
			$result['annotations']['removed']
		);
	}

	/**
	 * One differing dimension is the variable under test, so the runs stay comparable.
	 */
	public function test_guard_accepts_a_single_differing_dimension(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] ),
			$this->make_run( 1002, [ $this->test_case( 'plugin-a', 'passed' ) ], [ 'woocommerce_version' => '11.1.0' ] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertTrue( $result['guard']['comparable'] );
		$this->assertSame( [ 'woocommerce_version' ], array_column( $result['guard']['differences'], 'field' ) );
		$this->assertSame( [], $result['guard']['warnings'] );
	}

	/**
	 * Two or more differing dimensions cannot attribute a result change to any of
	 * them, so the comparison is flagged rather than silently trusted.
	 */
	public function test_guard_flags_runs_that_differ_in_more_than_one_dimension(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] ),
			$this->make_run( 1002, [ $this->test_case( 'plugin-a', 'passed' ) ], [
				'woocommerce_version' => '11.1.0',
				'php_version'         => '8.3',
			] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertFalse( $result['guard']['comparable'] );
		$this->assertSame(
			[ 'woocommerce_version', 'php_version' ],
			array_column( $result['guard']['differences'], 'field' )
		);
		$this->assertCount( 1, $result['guard']['warnings'] );
		$this->assertStringContainsString( 'differ in 2 dimensions', $result['guard']['warnings'][0] );
	}

	/**
	 * Different test types are not the same population of tests.
	 */
	public function test_guard_flags_different_test_types(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] ),
			$this->make_run( 1002, [ $this->test_case( 'plugin-a', 'passed' ) ], [ 'test_type' => 'woo-api' ] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertFalse( $result['guard']['comparable'] );
		$this->assertStringContainsString( 'different test types', $result['guard']['warnings'][0] );
	}

	/**
	 * A package version bump is a context difference, read out of qitPackageMetadata.
	 */
	public function test_guard_detects_a_test_package_version_change(): void {
		$run_b            = $this->make_run( 1002, [ $this->test_case( 'plugin-a', 'passed' ) ] );
		$ctrf             = json_decode( $run_b['ctrf_json'], true );
		$ctrf['results']['extra']['qitPackageMetadata']['packages'][0]['version'] = '1.3.0';
		$run_b['ctrf_json'] = json_encode( $ctrf );

		$this->mock_runs( [
			$this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] ),
			$run_b,
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertSame( [ 'test_packages' ], array_column( $result['guard']['differences'], 'field' ) );
		$this->assertSame( 'woocommerce/activation@1.3.0', $result['runs']['b']['context']['test_packages'] );
	}

	/**
	 * Summary counters are reported side by side with a delta.
	 */
	public function test_compare_reports_summary_counts_side_by_side(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-b', 'passed' ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-b', 'failed' ),
			] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertSame( 2, $result['summary']['a']['passed'] );
		$this->assertSame( 1, $result['summary']['b']['passed'] );
		$this->assertSame( -1, $result['summary']['delta']['passed'] );
		$this->assertSame( 1, $result['summary']['delta']['failed'] );
		$this->assertSame( 0, $result['summary']['delta']['tests'] );
	}

	/**
	 * CTRF does not guarantee unique test names, so duplicates must not collapse.
	 */
	public function test_compare_keeps_duplicate_test_names_distinct(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'same-name', 'passed' ),
				$this->test_case( 'same-name', 'passed' ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'same-name', 'passed' ),
				$this->test_case( 'same-name', 'failed' ),
			] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertSame( [ 'same-name #2' ], array_column( $result['tests']['introduced'], 'key' ) );
		$this->assertSame( 1, $result['totals']['unchanged'] );
	}

	/**
	 * A suite qualifies the test name, so two suites can carry the same test name.
	 */
	public function test_compare_keys_tests_by_suite_and_name(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'checkout', 'passed', [ 'suite' => 'cart' ] ),
				$this->test_case( 'checkout', 'passed', [ 'suite' => 'admin' ] ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'checkout', 'failed', [ 'suite' => 'cart' ] ),
				$this->test_case( 'checkout', 'passed', [ 'suite' => 'admin' ] ),
			] ),
		] );

		list( , $result ) = $this->run_compare_json();

		$this->assertSame( [ 'cart :: checkout' ], array_column( $result['tests']['introduced'], 'key' ) );
	}

	/**
	 * A test type that does not report CTRF cannot be compared, and says so.
	 */
	public function test_compare_rejects_runs_without_ctrf_results(): void {
		$run_a = $this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] );
		$run_b = $this->make_run( 1002, [ $this->test_case( 'plugin-a', 'passed' ) ] );

		$run_b['ctrf_json'] = '';

		$this->mock_runs( [ $run_a, $run_b ] );

		$exit_code = $this->application_tester->run( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1002',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'has no CTRF results to compare', $this->application_tester->getDisplay() );
	}

	public function test_compare_rejects_a_missing_run(): void {
		$this->mock_runs( [ $this->make_run( 1001, [ $this->test_case( 'plugin-a', 'passed' ) ] ) ] );

		$exit_code = $this->application_tester->run( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1002',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'Test run 1002 was not found', $this->application_tester->getDisplay() );
	}

	public function test_compare_rejects_comparing_a_run_against_itself(): void {
		$exit_code = $this->application_tester->run( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1001',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'against itself', $this->application_tester->getDisplay() );
	}

	public function test_compare_rejects_an_unknown_format(): void {
		$exit_code = $this->application_tester->run( [
			'command'  => 'compare',
			'run_a'    => '1001',
			'run_b'    => '1002',
			'--format' => 'yaml',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'Invalid --format', $this->application_tester->getDisplay() );
	}

	/**
	 * The human output is the default, and is what most people will read.
	 */
	public function test_compare_human_output(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->test_case( 'plugin-a', 'passed' ),
				$this->test_case( 'plugin-b', 'failed' ),
				$this->test_case( 'plugin-c', 'failed' ),
				$this->test_case( 'plugin-gone', 'passed' ),
			] ),
			$this->make_run( 1002, [
				$this->test_case( 'plugin-a', 'failed', [
					'extra' => [ 'annotations' => [ [ 'type' => 'error', 'description' => 'Fatal on activation' ] ] ],
				] ),
				$this->test_case( 'plugin-b', 'passed' ),
				$this->test_case( 'plugin-c', 'failed' ),
				$this->test_case( 'plugin-new', 'skipped' ),
			], [ 'woocommerce_version' => '11.1.0' ] ),
		] );

		$exit_code = $this->application_tester->run( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1002',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::FAILURE, $exit_code );
		$this->assertMatchesSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * Human output truncates long sections, and says how to see the rest.
	 */
	public function test_compare_human_output_respects_limit(): void {
		$a_tests = [];
		$b_tests = [];

		for ( $i = 1; $i <= 5; $i++ ) {
			$a_tests[] = $this->test_case( 'plugin-' . $i, 'passed' );
			$b_tests[] = $this->test_case( 'plugin-' . $i, 'failed' );
		}

		$this->mock_runs( [
			$this->make_run( 1001, $a_tests ),
			$this->make_run( 1002, $b_tests ),
		] );

		$this->application_tester->run( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1002',
			'--limit' => '2',
		], [ 'capture_stderr_separately' => true ] );

		$display = $this->application_tester->getDisplay();

		$this->assertStringContainsString( 'Introduced failures (5)', $display );
		$this->assertStringContainsString( '... and 3 more. Use --limit=0 to show all.', $display );
		$this->assertStringNotContainsString( 'plugin-4', $display );
	}
}
