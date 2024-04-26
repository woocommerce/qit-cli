<?php

class EnvTest extends \PHPUnit\Framework\TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

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
		/**
		 * {"environment":"e2e","temporary_env":"\/home\/lucas\/automattic\/qit-cli\/dev\/config\/temporary-envs\/e2e-662c0dbb4ea99\/","created_at":1714163131,"status":"started","env_id":"662c0dbb4ea99","volumes":{"\/var\/www\/html\/wp-content\/plugins\/automatewoo":"\/home\/lucas\/automattic\/qit-cli\/dev\/config\/temporary-envs\/e2e-662c0dbb4ea99\/\/html\/wp-content\/plugins\/automatewoo","\/qit\/bin":"\/home\/lucas\/automattic\/qit-cli\/dev\/config\/temporary-envs\/e2e-662c0dbb4ea99\/\/bin","\/qit\/mu-plugins":"\/home\/lucas\/automattic\/qit-cli\/dev\/config\/temporary-envs\/e2e-662c0dbb4ea99\/\/mu-plugins","\/qit\/cache":"\/home\/lucas\/automattic\/qit-cli\/dev\/config\/cache\/","\/var\/www\/html":"qit_env_volume_662c0dbb4ea99"},"docker_images":["qit_env_db_662c0dbb4ea99","qit_env_php_662c0dbb4ea99","qit_env_nginx_662c0dbb4ea99"],"docker_network":"e2e-662c0dbb4ea99_qit_network_662c0dbb4ea99","php_extensions":[],"plugins":[{"slug":"automatewoo","source":"https:\/\/woothemes-products.s3.amazonaws.com\/plugin-packages\/automatewoo\/automatewoo.zip?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIA4DUDNEPINIB2X3P4%2F20240426%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20240426T201751Z&X-Amz-SignedHeaders=host&X-Amz-Expires=43200&X-Amz-Signature=2696f920deb5f7ebe51178202daa054f08506ec57dfdbc83b5d6feae406b9d86","downloaded_source":"\/home\/lucas\/automattic\/qit-cli\/dev\/config\/cache\/\/plugin\/automatewoo-6.0.17-116.zip","type":"plugin","handler":"QIT_CLI\\Environment\\ExtensionDownload\\Handlers\\QITHandler","version":"6.0.17","action":"activate","test_tags":["default"]}],"themes":[],"site_url":"http:\/\/localhost:32961","wordpress_version":"6.5.2","object_cache":false,"php_version":"7.4","nginx_port":"32961","domain":"localhost","woocommerce_version":null,"tests":[]}
		 */
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
		$lines         = explode( "\n", $output );
		$headers       = preg_split( '/\s+/', trim( $lines[0] ) );  // Split the header to find the index of 'version'
		$version_index = array_search( 'version', $headers );  // Locate the index of the 'version' column

		foreach ( $lines as $key => $line ) {
			if ( strpos( $line, 'automatewoo' ) !== false || strpos( $line, 'woocommerce' ) !== false ) {
				$parts                   = preg_split( '/\s+/', trim( $line ) );
				$parts[ $version_index ] = 'NORMALIZED_VERSION';
				$lines[ $key ]           = implode( '    ', $parts );
			}
		}
		$output = implode( "\n", $lines );

		$this->assertMatchesSnapshot( $output );
	}
}