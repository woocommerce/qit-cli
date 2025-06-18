<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;
use Spatie\Snapshots\Drivers\JsonDriver;

class ProfileTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use SnapshotHelpers;

	protected function getTempDir(): string {
		return sys_get_temp_dir() . '/qit_test_' . uniqid();
	}

	protected function getNonJsonLogMessage( string $nonJsonLog ): string {
		return file_exists( $nonJsonLog ) ? "\nNon-JSON Log:\n" . file_get_contents( $nonJsonLog ) : "\nNo non-JSON log";
	}

	public function test_run_e2e_no_qit_json_implicit_profile() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$this->scaffold_plugin( $tempDir . '/test-plugin' );
		$nonJsonLog = $tempDir . '/non_json.log';

		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--source' => $tempDir . '/test-plugin',
			'--plugin' => 'woocommerce',
			'--json',
			'--verbose',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_info', 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, 'Invalid JSON output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$this->assertArrayNotHasKey( 'error', $envInfo, 'Unexpected error in output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$normalizedEnvInfo = $this->normalize_env_info( $envInfo );
		$this->assertMatchesSnapshot( json_encode( $normalizedEnvInfo, JSON_PRETTY_PRINT ), new JsonDriver() );
	}

	public function test_run_e2e_no_qit_json_explicit_profile() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$this->scaffold_plugin( $tempDir . '/test-plugin' );
		$nonJsonLog = $tempDir . '/non_json.log';

		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--source'  => $tempDir . '/test-plugin',
			'--plugin'  => 'woocommerce',
			'--profile' => 'default',
			'--json',
			'--verbose',
		], [], 1, [ 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$result         = json_decode( $output, true );
		$nonJsonMessage = $this->getNonJsonLogMessage( $nonJsonLog );
		// Handle non-JSON output for early validation errors
		if ( ! is_array( $result ) ) {
			$this->assertStringContainsString( 'Test configuration \'e2e:default\' not found', file_get_contents( $nonJsonLog ), 'Expected error message not found' . $nonJsonMessage );

			return;
		}
		$this->assertArrayHasKey( 'error', $result, 'Expected error key in JSON output' . $nonJsonMessage );
		$errorMessage = is_array( $result['error'] ) ? $result['error']['message'] : $result['error'];
		$this->assertStringContainsString( 'Test configuration \'e2e:default\' not found', $errorMessage, 'Unexpected error message' . $nonJsonMessage );
	}

	public function test_run_e2e_with_qit_json_implicit_profile() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$this->scaffold_plugin( $tempDir . '/test-plugin' );
		$nonJsonLog = $tempDir . '/non_json.log';

		$qitJson = [
			'tests'        => [
				'e2e' => [
					'default' => [
						'env'         => 'base',
						'php_version' => '8.2',
					],
				],
			],
			'environments' => [
				'base' => [
					'php_version' => '8.2',
				],
			],
		];
		file_put_contents( $tempDir . '/qit.json', json_encode( $qitJson ) );

		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--source' => $tempDir . '/test-plugin',
			'--plugin' => 'woocommerce',
			'--json',
			'--verbose',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_info', 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, 'Invalid JSON output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$this->assertArrayNotHasKey( 'error', $envInfo, 'Unexpected error in output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$normalizedEnvInfo = $this->normalize_env_info( $envInfo );
		$this->assertEquals( '8.2', $normalizedEnvInfo['php_version'] );
		$this->assertMatchesSnapshot( json_encode( $normalizedEnvInfo, JSON_PRETTY_PRINT ), new JsonDriver() );
	}

	public function test_run_e2e_with_qit_json_explicit_profile() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$this->scaffold_plugin( $tempDir . '/test-plugin' );
		$nonJsonLog = $tempDir . '/non_json.log';

		$qitJson = [
			'tests'        => [
				'e2e' => [
					'custom' => [
						'env'         => 'base',
						'php_version' => '8.3',
					],
				],
			],
			'environments' => [
				'base' => [
					'php_version' => '8.3',
				],
			],
		];
		file_put_contents( $tempDir . '/qit.json', json_encode( $qitJson ) );

		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--source'  => $tempDir . '/test-plugin',
			'--plugin'  => 'woocommerce',
			'--profile' => 'custom',
			'--json',
			'--verbose',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_info', 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, 'Invalid JSON output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$this->assertArrayNotHasKey( 'error', $envInfo, 'Unexpected error in output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$normalizedEnvInfo = $this->normalize_env_info( $envInfo );
		$this->assertEquals( '8.3', $normalizedEnvInfo['php_version'] );
		$this->assertMatchesSnapshot( json_encode( $normalizedEnvInfo, JSON_PRETTY_PRINT ), new JsonDriver() );
	}

	public function test_run_e2e_profile_settings_override() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$this->scaffold_plugin( $tempDir . '/test-plugin' );
		$nonJsonLog = $tempDir . '/non_json.log';

		$qitJson = [
			'tests'        => [
				'e2e' => [
					'default' => [
						'env'         => 'base',
						'php_version' => '8.1',
					],
				],
			],
			'environments' => [
				'base' => [
					'php_version' => '8.1',
				],
			],
		];
		file_put_contents( $tempDir . '/qit.json', json_encode( $qitJson ) );

		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--source'      => $tempDir . '/test-plugin',
			'--plugin'      => 'woocommerce',
			'--php_version' => '8.2',
			'--json',
			'--verbose',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_info', 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, 'Invalid JSON output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$this->assertArrayNotHasKey( 'error', $envInfo, 'Unexpected error in output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$normalizedEnvInfo = $this->normalize_env_info( $envInfo );
		$this->assertEquals( '8.2', $normalizedEnvInfo['php_version'] );
		$this->assertMatchesSnapshot( json_encode( $normalizedEnvInfo, JSON_PRETTY_PRINT ), new JsonDriver() );
	}

	public function test_run_e2e_pre_test_build() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$this->scaffold_plugin( $tempDir . '/test-plugin' );
		$nonJsonLog = $tempDir . '/non_json.log';

		$qitJson = [
			'tests' => [
				'e2e' => [
					'default' => [
						'pre_test_build' => [
							'command' => 'echo "build" > ' . $tempDir . '/build.zip',
							'output'  => $tempDir . '/build.zip',
						],
					],
				],
			],
		];
		file_put_contents( $tempDir . '/qit.json', json_encode( $qitJson ) );

		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--source' => $tempDir . '/test-plugin',
			'--plugin' => 'woocommerce',
			'--json',
			'--verbose',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_info', 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$envInfo = json_decode( $output, true );
		$this->assertIsArray( $envInfo, 'Invalid JSON output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$this->assertArrayNotHasKey( 'error', $envInfo, 'Unexpected error in output' . $this->getNonJsonLogMessage( $nonJsonLog ) );
		$normalizedEnvInfo = $this->normalize_env_info( $envInfo );
		$this->assertFileExists( $tempDir . '/build.zip' );
		$this->assertStringContainsString( 'build.zip', json_encode( $normalizedEnvInfo ) );
		$this->assertMatchesSnapshot( json_encode( $normalizedEnvInfo, JSON_PRETTY_PRINT ), new JsonDriver() );
	}

	public function test_run_security_with_qit_json() {
		$tempDir = $this->getTempDir();
		mkdir( $tempDir );
		$nonJsonLog = $tempDir . '/non_json.log';

		$qitJson = [
			'tests' => [
				'security' => [
					'default' => [
						'security_level' => 'high',
						'pre_test_build' => [
							'command' => 'echo "build" > ' . $tempDir . '/build.zip',
							'output'  => $tempDir . '/build.zip',
						],
					],
				],
			],
		];
		file_put_contents( $tempDir . '/qit.json', json_encode( $qitJson ) );

		$output = qit( [
			'run:security',
			'woocommerce',
			'--json',
			'--verbose',
		], [], 0, [ 'QIT_SELF_TEST' => 'options', 'QIT_NON_JSON_OUTPUT' => $nonJsonLog ] );

		$options        = json_decode( $output, true );
		$nonJsonMessage = $this->getNonJsonLogMessage( $nonJsonLog );
		$this->assertIsArray( $options, 'Invalid JSON output' . $nonJsonMessage );
		$this->assertArrayNotHasKey( 'error', $options, 'Unexpected error in output' . $nonJsonMessage );
		$this->assertEquals( 'high', $options['security_level'], 'Unexpected security_level' . $nonJsonMessage );
		$this->assertFileExists( $tempDir . '/build.zip' );
		$this->assertMatchesSnapshot( json_encode( $options, JSON_PRETTY_PRINT ), new JsonDriver() );
	}
}