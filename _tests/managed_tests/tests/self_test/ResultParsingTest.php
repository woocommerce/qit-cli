<?php

namespace QITE2E\self_test;

use QITE2E\QITE2ETestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ResultParsingTest extends QITE2ETestCase {
	use MatchesSnapshots;

	/*
	 * For each file in __DIR__ . 'result_jsons' directory, assert that the result of validate_and_normalize is equal to the snapshot.
	 */
	public function test_result_parsing() {
		$files = glob( __DIR__ . '/result_jsons/*.json' );
		foreach ( $files as $file ) {
			$this->assertMatchesSnapshot( $this->validate_and_normalize( $file ) );
		}
	}
}