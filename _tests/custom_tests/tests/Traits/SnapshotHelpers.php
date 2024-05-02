<?php

namespace QIT\SelfTests\CustomTests\Traits;

use Spatie\Snapshots\MatchesSnapshots;

trait SnapshotHelpers {
	use MatchesSnapshots;

	public function assertMatchesNormalizedSnapshot( string $actual, ?\Spatie\Snapshots\Driver $driver = null ): void {
		$actual = str_replace( sys_get_temp_dir(), '/tmp-normalized', $actual );
		$actual = str_replace( '/tmp/', '/tmp-normalized/', $actual );

		/*
		 * "paratest" sets the "TEST_TOKEN" env var.
		 * If this is not set, it means we are running in a normal PHPUnit environment.
		 */
		if ( empty( getenv( 'TEST_TOKEN' ) ) ) {
			$actual = preg_replace( '/First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.\n/', '', $actual );
		}

		$normalized_spaces = '';

		foreach ( explode( "\n", $actual ) as $line ) {
			$normalized_spaces .= trim( $line ) . "\n";
		}

		$this->assertMatchesTextSnapshot( $normalized_spaces, $driver );
	}
}