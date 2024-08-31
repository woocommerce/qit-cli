<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use PHPUnit\Framework\TestCase;
use QIT_CLI\Cache;
use QIT_CLI\ManagerSync;
use QIT_CLI\RequiresPluginsResolver;

class RequiresPluginsResolverTest extends TestCase {
	/** @var Cache $cache */
	protected $cache;

	public function tearDown(): void {
		App::offsetUnset( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE' );
		parent::tearDown();
	}

	public function test_make_sut() {
		$this->assertInstanceOf( RequiresPluginsResolver::class, App::make( RequiresPluginsResolver::class ) );
	}

	public function test_resolve_no_dependencies() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'woo-extension-1',
					'dependencies' => [],
				],
			],
		], - 1 );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [], $d );
	}

	public function test_resolve_woo_dependency() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'woo-extension-1',
					'dependencies' => [
						'woo' => [
							123,
						],
					],
				],
			],
		], - 1 );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [ 'woo' => [ 123 ] ], $d );
	}

	public function test_resolve_wporg_dependency() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'woo-extension-1',
					'dependencies' => [
						'wporg' => [
							'bar',
						],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE', json_encode( [
			'requires_plugins' => [],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [ 'wporg' => [ 'bar' ] ], $d );
	}

	public function test_resolve_wporg_dependency_with_dependencies() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'woo-extension-1',
					'dependencies' => [
						'wporg' => [
							'bar',
						],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE', json_encode( [
			'requires_plugins' => [ 'foo' ],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [ 'wporg' => [ 'foo', 'bar' ] ], $d );
	}
}