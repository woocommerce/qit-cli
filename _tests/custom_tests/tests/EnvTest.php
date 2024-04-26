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
}