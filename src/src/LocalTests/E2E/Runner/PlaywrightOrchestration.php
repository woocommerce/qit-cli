<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\Environment\Extension;

/**
 * Class PlaywrightOrchestration.
 *
 * This class orchestrates and generates a list of Playwright test projects
 * for running various E2E test scenarios against WordPress plugins.
 */
class PlaywrightOrchestration {
	/**
	 * Generates an array of projects for testing.
	 *
	 * // phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName
	 *
	 * @param array<int,array{
	 *      slug:string,
	 *      test_tag:string,
	 *      type:string,
	 *      action:string,
	 *      path_in_php_container:string,
	 *      path_in_host:string
	 * }> $test_infos The array of test information.
	 *
	 * // phpcs:enable Squiz.Commenting.FunctionComment.MissingParamName
	 *
	 * @return array<int,array<string,mixed>> Returns a structured array of project configurations.
	 */
	public function make_projects( array $test_infos ): array {
		$projects        = [];
		$project_counter = 1;

		// Validate actions.
		foreach ( $test_infos as $t ) {
			if ( ! in_array( $t['action'], Extension::ACTIONS, true ) ) {
				throw new \InvalidArgumentException( "Invalid action '{$t['action']}' for plugin '{$t['slug']}'." );
			}
		}

		$multiple_plugins     = count( $test_infos ) > 1;
		$has_test_plugin      = false;
		$has_bootstrap_plugin = false;
		$has_activate_plugin  = false;

		foreach ( $test_infos as $t ) {
			if ( $t['action'] === 'test' ) {
				$has_test_plugin = true;
			} elseif ( $t['action'] === 'bootstrap' ) {
				$has_bootstrap_plugin = true;
			} elseif ( $t['action'] === 'activate' ) {
				$has_activate_plugin = true;
			}
		}

		// Helper functions.
		$get_plugin_name = function ( string $slug ): string {
			return ucwords( str_replace( [ 'woocommerce-', '-' ], [ '', ' ' ], $slug ) );
		};

		$format_name = function ( string $name ) use ( &$project_counter ): string {
			if ( in_array( App::getVar( 'TEST_MODE' ), [ E2ETestManager::$test_modes['codegen'], E2ETestManager::$test_modes['ui'] ], true ) ) {
				return sprintf( '%02d - %s', $project_counter++, $name );
			}

			return $name;
		};

		// Determine if we run shared steps.
		// We run shared setup/teardown if we have at least one plugin with bootstrap or test action.
		$run_shared_steps = $has_test_plugin || $has_bootstrap_plugin;

		$last_setup = null;

		// Run shared setup steps if applicable.
		if ( $run_shared_steps ) {
			foreach ( $test_infos as $t ) {
				$plugin_slug = $t['slug'];
				$base_dir    = $t['path_in_php_container'];
				$host_path   = $t['path_in_host'];
				$plugin_name = $get_plugin_name( $plugin_slug );

				// Shared setup scripts (sh, php, js).
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
		}

		$last_operation = $last_setup;

		// If we have multiple plugins and at least one test or bootstrap plugin, we do a DB export.
		// DB export is only necessary if there's a scenario where isolated tests can run (test action).
		// or if multiple plugins require a baseline snapshot (for bootstrap).
		$need_db_export = $multiple_plugins && ( $has_test_plugin || $has_bootstrap_plugin );
		if ( $need_db_export ) {
			$name           = $format_name( '[db export]' );
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

		// Process each plugin depending on its action.
		foreach ( $test_infos as $t ) {
			$action      = $t['action'];
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];
			$plugin_name = $get_plugin_name( $plugin_slug );

			$current_setup = $last_operation ?: null;

			if ( $action === 'activate' ) {
				// Just activate the plugin. No shared/isolated steps, no tests.
				// We'll run a bash command to activate the plugin.
				$name          = $format_name( "[activate] $plugin_name" );
				$projects[]    = [
					'name'         => $name,
					'testDir'      => '/qit/tests/e2e/scripts',
					'testMatch'    => 'bash.js',
					'dependencies' => $current_setup ? [ $current_setup ] : [],
					'retries'      => 0,
					'use'          => [
						'qitTestSlug' => $plugin_slug,
						'type'        => "Activate $plugin_slug (Bash)",
						'file'        => null,
						'command'     => "wp plugin activate {$plugin_slug}",
					],
				];
				$current_setup = $name;

				$last_operation = $current_setup;
				continue;
			}

			if ( $action === 'bootstrap' || $action === 'test' ) {
				// If not the first test/bootstrap and we have multiple plugins, do a DB import.
				// Only needed if we had a DB export and we're running a scenario that involves isolation.
				if ( ! $first_test && $need_db_export ) {
					$name          = $format_name( '[db import]' );
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
			}

			if ( $action === 'bootstrap' ) {
				// "bootstrap" implies multiple plugins scenario might need a consistent baseline.
				// No isolated setup/teardown, no tests for bootstrap itself.
				$last_operation = $current_setup;
				$first_test     = false;
				continue;
			}

			if ( $action === 'test' ) {
				// Test action: run isolated setup(s), test, isolated teardown(s).

				// Isolated setups.
				if ( file_exists( "{$host_path}/bootstrap/setup.sh" ) ) {
					$name          = $format_name( "[setup] $plugin_name (Shell)" );
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

				// Test phase.
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

				// Isolated teardowns.
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
		}

		// Shared teardowns run in reverse order, but only if we ran shared steps.
		if ( $run_shared_steps ) {
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
		}

		return $projects;
	}
}
