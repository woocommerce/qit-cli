<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class EnvConfigTest extends TestCase {
	use SnapshotHelpers;

	/**
	 * A helper that:
	 * 1) Writes the config content to a temp file (JSON or YML),
	 * 2) Runs "qit env:up --json" with QIT_SELF_TEST=env_info,
	 * 3) Returns the decoded & normalized $env_info array for snapshot comparison.
	 *
	 * Usage:
	 *   $env_info = $this->run_qit_config_test($config_contents, 'json');
	 *   $this->assertMatchesSnapshot(json_encode($env_info, JSON_PRETTY_PRINT));
	 */
	private function run_qit_config_test( string $config_contents, string $extension = 'json', array $cli_extra = [] ): array {
		// 1) Write the temp file
		$config_file = sprintf(
			'%s/qit_%s_%s.%s',
			sys_get_temp_dir(),
			uniqid(),
			$extension,
			$extension
		);
		file_put_contents( $config_file, $config_contents );

		// 2) Build the CLI command
		//    By default: ['env:up', '--config', $config_file, '--json']
		//    Then we append anything from $cli_extra, e.g. ['--wp','6.4','--plugin','another']
		$cli_command = array_merge( [
			'env:up',
			'--config',
			$config_file,
			'--json',
		], $cli_extra );

		// 3) Run qit, expecting exit code 137, with QIT_SELF_TEST=env_info
		$output = qit( $cli_command, [], 137, [ 'QIT_SELF_TEST' => 'env_info' ] );

		// Cleanup
		unlink( $config_file );

		// 4) Decode & normalize
		$env_info = json_decode( $output, true );

		return $this->normalize_env_info( $env_info );
	}

	private function normalize_env_info( array $env_info ): array {
		// 1) Capture the original env_id (e.g. "67d4f06a4b7ac") so we can find & replace it in paths
		$original_env_id = $env_info['env_id'] ?? null;
		if ( $original_env_id ) {
			$env_info['env_id'] = 'ENV_ID_NORMALIZED';
		}

		// 2) Overwrite date, domain, etc.
		if ( isset( $env_info['created_at'] ) ) {
			$env_info['created_at'] = 1700000000;
		}
		if ( isset( $env_info['domain'] ) ) {
			$env_info['domain'] = 'normalized.localhost';
		}

		// 3) Overwrite plugin info
		if ( ! empty( $env_info['plugins'] ) && is_array( $env_info['plugins'] ) ) {
			foreach ( $env_info['plugins'] as &$plugin ) {
				if ( isset( $plugin['version'] ) ) {
					$plugin['version'] = 'NORMALIZED_VERSION';
				}
				if ( isset( $plugin['downloaded_source'] ) ) {
					$plugin['downloaded_source'] = '/normalized/path.zip';
				}
			}
		}

		// 4) Recursively transform the entire array:
		//    - Replace the ORIGINAL env_id with ENV_ID_NORMALIZED in strings
		//    - Replace any long hex substring (tempnam-style) with ENV_ID_NORMALIZED
		//    - Remove everything up to qit-cli/_tests
		$normalize_recursive = function ( $data ) use ( $original_env_id, &$normalize_recursive ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data[ $key ] = $normalize_recursive( $value );
				}

				return $data;
			}

			if ( is_string( $data ) ) {
				// (A) Replace the original env_id if we have one
				if ( ! empty( $original_env_id ) ) {
					$data = str_replace( $original_env_id, 'ENV_ID_NORMALIZED', $data );
				}

				// (B) Replace any long hex substring (e.g. "67d4f193be95d")
				$data = preg_replace( '/[a-f0-9]{10,}/i', 'ENV_ID_NORMALIZED', $data );

				// (C) Remove everything leading up to "qit-cli/_tests" so it starts at "qit-cli/_tests/..."
				//     If you have JSON-escaped paths, use qit-cli\/_tests instead
				$data = preg_replace( '#.*?(qit-cli/_tests.*)#', '$1', $data );
			}

			return $data;
		};

		// Perform the final recursive pass
		$env_info = $normalize_recursive( $env_info );

		return $env_info;
	}


	public function test_json_config_array_of_strings(): void {
		$json_config = <<<'JSON'
{
  "plugins": [
    "woocommerce",
    "wordpress-importer"
  ],
  "themes": [
    "https://downloads.wordpress.org/theme/storefront.zip",
    "twentytwentyone"
  ]
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config, 'json' );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_yml_config_array_of_strings(): void {
		$yml_config = <<<YML
plugins:
  - woocommerce
  - wordpress-importer

themes:
  - https://downloads.wordpress.org/theme/storefront.zip
  - twentytwentyone
YML;
		$env_info   = $this->run_qit_config_test( $yml_config, 'yml' );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_json_config_associative_plugins(): void {
		$json_config = <<<'JSON'
{
  "plugins": {
    "woocommerce": { "action": "activate" },
    "wordpress-importer": { "action": "activate" }
  },
  "themes": ["twentytwentyone"]
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config, 'json' );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_json_config_with_wp_and_woo_versions(): void {
		$json_config = <<<'JSON'
{
  "wp": "6.5",
  "woo": "8.5.1",
  "plugins": ["wordpress-importer"]
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config, 'json' );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_json_config_skip_plugin_and_theme_activation(): void {
		$json_config = <<<'JSON'
{
  "skip_activating_plugins": true,
  "skip_activating_themes": true,
  "plugins": ["woocommerce"],
  "themes": ["storefront"]
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config, 'json' );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_and_cli_merge(): void {
		$json_config = <<<'JSON'
{
  "wp": "6.3",
  "php_version": "8.0",
  "plugins": [ "woocommerce" ]
}
JSON;

		// Provide additional CLI flags: --wp 6.4, --plugin=wordpress-importer
		$env_info = $this->run_qit_config_test( $json_config, 'json', [
			'--wp',
			'6.4',
			'--plugin',
			'wordpress-importer'
		] );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_local_paths(): void {
		$dummy_plugin_zip = sys_get_temp_dir() . '/fake-plugin-' . uniqid() . '.zip';
		file_put_contents( $dummy_plugin_zip, 'fake plugin contents' );

		$config_array = [
			'plugins' => [
				$dummy_plugin_zip,
			],
			'themes'  => [
				'/absolute/path/to/mytheme.zip'
			]
		];

		$json_config = json_encode( $config_array, JSON_PRETTY_PRINT );

		$env_info = $this->run_qit_config_test( $json_config, 'json' );
		unlink( $dummy_plugin_zip );

		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_env_vars_from_cli_and_file(): void {
		$env_file_path = sys_get_temp_dir() . '/qit_test_env_' . uniqid() . '.env';
		file_put_contents( $env_file_path, "HELLO=world\nFOO=from_env_file" );

		$json_config = <<<'JSON'
{
  "plugins": ["woocommerce"],
  "themes": ["storefront"]
}
JSON;

		$env_info = $this->run_qit_config_test( $json_config, 'json', [
			'--env_file',
			$env_file_path,
			'--env',
			'DB_NAME=wp_test'
		] );
		unlink( $env_file_path );

		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}