<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class TagsTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use SnapshotHelpers;

	public function test_runs_scaffolded_e2e() {
		// Make sure we start from a clean state.
		try {
			qit( [ 'tag:delete', 'qit-test-plugin:self-test-scaffolded' ] );
		} catch ( \Exception $e ) {
			// No-op.
		}

		$output = qit( [
				'tag:upload',
				'qit-test-plugin:self-test-scaffolded',
				$this->scaffold_test(),
			]
		);
		$this->assertMatchesNormalizedSnapshot( $output );

		// All test tags, which should include this one.
		$output = qit( [ 'tag:list', ] );
		$this->assertStringContainsString( 'self-test-scaffolded', $output );

		// Just tags of "qit-test-plugin", which should include this one.
		$output = qit( [ 'tag:list', 'qit-test-plugin', ]
		);
		$this->assertStringContainsString( 'self-test-scaffolded', $output );

		// Just tags of "qit-test-plugin-birthdays", which should not include.
		$output = qit( [ 'tag:list', 'qit-test-plugin-birthdays', ] );
		$this->assertStringNotContainsString( 'self-test-scaffolded', $output );

		// Delete the tag and re-run the fetch.
		qit( [ 'tag:delete', 'qit-test-plugin:self-test-scaffolded' ] );

		// Assert the tag is no longer found in the overall list.
		$output = qit( [ 'tag:list', ] );
		$this->assertStringNotContainsString( 'self-test-scaffolded', $output );

		// Assert the tag is no longer found when querying that single extension.
		$output = qit( [ 'tag:list', 'qit-test-plugin', ] );
		$this->assertStringNotContainsString( 'self-test-scaffolded', $output );
	}
}