<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

class PlaywrightOrchestration {
	public function make_projects(array $test_infos): array {
		$projects = [];
		$last_setup = null;
		$project_counter = 1;

		// Shared setups first
		foreach ($test_infos as $t) {
			$plugin_slug = $t['slug'];
			$base_dir = $t['path_in_php_container'];
			$host_path = $t['path_in_host'];

			// Shared setups (sh, php, js)
			if (file_exists("{$host_path}/bootstrap/shared-bootstrap.sh")) {
				$name = sprintf("%02d-%s-shared-setup-sh", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $name,
					'testDir' => '/qit/tests/e2e',
					'testMatch' => 'qit-bootstrap.js',
					'dependencies' => $last_setup ? [$last_setup] : [],
					'use' => [
						'qitTestSlug' => $plugin_slug,
						'type' => 'bash',
						'file' => "{$base_dir}/bootstrap/shared-bootstrap.sh",
					],
				];
				$last_setup = $name;
			}

			if (file_exists("{$host_path}/bootstrap/shared-bootstrap.php")) {
				$name = sprintf("%02d-%s-shared-setup-php", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $name,
					'testDir' => '/qit/tests/e2e',
					'testMatch' => 'qit-bootstrap.js',
					'dependencies' => $last_setup ? [$last_setup] : [], // Fixed potential null dependency
					'use' => [
						'qitTestSlug' => $plugin_slug,
						'type' => 'php',
						'file' => "{$base_dir}/bootstrap/shared-bootstrap.php",
					],
				];
				$last_setup = $name;
			}

			if (file_exists("{$host_path}/bootstrap/shared-bootstrap.js")) {
				$name = sprintf("%02d-%s-shared-setup-js", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $name,
					'testDir' => "{$base_dir}/bootstrap",
					'testMatch' => "shared-bootstrap.js",
					'dependencies' => $last_setup ? [$last_setup] : [],
					'use' => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$last_setup = $name;
			}
		}

		// DB Export after shared setups
		$name = sprintf("%02d-db-export", $project_counter++);
		$projects[] = [
			'name' => $name,
			'testDir' => '/qit/tests/e2e',
			'testMatch' => 'db-export.js',
			'dependencies' => $last_setup ? [$last_setup] : [], // Fixed null dependency
			'use' => ['browserName' => 'chromium'],
		];

		$last_test = $name;
		$first_test = true;

		// Tests with conditional DB import and isolated setups
		foreach ($test_infos as $t) {
			$plugin_slug = $t['slug'];
			$base_dir = $t['path_in_php_container'];
			$host_path = $t['path_in_host'];
			$last_setup = $last_test;

			if (!$first_test) {
				// Add DB import before subsequent tests
				$import_name = sprintf("%02d-db-import-before-%s", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $import_name,
					'testDir' => '/qit/tests/e2e',
					'testMatch' => 'db-import.js',
					'dependencies' => [$last_test],
					'use' => ['browserName' => 'chromium'],
				];
				$last_setup = $import_name;
			}

			// Isolated setups (sh, php, js)
			if (file_exists("{$host_path}/bootstrap/bootstrap.sh")) {
				$name = sprintf("%02d-%s-isolated-setup-sh", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $name,
					'testDir' => '/qit/tests/e2e',
					'testMatch' => 'qit-bootstrap.js',
					'dependencies' => [$last_setup],
					'use' => [
						'qitTestSlug' => $plugin_slug,
						'type' => 'bash',
						'file' => "{$base_dir}/bootstrap/bootstrap.sh",
					],
				];
				$last_setup = $name;
			}

			if (file_exists("{$host_path}/bootstrap/bootstrap.php")) {
				$name = sprintf("%02d-%s-isolated-setup-php", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $name,
					'testDir' => '/qit/tests/e2e',
					'testMatch' => 'qit-bootstrap.js',
					'dependencies' => [$last_setup],
					'use' => [
						'qitTestSlug' => $plugin_slug,
						'type' => 'php',
						'file' => "{$base_dir}/bootstrap/bootstrap.php",
					],
				];
				$last_setup = $name;
			}

			if (file_exists("{$host_path}/bootstrap/bootstrap.js")) {
				$name = sprintf("%02d-%s-isolated-setup-js", $project_counter++, $plugin_slug);
				$projects[] = [
					'name' => $name,
					'testDir' => "{$base_dir}/bootstrap",
					'testMatch' => "bootstrap.js",
					'dependencies' => [$last_setup],
					'use' => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$last_setup = $name;
			}

			// Add the actual test phase
			$name = sprintf("%02d-%s-test", $project_counter++, $plugin_slug);
			$projects[] = [
				'name' => $name,
				'testDir' => $base_dir,
				'testMatch' => '**/*.spec.js',
				'dependencies' => [$last_setup],
				'use' => [
					'browserName' => 'chromium',
					'qitTestSlug' => $plugin_slug,
				],
			];
			$last_test = $name;
			$first_test = false;
		}

		return $projects;
	}
}