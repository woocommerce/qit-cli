<?php

namespace QIT\SelfTests\CustomTests\Traits;

trait ScaffoldHelpers {
	/**
	 * @param string|null $spec_name This can be used to differentiate between different scaffolded tests.
	 *
	 * @return string The path to the scaffolded directory.
	 */
	protected function scaffold_test( ?string $spec_name = null ): string {
		$scaffolded_dir = sys_get_temp_dir() . '/qit_scaffolded_e2e-' . uniqid();
		qit( [ 'scaffold:e2e', $scaffolded_dir ] );

		if ( $spec_name ) {
			if ( ! rename( $scaffolded_dir . '/example.spec.js', $scaffolded_dir . "/$spec_name.spec.js" ) ) {
				throw new \RuntimeException( 'Failed to rename the scaffolded test file.' );
			}
		}

		return $scaffolded_dir;
	}

	protected function normalize_scaffolded_test_run_output( string $output ): string {
		/*
		 * This is an example output that we are normalizing. We will remove anything that is completely random,
		 * like timings and NPM upgrade notices, while keeping everything else so that we can snapshot test it.
		 * Lines that start with "***" will be normalized.
		 *
		 * Warning: Key "skip_activating_plugins" not found in environment info.
		 * Downloading plugins and themes...
		 * Setting up Docker...
		 * First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.
		 * Setting up WordPress...
		 * Activating plugins...
		 * Plugin qit-test-plugin/qit-test-plugin.php failed to activate.
		 * Environment ready.
		 *
		 * Bootstrapping Plugins
		 * Bootstrapping qit-test-plugin /qit/tests/e2e/qit-test-plugin/local/bootstrap/bootstrap.php
		 * Bootstrapping qit-test-plugin /qit/tests/e2e/qit-test-plugin/local/bootstrap/bootstrap.sh
		 * Running E2E Tests
		 * Running 1 test using 1 worker
		 * [1/1] [qit-test-plugin-local] › qit-test-plugin/local/example.spec.js:8:5 › I can se
		 *** 1 passed (5.7s)
		 * npm notice
		 * npm
		 *** notice New minor version of npm available! 10.2.4 -> 10.6.0
		 * npm
		 *** notice Changelog: <https://github.com/npm/cli/releases/tag/v10.6.0>
		 *** npm notice Run `npm install -g npm@10.6.0` to update!
		 * npm notice
		 *
		 * To open last HTML report run: qit e2e-report
		 *
		 * Shutting down environment...
		 */

		// Normalize "1 passed (5.7s)" => "passed (TIME)"
		// Normalize "1 passed (1.0m)" => "passed (TIME)"
		$output = preg_replace( '/passed \(\d+\.\d+[sm]\)/', 'passed (TIME)', $output );

		// Normalize npm version, "10.2.4 -> 10.6.0" => "VERSION_1 -> VERSION_2"
		$output = preg_replace( '/New minor version of npm available! \d+\.\d+\.\d+ -> \d+\.\d+\.\d+/', 'New minor version of npm available! VERSION_1 -> VERSION_2', $output );

		// "https://github.com/npm/cli/releases/tag/v10.6.0" => "https://github.com/npm/cli/releases/tag/vNORMALIZED_VERSION"
		$output = preg_replace( '/https:\/\/github.com\/npm\/cli\/releases\/tag\/v\d+\.\d+\.\d+/', 'https://github.com/npm/cli/releases/tag/vNORMALIZED_VERSION', $output );

		// "npm install -g npm@10.6.0" => "npm install -g npm@VERSION_2"
		$output = preg_replace( '/npm install -g npm@\d+\.\d+\.\d+/', 'npm install -g npm@VERSION_2', $output );

		return $output;
	}
}