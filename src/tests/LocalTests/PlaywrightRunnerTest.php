<?php

namespace QIT_CLI_Tests\LocalTests;

use QIT_CLI\LocalTests\E2E\Runner\PlaywrightRunner;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;

class PlaywrightRunnerTest extends QITTestCase {
	use MatchesSnapshots;

	protected function make_sut( array $test_infos ): PlaywrightRunner {
		return new class( new NullOutput() ) extends PlaywrightRunner {
			public function make_projects( array $test_infos ): array {
				return parent::make_projects( $test_infos );
			}
		};
	}

	/**
	 * Test generating projects for a single plugin without any setup files.
	 *
	 * **Scenario:**
	 * - Only one plugin is under test.
	 * - No `sharedSetup.js` or `isolatedSetup.js` files exist.
	 *
	 * **Expected Behavior:**
	 * - Only the test project for the plugin is created.
	 * - The test project has no dependencies.
	 */
	public function test_generate_basic_project() {
		// Existing test for a single plugin without sharedSetup.js or isolatedSetup.js
		/** @var array<int,array{
		 *     slug:string,
		 *     test_tag:string,
		 *     type:string,
		 *     action:string,
		 *     path_in_php_container:string,
		 *     path_in_playwright_container:string,
		 *     path_in_host:string
		 * }> $test_infos
		 */
		$test_infos = [
			[
				'slug'                         => 'test-slug',
				'test_tag'                     => 'test-tag',
				'type'                         => 'test-type',
				'action'                       => 'test-action',
				'path_in_php_container'        => 'test-path-in-php-container',
				'path_in_playwright_container' => 'test-path-in-playwright-container',
				'path_in_host'                 => 'test-path-in-host',
			],
		];

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
	}

	/**
	 * Test generating projects for a single plugin with `sharedSetup.js`.
	 *
	 * **Scenario:**
	 * - Only one plugin is under test.
	 * - `sharedSetup.js` exists in the plugin's `qit` directory.
	 *
	 * **Expected Behavior:**
	 * - A shared setup project is created for the plugin.
	 * - The test project depends on the shared setup project.
	 */
	public function test_single_plugin_with_shared_setup() {
		$test_infos = [
			[
				'slug'                         => 'test-slug',
				'test_tag'                     => 'test-tag',
				'type'                         => 'test-type',
				'action'                       => 'test-action',
				'path_in_php_container'        => 'test-path-in-php-container',
				'path_in_playwright_container' => 'test-path-in-playwright-container',
				'path_in_host'                 => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// Create the directory and the sharedSetup.js file
		$path_in_host = $test_infos[0]['path_in_host'];
		$qit_dir      = $path_in_host . '/qit';
		mkdir( $qit_dir, 0777, true );
		file_put_contents( $qit_dir . '/sharedSetup.js', '// shared setup' );

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Clean up
		unlink( $qit_dir . '/sharedSetup.js' );
		rmdir( $qit_dir );
		rmdir( $path_in_host );
	}

	/**
	 * Test generating projects for a single plugin with `isolatedSetup.js`.
	 *
	 * **Scenario:**
	 * - Only one plugin is under test.
	 * - `isolatedSetup.js` exists in the plugin's `qit` directory.
	 *
	 * **Expected Behavior:**
	 * - An isolated setup project is created for the plugin.
	 * - The test project depends on the isolated setup project.
	 */
	public function test_single_plugin_with_isolated_setup() {
		$test_infos = [
			[
				'slug'                         => 'test-slug',
				'test_tag'                     => 'test-tag',
				'type'                         => 'test-type',
				'action'                       => 'test-action',
				'path_in_php_container'        => 'test-path-in-php-container',
				'path_in_playwright_container' => 'test-path-in-playwright-container',
				'path_in_host'                 => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// Create the directory and the isolatedSetup.js file
		$path_in_host = $test_infos[0]['path_in_host'];
		$qit_dir      = $path_in_host . '/qit';
		mkdir( $qit_dir, 0777, true );
		file_put_contents( $qit_dir . '/isolatedSetup.js', '// isolated setup' );

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Clean up
		unlink( $qit_dir . '/isolatedSetup.js' );
		rmdir( $qit_dir );
		rmdir( $path_in_host );
	}

	/**
	 * Test generating projects for a single plugin with both `sharedSetup.js` and `isolatedSetup.js`.
	 *
	 * **Scenario:**
	 * - Only one plugin is under test.
	 * - Both `sharedSetup.js` and `isolatedSetup.js` exist in the plugin's `qit` directory.
	 *
	 * **Expected Behavior:**
	 * - Both shared and isolated setup projects are created for the plugin.
	 * - The isolated setup project depends on the shared setup project.
	 * - The test project depends on the isolated setup project.
	 */
	public function test_single_plugin_with_both_setups() {
		$test_infos = [
			[
				'slug'                         => 'test-slug',
				'test_tag'                     => 'test-tag',
				'type'                         => 'test-type',
				'action'                       => 'test-action',
				'path_in_php_container'        => 'test-path-in-php-container',
				'path_in_playwright_container' => 'test-path-in-playwright-container',
				'path_in_host'                 => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// Create the directory and both setup files
		$path_in_host = $test_infos[0]['path_in_host'];
		$qit_dir      = $path_in_host . '/qit';
		mkdir( $qit_dir, 0777, true );
		file_put_contents( $qit_dir . '/sharedSetup.js', '// shared setup' );
		file_put_contents( $qit_dir . '/isolatedSetup.js', '// isolated setup' );

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Clean up
		unlink( $qit_dir . '/sharedSetup.js' );
		unlink( $qit_dir . '/isolatedSetup.js' );
		rmdir( $qit_dir );
		rmdir( $path_in_host );
	}

	/**
	 * Test generating projects for multiple plugins without any setup files.
	 *
	 * **Scenario:**
	 * - Multiple plugins are under test.
	 * - No `sharedSetup.js` or `isolatedSetup.js` files exist for any plugin.
	 *
	 * **Expected Behavior:**
	 * - `db-export` and `db-import` projects are added.
	 * - Each test project depends on `db-import`.
	 */
	public function test_multiple_plugins_without_setups() {
		$test_infos = [
			[
				'slug'                         => 'plugin-one',
				'test_tag'                     => 'test-tag-1',
				'type'                         => 'test-type-1',
				'action'                       => 'test-action-1',
				'path_in_php_container'        => 'test-path-in-php-container-1',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-one',
				'path_in_host'                 => 'test-path-in-host/plugin-one',
			],
			[
				'slug'                         => 'plugin-two',
				'test_tag'                     => 'test-tag-2',
				'type'                         => 'test-type-2',
				'action'                       => 'test-action-2',
				'path_in_php_container'        => 'test-path-in-php-container-2',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-two',
				'path_in_host'                 => 'test-path-in-host/plugin-two',
			],
		];

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
	}

	/**
	 * Test generating projects for multiple plugins with `sharedSetup.js`.
	 *
	 * **Scenario:**
	 * - Multiple plugins are under test.
	 * - `sharedSetup.js` exists in each plugin's `qit` directory.
	 *
	 * **Expected Behavior:**
	 * - Shared setup projects are created for each plugin.
	 * - `db-export` depends on all shared setup projects.
	 * - `db-import` depends on `db-export`.
	 * - Each test project depends on `db-import`.
	 */
	public function test_multiple_plugins_with_shared_setup() {
		$test_infos = [
			[
				'slug'                         => 'plugin-one',
				'test_tag'                     => 'test-tag-1',
				'type'                         => 'test-type-1',
				'action'                       => 'test-action-1',
				'path_in_php_container'        => 'test-path-in-php-container-1',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-one',
				'path_in_host'                 => sys_get_temp_dir() . '/plugin-one',
			],
			[
				'slug'                         => 'plugin-two',
				'test_tag'                     => 'test-tag-2',
				'type'                         => 'test-type-2',
				'action'                       => 'test-action-2',
				'path_in_php_container'        => 'test-path-in-php-container-2',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-two',
				'path_in_host'                 => sys_get_temp_dir() . '/plugin-two',
			],
		];

		// Create sharedSetup.js for both plugins
		foreach ( $test_infos as $t ) {
			$path_in_host = $t['path_in_host'];
			$qit_dir      = $path_in_host . '/qit';
			mkdir( $qit_dir, 0777, true );
			file_put_contents( $qit_dir . '/sharedSetup.js', '// shared setup' );
		}

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Clean up
		foreach ( $test_infos as $t ) {
			$path_in_host = $t['path_in_host'];
			$qit_dir      = $path_in_host . '/qit';
			unlink( $qit_dir . '/sharedSetup.js' );
			rmdir( $qit_dir );
			rmdir( $path_in_host );
		}
	}

	/**
	 * Test generating projects for multiple plugins with `isolatedSetup.js`.
	 *
	 * **Scenario:**
	 * - Multiple plugins are under test.
	 * - `isolatedSetup.js` exists in each plugin's `qit` directory.
	 *
	 * **Expected Behavior:**
	 * - `db-export` and `db-import` projects are added.
	 * - Isolated setup projects are created for each plugin, depending on `db-import`.
	 * - Each test project depends on its respective isolated setup project.
	 */
	public function test_multiple_plugins_with_isolated_setup() {
		$test_infos = [
			[
				'slug'                         => 'plugin-one',
				'test_tag'                     => 'test-tag-1',
				'type'                         => 'test-type-1',
				'action'                       => 'test-action-1',
				'path_in_php_container'        => 'test-path-in-php-container-1',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-one',
				'path_in_host'                 => sys_get_temp_dir() . '/plugin-one',
			],
			[
				'slug'                         => 'plugin-two',
				'test_tag'                     => 'test-tag-2',
				'type'                         => 'test-type-2',
				'action'                       => 'test-action-2',
				'path_in_php_container'        => 'test-path-in-php-container-2',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-two',
				'path_in_host'                 => sys_get_temp_dir() . '/plugin-two',
			],
		];

		// Create isolatedSetup.js for both plugins
		foreach ( $test_infos as $t ) {
			$path_in_host = $t['path_in_host'];
			$qit_dir      = $path_in_host . '/qit';
			mkdir( $qit_dir, 0777, true );
			file_put_contents( $qit_dir . '/isolatedSetup.js', '// isolated setup' );
		}

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Clean up
		foreach ( $test_infos as $t ) {
			$path_in_host = $t['path_in_host'];
			$qit_dir      = $path_in_host . '/qit';
			unlink( $qit_dir . '/isolatedSetup.js' );
			rmdir( $qit_dir );
			rmdir( $path_in_host );
		}
	}

	/**
	 * Test generating projects for multiple plugins with both `sharedSetup.js` and `isolatedSetup.js`.
	 *
	 * **Scenario:**
	 * - Multiple plugins are under test.
	 * - Both `sharedSetup.js` and `isolatedSetup.js` exist in each plugin's `qit` directory.
	 *
	 * **Expected Behavior:**
	 * - Shared setup projects are created for each plugin.
	 * - `db-export` depends on all shared setup projects.
	 * - `db-import` depends on `db-export`.
	 * - Isolated setup projects depend on `db-import`.
	 * - Each test project depends on its respective isolated setup project.
	 */
	public function test_multiple_plugins_with_both_setups() {
		$test_infos = [
			[
				'slug'                         => 'plugin-one',
				'test_tag'                     => 'test-tag-1',
				'type'                         => 'test-type-1',
				'action'                       => 'test-action-1',
				'path_in_php_container'        => 'test-path-in-php-container-1',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-one',
				'path_in_host'                 => sys_get_temp_dir() . '/plugin-one',
			],
			[
				'slug'                         => 'plugin-two',
				'test_tag'                     => 'test-tag-2',
				'type'                         => 'test-type-2',
				'action'                       => 'test-action-2',
				'path_in_php_container'        => 'test-path-in-php-container-2',
				'path_in_playwright_container' => 'test-path-in-playwright-container/plugin-two',
				'path_in_host'                 => sys_get_temp_dir() . '/plugin-two',
			],
		];

		// Create both setup files for both plugins
		foreach ( $test_infos as $t ) {
			$path_in_host = $t['path_in_host'];
			$qit_dir      = $path_in_host . '/qit';
			mkdir( $qit_dir, 0777, true );
			file_put_contents( $qit_dir . '/sharedSetup.js', '// shared setup' );
			file_put_contents( $qit_dir . '/isolatedSetup.js', '// isolated setup' );
		}

		$sut = $this->make_sut( $test_infos );

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Clean up
		foreach ( $test_infos as $t ) {
			$path_in_host = $t['path_in_host'];
			$qit_dir      = $path_in_host . '/qit';
			unlink( $qit_dir . '/sharedSetup.js' );
			unlink( $qit_dir . '/isolatedSetup.js' );
			rmdir( $qit_dir );
			rmdir( $path_in_host );
		}
	}
}