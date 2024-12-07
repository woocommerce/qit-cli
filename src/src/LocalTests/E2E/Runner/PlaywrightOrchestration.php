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

		// Determine if there's any shared teardown.
		$has_shared_teardown = false;
		foreach ( $test_infos as $ti ) {
			$hp = $ti['path_in_host'];
			if (
				file_exists( "{$hp}/bootstrap/shared-teardown.sh" ) ||
				file_exists( "{$hp}/bootstrap/shared-teardown.php" ) ||
				file_exists( "{$hp}/bootstrap/shared-teardown.js" )
			) {
				$has_shared_teardown = true;
				break;
			}
		}

		// Count how many plugins have a 'test' action.
		$test_plugins_count = 0;
		foreach ( $test_infos as $ti ) {
			if ( $ti['action'] === 'test' ) {
				$test_plugins_count++;
			}
		}

		// We run shared steps if we have at least one plugin with bootstrap or test action.
		$run_shared_steps = $has_test_plugin || $has_bootstrap_plugin;

		// We do a DB export after shared setup if we have shared teardown or multiple test phases.
		$need_db_export = ( $has_shared_teardown || $test_plugins_count > 1 );

		$get_plugin_name = function ( string $slug ): string {
			return ucwords( str_replace( [ 'woocommerce-', '-' ], [ '', ' ' ], $slug ) );
		};

		$format_name = function ( string $name ) use ( &$project_counter ): string {
			if ( in_array( App::getVar( 'TEST_MODE' ), [ E2ETestManager::$test_modes['codegen'], E2ETestManager::$test_modes['ui'] ], true ) ) {
				return sprintf( '%02d - %s', $project_counter++, $name );
			}
			return $name;
		};

		$last_setup          = null;
		$last_operation      = null;
		$processed_test_plugins = 0;
		$first_test          = true;

		// Run shared setup steps if applicable.
		if ( $run_shared_steps ) {
			foreach ( $test_infos as $t ) {
				$plugin_slug = $t['slug'];
				$base_dir    = $t['path_in_php_container'];
				$host_path   = $t['path_in_host'];
				$plugin_name = $get_plugin_name( $plugin_slug );

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
							'browserName' => 'chromium',
							'qitTestSlug' => $plugin_slug,
						],
					];
					$last_setup = $name;
				}
			}
		}

		$last_operation = $last_setup;

		// If needed, do a DB export after shared setup.
		if ( $need_db_export && $last_operation ) {
			$name       = $format_name( '[db export]' );
			$projects[] = [
				'name'         => $name,
				'testDir'      => '/qit/tests/e2e/scripts',
				'testMatch'    => 'db-export.js',
				'retries'      => 0,
				'dependencies' => [ $last_operation ],
				'use'          => [ 'browserName' => 'chromium' ],
			];
			$last_operation = $name;
		}

		foreach ( $test_infos as $t ) {
			$action      = $t['action'];
			$plugin_slug = $t['slug'];
			$base_dir    = $t['path_in_php_container'];
			$host_path   = $t['path_in_host'];
			$plugin_name = $get_plugin_name( $plugin_slug );

			$current_setup = $last_operation ?: null;

			if ( $action === 'activate' ) {
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
				$current_setup  = $name;
				$last_operation = $current_setup;
				continue;
			}

			if ( $action === 'bootstrap' ) {
				// Bootstrap does nothing isolated here, just mark first_test as false after first one
				$last_operation = $current_setup;
				$first_test     = false;
				continue;
			}

			if ( $action === 'test' ) {
				// Run isolated setups
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

				// Test phase
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

				// Isolated teardowns
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
				$processed_test_plugins++;

				// After finishing a test plugin scenario, decide if we need a DB import.
				// We do this if $need_db_export is true AND
				// (more test plugins are coming OR shared teardown is present).
				$more_tests_coming = $processed_test_plugins < $test_plugins_count;

				if ( $need_db_export && ($more_tests_coming || $has_shared_teardown) ) {
					$name       = $format_name( '[db import]' );
					$projects[] = [
						'name'         => $name,
						'testDir'      => '/qit/tests/e2e/scripts',
						'testMatch'    => 'db-import.js',
						'dependencies' => [ $last_operation ],
						'retries'      => 0,
						'use'          => [ 'browserName' => 'chromium' ],
					];
					$last_operation = $name;
				}
			}
		}

		// Shared teardowns run in reverse order, but only if we ran shared steps.
		if ( $run_shared_steps ) {
			if ( $has_shared_teardown ) {
				// At this point, if shared teardowns exist, we've already done DB imports after test plugins as needed.
				// If no tests were run or no import occurred, we should ensure a baseline before shared teardowns:
				// Only if no test plugins triggered a DB import at the end do we need one here.
				// Actually, from logic above, if we had test plugins and shared teardown, we did the import after last test plugin scenario.
				// If we had no test plugins but shared teardown and multiple plugins scenario, we should have done an import after db export if needed.
				// If you find a scenario needing an import here, you can add it, but currently we've handled test scenarios.

				// If we had no tests at all, just bootstrap or activate with shared teardown,
				// then we have a DB export but never imported. Let's handle that:

				if ( $need_db_export && $last_operation && $test_plugins_count === 0 ) {
					// No tests were run. If we have shared teardown and multiple plugins (or reason to do DB export),
					// we likely need a baseline before teardown:
					$name       = $format_name( '[db import]' );
					$projects[] = [
						'name'         => $name,
						'testDir'      => '/qit/tests/e2e/scripts',
						'testMatch'    => 'db-import.js',
						'dependencies' => [ $last_operation ],
						'retries'      => 0,
						'use'          => [ 'browserName' => 'chromium' ],
					];
					$last_operation = $name;
				}
			}

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
