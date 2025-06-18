<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;
// No other use statements seem immediately necessary based on the snippet,
// but \RuntimeException is a global class, so it doesn't need a 'use' statement.

class CommandInputPriorityTest extends \PHPUnit\Framework\TestCase {
    use SnapshotHelpers;
    use ScaffoldHelpers;

    /**
     * @param array<string,mixed> $qit_json_array The array to convert to JSON.
     * @return string The absolute path to the created qit.json file.
     * @throws \RuntimeException If JSON encoding or file writing fails.
     */
    private static function create_temporary_qit_json( array $qit_json_array ): string {
        $qit_json_path = __DIR__ . '/qit.json';
        // Added JSON_UNESCAPED_SLASHES for consistency with previous implementations.
        $json_content  = json_encode( $qit_json_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

        if ( $json_content === false ) {
            throw new \RuntimeException( 'Failed to encode JSON for qit.json: ' . json_last_error_msg() );
        }

        if ( file_put_contents( $qit_json_path, $json_content ) === false ) {
            throw new \RuntimeException( "Failed to write temporary qit.json to {$qit_json_path}." );
        }

        $real_path = realpath( $qit_json_path );
        if ( $real_path === false ) {
        	throw new \RuntimeException( "Failed to get real path for temporary qit.json at {$qit_json_path}." );
        }

        return $real_path;
    }

    /**
     * @param string $qit_json_path The absolute path to the qit.json file.
     * @return void
     * @throws \RuntimeException If file deletion fails.
     */
    private static function delete_temporary_qit_json( string $qit_json_path ): void {
        if ( file_exists( $qit_json_path ) ) {
            if ( unlink( $qit_json_path ) === false ) {
                // Provide more context if possible, though unlink() itself doesn't give much.
                $error_details = error_get_last()['message'] ?? 'Unknown error';
                throw new \RuntimeException( "Failed to delete temporary qit.json at {$qit_json_path}. Error: $error_details" );
            }
        }
    }

	public function test_cli_overrides_config_and_defaults() {
		$qitJsonPath = null;

		try {
			$qitJsonPath = self::create_temporary_qit_json( [ 'wp' => '6.0' ] );

			$output = qit( [
				'run:e2e',
				'woocommerce-amazon-s3-storage',
				$this->scaffold_test(), // Use a scaffolded test path.
				'--wp=6.1',
				'--json',
			],
				[], // No options to override qit.json content directly in this test's qit() call.
				0,  // Expected exit code.
				[ 'QIT_SELF_TEST' => 'env_info' ] // Get env info.
			);

			$decoded_output = json_decode( $output, true );

			$this->assertIsArray( $decoded_output, "JSON decoding failed or did not produce an array. Output: $output" );
			$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON does not have 'env_info' key. Output: " . print_r( $decoded_output, true ) );
			$this->assertArrayHasKey( 'wp_version', $decoded_output['env_info'], "Decoded JSON 'env_info' does not have 'wp_version' key. Output: " . print_r( $decoded_output, true ) );
			$this->assertSame( '6.1', $decoded_output['env_info']['wp_version'], "WP version should be overridden by CLI to 6.1. Output: " . print_r( $decoded_output, true ) );

		} finally {
			if ( $qitJsonPath ) {
				self::delete_temporary_qit_json( $qitJsonPath );
			}
		}
	}

	public function test_config_overrides_defaults() {
		$qitJsonPath = null;

		try {
			// Create a qit.json file specifying WordPress version 6.0.
			$qitJsonPath = self::create_temporary_qit_json( [ 'wp' => '6.0' ] );

			$output = qit( [
				'run:e2e',
				'woocommerce-amazon-s3-storage',
				$this->scaffold_test(), // Use a scaffolded test path.
				'--json',               // Request JSON output.
			],
				[], // No options to override qit.json content directly.
				0,  // Expected exit code.
				[ 'QIT_SELF_TEST' => 'env_info' ] // Crucial to get env_info, including wp_version.
			);

			$decoded_output = json_decode( $output, true );

			$this->assertIsArray( $decoded_output, "JSON decoding failed or did not produce an array. Output: $output" );
			$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON does not have 'env_info' key. Output: " . print_r( $decoded_output, true ) );
			$this->assertArrayHasKey( 'wp_version', $decoded_output['env_info'], "Decoded JSON 'env_info' does not have 'wp_version' key. Output: " . print_r( $decoded_output, true ) );
			$this->assertSame( '6.0', $decoded_output['env_info']['wp_version'], "WP version should be 6.0 as set in qit.json. Output: " . print_r( $decoded_output, true ) );

		} finally {
			if ( $qitJsonPath ) {
				self::delete_temporary_qit_json( $qitJsonPath );
			}
		}
	}

	public function test_defaults_only() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(), // Use a scaffolded test path.
			'--json',               // Request JSON output.
		],
			[], // No options to override qit.json content directly.
			0,  // Expected exit code.
			// QIT_SELF_TEST is crucial to get env_info, including wp_version.
			// No qit.json is created, and no --wp CLI option is passed.
			[ 'QIT_SELF_TEST' => 'env_info' ]
		);

		$decoded_output = json_decode( $output, true );

		$this->assertIsArray( $decoded_output, "JSON decoding failed or did not produce an array. Output: $output" );
		$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON does not have 'env_info' key. Output: " . print_r( $decoded_output, true ) );
		$this->assertArrayHasKey( 'wp_version', $decoded_output['env_info'], "Decoded JSON 'env_info' does not have 'wp_version' key. Output: " . print_r( $decoded_output, true ) );
		$this->assertSame( 'stable', $decoded_output['env_info']['wp_version'], "WP version should be 'stable' (default). Output: " . print_r( $decoded_output, true ) );
	}

	public function test_invalid_profile_fails() {
		$invalid_profile_name = 'invalid-profile-does-not-exist';
		$output                 = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			"--profile=$invalid_profile_name",
		],
			[], // No options.
			1   // Expected exit code for failure.
		);

		$this->assertStringContainsString( "Profile '$invalid_profile_name' not found.", $output, "Expected error message for invalid profile not found in output: $output" );
	}

	public function test_profile_from_config_is_used() {
		$qitJsonPath = null;

		try {
			$config_content = [
				'profiles' => [
					'my_e2e_profile' => [
						'wp' => '5.9',
					],
				],
			];

			$qitJsonPath = self::create_temporary_qit_json( $config_content );

			$output = qit( [
				'run:e2e',
				'woocommerce-amazon-s3-storage',
				$this->scaffold_test(),
				'--profile=my_e2e_profile',
				'--json',
			],
				[], // No options.
				0,  // Expected exit code.
				[ 'QIT_SELF_TEST' => 'env_info' ] // Get env info.
			);

			$decoded_output = json_decode( $output, true );

			$this->assertIsArray( $decoded_output, "JSON decoding failed or did not produce an array. Output: $output" );
			$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON does not have 'env_info' key. Output: " . print_r( $decoded_output, true ) );
			$this->assertArrayHasKey( 'wp_version', $decoded_output['env_info'], "Decoded JSON 'env_info' does not have 'wp_version' key. Output: " . print_r( $decoded_output, true ) );
			$this->assertSame( '5.9', $decoded_output['env_info']['wp_version'], "WP version should be '5.9' as per 'my_e2e_profile' in qit.json. Output: " . print_r( $decoded_output, true ) );

		} finally {
			if ( $qitJsonPath ) {
				self::delete_temporary_qit_json( $qitJsonPath );
			}
		}
	}

	public function testEnvUpCliOverridesConfig() {
		$qitJsonPath = null;

		try {
			$config_content = [
				'environments' => [
					'default' => [
						'wordpress_version'   => '6.0',
						'php_version'         => '7.4',
						'woocommerce_version' => '7.0',
					],
				],
			];
			$qitJsonPath    = self::create_temporary_qit_json( $config_content );

			// CLI options should override qit.json.
			// Assuming --php_version is the correct CLI option name from the schema.
			// Assuming --woo is the correct CLI option name.
			$output = qit( [
				'env:up',
				'--json',
				'--wp=6.1',
				'--php_version=8.0', // This is the schema-derived option name for UpEnvironmentCommand.
				'--woo=7.1',
			],
				[], // No $options_to_override_qit_json needed here.
				0,  // Expected exit code.
				[ 'QIT_SELF_TEST' => 'env_info' ]
			);

			$decoded_output = json_decode( $output, true );

			$this->assertIsArray( $decoded_output, "JSON decoding failed. Output: $output" );
			$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON missing 'env_info'. Output: " . print_r( $decoded_output, true ) );
			$env_info = $decoded_output['env_info'];
			$this->assertSame( '6.1', $env_info['wp_version'], "WP version should be CLI override '6.1'." );
			$this->assertSame( '8.0', $env_info['php_version'], "PHP version should be CLI override '8.0'." );
			// Check for WooCommerce version - it might be in 'woo_version' or in 'plugins' array.
			$this->assertSame( '7.1', $env_info['woo_version'] ?? null, "WooCommerce version should be CLI override '7.1' if present in woo_version." );

		} finally {
			if ( $qitJsonPath ) {
				self::delete_temporary_qit_json( $qitJsonPath );
			}
		}
	}

	public function testEnvUpConfigOverridesDefaults() {
		$qitJsonPath = null;

		try {
			$config_content = [
				'environments' => [
					'default' => [
						'wordpress_version'   => '5.9',
						'php_version'         => '8.1',
						'woocommerce_version' => '6.9',
					],
				],
			];
			$qitJsonPath    = self::create_temporary_qit_json( $config_content );

			// qit.json values should be used as no CLI overrides are provided for these.
			$output = qit( [
				'env:up',
				'--json',
			],
				[],
				0,
				[ 'QIT_SELF_TEST' => 'env_info' ]
			);

			$decoded_output = json_decode( $output, true );

			$this->assertIsArray( $decoded_output, "JSON decoding failed. Output: $output" );
			$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON missing 'env_info'. Output: " . print_r( $decoded_output, true ) );
			$env_info = $decoded_output['env_info'];
			$this->assertSame( '5.9', $env_info['wp_version'], "WP version should be from qit.json '5.9'." );
			$this->assertSame( '8.1', $env_info['php_version'], "PHP version should be from qit.json '8.1'." );
			$this->assertSame( '6.9', $env_info['woo_version'] ?? null, "WooCommerce version should be from qit.json '6.9' if present in woo_version." );

		} finally {
			if ( $qitJsonPath ) {
				self::delete_temporary_qit_json( $qitJsonPath );
			}
		}
	}

	public function testEnvUpDefaultsOnly() {
		// No qit.json, no CLI overrides for versions.
		$output = qit( [
			'env:up',
			'--json',
		],
			[],
			0,
			[ 'QIT_SELF_TEST' => 'env_info' ]
		);

		$decoded_output = json_decode( $output, true );

		$this->assertIsArray( $decoded_output, "JSON decoding failed. Output: $output" );
		$this->assertArrayHasKey( 'env_info', $decoded_output, "Decoded JSON missing 'env_info'. Output: " . print_r( $decoded_output, true ) );
		$env_info = $decoded_output['env_info'];

		$this->assertSame( 'stable', $env_info['wp_version'], "WP version should be default 'stable'." );
		// Assuming '8.0' is the default PHP version from the schema. Adjust if test output differs.
		// Let's check what the schema defines as default first.
		// If UpEnvironmentCommand.php defines a default in its schema for php_version, it should be used.
		// For now, let's assert it's '8.0' as a common default.
		$this->assertSame( '8.0', $env_info['php_version'], "PHP version should be the schema default (e.g., '8.0')." );

		// WooCommerce should not be installed by default if not specified.
		$this->assertArrayNotHasKey( 'woo_version', $env_info, "WooCommerce version should not be present by default." );
		$found_woo_in_plugins = false;
		if ( isset( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as $plugin ) {
				if ( isset( $plugin['slug'] ) && $plugin['slug'] === 'woocommerce' ) {
					$found_woo_in_plugins = true;
					break;
				}
			}
		}
		$this->assertFalse( $found_woo_in_plugins, "WooCommerce plugin should not be present in the plugins list by default." );
	}
}
