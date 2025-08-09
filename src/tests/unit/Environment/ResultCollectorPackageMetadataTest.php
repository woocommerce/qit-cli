<?php

namespace QIT_CLI_Tests\Environment;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\ResultCollector;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\NodeDependencyManager;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Test that ResultCollector properly adds package metadata to CTRF reports
 */
class ResultCollectorPackageMetadataTest extends TestCase {

	private string $temp_dir;
	private ResultCollector $collector;

	protected function setUp(): void {
		parent::setUp();
		
		// Create temp directory for test artifacts
		$this->temp_dir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		mkdir( $this->temp_dir, 0755, true );
		
		// Create ResultCollector with mocked dependencies
		$docker = $this->createMock( Docker::class );
		$node_deps = $this->createMock( NodeDependencyManager::class );
		$this->collector = new ResultCollector( $docker, $node_deps );
	}

	protected function tearDown(): void {
		// Clean up temp directory
		if ( is_dir( $this->temp_dir ) ) {
			$this->rrmdir( $this->temp_dir );
		}
		parent::tearDown();
	}

	/**
	 * Test that package metadata is added to CTRF report
	 */
	public function test_adds_package_metadata_to_ctrf(): void {
		// Create a sample CTRF report without metadata
		$ctrf = [
			'reportFormat' => 'CTRF',
			'specVersion' => '1.0.0',
			'results' => [
				'tool' => [
					'name' => 'playwright',
					'version' => '1.0.0'
				],
				'summary' => [
					'tests' => 5,
					'passed' => 4,
					'failed' => 1,
					'skipped' => 0,
					'pending' => 0,
					'other' => 0,
					'start' => 1000000,
					'stop' => 1000100
				],
				'tests' => [
					[
						'name' => 'Test 1',
						'status' => 'passed',
						'duration' => 100,
						'extra' => [
							'packageSlug' => 'woocommerce/checkout-tests:1.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'woocommerce'
						]
					],
					[
						'name' => 'Test 2',
						'status' => 'passed',
						'duration' => 150,
						'extra' => [
							'packageSlug' => 'woocommerce/checkout-tests:1.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'woocommerce'
						]
					],
					[
						'name' => 'Test 3',
						'status' => 'failed',
						'duration' => 200,
						'extra' => [
							'packageSlug' => 'woocommerce/payment-tests:2.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'woocommerce'
						]
					],
					[
						'name' => 'Setup lifecycle',
						'status' => 'passed',
						'duration' => 50,
						'extra' => [
							'packageSlug' => 'woocommerce/utility-setup:1.0.0',
							'phase' => 'setup',
							'testType' => 'e2e',
							'namespace' => 'woocommerce'
						]
					]
				]
			]
		];

		// Write CTRF to file
		$ctrf_path = $this->temp_dir . '/ctrf-report.json';
		file_put_contents( $ctrf_path, json_encode( $ctrf, JSON_PRETTY_PRINT ) );

		// Set up tracking data
		$this->collector->reset_tracking();
		
		// Use reflection to set private tracking properties
		$reflection = new \ReflectionClass( $this->collector );
		
		$blob_tracking = $reflection->getProperty( 'blob_tracking' );
		$blob_tracking->setAccessible( true );
		$blob_tracking->setValue( $this->collector, [
			'total_packages' => 3,
			'packages_with_blob' => 2,
			'packages_without_blob' => [ 'utility-setup:1.0.0' ]  // Use the basename format that matches
		] );
		
		$allure_tracking = $reflection->getProperty( 'allure_tracking' );
		$allure_tracking->setAccessible( true );
		$allure_tracking->setValue( $this->collector, [
			'total_packages' => 3,
			'packages_with_allure' => 2,  // Only the test packages have allure
			'packages_without_allure' => [ 'utility-setup:1.0.0' ]
		] );

		// Call the private method using reflection
		$method = $reflection->getMethod( 'add_package_metadata_to_merged_ctrf' );
		$method->setAccessible( true );
		$method->invoke( $this->collector, $ctrf_path );

		// Read the enhanced CTRF
		$enhanced_ctrf = json_decode( file_get_contents( $ctrf_path ), true );

		// Verify metadata was added
		$this->assertArrayHasKey( 'extra', $enhanced_ctrf['results'] );
		$this->assertArrayHasKey( 'qitPackageMetadata', $enhanced_ctrf['results']['extra'] );

		$metadata = $enhanced_ctrf['results']['extra']['qitPackageMetadata'];

		// Check version
		$this->assertEquals( '1.0.0', $metadata['version'] );

		// Check packages array
		$this->assertArrayHasKey( 'packages', $metadata );
		$this->assertCount( 3, $metadata['packages'] );

		// Verify package details
		$packages_by_id = [];
		foreach ( $metadata['packages'] as $pkg ) {
			$packages_by_id[ $pkg['packageId'] ] = $pkg;
		}

		// Check checkout-tests package
		$this->assertArrayHasKey( 'woocommerce/checkout-tests:1.0.0', $packages_by_id );
		$checkout = $packages_by_id['woocommerce/checkout-tests:1.0.0'];
		$this->assertEquals( 'woocommerce', $checkout['namespace'] );
		$this->assertEquals( 'e2e', $checkout['testType'] );
		$this->assertTrue( $checkout['hasRunPhase'] );
		$this->assertEquals( 2, $checkout['testCount'] );
		$this->assertTrue( $checkout['hasBlobReport'] );
		$this->assertTrue( $checkout['hasAllureReport'] );

		// Check payment-tests package
		$this->assertArrayHasKey( 'woocommerce/payment-tests:2.0.0', $packages_by_id );
		$payment = $packages_by_id['woocommerce/payment-tests:2.0.0'];
		$this->assertEquals( 1, $payment['testCount'] );
		$this->assertTrue( $payment['hasBlobReport'] );
		$this->assertTrue( $payment['hasAllureReport'] );

		// Check utility-setup package
		$this->assertArrayHasKey( 'woocommerce/utility-setup:1.0.0', $packages_by_id );
		$utility = $packages_by_id['woocommerce/utility-setup:1.0.0'];
		$this->assertFalse( $utility['hasRunPhase'] );
		$this->assertEquals( 0, $utility['testCount'] );
		$this->assertFalse( $utility['hasBlobReport'] );
		$this->assertFalse( $utility['hasAllureReport'] );

		// Check summary
		$this->assertArrayHasKey( 'summary', $metadata );
		$this->assertEquals( 3, $metadata['summary']['totalPackages'] );
		$this->assertEquals( 2, $metadata['summary']['packagesWithTests'] );
		$this->assertEquals( 1, $metadata['summary']['utilityPackages'] );

		// Check report completeness
		$this->assertArrayHasKey( 'reportCompleteness', $metadata );
		
		// Blob completeness
		$this->assertArrayHasKey( 'blob', $metadata['reportCompleteness'] );
		$this->assertTrue( $metadata['reportCompleteness']['blob']['complete'] ); // All test packages have blobs
		$this->assertEquals( 2, $metadata['reportCompleteness']['blob']['packagesWithBlob'] );
		$this->assertEquals( 2, $metadata['reportCompleteness']['blob']['totalPackagesWithTests'] );
		$this->assertEquals( [ 'utility-setup:1.0.0' ], $metadata['reportCompleteness']['blob']['missingFrom'] );

		// Allure completeness
		$this->assertArrayHasKey( 'allure', $metadata['reportCompleteness'] );
		$this->assertTrue( $metadata['reportCompleteness']['allure']['complete'] ); // All test packages have allure
		$this->assertEquals( 2, $metadata['reportCompleteness']['allure']['packagesWithAllure'] );
		$this->assertEquals( [ 'utility-setup:1.0.0' ], $metadata['reportCompleteness']['allure']['missingFrom'] );

		// Check tool extra
		$this->assertArrayHasKey( 'extra', $enhanced_ctrf['results']['tool'] );
		$this->assertEquals( 'test-packages', $enhanced_ctrf['results']['tool']['extra']['orchestrationType'] );
	}

	/**
	 * Test that metadata is not added when no package info exists
	 */
	public function test_no_metadata_when_no_packages(): void {
		// Create a CTRF report without package information in tests
		$ctrf = [
			'reportFormat' => 'CTRF',
			'specVersion' => '1.0.0',
			'results' => [
				'tool' => [
					'name' => 'playwright',
					'version' => '1.0.0'
				],
				'summary' => [
					'tests' => 2,
					'passed' => 2,
					'failed' => 0,
					'skipped' => 0,
					'pending' => 0,
					'other' => 0,
					'start' => 1000000,
					'stop' => 1000100
				],
				'tests' => [
					[
						'name' => 'Regular test 1',
						'status' => 'passed',
						'duration' => 100
					],
					[
						'name' => 'Regular test 2',
						'status' => 'passed',
						'duration' => 150
					]
				]
			]
		];

		// Write CTRF to file
		$ctrf_path = $this->temp_dir . '/ctrf-report-no-packages.json';
		file_put_contents( $ctrf_path, json_encode( $ctrf, JSON_PRETTY_PRINT ) );

		// Call the private method
		$reflection = new \ReflectionClass( $this->collector );
		$method = $reflection->getMethod( 'add_package_metadata_to_merged_ctrf' );
		$method->setAccessible( true );
		$method->invoke( $this->collector, $ctrf_path );

		// Read the CTRF
		$result_ctrf = json_decode( file_get_contents( $ctrf_path ), true );

		// Verify metadata was still added but with empty packages
		$this->assertArrayHasKey( 'extra', $result_ctrf['results'] );
		$this->assertArrayHasKey( 'qitPackageMetadata', $result_ctrf['results']['extra'] );

		$metadata = $result_ctrf['results']['extra']['qitPackageMetadata'];
		$this->assertEmpty( $metadata['packages'] );
		$this->assertEquals( 0, $metadata['summary']['totalPackages'] );
		$this->assertEquals( 0, $metadata['summary']['packagesWithTests'] );
	}

	/**
	 * Test handling of missing blob/allure tracking
	 */
	public function test_handles_missing_tracking_gracefully(): void {
		// Create a CTRF with package tests
		$ctrf = [
			'reportFormat' => 'CTRF',
			'specVersion' => '1.0.0',
			'results' => [
				'tool' => [
					'name' => 'playwright',
					'version' => '1.0.0'
				],
				'summary' => [
					'tests' => 1,
					'passed' => 1,
					'failed' => 0,
					'skipped' => 0,
					'pending' => 0,
					'other' => 0,
					'start' => 1000000,
					'stop' => 1000100
				],
				'tests' => [
					[
						'name' => 'Test 1',
						'status' => 'passed',
						'duration' => 100,
						'extra' => [
							'packageSlug' => 'test/package:1.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'test'
						]
					]
				]
			]
		];

		$ctrf_path = $this->temp_dir . '/ctrf-no-tracking.json';
		file_put_contents( $ctrf_path, json_encode( $ctrf, JSON_PRETTY_PRINT ) );

		// Don't set any tracking data - should handle gracefully
		$this->collector->reset_tracking();

		// Call the method
		$reflection = new \ReflectionClass( $this->collector );
		$method = $reflection->getMethod( 'add_package_metadata_to_merged_ctrf' );
		$method->setAccessible( true );
		$method->invoke( $this->collector, $ctrf_path );

		// Read result
		$result_ctrf = json_decode( file_get_contents( $ctrf_path ), true );
		$metadata = $result_ctrf['results']['extra']['qitPackageMetadata'];

		// Should have package info but no blob/allure info
		$this->assertCount( 1, $metadata['packages'] );
		$package = $metadata['packages'][0];
		$this->assertEquals( 'test/package:1.0.0', $package['packageId'] );
		$this->assertArrayNotHasKey( 'hasBlobReport', $package );
		$this->assertArrayNotHasKey( 'hasAllureReport', $package );

		// Should not have reportCompleteness section
		$this->assertArrayNotHasKey( 'reportCompleteness', $metadata );
	}

	/**
	 * Helper to recursively remove directory
	 */
	private function rrmdir( string $dir ): void {
		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					if ( is_dir( $dir . "/" . $object ) ) {
						$this->rrmdir( $dir . "/" . $object );
					} else {
						unlink( $dir . "/" . $object );
					}
				}
			}
			rmdir( $dir );
		}
	}
}