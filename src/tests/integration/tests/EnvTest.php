<?php

use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class EnvTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;

	protected function tearDown(): void {
		qit( [ 'env:down' ] );
		parent::tearDown();
	}

	public function test_env_up() {
		$output = qit( [ 'env:up' ], <<<'JSON'
{
  "sut": {
    "type": "plugin",
    "slug": "test-plugin",
    "source": { "type": "local", "path": "./" }
  },
  "environments": {
    "default": {
      "php_version": "8.2",
      "wp_version": "stable"
    }
  }
}
JSON
		);

		// Extract the dynamic environment ID
		preg_match( '/Temporary test environment created. \((\w+)\)/', $output, $matches );
		$envId = $matches[1];

		// Extract the dynamic port number
		preg_match( '/localhost:(\d+)/', $output, $matches );
		$port = $matches[1];

		// Replace all instances of the environment ID and port number in the output
		$normalizedOutput = str_replace( $envId, 'ENV_ID', $output );
		$normalizedOutput = str_replace( $port, 'PORT', $normalizedOutput );
		$normalizedOutput = str_replace( $GLOBALS['QIT_HOME'], 'QIT_HOME', $normalizedOutput );

		// "WordPress Version: 6.5.2" => "WordPress Version: 6.5.2-normalized"
		$normalizedOutput = preg_replace( '/WordPress Version: .+/', 'WordPress Version: NORMALIZED', $normalizedOutput );

		$this->assertMatchesNormalizedSnapshot( $normalizedOutput );
	}

	public function test_env_up_with_parameters() {
		$output = qit( [
				'env:up',
				'--wp_version',
				'6.5',
				'--php_version',
				'8.3',
			]
		);

		// Check that WordPress Version is as expected:
		$this->assertStringContainsString( 'WordPress Version: 6.5', $output );

		// Check that PHP Version is as expected:
		$this->assertStringContainsString( 'PHP Version: 8.3', $output );
	}

	public function test_env_up_with_object_cache() {
		$output = qit( [
				'env:up',
				'--object_cache',
			]
		);

		$this->assertStringContainsString( 'Redis Object Cache? Yes', $output );
	}

	public function test_env_up_with_file() {
		$output = qit( [ 'env:up' ], <<<'JSON'
{
  "environments": {
    "default": {
      "wp_version": "6.4",
      "php_version": "8.2"
    }
  }
}
JSON
		);

		// Check that WordPress Version is as expected:
		$this->assertStringContainsString( 'WordPress Version: 6.4', $output );

		// Check that PHP Version is as expected:
		$this->assertStringContainsString( 'PHP Version: 8.2', $output );
	}

	public function test_env_up_with_file_and_parameters() {
		$output = qit( [ 'env:up' ], <<<'JSON'
{
  "environments": {
    "default": {
      "wp_version": "6.4",
      "php_version": "8.3"
    }
  }
}
JSON
		);

		// Check that WordPress Version is as expected:
		$this->assertStringContainsString( 'WordPress Version: 6.4', $output );

		// Check that PHP Version is as expected:
		$this->assertStringContainsString( 'PHP Version: 8.3', $output );
	}

	public function test_env_up_with_plugins() {
		// 1. A heredoc that already conforms to the schema the CLI expects
		//    – single default environment, plugins as an ARRAY of objects.
		$qitJson = <<<'JSON'
{
  "environments": {
    "default": {
      "plugins": [
        {
          "slug": "woocommerce-amazon-s3-storage",
          "from": "wporg"
        },
        {
          "slug": "woocommerce",
          "from": "wporg"
        }
      ]
    }
  }
}
JSON;

		// 2. Bring the environment up and capture the JSON response.
		$envInfo = json_decode(
			qit( [ 'env:up', '--json' ], $qitJson ),   // ← second argument is now the heredoc string
			true
		);

		// 3. Run `wp plugin list` inside that environment.
		$rawList = qit( [
			'env:exec',
			'--env_id',
			$envInfo['env_id'],
			'wp plugin list --fields=name,status'
		] );

		// 4. Canonicalise the listing so snapshots are stable.
		$lines  = array_filter( explode( "\n", $rawList ) );  // drop empties
		$header = array_shift( $lines );                    // keep header separate
		sort( $lines );                                     // alphabetical order
		$output = $header . "\n" . implode( "\n", $lines );

		$this->assertMatchesNormalizedSnapshot( $output );
	}


	public function test_env_up_with_additional_volumes() {
		if ( file_exists( sys_get_temp_dir() . '/qit-tmp-plugin.php' ) && ! unlink( sys_get_temp_dir() . '/qit-tmp-plugin.php' ) ) {
			throw new \RuntimeException( 'Could not delete the temporary file.' );
		}

		file_put_contents( sys_get_temp_dir() . '/qit-tmp-plugin.php',
			<<<'PHP'
<?php
/**
 * Plugin Name: QIT Temporary Plugin
 * Description: A temporary plugin for testing.
 * Version: 1.0
 */
PHP
		);

		$json = json_decode( qit( [
				'env:up',
				'--json',
				'--volume',
				sprintf( sys_get_temp_dir() . '/qit-tmp-plugin.php' . ':/var/www/html/wp-content/plugins/qit-tmp-plugin.php' ),
			]
		), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin get qit-tmp-plugin',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_wordpress_stable_version() {
		$json = json_decode( qit( [ 'env:up', '--json', '--wp_version', 'stable' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp core check-update --force-check',
		] );

		$this->assertStringContainsString( 'WordPress is at the latest version', $output );
	}

	public function test_env_up_wordpress_nightly_version() {
		$json = json_decode( qit( [ 'env:up', '--json', '--wp_version', 'nightly' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp core version',
		] );

		$this->assertMatchesRegularExpression(
			'/^\d+\.\d+-(alpha|beta|RC\d?)-\d+/',
			trim($output)
		);
	}

	public function test_env_up_woocommerce_stable_version() {
		$json = json_decode( qit( [ 'env:up', '--json', '--woo_version', 'stable', '--plugin', 'woocommerce' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin update woocommerce',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_woocommerce_stable_version_alternative_syntax() {
		$json = json_decode( qit( [ 'env:up', '--json', '--plugin', 'woocommerce', ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin update woocommerce',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_woocommerce_nightly_version() {
		$json = json_decode( qit( [
			'env:up',
			'--json',
			'--woo_version',
			'nightly',
			'--plugin',
			'woocommerce',
		] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin get woocommerce',
		] );

		$this->assertStringContainsString( '-dev', $output );
	}

	public function test_env_up_woocommerce_rc_version() {
		$this->markTestSkipped();
		$json = json_decode( qit( [
			'env:up',
			'--json',
			'--woo_version',
			'rc',
			'--plugin',
			'https://github.com/woocommerce/woocommerce/releases/download/wc-beta-tester-2.3.0/woocommerce-beta-tester.zip:activate',
		] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin update woocommerce',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_with_additional_php_extensions() {
		$json = json_decode( qit( [
				'env:up',
				'--json',
				'--php_extension',
				'gd',
			]
		), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'php -m | grep gd',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_with_additional_themes() {
		$json = json_decode( qit( [
				'env:up',
				'--json',
				'--theme',
				'storefront',
				'--theme',
				'twentyseventeen',
			]
		), true );

		$output = '';

		$output .= qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme get storefront --fields=name,status',
		] );

		$output .= "\n";

		$output .= qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme get twentyseventeen --fields=name,status',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}
}
