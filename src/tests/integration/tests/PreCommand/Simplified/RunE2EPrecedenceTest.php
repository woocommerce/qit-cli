<?php

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\Traits\SnapshotHelpers;

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
			'--json',
			'woocommerce',
			'--config',
			$configPath,
		] );

		$this->assertMatchesEnvUpSnapshot( $raw );

		// Clean up
		unlink( $configPath );
	}
}
