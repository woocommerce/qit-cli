<?php

namespace integration\tests\TestPackages\Network;
/**
 * Integration tests for network requirements feature.
 */
class NetworkRequirementsTest extends \PHPUnit\Framework\TestCase {

	protected function tearDown(): void {
		qit( [ 'env:down' ] );
		parent::tearDown();
	}

	/**
	 * Helper to create a test package with specified network requirement.
	 */
	private function create_test_package( string $name, ?bool $requires_network = null ): string {
		// Use system temp dir with unique name - let OS clean up
		$test_dir = sys_get_temp_dir() . '/qit-network-test-' . $name . '-' . uniqid();
		mkdir( $test_dir, 0755, true );
		
		// Create a bash script that tests network connectivity and outputs CTRF
		$test_script = '#!/bin/bash
mkdir -p ./results
mkdir -p ./blob-report

# Test network connectivity
echo "Testing network connectivity..."
if curl -s --max-time 5 https://example.com > /dev/null 2>&1; then
    NETWORK_STATUS="online"
    TEST_STATUS="passed"
    echo "✓ Network is available"
else
    NETWORK_STATUS="offline"
    TEST_STATUS="passed"
    echo "✗ Network is blocked"
fi

# Generate CTRF based on results
cat > ./results/ctrf.json << EOF
{
  "results": {
    "tool": {
      "name": "network-test"
    },
    "summary": {
      "tests": 1,
      "passed": 1,
      "failed": 0,
      "skipped": 0,
      "pending": 0,
      "other": 0,
      "start": $(date +%s000),
      "stop": $(($(date +%s000) + 100))
    },
    "tests": [
      {
        "name": "Network connectivity check",
        "status": "$TEST_STATUS",
        "duration": 100,
        "message": "Network is $NETWORK_STATUS"
      }
    ]
  }
}
EOF

# Create blob report
echo "Network test completed: $NETWORK_STATUS" > test.txt
zip -q ./blob-report/report.zip test.txt
rm test.txt

echo "Test completed successfully"
';
		
		file_put_contents( $test_dir . '/test-network.sh', $test_script );
		chmod( $test_dir . '/test-network.sh', 0755 );
		
		// Create a minimal test package manifest
		$manifest = [
			'package' => 'test/' . $name,
			'test_type' => 'e2e',
			'description' => 'Test package for network requirements',
			'test' => [
				'phases' => [
					'run' => [
						'host: ./test-network.sh'
					]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './blob-report'
				]
			]
		];
		
		// Set network requirement if specified
		if ( $requires_network !== null ) {
			$manifest['requires_network'] = $requires_network;
		}
		// If null, don't add the field at all to test default behavior
		
		file_put_contents( $test_dir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );
		
		return $test_dir;
	}

	/**
	 * Test that packages without requires_network default to offline.
	 */
	public function test_default_is_offline() {
		$test_dir = $this->create_test_package( 'default-offline-' . uniqid() );

		// Create config file with test packages in environment
		$config = [
			'environments' => [
				'default' => [
					'test_packages' => [ $test_dir ]
				]
			]
		];
		$config_file = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config_file, json_encode( $config, JSON_PRETTY_PRINT ) );

		// Use env:up to check network settings
		$output = qit( [
			'env:up',
			'--config=' . $config_file,
			'--json'
		] );

		$json = json_decode( $output, true );

		// Should run in offline mode (QIT_NETWORK_RESTRICTION = true)
		$this->assertEquals(
			'true',
			$json['envs']['QIT_NETWORK_RESTRICTION'] ?? 'false',
			'Package without requires_network should default to offline'
		);

		// No cleanup needed - OS will handle temp dir
	}

	/**
	 * Test that packages with requires_network=false run offline.
	 */
	public function test_explicit_offline() {
		$test_dir = $this->create_test_package( 'explicit-offline-' . uniqid(), false );

		// Create config file with test packages in environment
		$config = [
			'environments' => [
				'default' => [
					'test_packages' => [ $test_dir ]
				]
			]
		];
		$config_file = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config_file, json_encode( $config, JSON_PRETTY_PRINT ) );

		$output = qit( [
			'env:up',
			'--config=' . $config_file,
			'--json'
		] );

		$json = json_decode( $output, true );

		// Should run in offline mode
		$this->assertEquals(
			'true',
			$json['envs']['QIT_NETWORK_RESTRICTION'] ?? 'false',
			'Package with requires_network=false should run offline'
		);

		// No cleanup needed - OS will handle temp dir
	}

	/**
	 * Test that packages with requires_network=true get network access.
	 */
	public function test_requires_network_enables_network() {
		$test_dir = $this->create_test_package( 'requires-network-' . uniqid(), true );

		// Create config file with test packages in environment
		$config = [
			'environments' => [
				'default' => [
					'test_packages' => [ $test_dir ]
				]
			]
		];
		$config_file = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config_file, json_encode( $config, JSON_PRETTY_PRINT ) );

		$output = qit( [
			'env:up',
			'--config=' . $config_file,
			'--json'
		] );

		$json = json_decode( $output, true );

		// Should run in online mode (network_restriction = false)
		$this->assertEquals(
			'false',
			$json['envs']['QIT_NETWORK_RESTRICTION'] ?? 'true',
			'Package with requires_network=true should get network access'
		);

		// No cleanup needed - OS will handle temp dir
	}

	/**
	 * Test that --offline flag blocks packages requiring network.
	 */
	public function test_offline_flag_blocks_network_packages() {
		$test_dir = $this->create_test_package( 'needs-network-' . uniqid(), true );

		// This should fail
		$exit_code = 0;
		try {
			qit( [
				'run:e2e',
				'woocommerce',
				'--test-package=' . $test_dir,
				'--offline'
			] );
		} catch ( \Exception $e ) {
			$exit_code     = 1;
			$error_message = $e->getMessage();
		}

		$this->assertEquals( 1, $exit_code, 'Should fail when forcing offline with network-requiring package' );
		$this->assertStringContainsString(
			'Cannot run in offline mode',
			$error_message ?? '',
			'Should have clear error message'
		);

		// No cleanup needed - OS will handle temp dir
	}

	/**
	 * Test that --online flag forces network for offline packages.
	 */
	public function test_online_flag_forces_network() {
		$test_dir = $this->create_test_package( 'offline-forced-' . uniqid(), false );

		// Create config file with test packages in environment
		$config = [
			'environments' => [
				'default' => [
					'test_packages' => [ $test_dir ]
				]
			]
		];
		$config_file = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config_file, json_encode( $config, JSON_PRETTY_PRINT ) );

		$output = qit( [
			'env:up',
			'--config=' . $config_file,
			'--online',
			'--json'
		] );

		$json = json_decode( $output, true );

		// Should run in online mode (forced)
		$this->assertEquals(
			'false',
			$json['envs']['QIT_NETWORK_RESTRICTION'] ?? 'true',
			'--online flag should force network access'
		);

		// No cleanup needed - OS will handle temp dir
	}

	/**
	 * Test mixed packages - if any requires network, all get network.
	 */
	public function test_mixed_packages_enable_network() {
		$offline_dir = $this->create_test_package( 'offline-pkg-' . uniqid(), false );
		$online_dir  = $this->create_test_package( 'online-pkg-' . uniqid(), true );

		// Create config file with test packages in environment
		$config = [
			'environments' => [
				'default' => [
					'test_packages' => [ $offline_dir, $online_dir ]
				]
			]
		];
		$config_file = sys_get_temp_dir() . '/qit-config-' . uniqid() . '.json';
		file_put_contents( $config_file, json_encode( $config, JSON_PRETTY_PRINT ) );

		$output = qit( [
			'env:up',
			'--config=' . $config_file,
			'--json'
		] );

		$json = json_decode( $output, true );

		// Should run in online mode (because one package requires it)
		$this->assertEquals(
			'false',
			$json['envs']['QIT_NETWORK_RESTRICTION'] ?? 'true',
			'Mixed packages should enable network when any requires it'
		);

		// No cleanup needed - OS will handle temp dir
	}
}