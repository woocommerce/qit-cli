<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class TestGroupsConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
		$this->mockWooComDownloadUrls( [
			'woocommerce' => 'https://qit.woo.com/downloads/woocommerce.zip',
		] );
	}

	public function test_test_groups(): void {
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
				'e2e'     => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
				'phpstan' => [
					'basic' => [
						'environment'   => 'default',
						'phpstan_level' => 0,
					],
				],
			],
			'test_groups'   => [
				'pre_release' => [
					'e2e'     => [ 'default' ],
					'phpstan' => [ 'basic' ],
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

	public function test_invalid_test_group_reference(): void {
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
			'test_groups'   => [
				'pre_release' => [
					'e2e' => [ 'invalid' ],
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
		$this->assertStringContainsString( 'Test profile \'invalid\' for type \'e2e\' in group \'pre_release\' not found', $error );
	}
}