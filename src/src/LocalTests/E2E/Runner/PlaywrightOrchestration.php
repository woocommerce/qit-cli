<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\LocalTests\E2E\E2ETestManager;

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

		// Helper function to get readable plugin name.
		$get_plugin_name = function ( string $slug ): string {
			return ucwords( str_replace( [ 'woocommerce-', '-' ], [ '', ' ' ], $slug ) );
		};

		// Helper function to format project name with counter.
		$format_name = function ( string $name ) use ( &$project_counter ): string {
			/*
			 * In UI mode, prefixes the project name with a counter (e.g., "01 - [test] Plugin (Run)").
			 * This provides visual ordering of test order since dependencies aren't visible when running in UI mode.
			 */
			if ( in_array( App::getVar( 'TEST_MODE' ), [ E2ETestManager::$test_modes['codegen'], E2ETestManager::$test_modes['ui'] ], true ) ) {
				return sprintf( '%02d - %s', $project_counter ++, $name );
			} else {
				return $name;
			}
		};

		// Shared setups first.
		foreach ( $test_infos as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];
			$plugin_name = $get_plugin_name( $plugin_slug );

			// Shared setups (sh, php, js).
			if ( file_exists( "{$host_path}/bootstrap/shared-setup.sh" ) ) {
				$name       = $format_name( "[setup:shared] $plugin_name (Shell)" );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Setup for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/shared-setup.sh",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-setup.php" ) ) {
				$name       = $format_name( "[setup:shared] $plugin_name (PHP)" );
				$projects[] = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => $last_setup ? [ $last_setup ] : [],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Setup for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/shared-setup.php",
					],
				];
				$last_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-setup.js" ) ) {
				$name       = $format_name( "[setup:shared] $plugin_name (JS)" );
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
			$name           = $format_name( '[db export' );
			$projects[]     = [
				'name'         => $name,
				'testDir'      => '/qit/tests/e2e/scripts',
				'testMatch'    => 'db-export.js',
				'retries'      => 0,
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
			$plugin_name   = $get_plugin_name( $plugin_slug );
			$current_setup = $last_operation ?: null;

			if ( ! $first_test && $multiple_plugins ) {
				// Add DB import before subsequent tests
				$name          = $format_name( "[db import" );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'db-import.js',
					'dependencies' => [ $last_operation ],
					'retries'      => 0,
					'use'          => [ 'browserName' => 'chromium' ],
				];
				$current_setup = $name;
			}

			// Isolated setups (sh, php, js).
			if ( file_exists( "{$host_path}/bootstrap/setup.sh" ) ) {
				$name = $format_name( "[setup] $plugin_name (Shell)" );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Setup for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/setup.sh",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/setup.php" ) ) {
				$name          = $format_name( "[setup] $plugin_name (PHP)" );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Setup for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/setup.php",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/setup.js" ) ) {
				$name          = $format_name( "[setup] $plugin_name (JS)" );
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
			$name          = $format_name( "[test] $plugin_name (Run)" );
			$projects[]    = [
				'name'         => $name,
				'testDir'      => $base_dir,
				'testMatch'    => '**/*.spec.js',
				'dependencies' => $current_setup ? [ $current_setup ] : [],
				'use'          => [
					'browserName' => 'chromium',
					'qitTestSlug' => $plugin_slug,
				],
			];
			$current_setup = $name;

			// Add isolated teardowns.
			if ( file_exists( "{$host_path}/bootstrap/teardown.sh" ) ) {
				$name          = $format_name( "[teardown] $plugin_name (Shell)" );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => [ $current_setup ],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Teardown for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/teardown.sh",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/teardown.php" ) ) {
				$name          = $format_name( "[teardown] $plugin_name (PHP)" );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => [ $current_setup ],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Isolated Teardown for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/teardown.php",
					],
				];
				$current_setup = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/teardown.js" ) ) {
				$name          = $format_name( "[teardown] $plugin_name (JS)" );
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

			$last_operation = $current_setup;
			$first_test     = false;
		}

		// Add shared teardowns in reverse order.
		foreach ( array_reverse( $test_infos ) as $t ) {
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];
			$plugin_name = $get_plugin_name( $plugin_slug );

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.sh" ) ) {
				$name           = $format_name( "[teardown:shared] $plugin_name (Shell)" );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => [ $last_operation ],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Teardown for $plugin_slug (Bash)",
						'file'        => "{$base_dir}/bootstrap/shared-teardown.sh",
					],
				];
				$last_operation = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.php" ) ) {
				$name           = $format_name( "[teardown:shared] $plugin_name (PHP)" );
				$projects[]     = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'php.js',
					'dependencies' => [ $last_operation ],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Shared Teardown for $plugin_slug (PHP)",
						'file'        => "{$base_dir}/bootstrap/shared-teardown.php",
					],
				];
				$last_operation = $name;
			}

			if ( file_exists( "{$host_path}/bootstrap/shared-teardown.js" ) ) {
				$name           = $format_name( "[teardown:shared] $plugin_name (JS)" );
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