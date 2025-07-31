<?php

namespace integration\tests\PreCommand\Simplified;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\Traits\SnapshotHelpers;

final class RemoteTestPrecedenceTest extends TestCase {
	use SnapshotHelpers;

	/* ================================================================
	 * 1. CLI overrides config for environment-aware commands
	 * ============================================================== */

	public function test_cli_overrides_config_for_compatibility_test(): void {
		/* ---------- 1. Fixture: qit.json on disk ---------- */
		$config = [
			'test_types' => [
				'compatibility' => [
					'default' => [
						'wordpress_version'   => '6.0',
						'woocommerce_version' => '8.0.0',
					],
				],
			],
		];
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config ) );

		/* ---------- 2. Execute CLI command with options early return ---------- */
		$raw = qit_run_remote_test( [
'run:compatibility',
'woocommerce',
'--config',
$configPath,
'--wordpress_version',
'6.2',              // should beat config
'--woocommerce_version',
'9.0.0',            // should beat config
] );

		$options = json_decode( $raw, true );

		// Verify CLI options override config
		$this->assertEquals( '6.2', $options['wordpress_version'] );
		$this->assertEquals( '9.0.0', $options['woocommerce_version'] );

		// Snapshot the full options for regression testing
		$this->assertMatchesRemoteTestSnapshot( $options );

		// Clean up
		unlink( $configPath );
	}

	/* ================================================================
	 * 2. SUT slug vs numeric‑ID resolution
	 * ============================================================== */

	public function test_sut_resolution_for_remote_test(): void {
		$raw = qit_run_remote_test( [
'run:security',
'woocommerce',  // SUT slug
] );

		$options = json_decode( $raw, true );

		// Verify SUT was resolved to woo_id
		$this->assertArrayHasKey( 'woo_id', $options );
		$this->assertIsInt( $options['woo_id'] );

		// Verify event is set for published extension test
		$this->assertEquals( 'cli_published_extension_test', $options['event'] );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	public function test_numeric_sut_id_passthrough(): void {
		$raw = qit_run_remote_test( [
'run:security',
'12345',  // Numeric SUT ID
] );

		$options = json_decode( $raw, true );

		// Verify numeric ID is passed through
		$this->assertEquals( '12345', (string) $options['woo_id'] );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	/* ================================================================
	 * 3. ZIP upload with valid plugin structure
	 * ============================================================== */

	public function test_zip_upload_option_for_remote_test(): void {
		// Use existing valid plugin ZIP from test data
		$zipPath = __DIR__ . '/../../../data/plugins/my-plugin.zip';
		$this->assertFileExists( $zipPath, 'Test plugin ZIP should exist in test data' );

		$raw = qit_run_remote_test( [
'run:security',
'woocommerce',
'--zip',
$zipPath,
] );

		$options = json_decode( $raw, true );

		// Verify upload_id is set and zip is removed from options
		$this->assertArrayHasKey( 'upload_id', $options );
		$this->assertArrayNotHasKey( 'zip', $options );

		// Verify event is set for development extension test
		$this->assertEquals( 'cli_development_extension_test', $options['event'] );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	/* ================================================================
	 * 4. Additional plugins conversion (correct option name)
	 * ============================================================== */

	public function test_additional_plugins_conversion(): void {
		$raw = qit_run_remote_test( [
'run:phpstan',  // phpstan supports additional_plugins
'woocommerce',
'--additional_plugins',  // Correct option name
'woocommerce-subscriptions,woocommerce-bookings',
] );

		$options = json_decode( $raw, true );

		// Verify additional_plugins are present (they get converted internally)
		$this->assertArrayHasKey( 'additional_woo_plugins', $options );
		$this->assertIsString( $options['additional_woo_plugins'] );

		// Should be comma-separated IDs
		$ids = explode( ',', $options['additional_woo_plugins'] );
		foreach ( $ids as $id ) {
			$this->assertIsNumeric( trim( $id ) );
		}

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	/* ================================================================
	 * 5. Profile precedence for environment-aware commands
	 * ============================================================== */

	public function test_profile_precedence_for_remote_test(): void {
		$config = [
			'test_types' => [
				'compatibility' => [
					'default' => [
						'wordpress_version'   => '6.1',
						'woocommerce_version' => '8.0.0',
					],
					'staging' => [
						'wordpress_version'   => '6.2',
						'woocommerce_version' => '8.5.0',
					],
				],
			],
		];
		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config ) );

		$raw = qit_run_remote_test( [
'run:compatibility',
'woocommerce',
'--config',
$configPath,
'--profile',
'staging',
] );

		$options = json_decode( $raw, true );

		// Verify staging profile values are used
		$this->assertEquals( '6.2', $options['wordpress_version'] );
		$this->assertEquals( '8.5.0', $options['woocommerce_version'] );

		$this->assertMatchesRemoteTestSnapshot( $options );

		unlink( $configPath );
	}

	/* ================================================================
	 * 6. Error handling
	 * ============================================================== */

	public function test_missing_sut_causes_error(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessageMatches( '/No System.*Under.*Test.*specified/' );

		qit_run_remote_test( [
'run:security',
// No SUT provided
], [], 1 ); // Expect exit code 1
	}

	/* ================================================================
	 * 7. Different test types with their supported options
	 * ============================================================== */

	public function test_phpstan_with_version_options(): void {
		$raw = qit_run_remote_test( [
'run:phpstan',
'woocommerce',
'--wordpress_version',
'6.2',
'--woocommerce_version',
'9.0.0',
] );

		$options = json_decode( $raw, true );

		// Verify options are set
		$this->assertEquals( '6.2', $options['wordpress_version'] );
		$this->assertEquals( '9.0.0', $options['woocommerce_version'] );

		// Verify SUT resolution
		$this->assertArrayHasKey( 'woo_id', $options );
		$this->assertIsInt( $options['woo_id'] );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	public function test_woo_e2e_with_version_options(): void {
		$raw = qit_run_remote_test( [
'run:woo-e2e',
'woocommerce',
'--wordpress_version',
'6.2',
'--woocommerce_version',
'9.0.0',
] );

		$options = json_decode( $raw, true );

		// Verify options are set
		$this->assertEquals( '6.2', $options['wordpress_version'] );
		$this->assertEquals( '9.0.0', $options['woocommerce_version'] );

		// Verify SUT resolution
		$this->assertArrayHasKey( 'woo_id', $options );
		$this->assertIsInt( $options['woo_id'] );

		// Verify event is set for published extension test
		$this->assertEquals( 'cli_published_extension_test', $options['event'] );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	public function test_security_test_basic_options(): void {
		$raw = qit_run_remote_test( [
'run:security',
'woocommerce',
] );

		$options = json_decode( $raw, true );

		// Verify SUT resolution
		$this->assertArrayHasKey( 'woo_id', $options );
		$this->assertIsInt( $options['woo_id'] );

		// Security tests don't support version options, so they shouldn't be present
		$this->assertArrayNotHasKey( 'wordpress_version', $options );
		$this->assertArrayNotHasKey( 'woocommerce_version', $options );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}

	public function test_malware_test_basic_options(): void {
		$raw = qit_run_remote_test( [
'run:malware',
'woocommerce',
] );

		$options = json_decode( $raw, true );

		// Verify SUT resolution
		$this->assertArrayHasKey( 'woo_id', $options );
		$this->assertIsInt( $options['woo_id'] );

		$this->assertMatchesRemoteTestSnapshot( $options );
	}
}
