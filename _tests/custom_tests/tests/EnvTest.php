<?php

use Spatie\Snapshots\MatchesSnapshots;

class EnvTest extends \PHPUnit\Framework\TestCase {
	use MatchesSnapshots;

	public function test_env_up() {
		$output = qit( [ 'env:up' ] );

		// Extract the dynamic environment ID
		preg_match( '/Temporary test environment created. \((\w+)\)/', $output, $matches );
		$envId = $matches[1];

		// Extract the dynamic port number
		preg_match( '/localhost:(\d+)/', $output, $matches );
		$port = $matches[1];

		// Replace all instances of the environment ID and port number in the output
		$normalizedOutput = str_replace( $envId, 'ENV_ID', $output );
		$normalizedOutput = str_replace( $port, 'PORT', $normalizedOutput );
		$normalizedOutput = str_replace( $GLOBALS['RUN_ID'], 'RUN_ID', $normalizedOutput );

		// "WordPress Version: 6.5.2" => "WordPress Version: 6.5.2-normalized"
		$normalizedOutput = preg_replace( '/WordPress Version: .+/', 'WordPress Version: NORMALIZED', $normalizedOutput );

		$this->assertMatchesSnapshot( $normalizedOutput );
	}

	public function test_env_up_with_parameters() {
		$output = qit( [
				'env:up',
				'--wordpress_version',
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
		$output = qit( [ 'env:up' ], [
			'wordpress_version' => '6.4',
			'php_version'       => '8.2',
		] );

		// Check that WordPress Version is as expected:
		$this->assertStringContainsString( 'WordPress Version: 6.4', $output );

		// Check that PHP Version is as expected:
		$this->assertStringContainsString( 'PHP Version: 8.2', $output );
	}

	public function test_env_up_with_file_and_parameters() {
		$output = qit( [ 'env:up' ], [
			'wordpress_version' => '6.4',
			'php_version'       => '8.3',
		] );

		// Check that WordPress Version is as expected:
		$this->assertStringContainsString( 'WordPress Version: 6.4', $output );

		// Check that PHP Version is as expected:
		$this->assertStringContainsString( 'PHP Version: 8.3', $output );
	}

	public function test_env_up_with_plugins() {
		$json = json_decode( qit( [ 'env:up', '--json' ], [
			'plugins' => [
				'automatewoo' => [
					'action' => 'activate',
				],
				'woocommerce' => [
					'action' => 'activate',
				],
			],
		] ), true );

		$output = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp plugin list',
		] );

		/**
		 * name    status    update    version    update_version    auto_update
		 * automatewoo    active    none    6.0.20        off
		 * woocommerce    active    available    8.7.0.20    8.8.2    off
		 * qit-wp-cli    must-use    none            off
		 * wp-cli-github-cache    must-use    none            off
		 */

		// Iterate over each line, for the "automatewoo" and "woocommerce", normalize the version.
		$lines                = explode( "\n", $output );
		$headers              = preg_split( '/\s+/', trim( $lines[0] ) );  // Split the header to find the index of 'version'
		$version_index        = array_search( 'version', $headers );  // Locate the index of the 'version' column
		$update_version_index = array_search( 'update_version', $headers );  // Locate the index of the 'update_version' column

		foreach ( $lines as $key => $line ) {
			if ( strpos( $line, 'automatewoo' ) !== false || strpos( $line, 'woocommerce' ) !== false ) {
				$parts                          = preg_split( '/\s+/', trim( $line ) );
				$parts[ $version_index ]        = 'NORMALIZED_VERSION';
				$parts[ $update_version_index ] = 'NORMALIZED_VERSION';
				$lines[ $key ]                  = implode( '    ', $parts );
			}
		}
		$output = implode( "\n", $lines );

		$this->assertMatchesSnapshot( $output );
	}

	public function test_env_up_with_additional_volumes() {
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

		$this->assertMatchesSnapshot( $output );
	}
}