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
	 * @var ExtensionTestRunner
	 */
	protected $extension_test_runner;

	/**
	 * Constructor
	 *
	 * @param ExtensionTestRunner $extension_test_runner
	 */
	public function __construct( ExtensionTestRunner $extension_test_runner ) {
		$this->extension_test_runner = $extension_test_runner;
	}

	/**
	 * Runs any shared-setup.* (sh|php|js) scripts.
	 *
	 * @param mixed $env_info
	 * @param SymfonyStyle $io
	 */
	public function run_shared_setup( $env_info, SymfonyStyle $io ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
			     && $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			// Delegates to the ExtensionTestRunner:
			$this->extension_test_runner->run_script_if_exists(
				$env_info,
				$test_item,
				'shared-setup.sh',
				'Shared Setup',
				$io
			);
		}
	}

	/**
	 * Runs any shared-teardown.* (sh|php|js) scripts.
	 *
	 * @param mixed $env_info
	 * @param SymfonyStyle $io
	 */
	public function run_shared_teardown( $env_info, SymfonyStyle $io ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
			     && $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			// Delegates to the ExtensionTestRunner:
			$this->extension_test_runner->run_script_if_exists(
				$env_info,
				$test_item,
				'shared-teardown.sh',
				'Shared Teardown',
				$io
			);
		}
	}
}