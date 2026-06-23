<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\ManagerSync;

class CacheResyncTest extends QITTestCase {

	/**
	 * The bootstrap bucket (which holds "versions") has a short TTL. On cold-CI
	 * runs, slow setup steps (package download, npm install, Playwright browser
	 * install) can elapse past that TTL between the startup sync and the point
	 * where a version like "rc" is resolved, so Cache::get() prunes the bucket.
	 *
	 * Reading a bootstrap key in that state must transparently re-sync and return
	 * the value, not throw "The manager sync data bucket is not available."
	 */
	public function test_get_manager_sync_data_resyncs_when_bucket_pruned() {
		$cache        = App::make( Cache::class );
		$manager_sync = App::make( ManagerSync::class );

		// setUp() already synced, so the bucket is present.
		$this->assertIsArray( $cache->get( $manager_sync->bootstrap_cache_key ), 'Bootstrap bucket is populated after setUp().' );

		// Simulate the bucket being pruned mid-process once its TTL elapsed.
		$cache->delete( $manager_sync->bootstrap_cache_key );
		$this->assertNull( $cache->get( $manager_sync->bootstrap_cache_key ), 'Bootstrap bucket is gone after pruning.' );

		// Should re-sync transparently rather than throw.
		$versions = $cache->get_manager_sync_data( 'versions' );

		$this->assertIsArray( $versions );
		$this->assertArrayHasKey( 'woocommerce', $versions );
		$this->assertSame( '10.6.1', $versions['woocommerce']['rc_unsynced'] );

		// The bucket is repopulated, so subsequent reads hit the warm cache.
		$this->assertIsArray( $cache->get( $manager_sync->bootstrap_cache_key ), 'Bootstrap bucket is repopulated after re-sync.' );
	}

	/**
	 * A key that genuinely does not exist in a present bucket must still throw,
	 * so the re-sync path doesn't mask real "missing key" errors.
	 */
	public function test_get_manager_sync_data_throws_for_unknown_key_in_present_bucket() {
		$this->expectException( \UnexpectedValueException::class );
		$this->expectExceptionMessage( "does not have the key 'this_key_does_not_exist'" );

		App::make( Cache::class )->get_manager_sync_data( 'this_key_does_not_exist' );
	}
}
