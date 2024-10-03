<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class CompatibilityTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;
	use ScaffoldHelpers;

	public function test_sut_and_activate_additional() {
		$output = qit( [
				'run:e2e',
				'qit-test-plugin',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:activate'
			]
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_sut_and_bootstrap_additional() {
		// Upload Woo Test Tag.
		qit( [
				'tag:upload',
				'woocommerce:self-test-bootstrap-additional',
				$this->scaffold_test(),
			]
		);

		// Run qit-test-plugin, bootstrapping Woo.
		$output = qit( [
				'run:e2e',
				'qit-test-plugin',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:bootstrap:self-test-bootstrap-additional',
			]
		);

		// Cleanup.
		qit( [ 'tag:delete', 'woocommerce:self-test-bootstrap-additional' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_sut_and_test_additional() {
		// Upload Woo Test Tag.
		qit( [
				'tag:upload',
				'woocommerce:self-test-sut-and-test-additional',
				$this->scaffold_test(),
			]
		);

		// Run qit-test-plugin, bootstrapping Woo.
		$output = qit( [
				'run:e2e',
				'qit-test-plugin',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:test:self-test-sut-and-test-additional',
			]
		);

		// Cleanup.
		qit( [ 'tag:delete', 'woocommerce:self-test-sut-and-test-additional' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_multiple_tags_and_multiple_plugins_with_multiple_tags() {
		qit( [
			'tag:upload',
			'qit-test-plugin:self-test-multiple-test-tags',
			$this->scaffold_test(),
		] );

		qit( [
			'tag:upload',
			'qit-test-plugin:self-test-multiple-test-tags-another',
			$this->scaffold_test( 'another-tag' ),
		] );

		qit( [
			'tag:upload',
			'woocommerce:self-test-multiple-test-tags',
			$this->scaffold_test(),
		] );

		qit( [
			'tag:upload',
			'woocommerce:self-test-multiple-test-tags-another',
			$this->scaffold_test( 'another-tag' ),
		] );

		$output = qit( [
			'run:e2e',
			'qit-test-plugin',
			'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
			'--plugin',
			'woocommerce:test:self-test-multiple-test-tags,self-test-multiple-test-tags-another',
		] );

		qit( [ 'tag:delete', 'qit-test-plugin:self-test-multiple-test-tags' ] );
		qit( [ 'tag:delete', 'qit-test-plugin:self-test-multiple-test-tags-another' ] );
		qit( [ 'tag:delete', 'woocommerce:self-test-multiple-test-tags' ] );
		qit( [ 'tag:delete', 'woocommerce:self-test-multiple-test-tags-another' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}
}