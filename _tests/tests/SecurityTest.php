<?php

namespace QITE2E;

use QITE2E\QITE2ETestCase;
use Spatie\Snapshots\MatchesSnapshots;

class SecurityTest extends QITE2ETestCase {
	use MatchesSnapshots;

	public function test_security_main() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../security/main/main.txt' ) );
	}
}