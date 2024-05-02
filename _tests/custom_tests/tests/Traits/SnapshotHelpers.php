<?php

namespace QIT\SelfTests\CustomTests\Traits;

use Spatie\Snapshots\MatchesSnapshots;

trait SnapshotHelpers {
	use MatchesSnapshots;

	public function assertMatchesNormalizedSnapshot( $actual, ?\Spatie\Snapshots\Driver $driver = null ): void {
		$actual = str_replace( sys_get_temp_dir(), '/tmp-normalized', $actual );
		$this->assertMatchesSnapshot( $actual, $driver );
	}
}