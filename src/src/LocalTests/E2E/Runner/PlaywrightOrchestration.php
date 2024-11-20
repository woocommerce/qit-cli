<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

class PlaywrightOrchestration {
	public function make_projects( array $test_infos ): array {
		$projects         = [];
		$last_setup       = null;
		$project_counter  = 1;
		$multiple_plugins = count( $test_infos ) > 1;

		// Shared setups first
		foreach ( $test_infos as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];

			// Shared setups (sh, php, js)
			if ( file_exists( "{$host_path}/bootstrap/shared-bootstrap.sh" ) ) {
				$name       = sprintf( "%02d-%s-shared-setup-sh", $project_counter ++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'bash',
						'file'        => "{$base_dir}/bootstrap/shared-bootstrap.sh",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-bootstrap.php" ) ) {
				$name       = sprintf( "%02d-%s-shared-setup-php", $project_counter ++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'php',
						'file'        => "{$base_dir}/bootstrap/shared-bootstrap.php",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-bootstrap.js" ) ) {
				$name       = sprintf( "%02d-%s-shared-setup-js", $project_counter ++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => "shared-bootstrap.js",
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$last_setup = $name;
			}
		}

		// Track the last operation for dependency chain
		$last_operation = $last_setup;

		// DB Export only for multiple plugins
		if ( $multiple_plugins ) {
			$name           = sprintf( "%02d-db-export", $project_counter ++ );
			$projects[]     = [
				'name'         => $name,
				'testDir'      => '/qit/tests/e2e',
				'testMatch'    => 'db-export.js',
				'dependencies' => $last_operation ? [ $last_operation ] : [],
				'use'          => [ 'browserName' => 'chromium' ],
			];
			$last_operation = $name;
		}

		$first_test = true;

		// Tests with conditional DB import and isolated setups
		foreach ( $test_infos as $t ) {
			$plugin_slug   = $t['slug'];
			$base_dir      = $t['path_in_php_container'];
			$host_path     = $t['path_in_host'];
			$current_setup = $last_operation ?: null; // Initialize as null if no previous operations

			if ( ! $first_test && $multiple_plugins ) {
				// Add DB import before subsequent tests
				$import_name   = sprintf( "%02d-db-import-before-%s", $project_counter ++, $plugin_slug );
				$projects[]    = [
					'name'         => $import_name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'db-import.js',
					'dependencies' => $last_operation ? [ $last_operation ] : [],
					'use'          => [ 'browserName' => 'chromium' ],
				];
				$current_setup = $import_name;
			}

			// Isolated setups (sh, php, js)
			if ( file_exists( "{$host_path}/bootstrap/bootstrap.sh" ) ) {
				$name          = sprintf( "%02d-%s-isolated-setup-sh", $project_counter ++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'bash',
						'file'        => "{$base_dir}/bootstrap/bootstrap.sh",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/bootstrap.php" ) ) {
				$name          = sprintf( "%02d-%s-isolated-setup-php", $project_counter ++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'php',
						'file'        => "{$base_dir}/bootstrap/bootstrap.php",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/bootstrap.js" ) ) {
				$name          = sprintf( "%02d-%s-isolated-setup-js", $project_counter ++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => "bootstrap.js",
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$current_setup = $name;
			}

			// Add the actual test phase
			$name       = sprintf( "%02d-%s-test", $project_counter ++, $plugin_slug );
			$projects[] = [
				'name'         => $name,
				'testDir'      => $base_dir,
				'testMatch'    => '**/*.spec.js',
				'dependencies' => $current_setup ? [ $current_setup ] : [],
				'use'          => [
					'browserName' => 'chromium',
					'qitTestSlug' => $plugin_slug,
				],
			];

			// Isolated teardowns (runs after plugin's tests)
			if ( file_exists( "{$host_path}/bootstrap/teardown.sh" ) ) {
				$name          = sprintf( "%02d-%s-isolated-teardown-sh", $project_counter ++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => [ $current_setup ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'bash',
						'file'        => "{$base_dir}/bootstrap/teardown.sh",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/teardown.js" ) ) {
				$name          = sprintf( "%02d-%s-isolated-teardown-js", $project_counter ++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => "teardown.js",
					'dependencies' => [ $current_setup ],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$current_setup = $name;
			}

			$last_operation = $name;
			$first_test     = false;
		}

		// Shared teardowns (runs after all tests are complete)
		foreach ( array_reverse( $test_infos ) as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.sh" ) ) {
				$name           = sprintf( "%02d-%s-shared-teardown-sh", $project_counter ++, $plugin_slug );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => [ $last_operation ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'bash',
						'file'        => "{$base_dir}/bootstrap/shared-teardown.sh",
					],
				];
				$last_operation = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.js" ) ) {
				$name           = sprintf( "%02d-%s-shared-teardown-js", $project_counter ++, $plugin_slug );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => "shared-teardown.js",
					'dependencies' => [ $last_operation ],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$last_operation = $name;
			}
		}

		return $projects;
	}
}