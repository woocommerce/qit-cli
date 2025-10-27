<?php

namespace QIT\IntegrationTests\TestPackages;

use PHPUnit\Framework\TestCase;
use QIT\IntegrationTests\TestCleanupHelper;

/**
 * Test that parallel execution safety features work correctly.
 */
class ParallelSafetyTest extends TestCase {
	
	public function test_process_id_is_defined(): void {
		$this->assertTrue( defined( 'QIT_TEST_PROCESS_ID' ), 'QIT_TEST_PROCESS_ID should be defined' );
		
		$process_id = QIT_TEST_PROCESS_ID;
		$this->assertNotEmpty( $process_id, 'Process ID should not be empty' );
		
		// In parallel mode, should be a numeric token
		if ( getenv( 'TEST_TOKEN' ) ) {
			$this->assertEquals( getenv( 'TEST_TOKEN' ), $process_id, 'Process ID should match TEST_TOKEN in parallel mode' );
		} else {
			// In serial mode, should start with 'serial-'
			$this->assertStringStartsWith( 'serial-', $process_id, 'Process ID should start with "serial-" in non-parallel mode' );
		}
	}
	
	public function test_process_specific_package_names(): void {
		// Generate two package names
		$package1 = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'test1' );
		$package2 = TestCleanupHelper::generate_test_package_name( 'woocommerce', 'test2' );
		
		// Both should contain the process ID
		$process_prefix = TestCleanupHelper::get_process_prefix();
		$this->assertStringContainsString( $process_prefix, $package1, 'Package name should contain process prefix' );
		$this->assertStringContainsString( $process_prefix, $package2, 'Package name should contain process prefix' );
		
		// They should be different from each other
		$this->assertNotEquals( $package1, $package2, 'Package names should be unique' );
		
		// Format check - account for both parallel (numeric) and serial (serial-xxx) process IDs
		// Process ID can be numeric (parallel) or serial-{uniqid} (serial mode)
		$this->assertMatchesRegularExpression(
			'/^woocommerce\/qit-integration-test-([\d]+|serial-[a-f0-9]+)-test[12]-[a-f0-9]{8}$/',
			$package1,
			'Package name should match expected format'
		);
	}
	
	public function test_cleanup_method_selection(): void {
		// This test just verifies the method exists and can be called
		// Actual cleanup is tested in integration tests
		
		$this->assertTrue(
			method_exists( TestCleanupHelper::class, 'cleanup_test_packages' ),
			'cleanup_test_packages method should exist'
		);
		
		$this->assertTrue(
			method_exists( TestCleanupHelper::class, 'cleanup_process_packages' ),
			'cleanup_process_packages method should exist'
		);
		
		$this->assertTrue(
			method_exists( TestCleanupHelper::class, 'cleanup_all_test_packages' ),
			'cleanup_all_test_packages method should exist'
		);
	}
	
	public function test_parallel_execution_detection(): void {
		$is_parallel = (bool) getenv( 'TEST_TOKEN' );
		
		if ( $is_parallel ) {
			$this->assertNotEmpty( getenv( 'TEST_TOKEN' ), 'TEST_TOKEN should be set in parallel mode' );
			$this->assertIsNumeric( getenv( 'TEST_TOKEN' ), 'TEST_TOKEN should be numeric' );
		} else {
			$this->assertEmpty( getenv( 'TEST_TOKEN' ), 'TEST_TOKEN should not be set in serial mode' );
		}
	}
}