<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\ManagerSync;
use QIT_CLI\RequestBuilder;

class SyncAuthTest extends QITTestCase {

	/**
	 * Verify that without_auth() sets the skip_auth flag on the builder.
	 */
	public function test_without_auth_sets_skip_auth_flag() {
		$builder = new class( 'https://example.com/test' ) extends RequestBuilder {
			public function is_skip_auth(): bool {
				return $this->skip_auth;
			}
		};

		$this->assertFalse( $builder->is_skip_auth() );
		$builder->without_auth();
		$this->assertTrue( $builder->is_skip_auth() );
	}

	/**
	 * Verify that without_auth() returns $this for fluent chaining.
	 */
	public function test_without_auth_is_fluent() {
		$builder = new RequestBuilder( 'https://example.com/test' );
		$this->assertSame( $builder, $builder->without_auth() );
	}

	/**
	 * Verify that bootstrap sync (cli/sync) skips auth while
	 * extensions sync (cli/sync/extensions) uses auth.
	 *
	 * This locks in the split so a future refactor cannot silently
	 * reintroduce credentials on the bootstrap route.
	 */
	public function test_bootstrap_sync_skips_auth_and_extensions_sync_uses_auth() {
		App::setVar( 'mocked_requests', [] );

		App::make( Cache::class )->set( 'manager_secret', 'test-secret', -1 );
		App::make( ManagerSync::class )->maybe_sync( true );

		$requests = App::getVar( 'mocked_requests' );

		$bootstrap_request  = null;
		$extensions_request = null;

		foreach ( $requests as $req ) {
			if ( strpos( $req['url'], 'cli/sync/extensions' ) !== false ) {
				$extensions_request = $req;
			} elseif ( strpos( $req['url'], 'cli/sync' ) !== false ) {
				$bootstrap_request = $req;
			}
		}

		$this->assertNotNull( $bootstrap_request, 'Bootstrap sync request was made.' );
		$this->assertNotNull( $extensions_request, 'Extensions sync request was made.' );

		$this->assertTrue( $bootstrap_request['skip_auth'], 'Bootstrap sync must not send credentials.' );
		$this->assertFalse( $extensions_request['skip_auth'], 'Extensions sync must send credentials.' );
	}
}
