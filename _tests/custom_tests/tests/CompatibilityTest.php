<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use Spatie\Snapshots\MatchesSnapshots;

class CompatibilityTest extends \PHPUnit\Framework\TestCase {
	use MatchesSnapshots;
	use ScaffoldHelpers;

	public function test_sut_and_activate_additional() {
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

	public function test_sut_and_bootstrap_additional() {
		// Upload Woo Test Tag.
		qit( [
				'tag:upload',
				'woocommerce',
				$this->scaffold_test(),
				'self-test-scaffolded',
			]
		);

		// Run AutomateWoo, bootstrapping Woo.
		$output = qit( [
				'run:e2e',
				'automatewoo',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:bootstrap:self-test-scaffolded',
			]
		);

		// Cleanup.
		qit( [ 'tag:delete', 'woocommerce:self-test-scaffolded' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesSnapshot( $output );
	}

	public function test_sut_and_test_additional() {
		// Upload Woo Test Tag.
		qit( [
				'tag:upload',
				'woocommerce',
				$this->scaffold_test(),
				'self-test-scaffolded',
			]
		);

		// Run AutomateWoo, bootstrapping Woo.
		$output = qit( [
				'run:e2e',
				'automatewoo',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:test:self-test-scaffolded',
			]
		);

		// Cleanup.
		qit( [ 'tag:delete', 'woocommerce:self-test-scaffolded' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesSnapshot( $output );
	}
}