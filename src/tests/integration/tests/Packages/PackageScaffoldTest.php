<?php

use QIT\IntegrationTests\Traits\SnapshotHelpers;

class PackageScaffoldTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;

	/**
	 * Test scaffold E2E - the only test that matters
	 * This test validates that the scaffold command creates the expected structure
	 */
	public function test_scaffold_e2e_basic_with_options() {
		$temp_dir = sys_get_temp_dir() . '/qit_scaffold_test_basic-' . uniqid();

		// Run the scaffold command with package option in format vendor/package:version
		$output = qit( [
			'package:scaffold',
			$temp_dir,
			'--package=woocommerce/tests:1.0.0',
			'--framework=playwright',
			'--test-type=e2e',
			'--no-interaction'
		] );

		// Check that the directory was created
		$this->assertDirectoryExists( $temp_dir );

		// Check that qit-test.json exists and has correct content
		$this->assertFileExists( $temp_dir . '/qit-test.json' );
		$manifest = json_decode( file_get_contents( $temp_dir . '/qit-test.json' ), true );
		$this->assertEquals( 'woocommerce/tests', $manifest['package'] );
		$this->assertEquals( 'e2e', $manifest['test_type'] );

		// Validate that the run command is clean (no reporter flags)
		$this->assertSame(
			['npx playwright test'],
			$manifest['test']['phases']['run']
		);
		$this->assertArrayHasKey('allure-dir', $manifest['test']['results']);
		$this->assertEquals('./results/allure', $manifest['test']['results']['allure-dir']);

		// Validate that all three bootstrap scripts are wired correctly in manifest
		$this->assertSame(
			['./bootstrap/global-setup.sh'],
			$manifest['test']['phases']['globalSetup']
		);
		$this->assertSame(
			['./bootstrap/setup.sh'],
			$manifest['test']['phases']['setup']
		);
		$this->assertSame(
			['./bootstrap/global-teardown.sh'],
			$manifest['test']['phases']['globalTeardown']
		);

		// Check that all three bootstrap scripts exist and are executable
		$this->assertFileExists( $temp_dir . '/bootstrap/global-setup.sh' );
		$this->assertFileExists( $temp_dir . '/bootstrap/setup.sh' );
		$this->assertFileExists( $temp_dir . '/bootstrap/global-teardown.sh' );
		
		// Verify files are executable
		$this->assertTrue( is_executable( $temp_dir . '/bootstrap/global-setup.sh' ) );
		$this->assertTrue( is_executable( $temp_dir . '/bootstrap/setup.sh' ) );
		$this->assertTrue( is_executable( $temp_dir . '/bootstrap/global-teardown.sh' ) );

		// Check that results directory exists
		$this->assertDirectoryExists( $temp_dir . '/results' );

		$this->assertMatchesTextSnapshot( file_get_contents( $temp_dir . '/playwright.config.js' ) );
		$this->assertMatchesTextSnapshot( file_get_contents( $temp_dir . '/qit-test.json' ) );
		$this->assertMatchesTextSnapshot( file_get_contents( $temp_dir . '/bootstrap/setup.sh' ) );
	}
}

