<?php

namespace QIT\IntegrationTests\Traits;

use QIT\IntegrationTests\Utils\Normalizer;

trait ScaffoldHelpers {
	/**
	 * @param string|null $spec_name This can be used to differentiate between different scaffolded tests.
	 * @param string|null $namespace The namespace (extension slug) for the test package.
	 * @param string|null $package The package slug for the test package.
	 *
	 * @return string The path to the scaffolded directory.
	 */
	protected function scaffold_test( ?string $spec_name = null, ?string $namespace = 'woocommerce', ?string $package = 'internal-integration-test' ): string {
		$scaffolded_dir = sys_get_temp_dir() . '/qit_scaffolded_e2e-' . uniqid();
		
		$command = [ 'scaffold:e2e', $scaffolded_dir ];
		
		if ( $namespace && $package ) {
			$command[] = '--namespace=' . $namespace;
			$command[] = '--package=' . $package;
		}
		
		qit( $command );

		if ( $spec_name ) {
			if ( ! rename( $scaffolded_dir . '/example.spec.js', $scaffolded_dir . "/$spec_name.spec.js" ) ) {
				throw new \RuntimeException( 'Failed to rename the scaffolded test file.' );
			}
		}

		return $scaffolded_dir;
	}

	/**
	 * There is no "scaffold:plugin" command, so we mock one in the given path.
	 *
	 * @param string $plugin_path The path of the plugin to scaffold.
	 */
	protected function scaffold_plugin( string $plugin_path ): void {
		if ( ! file_exists( dirname( $plugin_path ) ) ) {
			throw new \RuntimeException( 'The parent directory of the plugin path does not exist. Expected to exist: ' . dirname( $plugin_path ) );
		}

		$plugin_name      = basename( $plugin_path );
		$plugin_main_file = sprintf( '%s/%s.php', $plugin_path, basename( $plugin_path ) );

		if ( file_exists( $plugin_path ) ) {
			unlink( $plugin_main_file );
			rmdir( $plugin_path );
		}

		if ( ! mkdir( $plugin_path, 0755, true ) ) {
			throw new \RuntimeException( 'Failed to create the plugin directory at ' . $plugin_path );
		}

		$plugin_contents = <<<PHP
<?php
/*
 * Plugin Name: $plugin_name
 */
PHP;

		if ( ! file_put_contents( $plugin_main_file, $plugin_contents ) ) {
			throw new \RuntimeException( 'Failed to create the plugin main file at ' . $plugin_main_file );
		}

		register_shutdown_function( function () use ( $plugin_path, $plugin_main_file ) {
			if ( file_exists( $plugin_main_file ) ) {
				unlink( $plugin_main_file );
			}
			if ( file_exists( $plugin_path ) ) {
				rmdir( $plugin_path );
			}
		} );
	}

	/**
	 * Accept either the whole Pre‑Command result *or* just the env_info
	 * subtree and normalise all volatile fields.
	 *
	 * @param array<string,mixed> $payload
	 */
	protected function normalize_env_info( array $payload ): array {
		// Delegates to the shared helper and returns immediately
		return Normalizer::precommand( $payload );
	}

	protected function normalize_snapshot_diff( string $output, int $expected_diff ): string {
		$pattern = '/([+-]?\d+) pixels \(ratio [0-9.]+ of all image pixels\) are different\./';

		return preg_replace_callback(
			$pattern,
			function ( $matches ) use ( $expected_diff ) {
				$current_diff = (int) $matches[1];

				if ( abs( $current_diff - $expected_diff ) <= 5 ) {
					return "(SMALL_DIFF close to {$expected_diff})";
				}

				return $matches[0] . ' (HIGH_DIFF)';
			},
			$output
		);
	}

	protected function normalize_scaffolded_test_run_output( string $output ): string {
		/*
		 * This is an example output that we are normalizing. We will remove anything that is completely random,
		 * like timings and NPM upgrade notices, while keeping everything else so that we can snapshot test it.
		 * Lines that start with "***" will be normalized.
		 *
		 * Warning: Key "skip_activating_plugins" not found in environment info.
		 * Processing plugins and themes...
		 * Setting up Docker...
		 * First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.
		 * Setting up WordPress...
		 * Activating plugins...
		 * Plugin woocommerce-amazon-s3-storage/woocommerce-amazon-s3-storage.php failed to activate.
		 * Environment ready.
		 *
		 * Bootstrapping Plugins
		 * Bootstrapping woocommerce-amazon-s3-storage /qit/tests/e2e/woocommerce-amazon-s3-storage/local/bootstrap/bootstrap.php
		 * Bootstrapping woocommerce-amazon-s3-storage /qit/tests/e2e/woocommerce-amazon-s3-storage/local/bootstrap/bootstrap.sh
		 * Running E2E Tests
		 * Running 1 test using 1 worker
		 * [1/1] [woocommerce-amazon-s3-storage-local] › woocommerce-amazon-s3-storage/local/example.spec.js:8:5 › I can se
		 *** 1 passed (5.7s)
		 * npm notice
		 * npm
		 *** notice New minor version of npm available! 10.2.4 -> 10.6.0
		 * npm
		 *** notice Changelog: <https://github.com/npm/cli/releases/tag/v10.6.0>
		 *** npm notice Run `npm install -g npm@10.6.0` to update!
		 * npm notice
		 *
		 * To open last HTML report run: qit report
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