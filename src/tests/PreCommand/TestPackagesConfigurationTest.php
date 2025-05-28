<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class TestPackagesConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
	}

	public function test_test_package_with_lifecycle(): void {
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
						'test_dir'         => './tests/e2e',
						'description'      => 'E2E tests',
						'test_command'     => 'npm run playwright',
						'lifecycle'        => [
							'before_all_tests' => [
								[
									'command'  => '/normalized/path/before_all.sh',
									'priority' => 10,
									'runs_on'  => 'docker',
								],
							],
							'after_all_tests'  => [
								[
									'command' => '/normalized/path/after_all.sh',
									'runs_on' => 'docker',
								],
							],
						],
						'test_results'     => [
							'ctrf'   => './results/ctrf.json',
							'allure' => './results/allure-results',
						],
						'mu_plugins'       => [ '/normalized/path/mu-plugin.php' ],
						'required_secrets' => [ 'API_KEY' ],
						'env_vars'         => [
							'QIT_E2E_DEBUG' => 'true',
						],
					],
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'test_packages', $env_info );
		$this->assertArrayHasKey( 'e2e', $env_info['test_packages'] );
		$this->assertArrayHasKey( 'default', $env_info['test_packages']['e2e'] );
		$this->assertEquals( './tests/e2e', $env_info['test_packages']['e2e']['default']['test_dir'] );
		$this->assertCount( 2, $env_info['test_packages']['e2e']['default']['lifecycle']['before_all_tests'] );
		$this->assertCount( 1, $env_info['test_packages']['e2e']['default']['lifecycle']['after_all_tests'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_test_package_extends(): void {
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
							'test_packages' => [ 'local/default', 'local/basic' ],
						],
					],
				],
			],
			'test_packages' => [
				'e2e' => [
					'default' => [
						'test_dir'     => './tests/e2e',
						'test_command' => 'npm run playwright',
						'env_vars'     => [ 'QIT_E2E_DEBUG' => 'true' ],
					],
					'basic'   => [
						'extends'      => 'default',
						'test_command' => 'npm run playwright --project basic',
					],
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$env_info = $this->run_unit_test( $config );
		$this->assertEquals( 'npm run playwright --project basic', $env_info['test_packages']['e2e']['basic']['test_command'] );
		$this->assertEquals( [ 'QIT_E2E_DEBUG' => 'true' ], $env_info['test_packages']['e2e']['basic']['env_vars'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}