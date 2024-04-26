<?php

class BasicTest extends \PHPUnit\Framework\TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	public function test_run_env_up_exists() {
		qit( [ 'env:up', '--help' ] );
		// If we got here, it means the command ran successfully.
		$this->assertTrue( true );
	}

	public function test_run_e2e_exists() {
		qit( [ 'run:e2e', '--help' ] );
		// If we got here, it means the command ran successfully.
		$this->assertTrue( true );
	}
}