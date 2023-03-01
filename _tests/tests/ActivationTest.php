<?php

namespace QITE2E;

use Spatie\Snapshots\MatchesSnapshots;

class ActivationTest extends QITE2ETestCase {
	use MatchesSnapshots;

	public function test_security_tests() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation.txt' ) );
	}
}