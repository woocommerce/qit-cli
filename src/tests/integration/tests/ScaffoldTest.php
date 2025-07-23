<?php

use QIT\IntegrationTests\Traits\SnapshotHelpers;

class ScaffoldTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;

	/**
	 * Test scaffold E2E - the only test that matters
	 * This test validates that the scaffold command creates the expected structure
	 */
	public function test_scaffold_e2e_basic_with_options() {
		$temp_dir = sys_get_temp_dir() . '/qit_scaffold_test_basic-' . uniqid();

		// Run the scaffold command with vendor and package options
		$output = qit( [
			'scaffold:e2e',
			$temp_dir,
			'--vendor=acme',
			'--package=demo',
			'--no-interaction'
		] );

		// Check that the directory was created
		$this->assertDirectoryExists( $temp_dir );

		// Check that manifest.json exists and has correct content
		$this->assertFileExists( $temp_dir . '/manifest.json' );
		$manifest = json_decode( file_get_contents( $temp_dir . '/manifest.json' ), true );
		$this->assertEquals( 'acme', $manifest['vendor'] );
		$this->assertEquals( 'demo', $manifest['package'] );
		$this->assertEquals( 'e2e', $manifest['test_type'] );
		
		// Validate that the run command is clean (no reporter flags)
		$this->assertSame(
			['npx playwright test'],
			$manifest['test']['phases']['run']
		);
		$this->assertArrayHasKey('allure-dir', $manifest['test']['results']);
		$this->assertEquals('./results/allure', $manifest['test']['results']['allure-dir']);

		$this->assertMatchesTextSnapshot( file_get_contents( $temp_dir . '/playwright.config.ts' ) );
		$this->assertMatchesTextSnapshot( file_get_contents( $temp_dir . '/manifest.json' ) );
		$this->assertMatchesTextSnapshot( file_get_contents( $temp_dir . '/bootstrap/setup.sh' ) );
	}
}

