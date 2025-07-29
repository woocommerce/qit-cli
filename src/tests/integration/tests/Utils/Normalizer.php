<?php
declare( strict_types=1 );

namespace QIT\IntegrationTests\Utils;

final class Normalizer {
	/* ─────────────────────────── constants ───────────────────────── */

	private const TMP_PLACEHOLDER = '/tmp-normalised/';
	private const CACHE_DIR_PLACEHOLDER = '/normalized/cache/dir';
	private const DL_PLACEHOLDER = '/normalized/downloaded-path/file.zip';

	/* ─────────────────────── generic replacements ────────────────── */

	private static function maskPaths( string $v, ?string $envId = null, ?string $repoRoot = null ): string {
		$tmp = rtrim( sys_get_temp_dir(), '/' );

		$v = str_replace( "$tmp/", self::TMP_PLACEHOLDER, $v );
		$v = str_replace( '/tmp/', self::TMP_PLACEHOLDER, $v );

		$v = preg_replace( '/qit-env-[a-f0-9]{32}\.json/', 'qit-env-<hash>.json', $v );
		$v = preg_replace( '/qit-cache-\d{8}-\d+/', 'qit-cache-NORMALISED', $v );
		$v = preg_replace( '/qit_scaffolded_e2e-[a-f0-9]+/', 'qit_scaffolded_e2e-NORMALISED', $v );
		$v = preg_replace( '/qit_config-qit_custom_tests_[a-f0-9]+/', 'qit_config-qit_custom_tests_NORMALISED', $v );
		$v = preg_replace( '/cache\/[a-f0-9]+/', 'cache/NORMALISED_ID/', $v );

		if ( $envId ) {
			$v = str_replace( $envId, 'ENV_ID', $v );
			$v = preg_replace( '/e2e-[a-f0-9]{11,}/', 'e2e-ENV_ID', $v );
			$v = str_replace( "qit_network_$envId", 'qit_network_ENV_ID', $v );
		}
		if ( $repoRoot ) {
			$v = str_replace( $repoRoot, '/repo', $v );
		}

		return $v;
	}

	/* ─────────────────── plugin/theme array helper ───────────────── */

	/**
	 * Normalise every plugin / theme array.
	 *
	 *  • Re‑writes only the values that are known to be volatile
	 *  • Masks paths inside *all* other string fields (so a new
	 *    field added tomorrow will automatically be normalised)
	 *  • Never unsets or hides a key
	 *
	 * @param array<int,array<string,mixed>> $items (passed by reference)
	 */
	private static function normalisePluginArray( array &$items ): void {
		foreach ( $items as &$plugin ) {
			/* ───────────────────── source URL (special case) ────────── */
			if ( isset( $plugin['source'] ) && is_string( $plugin['source'] )
			     && str_starts_with( $plugin['source'], 'http' ) ) {

				$plugin['source'] = 'https://normalized-remote-source/' .
				                    basename( parse_url( $plugin['source'], PHP_URL_PATH ) );
			}

			/* ────────────────── per‑field normalisation map ─────────── */
			$replacements = [
				'version'           => 'NORMALIZED_VERSION',
				'downloaded_source' => self::DL_PLACEHOLDER,
				'download_url'      => 'NORMALIZED_DOWNLOAD_URL',
				'checksum'          => 'NORMALIZED_CHECKSUM',
			];

			/* ─────────────────── iterate over *all* keys ─────────────── */
			foreach ( $plugin as $key => &$value ) {
				if ( ! is_string( $value ) ) {
					continue;               // non‑scalar → leave intact
				}

				if ( isset( $replacements[ $key ] ) && $value !== '' ) {
					// Known volatile field → hard‑replace
					$value = $replacements[ $key ];
				} else {
					// Unknown string field → just mask paths / ids
					$value = self::maskPaths( $value );
				}
			}
			unset( $value ); // break reference
		}
	}


	/* ──────────────────── public entry‑points ───────────────────── */

	/**
	 * Normalise a Pre‑Command payload so it can be snap‑shotted.
	 *
	 * @param string|array $payload  Raw JSON returned by qit_precommand()
	 *                               **or** the already‑decoded array.
	 * @return string|array          Same type that was passed in.
	 */
	public static function precommand( string|array $payload ): string|array {
		// Step 1 – ensure we are working with an array.
		$isJson  = is_string( $payload );
		$data    = $isJson ? json_decode( $payload, true ) : $payload;

		if ( ! is_array( $data ) ) {
			throw new \InvalidArgumentException(
				'Normalizer::precommand() expects JSON object or array.'
			);
		}

		/* ─────────────────────── env‑id handling ─────────────────── */
		$envId    = $data['env_id']               // top‑level (new)
		           ?? $data['env_info']['env_id'] // legacy
		           ?? null;
		$repoRoot = realpath( __DIR__ . '/../../..' );

		// 1. recurse over every scalar string
		array_walk_recursive( $data, function ( &$v ) use ( $envId, $repoRoot ) {
			if ( is_string( $v ) ) {
				$v = self::maskPaths( $v, $envId, $repoRoot );
			}
		} );

		// 2. stable scalars
		$data['env_info']['created_at']    = 0;
		$data['env_info']['nginx_port']    = 'PORT';
		$data['env_info']['temporary_env'] = self::TMP_PLACEHOLDER . 'temporary-env/';

		// Root‑level timestamp is also volatile
		if ( isset( $data['created_at'] ) ) {
			$data['created_at'] = 0;
		}

		// 3. cache dir & downloaded paths
		if ( isset( $data['configuration']['cache_dir'] ) ) {
			$data['configuration']['cache_dir'] = self::CACHE_DIR_PLACEHOLDER;
		}
		if ( isset( $data['downloaded_paths'] ) ) {
			foreach ( $data['downloaded_paths'] as &$p ) {
				$p = self::DL_PLACEHOLDER;
			}
		}

		// 4. plugins everywhere
		foreach (
			[
				&$data['env_info']['plugins'],
				&$data['configuration']['resolved_plugins'],
				&$data['resolved_extensions'],
			] as &$maybe
		) {
			if ( is_array( $maybe ) ) {
				self::normalisePluginArray( $maybe );
			}
		}

		if ( isset( $data['configuration']['sut_extension'] )
		     && is_array( $data['configuration']['sut_extension'] ) ) {

			$tmp = [ &$data['configuration']['sut_extension'] ];
			self::normalisePluginArray( $tmp );
			$data['configuration']['sut_extension'] = $tmp[0];
		}

		// Filter out built-in MU-plugin volume mapping from volumes array
		if ( isset( $data['volumes'] ) && is_array( $data['volumes'] ) ) {
			$data['volumes'] = array_filter( $data['volumes'], function( $volume ) {
				// Filter out MU-plugin volume mappings (they contain 'mu-plugins' in the path)
				return ! str_contains( $volume, 'mu-plugins' );
			} );
			// Re-index the array to maintain consistent ordering
			$data['volumes'] = array_values( $data['volumes'] );
		}

		/* Step 3 – return in the same format we received */
		return $isJson ? json_encode( $data, JSON_PRETTY_PRINT ) : $data;
	}

	/** Normalise raw CLI / stdout logs */
	public static function cli( string $raw ): string {
		$raw = self::maskPaths( $raw );
		$raw = preg_replace( '/qit-results-[a-z0-9]+/', 'qit-results-normalisedid', $raw );

		// (keep the rest of your existing CLI‑specific rules unchanged)
		return $raw;
	}
}
