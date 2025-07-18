<?php

use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class EnvTest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;

	protected function tearDown(): void {
		qit( [ 'env:down' ] );
		parent::tearDown();
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

		// 2. Check the configuration resolution using qit
		$output  = qit( [ 'env:up', '--json' ], $qitJson );
		$env     = json_decode( $output, true );
		$plugins = $env['plugins'];

		// Verify plugins are correctly resolved
		$this->assertCount( 2, $plugins );
		$pluginSlugs = array_column( $plugins, 'slug' );
		$this->assertContains( 'woocommerce-amazon-s3-storage', $pluginSlugs );
		$this->assertContains( 'woocommerce', $pluginSlugs );

		// 3. Bring the environment up and capture the JSON response for actual execution
		$envInfo = json_decode(
			qit( [ 'env:up', '--json' ], $qitJson ),
			true
		);

		// 4. Run `wp plugin list` inside that environment.
		$rawList = qit( [
			'env:exec',
			'--env_id',
			$envInfo['env_id'],
			'wp plugin list --fields=name,status'
		] );

		// 5. Canonicalise the listing so snapshots are stable.
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

		// Check the configuration resolution using qit
		$output = qit( [
				'env:up',
				'--volume',
				sprintf( sys_get_temp_dir() . '/qit-tmp-plugin.php' . ':/var/www/html/wp-content/plugins/qit-tmp-plugin.php' ),
				'--json',
			]
		);

		$env         = json_decode( $output, true );
		$userVolumes = array_values(
			array_filter(
				array_keys( $env['volumes'] ),
				static fn( $v ) => str_contains( $v, 'qit-tmp-plugin.php' )
			)
		);

		$this->assertCount( 1, $userVolumes );
		$this->assertStringContainsString( 'qit-tmp-plugin.php', $userVolumes[0] );


		// Bring up the environment for actual execution
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

	public function test_env_up_wordpress_stable() {
		// Check the configuration resolution using qit
		$output = qit( [ 'env:up', '--wp', 'stable', '--json' ] );
		$env    = json_decode( $output, true );

		// Verify WordPress version is correctly resolved
		$this->assertSame( 'stable', $env['wp'] );

		// Bring up the environment for actual execution
		$json = json_decode( qit( [ 'env:up', '--json', '--wp', 'stable' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp core check-update --force-check',
		] );

		$this->assertStringContainsString( 'WordPress is at the latest version', $output );
	}

	public function test_env_up_wordpress_nightly() {
		// Check the configuration resolution using qit
		$output = qit( [ 'env:up', '--wp', 'nightly', '--json' ] );
		$env    = json_decode( $output, true );

		// Verify WordPress version is correctly resolved
		$this->assertSame( 'nightly', $env['wp'] );

		// Bring up the environment for actual execution
		$json = json_decode( qit( [ 'env:up', '--json', '--wp', 'nightly' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp core version',
		] );

		$this->assertMatchesRegularExpression(
			'/^\d+\.\d+-(alpha|beta|RC\d?)-\d+/',
			trim( $output )
		);
	}

	public function test_env_up_woocommerce_stable() {
		// Check the configuration resolution using qit
		$output = qit( [ 'env:up', '--woo', 'stable', '--plugin', 'woocommerce', '--json' ] );
		$env    = json_decode( $output, true );

		// Verify WooCommerce version is correctly resolved
		$this->assertSame( 'stable', $env['woo'] );
		$pluginSlugs = array_column( $env['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );

		// Bring up the environment for actual execution
		$json = json_decode( qit( [ 'env:up', '--json', '--woo', 'stable', '--plugin', 'woocommerce' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin update woocommerce',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_woocommerce_stable_alternative_syntax() {
		// Check the configuration resolution using qit
		$output = qit( [ 'env:up', '--plugin', 'woocommerce', '--json' ] );
		$env    = json_decode( $output, true );

		// Verify WooCommerce is correctly resolved
		$pluginSlugs = array_column( $env['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );

		// Bring up the environment for actual execution
		$json = json_decode( qit( [ 'env:up', '--json', '--plugin', 'woocommerce' ] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin update woocommerce',
		] );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_env_up_woocommerce_nightly() {
		// Check the configuration resolution using qit
		$output = qit( [
			'env:up',
			'--woo',
			'nightly',
			'--plugin',
			'woocommerce',
			'--json',
		] );

		$env = json_decode( $output, true );

		// Verify WooCommerce version is correctly resolved
		$this->assertSame( 'nightly', $env['woo'] );
		$pluginSlugs = array_column( $env['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );

		// Bring up the environment for actual execution
		$json = json_decode( qit( [
			'env:up',
			'--json',
			'--woo',
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

	public function test_env_up_woocommerce_rc() {
		$this->markTestSkipped();
		$json = json_decode( qit( [
			'env:up',
			'--json',
			'--woo',
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
		// Check the configuration resolution using qit
		$output = qit( [
				'env:up',
				'--php_extension',
				'gd',
				'--json',
			]
		);

		$env = json_decode( $output, true );

		// Verify PHP extension is correctly resolved
		$this->assertContains( 'gd', $env['php_extensions'] );

		// Bring up the environment for actual execution
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
		// Check the configuration resolution using qit
		$output = qit( [
				'env:up',
				'--theme',
				'storefront',
				'--theme',
				'twentyseventeen',
				'--json',
			]
		);

		$env = json_decode( $output, true );

		// Verify themes are correctly resolved
		$this->assertCount( 2, $env['themes'] );
		$themeSlugs = array_column( $env['themes'], 'slug' );
		$this->assertContains( 'storefront', $themeSlugs );
		$this->assertContains( 'twentyseventeen', $themeSlugs );

		// Bring up the environment for actual execution
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
