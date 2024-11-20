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