<?php

namespace QIT_CLI_Tests\Environment;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\ResultCollector;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\NodeDependencyManager;

/**
 * Test that ResultCollector properly adds enhanced package metadata including type and order
 */
class ResultCollectorEnhancedMetadataTest extends TestCase {

	private string $temp_dir;
	private ResultCollector $collector;

	protected function setUp(): void {
		parent::setUp();
		
		// Create temp directory for test artifacts
		$this->temp_dir = sys_get_temp_dir() . '/qit-enhanced-test-' . uniqid();
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
	 * Test that packageType and packageOrder are added to tests and metadata
	 */
	public function test_adds_package_type_and_order(): void {
		// Create a CTRF report with both test and utility packages
		$ctrf = [
			'reportFormat' => 'CTRF',
			'specVersion' => '1.0.0',
			'results' => [
				'tool' => [
					'name' => 'playwright',
					'version' => '1.0.0'
				],
				'summary' => [
					'tests' => 6,
					'passed' => 5,
					'failed' => 1,
					'skipped' => 0,
					'pending' => 0,
					'other' => 0,
					'start' => 1000000,
					'stop' => 1000600
				],
				'tests' => [
					// Utility package - setup only
					[
						'name' => '[setup] docker-compose up',
						'status' => 'passed',
						'duration' => 100,
						'extra' => [
							'packageSlug' => 'test/utility-setup:1.0.0',
							'phase' => 'setup',
							'testType' => 'e2e',
							'namespace' => 'test'
						]
					],
					// First test package
					[
						'name' => 'Checkout Test 1',
						'status' => 'passed',
						'duration' => 200,
						'extra' => [
							'packageSlug' => 'test/checkout-tests:2.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'test'
						]
					],
					[
						'name' => 'Checkout Test 2',
						'status' => 'failed',
						'duration' => 250,
						'extra' => [
							'packageSlug' => 'test/checkout-tests:2.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'test'
						]
					],
					// Second test package
					[
						'name' => 'Payment Test 1',
						'status' => 'passed',
						'duration' => 300,
						'extra' => [
							'packageSlug' => 'test/payment-tests:3.0.0',
							'phase' => 'run',
							'testType' => 'e2e',
							'namespace' => 'test'
						]
					],
					// Utility package teardown
					[
						'name' => '[teardown] docker-compose down',
						'status' => 'passed',
						'duration' => 50,
						'extra' => [
							'packageSlug' => 'test/utility-setup:1.0.0',
							'phase' => 'teardown',
							'testType' => 'e2e',
							'namespace' => 'test'
						]
					],
					// Lifecycle entry with flags
					[
						'name' => '[globalTeardown] cleanup',
						'status' => 'passed',
						'duration' => 30,
						'extra' => [
							'type' => 'lifecycle',
							'phase' => 'globalTeardown',
							'package' => 'global',
							'isLifecycle' => true,
							'countsTowardTotals' => false
						]
					]
				]
			]
		];

		// Write CTRF to file
		$ctrf_path = $this->temp_dir . '/ctrf-enhanced.json';
		file_put_contents( $ctrf_path, json_encode( $ctrf, JSON_PRETTY_PRINT ) );

		// Set up tracking data
		$this->collector->reset_tracking();

		// Call the private method using reflection
		$reflection = new \ReflectionClass( $this->collector );
		$method = $reflection->getMethod( 'add_package_metadata_to_merged_ctrf' );
		$method->setAccessible( true );
		$method->invoke( $this->collector, $ctrf_path );

		// Read the enhanced CTRF
		$enhanced_ctrf = json_decode( file_get_contents( $ctrf_path ), true );

		// Check metadata was added
		$this->assertArrayHasKey( 'extra', $enhanced_ctrf['results'] );
		$this->assertArrayHasKey( 'qitPackageMetadata', $enhanced_ctrf['results']['extra'] );

		$metadata = $enhanced_ctrf['results']['extra']['qitPackageMetadata'];

		// Check packages array
		$this->assertArrayHasKey( 'packages', $metadata );
		$this->assertCount( 3, $metadata['packages'] ); // 3 packages total

		// Build lookup by package ID
		$packages_by_id = [];
		foreach ( $metadata['packages'] as $pkg ) {
			$packages_by_id[ $pkg['packageId'] ] = $pkg;
		}

		// Check utility package
		$utility = $packages_by_id['test/utility-setup:1.0.0'];
		$this->assertEquals( 'utility', $utility['packageType'] );
		$this->assertEquals( 1, $utility['executionOrder'] ); // First seen
		$this->assertFalse( $utility['hasRunPhase'] );
		$this->assertEquals( 0, $utility['testCount'] );
		$this->assertEquals( 0, $utility['duration'] );

		// Check first test package
		$checkout = $packages_by_id['test/checkout-tests:2.0.0'];
		$this->assertEquals( 'test', $checkout['packageType'] );
		$this->assertEquals( 2, $checkout['executionOrder'] ); // Second seen
		$this->assertTrue( $checkout['hasRunPhase'] );
		$this->assertEquals( 2, $checkout['testCount'] );
		$this->assertEquals( 450, $checkout['duration'] ); // 200 + 250

		// Check second test package
		$payment = $packages_by_id['test/payment-tests:3.0.0'];
		$this->assertEquals( 'test', $payment['packageType'] );
		$this->assertEquals( 3, $payment['executionOrder'] ); // Third seen
		$this->assertTrue( $payment['hasRunPhase'] );
		$this->assertEquals( 1, $payment['testCount'] );
		$this->assertEquals( 300, $payment['duration'] );

		// Check that individual tests got enhanced with packageType and packageOrder
		$tests = $enhanced_ctrf['results']['tests'];
		
		// Check utility setup test
		$this->assertEquals( 'utility', $tests[0]['extra']['packageType'] );
		$this->assertEquals( 1, $tests[0]['extra']['packageOrder'] );

		// Check checkout tests
		$this->assertEquals( 'test', $tests[1]['extra']['packageType'] );
		$this->assertEquals( 2, $tests[1]['extra']['packageOrder'] );
		$this->assertEquals( 'test', $tests[2]['extra']['packageType'] );
		$this->assertEquals( 2, $tests[2]['extra']['packageOrder'] );

		// Check payment test
		$this->assertEquals( 'test', $tests[3]['extra']['packageType'] );
		$this->assertEquals( 3, $tests[3]['extra']['packageOrder'] );

		// Check utility teardown
		$this->assertEquals( 'utility', $tests[4]['extra']['packageType'] );
		$this->assertEquals( 1, $tests[4]['extra']['packageOrder'] );

		// Lifecycle entry should not have packageType/packageOrder added (no packageSlug)
		$this->assertArrayNotHasKey( 'packageType', $tests[5]['extra'] );
		$this->assertArrayNotHasKey( 'packageOrder', $tests[5]['extra'] );
		// But should still have lifecycle flags
		$this->assertTrue( $tests[5]['extra']['isLifecycle'] );
		$this->assertFalse( $tests[5]['extra']['countsTowardTotals'] );
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