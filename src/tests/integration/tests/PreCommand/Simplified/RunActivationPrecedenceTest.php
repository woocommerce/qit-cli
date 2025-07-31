<?php

namespace integration\tests\PreCommand\Simplified;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\Traits\SnapshotHelpers;
use function qit_run_remote_test;

/**
 * Test run:activation command with configuration precedence.
 */
final class RunActivationPrecedenceTest extends TestCase {
	use SnapshotHelpers;

	public function test_activation_with_default_config(): void {
		$config = [
			'test_types' => [
				'activation' => [
					'default' => [
						'wp'  => '6.0',
						'woo' => '8.0.0',
						'php' => '8.1',
					],
				],
			],
		];

		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

		$raw = qit_run_remote_test( [
			'run:activation',
			'woocommerce',
			'--config',
			$configPath,
		] );

		$options = json_decode( $raw, true );

		// Verify config values are applied
		$this->assertEquals( '6.0', $options['wp'] );
		$this->assertEquals( '8.0.0', $options['woo'] );
		$this->assertEquals( '8.1', $options['php'] );

		// Snapshot the full options for regression testing
		$this->assertMatchesRemoteTestSnapshot( $options );

		// Clean up
		unlink( $configPath );
	}

	public function test_activation_cli_overrides_config(): void {
		$config = [
			'test_types' => [
				'activation' => [
					'default' => [
						'wp'  => '6.0',
						'woo' => '8.0.0',
						'php' => '8.1',
					],
				],
			],
		];

		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

		$raw = qit_run_remote_test( [
			'run:activation',
			'woocommerce',
			'--config',
			$configPath,
			'--wp',
			'6.2',
			'--woo',
			'9.0.0',
			'--php',
			'8.2',
		] );

		$options = json_decode( $raw, true );

		// Verify CLI options override config
		$this->assertEquals( '6.2', $options['wp'] );
		$this->assertEquals( '9.0.0', $options['woo'] );
		$this->assertEquals( '8.2', $options['php'] );

		// Snapshot the full options for regression testing
		$this->assertMatchesRemoteTestSnapshot( $options );

		// Clean up
		unlink( $configPath );
	}

	public function test_activation_with_profile(): void {
		$config = [
			'test_types' => [
				'activation' => [
					'default' => [
						'wp'  => '6.0',
						'woo' => '8.0.0',
					],
					'staging' => [
						'wp'  => '6.2',
						'woo' => '8.5.0',
					],
				],
			],
		];

		$configPath = tempnam( sys_get_temp_dir(), 'qit_test_' );
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

		$raw = qit_run_remote_test( [
			'run:activation',
			'woocommerce',
			'--config',
			$configPath,
			'--profile',
			'staging',
		] );

		$options = json_decode( $raw, true );

		// Verify staging profile values are used
		$this->assertEquals( '6.2', $options['wp'] );
		$this->assertEquals( '8.5.0', $options['woo'] );

		// Snapshot the full options for regression testing
		$this->assertMatchesRemoteTestSnapshot( $options );

		// Clean up
		unlink( $configPath );
	}
}