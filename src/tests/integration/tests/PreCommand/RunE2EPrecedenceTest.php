<?php

namespace integration\tests\Simplified;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\Traits\SnapshotHelpers;

use PHPUnit\Framework\Attributes\Group;

#[Group("docker")]
class RunE2EPrecedenceTest extends TestCase {
	use SnapshotHelpers;

	public function test_cli_overrides_config_for_run_e2e(): void {
		/* ---------- 1. Fixture: qit.json on disk ---------- */
		$config     = [
			'environments' => [
				'default' => [
					'php'     => '7.4',
					'wp'      => '5.9',
					'plugins' => [ 'woocommerce' ],
				],
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'php' => '8.0',                  // profile‑level value
						'sut' => [
							'slug'   => 'test-plugin',
							'type'   => 'plugin',
							'source' => [ 'type' => 'local', 'path' => './' ]
						],
					],
				],
			],
		];
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config ) );

		/* ---------- 2. Execute CLI command with env_info early return ---------- */
		$raw = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--json',
			'--config',
			$configPath,
			'--php',
			'8.1',              // should beat every other layer
			'--plugin',
			'jetpack',       // added via CLI
		] );

		$this->assertMatchesEnvUpSnapshot( $raw );

		// Clean up
		unlink( $configPath );
	}

	public function test_profile_defaults_for_run_e2e(): void {
		/* ---------- 1. Fixture: qit.json with profile defaults ---------- */
		$config     = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'php' => '8.0',
						'wp'  => '6.1',
						'sut' => [
							'slug'   => 'test-plugin',
							'type'   => 'plugin',
							'source' => [ 'type' => 'local', 'path' => './' ]
						],
					],
				],
			],
		];
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config ) );

		/* ---------- 2. Execute without CLI overrides ---------- */
		$raw = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--json',
			'--config',
			$configPath,
		] );

		$this->assertMatchesEnvUpSnapshot( $raw );

		// Clean up
		unlink( $configPath );
	}

	/* ================================================================
	 * 1. CLI scalars beat both qit.json & profile defaults
	 * ============================================================== */
	public function test_cli_overrides_config_and_profile_scalars(): void {
		// qit.json fixture ── profile + env defaults
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'sut'          => [
				'slug'   => 'woocommerce',
				'type'   => 'plugin',
				'source' => [ 'type' => 'wporg' ],
			],
			'test_types'   => [
				'e2e' => [
					'default' => [ 'php' => '8.0', 'wp' => '6.1' ],
				],
			],
			'environments' => [
				'default' => [ 'php' => '7.4', 'wp' => '5.9' ],
			],
		] ) );

		$stdout = qit_run_e2e( [
			'run:e2e',
			'woocommerce',          // positional SUT slug
			'--config',
			$cfg,
			'--json',
			'--php',
			'8.2',       // CLI must win
			'--wp',
			'6.3',
		] );

		$payload = json_decode( $stdout, true, 512, JSON_THROW_ON_ERROR );
		$this->assertSame( '8.2', $payload['php_version'] );
		$this->assertSame( '6.3', $payload['wordpress_version'] );

		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $cfg );
	}

	/* ================================================================
		file_put_contents( $cfg, json_encode( [
			'sut' => [
				'slug'   => 'woocommerce',
				'type'   => 'plugin',
				'source' => [ 'type' => 'wporg' ],
			],
			'test_types' => [
				'e2e' => [
					'default' => [ 'php' => '8.1', 'wp' => '6.2' ],
				],
			],
		] ) );

		$stdout = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--config',
			$cfg,
			'--json',
		] );

		$payload = json_decode( $stdout, true, 512, JSON_THROW_ON_ERROR );
		$this->assertSame( '8.1', $payload['php_version'] );
		$this->assertSame( '6.2', $payload['wordpress_version'] );

		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $cfg );
	}

	/* ================================================================
	 * 3. CLI‑provided SUT slug overrides root‑level “sut” in qit.json
	 *    (command should warn but proceed)
	 * ============================================================== */
	public function test_cli_sut_slug_overrides_qit_json_sut(): void {
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'sut' => [
				'slug'   => 'config-plugin',
				'type'   => 'plugin',
				'source' => [ 'type' => 'local', 'path' => '' . __DIR__ . '/../../data/plugins/config-plugin' ],
			],
		] ) );

		// capture stderr for the warning message
		[ $stdout, $stderr ] = qit_run_e2e(
			[
				'run:e2e',
				'woocommerce',   // ← CLI overrides the slug
				'--config',
				$cfg,
				'--json',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$payload = json_decode( $stdout, true, 512, JSON_THROW_ON_ERROR );
		$this->assertSame( 'woocommerce', $payload['sut']['slug'], 'CLI slug should override qit.json SUT' );
		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $cfg );
	}

	/* ================================================================
 * 4. CLI‑provided plugins merge with – and dedupe – qit.json plugins
 * ============================================================== */
	public function test_cli_plugins_extend_and_dedupe(): void {
		$tmp = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmp, json_encode( [
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce', 'akismet' ],
				],
			],
		] ) );

		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--config',
			$tmp,
			'--json',
			'--plugin',
			'jetpack',
			'--plugin',
			'woocommerce',   // duplicate on purpose
		] );

		$payload = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );
		$slugs   = array_map(
			static function ( $item ) {
				return is_array( $item ) ? $item['slug'] ?? null : $item;
			},
			$payload['plugins'] ?? []
		);

		$this->assertEqualsCanonicalizing(
			[ 'woocommerce', 'akismet', 'jetpack' ],
			array_filter( $slugs ),
			'Plugins list should be the union of config + CLI without duplicates.'
		);

		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $tmp );
	}

	/* ================================================================
	 * 5. Flags & tunnel options – object_cache + tunnel propagation
	 * ============================================================== */
	public function test_object_cache_and_tunnel_flags(): void {
		$tmpCfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmpCfg, json_encode( [
			'environments' => [
				'default' => [ 'object_cache' => false ],
			],
		] ) );

		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--config',
			$tmpCfg,
			'--json',
			'--object_cache',
			'--tunnel=cloudflare',
		] );

		$payload = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );

		$this->assertTrue( $payload['object_cache'] );
		$this->assertTrue( $payload['tunnel'] );
		$this->assertSame( 'cloudflare', $payload['tunnel_type'] );

		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $tmpCfg );
	}

	/* ================================================================
	 * 6. Environment variables – `--env` and `--env_file` merging
	 * ============================================================== */
	public function test_env_var_and_env_file_merging(): void {
		// prepare a .env file
		$envFile = tempnam( sys_get_temp_dir(), 'qit_env_' );
		file_put_contents( $envFile, "TOKEN=from_file\nMODE=prod\n" );

		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--json',
			'--env',
			'DEBUG=true',
			'--env_file',
			$envFile,
		] );

		$payload = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );

		$this->assertContains( 'DEBUG=true', $payload['extra']['envs'] ?? [] );
		$this->assertContains( $envFile, $payload['extra']['env_files'] ?? [] );
		$this->assertCount( 1, $payload['extra']['env_files'] ?? [] );

		$this->assertMatchesEnvUpSnapshot( $payload );
		@unlink( $envFile );
	}

	/* ================================================================
	 * 7. PHP‑extensions – CLI additions merged & deduplicated
	 * ============================================================== */
	public function test_php_extensions_merge_and_dedupe(): void {
		$tmp = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmp, json_encode( [
			'environments' => [
				'default' => [
					'php_extensions' => [ 'xdebug', 'soap' ],
				],
			],
		] ) );

		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--config',
			$tmp,
			'--json',
			'--php_extension',
			'imagick',
			'--php_extension',
			'xdebug',  // duplicate on purpose
		] );

		$data = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );

		$this->assertEqualsCanonicalizing(
			[ 'xdebug', 'soap', 'imagick' ],
			$data['php_extensions'] ?? [],
			'php_extensions should be the union of config & CLI without duplicates.',
		);

		$this->assertMatchesEnvUpSnapshot( $data );
		unlink( $tmp );
	}

	/* ================================================================
	 * 8. Volume mappings – CLI entries merged & deduplicated
	 * ============================================================== */
	public function test_volume_mappings_merge_and_dedupe(): void {
		$tmp = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmp, json_encode( [
			'environments' => [
				'default' => [
					'volumes' => [
						'/host/cache:/var/www/cache',
					],
				],
			],
		] ) );

		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--config',
			$tmp,
			'--json',
			'--volume',
			'/host/logs:/var/www/logs',
			'--volume',
			'/host/cache:/var/www/cache',  // duplicate
		] );

		$data = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );

		$this->assertEqualsCanonicalizing(
			[
				'' . __DIR__ . '/../../helpers/custom-test-mu-plugin.php:/var/www/html/wp-content/mu-plugins/custom-test-mu-plugin.php',
				'/host/cache:/var/www/cache',
				'/host/logs:/var/www/logs',
			],
			$data['volumes'] ?? [],
			'Volumes list should contain both entries, deduplicated, plus auto-injected mu-plugin.',
		);

		$this->assertMatchesEnvUpSnapshot( $data );
		unlink( $tmp );
	}

	/* ================================================================
	 * 9. `--object_cache` flag – CLI must turn it on in the payload
	 * ============================================================== */
	public function test_object_cache_flag_propagates(): void {
		$tmp = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $tmp, json_encode( [
			'environments' => [ 'default' => [ /* object_cache omitted (=false) */ ] ],
		] ) );

		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--config',
			$tmp,
			'--json',
			'--object_cache',           // <- flag we are testing
		] );

		$data = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );
		$this->assertTrue(
			$data['object_cache'],
			'CLI flag --object_cache must propagate as object_cache = true in the payload.',
		);

		$this->assertMatchesEnvUpSnapshot( $data );
		unlink( $tmp );
	}

	/* ================================================================
	 * 10. Default fall‑back – framework defaults when nothing provided
	 * ============================================================== */
	public function test_defaults_when_no_cli_or_config_values(): void {
		/* 1 · run Pre‑Command with **minimum** input */
		$out = qit_run_e2e( [
			'run:e2e',
			'woocommerce',
			'--json',
		] );

		$data = json_decode( $out, true, 512, JSON_THROW_ON_ERROR );

		/* 2 · scalar defaults declared by UpEnvironmentCommand */
		$this->assertSame( '8.2', $data['php_version'], 'Default PHP should be 8.2' );
		$this->assertSame( 'stable', $data['wordpress_version'], 'Default WP should be "stable"' );

		/* 3 · flags & tunnel defaults */
		$this->assertFalse( $data['object_cache'], 'object_cache must default to false' );
		$this->assertFalse( $data['tunnel'], 'tunnel flag must default to false' );
		$this->assertSame( 'no_tunnel', $data['tunnel_type'] );

		/* 4 · optional array fields start empty (except for MU‑plugin volume) */
		foreach ( [ 'plugins', 'themes', 'php_extensions' ] as $k ) {
			$this->assertEmpty(
				$data[ $k ],
				"$k should be empty when not provided in CLI or config.",
			);
		}
		// Volume list always contains one MU‑plugin mapping added internally;
		// ensure **only** that no user‑specified volumes are present.
		$this->assertCount(
			1,
			$data['volumes'],
			'Only the internal MU‑plugin volume should be present by default.',
		);

		/* 5 · snapshot full, normalised payload */
		$this->assertMatchesEnvUpSnapshot( $data );
	}

	/* ================================================================
	 * 11. Conflict: CLI slug beats root‑level qit.json → warning shown
	 * ============================================================== */
	public function test_cli_slug_wins_and_emits_warning(): void {
		// ── 1. qit.json declares a root‑level SUT slug ───────────────
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'sut' => [
				'slug'   => 'file-plugin',
				'type'   => 'plugin',
				'source' => [ 'type' => 'url', 'url' => 'https://example.com/file-plugin.zip' ],
			],
		] ) );

		// ── 2. Run with a *different* positional slug (CLI wins) ─────
		[ $stdout, $stderr, $exitCode ] = qit_run_e2e(
			[
				'run:e2e',
				'woocommerce',   // <— positional slug (real plugin)
				'--json',
				'--config',
				$cfg,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$payload = json_decode( $stdout, true, 512, JSON_THROW_ON_ERROR );

		// ── 3. Assertions ─────────────────────────────────────────────
		$this->assertSame( 'woocommerce', $payload['sut']['slug'] );
		$this->assertStringContainsString(
			'Using CLI slug "woocommerce" instead of qit.json value "file-plugin"',
			$stderr,
			'User must be warned about the conflict so it’s not silent.'
		);

		$this->assertMatchesEnvUpSnapshot( $payload );

		unlink( $cfg );
	}

	/* ================================================================
	 * 12. `--sut-dir` overrides *everything* (root + CLI slug)
	 * ============================================================== */
	public function test_sut_dir_flag_overrides_everything(): void {
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'sut' => [
				'slug'   => 'marketplace-plugin',
				'type'   => 'plugin',
				'source' => [ 'type' => 'url', 'url' => 'https://example.com/marketplace.zip' ],
			],
		] ) );

		$localDir = __DIR__ . '/fixtures/my-local-build'; // any path you want

		$stdout = qit_run_e2e( [
			'run:e2e',
			'marketplace-plugin',
			'--zip=' . $localDir,
			'--json',
			'--config',
			$cfg,
		] );

		$payload = json_decode( $stdout, true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( 'plugin', $payload['sut']['type'] );
		$this->assertSame(
			[ 'type' => 'dir', 'path' => $localDir ],
			$payload['sut']['source'],
			'`--sut-dir` must completely replace any other SUT source information.'
		);

		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $cfg );
	}

	/* ================================================================
	 * 13. Invalid `sut.type` triggers clean validation error
	 * ============================================================== */
	public function test_invalid_sut_type_throws(): void {
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'sut' => [
				'slug'   => 'weird-extension',
				'type'   => 'widget',   // <-- invalid
				'source' => [ 'type' => 'url', 'url' => 'https://example.com/whatever.zip' ],
			],
		] ) );

		[ , $stderr, $exitCode ] = qit_run_e2e(
			[
				'run:e2e',
				'weird-extension',
				'--json',
				'--config',
				$cfg,
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertNotEquals(
			0,
			$exitCode,
			'The command must fail fast when sut.type is not plugin|theme.'
		);
		$this->assertStringContainsString(
			'sut.type must be either “plugin” or “theme”',
			$stderr
		);

		unlink( $cfg );
	}

	/* ================================================================
	 * 14. Root‑level SUT is used when CLI omits the positional slug
	 * ============================================================== */
	public function test_root_level_sut_used_when_cli_slug_missing(): void {
		// 1 · qit.json contains a complete SUT block
		$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $cfg, json_encode( [
			'sut' => [
				'slug'   => 'file-plugin',
				'type'   => 'plugin',
				'source' => [
					'type' => 'url',
					'url'  => 'https://example.com/file-plugin.zip',
				],
			],
		] ) );

		// 2 · Run without positional slug  (user typed just “qit run:e2e”)
		$stdout = qit_run_e2e( [
			'run:e2e',
			'--json',
			'--config',
			$cfg,
		] );

		$payload = json_decode( $stdout, true, 512, JSON_THROW_ON_ERROR );

		// 3 · Assertions
		$this->assertSame( 'file-plugin', $payload['sut']['slug'] );
		$this->assertSame( 'plugin', $payload['sut']['type'] );
		$this->assertSame(
			[ 'type' => 'url', 'url' => 'https://example.com/file-plugin.zip' ],
			$payload['sut']['source']
		);

		$this->assertMatchesEnvUpSnapshot( $payload );
		unlink( $cfg );
	}

	/* ================================================================
	 * 15. Hard‑fail when neither CLI nor qit.json provide a SUT
	 * ============================================================== */
public function test_missing_sut_causes_error(): void {
// 1 · Empty qit.json (no "sut" key)
$cfg = tempnam( sys_get_temp_dir(), 'qit_test_' );
file_put_contents( $cfg, '{}' );

// 2 · Expect RuntimeException when no SUT is provided
$this->expectException( \RuntimeException::class );
$this->expectExceptionMessageMatches( '/No System.*Under.*Test.*specified/' );

// 3 · Run without positional slug  → must fail
qit_run_e2e(
[
'run:e2e',
'--json',
'--config',
$cfg,
]
);

unlink( $cfg );
}


}
