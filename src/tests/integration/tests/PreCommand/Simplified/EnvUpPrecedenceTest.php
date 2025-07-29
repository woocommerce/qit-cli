<?php

namespace integration\tests\PreCommand\Simplified;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\Traits\SnapshotHelpers;

final class EnvUpPrecedenceTest extends TestCase {
	use SnapshotHelpers;

	// already pulls in MatchesSnapshots

	public function test_cli_precedence_for_env_up(): void {
		// 1. Write qit.json
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'php' => '7.4',
					'wp'  => '5.9',
				],
			],
		] ) );

		// 2. Run Pre‑Command
		$raw = qit_precommand( [
			'env:up',
			'--php',
			'8.1',   // CLI should override config
			'--wp',
			'6.0',   // CLI should override config
			'--config',
			$configPath,
		] );

		// 3. Snapshot with built‑in normalisation
		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/*────────────────── NEW TESTS ──────────────────*/

	/**
	 * CLI arrays should be appended to (not replace) config arrays and de‑duplicated.
	 */
	public function test_array_merging_precedence(): void {
		$tmp = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmp, json_encode( [
			'environments' => [
				'default' => [
					'plugins'        => [ 'woocommerce', 'akismet' ],
					'themes'         => [ 'twentytwentythree' ],
					'volumes'        => [ '/foo:/bar' ],
					'php_extensions' => [ 'imagick' ],
				],
			],
		] ) );

		$raw     = qit_precommand( [
			'env:up',
			'--plugin',
			'jetpack',
			'--theme',
			'twentytwentytwo',
			'--volume',
			'/tmp:/tmp',
			'--php_extension',
			'xdebug',
			'--config',
			$tmp,
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$plugins = $payload['plugins'];
		$themes  = $payload['themes'];

		// Debug: dump the actual payload to understand what we're getting
		file_put_contents( '/tmp/test_debug.json', json_encode( $payload, JSON_PRETTY_PRINT ) );
		echo "\nDEBUG: Plugins array: " . json_encode( $plugins ) . "\n";
		echo "DEBUG: Themes array: " . json_encode( $themes ) . "\n";

		// Plugins and themes are returned as simple string arrays, not objects
		$this->assertCount( 3, $plugins );  // woocommerce, akismet, jetpack
		$this->assertEqualsCanonicalizing(
			[ 'woocommerce', 'akismet', 'jetpack' ],
			$plugins
		);
		$this->assertEquals(
			[ 'twentytwentythree', 'twentytwentytwo' ],
			$themes
		);
		$this->assertContains( '/foo:/bar', $payload['volumes'] );
		$this->assertContains( '/tmp:/tmp', $payload['volumes'] );
		$this->assertContains( 'imagick', $payload['php_extensions'] );
		$this->assertContains( 'xdebug', $payload['php_extensions'] );

		// Snapshot the fully‑normalised payload for shape regression.
		$this->assertMatchesPrecommandSnapshot( $payload );
	}

	/**
	 * Flags / booleans & tunnel option should survive layering.
	 */
	public function test_flags_and_tunnel(): void {
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'environments' => [
				'default' => [
					'object_cache' => false,
				],
			],
		] ) );

		$raw  = qit_precommand( [
			'env:up',
			'--object_cache',    // flips the flag to TRUE
			'--tunnel=cloudflare',
			'--config',
			$cfg,
		] );
		$data = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertTrue( $data['object_cache'] );
		$this->assertTrue( $data['tunnel'] );
		$this->assertEquals( 'cloudflare', $data['tunnel_type'] );

		$this->assertMatchesPrecommandSnapshot( $data );
	}

	/**
	 * Profiles (test‑type defaults) < config environment extends < CLI.
	 */
	public function test_profile_and_extends_precedence(): void {
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'test_types'   => [
				'e2e' => [
					'default' => [ 'php' => '7.4' ], // profile default
				],
			],
			'environments' => [
				'base'  => [
					'php' => '8.0',              // base environment setting
				],
				'local' => [
					'extends' => 'base',
					// no php override, so inherits 8.0 from base
				],
			],
		] ) );

		$raw = qit_precommand( [
			'env:up',
			'--environment=local',
			'--php=8.3',              // CLI beats everything
			'--config',
			$cfg,
		] );

		$data = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$this->assertEquals( '8.3', $data['php'] );  // CLI

		$this->assertMatchesPrecommandSnapshot( $data );
	}


	/* ================================================================
	 * 1. CLI scalars override everything in qit.json
	 * ============================================================== */
	public function test_cli_overrides_config_scalars(): void {
		// 1 · fixture qit.json
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'php' => '7.4',
					'wp'  => '5.9',
					'woo' => '7.9',
				],
			],
		], JSON_PRETTY_PRINT ) );

		// 2 · run Pre‑Command
		$raw = qit_precommand( [
			'env:up',
			'--php',
			'8.1',   // CLI wins
			'--wp',
			'6.0',
			'--woo',
			'8.0',
			'--config',
			$configPath,
		] );

		// 3 · targeted assertions
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$this->assertSame( '8.1', $payload['php'] );
		$this->assertSame( '6.0', $payload['wp'] );
		$this->assertSame( '8.0', $payload['woo'] );

		// 4 · full‑payload snapshot (normalised automatically)
		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
	 * 2. Profile‑level values beat defaults, but CLI still wins
	 * ============================================================== */
	public function test_profile_precedence_chain(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			// test‑type profile
			'test_types'   => [
				'e2e' => [
					'default' => [ 'php' => '8.0', 'wp' => '6.1' ],
				],
			],
			// environment default
			'environments' => [
				'default' => [ 'php' => '7.4', 'wp' => '5.9' ],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_precommand( [
			'env:up',
			'--php',
			'8.2',     // final winner
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( '8.2', $payload['php'], 'CLI must override profile/default.' );
		$this->assertSame( '6.1', $payload['wp'], 'Profile overrides environment default.' );

		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
	 * 3. `extends` chain is merged recursively (arrays de‑duplicated)
	 * ============================================================== */
	public function test_extends_merging_inherited_plugins(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'base'    => [
					'php'     => '8.0',
					'plugins' => [ 'woocommerce' ],
				],
				'premium' => [
					'extends' => 'base',
					'plugins' => [ 'jetpack' ],
				],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_precommand( [
			'env:up',
			'--environment',
			'premium',
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		// Extract plugin slugs regardless of exact representation.
		$slugs = array_map(
			static function ( $item ) {
				if ( is_array( $item ) && isset( $item['slug'] ) ) {
					return $item['slug'];
				}

				return is_string( $item ) ? $item : null;
			},
			$payload['plugins'] ?? []
		);

		$this->assertEqualsCanonicalizing(
			[ 'woocommerce', 'jetpack' ],
			array_filter( $slugs ),
			'Child environment should inherit + extend plugin list.',
		);

		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
 * 4. Boolean flags – `--object_cache` turns it on
 * ============================================================== */
	public function test_object_cache_flag(): void {
		// qit.json does NOT enable object‑cache
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [ 'default' => [] ],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_precommand( [
			'env:up',
			'--object_cache',        // short‑hand -o would work as well
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$this->assertTrue( $payload['object_cache'], 'CLI flag must set object_cache = true.' );

		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
	 * 5. CLI‑provided plugins are merged & deduplicated
	 * ============================================================== */
	public function test_cli_plugins_extend_config(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'akismet' ],
				],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_precommand( [
			'env:up',
			'--plugin',
			'jetpack',
			'--plugin',
			'woocommerce',      // duplicate of config on purpose
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$slugs   = array_map(
			static function ( $item ) {
				return is_array( $item ) ? $item['slug'] ?? null : $item;
			},
			$payload['plugins'] ?? []
		);

		$this->assertEqualsCanonicalizing(
			[ 'woocommerce', 'akismet', 'jetpack' ],
			array_filter( $slugs ),
			'CLI plugins should be merged (no duplicates, order irrelevant).',
		);

		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
	 * 6. Environment variables from CLI & file are both honoured
	 * ============================================================== */
	public function test_env_vars_and_files(): void {
		// ---- 1. create an .env file -------------------------------
		$envFilePath = tempnam( sys_get_temp_dir(), 'qit_env_' );
		file_put_contents( $envFilePath, "API_KEY=from_file\nMODE=test\n" );

		// ---- 2. run Pre‑Command -----------------------------------
		$raw = qit_precommand( [
			'env:up',
			'--env_var',
			'DEBUG=true',
			'--env_file',
			$envFilePath,
		] );

		// ---- 3. targeted checks -----------------------------------
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertContains( 'DEBUG=true', $payload['extra']['env_vars'] ?? [] );
		$this->assertCount( 1, $payload['extra']['env_files'] ?? [] );

		$this->assertMatchesPrecommandSnapshot( $raw );

		// ---- 4. cleanup -------------------------------------------
		@unlink( $envFilePath );
	}

	/* ================================================================
	 * 7. PHP‑extensions – CLI additions merged & deduplicated
	 * ============================================================== */
	public function test_php_extensions_merge_and_dedupe(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'php_extensions' => [ 'xdebug', 'soap' ],
				],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_precommand( [
			'env:up',
			'--php_extension',
			'imagick',
			'--php_extension',
			'xdebug',      // duplicate on purpose
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$this->assertEqualsCanonicalizing(
			[ 'xdebug', 'soap', 'imagick' ],
			$payload['php_extensions'] ?? [],
			'php_extensions should be the union of config & CLI without duplicates.'
		);

		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
	 * 8. Volume mappings – CLI entries merged & preserved
	 * ============================================================== */
	public function test_volume_mappings_merge_and_dedupe(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'volumes' => [
						'/host/cache:/var/www/cache',
					],
				],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_precommand( [
			'env:up',
			'--volume',
			'/host/logs:/var/www/logs',
			'--volume',
			'/host/cache:/var/www/cache', // duplicate
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		// Normalize the payload to get stable paths for assertions
		$normalizedPayload = \QIT\IntegrationTests\Utils\Normalizer::precommand( $payload );
		
		$this->assertEqualsCanonicalizing(
			[
				'/host/cache:/var/www/cache',
				'/host/logs:/var/www/logs',
				'/repo/integration/helpers/CUSTOM_MU_PLUGIN.php:/var/www/html/wp-content/mu-plugins/CUSTOM_MU_PLUGIN.php',
			],
			$normalizedPayload['volumes'] ?? [],
			'Volume mappings should be merged and deduplicated, including auto-injected mu-plugin.',
		);

		$this->assertMatchesPrecommandSnapshot( $raw );
	}

	/* ================================================================
	 * 9. `--json` flag – pre‑command output is already JSON
	 *    (flag should be accepted and produce identical payload)
	 * ============================================================== */
	public function test_json_flag_is_accepted_and_idempotent(): void {
		// 1. Run once without --json, once with --json
		$rawWithout = qit_precommand( [ 'env:up', '--php', '8.2' ] );
		$rawWith    = qit_precommand( [ 'env:up', '--php', '8.2', '--json' ] );

		// 2. Decode -> normalise -> re‑encode so both payloads become deterministic
		$normWithout = json_encode(
			\QIT\IntegrationTests\Utils\Normalizer::precommand( json_decode( $rawWithout, true ) ),
			JSON_UNESCAPED_SLASHES
		);
		$normWith    = json_encode(
			\QIT\IntegrationTests\Utils\Normalizer::precommand( json_decode( $rawWith, true ) ),
			JSON_UNESCAPED_SLASHES
		);

		// 3. They must be identical
		$this->assertJsonStringEqualsJsonString(
			$normWithout,
			$normWith,
			'When running in pre‑command mode the output is JSON regardless of --json; the flag must not alter the payload.'
		);

		// 4. Snapshot the normalised version (not the raw one)
		$this->assertMatchesPrecommandSnapshot( json_decode( $normWith, true ) );
	}


	/* ================================================================
	 * 10. Default fall‑back – no optional args keeps framework defaults
	 * ============================================================== */
	public function test_defaults_when_no_cli_or_config_values(): void {
		// No --config file, no CLI overrides – pure defaults
		$raw = qit_precommand( [ 'env:up' ] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		// Normalize the payload to get stable paths for assertions
		$normalizedPayload = \QIT\IntegrationTests\Utils\Normalizer::precommand( $payload );

		// ── scalar defaults that UpEnvironmentCommand declares ─────────
		$this->assertSame( '8.2', $normalizedPayload['php'], 'Default PHP should be 8.2' );
		$this->assertSame( 'stable', $normalizedPayload['wp'], 'Default WP should be "stable"' );

		// ── flags / tunnel ─────────────────────────────────────────────
		$this->assertFalse( $normalizedPayload['object_cache'], 'object_cache must default to false' );
		$this->assertFalse( $normalizedPayload['tunnel'], 'tunnel flag must be false by default' );
		$this->assertSame( 'no_tunnel', $normalizedPayload['tunnel_type'] );

		// ── optional arrays should start empty ─────────────────────────
		foreach ( [ 'plugins', 'themes', 'php_extensions' ] as $k ) {
			$this->assertEmpty( $normalizedPayload[ $k ], "$k should be empty when not provided" );
		}

		// ── volumes should contain only the auto-injected mu-plugin ────
		$this->assertCount( 1, $normalizedPayload['volumes'], 'volumes should contain only the auto-injected mu-plugin' );
		$this->assertContains(
			'/repo/integration/helpers/CUSTOM_MU_PLUGIN.php:/var/www/html/wp-content/mu-plugins/CUSTOM_MU_PLUGIN.php',
			$normalizedPayload['volumes'],
			'Auto‑injected mu‑plugin volume must always be present'
		);

		// Snapshot the fully‑normalised payload
		$this->assertMatchesPrecommandSnapshot( $raw );
	}


}
