<?php

namespace QIT_CLI_Tests\LocalTests;

use QIT_CLI\App;
use QIT_CLI\LocalTests\E2E\Runner\PlaywrightOrchestration;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PlaywrightOrchestrationTest extends QITTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();

		// List of directories that tests commonly create
		$testDirs = [
			sys_get_temp_dir() . '/activate-plugin',
			sys_get_temp_dir() . '/test-plugin',
			sys_get_temp_dir() . '/bootstrap-plugin',
			sys_get_temp_dir() . '/plugin-one',
			sys_get_temp_dir() . '/plugin-two',
			// add others if needed
		];

		// Use the safe, whitelist-based cleanup method on each directory
		foreach ( $testDirs as $dir ) {
			$this->cleanup( $dir );
		}
	}

	protected function make_sut(): PlaywrightOrchestration {
		return App::make( PlaywrightOrchestration::class );
	}

	public function test_invalid_action() {
		$this->expectException( \InvalidArgumentException::class );

		$test_infos = [
			[
				'slug'                  => 'test-plugin',
				'test_tag'              => 'test-tag',
				'type'                  => 'test-type',
				'action'                => 'invalid-action',
				'path_in_php_container' => 'test-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-invalid',
			],
		];

		$sut = $this->make_sut();
		$sut->make_projects( $test_infos );
	}


	/**
	 * Test a single plugin with only "activate" action.
	 *
	 * Expected: Just an activation step, no shared or isolated steps, no tests.
	 */
	public function test_single_plugin_activate_only() {
		$test_infos = [
			[
				'slug'                  => 'activate-plugin',
				'test_tag'              => 'test-tag',
				'type'                  => 'test-type',
				'action'                => 'activate',
				'path_in_php_container' => 'test-path-activate',
				'path_in_host'          => sys_get_temp_dir() . '/activate-plugin',
			],
		];

		// Ensure directory exists (no setups needed, but just to be consistent)
		if ( ! file_exists( $test_infos[0]['path_in_host'] ) ) {
			mkdir( $test_infos[0]['path_in_host'], 0777, true );
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	/**
	 * Test multiple plugins with only "bootstrap" action.
	 *
	 * Expected: Run shared setups/teardowns if they exist, no isolated setups or tests.
	 */
	public function test_multiple_plugins_bootstrap_only() {
		$test_infos = [
			[
				'slug'                  => 'bootstrap-one',
				'test_tag'              => 'test-tag-1',
				'type'                  => 'test-type-1',
				'action'                => 'bootstrap',
				'path_in_php_container' => 'test-path-bootstrap-1',
				'path_in_host'          => sys_get_temp_dir() . '/bootstrap-one',
			],
			[
				'slug'                  => 'bootstrap-two',
				'test_tag'              => 'test-tag-2',
				'type'                  => 'test-type-2',
				'action'                => 'bootstrap',
				'path_in_php_container' => 'test-path-bootstrap-2',
				'path_in_host'          => sys_get_temp_dir() . '/bootstrap-two',
			],
		];

		// Create shared setup and teardown for both
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.sh', 'shared-teardown.sh' ]
		);
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'shared-setup.js', 'shared-teardown.js' ]
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		foreach ( $test_infos as $info ) {
			$this->cleanup( $info['path_in_host'] );
		}
	}

	/**
	 * Test a scenario with one plugin "test" and another "bootstrap".
	 *
	 * Expected:
	 * - Shared setups/teardowns run
	 * - "test" plugin runs full lifecycle: isolated setups, test, isolated teardown
	 * - "bootstrap" plugin runs shared steps only
	 * - DB export/import steps happen because multiple plugins and test action involved
	 */
	public function test_mixed_bootstrap_and_test() {
		$test_infos = [
			[
				'slug'                  => 'bootstrap-plugin',
				'test_tag'              => 'test-tag-b',
				'type'                  => 'test-type-b',
				'action'                => 'bootstrap',
				'path_in_php_container' => 'test-path-bootstrap',
				'path_in_host'          => sys_get_temp_dir() . '/bootstrap-plugin',
			],
			[
				'slug'                  => 'test-plugin',
				'test_tag'              => 'test-tag-t',
				'type'                  => 'test-type-t',
				'action'                => 'test',
				'path_in_php_container' => 'test-path-test',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// Shared setups for both
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.sh', 'shared-teardown.sh' ]
		);

		// test-plugin has both shared and isolated setups, plus test files:
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'shared-setup.php', 'setup.js', 'teardown.js', 'shared-teardown.js' ]
		);

		// Also create a dummy .spec.js test file for the test plugin
		$test_spec_dir = $test_infos[1]['path_in_host'];
		if ( ! file_exists( $test_spec_dir ) ) {
			mkdir( $test_spec_dir, 0777, true );
		}
		// Just a placeholder spec file
		file_put_contents( "$test_spec_dir/test.spec.js", "// dummy test" );

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Cleanup
		unlink( "$test_spec_dir/test.spec.js" );
		foreach ( $test_infos as $info ) {
			$this->cleanup( $info['path_in_host'] );
		}
	}

	/**
	 * Test a scenario with one plugin "activate" and another "test".
	 *
	 * Expected:
	 * - Activate plugin only activates, no shared steps needed for it alone.
	 * - Test plugin runs full lifecycle and may trigger shared steps if present.
	 * - DB export/import steps if multiple plugins and test involved.
	 */
	public function test_mixed_activate_and_test() {
		$test_infos = [
			[
				'slug'                  => 'activate-plugin',
				'test_tag'              => 'test-tag-a',
				'type'                  => 'test-type-a',
				'action'                => 'activate',
				'path_in_php_container' => 'test-path-activate',
				'path_in_host'          => sys_get_temp_dir() . '/activate-plugin',
			],
			[
				'slug'                  => 'test-plugin',
				'test_tag'              => 'test-tag-t',
				'type'                  => 'test-type-t',
				'action'                => 'test',
				'path_in_php_container' => 'test-path-test',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// test-plugin has full lifecycle:
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'shared-setup.sh', 'setup.sh', 'teardown.sh', 'shared-teardown.sh' ]
		);
		$test_spec_dir = $test_infos[1]['path_in_host'];
		file_put_contents( "$test_spec_dir/test.spec.js", "// dummy test" );

		// Activate plugin: no setups needed, just the directory
		if ( ! file_exists( $test_infos[0]['path_in_host'] ) ) {
			mkdir( $test_infos[0]['path_in_host'], 0777, true );
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Cleanup
		unlink( "$test_spec_dir/test.spec.js" );
		foreach ( $test_infos as $info ) {
			if ( file_exists( $info['path_in_host'] . '/bootstrap' ) ) {
				$this->cleanup( $info['path_in_host'] );
			} else {
				// No bootstrap directory for activate plugin, just remove plugin dir.
				if ( file_exists( $info['path_in_host'] ) ) {
					rmdir( $info['path_in_host'] );
				}
			}
		}
	}


	/**
	 * Scenario 1: Single plugin test action with no setup/teardown and no .spec.js
	 *
	 * Expected:
	 * - No shared or isolated steps since no files.
	 * - Possibly just a test run step if it looks for specs (no specs => no test steps).
	 * - Should not error out.
	 */
	public function test_single_plugin_test_no_setup_no_teardowns_no_specs() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-no-setup',
				'test_tag'              => 'tag-no-setup',
				'type'                  => 'type-no-setup',
				'action'                => 'test',
				'path_in_php_container' => 'no-setup-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-no-setup',
			],
		];

		// Create plugin directory without any bootstrap or specs
		if ( ! file_exists( $test_infos[0]['path_in_host'] ) ) {
			mkdir( $test_infos[0]['path_in_host'], 0777, true );
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	/**
	 * Scenario 2: Single plugin test action with only shared setup files and no tests
	 *
	 * Expected:
	 * - Shared setup steps run.
	 * - No isolated steps or tests since no specs.
	 * - Shared teardown if present would run.
	 */
	public function test_single_plugin_test_only_shared_setup_no_specs() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-shared-only',
				'test_tag'              => 'tag-shared-only',
				'type'                  => 'type-shared-only',
				'action'                => 'test',
				'path_in_php_container' => 'shared-only-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-shared-only',
			],
		];

		// Create shared setup files only
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.sh' ] // Just a single shared setup file
		);

		// No specs created
		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	/**
	 * Scenario 3: Single plugin test action with only isolated setup files (no shared, no teardown)
	 *
	 * Expected:
	 * - Isolated setup steps run.
	 * - If no specs, then no actual test steps run.
	 * - No shared or teardown steps.
	 */
	public function test_single_plugin_test_only_isolated_setup_no_shared_no_teardown() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-isolated-only',
				'test_tag'              => 'tag-isolated-only',
				'type'                  => 'type-isolated-only',
				'action'                => 'test',
				'path_in_php_container' => 'isolated-only-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-isolated-only',
			],
		];

		// Create isolated setup file only
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'setup.sh' ] // only isolated setup
		);

		// No specs
		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	/**
	 * Scenario 4: Multiple plugins all with test action
	 *
	 * Expected:
	 * - Shared steps run once if any shared files.
	 * - DB export/import occurs because multiple plugins and test actions.
	 * - Each plugin runs isolated setups, tests, teardowns in sequence.
	 */
	public function test_multiple_plugins_all_test_action() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-one',
				'test_tag'              => 'tag-one',
				'type'                  => 'type-one',
				'action'                => 'test',
				'path_in_php_container' => 'test-one-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-one',
			],
			[
				'slug'                  => 'test-plugin-two',
				'test_tag'              => 'tag-two',
				'type'                  => 'type-two',
				'action'                => 'test',
				'path_in_php_container' => 'test-two-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-two',
			],
		];

		// Create shared and isolated setup files, and test specs for both plugins
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.sh', 'setup.js', 'teardown.sh' ]
		);
		file_put_contents( $test_infos[0]['path_in_host'] . '/test.spec.js', "// test spec one" );

		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'shared-setup.php', 'setup.sh', 'teardown.js' ]
		);
		file_put_contents( $test_infos[1]['path_in_host'] . '/test.spec.js', "// test spec two" );

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Cleanup
		unlink( $test_infos[0]['path_in_host'] . '/test.spec.js' );
		unlink( $test_infos[1]['path_in_host'] . '/test.spec.js' );

		foreach ( $test_infos as $info ) {
			$this->cleanup( $info['path_in_host'] );
		}
	}

	/**
	 * Scenario 5: Multiple test plugins plus one activate plugin
	 *
	 * Expected:
	 * - Shared steps run due to test plugins.
	 * - Activate plugin just activates, no isolated steps.
	 * - Test plugins run full lifecycle with DB export/import as needed.
	 */
	public function test_multiple_test_and_one_activate() {
		$test_infos = [
			[
				'slug'                  => 'activate-plugin-x',
				'test_tag'              => 'tag-act-x',
				'type'                  => 'type-act-x',
				'action'                => 'activate',
				'path_in_php_container' => 'activate-x-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/activate-plugin-x',
			],
			[
				'slug'                  => 'test-plugin-a',
				'test_tag'              => 'tag-a',
				'type'                  => 'type-a',
				'action'                => 'test',
				'path_in_php_container' => 'test-a-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-a',
			],
			[
				'slug'                  => 'test-plugin-b',
				'test_tag'              => 'tag-b',
				'type'                  => 'type-b',
				'action'                => 'test',
				'path_in_php_container' => 'test-b-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-b',
			],
		];

		// Create isolated and shared setups for the test plugins
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'shared-setup.sh', 'setup.sh', 'teardown.sh' ]
		);
		file_put_contents( $test_infos[1]['path_in_host'] . '/test.spec.js', "// test spec a" );

		$this->createSetupFiles(
			$test_infos[2]['path_in_host'],
			[ 'shared-setup.js', 'setup.php', 'teardown.php' ]
		);
		file_put_contents( $test_infos[2]['path_in_host'] . '/test.spec.js', "// test spec b" );

		// Activate plugin needs just a directory
		if ( ! file_exists( $test_infos[0]['path_in_host'] ) ) {
			mkdir( $test_infos[0]['path_in_host'], 0777, true );
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Cleanup
		unlink( $test_infos[1]['path_in_host'] . '/test.spec.js' );
		unlink( $test_infos[2]['path_in_host'] . '/test.spec.js' );

		foreach ( $test_infos as $info ) {
			$this->cleanup( $info['path_in_host'] );
		}
	}

	/**
	 * Scenario 6: Multiple plugins with bootstrap and activate only (no test)
	 *
	 * Expected:
	 * - Shared steps run because of bootstrap.
	 * - Activate just activates.
	 * - No isolated or test steps.
	 */
	public function test_multiple_bootstrap_and_activate_only() {
		$test_infos = [
			[
				'slug'                  => 'bootstrap-only-plugin',
				'test_tag'              => 'tag-boot-only',
				'type'                  => 'type-boot-only',
				'action'                => 'bootstrap',
				'path_in_php_container' => 'boot-only-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/bootstrap-only-plugin',
			],
			[
				'slug'                  => 'activate-plugin-y',
				'test_tag'              => 'tag-act-y',
				'type'                  => 'type-act-y',
				'action'                => 'activate',
				'path_in_php_container' => 'activate-y-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/activate-plugin-y',
			],
		];

		// Bootstrap plugin: only shared setups
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.php', 'shared-teardown.php' ]
		);

		// Activate plugin: just ensure directory
		if ( ! file_exists( $test_infos[1]['path_in_host'] ) ) {
			mkdir( $test_infos[1]['path_in_host'], 0777, true );
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		foreach ( $test_infos as $info ) {
			$this->cleanup( $info['path_in_host'] );
		}
	}

	/**
	 * Scenario 7: Plugin with only teardown files (no setup)
	 *
	 * Expected:
	 * - If no specs, no tests actually run, but teardown steps should still appear logically after tests.
	 * - If specs exist, tests run and then teardown executes.
	 */
	public function test_single_plugin_test_only_teardown_no_setup() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-only-teardown',
				'test_tag'              => 'tag-teardown-only',
				'type'                  => 'type-teardown-only',
				'action'                => 'test',
				'path_in_php_container' => 'teardown-only-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-only-teardown',
			],
		];

		// Create only teardown files
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'teardown.sh' ]
		);
		// Add a test spec so that a test phase actually occurs
		file_put_contents( $test_infos[0]['path_in_host'] . '/test.spec.js', "// dummy test for teardown" );

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		unlink( $test_infos[0]['path_in_host'] . '/test.spec.js' );
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	/**
	 * Scenario 8: Plugin with partial setups (only setup.php)
	 *
	 * Expected:
	 * - Isolated setup (php) runs, no shell/js setups.
	 * - If test spec exists, run tests; no shared steps if none provided.
	 */
	public function test_single_plugin_test_partial_setup_php_only() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-partial-setup',
				'test_tag'              => 'tag-partial-setup',
				'type'                  => 'type-partial-setup',
				'action'                => 'test',
				'path_in_php_container' => 'partial-setup-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-partial-setup',
			],
		];

		// Create only setup.php
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'setup.php' ]
		);
		// Add a spec
		file_put_contents( $test_infos[0]['path_in_host'] . '/test.spec.js', "// dummy test partial" );

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		unlink( $test_infos[0]['path_in_host'] . '/test.spec.js' );
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	/**
	 * Scenario 9: No plugins at all (empty test_infos)
	 *
	 * Expected:
	 * - No projects generated.
	 */
	public function test_no_plugins_at_all() {
		$test_infos = [];

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
		// No cleanup needed
	}

	/**
	 * Scenario 10: Invalid file in bootstrap directory
	 *
	 * Expected:
	 * - Orchestration should skip or ignore unexpected files.
	 * - Should not throw errors.
	 */
	public function test_single_plugin_test_with_invalid_bootstrap_file() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-invalid-file',
				'test_tag'              => 'tag-invalid-file',
				'type'                  => 'type-invalid-file',
				'action'                => 'test',
				'path_in_php_container' => 'invalid-file-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-invalid-file',
			],
		];

		// Create setup files and an invalid file
		$bootstrap_dir = $this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'setup.sh' ]
		);

		// Add invalid file
		file_put_contents( "$bootstrap_dir/unknown.txt", "unexpected file content" );

		// Add spec
		file_put_contents( $test_infos[0]['path_in_host'] . '/test.spec.js', "// dummy test invalid file" );

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		unlink( $test_infos[0]['path_in_host'] . '/test.spec.js' );
		// Cleanup will skip unknown.txt since it doesn't match expected patterns
		// but will not throw an error. Just leaves it there.
		$this->cleanup( $test_infos[0]['path_in_host'] );
	}

	// Helper methods from original tests:
	protected function createSetupFiles( $path, $files ) {
		$bootstrap_dir = $path . '/bootstrap';

		if ( file_exists( $bootstrap_dir ) ) {
			$this->cleanup( $path );
		}

		if ( ! mkdir( $bootstrap_dir, 0755, true ) ) {
			throw new \RuntimeException( "Could not create the bootstrap directory: $bootstrap_dir" );
		}

		foreach ( $files as $file ) {
			if ( ! file_put_contents( "$bootstrap_dir/$file", "// Test content for $file" ) ) {
				throw new \RuntimeException( "Could not create the file: $bootstrap_dir/$file" );
			}
		}

		return $bootstrap_dir;
	}

	protected function cleanup( $dir ) {
		$expected_files = [
			'*.spec.js',
			'*.php',
			'*.js',
			'*.sh',
			'dependencies.json',
		];

		$expected_subdirectories = [
			'bootstrap',
		];

		$realpath = realpath( $dir );
		$tmp_dir  = realpath( sys_get_temp_dir() );

		if ( $realpath === false ) {
			return; // Directory doesn't exist.
		}

		// Must be inside our temp dir and not the temp dir itself
		if ( $realpath === $tmp_dir || strpos( $realpath, $tmp_dir ) !== 0 ) {
			return;
		}

		// Check if the directory is a known plugin directory
		if ( ! preg_match( '/^(test-plugin|plugin-|bootstrap-plugin|activate-plugin).*/', basename( $realpath ) ) ) {
			return;
		}

		$items = glob( $realpath . '/*', GLOB_MARK );
		if ( $items === false ) {
			// No items, try removing the directory if empty
			@rmdir( $realpath );

			return;
		}

		foreach ( $items as $item ) {
			$item     = rtrim( $item, '/' );
			$filename = basename( $item );

			if ( is_dir( $item ) ) {
				// If this is the bootstrap directory, handle inline
				if ( $filename === 'bootstrap' ) {
					$this->cleanupBootstrapDir( $item, $expected_files );
					@rmdir( $item );
				} else {
					// Unexpected directory, skip it
				}
			} elseif ( is_file( $item ) ) {
				if ( $this->matchesExpectedFiles( $filename, $expected_files ) ) {
					@unlink( $item );
				} else {
					// Unexpected file, skip
				}
			}
		}

		@rmdir( $realpath );
	}

	protected function cleanupBootstrapDir( $bootstrap_dir, $expected_files ) {
		// Remove allowed files inside bootstrap, no pattern check for the dir name itself.
		$bootstrap_items = glob( $bootstrap_dir . '/*', GLOB_MARK );
		if ( $bootstrap_items !== false ) {
			foreach ( $bootstrap_items as $b_item ) {
				$b_item     = rtrim( $b_item, '/' );
				$b_filename = basename( $b_item );

				if ( is_file( $b_item ) ) {
					if ( $this->matchesExpectedFiles( $b_filename, $expected_files ) ) {
						@unlink( $b_item );
					}
				} elseif ( is_dir( $b_item ) ) {
					// If ever needed, handle sub-subdirectories here
					// For now, we assume we don't nest further.
				}
			}
		}
	}

	protected function matchesExpectedFiles( $filename, $patterns ) {
		foreach ( $patterns as $pattern ) {
			if ( fnmatch( $pattern, $filename ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Complex Scenario: Mixed actions with multiple test plugins, bootstrap, and activate plugins.
	 *
	 * Scenario:
	 * - Plugin A: test plugin with full lifecycle (shared & isolated setups, teardowns, and specs).
	 * - Plugin B: test plugin with partial setups (only setup.php) and specs.
	 * - Plugin C: bootstrap plugin with shared setups/teardowns only.
	 * - Plugin D: activate plugin (no setups, no tests).
	 * - Plugin E: test plugin with no shared setups, only isolated teardown and a spec.
	 *
	 * Expected:
	 * - Shared steps run if any test/bootstrap plugin exists.
	 * - Multiple DB exports/imports due to multiple test plugins.
	 * - Activate plugin just activates at the right time.
	 * - Multiple `[db import #x]` steps should appear to maintain environment consistency.
	 */
	public function test_complex_scenario_mixed_actions() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-a',
				'test_tag'              => 'tag-a',
				'type'                  => 'type-a',
				'action'                => 'test',
				'path_in_php_container' => 'test-a-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-a',
			],
			[
				'slug'                  => 'test-plugin-b',
				'test_tag'              => 'tag-b',
				'type'                  => 'type-b',
				'action'                => 'test',
				'path_in_php_container' => 'test-b-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-b',
			],
			[
				'slug'                  => 'bootstrap-c',
				'test_tag'              => 'tag-c',
				'type'                  => 'type-c',
				'action'                => 'bootstrap',
				'path_in_php_container' => 'boot-c-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/bootstrap-c',
			],
			[
				'slug'                  => 'activate-plugin-d',
				'test_tag'              => 'tag-d',
				'type'                  => 'type-d',
				'action'                => 'activate',
				'path_in_php_container' => 'activate-d-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/activate-plugin-d',
			],
			[
				'slug'                  => 'test-plugin-e',
				'test_tag'              => 'tag-e',
				'type'                  => 'type-e',
				'action'                => 'test',
				'path_in_php_container' => 'test-e-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-e',
			],
		];

		// Plugin A: full lifecycle: shared & isolated setups, tests, and teardowns
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.sh', 'setup.sh', 'teardown.sh', 'shared-teardown.php', 'shared-teardown.sh', 'setup.php' ]
		);
		file_put_contents( $test_infos[0]['path_in_host'] . '/test.spec.js', "// test spec A" );

		// Plugin B: test plugin partial: only isolated setup.php and test
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'setup.php', 'teardown.js' ] // has isolated setup.php and isolated teardown.js
		);
		file_put_contents( $test_infos[1]['path_in_host'] . '/test.spec.js', "// test spec B" );

		// Plugin C: bootstrap plugin with shared setups/teardowns only
		$this->createSetupFiles(
			$test_infos[2]['path_in_host'],
			[ 'shared-setup.js', 'shared-teardown.sh' ]
		);

		// Plugin D: activate plugin - just ensure directory exists
		if ( ! file_exists( $test_infos[3]['path_in_host'] ) ) {
			mkdir( $test_infos[3]['path_in_host'], 0777, true );
		}

		// Plugin E: test plugin with no shared setups, only isolated teardown and a spec
		$this->createSetupFiles(
			$test_infos[4]['path_in_host'],
			[ 'teardown.php' ] // only isolated teardown
		);
		file_put_contents( $test_infos[4]['path_in_host'] . '/test.spec.js', "// test spec E" );

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );

		// Cleanup
		unlink( $test_infos[0]['path_in_host'] . '/test.spec.js' );
		unlink( $test_infos[1]['path_in_host'] . '/test.spec.js' );
		unlink( $test_infos[4]['path_in_host'] . '/test.spec.js' );

		foreach ( $test_infos as $info ) {
			$this->cleanup( $info['path_in_host'] );
		}
	}

	public function test_conflicting_db_import_steps() {
		$test_infos = [
			// Three test plugins to force multiple db imports.
			[
				'slug'                  => 'test-plugin-one',
				'test_tag'              => 'tag-one',
				'type'                  => 'type-one',
				'action'                => 'test',
				'path_in_php_container' => 'test-one-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-one',
			],
			[
				'slug'                  => 'test-plugin-two',
				'test_tag'              => 'tag-two',
				'type'                  => 'type-two',
				'action'                => 'test',
				'path_in_php_container' => 'test-two-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-two',
			],
			[
				'slug'                  => 'test-plugin-three',
				'test_tag'              => 'tag-three',
				'type'                  => 'type-three',
				'action'                => 'test',
				'path_in_php_container' => 'test-three-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-three',
			],
			// One bootstrap plugin to ensure shared steps run and possibly another db import before shared teardown
			[
				'slug'                  => 'bootstrap-plugin',
				'test_tag'              => 'tag-boot',
				'type'                  => 'type-boot',
				'action'                => 'bootstrap',
				'path_in_php_container' => 'boot-container-path',
				'path_in_host'          => sys_get_temp_dir() . '/bootstrap-plugin',
			],
		];

		// Setup for each test plugin: minimal isolated setups and a spec file.
		// Plugin one: a shared setup to force DB export.
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[ 'shared-setup.sh', 'setup.sh', 'teardown.sh' ]
		);
		file_put_contents( $test_infos[0]['path_in_host'] . '/test.spec.js', "// spec one" );

		// Plugin two: another setup and spec to ensure a second db import occurs
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			[ 'setup.js', 'teardown.php' ]
		);
		file_put_contents( $test_infos[1]['path_in_host'] . '/test.spec.js', "// spec two" );

		// Plugin three: also has a setup and spec to force a third db import
		$this->createSetupFiles(
			$test_infos[2]['path_in_host'],
			[ 'setup.php', 'teardown.js' ]
		);
		file_put_contents( $test_infos[2]['path_in_host'] . '/test.spec.js', "// spec three" );

		// Bootstrap plugin: shared teardown only to ensure one last db import before tear down
		$this->createSetupFiles(
			$test_infos[3]['path_in_host'],
			[ 'shared-setup.php', 'shared-teardown.js' ]
		);

		$sut      = $this->make_sut();
		$projects = $sut->make_projects( $test_infos );

		// Assert against snapshot to verify we see something like:
		// [db import], [db import #2], [db import #3], etc.
		$this->assertMatchesJsonSnapshot( $projects );

		// Cleanup
		foreach ( $test_infos as $info ) {
			if ( file_exists( $info['path_in_host'] . '/test.spec.js' ) ) {
				unlink( $info['path_in_host'] . '/test.spec.js' );
			}
			$this->cleanup( $info['path_in_host'] );
		}
	}

	public function test_multiple_plugins_no_shared_no_initial_setup() {
		$test_infos = [
			[
				'slug'                  => 'test-plugin-a',
				'test_tag'              => 'tag-a',
				'type'                  => 'type-a',
				'action'                => 'test',
				'path_in_php_container' => 'container-a',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-a',
			],
			[
				'slug'                  => 'test-plugin-b',
				'test_tag'              => 'tag-b',
				'type'                  => 'type-b',
				'action'                => 'test',
				'path_in_php_container' => 'container-b',
				'path_in_host'          => sys_get_temp_dir() . '/test-plugin-b',
			],
		];

		// Just ensure minimal environment: create directories, add test.spec.js files
		foreach ( $test_infos as $info ) {
			mkdir( $info['path_in_host'], 0777, true );
			file_put_contents( $info['path_in_host'] . '/test.spec.js', '// dummy' );
		}

		$sut = $this->make_sut();
		$projects = $sut->make_projects( $test_infos );
		$this->assertMatchesJsonSnapshot( $projects );

		// Cleanup
		foreach ( $test_infos as $info ) {
			unlink( $info['path_in_host'] . '/test.spec.js' );
			rmdir( $info['path_in_host'] );
		}
	}
}
