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

	protected function normalize_env_info( array $env_info ): array {
		$id = $env_info['env_id'];

		// Decode, str_replace, encode
		$env_info = json_encode( $env_info, JSON_UNESCAPED_SLASHES );

		$d = __DIR__;

		while ( true ) {
			$d = dirname( $d );
			if ( basename( $d ) === 'custom_tests' ) {
				$dir_to_replace = realpath( $d );
				break;
			}

			if ( $d === '/' ) {
				throw new \RuntimeException( 'Could not find the "custom_tests" directory.' );
			}
		}

		$env_info = str_replace( $id, 'ENV_ID_NORMALIZED', $env_info );
		$env_info = str_replace( $dir_to_replace, '/path/normalized/', $env_info );
		$env_info = str_replace( sys_get_temp_dir(), '/tmp-normalized', $env_info );
		$env_info = str_replace( '/tmp/', '/tmp-normalized/', $env_info );
		$env_info = preg_replace( '/qit_scaffolded_e2e-[a-f0-9]+/', 'qit_scaffolded_e2e-NORMALIZED_ID', $env_info );
		$env_info = preg_replace( '/qit_config-qit_custom_tests_[a-f0-9]+/', 'qit_config-qit_custom_tests_NORMALIZED_ID', $env_info );

		$env_info = json_decode( $env_info, true );

		$env_info['created_at'] = '1700000000';
		$env_info['sut_id']     = '123';

		foreach ( $env_info['plugins'] as &$p ) {
			if ( strpos( $p['source'], 'http' ) !== false ) {
				$filename    = explode( '/', parse_url( $p['source'], PHP_URL_PATH ) );
				$filename    = end( $filename );
				$p['source'] = 'https://normalized-remote-source/' . $filename;
			}
			if ( ! empty( $p['version'] ) ) {
				$p['version'] = 'NORMALIZED_VERSION';
			}
			if ( ! empty( $p['downloaded_source'] ) ) {
				$p['downloaded_source'] = '/normalized/downloaded-path/file.zip';
			}
		}

		return $env_info;
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