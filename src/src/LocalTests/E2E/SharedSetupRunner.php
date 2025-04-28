<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\Environment\Extension;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs lifecycle.sharedSetup / lifecycle.sharedTeardown for each plugin that has action=bootstrap or action=test.
 */
class SharedSetupRunner {
	public function run_shared_setup( $env_info, SymfonyStyle $io, ExtensionTestRunner $extension_test_runner ): void {
		foreach ( $env_info->tests as $test_item ) {
			// We only run shared setup for items with action=bootstrap or action=test
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if (
				$test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			$plugin_dir = $test_item['path_in_host'] ?? '';
			$config     = $test_item['config'] ?? [];

			if ( ! empty( $config['lifecycle']['sharedSetup'] ) && is_array( $config['lifecycle']['sharedSetup'] ) ) {
				foreach ( $config['lifecycle']['sharedSetup'] as $script ) {
					$extension_test_runner->run_script_if_exists(
						$env_info,
						$test_item,
						rtrim( $plugin_dir, '/' ) . '/' . $script,
						'Shared Setup',
						$io
					);
				}
			}
		}
	}

	public function run_shared_teardown( $env_info, SymfonyStyle $io, ExtensionTestRunner $extension_test_runner ): void {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if (
				$test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			$plugin_dir = $test_item['path_in_host'] ?? '';
			$config     = $test_item['config'] ?? [];

			if ( ! empty( $config['lifecycle']['sharedTeardown'] ) && is_array( $config['lifecycle']['sharedTeardown'] ) ) {
				foreach ( $config['lifecycle']['sharedTeardown'] as $script ) {
					$extension_test_runner->run_script_if_exists(
						$env_info,
						$test_item,
						rtrim( $plugin_dir, '/' ) . '/' . $script,
						'Shared Teardown',
						$io
					);
				}
			}
		}
	}
}