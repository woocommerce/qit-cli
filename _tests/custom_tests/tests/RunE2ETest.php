<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use Spatie\Snapshots\MatchesSnapshots;

class RunE2ETest extends \PHPUnit\Framework\TestCase {
	use MatchesSnapshots;
	use ScaffoldHelpers;

	public function test_fails_if_dependency_unmet() {
		$output = qit( [
			'run:e2e',
			'automatewoo',
			$this->scaffold_test(),
		],
			[],
			1
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesSnapshot( $output );
	}

	public function test_runs_scaffolded_e2e() {
		$output = qit( [
				'run:e2e',
				'automatewoo',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:activate',
			]
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesSnapshot( $output );
	}

	public function test_tag_and_run_test() {
		qit( [
			'tag:upload',
			'automatewoo',
			$this->scaffold_test(),
			'self-test-scaffolded',
		] );

		$output = qit( [
			'run:e2e',
			'automatewoo:test:self-test-scaffolded',
			'--plugin',
			'woocommerce:activate',
		] );

		qit( [ 'tag:delete', 'automatewoo:self-test-scaffolded' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesSnapshot( $output );
	}

	public function test_multiple_tags_and_run_tests() {
		qit( [
			'tag:upload',
			'automatewoo',
			$this->scaffold_test(),
			'self-test-scaffolded',
		] );

		qit( [
			'tag:upload',
			'automatewoo',
			$this->scaffold_test( 'another-tag' ),
			'self-test-scaffolded-another',
		] );

		$output = qit( [
			'run:e2e',
			'automatewoo:test:self-test-scaffolded,self-test-scaffolded-another',
			'--plugin',
			'woocommerce:activate',
		] );

		qit( [ 'tag:delete', 'automatewoo:self-test-scaffolded' ] );
		qit( [ 'tag:delete', 'automatewoo:self-test-scaffolded-another' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesSnapshot( $output );
	}
}