<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\Environment\Extension;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Handles any scripts named shared-setup.* or shared-teardown.*
 * for items whose action=bootstrap or action=test.
 */
class SharedSetupRunner {

	/**
	 * @var QITE2EConfig
	 */
	protected $qit_e2e_config;

	/**
	 * Constructor
	 *
	 * @param ExtensionTestRunner $extension_test_runner
	 */
	public function __construct( QITE2EConfig $qit_e2e_config ) {
		$this->qit_e2e_config = $qit_e2e_config;
	}

	/**
	 * Runs any shared-setup.* (sh|php|js) scripts.
	 *
	 * @param mixed        $env_info
	 * @param SymfonyStyle $io
	 */
	public function run_shared_setup( $env_info, SymfonyStyle $io, ExtensionTestRunner $extension_test_runner ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			// Load the plugin's config from its test directory
			$plugin_dir = $test_item['path_in_host'];
			$config     = $this->qit_e2e_config->load_config( $plugin_dir );

			// Now get $config['sharedSetup']
			foreach ( $config['sharedSetup'] as $script ) {
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

	/**
	 * Runs any shared-teardown.* (sh|php|js) scripts.
	 *
	 * @param mixed        $env_info
	 * @param SymfonyStyle $io
	 */
	public function run_shared_teardown( $env_info, SymfonyStyle $io, ExtensionTestRunner $extension_test_runner ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			// Load the plugin's config from its test directory
			$plugin_dir = $test_item['path_in_host'];
			$config     = $this->qit_e2e_config->load_config( $plugin_dir );

			foreach ( $config['sharedTeardown'] as $script ) {
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
