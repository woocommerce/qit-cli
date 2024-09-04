<?php

namespace QIT_CLI;

class WooExtensionsList {
	/** @var Cache $cache */
	protected $cache;

	/** @var string $woo_extensions_cache_key */
	protected $woo_extensions_cache_key;

	/** @var ManagerSync $manager_sync */
	protected $manager_sync;

	public function __construct( Cache $cache, ManagerSync $manager_sync ) {
		$this->cache                    = $cache;
		$this->manager_sync             = $manager_sync;
		$this->woo_extensions_cache_key = sprintf( 'woo_extensions_%s', md5( get_manager_url() ) );
	}

	/**
	 * Force re-sync to fetch WooExtensions list associated with the current Partner.
	 *
	 * @throws \Exception|\RuntimeException When could not retrieve list of WooCommerce extensions.
	 */
	public function fetch_woo_extensions_available(): void {
		$this->manager_sync->maybe_sync( true );
	}

	/**
	 * @return array<mixed> Gets the Woo Extensions list that the current authenticated user has access to.
	 */
	public function get_woo_extension_list(): array {
		try {
			return $this->cache->get_manager_sync_data( 'extensions' );
		} catch ( \Exception $e ) {
			return [];
		}
	}

	/**
	 * @param string|int $slug_or_id
	 *
	 * @return void
	 */
	public function check_woo_extension_exists( $slug_or_id ): void {
		if ( is_numeric( $slug_or_id ) ) {
			$this->get_woo_extension_slug_by_id( $slug_or_id );
		} else {
			$this->get_woo_extension_id_by_slug( $slug_or_id );
		}
	}

	public function get_woo_extension_id_by_slug( string $woo_extension_slug ): int {
		$extensions = $this->get_woo_extension_list();

		foreach ( $extensions as $e ) {
			if ( $e['slug'] === $woo_extension_slug ) {
				return (int) $e['id'];
			}
		}

		throw new \UnexpectedValueException( "Could not find Woo Extension with slug $woo_extension_slug." );
	}

	public function get_woo_extension_slug_by_id( int $woo_extension_id ): string {
		$extensions = $this->get_woo_extension_list();

		foreach ( $extensions as $e ) {
			if ( $e['id'] === $woo_extension_id ) {
				return $e['slug'];
			}
		}

		throw new \UnexpectedValueException( "Could not find Woo Extension with id $woo_extension_id." );
	}

	public function get_woo_extension_type( int $woo_extension_id ): string {
		$extensions = $this->get_woo_extension_list();

		foreach ( $extensions as $e ) {
			if ( $e['id'] === $woo_extension_id ) {
				return $e['type'] ?? 'plugin'; // Todo: Remove "??" when we merge the Improved Activation PR.
			}
		}

		throw new \UnexpectedValueException( "Could not find Woo Extension with id $woo_extension_id." );
	}

	/**
	 * @param int $woo_extension_id
	 *
	 * @return array{
	 *     wporg:array<string>,
	 *     woo:array<int>
	 * } Array of WPORG Slugs and Woo IDs that the given Woo Extension depends on.
	 */
	public function get_woo_extension_dependencies( int $woo_extension_id ): array {
		$extensions = $this->get_woo_extension_list();

		foreach ( $extensions as $e ) {
			if ( $e['id'] === $woo_extension_id ) {
				return $e['dependencies'] ?? []; // Todo: Remove "??" when we merge the Improved Activation PR.
			}
		}

		throw new \UnexpectedValueException( "Could not find Woo Extension with id $woo_extension_id." );
	}
}
