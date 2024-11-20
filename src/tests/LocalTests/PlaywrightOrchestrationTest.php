<?php

namespace QIT_CLI_Tests\LocalTests;

use QIT_CLI\App;
use QIT_CLI\LocalTests\E2E\Runner\PlaywrightOrchestration;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PlaywrightOrchestrationTest extends QITTestCase {
	use MatchesSnapshots;

	protected function make_sut(): PlaywrightOrchestration {
		return App::make(PlaywrightOrchestration::class);
	}

	protected function tearDown(): void {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one',
			],
			[
				'slug' => 'plugin-two',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two',
			],
			[
				'slug' => 'test-slug',
				'path_in_host' => sys_get_temp_dir() . '/test-plugin',
			]
		];

		foreach ($test_infos as $t) {
			$path_in_host = $t['path_in_host'];
			if (file_exists($path_in_host)) {
				$this->cleanup($path_in_host . '/bootstrap');
			}
		}
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
		/** @var array<int,array{
		 *     slug:string,
		 *     test_tag:string,
		 *     type:string,
		 *     action:string,
		 *     path_in_php_container:string,
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
				'path_in_host'                 => 'test-path-in-host',
			],
		];

		$sut = $this->make_sut();

		$this->assertMatchesJsonSnapshot( $sut->make_projects( $test_infos ) );
	}

	public function test_single_plugin_with_shared_setup() {
		$test_infos = [
			[
				'slug' => 'test-slug',
				'test_tag' => 'test-tag',
				'type' => 'test-type',
				'action' => 'test-action',
				'path_in_php_container' => 'test-path-in-php-container',
				'path_in_host' => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// Create the directory and the shared setup file
		$path_in_host = $test_infos[0]['path_in_host'];
		$bootstrap_dir = $path_in_host . '/bootstrap';
		mkdir($bootstrap_dir, 0777, true);
		file_put_contents($bootstrap_dir . '/shared-bootstrap.js', '// shared setup');

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		// Clean up
		unlink($bootstrap_dir . '/shared-bootstrap.js');
		rmdir($bootstrap_dir);
		rmdir($path_in_host);
	}

	public function test_single_plugin_with_isolated_setup() {
		$test_infos = [
			[
				'slug' => 'test-slug',
				'test_tag' => 'test-tag',
				'type' => 'test-type',
				'action' => 'test-action',
				'path_in_php_container' => 'test-path-in-php-container',
				'path_in_host' => sys_get_temp_dir() . '/test-plugin',
			],
		];

		// Create the directory and the isolated setup file
		$path_in_host = $test_infos[0]['path_in_host'];
		$bootstrap_dir = $path_in_host . '/bootstrap';
		mkdir($bootstrap_dir, 0777, true);
		file_put_contents($bootstrap_dir . '/bootstrap.js', '// isolated setup');

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		// Clean up
		unlink($bootstrap_dir . '/bootstrap.js');
		rmdir($bootstrap_dir);
		rmdir($path_in_host);
	}

	public function test_single_plugin_with_all_shared_setups() {
		$test_infos = [[
			'slug' => 'test-slug',
			'test_tag' => 'test-tag',
			'type' => 'test-type',
			'action' => 'test-action',
			'path_in_php_container' => 'test-path-in-php-container',
			'path_in_host' => sys_get_temp_dir() . '/test-plugin'
		]];

		$bootstrap_dir = $this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			['shared-bootstrap.sh', 'shared-bootstrap.php', 'shared-bootstrap.js']
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($bootstrap_dir);
	}

	public function test_multiple_plugins_with_mixed_setups() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		// Plugin one with shell and PHP setups
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			['shared-bootstrap.sh', 'shared-bootstrap.php', 'bootstrap.sh']
		);

		// Plugin two with PHP and JS setups
		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			['shared-bootstrap.php', 'shared-bootstrap.js', 'bootstrap.js']
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($test_infos[0]['path_in_host'] . '/bootstrap');
		$this->cleanup($test_infos[1]['path_in_host'] . '/bootstrap');
	}

	public function test_multiple_plugins_without_setups() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));
	}

	public function test_multiple_plugins_with_shared_setups_only() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		// Create shared setup files for both plugins
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			['shared-bootstrap.sh', 'shared-bootstrap.php', 'shared-bootstrap.js']
		);

		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			['shared-bootstrap.sh', 'shared-bootstrap.php', 'shared-bootstrap.js']
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($test_infos[0]['path_in_host'] . '/bootstrap');
		$this->cleanup($test_infos[1]['path_in_host'] . '/bootstrap');
	}

	public function test_multiple_plugins_with_isolated_setups_only() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		// Create isolated setup files for both plugins
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			['bootstrap.sh', 'bootstrap.php', 'bootstrap.js']
		);

		$this->createSetupFiles(
			$test_infos[1]['path_in_host'],
			['bootstrap.sh', 'bootstrap.php', 'bootstrap.js']
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($test_infos[0]['path_in_host'] . '/bootstrap');
		$this->cleanup($test_infos[1]['path_in_host'] . '/bootstrap');
	}

	public function test_single_plugin_with_mixed_setups() {
		$test_infos = [
			[
				'slug' => 'test-slug',
				'test_tag' => 'test-tag',
				'type' => 'test-type',
				'action' => 'test-action',
				'path_in_php_container' => 'test-path-in-php-container',
				'path_in_host' => sys_get_temp_dir() . '/test-plugin'
			]
		];

		// Create both shared and isolated setup files
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[
				'shared-bootstrap.sh',
				'shared-bootstrap.php',
				'shared-bootstrap.js',
				'bootstrap.sh',
				'bootstrap.php',
				'bootstrap.js'
			]
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($test_infos[0]['path_in_host'] . '/bootstrap');
	}

	public function test_multiple_plugins_with_all_setup_types() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		// Create all types of setup files for both plugins
		foreach ($test_infos as $info) {
			$this->createSetupFiles(
				$info['path_in_host'],
				[
					'shared-bootstrap.sh',
					'shared-bootstrap.php',
					'shared-bootstrap.js',
					'bootstrap.sh',
					'bootstrap.php',
					'bootstrap.js'
				]
			);
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		foreach ($test_infos as $info) {
			$this->cleanup($info['path_in_host'] . '/bootstrap');
		}
	}

	/**
	 * Test a single plugin with isolated teardown.
	 *
	 * **Scenario:**
	 * - One plugin under test
	 * - Only isolated teardown files exist
	 *
	 * **Expected Behavior:**
	 * - Test phase followed by teardown phase
	 */
	public function test_single_plugin_with_isolated_teardown() {
		$test_infos = [
			[
				'slug' => 'test-slug',
				'test_tag' => 'test-tag',
				'type' => 'test-type',
				'action' => 'test-action',
				'path_in_php_container' => 'test-path-in-php-container',
				'path_in_host' => sys_get_temp_dir() . '/test-plugin'
			]
		];

		// Create only teardown files
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			['teardown.sh', 'teardown.js']
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($test_infos[0]['path_in_host'] . '/bootstrap');
	}

	/**
	 * Test a single plugin with both shared and isolated teardowns.
	 *
	 * **Scenario:**
	 * - One plugin under test
	 * - Has both shared and isolated teardowns
	 *
	 * **Expected Behavior:**
	 * - Test phase
	 * - Isolated teardown
	 * - Shared teardown
	 */
	public function test_single_plugin_with_all_teardowns() {
		$test_infos = [
			[
				'slug' => 'test-slug',
				'test_tag' => 'test-tag',
				'type' => 'test-type',
				'action' => 'test-action',
				'path_in_php_container' => 'test-path-in-php-container',
				'path_in_host' => sys_get_temp_dir() . '/test-plugin'
			]
		];

		// Create both shared and isolated teardown files
		$this->createSetupFiles(
			$test_infos[0]['path_in_host'],
			[
				'teardown.sh',
				'teardown.js',
				'shared-teardown.sh',
				'shared-teardown.js'
			]
		);

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		$this->cleanup($test_infos[0]['path_in_host'] . '/bootstrap');
	}

	/**
	 * Test multiple plugins with isolated teardowns.
	 *
	 * **Scenario:**
	 * - Multiple plugins under test
	 * - Each has isolated teardown
	 *
	 * **Expected Behavior:**
	 * - Plugin 1: test -> isolated teardown
	 * - DB operations
	 * - Plugin 2: test -> isolated teardown
	 */
	public function test_multiple_plugins_with_isolated_teardowns() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		// Create isolated teardown files for both plugins
		foreach ($test_infos as $info) {
			$this->createSetupFiles(
				$info['path_in_host'],
				['teardown.sh', 'teardown.js']
			);
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		foreach ($test_infos as $info) {
			$this->cleanup($info['path_in_host'] . '/bootstrap');
		}
	}

	/**
	 * Test complete setup with everything - setups and teardowns.
	 *
	 * **Scenario:**
	 * - Multiple plugins under test
	 * - All types of setups and teardowns
	 *
	 * **Expected Behavior:**
	 * - Shared setups for all plugins
	 * - DB export
	 * - For each plugin:
	 *   - DB import (if not first)
	 *   - Isolated setup
	 *   - Test
	 *   - Isolated teardown
	 * - Shared teardowns in reverse order
	 */
	public function test_multiple_plugins_with_all_setups_and_teardowns() {
		$test_infos = [
			[
				'slug' => 'plugin-one',
				'test_tag' => 'test-tag-1',
				'type' => 'test-type-1',
				'action' => 'test-action-1',
				'path_in_php_container' => 'test-path-in-php-container-1',
				'path_in_host' => sys_get_temp_dir() . '/plugin-one'
			],
			[
				'slug' => 'plugin-two',
				'test_tag' => 'test-tag-2',
				'type' => 'test-type-2',
				'action' => 'test-action-2',
				'path_in_php_container' => 'test-path-in-php-container-2',
				'path_in_host' => sys_get_temp_dir() . '/plugin-two'
			]
		];

		// Create all types of files for both plugins
		foreach ($test_infos as $info) {
			$this->createSetupFiles(
				$info['path_in_host'],
				[
					'shared-bootstrap.sh',
					'shared-bootstrap.php',
					'shared-bootstrap.js',
					'bootstrap.sh',
					'bootstrap.php',
					'bootstrap.js',
					'teardown.sh',
					'teardown.js',
					'shared-teardown.sh',
					'shared-teardown.js'
				]
			);
		}

		$sut = $this->make_sut();
		$this->assertMatchesJsonSnapshot($sut->make_projects($test_infos));

		foreach ($test_infos as $info) {
			$this->cleanup($info['path_in_host'] . '/bootstrap');
		}
	}

	protected function createSetupFiles($path, $files) {
		$bootstrap_dir = $path . '/bootstrap';
		mkdir($bootstrap_dir, 0777, true);

		foreach ($files as $file) {
			file_put_contents("$bootstrap_dir/$file", "// Test content for $file");
		}

		return $bootstrap_dir;
	}

	protected function cleanup($bootstrap_dir) {
		array_map('unlink', glob("$bootstrap_dir/*"));
		rmdir($bootstrap_dir);
		rmdir(dirname($bootstrap_dir));
	}
}