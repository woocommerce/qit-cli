<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class TestTypesConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
		$this->mockWooComDownloadUrls( [
			'woocommerce' => 'https://qit.woo.com/downloads/woocommerce.zip',
		] );
	}

	public function test_e2e_test_type_with_test_packages(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				'e2e' => [
					'default' => [
						'test_dir'     => './tests/e2e',
						'test_command' => 'npm run playwright',
					],
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'No command found for test type \'e2e\'', $error );
	}

	public function test_woo_e2e_test_type_with_tweaks(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'test_types'   => [
				'woo-e2e' => [
					'default' => [
						'environment' => 'default',
						'php_version' => '8.4',
						'tweaks'      => [
							'skip' => [ 'test case 1', '/regex.*/' ],
						],
					],
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'No command found for test type \'woo-e2e\'', $error );
	}

	public function test_missing_test_packages_reference(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/invalid' ],
						],
					],
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Test package \'local/invalid\' in \'e2e:default\' not found in test_packages', $error );
	}
}