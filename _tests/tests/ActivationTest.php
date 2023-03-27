<?php

namespace QITE2E;

use QITE2E\QITE2ETestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ActivationTest extends QITE2ETestCase {
	use MatchesSnapshots;

	public function test_activation_php82() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation/php82/php82.txt' ) );
	}
	public function test_activation_generic() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation/generic/generic.txt' ) );
	}
	public function test_activation_php81() {
		$this->assertMatchesSnapshot( $this->validate_and_normalize( __DIR__ . '/../activation/php81/php81.txt' ) );
	}
}