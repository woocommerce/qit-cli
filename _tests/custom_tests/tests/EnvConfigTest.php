<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

/**
 * Final EnvConfigTest aligning with your parser rules:
 * - If key is recognized, source is optional.
 * - If key is unknown, user must define source.
 * - If slug is set, it must match the key.
 * - No numeric short syntax or nonexistent flags tested.
 */
class EnvConfigTest extends TestCase {
	use SnapshotHelpers;

	/**
	 * Writes the config to a temp file, runs `qit env:up --json`, and returns the final $env_info.
	 */
	private function run_qit_config_test( string $config_contents, string $extension = 'json', array $cli_extra = [] ): array {
		// 1) Temp file
		$config_file = sprintf(
			'%s/qit_%s_%s.%s',
			sys_get_temp_dir(),
			uniqid(),
			$extension,
			$extension
		);
		file_put_contents( $config_file, $config_contents );

		// 2) Build CLI command
		$cli_command = array_merge( [
			'env:up',
			'--config',
			$config_file,
			'--json',
		], $cli_extra );

		// 3) Run qit, expecting exit code 137, with QIT_SELF_TEST=env_info
		$output = qit( $cli_command, [], 137, [ 'QIT_SELF_TEST' => 'env_info' ] );

		unlink( $config_file );

		// 4) Decode & normalize
		$env_info = json_decode( $output, true );

		return $this->normalize_env_info( $env_info );
	}

	/**
	 * Normalizes $env_info so snapshots don’t break from random IDs or paths.
	 */
	private function normalize_env_info( array $env_info ): array {
		// Possibly remove environment-specific IDs, timestamps, etc.
		$original_env_id = $env_info['env_id'] ?? null;
		if ( $original_env_id ) {
			$env_info['env_id'] = 'ENV_ID_NORMALIZED';
		}

		if ( isset( $env_info['created_at'] ) ) {
			$env_info['created_at'] = 1700000000;
		}
		if ( isset( $env_info['domain'] ) ) {
			$env_info['domain'] = 'normalized.localhost';
		}

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

		$normalize_recursive = function ( $data ) use ( $original_env_id, &$normalize_recursive ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $key => $value ) {
					$data[ $key ] = $normalize_recursive( $value );
				}

				return $data;
			}

			if ( is_string( $data ) ) {
				if ( ! empty( $original_env_id ) ) {
					$data = str_replace( $original_env_id, 'ENV_ID_NORMALIZED', $data );
				}

				// Replace any long hex substring
				$data = preg_replace( '/[a-f0-9]{10,}/i', 'ENV_ID_NORMALIZED', $data );
				// Remove everything up to qit-cli/_tests
				$data = preg_replace( '#.*?(qit-cli/_tests.*)#', '$1', $data );

				$real_temp_dir = realpath( sys_get_temp_dir() );
				if ( $real_temp_dir ) {
					$data = str_replace( $real_temp_dir, '/tmp', $data );
					$data = str_replace( rtrim( $real_temp_dir, '/' ) . '/', '/tmp/', $data );
				}
			}

			return $data;
		};

		return $normalize_recursive( $env_info );
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
		$env_info    = $this->run_qit_config_test( $json_config );
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
		$env_info    = $this->run_qit_config_test( $json_config );
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
		$env_info    = $this->run_qit_config_test( $json_config );
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
		$env_info    = $this->run_qit_config_test( $json_config );
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

		$cli_extra = [
			'--wp',
			'6.4',
			'--plugin',
			'wordpress-importer'
		];
		$env_info  = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_local_paths(): void {
		$dummy_plugin_zip = sys_get_temp_dir() . '/fake-plugin-' . uniqid() . '.zip';
		file_put_contents( $dummy_plugin_zip, 'fake plugin contents' );

		$config_array = [
			'plugins' => [ $dummy_plugin_zip ],
			'themes'  => [ '/absolute/path/to/mytheme.zip' ]
		];
		$json_config  = json_encode( $config_array, JSON_PRETTY_PRINT );

		$env_info = $this->run_qit_config_test( $json_config );
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

		$cli_extra = [
			'--env_file',
			$env_file_path,
			'--env',
			'DB_NAME=wp_test'
		];
		$env_info  = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		unlink( $env_file_path );

		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_brackets_array(): void {
		$json_config = <<<'JSON'
{
  "wp": "stable",
  "php_version": "7.4",
  "plugins": [
    "woocommerce",
    "wordpress-importer"
  ],
  "themes": [
    "https://downloads.wordpress.org/theme/storefront.zip",
    "https://downloads.wordpress.org/theme/twentytwentyfour.zip"
  ]
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_invalid_json(): void {
		$this->expectException( \RuntimeException::class );
		$json_config = <<<'JSON'
{
  "wp": "stable",
  "php_version": "7.4",
  "plugins": {
    "woocommerce",
    "wordpress-importer"
  },
  "themes": {
    "https://downloads.wordpress.org/theme/storefront.zip",
    "https://downloads.wordpress.org/theme/twentytwentyfour.zip"
  }
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_partial_associative(): void {
		$json_config = <<<'JSON'
{
  "wp": "stable",
  "php_version": "7.4",
  "plugins": [
    "woocommerce",
    "wordpress-importer"
  ],
  "themes": {
    "https://downloads.wordpress.org/theme/storefront.zip": {
	  "action": "activate"
	},
    "https://downloads.wordpress.org/theme/twentytwentyfour.zip": {
      "source": "https://downloads.wordpress.org/theme/twentytwentyfour.zip"
    }
  }
}
JSON;
		$env_info    = $this->run_qit_config_test( $json_config );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_partial_associative_mixed_sources(): void {
		// Fix: Provide "source" for local/remote keys, so parser doesn't fail
		$json_config = <<<'JSON'
{
  "wp": "stable",
  "php_version": "7.4",
  "plugins": {
    "woocommerce": {},
    "/path/to/local/plugin-directory": {
      "source": "/path/to/local/plugin-directory",
      "action": "activate"
    },
    "/path/to/local/plugin.zip": {
      "source": "/path/to/local/plugin.zip"
    },
    "https://example.com/some-remote-plugin.zip": {
      "source": "https://example.com/some-remote-plugin.zip",
      "test_tags": ["e2e", "my-custom-tag"]
    }
  },
  "themes": {
    "https://downloads.wordpress.org/theme/storefront.zip": {
      "source": "https://downloads.wordpress.org/theme/storefront.zip",
      "action": "activate"
    },
    "https://downloads.wordpress.org/theme/twentytwentyfour.zip": {
      "source": "https://downloads.wordpress.org/theme/twentytwentyfour.zip"
    },
    "/path/to/local/theme-directory": {
      "source": "/path/to/local/theme-directory"
    },
    "/path/to/local/theme.zip": {
      "source": "/path/to/local/theme.zip",
      "test_tags": ["special-theme-test"]
    }
  }
}
JSON;

		$env_info = $this->run_qit_config_test( $json_config );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_cli_plugin_override(): void {
		$json_config = <<<'JSON'
{
  "plugins": [
    "woocommerce",
    {
      "slug": "my-plugin",
      "source": "./my-plugin.zip"
    }
  ],
  "themes": [
    "storefront"
  ]
}
JSON;

		$cli_extra = [
			'--plugin',
			'woocommerce:activate:newTestTag',
			'--plugin',
			'contact-form-7'
		];
		$env_info  = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_wp_and_woo_version_cli_override(): void {
		$json_config = <<<'JSON'
{
  "wp": "6.3",
  "woo": "8.5.1",
  "plugins": ["woocommerce"]
}
JSON;

		$cli_extra = [
			'--wp',
			'6.4',
			'--woo',
			'nightly'
		];
		$env_info  = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_multiple_cli_plugins_with_config_base(): void {
		$json_config = <<<'JSON'
{
  "plugins": ["woocommerce", "my-plugin"],
  "themes": ["storefront"]
}
JSON;

		$cli_extra = [
			'--plugin',
			'contact-form-7',
			'--plugin',
			'./my-second-plugin.zip',
			'--plugin',
			'woocommerce:install:e2e-tag'
		];

		$env_info = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_cli_short_syntax_edge_cases(): void {
		$json_config = <<<'JSON'
{
  "plugins": ["woocommerce"]
}
JSON;

		$cli_extra = [
			'--plugin',
			'my-plugin:test',
			'--plugin',
			'another-plugin:activate:',
			'--plugin',
			'final-plugin:activate:foo,bar'
		];

		$env_info = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_mixed_assoc_with_cli_override_path(): void {
		$json_config = <<<'JSON'
{
  "plugins": {
    "woocommerce": {
      "action": "bootstrap",
      "source": "woocommerce"
    },
    "/path/to/local/plugin-dir": {
      "source": "/path/to/local/plugin-dir"
    }
  },
  "themes": ["storefront"]
}
JSON;

		$cli_extra = [
			'--plugin',
			'/path/to/local/plugin-dir:test:anotherTag'
		];
		$env_info  = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_config_and_cli_same_slug_override(): void {
		$json_config = <<<'JSON'
{
  "plugins": [
    "woocommerce",
    "woocommerce:test:myInitialTag"
  ]
}
JSON;

		// Then CLI also references woo with a different action/test_tags:
		$cli_extra = [
			'--plugin',
			'woocommerce:activate:base64bmV3VGVzdFRhZw=='  // => test_tags=["newTestTag"]
		];

		$env_info = $this->run_qit_config_test( $json_config, 'json', $cli_extra );
		$this->assertMatchesSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

}