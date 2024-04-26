<?php

class RunE2ETest extends \PHPUnit\Framework\TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	public function test_runs_scaffolded_e2e() {
		$scaffolded_dir = sys_get_temp_dir() . '/qit_scaffolded_e2e-' . uniqid();
		qit( [ 'scaffold:e2e', $scaffolded_dir ] );

		$output = qit( [
				'run:e2e',
				'automatewoo',
				$scaffolded_dir,
			]
		);

		/*
		 * Warning: Key "skip_activating_plugins" not found in environment info.
		 * Downloading plugins and themes...
		 * Setting up Docker...
		 * First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.
		 * Setting up WordPress...
		 * Activating plugins...
		 * Plugin automatewoo/automatewoo.php failed to activate.
		 * Environment ready.
		 *
		 * Bootstrapping Plugins
		 * Bootstrapping automatewoo /qit/tests/e2e/automatewoo/local/bootstrap/bootstrap.php
		 * Bootstrapping automatewoo /qit/tests/e2e/automatewoo/local/bootstrap/bootstrap.sh
		 * Running E2E Tests
		 * Running 1 test using 1 worker
		 * [1/1] [automatewoo-local] › automatewoo/local/example.spec.js:8:5 › I can se
		 * 1 passed (5.7s)
		 * npm notice
		 * npm
		 * notice New minor version of npm available! 10.2.4 -> 10.6.0
		 * npm
		 * notice Changelog: <https://github.com/npm/cli/releases/tag/v10.6.0>
		 * npm notice Run `npm install -g npm@10.6.0` to update!
		 * npm notice
		 *
		 * To open last HTML report run: qit e2e-report
		 *
		 * Shutting down environment...
		 */

		// Normalize "1 passed (5.7s)" => "1 passed (TIME)"
		$output = preg_replace( '/1 passed \(\d+\.\ds\)/', '1 passed (TIME)', $output );

		// Normalize npm version, "10.2.4 -> 10.6.0" => "VERSION_1 -> VERSION_2"
		$output = preg_replace( '/New minor version of npm available! \d+\.\d+\.\d+ -> \d+\.\d+\.\d+/', 'New minor version of npm available! VERSION_1 -> VERSION_2', $output );

		// "https://github.com/npm/cli/releases/tag/v10.6.0" => "https://github.com/npm/cli/releases/tag/vNORMALIZED_VERSION"
		$output = preg_replace( '/https:\/\/github.com\/npm\/cli\/releases\/tag\/v\d+\.\d+\.\d+/', 'https://github.com/npm/cli/releases/tag/vNORMALIZED_VERSION', $output );

		// "npm install -g npm@10.6.0" => "npm install -g npm@VERSION_2"
		$output = preg_replace( '/npm install -g npm@\d+\.\d+\.\d+/', 'npm install -g npm@VERSION_2', $output );

		$this->assertMatchesSnapshot( $output );
	}
}