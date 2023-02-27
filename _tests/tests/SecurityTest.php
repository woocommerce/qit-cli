<?php

namespace QITE2E;

use Spatie\Snapshots\MatchesSnapshots;

class SecurityTest extends QITE2ETestCase {
	use MatchesSnapshots;

	public function test_security_tests() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../security.txt' ) );
	}
}