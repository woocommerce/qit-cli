<?php

namespace QITE2E;

use Spatie\Snapshots\MatchesSnapshots;

class ActivationTest extends QITE2ETestCase {
	use MatchesSnapshots;

	public function test_main_activation() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation-main.txt' ) );
	}

	public function test_php81_activation() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation-php81.txt' ) );
	}

	public function test_php82_activation() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation-php82.txt' ) );
	}
}