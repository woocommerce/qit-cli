<?php

namespace integration\tests\TestPackages\Network;

/**
 * Integration tests for network requirements feature.
 * 
 * Tests that test packages can declare network requirements and the environment
 * respects those requirements as documented.
 * 
 * We verify network state by checking if test packages can reach WordPress.org API.
 */
class NetworkRequirementsTest extends \PHPUnit\Framework\TestCase {
	
	/** @var string */
	private $fixturesDir;
	
	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages/network';
	}
	
	
	/**
	 * Test that a package without requires.network field runs offline by default.
	 */
	public function test_package_without_network_field_runs_offline() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' ); // Create empty file for volume mount
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-default',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		// Test should complete successfully
		$this->assertStringContainsString( 'PASSED', $output );
		
		// Check network status
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NO_NETWORK', $networkStatus, 
			'Package without requires.network should run offline' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that a package with requires.network: false runs offline.
	 */
	public function test_package_with_requires_network_false_runs_offline() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-not-required',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NO_NETWORK', $networkStatus,
			'Package with requires.network=false should run offline' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that a package with requires.network: true runs with network enabled.
	 */
	public function test_package_with_requires_network_true_runs_online() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-required',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NETWORK', $networkStatus, 
			'Package with requires.network=true should have network access' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that multiple offline packages run offline.
	 */
	public function test_multiple_offline_packages_run_offline() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-default',
			'--test-package=' . $this->fixturesDir . '/network-not-required',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		// With multiple packages, they share the same network state
		// The last one to write wins, but they should all see NO_NETWORK
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NO_NETWORK', $networkStatus, 
			'All packages should run offline when none require network' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that mixing offline and online packages enables network for ALL.
	 * This is the key behavior: when ANY package requires network, ALL get it.
	 */
	public function test_mixed_packages_all_run_online() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-not-required',
			'--test-package=' . $this->fixturesDir . '/network-required',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		// Key behavior: when ANY package requires network, ALL get it
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NETWORK', $networkStatus, 
			'All packages should run with network when any requires it' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that --offline flag blocks network-requiring packages.
	 * Per docs: "Will error if any test package requires network"
	 */
	public function test_offline_flag_blocks_network_requiring_packages() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessageMatches( '/requires network|cannot.*offline|network.*required/i' );
		
		qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-required',
			'--offline',
		] );
	}
	
	/**
	 * Test that --offline flag works with packages that don't require network.
	 */
	public function test_offline_flag_with_offline_packages() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-default',
			'--offline',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NO_NETWORK', $networkStatus,
			'--offline flag should enforce offline mode' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that --online flag forces network enabled even for offline packages.
	 */
	public function test_online_flag_forces_network_enabled() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-not-required',
			'--online',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NETWORK', $networkStatus,
			'--online flag should force network enabled' );
		
		@unlink( $statusFile );
	}
	
	/**
	 * Test that --online flag works with network-requiring packages.
	 */
	public function test_online_flag_with_network_requiring_package() {
		$statusFile = tempnam( sys_get_temp_dir(), 'network-status-' );
		file_put_contents( $statusFile, '' );
		
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/network-required',
			'--online',
			'--volume=' . $statusFile . ':/tmp/network-status.txt:rw',
		] );
		
		$this->assertStringContainsString( 'PASSED', $output );
		
		$networkStatus = trim( file_get_contents( $statusFile ) );
		$this->assertEquals( 'NETWORK', $networkStatus );
		
		@unlink( $statusFile );
	}
}