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
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'wporg' => [],
						'woo'   => [],
					],
				],
			],
		], - 1 );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [ 'woo' => [], 'wporg' => [] ], $d );
	}

	public function test_resolve_woo_dependency() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'woo'   => [
							123,
						],
						'wporg' => [],
					],
				],
			],
		], - 1 );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [
			'woo'   => [ 123 ],
			'wporg' => [],
		], $d );
	}

	public function test_resolve_wporg_dependency() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'wporg' => [
							'automatewoo',
						],
						'woo'   => [],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_automatewoo', json_encode( [
			'requires_plugins' => [],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [
			'woo'   => [],
			'wporg' => [ 'automatewoo' ],
		], $d );
	}

	public function test_resolve_wporg_dependency_with_dependencies() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'wporg' => [
							'automatewoo',
						],
						'woo'   => [],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_automatewoo', json_encode( [
			'requires_plugins' => [ 'woocommerce-payments' ],
		] ) );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_woocommerce-payments', json_encode( [
			'requires_plugins' => [],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [
			'woo'   => [],
			'wporg' => [ 'woocommerce-payments', 'automatewoo' ],
		], $d );
	}

	public function test_resolve_wporg_dependency_as_itself() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'wporg' => [
							'automatewoo-referrals',
						],
						'woo'   => [],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_automatewoo-referrals', json_encode( [
			'requires_plugins' => [ '' ],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [
			'woo'   => [],
			'wporg' => [ 'automatewoo-referrals' ],
		], $d );
	}

	public function test_resolve_woo_dependency_as_itself() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'wporg' => [],
						'woo'   => [
							1,
						],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_automatewoo-referrals', json_encode( [
			'requires_plugins' => [ '' ],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [
			'woo'   => [ 1 ],
			'wporg' => [],
		], $d );
	}

	public function test_resolve_wporg_dependencies_of_woo_dependency() {
		App::make( Cache::class )->set( App::make( ManagerSync::class )->sync_cache_key, [
			'extensions' => [
				[
					'id'           => 1,
					'slug'         => 'automatewoo-referrals',
					'dependencies' => [
						'wporg' => [],
						'woo' => [ 2 ],
					],
				],
				[
					'id'           => 2,
					'slug'         => 'automatewoo-birthdays',
					'dependencies' => [
						'wporg' => [
							'woocommerce-payments',
						],
						'woo'   => [],
					],
				],
			],
		], - 1 );

		App::setVar( 'MOCKED_WPORG_REQUIRES_PLUGINS_RESPONSE_woocommerce-payments', json_encode( [
			'requires_plugins' => [ 'woocommerce' ],
		] ) );

		$d = App::make( RequiresPluginsResolver::class )->resolve_dependencies( 1 );

		$this->assertEquals( [
			'woo'   => [ 2 ],
			'wporg' => [ 'woocommerce', 'woocommerce-payments' ],
		], $d );
	}
}