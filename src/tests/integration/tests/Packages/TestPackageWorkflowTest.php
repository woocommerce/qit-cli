<?php

use QIT\IntegrationTests\Traits\ScaffoldHelpers;
use QIT\IntegrationTests\Traits\SnapshotHelpers;

class TestPackageWorkflowTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use SnapshotHelpers;

	/**
	 * Test the complete workflow: scaffold -> create qit.json -> run test
	 */
	public function test_complete_package_workflow_with_local_test() {
		// Step 1: Scaffold a test package
		$package_dir = sys_get_temp_dir() . '/my-test-package-' . uniqid();

		$scaffold_output = qit( [
			'package:scaffold',
			$package_dir,
			'--namespace=woocommerce',
			'--package=internal-integration-test',
			'--framework=playwright',
			'--test-type=e2e',
			'--no-interaction'
		] );

		$this->assertDirectoryExists( $package_dir );
		$this->assertFileExists( $package_dir . '/manifest.json' );

		// Step 2: Create qit.json configuration
		$qit_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/qit',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $package_dir ]
					]
				]
			],
			'environments' => [
				'without-setup' => [
					'php' => '8.2',
					'wp'  => 'stable'
				],
				'with-setup'    => [
					'php'                => '8.2',
					'wp'                 => 'stable',
					'bootstrap_packages' => [ $package_dir ]
				]
			]
		];

		$qit_json_path = $this->create_temporary_qit_json( $qit_json );

		// Step 3: Run the test with the without-setup environment
		$run_output = qit( [
			'run:e2e',
			'woocommerce',
			'--environment=without-setup',
			'--config=' . $qit_json_path
		] );

		// Verify the test ran successfully
		$this->assertStringContainsString( 'Environment ready', $run_output );
		$this->assertStringContainsString( 'Running E2E Tests', $run_output );

		// Clean up
		$this->delete_temporary_qit_json( $qit_json_path );
		$this->recursiveRemoveDirectory( $package_dir );
	}

	/**
	 * Test workflow with bootstrap packages
	 */
	public function test_workflow_with_bootstrap_environment() {
		$package_dir = sys_get_temp_dir() . '/bootstrap-test-package-' . uniqid();

		// Scaffold
		qit( [
			'package:scaffold',
			$package_dir,
			'--namespace=woocommerce',
			'--package=internal-integration-test',
			'--framework=playwright',
			'--test-type=e2e',
			'--no-interaction'
		] );

		// Create qit.json with bootstrap configuration
		$qit_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/qit',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $package_dir ]
					]
				]
			],
			'environments' => [
				'with-setup' => [
					'php'                => '8.2',
					'wp'                 => 'stable',
					'bootstrap_packages' => [ $package_dir ]
				]
			]
		];

		$qit_json_path = $this->create_temporary_qit_json( $qit_json );

		// Run with bootstrap environment
		$run_output = qit( [
			'run:e2e',
			'woocommerce',
			'--environment=with-setup',
			'--config=' . $qit_json_path
		] );

		$this->assertStringContainsString( 'Bootstrapping', $run_output );

		// Clean up
		$this->delete_temporary_qit_json( $qit_json_path );
		$this->recursiveRemoveDirectory( $package_dir );
	}

	/**
	 * Test package publish workflow (if publishing is available)
	 */
	public function test_package_publish_workflow() {
		$package_dir = sys_get_temp_dir() . '/publish-test-package-' . uniqid();

		// Scaffold
		qit( [
			'package:scaffold',
			$package_dir,
			'--namespace=woocommerce',
			'--package=internal-integration-test',
			'--framework=playwright',
			'--test-type=e2e',
			'--no-interaction'
		] );

		// Test publish command (dry run or with --force if needed)
		$publish_output = qit( [
			'package:publish',
			$package_dir,
			'--force' // Use force to avoid interactive prompts
		] );

		// Verify publish process started (exact assertions depend on implementation)
		$this->assertStringContainsString( 'Publishing', $publish_output );

		$this->recursiveRemoveDirectory( $package_dir );
	}

	/**
	 * Test multiple test packages in single configuration
	 */
	public function test_multiple_test_packages_workflow() {
		$package1_dir = sys_get_temp_dir() . '/test-package-1-' . uniqid();
		$package2_dir = sys_get_temp_dir() . '/test-package-2-' . uniqid();

		// Scaffold two packages
		qit( [
			'package:scaffold',
			$package1_dir,
			'--namespace=woocommerce',
			'--package=internal-integration-test-1',
			'--framework=playwright',
			'--test-type=e2e',
			'--no-interaction'
		] );

		qit( [
			'package:scaffold',
			$package2_dir,
			'--namespace=woocommerce',
			'--package=internal-integration-test-2',
			'--framework=playwright',
			'--test-type=e2e',
			'--no-interaction'
		] );

		// Create qit.json with multiple test packages
		$qit_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/qit',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'woocommerce',
				'source' => [ 'type' => 'wporg' ]
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => [ $package1_dir, $package2_dir ]
					]
				]
			],
			'environments' => [
				'multi-test' => [
					'php' => '8.2',
					'wp'  => 'stable'
				]
			]
		];

		$qit_json_path = $this->create_temporary_qit_json( $qit_json );

		// Run tests
		$run_output = qit( [
			'run:e2e',
			'woocommerce',
			'--environment=multi-test',
			'--config=' . $qit_json_path
		] );

		// Verify both packages were processed
		$this->assertStringContainsString( 'test-package-1', $run_output );
		$this->assertStringContainsString( 'test-package-2', $run_output );

		// Clean up
		$this->delete_temporary_qit_json( $qit_json_path );
		$this->recursiveRemoveDirectory( $package1_dir );
		$this->recursiveRemoveDirectory( $package2_dir );
	}

	/**
	 * Helper methods
	 */
	private function create_temporary_qit_json( array $qit_json_array ): string {
		$qit_json_path = sys_get_temp_dir() . '/qit-' . uniqid() . '.json';

		$json_content = json_encode( $qit_json_array, JSON_PRETTY_PRINT );
		if ( $json_content === false ) {
			throw new \RuntimeException( 'Failed to encode JSON for qit.json: ' . json_last_error_msg() );
		}

		if ( ! file_put_contents( $qit_json_path, $json_content ) ) {
			throw new \RuntimeException( "Failed to create temporary qit.json file at $qit_json_path." );
		}

		return $qit_json_path;
	}

	private function delete_temporary_qit_json( string $qit_json_path ): void {
		if ( file_exists( $qit_json_path ) ) {
			if ( ! unlink( $qit_json_path ) ) {
				$error_details = error_get_last()['message'] ?? 'Unknown error';
				throw new \RuntimeException( "Failed to delete temporary qit.json file at $qit_json_path. Error: $error_details" );
			}
		}
	}

	private function recursiveRemoveDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursiveRemoveDirectory( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}