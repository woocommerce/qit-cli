<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class TagsTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use SnapshotHelpers;

	public function test_runs_scaffolded_e2e() {
		// Make sure we start from a clean state.
		try {
			qit( [ 'tag:delete', 'automatewoo:self-test-scaffolded' ] );
		} catch ( \Exception $e ) {
			// No-op.
		}

		$output = qit( [
				'tag:upload',
				'automatewoo:self-test-scaffolded',
				$this->scaffold_test(),
			]
		);
		$this->assertMatchesNormalizedSnapshot( $output );

		// All test tags, which should include this one.
		$output = qit( [ 'tag:list', ] );
		$this->assertStringContainsString( 'self-test-scaffolded', $output );

		// Just tags of "automatewoo", which should include this one.
		$output = qit( [ 'tag:list', 'automatewoo', ]
		);
		$this->assertStringContainsString( 'self-test-scaffolded', $output );

		// Just tags of "automatewoo-birthdays", which should not include.
		$output = qit( [ 'tag:list', 'automatewoo-birthdays', ] );
		$this->assertStringNotContainsString( 'self-test-scaffolded', $output );

		// Delete the tag and re-run the fetch.
		qit( [ 'tag:delete', 'automatewoo:self-test-scaffolded' ] );

		// Assert the tag is no longer found in the overall list.
		$output = qit( [ 'tag:list', ] );
		$this->assertStringNotContainsString( 'self-test-scaffolded', $output );

		// Assert the tag is no longer found when querying that single extension.
		$output = qit( [ 'tag:list', 'automatewoo', ] );
		$this->assertStringNotContainsString( 'self-test-scaffolded', $output );
	}
}