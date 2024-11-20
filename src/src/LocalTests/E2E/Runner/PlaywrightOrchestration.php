<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

class PlaywrightOrchestration {
	/**
	 * // phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName
	 *
	 * @param array<int,array{
	 *     slug:string,
	 *     test_tag:string,
	 *     type:string,
	 *     action:string,
	 *     path_in_php_container:string,
	 *     path_in_host:string
	 *  }> $test_infos
	 *
	 * // phpcs:enable
	 *
	 * @return array<int,array<string,scalar>>
	 */
	public function make_projects( array $test_infos ): array {
		$projects        = [];
		$last_setup      = null;
		$project_counter = 1;

		foreach ( $test_infos as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];

			// Shared setups (sh, php, js)
			if ( file_exists( "{$host_path}/bootstrap/shared-setup.sh" ) ) {
				$name       = sprintf( "%02d-%s-shared-setup-sh", $project_counter ++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'bash',
						'file'        => "{$base_dir}/bootstrap/shared-setup.sh",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-setup.php" ) ) {
				$name       = sprintf( "%02d-%s-shared-setup-php", $project_counter ++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'qit-bootstrap.js',
					'dependencies' => [ $last_setup ],
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => 'php',
						'file'        => "{$base_dir}/bootstrap/shared-setup.php",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-setup.js" ) ) {
				$name       = sprintf( "%02d-%s-shared-setup-js", $project_counter ++, $plugin_slug );
				$projects[] = [
					'name'         => $name,
					'testDir'      => "{$base_dir}/bootstrap",
					'testMatch'    => "shared-setup.js",
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'use'          => [
						'browserName' => 'chromium',
						'qitTestSlug' => $plugin_slug,
					],
				];
				$last_setup = $name;
			}
		}

		// DB Export after shared setups
		$name       = sprintf( "%02d-db-export", $project_counter ++ );
		$projects[] = [
			'name'         => $name,
			'testDir'      => '/qit/tests/e2e',
			'testMatch'    => 'db-export.js',
			'dependencies' => [ $last_setup ],
			'use'          => [ 'browserName' => 'chromium' ],
		];

		$last_test  = $name;
		$first_test = true;

		// Tests with conditional DB import
		foreach ( $test_infos as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];

			if ( ! $first_test ) {
				// Add DB import before subsequent tests
				$import_name = sprintf( "%02d-db-import-before-%s", $project_counter ++, $plugin_slug );
				$projects[]  = [
					'name'         => $import_name,
					'testDir'      => '/qit/tests/e2e',
					'testMatch'    => 'db-import.js',
					'dependencies' => [ $last_test ],
					'use'          => [ 'browserName' => 'chromium' ],
				];
				$last_test   = $import_name;
			}

			$name       = sprintf( "%02d-%s-test", $project_counter ++, $plugin_slug );
			$projects[] = [
				'name'         => $name,
				'testDir'      => $base_dir,
				'testMatch'    => '**/*.spec.js',
				'dependencies' => [ $last_test ],
				'use'          => [
					'browserName' => 'chromium',
					'qitTestSlug' => $plugin_slug,
				],
			];
			$last_test  = $name;
			$first_test = false;
		}

		return $projects;
	}
}