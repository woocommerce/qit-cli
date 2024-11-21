<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

class PlaywrightOrchestration {
	/**
	 * // phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName
	 *
	 * @param array<int,array{
	 *      slug:string,
	 *      test_tag:string,
	 *      type:string,
	 *      action:string,
	 *      path_in_php_container:string,
	 *      path_in_host:string
	 *  }> $test_infos
	 *
	 * // phpcs:enable Squiz.Commenting.FunctionComment.MissingParamName
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function make_projects( array $test_infos ): array {
		$projects         = [];
		$last_setup       = null;
		$project_counter  = 1;
		$multiple_plugins = count( $test_infos ) > 1;

		// Shared setups first.
		foreach ( $test_infos as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];

			// Shared setups (sh, php, js).
			if ( file_exists( "{$host_path}/bootstrap/shared-setup.sh" ) ) {
				$name       = sprintf( '%02d-%s-shared-setup-sh', $project_counter++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Setup for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/shared-setup.sh",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-setup.php" ) ) {
				$name       = sprintf( '%02d-%s-shared-setup-php', $project_counter++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Setup for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/shared-setup.php",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-setup.js" ) ) {
				$name       = sprintf( '%02d-%s-shared-setup-js', $project_counter++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => 'shared-setup.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'browserName' => 'chromium',
					],
				];
				$last_setup = $name;
			}
		}

		// Track the last operation for dependency chain.
		$last_operation = $last_setup;

		// DB Export only for multiple plugins.
		if ( $multiple_plugins ) {
			$name           = sprintf( '%02d-db-export', $project_counter++ );
			$projects[]     = [
				'name'         => $name,
				'testDir'      => '/qit/tests/e2e/scripts',
				'testMatch'    => 'db-export.js',
				'dependencies' => $last_operation ? [ $last_operation ] : [],
				'use'          => [ 'browserName' => 'chromium' ],
			];
			$last_operation = $name;
		}

		$first_test = true;

		// Tests with conditional DB import and isolated setups.
		foreach ( $test_infos as $t ) {
			$plugin_slug   = $t['slug'];
			$base_dir      = $t['path_in_php_container'];
			$host_path     = $t['path_in_host'];
			$current_setup = $last_operation ?: null;

			if ( ! $first_test && $multiple_plugins ) {
				// Add DB import before subsequent tests - depends on previous plugin's complete chain.
				$import_name   = sprintf( '%02d-db-import-before-%s', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $import_name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'db-import.js',
					'dependencies' => [ $last_operation ],
					'use'          => [ 'browserName' => 'chromium' ],
				];
				$current_setup = $import_name;
			}

			// Isolated setups (sh, php, js).
			if ( file_exists( "{$host_path}/bootstrap/setup.sh" ) ) {
				$name          = sprintf( '%02d-%s-isolated-setup-sh', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Setup for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/setup.sh",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/setup.php" ) ) {
				$name          = sprintf( '%02d-%s-isolated-setup-php', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Setup for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/setup.php",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/setup.js" ) ) {
				$name          = sprintf( '%02d-%s-isolated-setup-js', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => 'setup.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$current_setup = $name;
			}

			// Add the actual test phase.
			$test_name     = sprintf( '%02d-%s-test', $project_counter++, $plugin_slug );
			$projects[]    = [
				'name'         => $test_name,
				'testDir'      => $base_dir,
				'testMatch'    => '**/*.spec.js',
				'dependencies' => $current_setup ? [ $current_setup ] : [],
				'use'          => [
					'browserName' => 'chromium',
					'qitTestSlug' => $plugin_slug,
				],
			];
			$current_setup = $test_name;

			// Add isolated teardowns.
			if ( file_exists( "{$host_path}/bootstrap/teardown.sh" ) ) {
				$name          = sprintf( '%02d-%s-isolated-teardown-sh', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => [ $current_setup ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Teardown for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/teardown.sh",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/teardown.php" ) ) {
				$name          = sprintf( '%02d-%s-isolated-teardown-php', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => [ $current_setup ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Teardown for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/teardown.php",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/teardown.js" ) ) {
				$name          = sprintf( '%02d-%s-isolated-teardown-js', $project_counter++, $plugin_slug );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => 'teardown.js',
					'dependencies' => [ $current_setup ],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$current_setup = $name;
			}

			// Update last_operation after all plugin operations (setup, test, teardown) are complete.
			$last_operation = $current_setup;
			$first_test     = false;
		}

		// Add shared teardowns in reverse order.
		foreach ( array_reverse( $test_infos ) as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.sh" ) ) {
				$name           = sprintf( '%02d-%s-shared-teardown-sh', $project_counter++, $plugin_slug );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => [ $last_operation ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Teardown for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/shared-teardown.sh",
					],
				];
				$last_operation = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.php" ) ) {
				$name           = sprintf( '%02d-%s-shared-teardown-php', $project_counter++, $plugin_slug );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => [ $last_operation ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Teardown for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/shared-teardown.php",
					],
				];
				$last_operation = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.js" ) ) {
				$name           = sprintf( '%02d-%s-shared-teardown-js', $project_counter++, $plugin_slug );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => 'shared-teardown.js',
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
