<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class ErrorCasesTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
	}

	public function test_invalid_source_type(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'invalid',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid source type \'invalid\'', $error );
	}

	public function test_missing_source_fields(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'build',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Build source must contain a non-empty "command" string', $error );
	}

	public function test_invalid_plugin_from(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						[ 'slug' => 'custom-plugin', 'from' => 'invalid' ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid \'from\' value \'invalid\' for \'custom-plugin\' in plugins', $error );
	}

	public function test_duplicate_plugin_slug(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [
						'woocommerce',
						[ 'slug' => 'woocommerce', 'from' => 'local', 'path' => './woocommerce' ],
					],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Duplicate slug \'woocommerce\' in plugins', $error );
	}

	public function test_invalid_php_version(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins'     => [ 'woocommerce' ],
					'php_version' => 'invalid',
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid php_version in environment \'default\'', $error );
	}
}