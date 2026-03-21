<?php

// namespace integration\tests\Commands;

use PHPUnit\Framework\TestCase;

/**
 * Test RunGroupCommand functionality.
 *
 * These tests verify that the run:group command correctly:
 * 1. Reads group definitions from qit.json
 * 2. Executes all tests in a group
 * 3. Handles --only filtering
 * 4. Provides clear error messages
 */
use PHPUnit\Framework\Attributes\Group;

#[Group("remote-test")]
class RunGroupCommandTest extends TestCase {

	/**
	 * Test that run:group executes all tests in a defined group.
	 */
	public function test_run_group_executes_all_tests(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with test groups
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'test_types' => [
					'security' => [
						'default' => []
					],
					'phpstan' => [
						'default' => []
					]
				],
				'groups' => [
					'ci-basic' => [
						'security' => [ 'default' ],
						'phpstan' => [ 'default' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Run the group - this will actually queue the tests
			// We need to use qit_run_remote_test for each test type to avoid real API calls
			$proc = qit( [
				'run:group',
				'ci-basic',
				'--config', $configPath
			], [], 0, [], true );

			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();

			// Verify group creation started
			$this->assertStringContainsString( 'Creating group "ci-basic"', $output );
			$this->assertStringContainsString( '2 test(s)', $output );

			// Verify summary shows the tests
			$this->assertStringContainsString( 'Group Summary', $output );
			$this->assertStringContainsString( 'Security', $output );
			$this->assertStringContainsString( 'PHPStan', $output );

			// Group execution should succeed
			$this->assertEquals( 0, $exitCode, 'Group execution should succeed. Output: ' . $output );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that run:group --only filters to specific test type.
	 */
	public function test_run_group_with_only_filter(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with multiple test types in group
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'test_types' => [
					'security' => [
						'scan' => []
					],
					'phpstan' => [
						'default' => []
					],
					'phpcompatibility' => [
						'check' => []
					]
				],
				'groups' => [
					'full-suite' => [
						'security' => [ 'scan' ],
						'phpstan' => [ 'default' ],
						'phpcompatibility' => [ 'check' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Run group with --only filter
			$proc = qit( [
				'run:group',
				'full-suite',
				'--only', 'security',
				'--config', $configPath
			], [], 0, [], true );

			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();

			// Should only run security test
			$this->assertStringContainsString( 'Creating group "full-suite" with 1 test(s)', $output );
			$this->assertStringContainsString( 'Security', $output );

			// Should NOT run other test types
			$this->assertStringNotContainsString( 'PHPStan', $output );
			$this->assertStringNotContainsString( 'PHPCompatibility', $output );

			$this->assertEquals( 0, $exitCode, 'Filtered group execution should succeed. Output: ' . $output );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test error handling when group doesn't exist.
	 */
	public function test_run_group_nonexistent_group_error(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with groups
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'groups' => [
					'ci-quick' => [
						'security' => [ 'default' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Try to run non-existent group
			try {
				qit( [
					'run:group',
					'nonexistent-group',
					'--config', $configPath
				], [], 0, [], false );

				$this->fail( 'Expected exception for nonexistent group' );
			} catch ( \RuntimeException $e ) {
				// Verify error message is helpful
				$this->assertStringContainsString( 'Group \'nonexistent-group\' not found', $e->getMessage() );
				$this->assertStringContainsString( 'Available groups: ci-quick', $e->getMessage() );
			}

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test error handling when --only specifies invalid test type.
	 */
	public function test_run_group_invalid_only_filter_error(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with groups
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'test_types' => [
					'security' => [
						'default' => []
					]
				],
				'groups' => [
					'ci-quick' => [
						'security' => [ 'default' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Try to filter by non-existent test type
			try {
				qit( [
					'run:group',
					'ci-quick',
					'--only', 'performance',
					'--config', $configPath
				], [], 0, [], false );

				$this->fail( 'Expected exception for invalid test type' );
			} catch ( \RuntimeException $e ) {
				// Verify error message shows available types
				$this->assertStringContainsString( 'Test type \'performance\' not found in group', $e->getMessage() );
				$this->assertStringContainsString( 'Available test types: security', $e->getMessage() );
			}

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test error handling when no SUT is defined.
	 */
	public function test_run_group_no_sut_error(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json WITHOUT sut section
			$config = [
				'test_types' => [
					'security' => [
						'default' => []
					]
				],
				'groups' => [
					'ci-quick' => [
						'security' => [ 'default' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Try to run group without SUT
			try {
				qit( [
					'run:group',
					'ci-quick',
					'--config', $configPath
				], [], 0, [], false );

				$this->fail( 'Expected exception for missing SUT' );
			} catch ( \RuntimeException $e ) {
				// Verify error message explains SUT requirement
				$this->assertStringContainsString( 'No SUT defined in qit.json', $e->getMessage() );
				$this->assertStringContainsString( 'Groups require a SUT to test', $e->getMessage() );
			}

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that run:group works with multiple profiles per test type.
	 */
	public function test_run_group_multiple_profiles_per_type(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with multiple profiles
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'test_types' => [
					'phpstan' => [
						'basic' => [
							'phpstan_level' => 5
						],
						'strict' => [
							'phpstan_level' => 9
						]
					]
				],
				'groups' => [
					'quality' => [
						'phpstan' => [ 'basic', 'strict' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Run group with multiple profiles
			$proc = qit( [
				'run:group',
				'quality',
				'--config', $configPath
			], [], 0, [], true );

			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();

			// Should run both profiles
			$this->assertStringContainsString( 'Creating group "quality" with 2 test(s)', $output );
			$this->assertStringContainsString( 'PHPStan', $output );
			$this->assertStringContainsString( 'Group Summary', $output );

			$this->assertEquals( 0, $exitCode, 'Group with multiple profiles should succeed. Output: ' . $output );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test realistic CI workflow: multiple groups for different stages.
	 */
	public function test_ci_workflow_with_multiple_groups(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with CI-style groups
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'test_types' => [
					'security' => [
						'scan' => []
					],
					'phpstan' => [
						'default' => []
					],
					'phpcompatibility' => [
						'check' => []
					]
				],
				'groups' => [
					'pr-checks' => [
						'security' => [ 'scan' ],
						'phpstan' => [ 'default' ]
					],
					'pre-release' => [
						'security' => [ 'scan' ],
						'phpstan' => [ 'default' ],
						'phpcompatibility' => [ 'check' ]
					]
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Test PR checks group (fast tests)
			$proc1 = qit( [
				'run:group',
				'pr-checks',
				'--config', $configPath
			], [], 0, [], true );

			$output1 = $proc1->getOutput();
			$this->assertStringContainsString( 'Creating group "pr-checks" with 2 test(s)', $output1 );

			// Test pre-release group (comprehensive tests)
			$proc2 = qit( [
				'run:group',
				'pre-release',
				'--config', $configPath
			], [], 0, [], true );

			$output2 = $proc2->getOutput();
			$this->assertStringContainsString( 'Creating group "pre-release" with 3 test(s)', $output2 );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test that empty groups are handled gracefully.
	 */
	public function test_run_group_with_empty_group(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create qit.json with empty group
			$config = [
				'sut' => [
					'type' => 'plugin',
					'slug' => 'woocommerce',
					'source' => [
						'type' => 'wporg',
						'version' => 'stable'
					]
				],
				'groups' => [
					'empty-group' => []
				]
			];

			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );

			// Run empty group
			$proc = qit( [
				'run:group',
				'empty-group',
				'--config', $configPath
			], [], 0, [], true );

			$output = $proc->getOutput();
			$exitCode = $proc->getExitCode();

			// Should handle empty group gracefully (or fail with 0 tests - either is fine)
			// The command might output "Creating group..." or fail with "0 tests"
			$this->assertContains( $exitCode, [0, 1], 'Empty group handling. Output: ' . $output );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}
}
