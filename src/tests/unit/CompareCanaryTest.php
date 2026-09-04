<?php

use QIT_CLI\App;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

/**
 * The two domain rules `qit compare` applies to runs of the WooCommerce ecosystem
 * canary package, which a generic annotation diff gets wrong:
 *
 * 1. A finding that changed probes moved. It is not a regression, and the
 *    baseline's copy of it is not a fix.
 * 2. A finding missing from a probe that did not run to completion was never
 *    looked for, so its absence is not a fix either.
 *
 * Both shipped once already in the prototype with the bug in them, which is why
 * they are tested rather than only implemented.
 */
class CompareCanaryTest extends \QIT_CLI_Tests\QITTestCase {
	/** @var \Symfony\Component\Console\Tester\ApplicationTester */
	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
	}

	/**
	 * One probe result, as the canary package publishes it: a CTRF test carrying a
	 * `canary-probe-state` annotation and one `canary-finding` annotation per
	 * finding, each a JSON object with the two identities the rules read.
	 *
	 * @param array<int,array<string,string>> $findings
	 *
	 * @return array<string,mixed>
	 */
	private function probe( string $probe_id, string $state, array $findings = [] ): array {
		$annotations = [
			[ 'type' => 'probe-id', 'description' => $probe_id ],
			[ 'type' => 'canary-probe-state', 'description' => $state ],
		];

		foreach ( $findings as $finding ) {
			// The two identities are built here the way the package builds them, so a
			// fixture cannot accidentally agree with the comparison on a shape neither
			// of them would see in a real run.
			$annotations[] = [
				'type'        => 'canary-finding',
				'description' => (string) json_encode( [
					'key'       => sprintf( '%s:%s:%s', $finding['type'], $probe_id, $finding['signature'] ),
					'signature' => sprintf( '%s:%s', $finding['type'], $finding['signature'] ),
					'type'      => $finding['type'],
					'surface'   => $finding['surface'],
					'profile'   => $finding['profile'],
					'fixtures'  => [],
				] ),
			];
		}

		return [
			'name'     => $probe_id . ' - explores something',
			'status'   => empty( $findings ) ? 'passed' : 'failed',
			'duration' => 1000,
			'extra'    => [ 'annotations' => $annotations ],
		];
	}

	/**
	 * @param array<string,string> $overrides
	 *
	 * @return array<string,string>
	 */
	private function finding( string $type, string $signature, array $overrides = [] ): array {
		return array_merge( [
			'type'      => $type,
			'signature' => $signature,
			'surface'   => 'classic-checkout',
			'profile'   => 'synthetic',
		], $overrides );
	}

	/**
	 * @param array<int,array<string,mixed>> $probes
	 * @param array<string,mixed>            $overrides
	 *
	 * @return array<string,mixed>
	 */
	private function make_run( int $id, array $probes, array $overrides = [] ): array {
		$failed = count( array_filter( $probes, function ( $probe ) {
			return $probe['status'] === 'failed';
		} ) );

		$run = [
			'test_run_id'              => $id,
			'test_type'                => 'e2e',
			'wordpress_version'        => '6.7',
			'woocommerce_version'      => '11.0.0-rc.1',
			'php_version'              => '8.2',
			'extension_set'            => '',
			'version'                  => '1.0.0',
			'status'                   => $failed > 0 ? 'failed' : 'success',
			'woo_extension'            => [ 'name' => 'WooCommerce' ],
			'test_results_manager_url' => sprintf( 'https://qit.woo.com/results/%d.abc', $id ),
			'created_at'               => '2025-01-15 10:30:00',
			'update_complete'          => true,
			'test_result_json'         => '',
			'ctrf_json'                => (string) json_encode( [
				'reportFormat' => 'CTRF',
				'results'      => [
					'tool'    => [ 'name' => 'playwright' ],
					'summary' => [
						'tests'   => count( $probes ),
						'passed'  => count( $probes ) - $failed,
						'failed'  => $failed,
						'pending' => 0,
						'skipped' => 0,
						'other'   => 0,
					],
					'tests'   => $probes,
				],
			] ),
		];

		return array_merge( $run, $overrides );
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
			(string) json_encode( $keyed )
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function run_compare_json(): array {
		$this->application_tester->run( [
			'command'  => 'compare',
			'run_a'    => '1001',
			'run_b'    => '1002',
			'--format' => 'json',
		], [ 'capture_stderr_separately' => true ] );

		$decoded = json_decode( $this->application_tester->getDisplay(), true );

		$this->assertIsArray( $decoded, 'Output must be valid JSON. Got: ' . $this->application_tester->getDisplay() );

		return $decoded;
	}

	/**
	 * The motivating case, and the shape of the reproduction in QIT-1074: a
	 * candidate build that introduced findings the baseline did not have.
	 */
	public function test_canary_reports_what_the_candidate_introduced(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete' ),
			], [ 'woocommerce_version' => '10.9.4' ] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error: Call to a member function get_country() @ class-wc-customer.php' ),
					$this->finding( 'http-error', '500 /?wc-ajax=checkout' ),
				] ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 2, $result['canary']['totals']['introduced'] );
		$this->assertSame( 0, $result['canary']['totals']['resolved'] );
		$this->assertSame( 0, $result['canary']['totals']['moved'] );
		$this->assertSame( 0, $result['canary']['totals']['unverified'] );
		$this->assertSame( [], $result['canary']['warnings'] );
	}

	/**
	 * The other half of the reproduction: the fix. Every baseline finding has to
	 * come back as resolved, which is the proof that the fingerprints survived a
	 * version bump - had normalisation leaked a path or a line number, the same
	 * findings would arrive as introduced and resolved at once.
	 */
	public function test_canary_reports_resolved_findings_against_a_completed_probe(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-customer.php' ),
					$this->finding( 'monetary-drift', 'order total' ),
				] ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete' ),
			], [ 'woocommerce_version' => '11.0.0-rc.3' ] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 2, $result['canary']['totals']['resolved'] );
		$this->assertSame( 0, $result['canary']['totals']['introduced'] );
		$this->assertSame( 0, $result['canary']['totals']['unverified'] );
	}

	/**
	 * Rule 1. A fatal raised by a loopback request or a scheduled action lands
	 * under whichever probe happened to be running. Diffed per test that reads as a
	 * regression on one probe and a fix on the other, and both are wrong.
	 */
	public function test_a_finding_that_changed_probes_is_moved_rather_than_introduced_and_resolved(): void {
		$fatal = $this->finding( 'fatal', 'Uncaught Error: scheduled action failed @ class-wc-queue.php' );

		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete' ),
				$this->probe( 'woo-canary.pricing.drift', 'complete', [ $fatal ] ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['introduced'], 'A relocated finding is not a regression' );
		$this->assertSame( 0, $result['canary']['totals']['resolved'], 'The baseline copy of a relocated finding is not a fix' );
		$this->assertSame( 1, $result['canary']['totals']['moved'] );

		$moved = $result['canary']['findings']['moved'][0];

		$this->assertSame( 'fatal:Uncaught Error: scheduled action failed @ class-wc-queue.php', $moved['signature'] );
		$this->assertSame( [ 'woo-canary.checkout.custom-fields' ], array_column( $moved['from'], 'probe' ) );
		$this->assertSame( [ 'woo-canary.pricing.drift' ], array_column( $moved['to'], 'probe' ) );
	}

	/**
	 * A signature the baseline still records under its original probe has not moved
	 * anywhere. It also happened somewhere new, and calling that a move would hide
	 * a finding the candidate introduced.
	 */
	public function test_a_signature_that_stayed_put_and_also_appeared_elsewhere_is_introduced(): void {
		$fatal = $this->finding( 'fatal', 'Uncaught Error @ class-wc-queue.php' );

		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete', [ $fatal ] ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['moved'] );
		$this->assertSame( 1, $result['canary']['totals']['pre_existing'] );
		$this->assertSame(
			[ 'fatal:woo-canary.pricing.drift:Uncaught Error @ class-wc-queue.php' ],
			array_column( $result['canary']['findings']['introduced'], 'key' )
		);
	}

	/**
	 * Rule 2, and the failure mode the whole comparison exists to avoid: a
	 * candidate whose probe stops earlier than the baseline's never looked for the
	 * findings that come after the stopping point, so reporting them as fixed is an
	 * improvement that did not happen.
	 */
	public function test_a_truncated_probe_does_not_turn_baseline_findings_into_fixes(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'monetary-drift', 'order total' ),
				] ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'truncated', [
					$this->finding( 'workflow-failure', 'the Store API refused adding to the cart' ),
				] ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['resolved'], 'A probe that stopped early did not fix anything' );
		$this->assertSame( 1, $result['canary']['totals']['unverified'] );
		$this->assertSame( 1, $result['canary']['totals']['introduced'] );

		$unverified = $result['canary']['findings']['unverified'][0];

		$this->assertSame( 'monetary-drift:woo-canary.checkout.custom-fields:order total', $unverified['key'] );
		$this->assertSame( 'truncated', $unverified['probe_state'] );
		$this->assertStringContainsString( 'never looked for', $unverified['reason'] );

		$this->assertCount( 1, $result['canary']['warnings'] );
		$this->assertStringContainsString( 'could not be judged', $result['canary']['warnings'][0] );
	}

	/**
	 * A probe that broke publishes nothing, so the same caveat applies more
	 * strongly: its silence says nothing about the product.
	 */
	public function test_an_incomplete_probe_does_not_turn_baseline_findings_into_fixes(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-cart.php' ),
				] ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'incomplete' ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['resolved'] );
		$this->assertSame( 'incomplete', $result['canary']['findings']['unverified'][0]['probe_state'] );
		$this->assertSame(
			[ [ 'probe' => 'woo-canary.checkout.custom-fields', 'a' => 'complete', 'b' => 'incomplete' ] ],
			$result['canary']['probes']['changed']
		);
	}

	/**
	 * A probe the candidate does not have at all cannot have looked either. The
	 * absence has to read the same way as a probe that broke.
	 */
	public function test_a_probe_missing_from_the_candidate_does_not_resolve_anything(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-cart.php' ),
				] ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['resolved'] );
		$this->assertSame( 'unknown', $result['canary']['findings']['unverified'][0]['probe_state'] );
	}

	/**
	 * A finding on both sides is how WooCommerce already behaves, and is the bulk
	 * of a healthy comparison rather than something to report as a change.
	 */
	public function test_a_finding_present_in_both_runs_is_pre_existing(): void {
		$fatal = $this->finding( 'fatal', 'Uncaught Error @ class-wc-cart.php' );

		$this->mock_runs( [
			$this->make_run( 1001, [ $this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ) ] ),
			$this->make_run( 1002, [ $this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ) ] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 1, $result['canary']['totals']['pre_existing'] );
		$this->assertSame( 0, $result['canary']['totals']['introduced'] );
		$this->assertSame( 0, $result['canary']['totals']['resolved'] );
	}

	/**
	 * An introduced finding on a probe the baseline never finished may be
	 * pre-existing. It stays reported - under-reporting a regression is the worse
	 * error for an advisory package - but says what the baseline probe did.
	 */
	public function test_an_introduced_finding_says_when_the_baseline_probe_did_not_finish(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'truncated' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-cart.php' ),
				] ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 1, $result['canary']['totals']['introduced'] );
		$this->assertSame( 'truncated', $result['canary']['findings']['introduced'][0]['baseline_probe_state'] );
		$this->assertStringContainsString( 'may be pre-existing', $result['canary']['warnings'][0] );
	}

	/**
	 * The canary annotations are reported by the rules that understand them and
	 * nowhere else: left in the generic diff, one moved finding would arrive there
	 * as an annotation added and another removed, which is the bug in the first
	 * place.
	 */
	public function test_canary_annotations_are_left_out_of_the_generic_annotation_diff(): void {
		$fatal = $this->finding( 'fatal', 'Uncaught Error @ class-wc-queue.php' );

		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'truncated' ),
				$this->probe( 'woo-canary.pricing.drift', 'complete', [ $fatal ] ),
			] ),
		] );

		$result = $this->run_compare_json();

		$types = array_merge(
			array_column( $result['annotations']['added'], 'type' ),
			array_column( $result['annotations']['removed'], 'type' )
		);

		$this->assertNotContains( 'canary-finding', $types );
		$this->assertNotContains( 'canary-probe-state', $types );
		$this->assertSame( 1, $result['canary']['totals']['moved'] );
	}

	/**
	 * A finding annotation that cannot be read is counted rather than dropped: a
	 * finding that silently disappears from one side reads as an improvement.
	 */
	public function test_an_unreadable_finding_annotation_is_reported_rather_than_dropped(): void {
		$probe             = $this->probe( 'woo-canary.checkout.custom-fields', 'complete' );
		$probe['extra']['annotations'][] = [ 'type' => 'canary-finding', 'description' => 'not json at all' ];

		$this->mock_runs( [
			$this->make_run( 1001, [ $probe ] ),
			$this->make_run( 1002, [ $this->probe( 'woo-canary.checkout.custom-fields', 'complete' ) ] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['resolved'] );
		$this->assertStringContainsString( 'could not be read', $result['canary']['warnings'][0] );
		$this->assertStringContainsString( 'run A', $result['canary']['warnings'][0] );
	}

	/**
	 * Runs that carry no canary data get no canary section at all, so a consumer
	 * can tell "this canary run found nothing" from "these are not canary runs".
	 */
	public function test_a_run_without_canary_data_gets_no_canary_section(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [ [ 'name' => 'checkout', 'status' => 'passed', 'duration' => 1000 ] ] ),
			$this->make_run( 1002, [ [ 'name' => 'checkout', 'status' => 'passed', 'duration' => 1000 ] ] ),
		] );

		$result = $this->run_compare_json();

		$this->assertArrayNotHasKey( 'canary', $result );
	}

	/**
	 * The human output carries the same buckets as the JSON, since a reader
	 * skimming a terminal is the likelier consumer of an advisory result.
	 */
	public function test_canary_human_output_names_every_bucket(): void {
		$moved = $this->finding( 'fatal', 'Uncaught Error @ class-wc-queue.php' );

		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$moved,
					$this->finding( 'monetary-drift', 'order total' ),
				] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'truncated', [
					$this->finding( 'workflow-failure', 'the Store API refused adding to the cart' ),
				] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete', [ $moved ] ),
			] ),
		] );

		$exit_code = $this->application_tester->run( [
			'command' => 'compare',
			'run_a'   => '1001',
			'run_b'   => '1002',
		], [ 'capture_stderr_separately' => true ] );

		$display = $this->application_tester->getDisplay();

		$this->assertSame( Command::SUCCESS, $exit_code );
		$this->assertStringContainsString( 'Ecosystem canary findings', $display );
		$this->assertStringContainsString( 'Introduced (1)', $display );
		$this->assertStringContainsString( 'Moved between probes (1)', $display );
		$this->assertStringContainsString( 'Not looked for in run B (1)', $display );
		$this->assertStringContainsString( 'Resolved (0)', $display );
		$this->assertStringContainsString( 'Probe state changes (1)', $display );
		$this->assertStringContainsString( 'could not be judged', $display );
	}

	/**
	 * A finding is not tied to the probe that recorded it, which is the premise of
	 * the moved bucket. So a fatal the candidate's own probe did not see may simply
	 * have landed during another probe's window, and a probe that did not finish
	 * published nothing. Gating on the recording probe alone would call that a fix.
	 */
	public function test_another_unfinished_probe_blocks_a_resolution(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-queue.php' ),
				] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete' ),
				$this->probe( 'woo-canary.pricing.drift', 'truncated' ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 0, $result['canary']['totals']['resolved'], 'Another probe could have recorded it and did not finish' );
		$this->assertSame( 1, $result['canary']['totals']['unverified'] );

		$unverified = $result['canary']['findings']['unverified'][0];

		$this->assertSame( 'complete', $unverified['probe_state'], 'The recording probe itself did finish' );
		$this->assertStringContainsString( 'woo-canary.pricing.drift', $unverified['reason'] );
	}

	/**
	 * The healthy case is unaffected by that stricter rule: every probe completed,
	 * so an absence is an absence.
	 */
	public function test_a_fully_completed_candidate_run_still_resolves(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-queue.php' ),
				] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete' ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 1, $result['canary']['totals']['resolved'] );
		$this->assertSame( 0, $result['canary']['totals']['unverified'] );
	}

	/**
	 * A CTRF test key is the probe's filename and its human-readable description,
	 * both of which are prose. The published probe id is the identity, so rewording
	 * a description must not make a completed probe look absent.
	 */
	public function test_a_reworded_probe_description_does_not_lose_the_probe(): void {
		$candidate         = $this->probe( 'woo-canary.checkout.custom-fields', 'complete' );
		$candidate['name'] = 'woo-canary.checkout.custom-fields - now described differently';

		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-cart.php' ),
				] ),
			] ),
			$this->make_run( 1002, [ $candidate ] ),
		] );

		$result = $this->run_compare_json();

		$this->assertSame( 1, $result['canary']['totals']['resolved'], 'The probe is identified by its id, not its description' );
		$this->assertSame( [], $result['canary']['probes']['changed'] );
	}

	/**
	 * A probe fails when it records anything, so a finding that only changed probes
	 * flips two results: one probe passes, another fails. The comparison has
	 * already said that is neither a regression nor a fix, so it must not reach the
	 * exit code as one.
	 */
	public function test_exit_code_does_not_gate_on_a_finding_that_only_moved(): void {
		$fatal = $this->finding( 'fatal', 'Uncaught Error @ class-wc-queue.php' );

		$this->mock_runs( [
			$this->make_run( 1001, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [ $fatal ] ),
				$this->probe( 'woo-canary.pricing.drift', 'complete' ),
			] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete' ),
				$this->probe( 'woo-canary.pricing.drift', 'complete', [ $fatal ] ),
			] ),
		] );

		$exit_code = $this->application_tester->run( [
			'command'     => 'compare',
			'run_a'       => '1001',
			'run_b'       => '1002',
			'--exit-code' => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::SUCCESS, $exit_code );
	}

	/**
	 * A genuinely new finding still gates, so the reconciliation above cannot be
	 * used to make a regression disappear.
	 */
	public function test_exit_code_still_gates_on_an_introduced_finding(): void {
		$this->mock_runs( [
			$this->make_run( 1001, [ $this->probe( 'woo-canary.checkout.custom-fields', 'complete' ) ] ),
			$this->make_run( 1002, [
				$this->probe( 'woo-canary.checkout.custom-fields', 'complete', [
					$this->finding( 'fatal', 'Uncaught Error @ class-wc-cart.php' ),
				] ),
			] ),
		] );

		$exit_code = $this->application_tester->run( [
			'command'     => 'compare',
			'run_a'       => '1001',
			'run_b'       => '1002',
			'--exit-code' => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::FAILURE, $exit_code );
	}

	/**
	 * A probe that failed while recording nothing failed for a reason that has
	 * nothing to do with what it observed. That is the harness, and it gates.
	 */
	public function test_exit_code_gates_on_a_probe_that_failed_without_recording_anything(): void {
		$broken           = $this->probe( 'woo-canary.checkout.custom-fields', 'incomplete' );
		$broken['status'] = 'failed';

		$this->mock_runs( [
			$this->make_run( 1001, [ $this->probe( 'woo-canary.checkout.custom-fields', 'complete' ) ] ),
			$this->make_run( 1002, [ $broken ] ),
		] );

		$exit_code = $this->application_tester->run( [
			'command'     => 'compare',
			'run_a'       => '1001',
			'run_b'       => '1002',
			'--exit-code' => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertSame( Command::FAILURE, $exit_code );
	}
}
