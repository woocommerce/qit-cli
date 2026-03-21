<?php
declare( strict_types=1 );

namespace QIT_CLI\PreCommand\Configuration;

/**
 * Merges two environment configs: a base (from disk) and an overlay (from CLI).
 *
 * Rules:
 *  - Scalars: overlay wins.
 *  - plugins / themes: slug-keyed dedup, overlay wins for same slug.
 *  - volumes: concatenate (then deduplicate exact strings).
 *  - php_extensions: union (deduplicate).
 *  - envs: map merge, overlay wins per key.
 *
 * Both inputs are normalised (short-form → canonical) before merging.
 */
class ConfigMerger {

	/** @var array<string, string> Short-form key aliases. */
	private static array $key_aliases = [
		'php' => 'php_version',
		'wp'  => 'wordpress_version',
		'woo' => 'woocommerce_version',
	];

	/** @var string[] Scalar keys handled by the merger. */
	private static array $scalar_keys = [
		'php_version',
		'wordpress_version',
		'woocommerce_version',
		'object_cache',
		'tunnel',
		'tunnel_type',
		'network_mode',
	];

	/**
	 * Merge base config with overlay. Overlay wins for conflicts.
	 *
	 * @param array<string, mixed> $base    The base config (from qit.json environment block).
	 * @param array<string, mixed> $overlay The overlay config (from CLI via CliConfigParser).
	 *
	 * @return array<string, mixed> Merged config.
	 */
	public static function merge( array $base, array $overlay ): array {
		$base    = self::normalize( $base );
		$overlay = self::normalize( $overlay );

		$result = $base;

		/* ─ Scalars: overlay wins ─ */
		foreach ( self::$scalar_keys as $key ) {
			if ( array_key_exists( $key, $overlay ) ) {
				$result[ $key ] = $overlay[ $key ];
			}
		}

		/* ─ Slug-keyed lists: merge + dedup by slug ─ */
		foreach ( [ 'plugins', 'themes' ] as $key ) {
			if ( isset( $overlay[ $key ] ) ) {
				$result[ $key ] = self::merge_by_slug(
					$result[ $key ] ?? [],
					$overlay[ $key ]
				);
			}
		}

		/* ─ Volumes: concatenate + deduplicate exact strings ─ */
		if ( isset( $overlay['volumes'] ) ) {
			$result['volumes'] = array_values( array_unique( array_merge(
				$result['volumes'] ?? [],
				$overlay['volumes']
			) ) );
		}

		/* ─ PHP extensions: union with dedup ─ */
		if ( isset( $overlay['php_extensions'] ) ) {
			$result['php_extensions'] = array_values( array_unique( array_merge(
				$result['php_extensions'] ?? [],
				$overlay['php_extensions']
			) ) );
		}

		/* ─ Env vars: map merge, overlay wins per key ─ */
		if ( isset( $overlay['envs'] ) ) {
			$result['envs'] = array_merge(
				$result['envs'] ?? [],
				$overlay['envs']
			);
		}

		/* ─ Pass through any remaining overlay keys (overlay wins) ─ */
		$handled_keys = array_merge(
			self::$scalar_keys,
			[ 'plugins', 'themes', 'volumes', 'php_extensions', 'envs' ]
		);
		foreach ( $overlay as $key => $value ) {
			if ( ! in_array( $key, $handled_keys, true ) ) {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Normalise short-form aliases to their canonical long-form keys.
	 *
	 * @param array<string, mixed> $config
	 *
	 * @return array<string, mixed>
	 */
	public static function normalize( array $config ): array {
		foreach ( self::$key_aliases as $short => $long ) {
			if ( isset( $config[ $short ] ) && ! isset( $config[ $long ] ) ) {
				$config[ $long ] = $config[ $short ];
				unset( $config[ $short ] );
			}
		}

		return $config;
	}

	/**
	 * Merge two extension lists by slug. Later entries (overlay) win for same slug.
	 *
	 * @param array<int, string|array<string, mixed>> $base
	 * @param array<int, string|array<string, mixed>> $overlay
	 *
	 * @return array<int, string|array<string, mixed>>
	 */
	private static function merge_by_slug( array $base, array $overlay ): array {
		$index = [];

		foreach ( $base as $entry ) {
			$slug = is_string( $entry ) ? $entry : ( $entry['slug'] ?? null );
			if ( $slug ) {
				$index[ $slug ] = $entry;
			}
		}

		foreach ( $overlay as $entry ) {
			$slug = is_string( $entry ) ? $entry : ( $entry['slug'] ?? null );
			if ( $slug ) {
				$index[ $slug ] = $entry; // Overlay wins.
			}
		}

		return array_values( $index );
	}
}
