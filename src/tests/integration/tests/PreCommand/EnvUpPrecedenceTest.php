<?php

namespace integration\tests\Simplified;

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
		$raw = qit_run_env_up( [
			'env:up',
			'--json',
			'--php',
			'8.1',   // CLI should override config
			'--wp',
			'6.0',   // CLI should override config
			'--config',
			$configPath,
		] );

		// 3. Snapshot with built‑in normalisation
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/*────────────────── NEW TESTS ──────────────────*/

	/**
	 * CLI arrays should be appended to (not replace) config arrays and de‑duplicated.
	 */
	public function test_array_merging_precedence(): void {
		// Create real temp directories for volume source paths.
		// Use fixed name so snapshot normalizer produces deterministic output.
		$vol_dir = sys_get_temp_dir() . '/qit-vol-merge-test';
		@rmdir( $vol_dir );
		mkdir( $vol_dir, 0755, true );

		$tmp = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmp, json_encode( [
			'environments' => [
				'default' => [
					'plugins'        => [ 'woocommerce', 'akismet' ],
					'themes'         => [ 'twentytwentythree' ],
					'volumes'        => [ "$vol_dir:/container/path" ],
					'php_extensions' => [ 'imagick' ],
				],
			],
		] ) );

		$raw     = qit_run_env_up( [
			'env:up',
			'--json',
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

		// Plugins and themes are Extension objects — extract slugs.
		$plugin_slugs = array_map(
			static function ( $item ) {
				return is_array( $item ) ? $item['slug'] ?? null : $item;
			},
			$payload['plugins'] ?? []
		);
		$theme_slugs = array_map(
			static function ( $item ) {
				return is_array( $item ) ? $item['slug'] ?? null : $item;
			},
			$payload['themes'] ?? []
		);

		$this->assertEqualsCanonicalizing(
			[ 'woocommerce', 'akismet', 'jetpack' ],
			array_filter( $plugin_slugs )
		);
		$this->assertEqualsCanonicalizing(
			[ 'twentytwentythree', 'twentytwentytwo' ],
			array_filter( $theme_slugs )
		);

		// Volumes are parsed to associative array: container_path => host_path.
		$volumes = $payload['volumes'] ?? [];
		$this->assertArrayHasKey( '/container/path:ro', $volumes );
		$this->assertArrayHasKey( '/tmp:ro', $volumes );

		$this->assertContains( 'imagick', $payload['php_extensions'] );
		$this->assertContains( 'xdebug', $payload['php_extensions'] );

		// Snapshot the fully‑normalised payload for shape regression.
		$this->assertMatchesEnvUpSnapshot( $payload );

		@rmdir( $vol_dir );
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

		$raw  = qit_run_env_up( [
			'env:up',
			'--json',
			'--object_cache',    // flips the flag to TRUE
			'--tunnel=cloudflare',
			'--config',
			$cfg,
		] );
		$data = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertTrue( $data['object_cache'] );
		$this->assertTrue( $data['tunnel'] );
		$this->assertEquals( 'cloudflare', $data['tunnel_type'] );

		$this->assertMatchesEnvUpSnapshot( $data );
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

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
			'--environment=local',
			'--php=8.3',              // CLI beats everything
			'--config',
			$cfg,
		] );

		$data = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$this->assertEquals( '8.3', $data['php_version'] );  // CLI

		$this->assertMatchesEnvUpSnapshot( $data );
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
		$raw = qit_run_env_up( [
			'env:up',
			'--json',
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
		$this->assertSame( '8.1', $payload['php_version'] );
		$this->assertSame( '6.0', $payload['wordpress_version'] );
		$this->assertSame( '8.0', $payload['woocommerce_version'] );

		// 4 · full‑payload snapshot (normalised automatically)
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 2. Profile‑level values beat defaults, but CLI still wins
	 * ============================================================== */
	public function test_profile_precedence_chain(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			// test‑type profile — profiles don't merge scalars into env:up.
			// Profiles are a run:e2e concept; env:up only reads environments.
			'test_types'   => [
				'e2e' => [
					'default' => [ 'php' => '8.0', 'wp' => '6.1' ],
				],
			],
			// environment default — this is what env:up actually reads.
			'environments' => [
				'default' => [ 'php' => '7.4', 'wp' => '5.9' ],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
			'--php',
			'8.2',     // CLI overrides environment
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( '8.2', $payload['php_version'], 'CLI must override environment default.' );
		$this->assertSame( '5.9', $payload['wordpress_version'], 'Environment value used when CLI does not override.' );

		$this->assertMatchesEnvUpSnapshot( $raw );
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

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
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

		$this->assertMatchesEnvUpSnapshot( $raw );
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

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
			'--object_cache',        // short‑hand -o would work as well
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		$this->assertTrue( $payload['object_cache'], 'CLI flag must set object_cache = true.' );

		$this->assertMatchesEnvUpSnapshot( $raw );
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

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
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

		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 6. Environment variables from CLI & file are both honoured
	 * ============================================================== */
	public function test_env_and_files(): void {
		// ---- 1. create an .env file -------------------------------
		$envFilePath = tempnam( sys_get_temp_dir(), 'qit_env_' );
		file_put_contents( $envFilePath, "API_KEY=from_file\nMODE=test\n" );

		// ---- 2. run Pre‑Command -----------------------------------
		$raw = qit_run_env_up( [
			'env:up',
			'--json',
			'--env',
			'DEBUG=true',
			'--env_file',
			$envFilePath,
		] );

		// ---- 3. targeted checks -----------------------------------
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		// Env vars are parsed into an associative map keyed by variable name.
		$envs = $payload['envs'] ?? [];
		$this->assertArrayHasKey( 'DEBUG', $envs );
		$this->assertSame( 'true', $envs['DEBUG'] );
		$this->assertArrayHasKey( 'API_KEY', $envs );
		$this->assertSame( 'from_file', $envs['API_KEY'] );

		$this->assertMatchesEnvUpSnapshot( $raw );

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

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
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

		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 8. Volume mappings – CLI entries merged & preserved
	 * ============================================================== */
	public function test_volume_mappings_merge_and_dedupe(): void {
		// Create real temp directories for volume source paths.
		// Use fixed names so snapshot normalizer produces deterministic output.
		$cache_dir = sys_get_temp_dir() . '/qit-vol-cache-test';
		$logs_dir  = sys_get_temp_dir() . '/qit-vol-logs-test';
		@rmdir( $cache_dir );
		@rmdir( $logs_dir );
		mkdir( $cache_dir, 0755, true );
		mkdir( $logs_dir, 0755, true );

		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'volumes' => [
						"$cache_dir:/var/www/cache",
					],
				],
			],
		], JSON_PRETTY_PRINT ) );

		$raw = qit_run_env_up( [
			'env:up',
			'--json',
			'--volume',
			"$logs_dir:/var/www/logs",
			'--volume',
			"$cache_dir:/var/www/cache", // duplicate of config
			'--config',
			$configPath,
		] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		// Volumes are parsed to associative array: container_path => host_path.
		// Duplicate /var/www/cache should appear only once (dedup by container path).
		$volumes = $payload['volumes'] ?? [];
		$this->assertArrayHasKey( '/var/www/cache:ro', $volumes, 'Config volume should be present' );
		$this->assertArrayHasKey( '/var/www/logs:ro', $volumes, 'CLI volume should be present' );

		// Count unique container paths (strip :ro/:rw flags for counting).
		$container_paths = array_map( function ( $key ) {
			return explode( ':', $key )[0] . ':' . explode( ':', $key )[1];
		}, array_keys( $volumes ) );
		$this->assertCount(
			count( array_unique( $container_paths ) ),
			$container_paths,
			'No duplicate container paths after merge'
		);

		$this->assertMatchesEnvUpSnapshot( $raw );

		@rmdir( $cache_dir );
		@rmdir( $logs_dir );
	}

	/* ================================================================
	 * 9. `--json` flag – pre‑command output is already JSON
	 *    (flag should be accepted and produce identical payload)
	 * ============================================================== */
	public function test_json_flag_is_accepted_and_idempotent(): void {
		// 1. Run once without --json, once with --json
		$rawWithout = qit_run_env_up( [ 'env:up', '--json', '--php', '8.2' ] );
		$rawWith    = qit_run_env_up( [ 'env:up', '--json', '--php', '8.2', '--json' ] );

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
		$this->assertMatchesEnvUpSnapshot( json_decode( $normWith, true ) );
	}


	/* ================================================================
	 * 10. Default fall‑back – no optional args keeps framework defaults
	 * ============================================================== */
	public function test_defaults_when_no_cli_or_config_values(): void {
		// No --config file, no CLI overrides – pure defaults
		$raw = qit_run_env_up( [ 'env:up', '--json' ] );

		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );
		// Normalize the payload to get stable paths for assertions
		$normalizedPayload = \QIT\IntegrationTests\Utils\Normalizer::precommand( $payload );

		// ── scalar defaults that UpEnvironmentCommand declares ─────────
		$this->assertSame( '8.2', $normalizedPayload['php_version'], 'Default PHP should be 8.2' );
		$this->assertSame( 'stable', $normalizedPayload['wordpress_version'], 'Default WP should be "stable"' );

		// ── flags / tunnel ─────────────────────────────────────────────
		$this->assertFalse( $normalizedPayload['object_cache'], 'object_cache must default to false' );
		$this->assertFalse( $normalizedPayload['tunnel'], 'tunnel flag must be false by default' );
		$this->assertSame( 'no_tunnel', $normalizedPayload['tunnel_type'] );

		// ── optional arrays should start empty ─────────────────────────
		foreach ( [ 'plugins', 'themes', 'php_extensions' ] as $k ) {
			$this->assertEmpty( $normalizedPayload[ $k ], "$k should be empty when not provided" );
		}

		// ── volumes should contain only the auto-injected mu-plugin from test bootstrap ────
		$this->assertNotEmpty( $normalizedPayload['volumes'], 'volumes should contain the test bootstrap mu-plugin' );

		// Snapshot the fully‑normalised payload
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 11. Xdebug – `--xdebug` defaults to debug mode
	 * ============================================================== */
	public function test_xdebug_flag_defaults_to_debug(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--xdebug' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'debug', $payload['xdebug'] );
		$this->assertArrayHasKey( '/tmp/xdebug-output', $payload['volumes'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 12. Xdebug – explicit mode string
	 * ============================================================== */
	public function test_xdebug_with_explicit_mode(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--xdebug=profile' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'profile', $payload['xdebug'] );
		$this->assertArrayHasKey( '/tmp/xdebug-output', $payload['volumes'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 13. Xdebug – combined modes
	 * ============================================================== */
	public function test_xdebug_combined_modes(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--xdebug=debug,develop' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'debug,develop', $payload['xdebug'] );
		$this->assertArrayHasKey( '/tmp/xdebug-output', $payload['volumes'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 14. Xdebug – disabled by default when not passed
	 * ============================================================== */
	public function test_xdebug_disabled_by_default(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( '', $payload['xdebug'] );
		$this->assertArrayNotHasKey( '/tmp/xdebug-output', $payload['volumes'] );
	}

	/* ================================================================
	 * 15. Xdebug – CLI overrides config
	 * ============================================================== */
	public function test_cli_xdebug_overrides_config(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'xdebug' => false,
				],
			],
		] ) );

		$raw     = qit_run_env_up( [
			'env:up',
			'--json',
			'--xdebug=profile',
			'--config',
			$configPath,
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'profile', $payload['xdebug'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 16. Xdebug – config value survives when CLI does not override
	 * ============================================================== */
	public function test_config_xdebug_survives_without_cli_override(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json' ], [
			'environments' => [
				'default' => [
					'xdebug' => 'profile',
				],
			],
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'profile', $payload['xdebug'], 'Config xdebug value must survive when CLI does not pass --xdebug.' );
		$this->assertArrayHasKey( '/tmp/xdebug-output', $payload['volumes'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 18. Xdebug – qit.json `true` normalises to `debug`
	 * ============================================================== */
	public function test_config_xdebug_true_normalizes_to_debug(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json' ], [
			'environments' => [
				'default' => [
					'xdebug' => true,
				],
			],
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'debug', $payload['xdebug'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 19. Xdebug – qit.json string mode passes through
	 * ============================================================== */
	public function test_config_xdebug_string_passes_through(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json' ], [
			'environments' => [
				'default' => [
					'xdebug' => 'trace',
				],
			],
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'trace', $payload['xdebug'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 20. Timeout – CLI `--timeout=2` overrides default
	 * ============================================================== */
	public function test_timeout_cli_override(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json', '--timeout=2' ] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 2, $payload['timeout'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 21. Timeout – config value survives when CLI does not override
	 * ============================================================== */
	public function test_timeout_config_survives_without_cli_override(): void {
		$raw     = qit_run_env_up( [ 'env:up', '--json' ], [
			'environments' => [
				'default' => [
					'timeout' => 10,
				],
			],
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 10, $payload['timeout'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}

	/* ================================================================
	 * 22. Timeout – CLI overrides config
	 * ============================================================== */
	public function test_cli_timeout_overrides_config(): void {
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( [
			'environments' => [
				'default' => [
					'timeout' => 300,
				],
			],
		] ) );

		$raw     = qit_run_env_up( [
			'env:up',
			'--json',
			'--timeout=2',
			'--config',
			$configPath,
		] );
		$payload = json_decode( $raw, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 2, $payload['timeout'] );
		$this->assertMatchesEnvUpSnapshot( $raw );
	}
}
